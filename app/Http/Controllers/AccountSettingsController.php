<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\ClientContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AccountSettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeTab = in_array($request->input('tab'), ['profile', 'security', 'preferences', 'activity'], true)
            ? $request->input('tab')
            : 'profile';

        // Active sessions from database `sessions` table
        $currentSessionId = $request->session()->getId();
        $sessions = collect();
        if (Schema::hasTable('sessions')) {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($s) use ($currentSessionId) {
                    $device = ClientContext::parseDevice($s->user_agent ?? '');
                    return (object) [
                        'id'           => $s->id,
                        'ip_address'   => $s->ip_address ? ClientContext::normalizeIp($s->ip_address) : 'Unknown',
                        'browser'      => $device['browser'],
                        'os'           => $device['os'],
                        'device_type'  => $device['device'],
                        'device_label' => $device['label'],
                        'last_active'  => \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                        'is_current'   => $s->id === $currentSessionId,
                    ];
                });
        }

        // Personal recent activity strictly scoped to this user
        $recentActivity = AuditLog::where('user_id', $user->id)
            ->latest('created_at')
            ->limit(15)
            ->get()
            ->map(function ($log) {
                $device = ClientContext::parseDevice($log->user_agent ?? '');
                $log->device_label = $device['label'];
                $log->friendly_event = AuditLogController::formatEventLabel((string) $log->event);
                return $log;
            });

        // Role-specific personal operational snapshot
        $roleSnapshot = $this->buildRoleSnapshot($user);

        return view('account.settings', compact(
            'user',
            'activeTab',
            'sessions',
            'recentActivity',
            'roleSnapshot'
        ));
    }

    public function serveAvatar(User $user)
    {
        if (empty($user->avatar_data)) {
            abort(404);
        }

        $binary = base64_decode($user->avatar_data, true);
        if ($binary === false) {
            abort(404);
        }

        return response($binary, 200)
            ->header('Content-Type', $user->avatar_mime ?: 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=604800');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        $rules = [
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        // Only System Admin may modify their own legal identity fields (prevents audit/receipt spoofing)
        if ($isAdmin) {
            $rules['firstname'] = ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\-\'\.]+$/'];
            $rules['lastname']  = ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\-\'\.]+$/'];
            $rules['suffix']    = ['nullable', 'string', 'in:Jr.,Sr.,II,III,IV,V'];
        }

        $validated = $request->validate($rules, [
            'firstname.regex' => 'First name must contain letters only.',
            'lastname.regex'  => 'Last name must contain letters only.',
        ]);

        $user->email = trim($validated['email']);
        if ($isAdmin) {
            $user->firstname = trim($validated['firstname']);
            $user->lastname  = trim($validated['lastname']);
            $user->suffix    = $validated['suffix'] ?? null;
        }

        if ($request->boolean('remove_avatar')) {
            $user->avatar_data = null;
            $user->avatar_mime = null;
        } elseif ($request->hasFile('avatar')) {
            [$b64, $mime] = $this->encodeAvatar($request->file('avatar'));
            $user->avatar_data = $b64;
            $user->avatar_mime = $mime;
        }

        $user->save();

        return redirect()->route('account.settings', ['tab' => 'profile'])
            ->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request, NotificationService $notifications)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'min:8',
                'max:64',
                'confirmed',
                'regex:/^[\x21-\x7E]+$/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.regex' => 'Password must be 8-64 standard ASCII characters with uppercase, lowercase, number, and symbol (no spaces or emojis).',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('account.settings', ['tab' => 'security'])
                ->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        if (Hash::check($validated['password'], $user->password)) {
            return redirect()->route('account.settings', ['tab' => 'security'])
                ->withErrors(['password' => 'New password must be different from your current password.']);
        }

        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        // Automatically sign out other devices for security
        $revokedCount = 0;
        if (Schema::hasTable('sessions')) {
            $revokedCount = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        AuditLog::create([
            'user_id'        => $user->id,
            'actor_name'     => $user->full_name,
            'actor_role'     => $user->role,
            'event'          => 'password_changed',
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'old_values'     => ['status' => 'Previous password'],
            'new_values'     => ['status' => 'Password updated via Account Settings', 'other_sessions_revoked' => $revokedCount],
            'ip_address'     => ClientContext::ip($request),
            'user_agent'     => $request->userAgent(),
        ]);

        $notifications->notifyUser($user->id, [
            'type'       => 'password_changed',
            'category'   => 'security',
            'severity'   => 'info',
            'title'      => 'Password Changed',
            'message'    => 'Your account password was updated.' . ($revokedCount > 0 ? " {$revokedCount} other session(s) were signed out." : ''),
            'action_url' => route('account.settings', ['tab' => 'security']),
        ]);

        return redirect()->route('account.settings', ['tab' => 'security'])
            ->with('success', 'Password updated successfully.' . ($revokedCount > 0 ? " {$revokedCount} other device session(s) were signed out." : ''));
    }

    public function updateActionPassword(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'manager'], true)) {
            abort(403, 'Only Administrators and Operations Managers can set a Manager Override password.');
        }

        $validated = $request->validate([
            'current_login_password' => ['required', 'string'],
            'action_password'        => ['required', 'string', 'min:6', 'max:64', 'confirmed', 'regex:/^[\x21-\x7E]+$/'],
        ], [
            'action_password.regex' => 'Override password must contain standard ASCII characters only (no spaces or emojis).',
        ]);

        if (!Hash::check($validated['current_login_password'], $user->password)) {
            return redirect()->route('account.settings', ['tab' => 'security'])
                ->withErrors(['current_login_password' => 'Your login password is incorrect.']);
        }

        $user->action_password = Hash::make($validated['action_password']);
        $user->save();

        AuditLog::create([
            'user_id'        => $user->id,
            'actor_name'     => $user->full_name,
            'actor_role'     => $user->role,
            'event'          => 'action_password_updated',
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'old_values'     => null,
            'new_values'     => ['status' => 'Manager override password updated'],
            'ip_address'     => ClientContext::ip($request),
            'user_agent'     => $request->userAgent(),
        ]);

        return redirect()->route('account.settings', ['tab' => 'security'])
            ->with('success', 'Manager Override password updated successfully.');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'theme'          => ['required', 'in:light,dark'],
            'toast_alerts'   => ['nullable', 'boolean'],
            'mute_low_stock' => ['nullable', 'boolean'],
            'default_view'   => ['required', 'in:list,grid'],
        ]);

        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $prefs['toast_alerts']   = $request->boolean('toast_alerts');
        $prefs['mute_low_stock'] = $request->boolean('mute_low_stock');
        $prefs['default_view']   = $validated['default_view'];

        $user->theme = $validated['theme'];
        $user->preferences = $prefs;
        $user->save();

        return redirect()->route('account.settings', ['tab' => 'preferences'])
            ->with('success', 'Preferences saved successfully.');
    }

    public function revokeSession(Request $request, string $sessionId)
    {
        $user = Auth::user();
        if ($sessionId === $request->session()->getId()) {
            return redirect()->route('account.settings', ['tab' => 'security'])
                ->with('error', 'Use Sign Out to end your current session.');
        }

        $deleted = 0;
        if (Schema::hasTable('sessions')) {
            $deleted = DB::table('sessions')
                ->where('id', $sessionId)
                ->where('user_id', $user->id)
                ->delete();
        }

        if ($deleted > 0) {
            AuditLog::create([
                'user_id'        => $user->id,
                'actor_name'     => $user->full_name,
                'actor_role'     => $user->role,
                'event'          => 'session_revoked',
                'auditable_type' => User::class,
                'auditable_id'   => $user->id,
                'old_values'     => null,
                'new_values'     => ['revoked_session_count' => 1],
                'ip_address'     => ClientContext::ip($request),
                'user_agent'     => $request->userAgent(),
            ]);
        }

        return redirect()->route('account.settings', ['tab' => 'security'])
            ->with('success', 'Selected device session signed out.');
    }

    public function revokeOtherSessions(Request $request, NotificationService $notifications)
    {
        $user = Auth::user();
        $deleted = 0;

        if (Schema::hasTable('sessions')) {
            $deleted = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        if ($deleted > 0) {
            AuditLog::create([
                'user_id'        => $user->id,
                'actor_name'     => $user->full_name,
                'actor_role'     => $user->role,
                'event'          => 'sessions_revoked',
                'auditable_type' => User::class,
                'auditable_id'   => $user->id,
                'old_values'     => null,
                'new_values'     => ['revoked_session_count' => $deleted],
                'ip_address'     => ClientContext::ip($request),
                'user_agent'     => $request->userAgent(),
            ]);

            $notifications->notifyUser($user->id, [
                'type'       => 'sessions_revoked',
                'category'   => 'security',
                'severity'   => 'warning',
                'title'      => 'Other Sessions Signed Out',
                'message'    => "{$deleted} other active session(s) on your account were terminated.",
                'action_url' => route('account.settings', ['tab' => 'security']),
            ]);
        }

        return redirect()->route('account.settings', ['tab' => 'security'])
            ->with('success', $deleted > 0
                ? "Signed out {$deleted} other device session(s)."
                : 'No other active sessions were found.');
    }

    private function buildRoleSnapshot(User $user): array
    {
        $today = today();

        if ($user->role === 'staff') {
            $txQuery = Transaction::where('user_id', $user->id)
                ->whereIn('transaction_type', [0, 1])
                ->whereDate('created_at', $today);

            return [
                'title' => "Today's Cashier Shift Snapshot",
                'stats' => [
                    ['label' => 'Receipts Issued Today', 'value' => (string) (clone $txQuery)->count()],
                    ['label' => 'Sales Handled Today', 'value' => '₱' . number_format((float) (clone $txQuery)->sum('total_amount'), 2)],
                    ['label' => 'Pending Restock Requests', 'value' => (string) StockMovement::where('requested_by', $user->id)->where('status', 'pending')->count()],
                ],
            ];
        }

        if ($user->role === 'warehouse_staff') {
            return [
                'title' => "Today's Warehouse Snapshot",
                'stats' => [
                    ['label' => 'Pending Incoming Deliveries', 'value' => (string) PurchaseOrder::where('status', 'pending')->count()],
                    ['label' => 'GRNs Logged Today', 'value' => (string) StockMovement::where('requested_by', $user->id)->where('type', 'po_received')->whereDate('created_at', $today)->count()],
                    ['label' => 'My Pending Restock Requests', 'value' => (string) StockMovement::where('requested_by', $user->id)->where('status', 'pending')->count()],
                ],
            ];
        }

        return [
            'title' => "Today's Management Overview",
            'stats' => [
                ['label' => 'Actions Logged Today', 'value' => (string) AuditLog::where('user_id', $user->id)->whereDate('created_at', $today)->count()],
                ['label' => 'Approvals Handled Today', 'value' => (string) StockMovement::where('approved_by', $user->id)->whereDate('updated_at', $today)->count()],
                ['label' => 'Manager Override PIN', 'value' => $user->has_action_password ? 'Configured' : 'Not Set'],
            ],
        ];
    }

    private function encodeAvatar($file): array
    {
        $realPath = $file->getRealPath();
        $rawMime = $file->getMimeType() ?: 'image/jpeg';

        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            try {
                $src = @imagecreatefromstring(file_get_contents($realPath));
                if ($src !== false) {
                    $origW = imagesx($src);
                    $origH = imagesy($src);
                    $size = min($origW, $origH);
                    $srcX = (int) floor(($origW - $size) / 2);
                    $srcY = (int) floor(($origH - $size) / 2);
                    $target = 200;

                    $dst = imagecreatetruecolor($target, $target);
                    $white = imagecolorallocate($dst, 255, 255, 255);
                    imagefill($dst, 0, 0, $white);
                    imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $target, $target, $size, $size);

                    ob_start();
                    imagejpeg($dst, null, 82);
                    $compressed = ob_get_clean();

                    imagedestroy($src);
                    imagedestroy($dst);

                    if ($compressed !== false && strlen($compressed) > 0) {
                        return [base64_encode($compressed), 'image/jpeg'];
                    }
                }
            } catch (\Throwable $e) {
                // fallback below
            }
        }

        return [base64_encode(file_get_contents($realPath)), $rawMime];
    }
}

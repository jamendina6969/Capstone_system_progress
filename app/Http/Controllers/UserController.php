<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    private const PER_PAGE = 15;

    public const ALLOWED_SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
    public const ALLOWED_ROLES = ['admin', 'manager', 'staff', 'warehouse_staff'];

    private function landingRouteFor(User $user): string
    {
        return match ($user->role) {
            'staff' => 'pos',
            'warehouse_staff' => 'inventory.index',
            default => 'home',
        };
    }

    private function sanitizeUserInput(Request $request): void
    {
        $cleanName = fn ($val) => is_string($val) ? preg_replace('/\s+/', ' ', trim($val)) : $val;

        $suffix = $request->input('suffix');
        $suffix = is_string($suffix) ? trim($suffix) : null;

        $request->merge([
            'firstname' => $cleanName($request->input('firstname')),
            'lastname'  => $cleanName($request->input('lastname')),
            'suffix'    => $suffix === '' ? null : $suffix,
            'username'  => is_string($request->input('username')) ? strtolower(trim($request->input('username'))) : $request->input('username'),
            'email'     => is_string($request->input('email')) ? strtolower(trim($request->input('email'))) : $request->input('email'),
        ]);
    }

    private function strongPasswordRules(string $username = '', string $email = ''): array
    {
        return [
            'string',
            'confirmed',
            'max:72',
            'regex:/^[\x21-\x7E]+$/',
            Password::min(8)->mixedCase()->numbers()->symbols(),
            function (string $attribute, mixed $value, \Closure $fail) use ($username, $email) {
                $str = (string) $value;
                if (!preg_match('/[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/', $str)) {
                    $fail('The password must contain at least one standard keyboard special character (e.g. @, #, $, %, !).');
                }
                $valLower = strtolower($str);
                if ($username !== '' && $valLower === strtolower($username)) {
                    $fail('The password must not be the same as the username.');
                }
                if ($email !== '' && $valLower === strtolower($email)) {
                    $fail('The password must not be the same as the email address.');
                }
            },
        ];
    }

    public function index(Request $request)
    {
        $users = User::orderBy('lastname')->paginate(self::PER_PAGE)->withQueryString();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('admin.users.partials.rows', compact('users'))->render(),
                'next_page_url' => $users->nextPageUrl(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total_records' => $users->total(),
                'per_page' => $users->perPage(),
                'first_item' => $users->firstItem(),
                'last_item' => $users->lastItem(),
                'has_more' => $users->hasMorePages(),
            ]);
        }

        return view('admin.users.index', [
            'users' => $users,
            'allowedSuffixes' => self::ALLOWED_SUFFIXES,
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'allowedSuffixes' => self::ALLOWED_SUFFIXES,
        ]);
    }

    public function store(Request $request)
    {
        $this->sanitizeUserInput($request);

        $role = (string) $request->input('role');
        $isPrivileged = in_array($role, ['admin', 'manager'], true);

        $rules = [
            'firstname' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'lastname'  => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'suffix'    => ['nullable', 'string', Rule::in(self::ALLOWED_SUFFIXES)],
            'username'  => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('users', 'username'),
            ],
            'email'     => ['required', 'string', 'email:rfc,filter', 'max:150', Rule::unique('users', 'email')],
            'password'  => array_merge(
                ['required'],
                $this->strongPasswordRules((string) $request->input('username'), (string) $request->input('email'))
            ),
            'role'      => ['required', Rule::in(self::ALLOWED_ROLES)],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($isPrivileged) {
            $rules['action_password'] = ['required', 'string', 'confirmed', 'min:6', 'max:32', 'regex:/^[\x21-\x7E]+$/', 'different:password'];
        } else {
            $rules['action_password'] = ['nullable', 'string'];
        }

        $messages = [
            'firstname.regex' => 'First name may only contain letters, spaces, periods, hyphens, and apostrophes (no numbers or emojis).',
            'lastname.regex'  => 'Last name may only contain letters, spaces, periods, hyphens, and apostrophes (no numbers or emojis).',
            'suffix.in'       => 'Please select a valid suffix from the list.',
            'username.regex'  => 'Username must start with a letter or number and contain only lowercase letters, numbers, dots, underscores, or hyphens (no spaces).',
            'password.regex'  => 'Password may only contain standard letters, numbers, and keyboard symbols (no spaces or emojis).',
            'action_password.required'  => 'Manager Override Password is required for Admin and Manager roles.',
            'action_password.regex'     => 'Manager Override Password may only contain standard letters, numbers, and keyboard symbols (no spaces or emojis).',
            'action_password.different' => 'Manager Override Password must be different from the login password.',
            'action_password.confirmed' => 'Manager Override Password confirmation does not match.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $user = new User();
            $user->firstname = $data['firstname'];
            $user->lastname = $data['lastname'];
            $user->suffix = $data['suffix'] ?? null;
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = $data['role'];
            $user->is_active = $request->boolean('is_active');
            $user->must_change_password = true;
            $user->theme = 'light';

            $user->action_password = $isPrivileged && !empty($data['action_password'])
                ? Hash::make($data['action_password'])
                : null;

            $user->save();

            try {
                $authUser = Auth::user();
                AuditLog::create([
                    'user_id' => $authUser->id,
                    'actor_name' => $authUser->firstname . ' ' . $authUser->lastname,
                    'actor_role' => $authUser->role,
                    'event' => 'created',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'new_values' => array_merge(
                        $user->only(['firstname', 'lastname', 'suffix', 'username', 'email', 'role', 'is_active', 'must_change_password']),
                        ['has_action_password' => !empty($user->action_password)]
                    ),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully. They will be required to change their password on first login.');

        } catch (\Exception $e) {
            return back()->with('error', 'Database Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'allowedSuffixes' => self::ALLOWED_SUFFIXES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->sanitizeUserInput($request);

        $isSelf = $user->id === Auth::id();
        $targetRole = $isSelf ? $user->role : (string) $request->input('role');
        $targetActive = $isSelf ? true : $request->boolean('is_active');

        // Self-protection: prevent logged-in admin from demoting or deactivating themselves
        if ($isSelf) {
            if ($request->filled('role') && $request->input('role') !== $user->role) {
                return back()->withErrors(['role' => 'You cannot change your own account role.'])->withInput();
            }
            if ($request->has('is_active') && !$request->boolean('is_active')) {
                return back()->withErrors(['is_active' => 'You cannot deactivate your own account.'])->withInput();
            }
            $request->merge([
                'role' => $user->role,
                'is_active' => '1',
            ]);
        }

        // Last active admin protection
        if ($user->role === 'admin' && $user->is_active && ($targetRole !== 'admin' || !$targetActive)) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->withErrors([
                    'role' => 'Cannot demote or deactivate this account because at least one active System Administrator must remain.',
                ])->withInput();
            }
        }

        $isPrivileged = in_array($targetRole, ['admin', 'manager'], true);
        $wasPrivileged = in_array($user->role, ['admin', 'manager'], true);
        $needsOverridePassword = $isPrivileged && !$wasPrivileged && !$user->has_action_password;

        $allowedSuffixes = self::ALLOWED_SUFFIXES;
        if (!empty($user->suffix) && !in_array($user->suffix, $allowedSuffixes, true)) {
            $allowedSuffixes[] = $user->suffix;
        }

        $rules = [
            'firstname' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'lastname'  => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'suffix'    => ['nullable', 'string', Rule::in($allowedSuffixes)],
            'username'  => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email'     => ['required', 'string', 'email:rfc,filter', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => array_merge(
                ['nullable'],
                $this->strongPasswordRules((string) $request->input('username'), (string) $request->input('email')),
                [
                    function (string $attribute, mixed $value, \Closure $fail) use ($request, $user, $isPrivileged) {
                        if (empty($value)) {
                            return;
                        }
                        if ($isPrivileged && !$request->filled('action_password') && $user->action_password && Hash::check($value, $user->action_password)) {
                            $fail('The new login password must be different from the current Manager Override Password.');
                        }
                    },
                ]
            ),
            'role'      => ['required', Rule::in(self::ALLOWED_ROLES)],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($isPrivileged) {
            $actionPasswordRules = [
                $needsOverridePassword ? 'required' : 'nullable',
                'string',
                'confirmed',
                'min:6',
                'max:32',
                'regex:/^[\x21-\x7E]+$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $user) {
                    if (empty($value)) {
                        return;
                    }
                    if ($request->filled('password') && $value === $request->input('password')) {
                        $fail('Manager Override Password must be different from the login password.');
                    } elseif (!$request->filled('password') && Hash::check($value, $user->password)) {
                        $fail('Manager Override Password must be different from the current login password.');
                    }
                },
            ];
            $rules['action_password'] = $actionPasswordRules;
        } else {
            $rules['action_password'] = ['nullable', 'string'];
        }

        $messages = [
            'firstname.regex' => 'First name may only contain letters, spaces, periods, hyphens, and apostrophes (no numbers or emojis).',
            'lastname.regex'  => 'Last name may only contain letters, spaces, periods, hyphens, and apostrophes (no numbers or emojis).',
            'suffix.in'       => 'Please select a valid suffix from the list.',
            'username.regex'  => 'Username must start with a letter or number and contain only lowercase letters, numbers, dots, underscores, or hyphens (no spaces).',
            'password.regex'  => 'Password may only contain standard letters, numbers, and keyboard symbols (no spaces or emojis).',
            'action_password.required'  => 'Manager Override Password is required when promoting a user to Manager or Admin.',
            'action_password.regex'     => 'Manager Override Password may only contain standard letters, numbers, and keyboard symbols (no spaces or emojis).',
            'action_password.confirmed' => 'Manager Override Password confirmation does not match.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $oldValues = $user->only(['firstname', 'lastname', 'suffix', 'username', 'email', 'role', 'is_active']);
            $hadActionPassword = $user->has_action_password;

            $user->firstname = $data['firstname'];
            $user->lastname = $data['lastname'];
            $user->suffix = $data['suffix'] ?? null;
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->role = $targetRole;
            $user->is_active = $targetActive;

            $passwordChanged = false;
            $actionPasswordChanged = false;
            $actionPasswordCleared = false;

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $passwordChanged = true;

                // Force password change on next login when an admin resets another user's password
                if (!$isSelf) {
                    $user->must_change_password = true;
                }
            }

            if (!$isPrivileged) {
                if ($hadActionPassword) {
                    $user->action_password = null;
                    $actionPasswordCleared = true;
                }
            } elseif (!empty($data['action_password'])) {
                $user->action_password = Hash::make($data['action_password']);
                $actionPasswordChanged = true;
            }

            $user->save();

            // Force logout immediately if account was deactivated
            if (!$targetActive) {
                try {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                } catch (\Throwable $e) {
                    Log::warning('Session purge skipped: ' . $e->getMessage());
                }
            }

            try {
                $authUser = Auth::user();
                $newValues = $user->only(['firstname', 'lastname', 'suffix', 'username', 'email', 'role', 'is_active']);
                if ($passwordChanged) {
                    $newValues['password_changed'] = true;
                    $newValues['must_change_password'] = $user->must_change_password;
                }
                if ($actionPasswordChanged) {
                    $newValues['action_password_changed'] = true;
                }
                if ($actionPasswordCleared) {
                    $newValues['action_password_cleared'] = true;
                }

                AuditLog::create([
                    'user_id' => $authUser->id,
                    'actor_name' => $authUser->firstname . ' ' . $authUser->lastname,
                    'actor_role' => $authUser->role,
                    'event' => 'updated',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            $message = 'User updated successfully.';
            if ($passwordChanged && !$isSelf) {
                $message .= ' They will be prompted to set a new password on their next login.';
            }

            return redirect()->route('admin.users.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Database Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === 'admin' && $user->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Cannot delete the last active System Administrator account.');
            }
        }

        try {
            $oldValues = $user->only(['firstname', 'lastname', 'username', 'email', 'role']);

            // Snapshot the auth user BEFORE the deletion happens
            $authUser = Auth::user();
            $actorName = $authUser->firstname . ' ' . $authUser->lastname;
            $actorRole = $authUser->role;

            // Purge any active sessions for the user before deletion
            try {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            } catch (\Throwable $e) {}

            // Attempt to delete the user
            $user->delete();

            // If deletion succeeds, log it
            try {
                AuditLog::create([
                    'user_id' => $authUser->id,
                    'actor_name' => $actorName,
                    'actor_role' => $actorRole,
                    'event' => 'deleted',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'old_values' => $oldValues,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');

        } catch (QueryException $e) {
            // This catches Database Foreign Key Constraint errors
            $errorCode = $e->errorInfo[1] ?? null;
            if ($errorCode == 1451) {
                return back()->with('error', 'Cannot delete this user because they are linked to historical records (Transactions, Purchase Orders, or Logs). Please deactivate their account instead to preserve the audit trail.');
            }
            return back()->with('error', 'Database Error: ' . $e->getMessage());

        } catch (\Exception $e) {
            return back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    public function showForcePasswordChange()
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return redirect()->route($this->landingRouteFor($user));
        }

        return view('auth.force-change-password', ['user' => $user]);
    }

    public function updateForcePasswordChange(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return redirect()->route($this->landingRouteFor($user));
        }

        $data = $request->validate([
            'current_password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current temporary password is incorrect.');
                    }
                },
            ],
            'password' => array_merge(
                ['required'],
                $this->strongPasswordRules($user->username ?? '', $user->email ?? ''),
                [
                    function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                        if (Hash::check($value, $user->password)) {
                            $fail('Your new password must be different from your temporary password.');
                        }
                        if ($user->action_password && Hash::check($value, $user->action_password)) {
                            $fail('Your new login password must be different from your Manager Override Password.');
                        }
                    },
                ]
            ),
        ], [
            'password.regex' => 'Password may only contain standard letters, numbers, and keyboard symbols (no spaces or emojis).',
        ]);

        $user->password = Hash::make($data['password']);
        $user->must_change_password = false;
        $user->save();

        $request->session()->regenerate();

        try {
            AuditLog::create([
                'user_id' => $user->id,
                'actor_name' => $user->firstname . ' ' . $user->lastname,
                'actor_role' => $user->role,
                'event' => 'password_changed',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'new_values' => ['first_login_password_change' => true],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Audit Log Failed: ' . $e->getMessage());
        }

        return redirect()->route($this->landingRouteFor($user))
            ->with('success', 'Password updated successfully! Welcome to Hydrotech Predictive.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            try {
                AuditLog::create([
                    'user_id' => $user->id,
                    'actor_name' => $user->firstname . ' ' . $user->lastname,
                    'actor_role' => $user->role,
                    'event' => 'logout',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'loginname'     => ['required'],
            'loginpassword' => ['required'],
        ]);

        $loginIdentifier = strtolower(trim($data['loginname']));
        $field = filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $loginIdentifier)->first();

        if (!$user) {
            return back()->withErrors(['loginname' => 'Invalid username/email or password'])
                        ->withInput($request->only('loginname'));
        }

        // Strict Role Separation: System Administrators must sign in via the Secret Admin Portal
        if ($user->role === 'admin') {
            try {
                AuditLog::create([
                    'user_id' => $user->id,
                    'actor_name' => $user->firstname . ' ' . $user->lastname,
                    'actor_role' => 'admin',
                    'event' => 'admin_login_blocked_public',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'attempted_identifier' => $loginIdentifier,
                        'reason' => 'Administrator sign-in attempted on public login page',
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return back()->withErrors(['loginname' => 'Invalid username/email or password'])
                        ->withInput($request->only('loginname'));
        }

        if (!$user->is_active) {
            return back()->withErrors(['loginname' => 'Your account has been deactivated. Contact an administrator.'])
                        ->withInput($request->only('loginname'));
        }

        if (Auth::attempt([$field => $user->{$field}, 'password' => $data['loginpassword']])) {
            $request->session()->regenerate();

            try {
                AuditLog::create([
                    'user_id' => $user->id,
                    'actor_name' => $user->firstname . ' ' . $user->lastname,
                    'actor_role' => $user->role,
                    'event' => 'login',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            if ($user->must_change_password) {
                return redirect()->route('password.force.edit')
                    ->with('info', 'Welcome! For your security, please set a new personal password before continuing.');
            }

            return redirect()->route($this->landingRouteFor($user));
        }

        return back()->withErrors(['loginname' => 'Invalid username/email or password'])
                    ->withInput($request->only('loginname'));
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showAdminLogin()
    {
        return view('auth.admin-portal-login');
    }

    public function adminLogin(Request $request)
    {
        $data = $request->validate([
            'loginname'     => ['required', 'string'],
            'loginpassword' => ['required', 'string'],
        ]);

        $loginIdentifier = strtolower(trim($data['loginname']));
        $throttleKey = 'admin-portal-login:' . $request->ip();
        $maxAttempts = 5;
        $decaySeconds = 900; // 15 minutes

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);

            try {
                AuditLog::create([
                    'user_id' => null,
                    'actor_name' => 'System Security',
                    'actor_role' => 'guest',
                    'event' => 'admin_portal_rate_limited',
                    'auditable_type' => User::class,
                    'auditable_id' => 0,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'attempted_identifier' => $loginIdentifier,
                        'retry_after_seconds' => $seconds,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return back()->withErrors([
                'loginname' => "Too many failed administrator login attempts. Please try again in {$minutes} minute(s).",
            ])->withInput($request->only('loginname'));
        }

        $field = filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($field, $loginIdentifier)->first();

        // Only active System Administrator accounts are permitted on this portal
        if (!$user || $user->role !== 'admin') {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $remaining = RateLimiter::remaining($throttleKey, $maxAttempts);

            try {
                AuditLog::create([
                    'user_id' => $user?->id,
                    'actor_name' => $user ? ($user->firstname . ' ' . $user->lastname) : 'Unknown',
                    'actor_role' => $user?->role ?? 'guest',
                    'event' => 'admin_portal_failed',
                    'auditable_type' => User::class,
                    'auditable_id' => $user?->id ?? 0,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'attempted_identifier' => $loginIdentifier,
                        'reason' => $user ? 'Non-admin account attempted admin portal login' : 'Unknown account',
                        'attempts_remaining' => $remaining,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return back()->withErrors(['loginname' => 'Invalid administrator credentials.'])
                        ->withInput($request->only('loginname'));
        }

        if (!Hash::check($data['loginpassword'], $user->password)) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $remaining = RateLimiter::remaining($throttleKey, $maxAttempts);

            try {
                AuditLog::create([
                    'user_id' => $user->id,
                    'actor_name' => $user->firstname . ' ' . $user->lastname,
                    'actor_role' => 'admin',
                    'event' => 'admin_portal_failed',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'attempted_identifier' => $loginIdentifier,
                        'reason' => 'Incorrect password',
                        'attempts_remaining' => $remaining,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return back()->withErrors(['loginname' => 'Invalid administrator credentials.'])
                        ->withInput($request->only('loginname'));
        }

        if (!$user->is_active) {
            return back()->withErrors(['loginname' => 'This administrator account has been deactivated.'])
                        ->withInput($request->only('loginname'));
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        try {
            AuditLog::create([
                'user_id' => $user->id,
                'actor_name' => $user->firstname . ' ' . $user->lastname,
                'actor_role' => $user->role,
                'event' => 'admin_portal_login',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'email' => $user->email,
                    'role' => $user->role,
                    'portal' => 'secret_admin_portal',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Audit Log Failed: ' . $e->getMessage());
        }

        if ($user->must_change_password) {
            return redirect()->route('password.force.edit')
                ->with('info', 'Welcome! For your security, please set a new personal password before continuing.');
        }

        return redirect()->route($this->landingRouteFor($user));
    }
}

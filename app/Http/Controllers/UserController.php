<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    private const PER_PAGE = 15;

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

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'suffix'    => ['nullable', 'string', 'max:20'],
            'username'  => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'  => ['required', 'confirmed', 'min:8', 'max:255'],
            'role'      => ['required', 'in:admin,manager,staff,warehouse_staff'],
            'is_active' => ['nullable', 'boolean'],
            'action_password' => ['nullable', 'string', 'min:4'],
        ]);

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
            $user->theme = 'light';
            
            if (!empty($data['action_password'])) {
                $user->action_password = Hash::make($data['action_password']);
            }
            
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
                    'new_values' => $user->only(['firstname', 'lastname', 'username', 'email', 'role', 'is_active']),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return redirect()->route('admin.users.index')->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Database Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'suffix'    => ['nullable', 'string', 'max:20'],
            'username'  => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'confirmed', 'min:8', 'max:255'],
            'role'      => ['required', 'in:admin,manager,staff,warehouse_staff'],
            'is_active' => ['nullable', 'boolean'],
            'action_password' => ['nullable', 'string', 'min:4'],
        ]);

        try {
            $oldValues = $user->only(['firstname', 'lastname', 'suffix', 'username', 'email', 'role', 'is_active']);
            
            $user->firstname = $data['firstname'];
            $user->lastname = $data['lastname'];
            $user->suffix = $data['suffix'] ?? null;
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->role = $data['role'];
            $user->is_active = $request->boolean('is_active');

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            if (!empty($data['action_password'])) {
                $user->action_password = Hash::make($data['action_password']);
            }

            $user->save();

            try {
                $authUser = Auth::user();
                AuditLog::create([
                    'user_id' => $authUser->id,
                    'actor_name' => $authUser->firstname . ' ' . $authUser->lastname,
                    'actor_role' => $authUser->role,
                    'event' => 'updated',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'old_values' => $oldValues,
                    'new_values' => $user->only(['firstname', 'lastname', 'suffix', 'username', 'email', 'role', 'is_active']),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                Log::error('Audit Log Failed: ' . $e->getMessage());
            }

            return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Database Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, User $user)
    {
        try {
            $oldValues = $user->only(['firstname', 'lastname', 'username', 'email', 'role']);
            
            // Snapshot the auth user BEFORE the deletion happens
            $authUser = Auth::user(); 
            $actorName = $authUser->firstname . ' ' . $authUser->lastname;
            $actorRole = $authUser->role;

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
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1451) {
                return back()->with('error', 'Cannot delete user. They are still linked to other records (like Purchase Orders, Transactions, or Logs) that have a strict database constraint. Please run the migration to make these columns nullable.');
            }
            return back()->with('error', 'Database Error: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            return back()->with('error', 'System Error: ' . $e->getMessage());
        }
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
            'loginname' => ['required'],
            'loginpassword' => ['required'],
        ]);

        $field = filter_var($data['loginname'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $data['loginname'])->first();

        if (! $user) {
            return back()->withErrors(['loginname' => 'Invalid username/email or password'])
                        ->withInput($request->only('loginname'));
        }

        if (! $user->is_active) {
            return back()->withErrors(['loginname' => 'Your account has been deactivated. Contact an administrator.'])
                        ->withInput($request->only('loginname'));
        }

        if (Auth::attempt([$field => $data['loginname'], 'password' => $data['loginpassword']])) {
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

            if ($user->role === 'warehouse_staff') {
                return redirect()->route('inventory.view');
            }

            return redirect()->route('home');
        }

        return back()->withErrors(['loginname' => 'Invalid username/email or password'])
                    ->withInput($request->only('loginname'));
    }

    public function showLogin() 
    {
        return view('auth.login');
    }
}
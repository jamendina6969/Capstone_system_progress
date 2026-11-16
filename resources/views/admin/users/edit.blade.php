@extends('layouts.default')

@section('title', 'Edit User')

@section('content')
    <div class="max-w-2xl mx-auto bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm p-8">

        <div class="flex items-center justify-between mb-6 border-b-2 border-gray-200 dark:border-[#1e2536] pb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit User</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Update account details for {{ $user->full_name }}.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name fields -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="firstname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">First Name</label>
                    <input type="text" name="firstname" id="firstname" value="{{ old('firstname', $user->firstname) }}" required
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="lastname" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Last Name</label>
                    <input type="text" name="lastname" id="lastname" value="{{ old('lastname', $user->lastname) }}" required
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="suffix" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Suffix <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="suffix" id="suffix" value="{{ old('suffix', $user->suffix) }}" placeholder="Jr., Sr., III"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>
            </div>

            <!-- Username & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>
            </div>

            <!-- Password (optional on edit) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">New Password <span class="text-gray-400 font-normal">(leave blank)</span></label>
                    <input type="password" name="password" id="password"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                </div>
            </div>

            <!-- NEW: Manager Override Password -->
            <div>
                <label for="action_password" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Manager Override Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
                <input type="password" name="action_password" id="action_password" placeholder="Enter new override password"
                       class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                <p class="text-[10px] text-gray-500 mt-1">Used for high-level actions like confirming stock receipt.</p>
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role</label>
                    <select name="role" id="role" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                        <option value="warehouse_staff" {{ old('role', $user->role) === 'warehouse_staff' ? 'selected' : '' }}>Warehouse Staff</option>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 mt-6 sm:mt-7">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-gray-800 w-4 h-4 cursor-pointer">
                    <label for="is_active" class="text-sm font-semibold text-gray-700 dark:text-slate-300 cursor-pointer">Active account</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <a href="{{ route('admin.users.index') }}"
                   class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Save Changes
                </button>
            </div>

        </form>
    </div>
@endsection
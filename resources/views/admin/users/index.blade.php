@extends('layouts.default')

@section('title', 'Users Management')

@section('content')

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">User Management</h1>

    {{-- REMOVED: Flash messages (They are now handled globally in layouts/default.blade.php) --}}

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-140px)] min-h-[500px] mb-4">

        <!-- Header -->
        <div class="shrink-0 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">System Users</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage admin, manager, warehouse staff and staff accounts</p>
            </div>

            <button type="button" onclick="openAddUserModal()"
               class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer inline-flex items-center">
                + Add User
            </button>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full min-w-[760px] text-sm text-left relative">
                <thead id="usersTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Name</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Username</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Email</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Role</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] whitespace-nowrap">Status</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('admin.users.partials.rows', ['users' => $users])
                </tbody>
            </table>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more users...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more users. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-4 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($users->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $users->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $users->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $users->total() }}</span>
                    users
                @else
                    No users found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="User pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        @if ($page == $users->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- Add User Popup Modal -->
    <div id="addUserModal" class="fixed inset-0 z-50 {{ ($errors->any() && old('modal_type') === 'add') ? '' : 'hidden' }} overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New User</h3>
                <button type="button" onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="modal_type" value="add">

                @if ($errors->any() && old('modal_type') === 'add')
                    <div class="rounded-xl bg-red-100 text-red-700 border-2 border-red-200 px-4 py-3 text-sm font-medium mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">First Name</label>
                        <input type="text" name="firstname" value="{{ old('firstname') }}" required
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Last Name</label>
                        <input type="text" name="lastname" value="{{ old('lastname') }}" required
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Suffix <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., Sr., III"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Password</label>
                        <input type="password" name="password" required minlength="8" maxlength="255"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400"
                               placeholder="Minimum 8 characters">
                        <p class="text-[10px] text-gray-500 mt-1">Must be at least 8 characters.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required minlength="8" maxlength="255"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400"
                               placeholder="Repeat password">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Manager Override Password <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="password" name="action_password" placeholder="e.g. admin123"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    <p class="text-[10px] text-gray-500 mt-1">Used for high-level actions like confirming stock receipt.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role</label>
                        <select name="role" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                            <option value="warehouse_staff" {{ old('role') === 'warehouse_staff' ? 'selected' : '' }}>Warehouse Staff</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 mt-6 sm:mt-7">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-gray-800 w-4 h-4">
                        <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Active account</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeAddUserModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Popup Modal -->
    <div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit User</h3>
                <button type="button" onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="editUserForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="modal_type" value="edit">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">First Name</label>
                        <input type="text" name="firstname" id="edit_firstname"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Last Name</label>
                        <input type="text" name="lastname" id="edit_lastname"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Suffix <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="suffix" id="edit_suffix" placeholder="Jr., Sr., III"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Username</label>
                        <input type="text" name="username" id="edit_username"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email</label>
                        <input type="email" name="email" id="edit_email"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">New Password <span class="text-gray-400 font-normal">(leave blank)</span></label>
                        <input type="password" name="password" minlength="8" maxlength="255"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" minlength="8" maxlength="255"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Manager Override Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
                    <input type="password" name="action_password" placeholder="Enter new override password"
                           class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                    <p class="text-[10px] text-gray-500 mt-1">Used for high-level actions like confirming stock receipt.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role</label>
                        <select name="role" id="edit_role" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400 cursor-pointer">
                            <option value="warehouse_staff">Warehouse Staff</option>
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 mt-6 sm:mt-7">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-gray-800 w-4 h-4">
                        <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Active account</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditUserModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
            <div class="flex items-center gap-4 mb-6">
                <span class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete User</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Are you sure you want to delete <strong id="deleteUserName" class="text-gray-900 dark:text-white"></strong>? This action cannot be undone.</p>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                <button type="button" onclick="closeDeleteUserModal()"
                        class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out cursor-pointer">
                    Cancel
                </button>
                <form id="deleteUserForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-6 py-2.5 rounded-full transition-all duration-300 ease-in-out cursor-pointer shadow-xs">
                        Yes, Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div id="userDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeUserDetailModal()">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-6 sm:p-8 text-left" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-200 dark:border-[#1e2536]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] flex items-center justify-center font-bold text-gray-900 dark:text-white" id="udmInitial">
                        U
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="udmFullName">User Details</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5" id="udmUsername">@username</p>
                    </div>
                </div>
                <button type="button" onclick="closeUserDetailModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Email Address</span>
                    <span id="udmEmail" class="font-medium text-gray-900 dark:text-white"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">System Role</span>
                    <span id="udmRole" class="font-bold"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Account Status</span>
                    <span id="udmStatus" class="font-bold"></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-[#1e2536]">
                    <span class="text-gray-500 dark:text-slate-400">Created Date</span>
                    <span id="udmCreatedAt" class="text-xs text-gray-600 dark:text-slate-400"></span>
                </div>
            </div>
            <div class="flex items-center justify-between pt-5 border-t-2 border-gray-200 dark:border-[#1e2536] mt-4">
                <button type="button" id="udmEditBtn" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                    Edit User
                </button>
                <button type="button" onclick="closeUserDetailModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs sm:text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer">Close</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #334155; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // =====================================================
    // INFINITE SCROLL
    // =====================================================
    const tableContainer = document.getElementById('tableScrollContainer');
    const tableBody = document.getElementById('tableBody');
    const tableHead = document.getElementById('usersTableHead');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadError = document.getElementById('loadError');
    const retryLoadButton = document.getElementById('retryLoadButton');
    const paginationContainer = document.getElementById('paginationNumbersContainer');
    const resultsText = document.getElementById('resultsText');

    let nextPageUrl = @json($users->nextPageUrl());
    let currentPage = {{ $users->currentPage() }};
    let lastPage = {{ $users->lastPage() }};
    let totalRecords = {{ $users->total() }};
    let perPage = {{ $users->perPage() }};
    let initialPage = {{ $users->currentPage() }};
    let initialStart = {{ $users->firstItem() ?? 0 }} || 0;

    let isLoading = false;
    let visualPage = initialPage;

    function updateResultsCounter() {
        const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
        if (totalRecords === 0 || rowsLoaded === 0) { resultsText.textContent = 'No users found.'; return; }
        const firstRecord = initialStart;
        const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
        resultsText.innerHTML = `Showing <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span> to <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span> of <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span> users`;
    }

    function getVisiblePage() {
        const rows = tableBody.querySelectorAll('tr[data-record-row="true"]');
        if (!rows.length) return initialPage;

        const isAtBottom = (tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight) <= 5;
        if (isAtBottom && lastPage > 0) return lastPage;

        const containerRect = tableContainer.getBoundingClientRect();
        const headerHeight = tableHead ? tableHead.getBoundingClientRect().height : 0;
        const visibleTop = containerRect.top + headerHeight;
        const visibleBottom = containerRect.bottom;

        let firstVisibleIndex = -1;
        rows.forEach((row, index) => {
            const rect = row.getBoundingClientRect();
            if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) firstVisibleIndex = index;
        });

        if (firstVisibleIndex === -1) firstVisibleIndex = rows.length - 1;
        const pageOffset = Math.floor(firstVisibleIndex / perPage);
        return Math.min(lastPage, Math.max(1, initialPage + pageOffset));
    }

    function updatePaginationNumbers(pageToShow) {
        const page = Math.min(lastPage, Math.max(1, pageToShow));
        if (visualPage === page && paginationContainer.children.length > 0) return;
        visualPage = page;
        let html = '';
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(lastPage, page + 2);
        for (let i = startPage; i <= endPage; i++) {
            if (i === page) html += `<span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
            else html += `<button type="button" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</button>`;
        }
        paginationContainer.innerHTML = html;
    }

    async function loadMoreRecords() {
        if (!nextPageUrl || isLoading) return;
        isLoading = true;
        loadError.classList.add('hidden');
        loadingIndicator.classList.remove('hidden');

        try {
            const fetchUrl = new URL(nextPageUrl, window.location.origin);
            fetchUrl.searchParams.set('t', Date.now());
            const response = await fetch(fetchUrl.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Cache-Control': 'no-cache' } });
            if (!response.ok) throw new Error('Request failed.');
            const data = await response.json();

            const newPage = Number(data.current_page);
            if (data.html) {
                const temp = document.createElement('tbody');
                temp.innerHTML = data.html;
                tableBody.querySelector('[data-empty-row="true"]')?.remove();
                Array.from(temp.children).forEach(row => {
                    if (row.dataset.recordRow === 'true') tableBody.appendChild(row);
                });
            }

            nextPageUrl = data.next_page_url;
            currentPage = newPage;
            lastPage = data.last_page;
            totalRecords = data.total_records;
            perPage = data.per_page;
            updateResultsCounter();
            updatePaginationNumbers(getVisiblePage());
        } catch (error) {
            console.error('Error loading users:', error);
            loadError.classList.remove('hidden');
        } finally {
            isLoading = false;
            loadingIndicator.classList.add('hidden');
        }
    }

    tableContainer.addEventListener('scroll', function () {
        updatePaginationNumbers(getVisiblePage());
        const distanceFromBottom = tableContainer.scrollHeight - tableContainer.scrollTop - tableContainer.clientHeight;
        if (distanceFromBottom < 150 && nextPageUrl && !isLoading) loadMoreRecords();
    }, { passive: true });

    retryLoadButton?.addEventListener('click', () => loadMoreRecords());

    // Init counters
    updateResultsCounter();
    updatePaginationNumbers(initialPage);
});

// =====================================================
// MODAL FUNCTIONS (Global)
// =====================================================
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

let currentUserDetailData = null;
function openUserDetailModal(userJson) {
    const user = typeof userJson === 'string' ? JSON.parse(userJson) : userJson;
    currentUserDetailData = user;
    const name = user.full_name || (user.firstname + ' ' + user.lastname);
    document.getElementById('udmFullName').textContent = name;
    document.getElementById('udmInitial').textContent = (name.charAt(0) || 'U').toUpperCase();
    document.getElementById('udmUsername').textContent = '@' + user.username;
    document.getElementById('udmEmail').textContent = user.email || '—';
    document.getElementById('udmRole').textContent = (user.role || '').replace('_', ' ').toUpperCase();
    document.getElementById('udmStatus').innerHTML = user.is_active ? '<span class="text-emerald-600 dark:text-emerald-400">Active</span>' : '<span class="text-rose-600 dark:text-rose-400">Inactive</span>';
    document.getElementById('udmCreatedAt').textContent = user.created_at ? new Date(user.created_at).toLocaleDateString() : '—';
    
    document.getElementById('udmEditBtn').onclick = function() {
        closeUserDetailModal();
        openEditUserModal(currentUserDetailData);
    };

    const modal = document.getElementById('userDetailModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeUserDetailModal() {
    const modal = document.getElementById('userDetailModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeUserDetailModal();
});

function openEditUserModal(userJson) {
    const user = typeof userJson === 'string' ? JSON.parse(userJson) : userJson;
    if (!user) return;

    const form = document.getElementById('editUserForm');
    form.action = `/admin/users/${user.id}`;

    document.getElementById('edit_firstname').value = user.firstname || '';
    document.getElementById('edit_lastname').value = user.lastname || '';
    document.getElementById('edit_suffix').value = user.suffix || '';
    document.getElementById('edit_username').value = user.username || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_role').value = user.role || 'staff';
    document.getElementById('edit_is_active').checked = user.is_active == 1;

    document.getElementById('editUserModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openDeleteUserModal(userId, userName) {
    const form = document.getElementById('deleteUserForm');
    form.action = `/admin/users/${userId}`;
    document.getElementById('deleteUserName').innerText = userName;

    document.getElementById('deleteUserModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeDeleteUserModal() {
    document.getElementById('deleteUserModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Auto-open modal if validation failed
@if ($errors->any() && old('modal_type') === 'add')
    document.addEventListener('DOMContentLoaded', function () {
        openAddUserModal();
    });
@endif
</script>
@endpush
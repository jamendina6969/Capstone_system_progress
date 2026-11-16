@extends('layouts.default')

@section('title', 'Suppliers')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Suppliers Directory</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage vendors, track balances, and record payments</p>
        </div>
        <button type="button" onclick="toggleAddModal(true)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
            + Add Supplier
        </button>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if (session('error'))
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-medium text-rose-700 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-400">
            <p class="font-bold mb-1">The operation could not be completed:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-[calc(100vh-180px)] mb-4">
        
        <!-- Filter Section -->
        <div class="shrink-0 px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536] bg-gray-50/50 dark:bg-[#161B26]/50">
            <form id="filterForm" method="GET" action="{{ route('suppliers.index') }}" class="flex flex-col xl:flex-row xl:items-center gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search supplier name or contact..." autocomplete="off"
                               class="w-full rounded-xl bg-white dark:bg-[#161B26] border border-gray-300 dark:border-[#1e2536] pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-slate-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>
                </div>
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-sm text-left relative">
                <thead id="supplierTableHead" class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Supplier Name</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Contact Person</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Contact Info</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Outstanding Balance</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @include('suppliers.partials.rows', ['suppliers' => $suppliers])
                </tbody>
            </table>

            {{-- LOADING --}}
            <div id="loadingIndicator" class="hidden py-8 text-center text-gray-500 dark:text-slate-400 font-semibold text-sm">
                <svg class="animate-spin h-6 w-6 mx-auto text-slate-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Loading more suppliers...
            </div>

            {{-- ERROR --}}
            <div id="loadError" class="hidden py-4 text-center text-rose-500 text-sm">
                Unable to load more suppliers. <button type="button" id="retryLoadButton" class="underline font-semibold ml-1">Try again</button>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="shrink-0 px-8 py-5 border-t-2 border-gray-200 dark:border-[#1e2536] flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-slate-400 bg-white dark:bg-[#10141D]">
            <div class="whitespace-nowrap" id="resultsText">
                @if($suppliers->total() > 0)
                    Showing
                    <span class="font-semibold text-gray-900 dark:text-white" id="firstRecordNumber">{{ $suppliers->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900 dark:text-white" id="lastRecordNumber">{{ $suppliers->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalRecordNumber">{{ $suppliers->total() }}</span>
                    suppliers
                @else
                    No suppliers found
                @endif
            </div>

            <nav class="flex items-center gap-1.5 max-w-full" aria-label="Supplier pagination">
                <div id="paginationNumbersContainer" class="overflow-x-auto no-scrollbar flex items-center gap-1.5 px-1 py-1">
                    @foreach ($suppliers->getUrlRange(max(1, $suppliers->currentPage() - 2), min($suppliers->lastPage(), $suppliers->currentPage() + 2)) as $page => $url)
                        @if ($page == $suppliers->currentPage())
                            <span data-page="{{ $page }}" aria-current="page" class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">{{ $page }}</span>
                        @else
                            <button type="button" data-page="{{ $page }}" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    <!-- Floating Dropdown Container -->
    <div id="floatingActionMenu" class="hidden fixed z-[100] w-44 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-xl overflow-hidden">
        <div class="py-1">
            <button type="button" id="menuRateBtn" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 text-left transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                <span>Rate Supplier</span>
            </button>

            <button type="button" id="menuEditBtn" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#1e2536] text-left transition-colors border-t border-gray-100 dark:border-[#1e2536]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Supplier
            </button>

            <button type="button" id="menuDeleteBtn" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-left transition-colors border-t border-gray-100 dark:border-[#1e2536]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Delete Supplier
            </button>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- Add Supplier Modal -->
    <div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Supplier</h3>
                <button type="button" onclick="toggleAddModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Contact Person <span class="text-rose-500">*</span></label>
                    <input type="text" name="contact_person" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="vendor@example.com" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500">
                    </div>

                    <div class="relative" id="addPhoneWrapper">
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Phone <span class="text-rose-500">*</span></label>

                        <input type="hidden" name="country_code" id="addHiddenCountryCode" value="+63" required>

                        <div class="flex border-2 border-gray-200 dark:border-[#1e2536] rounded-xl overflow-hidden focus-within:border-slate-400 dark:focus-within:border-slate-500 bg-white dark:bg-[#161B26]">
                            <button type="button" onclick="toggleCountryDropdown('add')" class="flex items-center gap-1.5 px-3 py-2.5 bg-gray-50 dark:bg-[#1e2536] border-r-2 border-gray-200 dark:border-[#1e2536] hover:bg-gray-100 dark:hover:bg-[#2a344a] transition-colors focus:outline-none">
                                <img id="addSelectedFlag" src="https://flagcdn.com/ph.svg" alt="PH" class="w-5 h-3.5 object-cover rounded-sm shadow-sm">
                                <span id="addSelectedDial" class="text-sm font-semibold text-gray-700 dark:text-slate-300">+63</span>
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <input type="number" name="phone" required placeholder="9123456789" class="flex-1 w-full bg-transparent px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>

                        <div id="addCountryDropdownMenu" class="hidden absolute z-[110] top-full left-0 mt-1 w-72 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-xl overflow-hidden">
                            <div class="p-2 border-b border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26]">
                                <input type="text" id="addCountrySearch" onkeyup="filterCountries('add')" placeholder="Search country..." class="w-full bg-white dark:bg-[#10141D] border border-gray-300 dark:border-[#2a344a] rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:outline-none">
                            </div>
                            <ul id="addCountryList" class="max-h-60 overflow-y-auto text-sm text-gray-700 dark:text-slate-300"></ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Address <span class="text-rose-500">*</span></label>
                    <textarea name="address" rows="3" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="toggleAddModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Supplier</h3>
                <button type="button" onclick="toggleEditModal(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Contact Person <span class="text-rose-500">*</span></label>
                    <input type="text" name="contact_person" id="edit_contact_person" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" id="edit_email" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                    </div>

                    <div class="relative" id="editPhoneWrapper">
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Phone <span class="text-rose-500">*</span></label>

                        <input type="hidden" name="country_code" id="editHiddenCountryCode" required>

                        <div class="flex border-2 border-gray-200 dark:border-[#1e2536] rounded-xl overflow-hidden focus-within:border-slate-400 dark:focus-within:border-slate-500 bg-white dark:bg-[#161B26]">
                            <button type="button" onclick="toggleCountryDropdown('edit')" class="flex items-center gap-1.5 px-3 py-2.5 bg-gray-50 dark:bg-[#1e2536] border-r-2 border-gray-200 dark:border-[#1e2536] hover:bg-gray-100 dark:hover:bg-[#2a344a] transition-colors focus:outline-none">
                                <img id="editSelectedFlag" src="" alt="Flag" class="w-5 h-3.5 object-cover rounded-sm shadow-sm">
                                <span id="editSelectedDial" class="text-sm font-semibold text-gray-700 dark:text-slate-300"></span>
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <input type="number" name="phone" id="edit_phone" required class="flex-1 w-full bg-transparent px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>

                        <div id="editCountryDropdownMenu" class="hidden absolute z-[110] top-full left-0 mt-1 w-72 bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-xl shadow-xl overflow-hidden">
                            <div class="p-2 border-b border-gray-200 dark:border-[#1e2536] bg-gray-50 dark:bg-[#161B26]">
                                <input type="text" id="editCountrySearch" onkeyup="filterCountries('edit')" placeholder="Search country..." class="w-full bg-white dark:bg-[#10141D] border border-gray-300 dark:border-[#2a344a] rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:outline-none">
                            </div>
                            <ul id="editCountryList" class="max-h-60 overflow-y-auto text-sm text-gray-700 dark:text-slate-300"></ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Address <span class="text-rose-500">*</span></label>
                    <textarea name="address" id="edit_address" rows="3" required class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="toggleEditModal(false)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30 mb-4">
                <svg class="h-8 w-8 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Delete Supplier</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
                Are you sure you want to delete <strong id="deleteSupplierName" class="text-gray-900 dark:text-white"></strong>? This action cannot be undone.
            </p>

            <div class="flex justify-center gap-3">
                <button type="button" onclick="toggleDeleteModal(false)" class="w-full bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    Cancel
                </button>
                <button type="button" onclick="submitDelete()" class="w-full bg-white border-2 border-rose-600 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Record Payment</h3>
                <button type="button" onclick="togglePaymentModal(null)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <p class="text-sm mb-4">Supplier: <strong id="paymentSupplierName" class="text-gray-900 dark:text-white"></strong></p>
            <p class="text-sm mb-6">Current Balance: <strong id="paymentSupplierBalance" class="text-gray-900 dark:text-white"></strong></p>

            <form id="paymentForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Amount Tendered (₱)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="paymentAmountInput" required oninput="calculateChange()" placeholder="Enter payment amount" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                </div>

                <div class="bg-gray-50 dark:bg-[#161B26]/50 p-3 rounded-xl border border-gray-200 dark:border-[#1e2536]">
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Sukli / Change (Auto-calculated)</label>
                    <input type="hidden" name="change_amount" id="paymentChangeInput" value="0">
                    <div id="paymentChangeDisplay" class="text-lg font-bold text-gray-900 dark:text-white">
                        ₱0.00
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Note / Ref #</label>
                    <input type="text" name="description" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Upload Receipt (Optional)</label>
                    <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-100 dark:file:bg-[#1e2536] file:text-gray-700 dark:file:text-slate-300 hover:file:bg-gray-200 dark:hover:file:bg-[#2a344a] transition-all">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="togglePaymentModal(null)" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Supplier Rating Modal -->
    <div id="rateModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Rate Supplier</h3>
                <button type="button" onclick="closeRateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <p class="text-sm mb-4">Rating for: <strong id="rateSupplierName" class="text-gray-900 dark:text-white"></strong></p>

            <form id="rateForm" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="score" id="scoreInput" value="5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Score (1-5 Stars)</label>
                    <div class="flex gap-2 text-3xl cursor-pointer" id="starContainer">
                        @for($i = 1; $i <= 5; $i++)
                            <span data-score="{{ $i }}" class="text-amber-400 transition-colors">★</span>
                        @endfor
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Comment (Optional)</label>
                    <textarea name="comment" rows="2" class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-2.5 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeRateModal()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Cancel</button>
                    <button type="submit" class="bg-white border-2 border-amber-500 text-amber-600 hover:bg-amber-500 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-amber-500/80 dark:text-amber-400 dark:hover:bg-amber-500 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">Submit Rating</button>
                </div>
            </form>
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
    // =====================================================
    // COUNTRY CALLING CODE DATA
    // =====================================================
    const countries = [
        { name: "Philippines", code: "ph", dial: "+63" }, { name: "United States", code: "us", dial: "+1" },
        { name: "United Kingdom", code: "gb", dial: "+44" }, { name: "Australia", code: "au", dial: "+61" },
        { name: "Canada", code: "ca", dial: "+1" }, { name: "China", code: "cn", dial: "+86" },
        { name: "India", code: "in", dial: "+91" }, { name: "Japan", code: "jp", dial: "+81" },
        { name: "South Korea", code: "kr", dial: "+82" }, { name: "Singapore", code: "sg", dial: "+65" },
        { name: "Malaysia", code: "my", dial: "+60" }, { name: "Indonesia", code: "id", dial: "+62" },
        { name: "Thailand", code: "th", dial: "+66" }, { name: "Vietnam", code: "vn", dial: "+84" },
        { name: "United Arab Emirates", code: "ae", dial: "+971" }, { name: "Saudi Arabia", code: "sa", dial: "+966" },
        { name: "Germany", code: "de", dial: "+49" }, { name: "France", code: "fr", dial: "+33" },
        { name: "Italy", code: "it", dial: "+39" }, { name: "Spain", code: "es", dial: "+34" },
        { name: "Netherlands", code: "nl", dial: "+31" }, { name: "Brazil", code: "br", dial: "+55" },
        { name: "Mexico", code: "mx", dial: "+52" }, { name: "South Africa", code: "za", dial: "+27" },
        { name: "New Zealand", code: "nz", dial: "+64" }, { name: "Hong Kong", code: "hk", dial: "+852" },
        { name: "Taiwan", code: "tw", dial: "+886" }, { name: "Switzerland", code: "ch", dial: "+41" },
        { name: "Sweden", code: "se", dial: "+46" }, { name: "Norway", code: "no", dial: "+47" },
        { name: "Denmark", code: "dk", dial: "+45" }, { name: "Finland", code: "fi", dial: "+358" },
        { name: "Russia", code: "ru", dial: "+7" }, { name: "Turkey", code: "tr", dial: "+90" },
        { name: "Egypt", code: "eg", dial: "+20" }, { name: "Nigeria", code: "ng", dial: "+234" },
        { name: "Kenya", code: "ke", dial: "+254" }, { name: "Argentina", code: "ar", dial: "+54" },
        { name: "Chile", code: "cl", dial: "+56" }, { name: "Colombia", code: "co", dial: "+57" },
        { name: "Peru", code: "pe", dial: "+51" }, { name: "Portugal", code: "pt", dial: "+351" },
        { name: "Greece", code: "gr", dial: "+30" }, { name: "Ireland", code: "ie", dial: "+353" },
        { name: "Austria", code: "at", dial: "+43" }, { name: "Belgium", code: "be", dial: "+32" }
    ].sort((a, b) => a.name.localeCompare(b.name));

    let addSelectedCountry = countries.find(c => c.code === "ph");
    let editSelectedCountry = countries.find(c => c.code === "ph");
    let currentPaymentBalance = 0;

    function getFlagUrl(countryCode) {
        return `https://flagcdn.com/${countryCode.toLowerCase()}.svg`;
    }

    function renderCountryList(type, filterText = "") {
        const listEl = document.getElementById(`${type}CountryList`);
        if (!listEl) return;
        listEl.innerHTML = "";

        const currentSelection = type === 'add' ? addSelectedCountry : editSelectedCountry;
        const filtered = countries.filter(c => c.name.toLowerCase().includes(filterText.toLowerCase()) || c.dial.includes(filterText));

        if (filtered.length === 0) {
            listEl.innerHTML = `<li class="px-4 py-3 text-center text-gray-500 text-xs">No countries found</li>`;
            return;
        }

        filtered.forEach(country => {
            const li = document.createElement('li');
            const isSelected = currentSelection.code === country.code;
            li.className = `flex items-center justify-between px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors ${isSelected ? 'bg-blue-50 dark:bg-blue-900/20 font-semibold' : ''}`;
            li.innerHTML = `
                <div class="flex items-center gap-2">
                    <img src="${getFlagUrl(country.code)}" alt="${country.code}" class="w-5 h-3.5 object-cover rounded-sm shadow-sm">
                    <span class="truncate w-32 text-xs">${country.name}</span>
                </div>
                <span class="text-xs text-gray-500 dark:text-slate-400">${country.dial}</span>
            `;
            li.onclick = () => selectCountry(type, country);
            listEl.appendChild(li);
        });
    }

    function toggleCountryDropdown(type) {
        const menu = document.getElementById(`${type}CountryDropdownMenu`);
        const searchInput = document.getElementById(`${type}CountrySearch`);

        if (menu.classList.contains('hidden')) {
            document.getElementById('addCountryDropdownMenu').classList.add('hidden');
            document.getElementById('editCountryDropdownMenu').classList.add('hidden');
            menu.classList.remove('hidden');
            searchInput.value = '';
            renderCountryList(type);
            searchInput.focus();
        } else {
            menu.classList.add('hidden');
        }
    }

    function selectCountry(type, country) {
        if (type === 'add') {
            addSelectedCountry = country;
            document.getElementById('addSelectedFlag').src = getFlagUrl(country.code);
            document.getElementById('addSelectedDial').textContent = country.dial;
            document.getElementById('addHiddenCountryCode').value = country.dial;
            document.querySelector('#addModal input[name="phone"]').focus();
        } else {
            editSelectedCountry = country;
            document.getElementById('editSelectedFlag').src = getFlagUrl(country.code);
            document.getElementById('editSelectedDial').textContent = country.dial;
            document.getElementById('editHiddenCountryCode').value = country.dial;
            document.querySelector('#editModal input[name="phone"]').focus();
        }
        document.getElementById(`${type}CountryDropdownMenu`).classList.add('hidden');
    }

    function filterCountries(type) {
        const searchTerm = document.getElementById(`${type}CountrySearch`).value;
        renderCountryList(type, searchTerm);
    }

    document.addEventListener('click', function(e) {
        const addWrapper = document.getElementById('addPhoneWrapper');
        const editWrapper = document.getElementById('editPhoneWrapper');

        if (addWrapper && !addWrapper.contains(e.target)) document.getElementById('addCountryDropdownMenu').classList.add('hidden');
        if (editWrapper && !editWrapper.contains(e.target)) document.getElementById('editCountryDropdownMenu').classList.add('hidden');

        const actionMenu = document.getElementById('floatingActionMenu');
        if (actionMenu && !e.target.closest('button[onclick^="openActionMenu"]') && !actionMenu.contains(e.target)) {
            actionMenu.classList.add('hidden');
        }
    });

    // =====================================================
    // INFINITE SCROLL SETUP
    // =====================================================
    document.addEventListener('DOMContentLoaded', function () {
        const tableContainer = document.getElementById('tableScrollContainer');
        const tableBody = document.getElementById('tableBody');
        const tableHead = document.getElementById('supplierTableHead');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const loadError = document.getElementById('loadError');
        const retryLoadButton = document.getElementById('retryLoadButton');
        const paginationContainer = document.getElementById('paginationNumbersContainer');
        const resultsText = document.getElementById('resultsText');

        let nextPageUrl = @json($suppliers->nextPageUrl());
        let currentPage = {{ $suppliers->currentPage() }};
        let lastPage = {{ $suppliers->lastPage() }};
        let totalRecords = {{ $suppliers->total() }};
        let perPage = {{ $suppliers->perPage() }};
        let initialPage = {{ $suppliers->currentPage() }};
        let initialStart = {{ $suppliers->firstItem() ?? 0 }} || 0;

        let isLoading = false;
        let visualPage = initialPage;

        function updateResultsCounter() {
            const rowsLoaded = tableBody.querySelectorAll('tr[data-record-row="true"]').length;
            if (totalRecords === 0 || rowsLoaded === 0) {
                resultsText.textContent = 'No suppliers found.';
                return;
            }
            const firstRecord = initialStart;
            const lastRecord = Math.min(firstRecord + rowsLoaded - 1, totalRecords);
            resultsText.innerHTML = `
                Showing
                <span class="font-semibold text-gray-900 dark:text-white">${firstRecord}</span>
                to
                <span class="font-semibold text-gray-900 dark:text-white">${lastRecord}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-white">${totalRecords}</span>
                suppliers
            `;
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
                if (firstVisibleIndex === -1 && rect.bottom > visibleTop && rect.top < visibleBottom) {
                    firstVisibleIndex = index;
                }
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
                if (i === page) {
                    html += `<span class="shrink-0 px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-[#1E293B] rounded-full shadow-xs">${i}</span>`;
                } else {
                    html += `<button type="button" class="shrink-0 px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">${i}</button>`;
                }
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

                const response = await fetch(fetchUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache'
                    }
                });

                if (!response.ok) throw new Error('Request failed.');
                const data = await response.json();

                const newPage = Number(data.current_page);
                if (data.html) {
                    const temp = document.createElement('tbody');
                    temp.innerHTML = data.html;
                    tableBody.querySelector('[data-empty-row="true"]')?.remove();
                    Array.from(temp.children).forEach(row => {
                        if (row.dataset.recordRow === 'true') {
                            row.dataset.productPage = newPage;
                            tableBody.appendChild(row);
                        }
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
                console.error('Error loading suppliers:', error);
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

        retryLoadButton?.addEventListener('click', function () { loadMoreRecords(); });

        // Live search
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let debounceTimer;
        if (searchInput && filterForm) {
            const savedCursorPos = sessionStorage.getItem('searchCursorPos');
            if (savedCursorPos !== null) {
                searchInput.focus();
                searchInput.setSelectionRange(parseInt(savedCursorPos), parseInt(savedCursorPos));
                sessionStorage.removeItem('searchCursorPos');
            } else if (searchInput.value.length > 0) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                sessionStorage.setItem('searchCursorPos', searchInput.selectionStart);
                debounceTimer = setTimeout(() => filterForm.submit(), 300);
            });
        }

        // Init counters
        updateResultsCounter();
        updatePaginationNumbers(initialPage);
    });

    // =====================================================
    // REST OF EXISTING LOGIC (Modals, Create Form, etc.)
    // =====================================================
    function openActionMenu(event, id, name, rating, supplierData) {
        event.stopPropagation();
        const menu = document.getElementById('floatingActionMenu');

        if (!menu.classList.contains('hidden') && menu.dataset.currentId == id) {
            menu.classList.add('hidden');
            return;
        }

        menu.dataset.currentId = id;
        menu.classList.remove('hidden');

        const rect = event.currentTarget.getBoundingClientRect();
        menu.style.top = `${rect.bottom + window.scrollY + 8}px`;
        menu.style.left = `${rect.right + window.scrollX - 176}px`;

        document.getElementById('menuRateBtn').onclick = () => { toggleRateModal(id, name); menu.classList.add('hidden'); };
        document.getElementById('menuEditBtn').onclick = () => { const fakeBtn = document.createElement('button'); fakeBtn.dataset.supplier = JSON.stringify(supplierData); openEditModal(fakeBtn); menu.classList.add('hidden'); };
        document.getElementById('menuDeleteBtn').onclick = () => { confirmDelete(id, name); menu.classList.add('hidden'); };
    }

    function toggleAddModal(show) {
        document.getElementById('addModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function toggleEditModal(show) {
        document.getElementById('editModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function toggleDeleteModal(show) {
        document.getElementById('deleteModal').classList.toggle('hidden', !show);
        document.body.classList.toggle('overflow-hidden', show);
    }

    function togglePaymentModal(supplierId, name = '', balance = 0) {
        const modal = document.getElementById('paymentModal');
        const form = document.getElementById('paymentForm');

        if (supplierId) {
            const actualBalance = parseFloat(balance) || 0;
            if (actualBalance <= 0) return;

            form.action = `/suppliers/${supplierId}/payment`;
            document.getElementById('paymentSupplierName').innerText = name;
            currentPaymentBalance = actualBalance;
            document.getElementById('paymentSupplierBalance').innerText = `₱${currentPaymentBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            form.reset();

            document.getElementById('paymentChangeInput').value = '0';
            document.getElementById('paymentChangeDisplay').innerText = '₱0.00';
            document.getElementById('paymentChangeDisplay').className = 'text-lg font-bold text-gray-900 dark:text-white';

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function calculateChange() {
        const amountInput = document.getElementById('paymentAmountInput');
        const changeInput = document.getElementById('paymentChangeInput');
        const display = document.getElementById('paymentChangeDisplay');

        const amountTendered = parseFloat(amountInput.value) || 0;
        const change = Math.max(0, amountTendered - currentPaymentBalance);

        changeInput.value = change.toFixed(2);
        display.innerText = `₱${change.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        display.className = 'text-lg font-bold text-gray-900 dark:text-white';
    }

    function toggleRateModal(supplierId, name) {
        document.getElementById('rateForm').action = `/suppliers/${supplierId}/rate`;
        document.getElementById('rateSupplierName').innerText = name;
        document.getElementById('scoreInput').value = 5;

        document.querySelectorAll('#starContainer span').forEach(s => {
            s.classList.add('text-amber-400');
            s.classList.remove('text-gray-300');
        });

        document.getElementById('rateModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeRateModal() {
        document.getElementById('rateModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('#starContainer span').forEach(star => {
        star.addEventListener('click', function() {
            const score = parseInt(this.dataset.score);
            document.getElementById('scoreInput').value = score;

            document.querySelectorAll('#starContainer span').forEach(s => {
                const sScore = parseInt(s.dataset.score);
                if (sScore <= score) {
                    s.classList.add('text-amber-400');
                    s.classList.remove('text-gray-300');
                } else {
                    s.classList.remove('text-amber-400');
                    s.classList.add('text-gray-300');
                }
            });
        });
    });

    function openEditModal(button) {
        const supplier = JSON.parse(button.dataset.supplier);
        document.getElementById('editForm').action = `/suppliers/${supplier.id}`;

        document.getElementById('edit_name').value = supplier.name;
        document.getElementById('edit_contact_person').value = supplier.contact_person || '';
        document.getElementById('edit_email').value = supplier.email || '';
        document.getElementById('edit_address').value = supplier.address || '';

        let countryCode = "+63";
        let localPhone = supplier.phone || "";

        if (localPhone) {
            const parts = localPhone.split(' ');
            if (parts.length > 1 && parts[0].startsWith('+')) {
                countryCode = parts[0];
                localPhone = parts.slice(1).join('');
            }
        }

        let matchedCountry = countries.find(c => c.dial === countryCode) || countries.find(c => c.code === 'ph');
        editSelectedCountry = matchedCountry;

        document.getElementById('editSelectedFlag').src = getFlagUrl(matchedCountry.code);
        document.getElementById('editSelectedDial').textContent = countryCode;
        document.getElementById('editHiddenCountryCode').value = countryCode;
        document.getElementById('edit_phone').value = localPhone;

        toggleEditModal(true);
    }

    let supplierToDeleteId = null;

    function confirmDelete(supplierId, supplierName) {
        supplierToDeleteId = supplierId;
        document.getElementById('deleteSupplierName').innerText = supplierName;
        toggleDeleteModal(true);
    }

    function submitDelete() {
        if (supplierToDeleteId) {
            const form = document.getElementById('deleteForm');
            form.action = `/suppliers/${supplierToDeleteId}`;
            form.submit();
        }
    }

    renderCountryList('add');
    renderCountryList('edit');
</script>
@endpush
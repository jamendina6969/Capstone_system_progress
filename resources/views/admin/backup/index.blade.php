@extends('layouts.default')

@section('title', 'System Backups')

@section('content')

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">System Backup & Restore</h1>

    {{-- Note: Success/Error messages are handled globally by layouts.default --}}

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-auto lg:h-[calc(100vh-180px)] lg:min-h-[480px] mb-4">
        
        <!-- Header -->
        <div class="shrink-0 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 py-5 sm:py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Backup History</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage database and image backups</p>
            </div>

            <form action="{{ route('admin.backups.create') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create Backup Now
                </button>
            </form>
        </div>

        <!-- Scrollable Table -->
        <div id="tableScrollContainer" class="flex-1 min-h-0 overflow-y-auto overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-sm text-left relative">
                <thead class="bg-gray-50 dark:bg-[#161B26] text-gray-600 dark:text-slate-400 uppercase text-xs sticky top-0 z-10 shadow-sm border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <tr>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">File Name</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Size</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26]">Date Created</th>
                        <th class="px-8 py-4 bg-gray-50 dark:bg-[#161B26] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1e2536]">
                    @forelse ($files as $file)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors cursor-pointer"
                            onclick="openBackupDetail('{{ $file->getFilename() }}', '{{ number_format($file->getSize() / 1048576, 2) }} MB', '{{ date('M d, Y h:i A', $file->getMTime()) }}', '{{ number_format($file->getSize() / 1024, 0) }} KB', '{{ date('Y-m-d H:i:s', $file->getMTime()) }}')">
                            <td class="px-8 py-4 font-medium text-gray-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                    {{ $file->getFilename() }}
                                </div>
                            </td>
                            <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">
                                {{ number_format($file->getSize() / 1048576, 2) }} MB
                            </td>
                            <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">
                                {{ date('M d, Y h:i A', $file->getMTime()) }}
                            </td>
                            <td class="px-8 py-4 text-right" onclick="event.stopPropagation()">
                                <div class="flex justify-end gap-2 items-center">

                                    <!-- View Detail -->
                                    <button type="button" onclick="openBackupDetail('{{ $file->getFilename() }}', '{{ number_format($file->getSize() / 1048576, 2) }} MB', '{{ date('M d, Y h:i A', $file->getMTime()) }}', '{{ number_format($file->getSize() / 1024, 0) }} KB', '{{ date('Y-m-d H:i:s', $file->getMTime()) }}')" class="bg-white border-2 border-slate-400 text-slate-600 hover:bg-slate-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-slate-500/80 dark:text-slate-300 dark:hover:bg-slate-600 dark:hover:text-white text-xs font-semibold px-3 py-2 rounded-xl transition-all duration-300 shadow-xs inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </button>

                                    <!-- Download -->
                                    <a href="{{ route('admin.backups.download', $file->getFilename()) }}" class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs inline-flex items-center gap-1.5">
                                        Download
                                    </a>

                                    <!-- Restore -->
                                    <button type="button" onclick="confirmRestore('{{ $file->getFilename() }}')" class="bg-white border-2 border-amber-500 text-amber-600 hover:bg-amber-500 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-amber-500/80 dark:text-amber-400 dark:hover:bg-amber-500 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs">
                                        Restore
                                    </button>

                                    <!-- Delete -->
                                    <button type="button" onclick="confirmDelete('{{ $file->getFilename() }}')" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs">
                                        Delete
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-gray-400 dark:text-slate-500 font-medium">
                                No backups found. Create your first backup to protect your data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Backup Detail Modal -->
    <div id="backupDetailModal" class="fixed inset-0 z-[100] hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="if(event.target===this) closeBackupDetail()">
        <div class="relative w-full max-w-lg bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b-2 border-gray-100 dark:border-[#1e2536]">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950/50">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Backup File Details</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400">System database & image backup</p>
                    </div>
                </div>
                <button onclick="closeBackupDetail()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-8 py-6 space-y-4">
                <!-- Filename -->
                <div class="rounded-2xl bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-1">File Name</p>
                    <p id="bd-filename" class="text-sm font-mono font-semibold text-gray-900 dark:text-white break-all"></p>
                </div>

                <!-- Size & Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-1">File Size</p>
                        <p id="bd-size" class="text-lg font-bold text-blue-600 dark:text-blue-400"></p>
                        <p id="bd-size-kb" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5"></p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-1">Date Created</p>
                        <p id="bd-date" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                </div>

                <!-- Type Badge -->
                <div class="rounded-2xl bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] p-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-2">Backup Type</p>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                            Database + Images
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Restorable
                        </span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-5 border-t-2 border-gray-100 dark:border-[#1e2536] flex items-center justify-end gap-3">
                <button onclick="closeBackupDetail()" class="border-2 border-gray-300 dark:border-[#1e2536] text-gray-700 dark:text-slate-300 text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-gray-100 dark:hover:bg-[#1e2536] transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div id="restoreModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeRestoreModal()">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 text-center" onclick="event.stopPropagation()">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 dark:bg-amber-950/50 mb-5">
                <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Restore System?</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">This will overwrite the current database and images with the backup file. <strong class="text-gray-900 dark:text-white">A safety backup will be created first.</strong> The system will be in maintenance mode during this process.</p>
            
            <form id="restoreForm" method="POST" action="{{ route('admin.backups.restore') }}">
                @csrf
                <input type="hidden" name="backup_file" id="restoreFileName" value="">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeRestoreModal()" class="w-full border-2 border-slate-300 text-slate-700 dark:text-slate-300 dark:border-[#1e2536] text-xs font-semibold px-4 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-md transition-colors">Yes, Restore System</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteBackupModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeDeleteModal()">
        <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 text-center" onclick="event.stopPropagation()">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 dark:bg-rose-950/50 mb-5">
                <svg class="h-8 w-8 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Delete Backup Permanently?</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">Are you sure you want to permanently delete <span id="deleteBackupFileNameDisplay" class="font-mono font-bold text-gray-900 dark:text-white break-all"></span>? This action cannot be undone.</p>
            
            <form id="deleteBackupForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="w-full border-2 border-slate-300 text-slate-700 dark:text-slate-300 dark:border-[#1e2536] text-xs font-semibold px-4 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-md transition-colors">Yes, Delete Backup</button>
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
    const deleteBaseUrl = "{{ url('admin/backups') }}";

    function openBackupDetail(filename, sizeMB, date, sizeKB, rawDate) {
        document.getElementById('bd-filename').textContent = filename;
        document.getElementById('bd-size').textContent = sizeMB;
        document.getElementById('bd-size-kb').textContent = sizeKB;
        document.getElementById('bd-date').textContent = date;
        document.getElementById('backupDetailModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeBackupDetail() {
        document.getElementById('backupDetailModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function confirmRestore(filename) {
        document.getElementById('restoreFileName').value = filename;
        document.getElementById('restoreModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function confirmDelete(filename) {
        document.getElementById('deleteBackupFileNameDisplay').textContent = filename;
        document.getElementById('deleteBackupForm').action = `${deleteBaseUrl}/${encodeURIComponent(filename)}`;
        document.getElementById('deleteBackupModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteBackupModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBackupDetail();
            closeRestoreModal();
            closeDeleteModal();
        }
    });
</script>
@endpush
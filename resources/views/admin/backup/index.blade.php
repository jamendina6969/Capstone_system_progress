@extends('layouts.default')

@section('title', 'System Backups')

@section('content')

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">System Backup & Restore</h1>

    {{-- Note: Success/Error messages are handled globally by layouts.default --}}

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden flex flex-col h-[calc(100vh-180px)] mb-4">
        
        <!-- Header -->
        <div class="shrink-0 flex items-center justify-between px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
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
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2536]/40 transition-colors">
                            <td class="px-8 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                {{ $file->getFilename() }}
                            </td>
                            <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">
                                {{ number_format($file->getSize() / 1048576, 2) }} MB
                            </td>
                            <td class="px-8 py-4 text-gray-600 dark:text-slate-400 font-medium">
                                {{ date('M d, Y h:i A', $file->getMTime()) }}
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    
                                    <!-- Download -->
                                    <a href="{{ route('admin.backups.download', $file->getFilename()) }}" class="bg-white border-2 border-blue-600 text-blue-700 hover:bg-blue-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-blue-600/80 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs inline-flex items-center gap-1.5">
                                        Download
                                    </a>

                                    <!-- Restore -->
                                    <button type="button" onclick="confirmRestore('{{ $file->getFilename() }}')" class="bg-white border-2 border-amber-500 text-amber-600 hover:bg-amber-500 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-amber-500/80 dark:text-amber-400 dark:hover:bg-amber-500 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs">
                                        Restore
                                    </button>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.backups.delete', $file->getFilename()) }}" method="POST" onsubmit="return confirm('Delete this backup permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-300 shadow-xs">
                                            Delete
                                        </button>
                                    </form>

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

    <!-- Restore Confirmation Modal -->
    <div id="restoreModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
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
                    <button type="button" onclick="closeRestoreModal()" class="w-full border-2 border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-full hover:bg-slate-700 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-md transition-colors">Yes, Restore System</button>
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
    function confirmRestore(filename) {
        document.getElementById('restoreFileName').value = filename;
        document.getElementById('restoreModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endpush
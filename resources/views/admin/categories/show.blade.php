@extends('layouts.default')

@section('title', $category->name . ' - Subcategories')

@section('content')

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white uppercase">{{ $category->name }}</h1>

    @if (session('error'))
        <div class="mb-6 rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-rose-300 border-2 border-red-200 dark:border-rose-900 px-6 py-4 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-2 border-emerald-200 dark:border-emerald-900 px-6 py-4 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-sm overflow-hidden mb-8">

        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Brand Subcategories Hierarchy</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Viewing brand subcategories belonging to {{ $category->name }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.categories.index') }}"
                   class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer whitespace-nowrap">
                    &larr; Back to Categories
                </a>
            </div>
        </div>

        <!-- Subcategories Grid Section -->
        <div class="p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($category->children as $child)
                    <!-- Brand Subcategory Card -->
                    <div class="w-full border-2 border-gray-200 dark:border-[#1e2536] rounded-2xl overflow-hidden bg-white dark:bg-[#161B26] flex flex-col shadow-xs p-5 justify-between transition-all hover:border-blue-500/50 dark:hover:border-blue-500/50">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <a href="{{ route('admin.categories.subcategories.show', $child) }}" class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wider hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $child->name }}
                                </a>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">BRAND</span>
                            </div>

                            <p class="text-xs text-gray-400 dark:text-slate-400 font-mono mb-4">Code: {{ $child->code ?? '—' }}</p>
                        </div>

                        <div class="pt-3 border-t-2 border-gray-100 dark:border-[#1e2536] flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-slate-400">
                                Products: <span class="text-gray-900 dark:text-white font-bold">{{ $child->products->count() }}</span>
                            </span>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <!-- View Button -->
                                <a href="{{ route('admin.categories.subcategories.show', $child) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                    View &rarr;
                                </a>
                                
                                <div class="h-4 w-px bg-gray-300 dark:bg-gray-600"></div>

                                <!-- Edit Button -->
                                <button type="button" onclick="toggleEditSubcategoryModal({{ $child->id }}, true)" class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                
                                <!-- Delete Button -->
                                <button type="button" onclick="toggleDeleteSubcategoryModal({{ $child->id }}, true)" class="text-gray-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400 dark:text-slate-500 font-medium">
                        No brand subcategories found under this main category.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ================= EDIT SUB-CATEGORY MODALS ================= --}}
    @foreach ($category->children as $child)
        <div id="editSubcategoryModal-{{ $child->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-xl bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
                
                <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Brand: {{ $child->name }}</h3>
                    <button type="button" onclick="toggleEditSubcategoryModal({{ $child->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold">
                        &times;
                    </button>
                </div>

                <form action="{{ route('admin.categories.subcategories.update', $child) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Brand Name</label>
                            <input type="text" name="name" value="{{ $child->name }}" required 
                                   class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Code <span class="text-gray-400 font-normal">(Locked)</span></label>
                            <input type="text" readonly value="{{ $child->code ?? 'N/A' }}" 
                                   class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-500 dark:text-slate-400 cursor-not-allowed focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Parent Category</label>
                        <input type="text" readonly value="{{ $category->name }}" 
                               class="w-full rounded-xl bg-gray-100 dark:bg-[#161B26]/50 border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-600 dark:text-slate-300 cursor-not-allowed focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="description" rows="3" placeholder="Description..."
                                  class="w-full rounded-xl bg-white dark:bg-[#161B26] border-2 border-gray-200 dark:border-[#1e2536] px-4 py-3 text-sm text-gray-900 dark:text-slate-100 focus:outline-none focus:border-slate-400">{{ $child->description ?? '' }}</textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                        <button type="button" onclick="toggleEditSubcategoryModal({{ $child->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                            Save Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ================= DELETE SUB-CATEGORY MODALS ================= --}}
    @foreach ($category->children as $child)
        <div id="deleteSubcategoryModal-{{ $child->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#10141D] border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl shadow-2xl p-8 transform transition-all text-left" onclick="event.stopPropagation()">
                
                <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-200 dark:border-[#1e2536]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Delete Brand</h3>
                    <button type="button" onclick="toggleDeleteSubcategoryModal({{ $child->id }}, false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors text-xl font-bold cursor-pointer">
                        &times;
                    </button>
                </div>

                <p class="text-sm text-gray-600 dark:text-slate-300 mb-6 font-medium">
                    Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $child->name }}</strong>? This action cannot be undone.
                </p>

                <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-gray-200 dark:border-[#1e2536]">
                    <button type="button" onclick="toggleDeleteSubcategoryModal({{ $child->id }}, false)" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#161B26] transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <form action="{{ route('admin.categories.subcategories.destroy', $child) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-white border-2 border-rose-600 text-rose-700 hover:bg-rose-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-rose-600/80 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                            Delete Brand
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@push('scripts')
<script>
    function toggleEditSubcategoryModal(subcatId, show) {
        const modal = document.getElementById(`editSubcategoryModal-${subcatId}`);
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function toggleDeleteSubcategoryModal(subcatId, show) {
        const modal = document.getElementById(`deleteSubcategoryModal-${subcatId}`);
        if (show) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
</script>
@endpush
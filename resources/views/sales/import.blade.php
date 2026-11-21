@extends('layouts.default')

@section('title', 'Import Sales')

@section('content')

    <div class="max-w-xl mx-auto bg-white dark:bg-[#10141D] border border-gray-200 dark:border-[#1e2536] rounded-xl shadow-sm p-8">

        <h1 class="text-xl font-bold mb-1 text-gray-900 dark:text-white">Import Sales Records</h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
            Upload a CSV file of historical sales to backfill demand data.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-300 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-gray-50 dark:bg-[#161B26] border border-gray-200 dark:border-[#1e2536] rounded-lg p-4 mb-6 text-sm">
            <p class="font-medium text-gray-700 dark:text-slate-200 mb-2">Required CSV columns:</p>

            <ul class="list-disc list-inside text-gray-600 dark:text-slate-400 space-y-1">
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">product_name</code></li>
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">quantity</code></li>
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">date</code> — format: YYYY-MM-DD or DD/MM/YYYY</li>
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">purchase_cost</code></li>
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">selling_price</code></li>
            </ul>

            <p class="font-medium text-gray-700 dark:text-slate-200 mt-4 mb-2">Optional columns:</p>
            <ul class="list-disc list-inside text-gray-600 dark:text-slate-400 space-y-1">
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">main_category</code></li>
                <li><code class="bg-gray-200 dark:bg-slate-800 px-1 rounded">brand_category</code></li>
            </ul>

            <p class="text-xs text-gray-500 dark:text-slate-400 mt-3">
                Download the latest template to ensure that your CSV columns match the required format.
            </p>

            <a href="{{ route('sales.template') }}"
               class="inline-flex items-center gap-2 mt-3 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                Download CSV Template
            </a>
        </div>

        <form method="POST"
              action="{{ route('sales.import.process') }}"
              enctype="multipart/form-data"
              class="space-y-4">

            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                    CSV File
                </label>

                <input type="file"
                       name="csv_file"
                       accept=".csv,.txt"
                       required
                       class="w-full text-sm text-gray-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0 file:text-sm file:font-medium
                              file:bg-gray-100 dark:file:bg-[#161B26] file:text-gray-700 dark:file:text-slate-200
                              hover:file:bg-gray-200 dark:hover:file:bg-[#1e2536]">
            </div>

            <div class="border border-gray-200 dark:border-[#1e2536] rounded-lg p-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="auto_create_products"
                           value="1"
                           id="autoCreateCheckbox"
                           {{ old('auto_create_products') ? 'checked' : '' }}
                           onchange="toggleCategoryField()"
                           class="rounded border-gray-300 dark:border-slate-600">

                    <span class="text-sm font-medium text-gray-700 dark:text-slate-200">
                        Auto-create products not found in the system
                    </span>
                </label>

                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 ml-6">
                    Unmatched product names will be created automatically. You can edit their product information later in Product Management.
                </p>

                <div id="categoryField"
                     class="{{ old('auto_create_products') ? '' : 'hidden' }} mt-3">

                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                        Default Category for New Products
                    </label>

                    <select name="default_category_id"
                            class="w-full rounded-lg border border-gray-300 dark:border-[#1e2536] bg-white dark:bg-[#10141D] text-gray-900 dark:text-slate-100 px-3 py-2 text-sm">

                        <option value="">Uncategorized (default)</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('default_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-2">
                        A category specified in the CSV will take priority over this default category.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('sales.records') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-[#1e2536]">
                    Cancel
                </a>

                <button type="submit"
                        class="bg-gray-900 dark:bg-slate-700 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-gray-800 dark:hover:bg-slate-600">
                    Upload & Import
                </button>
            </div>
        </form>

    </div>

@endsection

@push('scripts')
<script>
    function toggleCategoryField() {
        const checkbox = document.getElementById('autoCreateCheckbox');
        const categoryField = document.getElementById('categoryField');

        if (!checkbox || !categoryField) return;

        categoryField.classList.toggle('hidden', !checkbox.checked);
    }

    document.addEventListener('DOMContentLoaded', toggleCategoryField);
</script>
@endpush
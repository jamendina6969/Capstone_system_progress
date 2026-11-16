<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\SupplierLedger;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    private const PER_PAGE = 16;

    private function getCategoryAndDescendantIds($categoryId)
    {
        $categoryIds = [(int) $categoryId];
        $parentIds = [(int) $categoryId];

        while (!empty($parentIds)) {
            $childIds = Category::whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $childIds = array_values(array_diff($childIds, $categoryIds));

            if (empty($childIds)) {
                break;
            }

            $categoryIds = array_merge($categoryIds, $childIds);
            $parentIds = $childIds;
        }

        return array_values(array_unique($categoryIds));
    }

    public function index(Request $request)
    {
        $searchTerm = trim($request->input('search', ''));
        $sourceFilter = $request->input('source');

        $page = max(1, LengthAwarePaginator::resolveCurrentPage());
        $perPage = self::PER_PAGE;

        $salesQuery = Transaction::query();
        $paymentsQuery = SupplierLedger::query();

        // ── SOURCE FILTER (added: 'refund') ─────────────────
        if ($sourceFilter === 'pos') {
            $salesQuery->where('transaction_type', 0);
            $paymentsQuery->whereRaw('1 = 0');
        } elseif ($sourceFilter === 'imported') {
            $salesQuery->where('transaction_type', 1);
            $paymentsQuery->whereRaw('1 = 0');
        } elseif ($sourceFilter === 'refund') {
            $salesQuery->where('transaction_type', 2);
            $paymentsQuery->whereRaw('1 = 0');
        } elseif ($sourceFilter === 'payment') {
            $salesQuery->whereRaw('1 = 0');
        }

        // ── CATEGORY FILTER ─────────────────────────────────
        if ($request->filled('category_id')) {
            $categoryIds = $this->getCategoryAndDescendantIds(
                $request->input('category_id')
            );

            $salesQuery->whereHas(
                'transactionItems.product',
                function ($query) use ($categoryIds) {
                    $query->whereIn('category_id', $categoryIds);
                }
            );

            $paymentsQuery->whereRaw('1 = 0');
        }

        // ── DATE FILTER ─────────────────────────────────────
        if ($request->filled('date_from')) {
            $salesQuery->whereDate('created_at', '>=', $request->input('date_from'));
            $paymentsQuery->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $salesQuery->whereDate('created_at', '<=', $request->input('date_to'));
            $paymentsQuery->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // ── SEARCH FILTER ───────────────────────────────────
        if ($searchTerm !== '') {
            $salesQuery->where(function ($query) use ($searchTerm) {
                $query->where('transaction_id', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('firstname', 'like', "%{$searchTerm}%")
                            ->orWhere('lastname', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('transactionItems', function ($itemQuery) use ($searchTerm) {
                        $itemQuery->where('product_name', 'like', "%{$searchTerm}%")
                            ->orWhere('product_sku', 'like', "%{$searchTerm}%")
                            ->orWhere('item_sku', 'like', "%{$searchTerm}%");
                    });
            });

            $paymentsQuery->where(function ($query) use ($searchTerm) {
                $query->where('description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($searchTerm) {
                        $supplierQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // ── UNION + PAGINATE ────────────────────────────────
        $salesSelect = $salesQuery->select(
            'id',
            'created_at',
            DB::raw("'sale' as record_type"),
            'id as unified_sort_id'
        );

        $paymentsSelect = $paymentsQuery->select(
            'id',
            'created_at',
            DB::raw("'payment' as record_type"),
            'id as unified_sort_id'
        );

        $combinedQuery = $salesSelect->unionAll($paymentsSelect)
            ->orderBy('created_at', 'desc')
            ->orderBy('unified_sort_id', 'desc');

        $paginatedResults = $combinedQuery->paginate($perPage)->withQueryString();

        // ── HYDRATE (added: eager load customerReturn) ──────
        $merged = $paginatedResults->getCollection()->map(function ($row) {
            if ($row->record_type === 'sale') {
                $item = Transaction::with([
                    'user',
                    'transactionItems.product.category',
                    'customerReturn',          // ← Loads return info if this is a refund
                ])->find($row->id);
                if ($item) $item->record_type = 'sale';
                return $item;
            } else {
                $item = SupplierLedger::with(['supplier', 'purchaseOrder'])->find($row->id);
                if ($item) $item->record_type = 'payment';
                return $item;
            }
        })->filter();

        $paginatedResults->setCollection($merged);
        $transactions = $paginatedResults;

        $nextPageUrl = $transactions->nextPageUrl();

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view(
                    'pos.partials.transaction-rows',
                    compact('transactions')
                )->render(),

                'next_page_url' => $nextPageUrl,
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total_records' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'first_item' => $transactions->firstItem(),
                'last_item' => $transactions->lastItem(),
                'has_more' => $transactions->hasMorePages(),
            ]);
        }

        return view(
            'pos.transactionHistory',
            compact('transactions', 'categories', 'nextPageUrl')
        );
    }

    public function showReceipt($id)
    {
        // <--- FIXED: Added eager loading for transactionItems.materials 
        // and customerReturn.items so the receipt renders instantly and correctly.
        $transaction = Transaction::with([
            'transactionItems.product',
            'transactionItems.materials.product', 
            'user',
            'customerReturn',   
            'customerReturn.items',
        ])->findOrFail($id);

        return view('pos.receipt', compact('transaction'));
    }

    public function showPaymentReceipt($id)
    {
        $ledger = SupplierLedger::with([
            'supplier',
            'purchaseOrder'
        ])->findOrFail($id);

        return view(
            'transactions.receipt-payment',
            compact('ledger')
        );
    }
}
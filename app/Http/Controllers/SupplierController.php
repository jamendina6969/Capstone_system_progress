<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierRating;
use App\Models\PurchaseOrder;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();

        // =====================================================
        // AJAX / INFINITE SCROLL RESPONSE
        // =====================================================
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('suppliers.partials.rows', compact('suppliers'))->render(),
                'next_page_url' => $suppliers->nextPageUrl(),
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'total_records' => $suppliers->total(),
                'per_page' => $suppliers->perPage(),
                'first_item' => $suppliers->firstItem(),
                'last_item' => $suppliers->lastItem(),
                'has_more' => $suppliers->hasMorePages(),
            ]);
        }

        return view('suppliers.index', compact('suppliers'));
    }

    public function discrepancyReport(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.product.category.parent', 'items.product.category'])
            ->where('status', 'completed')
            ->whereHas('items', function ($q) {
                $q->whereColumn('quantity', '>', 'received_quantity');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->whereHas('items.product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('category', function ($sub) use ($categoryId) {
                      $sub->where('parent_id', $categoryId);
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        $ordersWithDiscrepancies = $query->orderBy('received_at', 'desc')->paginate(20)->withQueryString();

        // Calculate metrics
        $totalOrders = PurchaseOrder::where('status', 'completed')->count();
        $totalDiscrepancies = PurchaseOrder::where('status', 'completed')
            ->whereHas('items', function ($q) {
                $q->whereColumn('quantity', '>', 'received_quantity');
            })->count();

        // Calculate total lost value across all discrepancies
        $totalLost = PurchaseOrder::where('status', 'completed')
            ->whereHas('items', function ($q) {
                $q->whereColumn('quantity', '>', 'received_quantity');
            })->with('items')->get()->sum(function ($po) {
                return $po->items->sum(function ($item) {
                    $short = max(0, $item->quantity - ($item->received_quantity ?? $item->quantity));
                    return $short * $item->unit_cost;
                });
            });

        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('suppliers.partials.discrepancy-rows', compact('ordersWithDiscrepancies'))->render(),
                'next_page_url' => $ordersWithDiscrepancies->nextPageUrl(),
                'current_page' => $ordersWithDiscrepancies->currentPage(),
                'last_page' => $ordersWithDiscrepancies->lastPage(),
                'total_records' => $ordersWithDiscrepancies->total(),
                'per_page' => $ordersWithDiscrepancies->perPage(),
            ]);
        }

        return view('suppliers.discrepancy-report', compact(
            'ordersWithDiscrepancies',
            'totalOrders',
            'totalDiscrepancies',
            'totalLost',
            'categories'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country_code' => 'required|string|max:10',
            'phone' => 'required|numeric',
            'address' => 'required|string',
        ]);

        $data['phone'] = $data['country_code'] . ' ' . $data['phone'];
        unset($data['country_code']);

        Supplier::create($data);

        return back()->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country_code' => 'required|string|max:10',
            'phone' => 'required|numeric',
            'address' => 'required|string',
        ]);

        $data['phone'] = $data['country_code'] . ' ' . $data['phone'];
        unset($data['country_code']);

        $supplier->update($data);

        return back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if (
            $supplier->ledgers()->exists()
            || $supplier->purchaseOrders()->exists()
            || $supplier->ratings()->exists()
        ) {
            return back()->with(
                'error',
                'This supplier cannot be deleted because it has existing purchase orders, ledger transactions, or ratings.'
            );
        }

        $supplier->delete();

        return back()->with('success', 'Supplier deleted successfully.');
    }

    public function addPayment(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'change_amount' => 'nullable|numeric|min:0|lte:amount',
            'description' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $amountTendered = (float) $data['amount'];
        $changeAmount = (float) ($data['change_amount'] ?? 0);
        $actualPayment = round($amountTendered - $changeAmount, 2);

        if ($actualPayment <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'The actual payment must be greater than zero.',
            ]);
        }

        $receiptPath = null;

        try {
            DB::transaction(function () use (
                $supplier, $data, $amountTendered, $changeAmount, $actualPayment, &$receiptPath
            ) {
                $lockedSupplier = Supplier::whereKey($supplier->id)->lockForUpdate()->firstOrFail();
                $outstandingBalance = (float) $lockedSupplier->balance;

                if ($outstandingBalance <= 0) {
                    throw ValidationException::withMessages(['amount' => 'This supplier has no outstanding balance to pay.']);
                }

                if ($actualPayment > $outstandingBalance + 0.009) {
                    throw ValidationException::withMessages(['amount' => 'The actual payment cannot exceed the outstanding balance of ₱' . number_format($outstandingBalance, 2) . '.']);
                }

                if (request()->hasFile('receipt')) {
                    $receiptPath = request()->file('receipt')->store('supplier-payment-receipts', 'public');
                }

                SupplierLedger::create([
                    'supplier_id' => $lockedSupplier->id,
                    'transaction_type' => 'payment',
                    'amount' => $amountTendered,
                    'change_amount' => $changeAmount,
                    'description' => $data['description'] ?? 'Payment made to supplier',
                    'receipt_path' => $receiptPath,
                ]);
            });

            return back()->with('success', 'Payment recorded successfully.');

        } catch (ValidationException $e) {
            if ($receiptPath) Storage::disk('public')->delete($receiptPath);
            throw $e;
        } catch (\Throwable $e) {
            if ($receiptPath) Storage::disk('public')->delete($receiptPath);
            report($e);
            return back()->with('error', 'Unable to record supplier payment. Please try again.');
        }
    }

    public function ledger(Request $request, Supplier $supplier)
    {
        $query = $supplier->ledgers()->with('purchaseOrder');

        if ($request->filled('transaction_type')) {
            $transactionType = $request->input('transaction_type');
            if (in_array($transactionType, ['purchase', 'payment', 'shortage'], true)) {
                $query->where('transaction_type', $transactionType);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('transaction_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $ledgers = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(13)->withQueryString();

        return view('suppliers.ledger', compact('supplier', 'ledgers'));
    }

    public function rate(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (!empty($data['purchase_order_id'])) {
            $belongsToSupplier = $supplier->purchaseOrders()->whereKey($data['purchase_order_id'])->exists();
            if (!$belongsToSupplier) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'The selected Purchase Order does not belong to this supplier.',
                ]);
            }
        }

        SupplierRating::create([
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $data['purchase_order_id'] ?? null,
            'user_id' => auth()->id(),
            'score' => $data['score'],
            'comment' => $data['comment'] ?? null,
        ]);

        $supplier->update(['rating' => round($supplier->ratings()->avg('score'))]);

        return back()->with('success', 'Supplier rated successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierReturnController extends Controller
{
    /**
     * List supplier returns (also hosts the create modal in the view).
     */
    public function index(Request $request)
    {
        $returns = SupplierReturn::with([
                'supplier',
                'purchaseOrder',
                'user',
                'items.product.category',
            ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($w) use ($search) {
                    $w->where('return_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('reason'), fn($q) => $q->where('reason', $request->reason))
            ->latest()
            ->paginate(15);

        // Data required by the create modal rendered inside index.blade.php
        $suppliers      = Supplier::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with('supplier')->latest()->get();
        $products       = Product::orderBy('product_name')->get();

        // AJAX / infinite-scroll response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'          => view('supplier-returns.partials.rows', compact('returns'))->render(),
                'current_page'  => $returns->currentPage(),
                'last_page'     => $returns->lastPage(),
                'next_page_url' => $returns->nextPageUrl(),
                'total_records' => $returns->total(),
                'per_page'      => $returns->perPage(),
            ]);
        }

        return view('supplier-returns.index', compact(
            'returns',
            'suppliers',
            'purchaseOrders',
            'products',
        ));
    }

    /**
     * Show standalone create form (create.blade.php).
     */
    public function create()
    {
        $suppliers      = Supplier::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with('supplier')->latest()->get();
        $products       = Product::orderBy('product_name')->get();

        return view('supplier-returns.create', compact(
            'suppliers',
            'purchaseOrders',
            'products',
        ));
    }

    /**
     * Persist a new supplier return.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'              => 'required|exists:suppliers,id',
            'purchase_order_id'        => 'nullable|exists:purchase_orders,id',
            'reason'                   => 'required|in:defective,wrong_item,overstock,other',
            'notes'                    => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|numeric|min:1',
            'items.*.unit_cost'        => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $supplierReturn = SupplierReturn::create([
                'return_number'     => 'SR-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'supplier_id'       => $validated['supplier_id'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'user_id'           => Auth::id(),
                'reason'            => $validated['reason'],
                'notes'             => $validated['notes'] ?? null,
                'status'            => 'pending',
                'total_amount'      => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $total   += $subtotal;
                $supplierReturn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'subtotal'   => $subtotal,
                ]);
            }

            $supplierReturn->update(['total_amount' => $total]);
        });

        return redirect()
            ->route('supplier-returns.index')
            ->with('success', 'Supplier return created successfully.');
    }

    /**
     * Approve a pending return (status → completed).
     * NOTE: add supplier balance / stock adjustments here if your schema supports it.
     */
    public function approve(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'pending') {
            return back()->with('error', 'Only pending returns can be approved.');
        }

        $supplierReturn->update(['status' => 'completed']);

        return back()->with('success', 'Return approved successfully.');
    }

    /**
     * Reject / cancel a pending return (status → rejected).
     */
    public function reject(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'pending') {
            return back()->with('error', 'Only pending returns can be cancelled.');
        }

        $supplierReturn->update(['status' => 'rejected']);

        return back()->with('success', 'Return cancelled.');
    }

    /**
     * Discrepancy report (routed as suppliers.discrepancy).
     */
    public function discrepancyReport()
    {
        $returns = SupplierReturn::with('supplier', 'items.product')
            ->whereIn('status', ['pending', 'completed'])
            ->latest()
            ->paginate(20);

        if (view()->exists('suppliers.discrepancy')) {
            return view('suppliers.discrepancy', compact('returns'));
        }

        return response()->json($returns);
    }
}
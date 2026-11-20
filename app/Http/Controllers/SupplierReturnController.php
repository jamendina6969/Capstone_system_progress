<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductItemSku;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierReturn;
use App\Services\ItemSkuService;
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

        $suppliers      = Supplier::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with('supplier')->latest()->get();
        $products       = Product::orderBy('product_name')->get();

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
            'source_location'          => 'nullable|in:warehouse,store,defective_returns',
            'notes'                    => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_cost'        => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $supplierReturn = SupplierReturn::create([
                'return_number'     => 'SR-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'supplier_id'       => $validated['supplier_id'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'user_id'           => Auth::id(),
                'reason'            => $validated['reason'],
                'source_location'   => $validated['source_location'] ?? 'warehouse',
                'notes'             => $validated['notes'] ?? null,
                'status'            => 'pending',
                'total_amount'      => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = round((int) $item['quantity'] * (float) $item['unit_cost'], 2);
                $total   += $subtotal;
                $supplierReturn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => (int) $item['quantity'],
                    'unit_cost'  => (float) $item['unit_cost'],
                    'subtotal'   => $subtotal,
                ]);
            }

            $supplierReturn->update(['total_amount' => round($total, 2)]);

            app(\App\Services\NotificationService::class)->notifyRoles(['admin', 'manager'], [
                'type'       => 'supplier_return_created',
                'category'   => 'returns',
                'severity'   => 'warning',
                'title'      => "New Supplier Return {$supplierReturn->return_number}",
                'message'    => Auth::user()->full_name . " filed return {$supplierReturn->return_number} (₱" . number_format($total, 2) . ") awaiting approval.",
                'action_url' => route('supplier-returns.index', ['status' => 'pending']),
            ], Auth::id());
        });

        return redirect()
            ->route('supplier-returns.index')
            ->with('success', 'Supplier return created and queued for manager approval.');
    }

    /**
     * Approve a pending return:
     * 1) Validates & deducts inventory stock (Warehouse, Store, or Defective Returns pool)
     * 2) Marks serialized product_item_skus as returned_to_supplier
     * 3) Logs StockMovement audit row
     * 4) Posts return_credit entry to SupplierLedger so Supplier payable balance decreases automatically
     */
    public function approve(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'pending') {
            return back()->with('error', 'Only pending returns can be approved.');
        }

        try {
            DB::transaction(function () use ($supplierReturn) {
                $lockedReturn = SupplierReturn::whereKey($supplierReturn->id)
                    ->with('items.product')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedReturn->status !== 'pending') {
                    throw new \RuntimeException('This return has already been processed.');
                }

                $user = Auth::user();
                $source = $lockedReturn->source_location ?: 'warehouse';
                $skuService = app(ItemSkuService::class);
                $sortedItems = $lockedReturn->items->sortBy('product_id');

                foreach ($sortedItems as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                    if (!$product) {
                        throw new \RuntimeException("Product #{$item->product_id} no longer exists.");
                    }

                    $qty = (int) $item->quantity;

                    if ($source === 'defective_returns') {
                        $defectiveCount = ProductItemSku::where('product_id', $product->id)
                            ->where('status', ProductItemSku::STATUS_RETURNED_DEFECTIVE)
                            ->count();

                        if ($defectiveCount < $qty) {
                            throw new \RuntimeException("Insufficient defective returned units for {$product->product_name}. Available: {$defectiveCount}, Requested: {$qty}.");
                        }

                        $beforeQty = (int) $product->warehouse_quantity;
                        $afterQty = $beforeQty; // Defective units were already excluded from sellable stock
                        $skuService->allocateForSupplierReturn($product, $qty, $lockedReturn->id, 'defective_returns');
                    } elseif ($source === 'store') {
                        $beforeQty = (int) $product->store_quantity;
                        if ($beforeQty < $qty) {
                            throw new \RuntimeException("Insufficient Store stock for {$product->product_name}. Available: {$beforeQty}, Requested: {$qty}.");
                        }
                        $product->store_quantity = $beforeQty - $qty;
                        $product->save();
                        $afterQty = (int) $product->store_quantity;
                        $skuService->allocateForSupplierReturn($product, $qty, $lockedReturn->id, 'store');
                    } else {
                        $beforeQty = (int) $product->warehouse_quantity;
                        if ($beforeQty < $qty) {
                            throw new \RuntimeException("Insufficient Warehouse stock for {$product->product_name}. Available: {$beforeQty}, Requested: {$qty}.");
                        }
                        $product->warehouse_quantity = $beforeQty - $qty;
                        $product->save();
                        $afterQty = (int) $product->warehouse_quantity;
                        $skuService->allocateForSupplierReturn($product, $qty, $lockedReturn->id, 'warehouse');
                    }

                    StockMovement::create([
                        'batch_id'        => $lockedReturn->return_number,
                        'product_id'      => $product->id,
                        'requested_by'    => $lockedReturn->user_id ?? $user->id,
                        'requester_name'  => $lockedReturn->user?->full_name ?? ($user->firstname . ' ' . $user->lastname),
                        'approved_by'     => $user->id,
                        'approver_name'   => $user->firstname . ' ' . $user->lastname,
                        'type'            => 'supplier_return',
                        'status'          => 'approved',
                        'delivery_status' => 'delivered',
                        'quantity'        => $qty,
                        'quantity_before' => $beforeQty,
                        'quantity_after'  => $afterQty,
                        'note'            => "Supplier Return {$lockedReturn->return_number} ({$lockedReturn->reason}, source: {$source})",
                        'approved_at'     => now(),
                    ]);
                }

                // Post Supplier Ledger Credit to automatically reduce payable balance
                if ((float) $lockedReturn->total_amount > 0) {
                    SupplierLedger::create([
                        'supplier_id'       => $lockedReturn->supplier_id,
                        'purchase_order_id' => $lockedReturn->purchase_order_id,
                        'transaction_type'  => 'return_credit',
                        'amount'            => (float) $lockedReturn->total_amount,
                        'change_amount'     => 0,
                        'description'       => "Supplier Return Credit {$lockedReturn->return_number} (" . ucfirst(str_replace('_', ' ', $lockedReturn->reason)) . ')',
                    ]);
                }

                $lockedReturn->update(['status' => 'completed']);

                \App\Models\AuditLog::create([
                    'user_id'        => $user->id,
                    'actor_name'     => $user->full_name,
                    'actor_role'     => $user->role,
                    'event'          => 'supplier_return_approved',
                    'auditable_type' => SupplierReturn::class,
                    'auditable_id'   => $lockedReturn->id,
                    'old_values'     => ['status' => 'pending'],
                    'new_values'     => [
                        'return_number'   => $lockedReturn->return_number,
                        'source_location' => $source,
                        'total_amount'    => (float) $lockedReturn->total_amount,
                        'ledger_credited' => true,
                    ],
                    'ip_address'     => \App\Support\ClientContext::ip(),
                    'user_agent'     => request()->userAgent(),
                ]);

                app(\App\Services\NotificationService::class)->notifyRoles(['admin', 'manager'], [
                    'type'       => 'supplier_return_approved',
                    'category'   => 'returns',
                    'severity'   => 'success',
                    'title'      => "Supplier Return Approved: {$lockedReturn->return_number}",
                    'message'    => "{$user->full_name} approved {$lockedReturn->return_number}. ₱" . number_format((float) $lockedReturn->total_amount, 2) . " credit applied to supplier ledger.",
                    'action_url' => route('suppliers.ledger', $lockedReturn->supplier_id),
                ], $user->id);
            });

            return back()->with('success', 'Supplier return approved: stock deducted, unit SKUs updated, and supplier ledger credited.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to approve supplier return: ' . $e->getMessage());
        }
    }

    /**
     * Reject / cancel a pending return (status -> rejected).
     */
    public function reject(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'pending') {
            return back()->with('error', 'Only pending returns can be cancelled.');
        }

        $supplierReturn->update(['status' => 'rejected']);

        return back()->with('success', 'Return cancelled.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Category;
use App\Models\SupplierLedger;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierReturnController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $query = SupplierReturn::with([
            'supplier', 
            'purchaseOrder', 
            'user',
            'items.product.category.parent'
        ])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($supQ) use ($search) {
                      $supQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->paginate(self::PER_PAGE)->withQueryString();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('supplier-returns.partials.rows', compact('returns'))->render(),
                'next_page_url' => $returns->nextPageUrl(),
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'total_records' => $returns->total(),
                'per_page' => $returns->perPage(),
                'first_item' => $returns->firstItem(),
                'last_item' => $returns->lastItem(),
                'has_more' => $returns->hasMorePages(),
            ]);
        }
            
        return view('supplier-returns.index', compact('returns'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('product_name')->get();
        $purchaseOrders = PurchaseOrder::where('status', 'received')->orderBy('created_at', 'desc')->get();
        
        return view('supplier-returns.create', compact('suppliers', 'products', 'purchaseOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'reason' => 'required|in:defective,wrong_item,overstock,other',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $returnNumber = 'RTN-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $totalAmount = 0;

            $return = SupplierReturn::create([
                'return_number' => $returnNumber,
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'user_id' => Auth::id(),
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $subtotal;

                SupplierReturnItem::create([
                    'supplier_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);
            }

            $return->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('supplier-returns.index')->with('success', 'Return request submitted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating return: ' . $e->getMessage())->withInput();
        }
    }

    public function approve(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'pending') {
            return back()->with('error', 'This return has already been processed.');
        }

        try {
            DB::beginTransaction();
            $user = Auth::user();

            foreach ($supplierReturn->items as $item) {
                if ($item->product->warehouse_quantity >= $item->quantity) {
                    $beforeQty = $item->product->warehouse_quantity;
                    $item->product->decrement('warehouse_quantity', $item->quantity);

                    StockMovement::create([
                        'batch_id' => 'RET-' . $supplierReturn->return_number,
                        'product_id' => $item->product_id,
                        'type' => 'return_to_supplier',
                        'quantity' => -$item->quantity,
                        'quantity_before' => $beforeQty,
                        'quantity_after' => $beforeQty - $item->quantity,
                        'status' => 'approved',
                        'delivery_status' => 'delivered',
                        'requested_by' => $user->id,
                        'requester_name' => $user->firstname . ' ' . $user->lastname,
                        'approved_by' => $user->id,
                        'approver_name' => $user->firstname . ' ' . $user->lastname,
                        'note' => "Returned to supplier: {$supplierReturn->return_number}",
                    ]);
                }
            }

            SupplierLedger::create([
                'supplier_id' => $supplierReturn->supplier_id,
                'purchase_order_id' => $supplierReturn->purchase_order_id,
                'transaction_type' => 'payment',
                'amount' => $supplierReturn->total_amount,
                'description' => "Return credit: {$supplierReturn->return_number}"
            ]);

            $supplierReturn->update(['status' => 'completed']);

            DB::commit();
            return back()->with('success', 'Return approved. Stock and balance adjusted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving return: ' . $e->getMessage());
        }
    }

    public function reject(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status !== 'pending') {
            return back()->with('error', 'This return has already been processed.');
        }

        $supplierReturn->update(['status' => 'rejected']);
        return back()->with('success', 'Return request rejected.');
    }

    public function discrepancyReport(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.product.category.parent'])
            ->where('discrepancy_amount', '>', 0);

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $category = Category::with('children')->find($categoryId);
            $categoryIds = [$categoryId];
            if ($category && $category->children->isNotEmpty()) {
                $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
            }

            $query->whereHas('items.product', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('po_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('supplier', function($supplierQuery) use ($searchTerm) {
                      $supplierQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        $totalLost = (clone $query)->sum('discrepancy_amount');
        $totalDiscrepancies = (clone $query)->count();
        $totalOrders = PurchaseOrder::where('status', 'received')->count();

        $ordersWithDiscrepancies = $query->orderBy('received_at', 'desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('suppliers.partials.discrepancy-rows', compact('ordersWithDiscrepancies'))->render(),
                'next_page_url' => $ordersWithDiscrepancies->nextPageUrl(),
                'current_page' => $ordersWithDiscrepancies->currentPage(),
                'last_page' => $ordersWithDiscrepancies->lastPage(),
                'total_records' => $ordersWithDiscrepancies->total(),
                'per_page' => $ordersWithDiscrepancies->perPage(),
                'first_item' => $ordersWithDiscrepancies->firstItem(),
                'last_item' => $ordersWithDiscrepancies->lastItem(),
                'has_more' => $ordersWithDiscrepancies->hasMorePages(),
            ]);
        }

        return view('suppliers.discrepancy-report', compact(
            'ordersWithDiscrepancies', 
            'totalLost', 
            'totalOrders', 
            'totalDiscrepancies',
            'categories'
        ));
    }
}
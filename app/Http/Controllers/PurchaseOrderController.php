<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\SupplierLedger;
use App\Models\StockMovement;
use App\Services\DemandForecastService;
use App\Services\EoqService;
use App\Imports\PurchaseOrdersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'items.product'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('creator_name', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.product', function ($productQuery) use ($search) {
                        $productQuery->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->paginate(self::PER_PAGE)->withQueryString();

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::orderBy('product_name')->get();

        $autoOpenModal = false;
        $purchaseRequest = null;

        if ($request->has('create_modal') && $request->has('from_pr')) {
            $autoOpenModal = true;
            $purchaseRequest = PurchaseRequest::with('items.product')->find($request->from_pr);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('purchase-orders.partials.rows', compact('purchaseOrders'))->render(),
                'next_page_url' => $purchaseOrders->nextPageUrl(),
                'current_page' => $purchaseOrders->currentPage(),
                'last_page' => $purchaseOrders->lastPage(),
                'total_records' => $purchaseOrders->total(),
                'per_page' => $purchaseOrders->perPage(),
                'first_item' => $purchaseOrders->firstItem(),
                'last_item' => $purchaseOrders->lastItem(),
                'has_more' => $purchaseOrders->hasMorePages(),
            ]);
        }

        return view(
            'purchase-orders.index',
            compact('purchaseOrders', 'suppliers', 'products', 'autoOpenModal', 'purchaseRequest')
        );
    }

    public function create(Request $request)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $fromPr = $request->filled('from_pr');

        if (!$isAdmin && !$fromPr) {
            return redirect()->route('purchase-orders.index')->with('info', 'Purchase Orders are created from approved Purchase Requests.');
        }

        if ($isAdmin && !$fromPr) {
            return redirect()->route('purchase-orders.index')->with('open_po_modal', true);
        }

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::orderBy('product_name')->get();
        $purchaseRequest = PurchaseRequest::with('items.product')->find($request->from_pr);

        if (!$purchaseRequest || $purchaseRequest->status !== 'approved') {
            return back()->with('error', 'Only approved Purchase Requests can be converted to Purchase Orders.');
        }

        return view('purchase-orders.create', compact('suppliers', 'products', 'purchaseRequest'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'from_pr' => 'nullable|exists:purchase_requests,id',
            'tax_amount' => 'nullable|numeric|min:0',
            'freight_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $poNumber = 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $subtotal = 0;
                $taxAmount = (float) ($request->tax_amount ?? 0);
                $freightAmount = (float) ($request->freight_amount ?? 0);
                
                $user = Auth::user();

                $po = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $request->supplier_id,
                    'user_id' => $user->id,
                    'creator_name' => $user->firstname . ' ' . $user->lastname,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'subtotal' => 0,
                    'tax_amount' => $taxAmount,
                    'freight_amount' => $freightAmount,
                    'total_amount' => 0,
                ]);

                foreach ($request->items as $item) {
                    $itemSubtotal = (int) $item['quantity'] * (float) $item['unit_cost'];
                    $subtotal += $itemSubtotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'subtotal' => $itemSubtotal,
                    ]);
                }

                $po->subtotal = $subtotal;
                $po->total_amount = $subtotal + $taxAmount + $freightAmount;
                $po->save();

                if ($request->filled('from_pr')) {
                    $pr = PurchaseRequest::whereKey($request->from_pr)->lockForUpdate()->first();

                    if (!$pr || $pr->status !== 'approved') {
                        throw ValidationException::withMessages([
                            'from_pr' => 'The Purchase Request is no longer approved.',
                        ]);
                    }

                    $pr->status = 'converted';
                    $pr->save();
                }
            });

            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully.');

        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error creating Purchase Order: ' . $e->getMessage())->withInput();
        }
    }

    private function receiveReturnRoute(): string
    {
        return auth()->user()->role === 'warehouse_staff'
            ? 'purchase-orders.deliveries'
            : 'purchase-orders.index';
    }

    public function deliveries()
    {
        $pending = PurchaseOrder::with(['supplier', 'items.product'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        $recent = PurchaseOrder::with('supplier')
            ->where('status', 'received')
            ->orderByDesc('received_at')
            ->limit(10)
            ->get();

        return view('purchase-orders.deliveries', compact('pending', 'recent'));
    }
    public function receiveForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.product');
        return view('purchase-orders.receiveForm', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return redirect()->route($this->receiveReturnRoute())->with('error', 'This Purchase Order has already been received.');
        }

        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route($this->receiveReturnRoute())->with('error', 'Only pending Purchase Orders can be received.');
        }

        $purchaseOrder->load('items.product');

        if ($purchaseOrder->items->isEmpty()) {
            return redirect()->route($this->receiveReturnRoute())->with('error', 'This Purchase Order has no items.');
        }

        $rules = [
            'grn_notes' => 'nullable|string|max:2000',
        ];

        foreach ($purchaseOrder->items as $item) {
            $rules["received_quantities.{$item->id}"] = ['required', 'integer', 'min:0', 'max:' . $item->quantity];
            $rules["destinations.{$item->id}"] = ['required', 'in:store,warehouse'];
        }

        $validated = $request->validate($rules);

        try {
            DB::transaction(function () use ($purchaseOrder, $validated) {
                $po = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();

                if ($po->status !== 'pending') {
                    throw ValidationException::withMessages(['status' => 'This Purchase Order has already been processed.']);
                }

                $po->load('items.product');
                $user = Auth::user();

                $totalReceivedQuantity = 0;
                $totalReceivedValue = 0;
                $totalDiscrepancy = 0;
                $shortageDescriptions = [];

                // DEADLOCK FIX: Sort items by product_id before locking to ensure consistent lock order
                $sortedItems = $po->items->sortBy('product_id');

                foreach ($sortedItems as $item) {
                    $receivedQty = (int) $validated['received_quantities'][$item->id];
                    $orderedQty = (int) $item->quantity;
                    $unitCost = (float) $item->unit_cost;
                    $destination = $validated['destinations'][$item->id];

                    $item->received_quantity = $receivedQty;
                    $item->save();

                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                    if (!$product) {
                        throw new \Exception("Product for PO item #{$item->id} no longer exists.");
                    }

                    if ($receivedQty > 0) {
                        if ($destination === 'store') {
                            $beforeQty = (int) $product->store_quantity;
                            $product->store_quantity = $beforeQty + $receivedQty;
                            $locationLabel = 'Store';
                        } else {
                            $beforeQty = (int) $product->warehouse_quantity;
                            $product->warehouse_quantity = $beforeQty + $receivedQty;
                            $locationLabel = 'Warehouse';
                        }

                        $product->save();
                        $afterQty = $beforeQty + $receivedQty;

                        // Generate serialized unit item_skus tagged with this PO for end-to-end supplier traceability
                        app(\App\Services\ItemSkuService::class)->generateUnits(
                            $product,
                            $receivedQty,
                            $destination === 'store' ? 'store' : 'warehouse',
                            $po->id
                        );

                        StockMovement::create([
                            'batch_id' => 'GRN-' . $po->po_number,
                            'product_id' => $item->product_id,
                            'type' => 'po_received',
                            'quantity' => $receivedQty,
                            'quantity_before' => $beforeQty,
                            'quantity_after' => $afterQty,
                            'status' => 'approved',
                            'delivery_status' => 'delivered',
                            'requested_by' => $user->id,
                            'requester_name' => $user->firstname . ' ' . $user->lastname,
                            'approved_by' => $user->id,
                            'approver_name' => $user->firstname . ' ' . $user->lastname,
                            'note' => "Received into {$locationLabel} from PO {$po->po_number}" . (!empty($validated['grn_notes']) ? ' - ' . $validated['grn_notes'] : ''),
                        ]);
                    }

                    $receivedValue = $receivedQty * $unitCost;
                    $totalReceivedValue += $receivedValue;

                    if ($receivedQty < $orderedQty) {
                        $discrepancyQty = $orderedQty - $receivedQty;
                        $discrepancyAmount = $discrepancyQty * $unitCost;
                        $totalDiscrepancy += $discrepancyAmount;
                        $shortageDescriptions[] = "{$discrepancyQty} units short of " . ($product->product_name ?? 'Product') . " (₱" . number_format($discrepancyAmount, 2) . ")";
                    }

                    $totalReceivedQuantity += $receivedQty;
                }

                if ($totalReceivedValue > 0) {
                    SupplierLedger::updateOrCreate(
                        ['purchase_order_id' => $po->id, 'transaction_type' => 'purchase'],
                        ['supplier_id' => $po->supplier_id, 'amount' => $totalReceivedValue, 'change_amount' => 0, 'description' => "Goods received for Purchase Order {$po->po_number}"]
                    );
                } else {
                    SupplierLedger::where('purchase_order_id', $po->id)->where('transaction_type', 'purchase')->delete();
                }

                if ($totalDiscrepancy > 0) {
                    SupplierLedger::updateOrCreate(
                        ['purchase_order_id' => $po->id, 'transaction_type' => 'shortage'],
                        ['supplier_id' => $po->supplier_id, 'amount' => $totalDiscrepancy, 'change_amount' => 0, 'description' => 'Shortage adjustment: ' . implode('; ', $shortageDescriptions)]
                    );
                } else {
                    SupplierLedger::where('purchase_order_id', $po->id)->where('transaction_type', 'shortage')->delete();
                }

                $po->status = 'received';
                $po->received_quantity = $totalReceivedQuantity;
                $po->discrepancy_amount = $totalDiscrepancy;
                $po->grn_notes = $validated['grn_notes'] ?? null;
                $po->received_at = now();
                $po->save();
            });

            return redirect()->route($this->receiveReturnRoute())->with('success', 'Goods Received Note recorded successfully.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route($this->receiveReturnRoute())->with('error', 'Error receiving Purchase Order: ' . $e->getMessage());
        }
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'user', 'items.product.category');

        $company = ['name' => 'STEVEN HYDROTECH EXPONENT', 'tagline' => 'Water Filtration & Treatment Specialist', 'tin' => 'TIN: 123-456-789-000', 'contact' => 'Contact: (02) 1234-5678 | Email: info@stevenhydrotech.com'];
        $locations = ['store' => 'Store (Legazpi Branch - MAIN)', 'warehouse' => 'Warehouse (Putiao Branch)'];

        $subtotal = (float) ($purchaseOrder->subtotal ?? $purchaseOrder->total_amount);
        $taxAmount = (float) ($purchaseOrder->tax_amount ?? 0);
        $shippingFee = (float) ($purchaseOrder->freight_amount ?? 0);
        $grandTotal = $subtotal + $taxAmount + $shippingFee;

        $pdf = Pdf::loadView('purchase-orders.pdf', compact('purchaseOrder', 'company', 'locations', 'subtotal', 'taxAmount', 'shippingFee', 'grandTotal'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
    }

    public function lowStockSuggestions(DemandForecastService $forecastService, EoqService $eoqService)
    {
        // 1. Fetch ONLY products that are actually at or below their reorder point from the database
        $lowStockProducts = Product::with('category')
            ->whereRaw('(store_quantity + warehouse_quantity) <= COALESCE(reorder_point, 10)')
            ->orderBy('product_name')
            ->get()
            ->transform(function ($product) use ($forecastService, $eoqService) {
                // 2. Compute EOQ only for low-stock items
                $annualDemand = $forecastService->projectedAnnualDemand($product);
                $eoq = $eoqService->calculate($product, $annualDemand);
                
                $totalStock = (int) ($product->store_quantity ?? 0) + (int) ($product->warehouse_quantity ?? 0);
                
                // 3. Suggested Qty = EOQ - Current Stock (minimum 0)
                $product->suggested_qty = max(0, (int) round($eoq - $totalStock));
                
                return $product;
            });

        $grouped = $lowStockProducts->groupBy('category_id');

        return view('purchase-orders.lowStock', compact('grouped'));
    }

    public function export(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.product'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('po_number', 'like', "%{$s}%")
                       ->orWhere('creator_name', 'like', "%{$s}%")
                       ->orWhereHas('supplier', fn ($x) => $x->where('name', 'like', "%{$s}%"))
                       ->orWhereHas('items.product', fn ($x) => $x->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at');

        $filename = 'purchase-orders-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, [
                'PO Number',
                'Supplier',
                'Status',
                'Date Issued',
                'Creator',
                'Total Amount',
                'Expected Delivery',
                'Notes',
            ]);

            $query->chunk(500, function ($orders) use ($out) {
                foreach ($orders as $po) {
                    fputcsv($out, [
                        $po->po_number,
                        $po->supplier->name ?? '',
                        ucfirst($po->status),
                        $po->created_at ? $po->created_at->format('Y-m-d') : '',
                        $po->creator_name ?? ($po->user->name ?? ''),
                        number_format((float) $po->total_amount, 2, '.', ''),
                        optional($po->expected_delivery_date)->format('Y-m-d') ?? '',
                        $po->notes ?? '',
                    ]);
                }
            });

            fclose($out);
        }, 200, $headers);
    }

            public function importTemplate()
    {
        $filename = 'purchase-orders-import-template.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return Response::stream(function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            
            // Headers changed to explicitly state these are PO-level fields
            fputcsv($out, [
                'Supplier Name',
                'Product Name',
                'Main Category',
                'Brand',
                'Quantity',
                'Unit Cost',
                'Expected Delivery Date',
                'PO Tax Amount',
                'PO Freight Amount',
                'Notes'
            ]);

            // Sample Row 1: The global Tax and Freight for this PO are entered here
            fputcsv($out, ['Apex Water Solutions', 'Sample Product A', 'Electronics', 'Samsung', '10', '150.00', '2024-12-31', '150.00', '50.00', 'Deliver to Main Store']);
            
            // Sample Row 2: Tax and Freight are left blank because they already applied to the whole PO from Row 1
            fputcsv($out, ['Apex Water Solutions', 'Another Product', 'Fittings', 'Apex', '5', '99.50', '2024-12-31', '', '', 'Same PO, multiple line items']);
            
            fclose($out);
        }, 200, $headers);
    }
    public function import(Request $request)
    {
        $request->validate([
            'file'             => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'duplicate_action' => 'required|in:skip,update',
        ]);

        try {
            Excel::import(
                new PurchaseOrdersImport($request->duplicate_action),
                $request->file('file')
            );

            return redirect()
                ->route('purchase-orders.index')
                ->with('success', 'Purchase orders imported successfully.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
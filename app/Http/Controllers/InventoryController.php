<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryController extends Controller
{
    private const PER_PAGE = 15;

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
        $query = Product::with('category');

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);

            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('product_sku', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('category_id')) {
            $categoryIds = $this->getCategoryAndDescendantIds($request->category_id);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('status')) {
            $reorder = 'COALESCE(reorder_point, 10)';
            $store = 'COALESCE(store_quantity, 0)';
            $wh = 'COALESCE(warehouse_quantity, 0)';
            $total = "($store + $wh)";

            if ($request->status === 'store_out') {
                $query->whereRaw("$store <= 0");
            } elseif ($request->status === 'store_low') {
                $query->whereRaw("$store > 0")->whereRaw("$store <= $reorder");
            } elseif ($request->status === 'wh_out') {
                $query->whereRaw("$wh <= 0");
            } elseif ($request->status === 'wh_low') {
                $query->whereRaw("$wh > 0")->whereRaw("$wh <= $reorder");
            } elseif ($request->status === 'total_out') {
                $query->whereRaw("$total <= 0");
            } elseif ($request->status === 'total_low') {
                $query->whereRaw("$total > 0")->whereRaw("$total <= $reorder");
            } elseif ($request->status === 'restock') {
                $query->where(function ($q) use ($store, $wh, $total, $reorder) {
                    $q->whereRaw("$store = 0")
                      ->orWhereRaw("$store <= $reorder")
                      ->orWhereRaw("$wh = 0")
                      ->orWhereRaw("$wh <= $reorder")
                      ->orWhereRaw("$total <= $reorder");
                });
            }
        }

        $sortField = $request->input('sort', 'product_name');
        $sortDirection = $request->input('direction', 'asc');

        $allowedSorts = [
            'product_name', 'product_sku', 'store_quantity', 'warehouse_quantity',
            'selling_price', 'purchase_cost', 'created_at',
        ];

        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $products = $query->paginate(self::PER_PAGE)->withQueryString();

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->where('type', 'product')
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->expectsJson()) {
            $viewData = compact('products', 'categories');

            return response()->json([
                'html' => view('inventory.partials.rows', $viewData)->render(),
                'cards_html' => view('inventory.partials.cards', $viewData)->render(),

                'next_page_url' => $products->nextPageUrl(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total_records' => $products->total(),
                'per_page' => $products->perPage(),
                'first_item' => $products->firstItem(),
                'last_item' => $products->lastItem(),
                'has_more' => $products->hasMorePages(),
            ]);
        }

        return view('inventory.index', compact('products', 'categories'));
    }

    public function transfer(Request $request, Product $product)
    {
        $validated = $request->validate([
            'direction' => ['required', 'in:to_store,to_warehouse'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $quantity = (int) $validated['quantity'];
        $direction = $validated['direction'];
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'manager'], true)) {
            return back()->with('error', 'You are not authorized to transfer stock.');
        }

        $batchId = 'TRF-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));

        try {
            DB::transaction(function () use ($product, $quantity, $direction, $batchId, $user) {
                $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

                if ($direction === 'to_store') {
                    if ((int) $lockedProduct->warehouse_quantity < $quantity) {
                        throw new \RuntimeException('Insufficient warehouse stock. Available: ' . (int) $lockedProduct->warehouse_quantity);
                    }

                    $quantityBefore = (int) $lockedProduct->store_quantity;
                    $lockedProduct->decrement('warehouse_quantity', $quantity);
                    $lockedProduct->increment('store_quantity', $quantity);
                    $lockedProduct->refresh();
                    $quantityAfter = (int) $lockedProduct->store_quantity;
                    $type = 'transfer_to_store';
                } else {
                    if ((int) $lockedProduct->store_quantity < $quantity) {
                        throw new \RuntimeException('Insufficient store stock. Available: ' . (int) $lockedProduct->store_quantity);
                    }

                    $quantityBefore = (int) $lockedProduct->warehouse_quantity;
                    $lockedProduct->decrement('store_quantity', $quantity);
                    $lockedProduct->increment('warehouse_quantity', $quantity);
                    $lockedProduct->refresh();
                    $quantityAfter = (int) $lockedProduct->warehouse_quantity;
                    $type = 'transfer_to_warehouse';
                }

                StockMovement::create([
                    'batch_id' => $batchId,
                    'product_id' => $lockedProduct->id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'status' => 'approved',
                    'delivery_status' => 'delivered',
                    'requested_by' => $user->id,
                    'requester_name' => $user->firstname . ' ' . $user->lastname,
                    'approved_by' => $user->id,
                    'approver_name' => $user->firstname . ' ' . $user->lastname,
                    'approved_at' => now(),
                    'note' => $validated['note'] ?? null,
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['quantity' => 'Could not process the transfer. ' . $exception->getMessage()])->withInput();
        }

        $destination = $direction === 'to_store' ? 'store' : 'warehouse';
        return redirect()->route('inventory.index')->with('success', "Transferred {$quantity} unit(s) to the {$destination} successfully.");
    }

    public function logs(Request $request)
    {
        $query = StockMovement::with(['product.category', 'requester', 'approver'])->orderBy('created_at', 'desc');

        if ($request->filled('category_id')) {
            $categoryIds = $this->getCategoryAndDescendantIds($request->category_id);
            $query->whereHas('product', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        }

        $movements = $query->get();

        $grouped = $movements->groupBy('batch_id')->map(function ($batch) {
            $mergedProducts = $batch->groupBy('product_id')->map(function ($productLogs) {
                $firstLog = $productLogs->first();
                $lastLog = $productLogs->last();
                return (object) [
                    'product' => $firstLog->product,
                    'quantity_before' => $firstLog->quantity_before,
                    'quantity' => $productLogs->sum('quantity'),
                    'quantity_after' => $lastLog->quantity_after,
                    'note' => $firstLog->note,
                ];
            });

            return (object) [
                'first_movement' => $batch->first(),
                'products' => $mergedProducts,
            ];
        });

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $paginated = new LengthAwarePaginator(
            $grouped->forPage($page, $perPage),
            $grouped->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->where('type', 'product')
            ->orderBy('name')
            ->get();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('inventory.partials.log-batches', ['grouped' => $paginated])->render(),
                'next_page_url' => $paginated->nextPageUrl(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total_records' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'first_item' => $paginated->firstItem(),
                'last_item' => $paginated->lastItem(),
                'has_more' => $paginated->hasMorePages(),
            ]);
        }

        return view('inventory.logs', [
            'grouped' => $paginated,
            'categories' => $categories,
        ]);
    }

    public function pendingApprovals(Request $request)
    {
        $query = StockMovement::with([
            'product.category',
            'requester',
        ])->whereIn('status', ['pending', 'approved'])
          ->whereIn('delivery_status', ['pending', 'in_transit']);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('product', function($prodQuery) use ($searchTerm) {
                    $prodQuery->where('product_name', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('requester', function($userQuery) use ($searchTerm) {
                    $userQuery->where('firstname', 'like', "%{$searchTerm}%")
                              ->orWhere('lastname', 'like', "%{$searchTerm}%");
                })
                ->orWhere('type', 'like', "%{$searchTerm}%")
                ->orWhere('note', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status === 'in_transit') {
                $query->where('status', 'approved')->where('delivery_status', 'in_transit');
            }
        }

        $pending = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('inventory.partials.approval-rows', compact('pending'))->render(),
                'next_page_url' => $pending->nextPageUrl(),
                'current_page' => $pending->currentPage(),
                'last_page' => $pending->lastPage(),
                'total_records' => $pending->total(),
                'per_page' => $pending->perPage(),
                'first_item' => $pending->firstItem(),
                'last_item' => $pending->lastItem(),
                'has_more' => $pending->hasMorePages(),
            ]);
        }

        return view('inventory.restockApprovals', compact('pending'));
    }

    public function approve(StockMovement $movement)
    {
        try {
            DB::transaction(function () use ($movement) {
                $lockedMovement = StockMovement::whereKey($movement->id)->lockForUpdate()->firstOrFail();

                if ($lockedMovement->status !== 'pending') {
                    throw new \RuntimeException('This movement has already been processed.');
                }

                $product = Product::whereKey($lockedMovement->product_id)->lockForUpdate()->firstOrFail();
                $user = Auth::user();

                if (in_array($lockedMovement->type, ['restock_store', 'restock_warehouse'], true)) {
                    $lockedMovement->status = 'approved';
                    $lockedMovement->delivery_status = 'in_transit';
                } elseif ($lockedMovement->type === 'transfer_to_store') {
                    if ($product->warehouse_quantity < $lockedMovement->quantity) {
                        throw new \RuntimeException('Insufficient warehouse stock to approve this transfer.');
                    }
                    $product->decrement('warehouse_quantity', $lockedMovement->quantity);
                    $product->increment('store_quantity', $lockedMovement->quantity);
                    $product->refresh();
                    $lockedMovement->quantity_after = $product->store_quantity;
                    $lockedMovement->status = 'approved';
                    $lockedMovement->delivery_status = 'delivered';
                } elseif ($lockedMovement->type === 'transfer_to_warehouse') {
                    if ($product->store_quantity < $lockedMovement->quantity) {
                        throw new \RuntimeException('Insufficient store stock to approve this transfer.');
                    }
                    $product->decrement('store_quantity', $lockedMovement->quantity);
                    $product->increment('warehouse_quantity', $lockedMovement->quantity);
                    $product->refresh();
                    $lockedMovement->quantity_after = $product->warehouse_quantity;
                    $lockedMovement->status = 'approved';
                    $lockedMovement->delivery_status = 'delivered';
                } else {
                    throw new \RuntimeException('Unsupported stock movement type.');
                }

                $lockedMovement->approved_by = $user->id;
                $lockedMovement->approver_name = $user->firstname . ' ' . $user->lastname;
                $lockedMovement->approved_at = now();
                $lockedMovement->save();
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Stock movement approved successfully.');
    }

    public function reject(Request $request, StockMovement $movement)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($movement, $validated) {
                $lockedMovement = StockMovement::whereKey($movement->id)->lockForUpdate()->firstOrFail();

                if ($lockedMovement->status !== 'pending') {
                    throw new \RuntimeException('This movement has already been processed.');
                }

                $user = Auth::user();
                $lockedMovement->status = 'rejected';
                $lockedMovement->delivery_status = 'not_approved';
                $lockedMovement->approved_by = $user->id;
                $lockedMovement->approver_name = $user->firstname . ' ' . $user->lastname;
                $lockedMovement->approved_at = now();
                $lockedMovement->note = $validated['rejection_reason'];
                $lockedMovement->save();
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Stock request rejected.');
    }

    public function restockBatch()
    {
        $categories = Category::whereNull('parent_id')->with('children')->where('type', 'product')->orderBy('name')->get();
        $products = Product::with('category')->orderBy('product_name')->get();
        return view('inventory.restockBatch', compact('categories', 'products'));
    }

    public function restock(Request $request)
    {
        $request->validate([
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.store_qty' => ['nullable', 'integer', 'min:0'],
            'products.*.warehouse_qty' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $batchId = 'RST-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
        $user = Auth::user();
        $isStaff = in_array($user->role, ['staff', 'warehouse_staff'], true);
        $hasChanges = false;

        try {
            DB::transaction(function () use ($request, $batchId, &$hasChanges, $isStaff, $user) {
                foreach ($request->products as $item) {
                    $storeAdd = (int) ($item['store_qty'] ?? 0);
                    $warehouseAdd = (int) ($item['warehouse_qty'] ?? 0);

                    if ($storeAdd <= 0 && $warehouseAdd <= 0) continue;

                    $product = Product::whereKey($item['id'])->lockForUpdate()->firstOrFail();
                    $hasChanges = true;
                    $beforeStore = (int) $product->store_quantity;
                    $beforeWarehouse = (int) $product->warehouse_quantity;
                    $status = $isStaff ? 'pending' : 'approved';
                    $deliveryStatus = $isStaff ? 'pending' : 'delivered';
                    $approvedBy = $isStaff ? null : $user->id;
                    $approverName = $isStaff ? null : $user->firstname . ' ' . $user->lastname;

                    if ($storeAdd > 0) {
                        $afterStore = $beforeStore;
                        if (!$isStaff) {
                            $product->increment('store_quantity', $storeAdd);
                            $afterStore += $storeAdd;
                        }
                        StockMovement::create([
                            'batch_id' => $batchId, 'product_id' => $product->id, 'type' => 'restock_store',
                            'quantity' => $storeAdd, 'quantity_before' => $beforeStore, 'quantity_after' => $afterStore,
                            'status' => $status, 'delivery_status' => $deliveryStatus, 'requested_by' => $user->id,
                            'requester_name' => $user->firstname . ' ' . $user->lastname,
                            'approved_by' => $approvedBy, 'approver_name' => $approverName,
                            'approved_at' => $isStaff ? null : now(), 'note' => $request->note,
                        ]);
                    }

                    if ($warehouseAdd > 0) {
                        $afterWarehouse = $beforeWarehouse;
                        if (!$isStaff) {
                            $product->increment('warehouse_quantity', $warehouseAdd);
                            $afterWarehouse += $warehouseAdd;
                        }
                        StockMovement::create([
                            'batch_id' => $batchId, 'product_id' => $product->id, 'type' => 'restock_warehouse',
                            'quantity' => $warehouseAdd, 'quantity_before' => $beforeWarehouse, 'quantity_after' => $afterWarehouse,
                            'status' => $status, 'delivery_status' => $deliveryStatus, 'requested_by' => $user->id,
                            'requester_name' => $user->firstname . ' ' . $user->lastname,
                            'approved_by' => $approvedBy, 'approver_name' => $approverName,
                            'approved_at' => $isStaff ? null : now(), 'note' => $request->note,
                        ]);
                    }
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['quantity' => 'Could not process the restock. ' . $exception->getMessage()])->withInput();
        }

        if (!$hasChanges) {
            return back()->with('error', 'No quantities were entered. Please input at least one quantity.');
        }

        $message = $isStaff ? 'Restock request submitted for approval.' : 'Stock restock completed successfully.';
        return redirect()->route('inventory.index')->with('success', $message);
    }

    public function myRequests(Request $request)
    {
        $user = Auth::user();
        $query = StockMovement::with(['product.category', 'approver', 'requester'])->orderBy('created_at', 'desc');

        if (in_array($user->role, ['staff', 'warehouse_staff'])) {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('product', function($prodQuery) use ($searchTerm) {
                    $prodQuery->where('product_name', 'like', "%{$searchTerm}%")
                              ->orWhere('product_sku', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('requester', function($userQuery) use ($searchTerm) {
                    $userQuery->where('firstname', 'like', "%{$searchTerm}%")
                              ->orWhere('lastname', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('approver', function($appQuery) use ($searchTerm) {
                    $appQuery->where('firstname', 'like', "%{$searchTerm}%")
                             ->orWhere('lastname', 'like', "%{$searchTerm}%");
                })
                ->orWhere('status', 'like', "%{$searchTerm}%")
                ->orWhere('delivery_status', 'like', "%{$searchTerm}%")
                ->orWhere('created_at', 'like', "%{$searchTerm}%")
                ->orWhere('note', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            if ($request->status === 'not_approved') {
                $query->where(function ($q) {
                    $q->where('status', 'rejected')->orWhere('delivery_status', 'not_approved');
                });
            } else {
                $query->where('delivery_status', $request->status);
            }
        }

        $requests = $query->paginate(13)->withQueryString();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('inventory.partials.my-requests-rows', compact('requests'))->render(),
                'next_page_url' => $requests->nextPageUrl(),
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total_records' => $requests->total(),
                'per_page' => $requests->perPage(),
                'first_item' => $requests->firstItem(),
                'last_item' => $requests->lastItem(),
                'has_more' => $requests->hasMorePages(),
            ]);
        }

        return view('inventory.myRequests', compact('requests'));
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'location' => ['required', 'in:store,warehouse'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        if ($user->role === 'staff' && $validated['location'] !== 'store') {
            return back()->withErrors(['location' => 'Staff can only request store restock.'])->withInput();
        }

        if ($user->role === 'warehouse_staff' && $validated['location'] !== 'warehouse') {
            return back()->withErrors(['location' => 'Warehouse staff can only request warehouse restock.'])->withInput();
        }

        $isStaff = in_array($user->role, ['staff', 'warehouse_staff'], true);

        $recentRequest = StockMovement::where('requested_by', $user->id)
            ->where('product_id', $validated['product_id'])
            ->where('quantity', $validated['quantity'])
            ->where('created_at', '>=', now()->subSeconds(3))
            ->first();

        if ($recentRequest) {
            return redirect()->route('inventory.myRequests')
                ->with('success', 'Restock request #' . $recentRequest->id . ' submitted for approval.');
        }

        $batchId = 'RST-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));

        try {
            $movement = DB::transaction(function () use ($validated, $user, $batchId, $isStaff) {
                $product = Product::whereKey($validated['product_id'])->lockForUpdate()->firstOrFail();
                $quantity = (int) $validated['quantity'];
                $isWarehouse = $validated['location'] === 'warehouse';
                $type = $isWarehouse ? 'restock_warehouse' : 'restock_store';
                $quantityBefore = $isWarehouse ? (int) $product->warehouse_quantity : (int) $product->store_quantity;
                $quantityAfter = $quantityBefore;

                if ($isStaff) {
                    $status = 'pending';
                    $deliveryStatus = 'pending';
                    $approvedBy = null;
                    $approvedAt = null;
                    $approverName = null;
                } else {
                    if ($isWarehouse) {
                        $product->increment('warehouse_quantity', $quantity);
                    } else {
                        $product->increment('store_quantity', $quantity);
                    }
                    $quantityAfter = $quantityBefore + $quantity;
                    $status = 'approved';
                    $deliveryStatus = 'delivered';
                    $approvedBy = $user->id;
                    $approvedAt = now();
                    $approverName = $user->firstname . ' ' . $user->lastname;
                }

                return StockMovement::create([
                    'batch_id' => $batchId, 'product_id' => $product->id, 'type' => $type,
                    'quantity' => $quantity, 'quantity_before' => $quantityBefore, 'quantity_after' => $quantityAfter,
                    'status' => $status, 'delivery_status' => $deliveryStatus, 'requested_by' => $user->id,
                    'requester_name' => $user->firstname . ' ' . $user->lastname,
                    'approved_by' => $approvedBy, 'approver_name' => $approverName,
                    'approved_at' => $approvedAt, 'note' => $validated['note'] ?? null,
                ]);
            });
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['quantity' => 'Could not create the request. ' . $exception->getMessage()])->withInput();
        }

        $message = $isStaff
            ? 'Restock request #' . $movement->id . ' submitted for approval.'
            : 'Restock request #' . $movement->id . ' completed successfully.';

        return redirect()->route('inventory.myRequests')->with('success', $message);
    }

    public function receiveRequest(Request $request, StockMovement $movement)
    {
        $user = Auth::user();

        // Admins and Managers bypass the action password check here as well
        if (!in_array($user->role, ['admin', 'manager'])) {
            $validated = $request->validate([
                'action_password' => ['required', 'string'],
            ]);

            $authorizer = User::whereIn('role', ['admin', 'manager'])
                ->whereNotNull('action_password')
                ->get()
                ->first(function ($authUser) use ($validated) {
                    return Hash::check($validated['action_password'], $authUser->action_password);
                });

            if (!$authorizer) {
                return back()->withErrors([
                    'action_password' => 'The action password is incorrect or does not belong to a Manager/Admin.',
                ]);
            }
        } else {
            $authorizer = $user; 
        }

        try {
            DB::transaction(function () use ($movement, $authorizer) {
                $lockedMovement = StockMovement::whereKey($movement->id)->lockForUpdate()->firstOrFail();

                if ($lockedMovement->status !== 'approved' || $lockedMovement->delivery_status !== 'in_transit') {
                    throw new \RuntimeException('This request is not in transit or has already been received.');
                }

                if (!in_array($lockedMovement->type, ['restock_store', 'restock_warehouse'], true)) {
                    throw new \RuntimeException('This movement cannot be processed through the receiving action.');
                }

                $product = Product::whereKey($lockedMovement->product_id)->lockForUpdate()->firstOrFail();

                if ($lockedMovement->type === 'restock_store') {
                    $quantityBefore = (int) $product->store_quantity;
                    $product->increment('store_quantity', $lockedMovement->quantity);
                    $lockedMovement->quantity_before = $quantityBefore;
                    $lockedMovement->quantity_after = $quantityBefore + (int) $lockedMovement->quantity;
                } else {
                    $quantityBefore = (int) $product->warehouse_quantity;
                    $product->increment('warehouse_quantity', $lockedMovement->quantity);
                    $lockedMovement->quantity_before = $quantityBefore;
                    $lockedMovement->quantity_after = $quantityBefore + (int) $lockedMovement->quantity;
                }

                $lockedMovement->delivery_status = 'received';
                $lockedMovement->approved_by = $authorizer->id;
                $lockedMovement->approver_name = $authorizer->firstname . ' ' . $authorizer->lastname;
                $lockedMovement->save();
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'Could not receive the stock request. Please try again.');
        }

        return redirect()->route('inventory.myRequests')->with('success', 'Items marked as received. Stock has been updated successfully.');
    }
}
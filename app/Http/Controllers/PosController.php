<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SalesRecord;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionItemMaterial; // <--- ADDED
use App\Services\DemandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosController extends Controller
{
    private const PER_PAGE = 24;

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

    private function getCartReservedQuantities(array $cart, $excludeProjectId = null): array
    {
        $reserved = ['store' => [], 'warehouse' => []];

        foreach ($cart as $id => $item) {
            if ($excludeProjectId && $id === $excludeProjectId) {
                continue;
            }

            if (($item['type'] ?? 'product') === 'project') {
                foreach ($item['project_breakdown']['materials'] as $mat) {
                    $reserved[$mat['source']][$mat['product_id']] = 
                        ($reserved[$mat['source']][$mat['product_id']] ?? 0) + (int) $mat['qty'];
                }
            } else {
                $reserved['store'][$id] = ($reserved['store'][$id] ?? 0) + (int) $item['quantity'];
            }
        }

        return $reserved;
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $searchTerms = array_filter(explode(' ', trim($request->search)));

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                        $subQuery->where('product_name', 'like', '%' . $term . '%')
                            ->orWhereHas('category', function ($catQuery) use ($term) {
                                $catQuery->where('name', 'like', '%' . $term . '%');
                            });
                    });
                }
            });
        }

        if ($request->filled('category_id')) {
            $categoryIds = $this->getCategoryAndDescendantIds($request->category_id);
            $query->whereIn('category_id', $categoryIds);
        }

        $items = $query->orderBy('product_name')->paginate(self::PER_PAGE)->withQueryString();
        
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $cart = session()->get('cart', []);

        $rawSubtotal = 0;
        $totalItemDiscounts = 0;
        $hasProjectInCart = false;

        foreach ($cart as $id => $item) {
            if (($item['type'] ?? 'product') === 'project') {
                $hasProjectInCart = true;
            }

            $itemBaseTotal = (float) $item['selling_price'] * (int) $item['quantity'];
            $rawSubtotal += $itemBaseTotal;

            $discType = $item['item_discount_type'] ?? 'none';
            $discVal = (float) ($item['item_discount_value'] ?? 0);
            $discScope = $item['item_discount_scope'] ?? 'whole';

            $itemDiscountAmount = 0;

            if ($discType === 'percentage' && $discVal > 0) {
                $itemDiscountAmount = $itemBaseTotal * ($discVal / 100);
            } elseif ($discType === 'amount' && $discVal > 0) {
                if ($discScope === 'per_item') {
                    $itemDiscountAmount = min($itemBaseTotal, $discVal * (int) $item['quantity']);
                } else {
                    $itemDiscountAmount = min($itemBaseTotal, $discVal);
                }
            }

            $cart[$id]['item_discount_amount'] = $itemDiscountAmount;
            $cart[$id]['subtotal'] = max(0, $itemBaseTotal - $itemDiscountAmount);
            $totalItemDiscounts += $itemDiscountAmount;
        }

        session()->put('cart', $cart);

        $subtotal = max(0, $rawSubtotal - $totalItemDiscounts);
        $cartDiscount = session()->get('cart_discount', ['type' => 'none', 'value' => 0, 'mode' => 'percentage']);
        $discountAmount = $this->calculateCartDiscount($subtotal, $cartDiscount);
        $total = max(0, $subtotal - $discountAmount);
        
        $vatRate = 0.12;
        $vatAmount = $total * $vatRate;
        $grandTotal = $total + $vatAmount;

        $completedTransaction = null;

        if (session()->has('completed_transaction_id')) {
            $completedTransaction = Transaction::with(['transactionItems.product', 'user'])
                ->find(session('completed_transaction_id'));
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pos.partials.product-cards', compact('items', 'cart', 'hasProjectInCart'))->render(),
                'next_page_url' => $items->nextPageUrl(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total_records' => $items->total(),
                'per_page' => $items->perPage(),
                'first_item' => $items->firstItem(),
                'last_item' => $items->lastItem(),
                'has_more' => $items->hasMorePages(),
            ]);
        }

        $offlineCatalog = Product::select([
            'id',
            'product_name',
            'product_sku',
            'selling_price',
            'store_quantity',
            'reorder_point',
        ])->get();

        return view('pos.pointOfSale', compact(
            'items', 'categories', 'cart', 'rawSubtotal', 'subtotal', 
            'totalItemDiscounts', 'discountAmount', 'total', 'cartDiscount', 
            'completedTransaction', 'hasProjectInCart', 'vatAmount', 'grandTotal',
            'offlineCatalog'
        ));
    }

    private function calculateCartDiscount($subtotal, $cartDiscount)
    {
        $type = $cartDiscount['type'] ?? 'none';
        $value = (float) ($cartDiscount['value'] ?? 0);
        $mode = $cartDiscount['mode'] ?? 'percentage';

        if ($type === 'senior' || $type === 'pwd') return $subtotal * 0.20;
        if ($type === 'percentage') return $subtotal * ($value / 100);
        if ($type === 'amount') return min($subtotal, $value);
        
        if ($type === 'employee' || $type === 'voucher') {
            if ($mode === 'percentage') return $subtotal * ($value / 100);
            return min($subtotal, $value);
        }

        return 0;
    }

    private function calculateItemDiscount($item)
    {
        if (($item['type'] ?? 'product') === 'project') return 0;

        $quantity = (int) $item['quantity'];
        $unitPrice = (float) $item['selling_price'];
        $baseTotal = $unitPrice * $quantity;

        $type = $item['item_discount_type'] ?? 'none';
        $value = (float) ($item['item_discount_value'] ?? 0);
        $scope = $item['item_discount_scope'] ?? 'whole';

        if ($type === 'percentage' && $value > 0) return $baseTotal * ($value / 100);
        if ($type === 'amount' && $value > 0) {
            if ($scope === 'per_item') return min($baseTotal, $value * $quantity);
            return min($baseTotal, $value);
        }

        return 0;
    }

    public function getProjectDetails($id)
    {
        $cart = session()->get('cart', []);
        if (!isset($cart[$id]) || $cart[$id]['type'] !== 'project') {
            return response()->json(['error' => 'Project not found in cart.'], 404);
        }

        $project = $cart[$id];
        
        return response()->json([
            'id' => $id,
            'name' => str_replace('PROJECT: ', '', $project['product_name']),
            'description' => $project['project_description'] ?? '',
            'breakdown' => $project['project_breakdown'],
            'image' => $project['project_image'] ? asset('storage/' . $project['project_image']) : null
        ]);
    }

    public function addProjectToCart(Request $request)
    {
        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'project_image' => 'nullable|image|max:2048',
            'materials' => 'required|array|min:1',
            'materials.*.product_id' => 'required|exists:products,id',
            'materials.*.quantity' => 'required|integer|min:1',
            'materials.*.source' => 'required|in:store,warehouse',
            'outsource_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'installation_cost' => 'nullable|numeric|min:0',
            'additional_cost' => 'nullable|numeric|min:0',
            'project_tax' => 'nullable|numeric|min:0',
            'project_freight' => 'nullable|numeric|min:0',
            'markup_type' => 'required|in:percentage,amount',
            'markup_value' => 'nullable|numeric|min:0',
        ]);

        $cart = session()->get('cart', []);
        $projectId = 'PROJ-' . strtoupper(substr(uniqid(), -6));

        try {
            $materialsBreakdown = [];
            $materialsSubtotal = 0;
            $reserved = $this->getCartReservedQuantities($cart);

            foreach ($data['materials'] as $mat) {
                $product = Product::findOrFail($mat['product_id']);
                $qty = (int) $mat['quantity'];
                $source = $mat['source'];
                
                $availableStore = (int) $product->store_quantity - ($reserved['store'][$product->id] ?? 0);
                $availableWh = (int) $product->warehouse_quantity - ($reserved['warehouse'][$product->id] ?? 0);

                if ($source === 'store' && $availableStore < $qty) {
                    throw new \RuntimeException("Not enough stock in STORE for {$product->product_name}. Available: {$availableStore}");
                }
                if ($source === 'warehouse' && $availableWh < $qty) {
                    throw new \RuntimeException("Not enough stock in WAREHOUSE for {$product->product_name}. Available: {$availableWh}");
                }

                $lineTotal = $qty * $product->selling_price;
                $materialsSubtotal += $lineTotal;

                $materialsBreakdown[] = [
                    'product_id' => $product->id,
                    'name' => $product->product_name,
                    'qty' => $qty,
                    'price' => (float) $product->selling_price,
                    'subtotal' => $lineTotal,
                    'source' => $source,
                ];
            }

            $outsource = (float) ($data['outsource_cost'] ?? 0);
            $labor = (float) ($data['labor_cost'] ?? 0);
            $install = (float) ($data['installation_cost'] ?? 0);
            $additional = (float) ($data['additional_cost'] ?? 0);
            $tax = (float) ($data['project_tax'] ?? 0);
            $freight = (float) ($data['project_freight'] ?? 0);
            $markupType = $data['markup_type'];
            $markupValue = (float) ($data['markup_value'] ?? 0);

            $projectSubtotal = $materialsSubtotal + $outsource + $labor + $install + $additional + $tax + $freight;
            $markupAmount = $markupType === 'percentage' ? ($projectSubtotal * ($markupValue / 100)) : $markupValue;
            $finalTotal = $projectSubtotal + $markupAmount;

            $imagePath = null;
            if ($request->hasFile('project_image')) {
                $imagePath = $request->file('project_image')->store('projects', 'public');
            }

            $cart[$projectId] = [
                'type' => 'project',
                'product_name' => 'PROJECT: ' . $data['project_name'],
                'selling_price' => $finalTotal,
                'quantity' => 1,
                'item_discount_type' => 'none',
                'item_discount_value' => 0,
                'item_discount_amount' => 0,
                'subtotal' => $finalTotal,
                'project_image' => $imagePath,
                'project_description' => $data['project_description'] ?? null,
                'project_breakdown' => [
                    'materials' => $materialsBreakdown,
                    'materials_subtotal' => $materialsSubtotal,
                    'outsource_cost' => $outsource,
                    'labor_cost' => $labor,
                    'installation_cost' => $install,
                    'additional_cost' => $additional,
                    'tax' => $tax,
                    'freight' => $freight,
                    'markup_type' => $markupType,
                    'markup_value' => $markupValue,
                    'markup_amount' => $markupAmount,
                    'project_subtotal' => $projectSubtotal,
                    'final_total' => $finalTotal,
                ],
            ];

            session()->put('cart', $cart);
            return redirect()->route('pos')->with('success', 'Custom Project added to cart.');

        } catch (\RuntimeException $e) {
            return redirect()->route('pos')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('pos')->with('error', 'Error adding project: ' . $e->getMessage());
        }
    }

    public function quickAddMaterial(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'source' => 'required|in:store,warehouse',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        $projectId = null;

        foreach ($cart as $id => $item) {
            if (($item['type'] ?? 'product') === 'project') {
                $projectId = $id;
                break;
            }
        }

        if (!$projectId) return redirect()->route('pos')->with('error', 'No active project found. Please create a project first.');

        $materials = $cart[$projectId]['project_breakdown']['materials'];
        $existingIndex = -1;

        foreach ($materials as $index => $mat) {
            if ($mat['product_id'] == $request->product_id && $mat['source'] === $request->source) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex >= 0) {
            $materials[$existingIndex]['qty'] += (int)$request->qty;
        } else {
            $product = Product::find($request->product_id);
            $materials[] = [
                'product_id' => $product->id,
                'name' => $product->product_name,
                'qty' => (int)$request->qty,
                'price' => (float)$product->selling_price,
                'subtotal' => 0,
                'source' => $request->source,
            ];
        }

        return $this->_processProjectUpdate($projectId, $materials);
    }

    public function quickDropMaterial(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'source' => 'required|in:store,warehouse',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        $projectId = null;

        foreach ($cart as $id => $item) {
            if (($item['type'] ?? 'product') === 'project') {
                $projectId = $id;
                break;
            }
        }

        if (!$projectId) return redirect()->route('pos')->with('error', 'No active project found.');

        $materials = $cart[$projectId]['project_breakdown']['materials'];
        $found = false;

        foreach ($materials as $index => $mat) {
            if ($mat['product_id'] == $request->product_id && $mat['source'] === $request->source) {
                if ($mat['qty'] < $request->qty) {
                    return redirect()->route('pos')->with('error', "Cannot drop more than what is in the project. Available in project: {$mat['qty']}");
                }
                $materials[$index]['qty'] -= (int)$request->qty;
                $found = true;
                break;
            }
        }

        if (!$found) return redirect()->route('pos')->with('error', 'Product not found in project for this source.');

        $materials = array_values(array_filter($materials, fn($m) => $m['qty'] > 0));

        if (empty($materials)) {
            return $this->removeFromCart($projectId);
        }

        return $this->_processProjectUpdate($projectId, $materials);
    }

    public function updateProjectInline(Request $request, $projectId)
    {
        $cart = session()->get('cart', []);
        if (!isset($cart[$projectId]) || $cart[$projectId]['type'] !== 'project') {
            return redirect()->route('pos')->with('error', 'Project not found in cart.');
        }

        $inputMaterials = $request->input('materials', []);
        if (empty($inputMaterials)) {
            return redirect()->route('pos')->with('error', 'A project must have at least one material.');
        }

        $materials = [];
        foreach ($inputMaterials as $mat) {
            $product = Product::find($mat['product_id']);
            if (!$product) continue;
            
            $materials[] = [
                'product_id' => $product->id,
                'name' => $product->product_name,
                'qty' => (int)$mat['quantity'],
                'price' => (float)$product->selling_price,
                'subtotal' => 0,
                'source' => $mat['source'],
            ];
        }

        return $this->_processProjectUpdate($projectId, $materials);
    }

    private function _processProjectUpdate($projectId, $materials)
    {
        $cart = session()->get('cart', []);
        try {
            $reserved = $this->getCartReservedQuantities($cart, $projectId);
            $materialsBreakdown = [];
            $materialsSubtotal = 0;

            foreach ($materials as $mat) {
                $product = Product::findOrFail($mat['product_id']);
                $qty = $mat['qty'];
                $source = $mat['source'];
                
                if ($qty <= 0) continue;

                $availableStore = (int) $product->store_quantity - ($reserved['store'][$product->id] ?? 0);
                $availableWh = (int) $product->warehouse_quantity - ($reserved['warehouse'][$product->id] ?? 0);

                if ($source === 'store' && $availableStore < $qty) {
                    throw new \RuntimeException("Not enough stock in STORE for {$product->product_name}. Available: {$availableStore}");
                }
                if ($source === 'warehouse' && $availableWh < $qty) {
                    throw new \RuntimeException("Not enough stock in WAREHOUSE for {$product->product_name}. Available: {$availableWh}");
                }

                $lineTotal = $qty * $product->selling_price;
                $materialsSubtotal += $lineTotal;

                $materialsBreakdown[] = [
                    'product_id' => $product->id,
                    'name' => $product->product_name,
                    'qty' => $qty,
                    'price' => (float) $product->selling_price,
                    'subtotal' => $lineTotal,
                    'source' => $source,
                ];
            }

            if (empty($materialsBreakdown)) throw new \RuntimeException("Project must contain valid materials.");

            $outsource = (float) ($cart[$projectId]['project_breakdown']['outsource_cost'] ?? 0);
            $labor = $cart[$projectId]['project_breakdown']['labor_cost'];
            $install = $cart[$projectId]['project_breakdown']['installation_cost'];
            $additional = $cart[$projectId]['project_breakdown']['additional_cost'];
            $tax = $cart[$projectId]['project_breakdown']['tax'];
            $freight = $cart[$projectId]['project_breakdown']['freight'];
            $markupType = $cart[$projectId]['project_breakdown']['markup_type'];
            $markupValue = $cart[$projectId]['project_breakdown']['markup_value'];

            $projectSubtotal = $materialsSubtotal + $outsource + $labor + $install + $additional + $tax + $freight;
            $markupAmount = $markupType === 'percentage' ? ($projectSubtotal * ($markupValue / 100)) : $markupValue;
            $finalTotal = $projectSubtotal + $markupAmount;

            $cart[$projectId]['selling_price'] = $finalTotal;
            $cart[$projectId]['subtotal'] = $finalTotal;
            $cart[$projectId]['project_breakdown']['materials'] = $materialsBreakdown;
            $cart[$projectId]['project_breakdown']['materials_subtotal'] = $materialsSubtotal;
            $cart[$projectId]['project_breakdown']['project_subtotal'] = $projectSubtotal;
            $cart[$projectId]['project_breakdown']['markup_amount'] = $markupAmount;
            $cart[$projectId]['project_breakdown']['final_total'] = $finalTotal;

            session()->put('cart', $cart);
            return redirect()->route('pos')->with('success', 'Project materials updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('pos')->with('error', $e->getMessage());
        }
    }

    public function updateProjectInCart(Request $request, $projectId)
    {
        $cart = session()->get('cart', []);
        if (!isset($cart[$projectId]) || $cart[$projectId]['type'] !== 'project') {
            return redirect()->route('pos')->with('error', 'Project not found in cart.');
        }

        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'project_image' => 'nullable|image|max:2048',
            'materials' => 'required|array|min:1',
            'materials.*.product_id' => 'required|exists:products,id',
            'materials.*.quantity' => 'required|integer|min:1',
            'materials.*.source' => 'required|in:store,warehouse',
            'outsource_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'installation_cost' => 'nullable|numeric|min:0',
            'additional_cost' => 'nullable|numeric|min:0',
            'project_tax' => 'nullable|numeric|min:0',
            'project_freight' => 'nullable|numeric|min:0',
            'markup_type' => 'required|in:percentage,amount',
            'markup_value' => 'nullable|numeric|min:0',
        ]);

        try {
            $reserved = $this->getCartReservedQuantities($cart, $projectId);
            $materialsBreakdown = [];
            $materialsSubtotal = 0;

            foreach ($data['materials'] as $mat) {
                $product = Product::findOrFail($mat['product_id']);
                $qty = (int) $mat['quantity'];
                $source = $mat['source'];
                
                $availableStore = (int) $product->store_quantity - ($reserved['store'][$product->id] ?? 0);
                $availableWh = (int) $product->warehouse_quantity - ($reserved['warehouse'][$product->id] ?? 0);

                if ($source === 'store' && $availableStore < $qty) {
                    throw new \RuntimeException("Not enough stock in STORE for {$product->product_name}. Available: {$availableStore}");
                }
                if ($source === 'warehouse' && $availableWh < $qty) {
                    throw new \RuntimeException("Not enough stock in WAREHOUSE for {$product->product_name}. Available: {$availableWh}");
                }

                $lineTotal = $qty * $product->selling_price;
                $materialsSubtotal += $lineTotal;

                $materialsBreakdown[] = [
                    'product_id' => $product->id,
                    'name' => $product->product_name,
                    'qty' => $qty,
                    'price' => (float) $product->selling_price,
                    'subtotal' => $lineTotal,
                    'source' => $source,
                ];
            }

            $outsource = (float) ($data['outsource_cost'] ?? 0);
            $labor = (float) ($data['labor_cost'] ?? 0);
            $install = (float) ($data['installation_cost'] ?? 0);
            $additional = (float) ($data['additional_cost'] ?? 0);
            $tax = (float) ($data['project_tax'] ?? 0);
            $freight = (float) ($data['project_freight'] ?? 0);
            $markupType = $data['markup_type'];
            $markupValue = (float) ($data['markup_value'] ?? 0);

            $projectSubtotal = $materialsSubtotal + $outsource + $labor + $install + $additional + $tax + $freight;
            $markupAmount = $markupType === 'percentage' ? ($projectSubtotal * ($markupValue / 100)) : $markupValue;
            $finalTotal = $projectSubtotal + $markupAmount;

            $imagePath = $cart[$projectId]['project_image'];
            if ($request->hasFile('project_image')) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $imagePath = $request->file('project_image')->store('projects', 'public');
            }

            $cart[$projectId] = [
                'type' => 'project',
                'product_name' => 'PROJECT: ' . $data['project_name'],
                'selling_price' => $finalTotal,
                'quantity' => 1,
                'item_discount_type' => 'none',
                'item_discount_value' => 0,
                'item_discount_amount' => 0,
                'subtotal' => $finalTotal,
                'project_image' => $imagePath,
                'project_description' => $data['project_description'] ?? null,
                'project_breakdown' => [
                    'materials' => $materialsBreakdown,
                    'materials_subtotal' => $materialsSubtotal,
                    'outsource_cost' => $outsource,
                    'labor_cost' => $labor,
                    'installation_cost' => $install,
                    'additional_cost' => $additional,
                    'tax' => $tax,
                    'freight' => $freight,
                    'markup_type' => $markupType,
                    'markup_value' => $markupValue,
                    'markup_amount' => $markupAmount,
                    'project_subtotal' => $projectSubtotal,
                    'final_total' => $finalTotal,
                ],
            ];

            session()->put('cart', $cart);
            return redirect()->route('pos')->with('success', 'Custom Project updated successfully.');

        } catch (\RuntimeException $e) {
            return redirect()->route('pos')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('pos')->with('error', 'Error updating project: ' . $e->getMessage());
        }
    }

    public function addToCart(Request $request, $id)
    {
        $action = $request->input('action');
        $request->validate(['action' => ['nullable', 'in:increase,decrease']]);

        if ($action === 'increase' || $action === 'decrease') {
            $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
            $inputQty = (int) $validated['quantity'];
        } elseif ($request->has('exact_quantity')) {
            $validated = $request->validate(['exact_quantity' => ['required', 'integer', 'min:0']]);
            $inputQty = (int) $validated['exact_quantity'];
        } else {
            return redirect()->route('pos')->with('error', 'Invalid quantity action.');
        }

        $cart = session()->get('cart', []);

        try {
            $product = Product::findOrFail($id);
            $currentCartQty = isset($cart[$id]) ? (int) $cart[$id]['quantity'] : 0;

            if ($action === 'increase') {
                $newCartQty = $currentCartQty + $inputQty;
            } elseif ($action === 'decrease') {
                $newCartQty = max(0, $currentCartQty - $inputQty);
            } else {
                $newCartQty = $inputQty;
            }

            if ($newCartQty > 0) {
                $reserved = $this->getCartReservedQuantities($cart);
                $availableStore = (int) $product->store_quantity - ($reserved['store'][$id] ?? 0) + $currentCartQty;

                if ($newCartQty > $availableStore) {
                    throw new \RuntimeException('Not enough stock for ' . $product->product_name . '. Available: ' . max(0, $availableStore));
                }
            }

            if ($newCartQty === 0) {
                unset($cart[$id]);
            } else {
                if (isset($cart[$id])) {
                    $cart[$id]['quantity'] = $newCartQty;
                } else {
                    $generatedItemSku = $product->item_sku ?: $product->product_sku . '-' . strtoupper(substr(uniqid(), -5));

                    $cart[$id] = [
                        'type' => 'product',
                        'product_name' => $product->product_name,
                        'product_sku' => $product->product_sku,
                        'item_sku' => $generatedItemSku,
                        'selling_price' => $product->selling_price,
                        'quantity' => $newCartQty,
                        'item_discount_type' => 'none',
                        'item_discount_value' => 0,
                        'item_discount_scope' => 'whole',
                        'item_discount_amount' => 0,
                        'subtotal' => $newCartQty * $product->selling_price,
                        'source' => 'store',
                    ];
                }
            }

            session()->put('cart', $cart);
        } catch (\RuntimeException $e) {
            return redirect()->route('pos')->with('error', $e->getMessage());
        }

        return redirect()->route('pos');
    }

    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);
        if (!isset($cart[$id])) return redirect()->route('pos');

        if (($cart[$id]['type'] ?? 'product') === 'project' && !empty($cart[$id]['project_image'])) {
            Storage::disk('public')->delete($cart[$id]['project_image']);
        }

        unset($cart[$id]);
        session()->put('cart', $cart);
        
        return redirect()->route('pos');
    }

    public function clearCart()
    {
        $cart = session()->get('cart', []);
        
        foreach ($cart as $item) {
            if (($item['type'] ?? 'product') === 'project' && !empty($item['project_image'])) {
                Storage::disk('public')->delete($item['project_image']);
            }
        }

        session()->forget(['cart', 'cart_discount']);
        return redirect()->route('pos')->with('success', 'Cart cleared successfully.');
    }

    public function holdOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->route('pos')->with('error', 'Cannot hold an empty order.');

        $cartDiscount = session()->get('cart_discount', ['type' => 'none', 'value' => 0, 'mode' => 'percentage']);
        $parkedOrders = session()->get('parked_orders', []);

        $parkedOrders[] = [
            'id' => 'HOLD-' . strtoupper(substr(uniqid(), -6)),
            'time' => now()->format('M d, h:i A'),
            'cart' => $cart,
            'cart_discount' => $cartDiscount,
            'item_count' => array_sum(array_column($cart, 'quantity')),
        ];

        session()->put('parked_orders', $parkedOrders);
        session()->forget(['cart', 'cart_discount']);

        return redirect()->route('pos', ['show_parked' => 1])->with('success', 'Order successfully parked.');
    }

    public function restoreParkedOrder($index)
    {
        $parkedOrders = session()->get('parked_orders', []);
        if (!array_key_exists($index, $parkedOrders)) return redirect()->route('pos')->with('error', 'Parked order not found.');

        $parkedOrder = $parkedOrders[$index];
        $currentCart = session()->get('cart', []);

        foreach ($currentCart as $item) {
            if (($item['type'] ?? 'product') === 'project' && !empty($item['project_image'])) {
                Storage::disk('public')->delete($item['project_image']);
            }
        }

        session()->put('cart', $parkedOrder['cart'] ?? []);
        session()->put('cart_discount', $parkedOrder['cart_discount'] ?? ['type' => 'none', 'value' => 0, 'mode' => 'percentage']);
        
        unset($parkedOrders[$index]);
        session()->put('parked_orders', array_values($parkedOrders));

        return redirect()->route('pos')->with('success', 'Parked order successfully restored.');
    }

    public function addParkedOrder($index)
    {
        $parkedOrders = session()->get('parked_orders', []);
        if (!array_key_exists($index, $parkedOrders)) return redirect()->route('pos', ['show_parked' => 1])->with('error', 'Parked order not found.');

        $parkedOrder = $parkedOrders[$index];
        $parkedCart = $parkedOrder['cart'] ?? [];
        $currentCart = session()->get('cart', []);

        foreach ($parkedCart as $id => $parkedItem) {
            if (isset($currentCart[$id]) && ($currentCart[$id]['type'] ?? 'product') === 'product' && ($parkedItem['type'] ?? 'product') === 'product') {
                $currentCart[$id]['quantity'] += (int) $parkedItem['quantity'];
                $currentCart[$id]['subtotal'] += $parkedItem['subtotal'];
            } else {
                if (($parkedItem['type'] ?? 'product') === 'project' && isset($currentCart[$id])) {
                    $newId = 'project_' . uniqid();
                    $currentCart[$newId] = $parkedItem;
                } else {
                    $currentCart[$id] = $parkedItem;
                }
            }
        }

        $currentDiscount = session()->get('cart_discount', ['type' => 'none', 'value' => 0, 'mode' => 'percentage']);
        if (($currentDiscount['type'] ?? 'none') === 'none' && !empty($parkedOrder['cart_discount']) && ($parkedOrder['cart_discount']['type'] ?? 'none') !== 'none') {
            session()->put('cart_discount', $parkedOrder['cart_discount']);
        }

        session()->put('cart', $currentCart);
        unset($parkedOrders[$index]);
        session()->put('parked_orders', array_values($parkedOrders));

        return redirect()->route('pos')->with('success', 'Parked order items successfully added to active cart.');
    }

    public function deleteParkedOrder($index)
    {
        $parkedOrders = session()->get('parked_orders', []);
        if (!array_key_exists($index, $parkedOrders)) return redirect()->route('pos', ['show_parked' => 1])->with('error', 'Parked order not found.');

        $parked = $parkedOrders[$index];

        foreach (($parked['cart'] ?? []) as $item) {
            if (($item['type'] ?? 'product') === 'project' && !empty($item['project_image'])) {
                Storage::disk('public')->delete($item['project_image']);
            }
        }

        unset($parkedOrders[$index]);
        session()->put('parked_orders', array_values($parkedOrders));

        return redirect()->route('pos', ['show_parked' => 1])->with('success', 'Parked order deleted.');
    }

    public function applyDiscount(Request $request)
    {
        $data = $request->validate([
            'discount_type' => ['required', 'string', 'in:senior,pwd,employee,voucher,percentage,amount,none'],
            'customer_name' => ['nullable', 'string'],
            'id_number' => ['nullable', 'string'],
            'tin_number' => ['nullable', 'string'],
            'employee_name' => ['nullable', 'string'],
            'employee_id' => ['nullable', 'string'],
            'voucher_code' => ['nullable', 'string'],
            'percentage_value' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'amount_value' => ['nullable', 'numeric', 'min:0'],
            'employee_mode' => ['nullable', 'string', 'in:percentage,amount'],
            'employee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_amount' => ['nullable', 'numeric', 'min:0'],
            'voucher_mode' => ['nullable', 'string', 'in:percentage,amount'],
            'voucher_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'voucher_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $type = $data['discount_type'];
        if ($type === 'none') {
            session()->forget('cart_discount');
            return redirect()->route('pos');
        }

        $value = 0;
        $mode = 'percentage';

        if ($type === 'percentage') {
            $value = $data['percentage_value'] ?? 0;
        } elseif ($type === 'amount') {
            $value = $data['amount_value'] ?? 0;
        } elseif ($type === 'employee') {
            $mode = $data['employee_mode'] ?? 'percentage';
            $value = $mode === 'percentage' ? ($data['employee_percentage'] ?? 0) : ($data['employee_amount'] ?? 0);
        } elseif ($type === 'voucher') {
            $mode = $data['voucher_mode'] ?? 'percentage';
            $value = $mode === 'percentage' ? ($data['voucher_percentage'] ?? 0) : ($data['voucher_amount'] ?? 0);
        }

        session()->put('cart_discount', array_merge($data, ['type' => $type, 'value' => $value, 'mode' => $mode]));
        return redirect()->route('pos');
    }

    public function applyItemDiscount(Request $request, $id)
    {
        $data = $request->validate([
            'item_discount_type' => ['required', 'string', 'in:percentage,amount,none'],
            'item_discount_value' => ['nullable', 'numeric', 'min:0'],
            'item_discount_scope' => ['nullable', 'string', 'in:whole,per_item'],
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['item_discount_type'] = $data['item_discount_type'];
            $cart[$id]['item_discount_value'] = (float) ($data['item_discount_value'] ?? 0);
            $cart[$id]['item_discount_scope'] = $data['item_discount_scope'] ?? 'whole';
            session()->put('cart', $cart);
        }

        return redirect()->route('pos');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->route('pos');

        $rawSubtotal = 0;
        $totalItemDiscounts = 0;
        $discountedItemsList = [];

        foreach ($cart as $id => $item) {
            $baseItemSubtotal = (float) $item['selling_price'] * (int) $item['quantity'];
            $rawSubtotal += $baseItemSubtotal;

            $itemDiscountAmount = $this->calculateItemDiscount($item);
            $cart[$id]['item_discount_amount'] = $itemDiscountAmount;
            $cart[$id]['subtotal'] = max(0, $baseItemSubtotal - $itemDiscountAmount);
            $totalItemDiscounts += $itemDiscountAmount;

            if (($item['item_discount_type'] ?? 'none') !== 'none' && $itemDiscountAmount > 0) {
                $discountedItemsList[] = $cart[$id];
            }
        }

        session()->put('cart', $cart);
        $subtotal = max(0, $rawSubtotal - $totalItemDiscounts);
        $cartDiscount = session()->get('cart_discount', ['type' => 'none', 'value' => 0, 'mode' => 'percentage']);
        $discountAmount = $this->calculateCartDiscount($subtotal, $cartDiscount);
        $total = max(0, $subtotal - $discountAmount);
        $totalAllDiscounts = $totalItemDiscounts + $discountAmount;

        $vatRate = 0.12;
        $vatAmount = $total * $vatRate;
        $grandTotal = $total + $vatAmount;

        return view('pos.checkout', compact(
            'cart', 'rawSubtotal', 'subtotal', 'totalItemDiscounts', 
            'discountAmount', 'total', 'cartDiscount', 'discountedItemsList', 'totalAllDiscounts',
            'vatAmount', 'grandTotal'
        ));
    }

    public function processCheckout(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->route('pos')->with('error', 'Your cart is empty.');

        $rawSubtotal = 0;
        $totalItemDiscounts = 0;

        foreach ($cart as $item) {
            $baseItemSubtotal = (float) $item['selling_price'] * (int) $item['quantity'];
            $rawSubtotal += $baseItemSubtotal;
            $totalItemDiscounts += $this->calculateItemDiscount($item);
        }

        $subtotal = max(0, $rawSubtotal - $totalItemDiscounts);
        $cartDiscount = session()->get('cart_discount', ['type' => 'none', 'value' => 0, 'mode' => 'percentage']);
        $discountAmount = $this->calculateCartDiscount($subtotal, $cartDiscount);
        $total = max(0, $subtotal - $discountAmount);
        
        $vatRate = 0.12;
        $vatAmount = $total * $vatRate;
        $grandTotal = $total + $vatAmount;

        $data = $request->validate(['amount_paid' => ['required', 'numeric', 'min:' . $grandTotal]]);
        $amountPaid = (float) $data['amount_paid'];
        $change = $amountPaid - $grandTotal;
        $transactionId = 'POS-' . now()->format('YmdHis') . '-' . uniqid();
        
        $user = Auth::user();

        try {
            $transaction = DB::transaction(function () use (
                $cart, $grandTotal, $amountPaid, $change, $transactionId, $cartDiscount, $discountAmount, $user
            ) {
                $requiredQuantities = ['store' => [], 'warehouse' => []];
                foreach ($cart as $id => $item) {
                    if (($item['type'] ?? 'product') === 'project') {
                        foreach ($item['project_breakdown']['materials'] as $mat) {
                            $requiredQuantities[$mat['source']][$mat['product_id']] = 
                                ($requiredQuantities[$mat['source']][$mat['product_id']] ?? 0) + (int) $mat['qty'];
                        }
                    } else {
                        $requiredQuantities['store'][$id] = 
                            ($requiredQuantities['store'][$id] ?? 0) + (int) $item['quantity'];
                    }
                }

                foreach ($requiredQuantities as $source => $products) {
                    foreach ($products as $productId => $reqQty) {
                        $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
                        if ($source === 'store' && $product->store_quantity < $reqQty) {
                            throw new \Exception("Stock for {$product->product_name} in STORE changed. Available: {$product->store_quantity}. Please adjust cart.");
                        }
                        if ($source === 'warehouse' && $product->warehouse_quantity < $reqQty) {
                            throw new \Exception("Stock for {$product->product_name} in WAREHOUSE changed. Available: {$product->warehouse_quantity}. Please adjust cart.");
                        }
                    }
                }

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'cashier_name' => $user->firstname . ' ' . $user->lastname,
                    'transaction_id' => $transactionId,
                    'transaction_type' => 0,
                    'total_amount' => $grandTotal,
                    'amount_paid' => $amountPaid,
                    'amount_change' => $change,
                    'cart_discount_type' => $cartDiscount['type'] ?? 'none',
                    'cart_discount_value' => $discountAmount,
                    'customer_name' => $cartDiscount['customer_name'] ?? null,
                ]);

                foreach ($cart as $id => $item) {
                    if (($item['type'] ?? 'product') === 'project') {
                        $projectItem = TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'item_type' => 'project',
                            'project_image' => $item['project_image'] ?? null,
                            'project_breakdown' => $item['project_breakdown'], // Snapshot for receipt
                            'product_id' => null,
                            'product_sku' => null,
                            'product_name' => $item['product_name'],
                            'selling_price' => $item['selling_price'],
                            'quantity' => 1,
                            'subtotal' => $item['subtotal'],
                        ]);

                        foreach ($item['project_breakdown']['materials'] as $mat) {
                            $product = Product::whereKey($mat['product_id'])->lockForUpdate()->firstOrFail();
                            $qty = (int) $mat['qty'];
                            $unitPrice = (float) $mat['price'];

                            // Allocate real serialized unit SKUs from product_item_skus (FIFO)
                            $allocatedSkus = app(\App\Services\ItemSkuService::class)->allocateForSale(
                                $product,
                                $qty,
                                $mat['source'] ?? 'store',
                                $transaction->id,
                                $projectItem->id
                            );

                            // Expand each unit into its own individual record with its exact serialized item_sku
                            for ($i = 0; $i < $qty; $i++) {
                                $matItemSku = $allocatedSkus[$i] ?? ($product->product_sku . '-' . strtoupper(substr(uniqid(), -5)));

                                TransactionItemMaterial::create([
                                    'transaction_item_id' => $projectItem->id,
                                    'product_id'          => $mat['product_id'],
                                    'product_sku'         => $product->product_sku,
                                    'item_sku'            => $matItemSku,
                                    'product_name'        => $mat['name'],
                                    'quantity'            => 1,
                                    'price'               => $unitPrice,
                                    'subtotal'            => $unitPrice,
                                    'source'              => $mat['source'],
                                ]);
                            }
                            
                            if ($mat['source'] === 'store') {
                                $product->decrement('store_quantity', $qty);
                            } else {
                                $product->decrement('warehouse_quantity', $qty);
                            }

                            SalesRecord::create([
                                'product_id' => $mat['product_id'],
                                'transaction_id' => $transaction->id,
                                'record_date' => now()->toDateString(),
                                'quantity' => $qty,
                                'source' => 'pos',
                            ]);
                        }

                    } else {
                        $product = Product::whereKey($id)->lockForUpdate()->firstOrFail();
                        $totalQuantity = (int) $item['quantity'];
                        $unitPrice = (float) $item['selling_price'];
                        $discType = $item['item_discount_type'] ?? 'none';
                        $discVal = (float) ($item['item_discount_value'] ?? 0);
                        $discScope = $item['item_discount_scope'] ?? 'whole';
                        
                        $product->decrement('store_quantity', $totalQuantity);
                        
                        $itemDiscountAmount = 0;
                        if ($discType === 'percentage' && $discVal > 0) {
                            $itemDiscountAmount = ($unitPrice * $totalQuantity) * ($discVal / 100);
                        } elseif ($discType === 'amount' && $discVal > 0) {
                            if ($discScope === 'per_item') {
                                $itemDiscountAmount = min($unitPrice * $totalQuantity, $discVal * $totalQuantity);
                            } else {
                                $itemDiscountAmount = min($unitPrice * $totalQuantity, $discVal);
                            }
                        }

                        $lineSubtotal = max(0, ($unitPrice * $totalQuantity) - $itemDiscountAmount);

                        $skuService = app(\App\Services\ItemSkuService::class);
                        $txItem = TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'item_type' => 'product',
                            'product_id' => $product->id,
                            'product_sku' => $product->product_sku,
                            'item_sku' => $item['item_sku'] ?? ($product->product_sku . '-PENDING'),
                            'product_name' => $product->product_name,
                            'selling_price' => $unitPrice,
                            'quantity' => $totalQuantity,
                            'subtotal' => $lineSubtotal,
                            'item_discount_type' => ($discType !== 'none' && $discVal > 0) ? $discType : null,
                            'item_discount_value' => $discVal,
                            'item_discount_scope' => ($discType !== 'none' && $discVal > 0) ? $discScope : null,
                        ]);

                        $allocatedSkus = $skuService->allocateForSale(
                            $product,
                            $totalQuantity,
                            'store',
                            $transaction->id,
                            $txItem->id
                        );
                        $txItem->update([
                            'item_sku' => $skuService->formatAssignedSkuLabel($allocatedSkus, $product),
                        ]);

                        SalesRecord::create([
                            'product_id' => $product->id,
                            'transaction_id' => $transaction->id,
                            'record_date' => now()->toDateString(),
                            'quantity' => $totalQuantity,
                            'source' => 'pos',
                        ]);
                    }
                }

                return $transaction;
            });
            
            session()->forget(['cart', 'cart_discount', 'completed_transaction_id']);
            return redirect()->route('receipt', $transaction->id);

        } catch (\Exception $e) {
            return redirect()->route('checkout')->with('error', $e->getMessage());
        }
    }

    /**
     * Synchronize transactions recorded during offline network disconnection.
     */
    public function syncOfflineOrders(Request $request)
    {
        $orders = $request->input('orders', []);
        if (empty($orders) || !is_array($orders)) {
            return response()->json(['success' => false, 'message' => 'No offline orders provided.'], 400);
        }

        $user = Auth::user();
        $demandService = app(DemandService::class);
        $syncedCount = 0;
        $syncedInvoices = [];
        $errors = [];

        foreach ($orders as $index => $orderData) {
            $transactionId = (string) ($orderData['invoice_number'] ?? ('OFFLINE-' . now()->format('YmdHis') . '-' . uniqid()));
            try {
                DB::transaction(function () use ($orderData, $user, $demandService, $transactionId, &$syncedCount, &$syncedInvoices) {
                    $items = $orderData['items'] ?? [];
                    if (empty($items) || !is_array($items)) {
                        return;
                    }

                    // Idempotency guard: if this offline invoice was already synced, mark it done without duplicating
                    if (Transaction::where('transaction_id', $transactionId)->exists()) {
                        $syncedInvoices[] = $transactionId;
                        $syncedCount++;
                        return;
                    }

                    $grandTotal = (float) ($orderData['total_amount'] ?? 0);
                    $amountPaid = (float) ($orderData['amount_paid'] ?? $grandTotal);
                    $change = max(0, round($amountPaid - $grandTotal, 2));
                    $recordDate = !empty($orderData['created_at'])
                        ? \Illuminate\Support\Carbon::parse($orderData['created_at'])->toDateString()
                        : now()->toDateString();

                    $transaction = Transaction::create([
                        'user_id' => $user->id,
                        'cashier_name' => $user->firstname . ' ' . $user->lastname,
                        'transaction_id' => $transactionId,
                        'transaction_type' => 0,
                        'total_amount' => $grandTotal,
                        'amount_paid' => $amountPaid,
                        'amount_change' => $change,
                        'cart_discount_type' => $orderData['cart_discount_type'] ?? 'none',
                        'cart_discount_value' => (float) ($orderData['cart_discount_value'] ?? 0),
                        'customer_name' => $orderData['customer_name'] ?? 'Walk-in Customer (Offline)',
                    ]);

                    foreach ($items as $item) {
                        $productId = $item['id'] ?? null;
                        if (!$productId) {
                            continue;
                        }

                        $product = Product::whereKey($productId)->lockForUpdate()->first();
                        if (!$product) {
                            continue;
                        }

                        $qty = max(1, (int) ($item['quantity'] ?? 1));
                        $unitPrice = (float) ($item['selling_price'] ?? $product->selling_price);
                        $lineSubtotal = isset($item['subtotal'])
                            ? (float) $item['subtotal']
                            : round($unitPrice * $qty, 2);

                        // Deduct store stock safely (minimum 0)
                        $beforeQty = (int) $product->store_quantity;
                        $afterQty = max(0, $beforeQty - $qty);
                        $product->store_quantity = $afterQty;
                        $product->save();

                        StockMovement::create([
                            'batch_id' => $transactionId,
                            'product_id' => $product->id,
                            'requested_by' => $user->id,
                            'requester_name' => $user->firstname . ' ' . $user->lastname,
                            'approved_by' => $user->id,
                            'approver_name' => $user->firstname . ' ' . $user->lastname,
                            'type' => 'sale_offline',
                            'status' => 'approved',
                            'delivery_status' => 'delivered',
                            'quantity' => $qty,
                            'quantity_before' => $beforeQty,
                            'quantity_after' => $afterQty,
                            'note' => 'Synced from offline cashier queue (' . $transactionId . ')',
                            'approved_at' => now(),
                        ]);

                        $skuService = app(\App\Services\ItemSkuService::class);
                        $txItem = TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'item_type' => 'product',
                            'product_id' => $product->id,
                            'product_sku' => $product->product_sku,
                            'item_sku' => $product->product_sku . '-PENDING',
                            'product_name' => $product->product_name,
                            'selling_price' => $unitPrice,
                            'quantity' => $qty,
                            'subtotal' => $lineSubtotal,
                        ]);

                        $allocatedSkus = $skuService->allocateForSale($product, $qty, 'store', $transaction->id, $txItem->id);
                        $txItem->update(['item_sku' => $skuService->formatAssignedSkuLabel($allocatedSkus, $product)]);

                        $saleRecord = SalesRecord::create([
                            'product_id' => $product->id,
                            'transaction_id' => $transaction->id,
                            'record_date' => $recordDate,
                            'quantity' => $qty,
                            'source' => 'pos',
                        ]);

                        $demandService->refreshForSale($saleRecord);
                    }

                    $syncedInvoices[] = $transactionId;
                    $syncedCount++;
                });
            } catch (\Throwable $e) {
                report($e);
                $errors[] = "Order {$transactionId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => count($syncedInvoices) > 0,
            'synced_count' => $syncedCount,
            'synced_invoices' => $syncedInvoices,
            'errors' => $errors,
        ], count($syncedInvoices) > 0 ? 200 : 422);
    }
}
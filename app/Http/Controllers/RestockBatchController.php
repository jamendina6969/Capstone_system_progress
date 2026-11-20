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
use Illuminate\Support\Str;

class RestockBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $searchTerms = array_filter(explode(' ', trim($request->search)));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where('product_name', 'like', '%' . $term . '%');
                }
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('product_name')->get();
        $categories = Category::orderBy('name')->get();

        $user = Auth::user();
        $location = $user->role === 'warehouse_staff' ? 'warehouse' : 'store';

        // Exit target: admins/managers/warehouse_staff go back to Inventory Management; other roles use the shared inventory view.
        $exitUrl = in_array(strtolower(trim($user->role)), ['admin', 'manager', 'warehouse_staff'], true)
            ? route('inventory.index')
            : route('inventory.view');

        return view('inventory.restockBatch', compact('products', 'categories', 'location', 'exitUrl'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'quantities'      => ['required', 'array'],
            'quantities.*'    => ['nullable', 'integer', 'min:0'],
            'locations'       => ['required', 'array'],
            'locations.*'     => ['required', 'in:store,warehouse'],
            'note'            => ['nullable', 'string', 'max:500'],
            'action_password' => ['nullable', 'string'],
        ]);

        // Build a clean map of only the rows with qty > 0
        $entries = [];
        foreach ($data['quantities'] as $productId => $qty) {
            if ($qty === null || (int) $qty <= 0) {
                continue;
            }

            $loc = $data['locations'][$productId] ?? null;
            if (!$loc) {
                continue;
            }

            $entries[$productId] = [
                'qty'      => (int) $qty,
                'location' => $loc,
            ];
        }

        if (empty($entries)) {
            return back()->with('error', 'Enter at least one quantity to restock.');
        }

        // Strict Role Enforcement & Password Validation
        $role = strtolower(trim($user->role));
        
        // Admins and Managers bypass all restrictions and do not need an action password
        if (in_array($role, ['admin', 'manager'])) {
            $requiresApproval = false;
        } else {
            $needsOverride = false;
            $restrictedAction = '';

            if ($role === 'warehouse_staff') {
                foreach ($entries as $entry) {
                    if ($entry['location'] === 'store') {
                        $needsOverride = true;
                        $restrictedAction = 'Warehouse staff cannot restock directly to Store.';
                        break;
                    }
                }
            } elseif ($role === 'staff') {
                foreach ($entries as $entry) {
                    if ($entry['location'] === 'warehouse') {
                        $needsOverride = true;
                        $restrictedAction = 'Staff cannot restock directly to Warehouse.';
                        break;
                    }
                }
            }

            $submittedPassword = trim($request->input('action_password', ''));
            $isPasswordValid = false;

            // 1. If ANY password is typed in, verify it against Admins/Managers ONLY
            if (!empty($submittedPassword)) {
                $authorizedUsers = User::whereIn('role', ['admin', 'manager'])
                    ->whereNotNull('action_password')
                    ->get();
                    
                foreach ($authorizedUsers as $authUser) {
                    if (Hash::check($submittedPassword, $authUser->action_password)) {
                        $isPasswordValid = true;
                        break;
                    }
                }

                // If no Admin/Manager matched, deny immediately
                if (!$isPasswordValid) {
                    return back()->with('error', 'The Admin/Manager Action Password you provided is incorrect.');
                }
            }

            // 2. If they need an override but didn't provide a valid password, block them.
            if ($needsOverride && !$isPasswordValid) {
                return back()->with('error', $restrictedAction . ' A valid Admin/Manager Action Password is required to override this.');
            }

            // 3. If a valid password is provided, it overrides the need for approval.
            $requiresApproval = $role === 'staff' && !$isPasswordValid;
        }
        
        $batchId          = Str::uuid();
        $approverName     = $requiresApproval ? null : $user->firstname . ' ' . $user->lastname;
        $requesterName    = $user->firstname . ' ' . $user->lastname;

        DB::transaction(function () use ($entries, $batchId, $user, $requiresApproval, $data, $approverName, $requesterName) {
            foreach ($entries as $productId => $entry) {
                $product = Product::find($productId);
                if (!$product) {
                    continue;
                }

                $column = $entry['location'] === 'store' ? 'store_quantity' : 'warehouse_quantity';
                $type   = $entry['location'] === 'store' ? 'restock_store'  : 'restock_warehouse';
                $qty    = $entry['qty'];

                $before = (int) ($product->$column ?? 0);
                $after  = $requiresApproval ? $before : $before + $qty;

                StockMovement::create([
                    'batch_id'        => $batchId,
                    'product_id'      => $product->id,
                    'requested_by'    => $user->id,
                    'requester_name'  => $requesterName,
                    'approved_by'     => $requiresApproval ? null : $user->id,
                    'approver_name'   => $approverName,
                    'type'            => $type,
                    'status'          => $requiresApproval ? 'pending' : 'approved',
                    // Direct restocks (admin/manager or authorized override) are applied immediately,
                    // so they are already delivered and must not appear in the approvals queue.
                    'delivery_status' => $requiresApproval ? 'pending' : 'delivered',
                    'quantity_before' => $before,
                    'quantity_after'  => $after,
                    'quantity'        => $qty,
                    'note'            => $data['note'] ?? null,
                    'approved_at'     => $requiresApproval ? null : now(),
                ]);

                if (!$requiresApproval) {
                    $product->increment($column, $qty);
                    app(\App\Services\ItemSkuService::class)->generateUnits($product, (int) $qty, $entry['location']);
                }
            }
        });

        $count = count($entries);
        $message = $requiresApproval
            ? "Restock batch submitted for approval ({$count} item(s))."
            : "Restock batch applied successfully ({$count} item(s)).";

        return back()->with('success', $message);
    }
}
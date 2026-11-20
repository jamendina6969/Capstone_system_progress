<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['user', 'approver', 'items.product'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('requester_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(firstname, ' ', lastname) like ?", ['%' . $search . '%']);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if (in_array(auth()->user()->role, ['staff', 'warehouse_staff'])) {
            $query->where('user_id', auth()->id());
        }

        $requests = $query->paginate(self::PER_PAGE)->withQueryString();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('purchase-requests.partials.rows', compact('requests'))->render(),
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
        
        return view('purchase-requests.index', compact('requests'));
    }

    public function create()
    {
        $products = Product::orderBy('product_name')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        return view('purchase-requests.create', compact('products', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $prNumber = 'PR-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $user = Auth::user();
            
            $pr = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'user_id' => $user->id,
                'requester_name' => $user->firstname . ' ' . $user->lastname,
                'supplier_id' => $request->supplier_id,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            app(\App\Services\NotificationService::class)->notifyRoles(['admin', 'manager'], [
                'type' => 'pr_submitted',
                'category' => 'orders',
                'severity' => 'info',
                'title' => "New Purchase Request {$pr->pr_number}",
                'message' => "{$user->full_name} submitted {$pr->pr_number} for review.",
                'action_url' => route('purchase-requests.index'),
            ], $user->id);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Purchase Request submitted successfully.'], 201);
            }

            return redirect()->route('purchase-requests.index')->with('success', 'Purchase Request submitted for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error creating PR: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error creating PR: ' . $e->getMessage())->withInput();
        }
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::transaction(function () use ($purchaseRequest, $request) {
            $purchaseRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'manager_notes' => $request->manager_notes
            ]);

            $purchaseRequest->items()->where('status', 'pending')->update(['status' => 'approved']);
        });

        if ($purchaseRequest->user_id && (int) $purchaseRequest->user_id !== (int) Auth::id()) {
            app(\App\Services\NotificationService::class)->notifyUser((int) $purchaseRequest->user_id, [
                'type' => 'pr_approved',
                'category' => 'orders',
                'severity' => 'success',
                'title' => "Purchase Request {$purchaseRequest->pr_number} Approved",
                'message' => Auth::user()->full_name . " approved your purchase request {$purchaseRequest->pr_number}.",
                'action_url' => route('purchase-requests.index'),
            ]);
        }

        return back()->with('success', 'Purchase Request approved.');
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::transaction(function () use ($purchaseRequest, $request) {
            $purchaseRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'manager_notes' => $request->manager_notes
            ]);

            $purchaseRequest->items()->where('status', 'pending')->update([
                'status' => 'rejected',
                'rejection_reason' => $request->manager_notes ?? 'Request fully rejected'
            ]);
        });

        if ($purchaseRequest->user_id && (int) $purchaseRequest->user_id !== (int) Auth::id()) {
            app(\App\Services\NotificationService::class)->notifyUser((int) $purchaseRequest->user_id, [
                'type' => 'pr_rejected',
                'category' => 'orders',
                'severity' => 'danger',
                'title' => "Purchase Request {$purchaseRequest->pr_number} Rejected",
                'message' => Auth::user()->full_name . " rejected {$purchaseRequest->pr_number}" . ($request->manager_notes ? ": {$request->manager_notes}" : '.'),
                'action_url' => route('purchase-requests.index'),
            ]);
        }

        return back()->with('success', 'Purchase Request rejected.');
    }

    public function rejectItem(Request $request, PurchaseRequest $purchaseRequest, PurchaseRequestItem $item)
    {
        if ($item->status !== 'pending') {
            return back()->with('error', 'This item has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $item->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($purchaseRequest->items()->where('status', 'pending')->count() === 0 && 
            $purchaseRequest->items()->where('status', 'approved')->count() === 0) {
            $purchaseRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        }

        return back()->with('success', 'Item rejected and removed from the request.');
    }

    public function convertToPO(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be converted to POs.');
        }

        return redirect()->route('purchase-orders.index', [
            'create_modal' => 1,
            'from_pr' => $purchaseRequest->id
        ]);
    }
}
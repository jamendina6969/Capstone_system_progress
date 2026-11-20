<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\StockMovement;
use App\Models\SupplierReturn;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    /**
     * Send a persistent notification to a single active user.
     */
    public function notifyUser(int $userId, array $payload): ?UserNotification
    {
        try {
            if (!Schema::hasTable('user_notifications')) {
                return null;
            }

            return UserNotification::create([
                'user_id'    => $userId,
                'type'       => (string) ($payload['type'] ?? 'general'),
                'category'   => (string) ($payload['category'] ?? 'system'),
                'severity'   => (string) ($payload['severity'] ?? 'info'),
                'title'      => (string) ($payload['title'] ?? 'Notification'),
                'message'    => (string) ($payload['message'] ?? ''),
                'action_url' => $payload['action_url'] ?? null,
                'meta'       => $payload['meta'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Send a persistent notification to all active users matching any of the given roles.
     */
    public function notifyRoles(array $roles, array $payload, ?int $excludeUserId = null): int
    {
        try {
            if (!Schema::hasTable('user_notifications')) {
                return 0;
            }

            $userIds = User::whereIn('role', $roles)
                ->where('is_active', true)
                ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
                ->pluck('id')
                ->all();

            if (empty($userIds)) {
                return 0;
            }

            $now = now();
            $rows = [];
            foreach ($userIds as $uid) {
                $rows[] = [
                    'user_id'    => $uid,
                    'type'       => (string) ($payload['type'] ?? 'general'),
                    'category'   => (string) ($payload['category'] ?? 'system'),
                    'severity'   => (string) ($payload['severity'] ?? 'info'),
                    'title'      => (string) ($payload['title'] ?? 'Notification'),
                    'message'    => (string) ($payload['message'] ?? ''),
                    'action_url' => $payload['action_url'] ?? null,
                    'meta'       => isset($payload['meta']) ? json_encode($payload['meta']) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            UserNotification::insert($rows);
            return count($rows);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Compute real-time actionable queues tailored strictly to the user's RBAC role.
     * These automatically clear the moment the pending work is resolved.
     */
    public function getLiveQueuesForUser(User $user): array
    {
        $role = $user->role;
        $muteLowStock = (bool) $user->getPreference('mute_low_stock', false);

        $queues = [];
        $badges = [
            'prCount'                  => 0,
            'approvalCount'            => 0,
            'incomingDeliveriesCount'  => 0,
            'pendingSupplierReturns'   => 0,
            'discrepancyCount'         => 0,
            'readyToReceiveCount'      => 0,
        ];

        if (in_array($role, ['admin', 'manager'], true)) {
            $prCount = PurchaseRequest::where('status', 'pending')->count();
            $approvalCount = StockMovement::where('status', 'pending')->count();
            $pendingReturns = SupplierReturn::where('status', 'pending')->count();
            $recentShortages = PurchaseOrder::whereIn('status', ['received', 'completed'])
                ->where('discrepancy_amount', '>', 0)
                ->where('received_at', '>=', now()->subDays(7))
                ->count();

            $badges['prCount'] = $prCount;
            $badges['approvalCount'] = $approvalCount;
            $badges['pendingSupplierReturns'] = $pendingReturns;
            $badges['discrepancyCount'] = $recentShortages;

            if ($prCount > 0) {
                $queues[] = [
                    'key'      => 'pending_prs',
                    'title'    => "{$prCount} Pending Purchase " . ($prCount === 1 ? 'Request' : 'Requests'),
                    'message'  => 'Staff purchase requests awaiting review and conversion to PO.',
                    'url'      => route('purchase-requests.index'),
                    'severity' => 'warning',
                    'count'    => $prCount,
                    'category' => 'orders',
                ];
            }

            if ($approvalCount > 0) {
                $queues[] = [
                    'key'      => 'pending_approvals',
                    'title'    => "{$approvalCount} Pending Restock " . ($approvalCount === 1 ? 'Approval' : 'Approvals'),
                    'message'  => 'Inventory transfer/restock requests waiting for manager approval.',
                    'url'      => route('inventory.approvals'),
                    'severity' => 'info',
                    'count'    => $approvalCount,
                    'category' => 'inventory',
                ];
            }

            if ($pendingReturns > 0) {
                $queues[] = [
                    'key'      => 'pending_supplier_returns',
                    'title'    => "{$pendingReturns} Pending Supplier " . ($pendingReturns === 1 ? 'Return' : 'Returns'),
                    'message'  => 'Approve to deduct stock and apply return credit to supplier ledger.',
                    'url'      => route('supplier-returns.index', ['status' => 'pending']),
                    'severity' => 'warning',
                    'count'    => $pendingReturns,
                    'category' => 'returns',
                ];
            }

            if ($recentShortages > 0) {
                $queues[] = [
                    'key'      => 'recent_shortages',
                    'title'    => "{$recentShortages} Delivery " . ($recentShortages === 1 ? 'Discrepancy' : 'Discrepancies') . ' (Last 7d)',
                    'message'  => 'Short deliveries logged from incoming POs — review in Discrepancy Report.',
                    'url'      => route('suppliers.discrepancy'),
                    'severity' => 'danger',
                    'count'    => $recentShortages,
                    'category' => 'orders',
                ];
            }

            if (!$muteLowStock) {
                $lowTotal = Product::whereRaw('(COALESCE(store_quantity,0) + COALESCE(warehouse_quantity,0)) <= COALESCE(reorder_point, 10)')->count();
                if ($lowTotal > 0) {
                    $queues[] = [
                        'key'      => 'low_stock_total',
                        'title'    => "{$lowTotal} " . ($lowTotal === 1 ? 'Product' : 'Products') . ' at Low / Zero Stock',
                        'message'  => 'Total store + warehouse stock is at or below reorder point.',
                        'url'      => route('inventory.index', ['status' => 'total_low']),
                        'severity' => 'danger',
                        'count'    => $lowTotal,
                        'category' => 'inventory',
                    ];
                }
            }
        } elseif ($role === 'warehouse_staff') {
            $incomingPos = PurchaseOrder::where('status', 'pending')->count();
            $readyTransfers = StockMovement::where('requested_by', $user->id)
                ->where('status', 'approved')
                ->where('delivery_status', 'in_transit')
                ->count();

            $badges['incomingDeliveriesCount'] = $incomingPos;
            $badges['readyToReceiveCount'] = $readyTransfers;

            if ($incomingPos > 0) {
                $queues[] = [
                    'key'      => 'incoming_deliveries',
                    'title'    => "{$incomingPos} Incoming PO " . ($incomingPos === 1 ? 'Delivery' : 'Deliveries'),
                    'message'  => 'Purchase orders awaiting physical inspection and GRN receiving.',
                    'url'      => route('purchase-orders.deliveries'),
                    'severity' => 'warning',
                    'count'    => $incomingPos,
                    'category' => 'orders',
                ];
            }

            if ($readyTransfers > 0) {
                $queues[] = [
                    'key'      => 'approved_restocks',
                    'title'    => "{$readyTransfers} Approved " . ($readyTransfers === 1 ? 'Restock' : 'Restocks') . ' Ready',
                    'message'  => 'Confirm receipt in My Restock Requests to update inventory.',
                    'url'      => route('inventory.myRequests'),
                    'severity' => 'success',
                    'count'    => $readyTransfers,
                    'category' => 'inventory',
                ];
            }

            if (!$muteLowStock) {
                $lowWh = Product::whereRaw('COALESCE(warehouse_quantity, 0) <= COALESCE(reorder_point, 10)')->count();
                if ($lowWh > 0) {
                    $queues[] = [
                        'key'      => 'low_stock_warehouse',
                        'title'    => "{$lowWh} Warehouse " . ($lowWh === 1 ? 'Item' : 'Items') . ' Low / Out',
                        'message'  => 'Warehouse stock is at or below reorder level.',
                        'url'      => route('inventory.index', ['status' => 'wh_low']),
                        'severity' => 'danger',
                        'count'    => $lowWh,
                        'category' => 'inventory',
                    ];
                }
            }
        } else {
            // Cashier / staff role
            $readyStoreRestocks = StockMovement::where('requested_by', $user->id)
                ->where('status', 'approved')
                ->where('delivery_status', 'in_transit')
                ->count();

            $badges['readyToReceiveCount'] = $readyStoreRestocks;

            if ($readyStoreRestocks > 0) {
                $queues[] = [
                    'key'      => 'ready_store_restocks',
                    'title'    => "{$readyStoreRestocks} Store " . ($readyStoreRestocks === 1 ? 'Restock' : 'Restocks') . ' Ready to Receive',
                    'message'  => 'Your approved store restock is ready — confirm receipt to stock shelves.',
                    'url'      => route('inventory.myRequests'),
                    'severity' => 'success',
                    'count'    => $readyStoreRestocks,
                    'category' => 'inventory',
                ];
            }

            if (!$muteLowStock) {
                $lowStore = Product::whereRaw('COALESCE(store_quantity, 0) <= COALESCE(reorder_point, 10)')->count();
                if ($lowStore > 0) {
                    $queues[] = [
                        'key'      => 'low_stock_store',
                        'title'    => "{$lowStore} Store Shelf " . ($lowStore === 1 ? 'Item' : 'Items') . ' Low / Out',
                        'message'  => 'Submit a restock request so POS items stay available for customers.',
                        'url'      => route('inventory.myRequests'),
                        'severity' => 'warning',
                        'count'    => $lowStore,
                        'category' => 'inventory',
                    ];
                }
            }
        }

        return [
            'queues' => $queues,
            'badges' => $badges,
        ];
    }

    /**
     * Build the combined topbar notification payload (live queues + personal unread/recent inbox).
     */
    public function getDropdownPayload(User $user, int $inboxLimit = 8): array
    {
        $live = $this->getLiveQueuesForUser($user);
        $queues = $live['queues'];
        $badges = $live['badges'];

        $inbox = collect();
        $unreadInboxCount = 0;

        if (Schema::hasTable('user_notifications')) {
            $unreadInboxCount = UserNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            $inbox = UserNotification::where('user_id', $user->id)
                ->orderByRaw('read_at IS NOT NULL ASC')
                ->latest('created_at')
                ->limit($inboxLimit)
                ->get();
        }

        $queueAlertCount = count($queues);
        $totalBadgeCount = $unreadInboxCount + $queueAlertCount;

        return [
            'total_count'        => $totalBadgeCount,
            'unread_inbox_count' => $unreadInboxCount,
            'queue_count'        => $queueAlertCount,
            'queues'             => $queues,
            'inbox'              => $inbox,
            'badges'             => $badges,
            'toast_enabled'      => (bool) $user->getPreference('toast_alerts', true),
        ];
    }
}

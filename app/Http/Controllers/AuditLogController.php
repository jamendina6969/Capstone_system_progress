<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\ClientContext;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    private const PER_PAGE = 16;

    /**
     * Sensitive or binary fields always redacted when rendering historical or new audit logs.
     */
    private const REDACTED_KEYS = [
        'password',
        'action_password',
        'remember_token',
        'product_image_data',
        'avatar_data',
    ];

    private const IGNORED_KEYS = [
        'id',
        'created_at',
        'updated_at',
        'email_verified_at',
    ];

    public const EVENT_LABELS = [
        'created'                    => 'Created',
        'updated'                    => 'Updated',
        'deleted'                    => 'Deleted',
        'login'                      => 'User Login',
        'logout'                     => 'User Logout',
        'login_failed'               => 'Failed Login',
        'admin_portal_login'         => 'Admin Portal Login',
        'admin_portal_login_failed'  => 'Failed Admin Login',
        'admin_login_blocked_public' => 'Blocked Public Admin Login',
        'force_password_changed'     => 'First-Login Password Changed',
        'password_changed'           => 'Password Changed',
        'action_password_updated'    => 'Override PIN Updated',
        'account_deactivated'        => 'Account Deactivated',
        'account_reactivated'        => 'Account Reactivated',
        'session_revoked'            => 'Device Signed Out',
        'sessions_revoked'           => 'All Other Sessions Revoked',
        'po_received'                => 'PO Received (GRN)',
        'supplier_return_approved'   => 'Supplier Return Approved',
        'customer_return_voided'     => 'Customer Return Voided',
        'backup_created'             => 'Backup Created',
        'backup_restored'            => 'Backup Restored',
    ];

    public const MODULE_MAP = [
        'security' => [
            'events' => [
                'login', 'logout', 'login_failed', 'admin_portal_login',
                'admin_portal_login_failed', 'admin_login_blocked_public',
                'force_password_changed', 'password_changed', 'action_password_updated',
                'account_deactivated', 'account_reactivated', 'session_revoked', 'sessions_revoked',
            ],
        ],
        'users' => [
            'models' => ['App\\Models\\User'],
        ],
        'catalog' => [
            'models' => ['App\\Models\\Product', 'App\\Models\\Category'],
        ],
        'inventory' => [
            'models' => ['App\\Models\\StockMovement', 'App\\Models\\EoqSetting', 'App\\Models\\ForecastSetting', 'App\\Models\\ProductOrderingCost'],
        ],
        'pos' => [
            'models' => ['App\\Models\\Transaction', 'App\\Models\\CustomerReturn'],
            'events' => ['customer_return_voided'],
        ],
        'procurement' => [
            'models' => ['App\\Models\\Supplier', 'App\\Models\\PurchaseOrder', 'App\\Models\\PurchaseRequest', 'App\\Models\\SupplierReturn', 'App\\Models\\SupplierLedger', 'App\\Models\\SupplierRating'],
            'events' => ['po_received', 'supplier_return_approved'],
        ],
        'system' => [
            'events' => ['backup_created', 'backup_restored'],
        ],
    ];

    public static function formatEventLabel(string $event): string
    {
        $key = strtolower(trim($event));
        return self::EVENT_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function eventBadgeClass(string $event): string
    {
        $key = strtolower(trim($event));

        return match (true) {
            in_array($key, ['created', 'po_received', 'supplier_return_approved', 'account_reactivated', 'backup_created'], true)
                => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-400 dark:border-emerald-900',
            in_array($key, ['updated', 'password_changed', 'force_password_changed', 'action_password_updated'], true)
                => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-400 dark:border-blue-900',
            in_array($key, ['deleted', 'login_failed', 'admin_portal_login_failed', 'admin_login_blocked_public', 'account_deactivated', 'customer_return_voided', 'backup_restored'], true)
                => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-400 dark:border-rose-900',
            in_array($key, ['login', 'admin_portal_login'], true)
                => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-400 dark:border-amber-900',
            in_array($key, ['logout', 'session_revoked', 'sessions_revoked'], true)
                => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            default
                => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-400 dark:border-purple-900',
        };
    }

    public static function formatRoleLabel(?string $role): string
    {
        return match ($role) {
            'admin'           => 'Admin',
            'manager'         => 'Manager',
            'warehouse_staff' => 'Warehouse',
            'staff'           => 'Cashier',
            default           => 'System',
        };
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = AuditLog::with('user')
            ->latest('created_at')
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_role', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('role')) {
            $role = $request->role;
            $query->where(function ($q) use ($role) {
                $q->where('actor_role', $role)
                    ->orWhereHas('user', fn ($u) => $u->where('role', $role));
            });
        }

        if ($request->filled('module') && isset(self::MODULE_MAP[$request->module])) {
            $spec = self::MODULE_MAP[$request->module];
            $models = $spec['models'] ?? [];
            $events = $spec['events'] ?? [];

            $query->where(function ($q) use ($models, $events) {
                if (!empty($models)) {
                    $q->whereIn('auditable_type', $models);
                }
                if (!empty($events)) {
                    $method = !empty($models) ? 'orWhereIn' : 'whereIn';
                    $q->{$method}('event', $events);
                }
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $logs = $this->buildFilteredQuery($request)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Decorate current page rows with clean human target, summary, and parsed device
        $logs->getCollection()->transform(fn (AuditLog $log) => $this->decorateRow($log));

        $events = AuditLog::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        if ($request->ajax()) {
            return response()->json([
                'html'          => view('audit-logs.partials.rows', compact('logs'))->render(),
                'next_page_url' => $logs->nextPageUrl(),
                'current_page'  => $logs->currentPage(),
                'last_page'     => $logs->lastPage(),
                'per_page'      => $logs->perPage(),
                'total_records' => $logs->total(),
                'first_item'    => $logs->firstItem(),
                'last_item'     => $logs->lastItem(),
                'has_more'      => $logs->hasMorePages(),
            ]);
        }

        return view('audit-logs.index', compact('logs', 'events'));
    }

    public function exportCsv(Request $request)
    {
        $logs = $this->buildFilteredQuery($request)->limit(2000)->get();
        $filename = 'hydrotech_audit_logs_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Timestamp', 'Actor', 'Role', 'Action', 'Target', 'Summary', 'IP Address', 'Device']);

            foreach ($logs as $log) {
                $decorated = $this->decorateRow($log);
                fputcsv($out, [
                    $log->created_at?->format('Y-m-d H:i:s') ?? '',
                    $decorated->display_actor,
                    self::formatRoleLabel($decorated->display_role),
                    $decorated->display_event,
                    $decorated->display_target,
                    $decorated->display_summary,
                    $log->ip_address ?? '',
                    $decorated->display_device,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        $decorated = $this->decorateRow($log);

        $old = is_array($log->old_values) ? $log->old_values : [];
        $new = is_array($log->new_values) ? $log->new_values : [];

        return response()->json([
            'success' => true,
            'data'    => [
                'user'              => $decorated->display_actor,
                'role'              => self::formatRoleLabel($decorated->display_role),
                'event'             => $decorated->display_event,
                'event_raw'         => $log->event,
                'event_badge_class' => self::eventBadgeClass((string) $log->event),
                'target_model'      => $decorated->display_target,
                'direction_context' => $this->getDirectionContext($log, $old, $new),
                'description'       => $decorated->display_summary,
                'ip_address'        => $log->ip_address ?? '—',
                'device'            => $decorated->display_device,
                'user_agent'        => $log->user_agent ?? '',
                'timestamp'         => $log->created_at?->format('M d, Y h:i A') ?? '—',
                'changes'           => $this->formatChangesForHumans($old, $new, (string) $log->event),
            ],
        ]);
    }

    private function decorateRow(AuditLog $log): AuditLog
    {
        $old = is_array($log->old_values) ? $log->old_values : [];
        $new = is_array($log->new_values) ? $log->new_values : [];

        $actor = 'System / Guest';
        if ($log->user) {
            $actor = trim((string) ($log->user->full_name ?: ($log->user->firstname . ' ' . $log->user->lastname))) ?: 'System / Guest';
        } elseif (!empty($log->actor_name)) {
            $actor = $log->actor_name;
        }

        $role = $log->actor_role ?: ($log->user?->role);
        $device = ClientContext::parseDevice($log->user_agent);

        $log->display_actor   = $actor;
        $log->display_role    = $role;
        $log->is_deleted_user = ($log->user_id !== null && !$log->user);
        $log->display_event   = self::formatEventLabel((string) $log->event);
        $log->display_badge   = self::eventBadgeClass((string) $log->event);
        $log->display_target  = $this->getSpecificTargetModel($log);
        $log->display_summary = $this->generateSummaryDescription($log, $old, $new);
        $log->display_device  = $device['label'];

        return $log;
    }

    private function getSpecificTargetModel(AuditLog $log): string
    {
        if (!$log->auditable_type || !$log->auditable_id) {
            return match (strtolower((string) $log->event)) {
                'login', 'logout', 'login_failed', 'admin_portal_login', 'admin_portal_login_failed', 'admin_login_blocked_public'
                    => 'Authentication Session',
                'backup_created', 'backup_restored'
                    => 'Database Backup',
                default => 'System Event',
            };
        }

        $modelClass = $log->auditable_type;
        $modelName = class_basename($modelClass);

        // Quick label extraction from payload first (avoids N+1 lookups where possible)
        $payload = array_merge(
            is_array($log->old_values) ? $log->old_values : [],
            is_array($log->new_values) ? $log->new_values : []
        );

        $label = $payload['product_name']
            ?? $payload['po_number']
            ?? $payload['pr_number']
            ?? $payload['return_number']
            ?? $payload['transaction_id']
            ?? $payload['batch_id']
            ?? $payload['name']
            ?? null;

        if (!$label && (isset($payload['firstname']) || isset($payload['lastname']))) {
            $label = trim(($payload['firstname'] ?? '') . ' ' . ($payload['lastname'] ?? ''));
        }

        if (!$label && class_exists($modelClass)) {
            try {
                $instance = $modelClass::find($log->auditable_id);
                if ($instance) {
                    $label = $instance->product_name
                        ?? $instance->po_number
                        ?? $instance->pr_number
                        ?? $instance->return_number
                        ?? $instance->transaction_id
                        ?? $instance->batch_id
                        ?? $instance->name
                        ?? (isset($instance->firstname) ? trim(($instance->firstname ?? '') . ' ' . ($instance->lastname ?? '')) : null);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $prettyModel = trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $modelName));
        return $label ? "{$prettyModel} · {$label}" : "{$prettyModel} #{$log->auditable_id}";
    }

    private function getDirectionContext(AuditLog $log, array $old, array $new): string
    {
        $payload = array_merge($old, $new);

        if (isset($payload['location'])) {
            return 'Location: ' . ucfirst((string) $payload['location']);
        }
        if (isset($payload['direction'])) {
            return 'Transfer: ' . str_replace('_', ' ', ucwords((string) $payload['direction'], '_'));
        }
        if (isset($payload['portal'])) {
            return 'Origin: ' . ucfirst((string) $payload['portal']) . ' Portal';
        }

        return match (strtolower((string) $log->event)) {
            'admin_portal_login', 'admin_portal_login_failed' => 'Origin: Secret Admin Portal',
            'admin_login_blocked_public'                      => 'Origin: Public Login (Blocked)',
            'login', 'login_failed'                           => 'Origin: Staff Login Portal',
            'logout', 'session_revoked', 'sessions_revoked'   => 'Origin: Session Management',
            default                                           => 'Origin: Hydrotech Web App',
        };
    }

    private function generateSummaryDescription(AuditLog $log, array $old, array $new): string
    {
        $event = strtolower((string) $log->event);
        $modelName = trim(preg_replace('/(?<!^)([A-Z])/', ' $1', class_basename($log->auditable_type ?? 'Record')));

        return match ($event) {
            'login'                      => 'Signed into the system.',
            'admin_portal_login'         => 'Administrator signed in via Secret Admin Portal.',
            'admin_portal_login_failed'  => 'Failed administrator login attempt on Secret Admin Portal.',
            'admin_login_blocked_public' => 'Blocked admin account login attempt on public staff login page.',
            'login_failed'               => 'Failed login attempt' . (!empty($new['attempted_username']) ? " for '{$new['attempted_username']}'." : '.'),
            'logout'                     => 'Signed out of active session.',
            'force_password_changed'     => 'Completed mandatory first-login password change.',
            'password_changed'           => 'Updated account password via Account Settings.',
            'action_password_updated'    => 'Updated Manager Override PIN/password.',
            'account_deactivated'        => 'Account deactivated and active sessions terminated.',
            'account_reactivated'        => 'Account reactivated for system access.',
            'session_revoked'            => 'Revoked an active session on another device.',
            'sessions_revoked'           => 'Signed out all other active device sessions.',
            'po_received'                => 'Recorded Goods Received Note' . (!empty($new['discrepancy_amount']) && (float) $new['discrepancy_amount'] > 0 ? ' with shortage logged.' : '.'),
            'supplier_return_approved'   => 'Approved supplier return, deducted stock, and credited ledger.',
            'customer_return_voided'     => 'Voided customer return and reversed stock adjustments.',
            'backup_created'             => 'Generated database backup snapshot.',
            'backup_restored'            => 'Restored database from backup file.',
            'created'                    => "Created new {$modelName} record.",
            'deleted'                    => "Deleted {$modelName} record.",
            'updated'                    => $this->summarizeUpdatedFields($modelName, $old, $new),
            default                      => self::formatEventLabel($event) . " on {$modelName}.",
        };
    }

    private function summarizeUpdatedFields(string $modelName, array $old, array $new): string
    {
        $changed = [];
        foreach ($new as $key => $val) {
            if (in_array($key, self::IGNORED_KEYS, true) || in_array($key, self::REDACTED_KEYS, true)) {
                continue;
            }
            if (!array_key_exists($key, $old) || $old[$key] !== $val) {
                $changed[] = ucwords(str_replace('_', ' ', $key));
            }
        }

        if (count($changed) === 1) {
            return "Updated {$changed[0]} on {$modelName}.";
        }
        if (count($changed) > 1) {
            $preview = implode(', ', array_slice($changed, 0, 3));
            $extra = count($changed) > 3 ? ' +' . (count($changed) - 3) . ' more' : '';
            return "Updated {$preview}{$extra} on {$modelName}.";
        }

        return "Updated {$modelName} record.";
    }

    private function formatChangesForHumans(array $old, array $new, string $event): array
    {
        $eventLower = strtolower($event);
        $changes = [];

        $moneyKeys = [
            'price', 'cost', 'amount', 'selling_price', 'purchase_cost',
            'total_amount', 'amount_paid', 'amount_change', 'subtotal',
            'discrepancy_amount', 'refund_amount', 'restock_value',
        ];

        if (in_array($eventLower, ['login', 'logout', 'admin_portal_login'], true)) {
            return [[
                'field' => 'Session Status',
                'old'   => $eventLower === 'logout' ? 'Active' : 'Signed Out',
                'new'   => $eventLower === 'logout' ? 'Signed Out' : 'Authenticated',
            ]];
        }

        $formatValue = function ($key, $value) use ($moneyKeys) {
            if (in_array($key, self::REDACTED_KEYS, true)) {
                return '[REDACTED]';
            }
            if ($value === null || $value === '') {
                return '—';
            }
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }
            if (is_array($value)) {
                return json_encode($value);
            }
            if (is_string($value) && strlen($value) > 250) {
                return substr($value, 0, 120) . '… [truncated]';
            }
            if (in_array($key, $moneyKeys, true) && is_numeric($value)) {
                return '₱' . number_format((float) $value, 2);
            }
            return (string) $value;
        };

        if ($eventLower === 'created') {
            foreach ($new as $key => $value) {
                if (in_array($key, self::IGNORED_KEYS, true) || in_array($key, self::REDACTED_KEYS, true)) {
                    continue;
                }
                $changes[] = [
                    'field' => ucwords(str_replace('_', ' ', $key)),
                    'old'   => null,
                    'new'   => $formatValue($key, $value),
                ];
            }
        } elseif ($eventLower === 'updated') {
            $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
            foreach ($allKeys as $key) {
                if (in_array($key, self::IGNORED_KEYS, true) || in_array($key, self::REDACTED_KEYS, true)) {
                    continue;
                }
                $oldVal = $old[$key] ?? null;
                $newVal = $new[$key] ?? null;
                if ($oldVal !== $newVal) {
                    $changes[] = [
                        'field' => ucwords(str_replace('_', ' ', $key)),
                        'old'   => $formatValue($key, $oldVal),
                        'new'   => $formatValue($key, $newVal),
                    ];
                }
            }
        } else {
            $data = !empty($new) ? $new : $old;
            foreach ($data as $key => $value) {
                if (in_array($key, self::IGNORED_KEYS, true) || in_array($key, self::REDACTED_KEYS, true)) {
                    continue;
                }
                $changes[] = [
                    'field' => ucwords(str_replace('_', ' ', $key)),
                    'old'   => '—',
                    'new'   => $formatValue($key, $value),
                ];
            }
        }

        return $changes;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    private const PER_PAGE = 16;

    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->latest('created_at')
            ->latest('id');

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%");
                    });
            });
        }

        // Event filter
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Paginate 16 records per page
        $logs = $query->paginate(self::PER_PAGE)->withQueryString();

        // Available events for the dropdown
        $events = AuditLog::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        // AJAX response for infinite scrolling
        if ($request->ajax()) {
            return response()->json([
                'html' => view('audit-logs.partials.rows', compact('logs'))->render(),
                'next_page_url' => $logs->nextPageUrl(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total_records' => $logs->total(),
                'first_item' => $logs->firstItem(),
                'last_item' => $logs->lastItem(),
                'has_more' => $logs->hasMorePages(),
            ]);
        }

        return view('audit-logs.index', compact('logs', 'events'));
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        $old = $log->old_values ?? [];
        $new = $log->new_values ?? [];

        $userName = 'System / Guest';

        if ($log->user) {
            $userName = $log->user->full_name
                ?? $log->user->name
                ?? trim(($log->user->firstname ?? '') . ' ' . ($log->user->lastname ?? ''));

            if ($userName === '') {
                $userName = 'System / Guest';
            }
        } elseif ($log->actor_name) {
            $userName = $log->actor_name;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userName,
                'event' => ucfirst($log->event),
                'target_model' => $this->getSpecificTargetModel($log),
                'direction_context' => $this->getDirectionContext($log, $old, $new),
                'description' => $this->generateSummaryDescription($log, $old, $new),
                'ip_address' => $log->ip_address ?? '—',
                'timestamp' => $log->created_at->format('M d, Y h:i A'),
                'changes' => $this->formatChangesForHumans($old, $new, $log->event),
            ],
        ]);
    }

    private function getSpecificTargetModel($log)
    {
        if (!$log->auditable_type || !$log->auditable_id) {
            if (in_array($log->event, ['login', 'logout'])) {
                return 'User Authentication Session';
            }

            return 'System Event';
        }

        $modelClass = $log->auditable_type;
        $modelName = class_basename($modelClass);

        $instance = class_exists($modelClass)
            ? $modelClass::find($log->auditable_id)
            : null;

        $identifier = '';

        if ($instance) {
            if (isset($instance->product_name)) {
                $identifier = ' (' . $instance->product_name . ')';
            } elseif (isset($instance->name)) {
                $identifier = ' (' . $instance->name . ')';
            } elseif (isset($instance->firstname) || isset($instance->lastname)) {
                $identifier = ' (' . trim(($instance->firstname ?? '') . ' ' . ($instance->lastname ?? '')) . ')';
            } elseif (isset($instance->transaction_id)) {
                $identifier = ' (' . $instance->transaction_id . ')';
            }
        } else {
            $oldValues = is_array($log->old_values)
                ? $log->old_values
                : [];

            $fallbackName = $oldValues['product_name']
                ?? $oldValues['name']
                ?? null;

            // If the deleted target was a User, grab their name from firstname/lastname
            if (!$fallbackName && (isset($oldValues['firstname']) || isset($oldValues['lastname']))) {
                $fallbackName = trim(($oldValues['firstname'] ?? '') . ' ' . ($oldValues['lastname'] ?? ''));
            }

            if ($fallbackName) {
                $identifier = ' (' . $fallbackName . ' - Deleted)';
            }
        }

        return "{$modelName} #{$log->auditable_id}{$identifier}";
    }

    private function getDirectionContext($log, $old, $new)
    {
        $payload = array_merge(
            is_array($old) ? $old : [],
            is_array($new) ? $new : []
        );

        if (isset($payload['location'])) {
            return 'Location: ' . ucfirst($payload['location']);
        }

        if (isset($payload['direction'])) {
            return 'Transfer Direction: ' .
                str_replace('_', ' ', ucwords($payload['direction'], '_'));
        }

        if ($log->event === 'login') {
            return 'Origin: Web Login Portal';
        }

        if ($log->event === 'logout') {
            return 'Origin: User Session Terminated';
        }

        return 'Origin: Web Dashboard Interface';
    }

    private function generateSummaryDescription($log, $old, $new)
    {
        $action = ucfirst($log->event);
        $modelName = class_basename($log->auditable_type ?? 'Record');

        if ($log->event === 'login') {
            return "User successfully logged into the system.";
        }

        if ($log->event === 'logout') {
            return "User logged out of the system session.";
        }

        if ($log->event === 'created') {
            return "A new {$modelName} record was successfully created and added to the database.";
        }

        if ($log->event === 'deleted') {
            $targetName = '';
            $oldData = is_array($old) ? $old : [];
            if (isset($oldData['firstname']) || isset($oldData['lastname'])) {
                $targetName = " (" . trim(($oldData['firstname'] ?? '') . ' ' . ($oldData['lastname'] ?? '')) . ")";
            }
            return "A {$modelName} record{$targetName} was permanently deleted from the system.";
        }

        if ($log->event === 'updated') {
            $changedFields = [];
            $ignoredKeys = [
                'id',
                'created_at',
                'updated_at',
                'password',
                'remember_token',
            ];

            $old = is_array($old) ? $old : [];
            $new = is_array($new) ? $new : [];

            foreach ($new as $key => $val) {
                if (in_array($key, $ignoredKeys)) {
                    continue;
                }

                if (!array_key_exists($key, $old) || $old[$key] !== $val) {
                    $changedFields[] = str_replace('_', ' ', $key);
                }
            }

            if (count($changedFields) === 1) {
                return "The {$modelName} record was updated. The field '" .
                    ucwords($changedFields[0]) . "' was modified.";
            } elseif (count($changedFields) > 1) {
                return "The {$modelName} record was updated. Multiple fields were modified.";
            }

            return "An existing {$modelName} record was updated with new attributes.";
        }

        return "A '{$action}' event was performed on a {$modelName}.";
    }

    private function formatChangesForHumans($old, $new, $event)
    {
        $ignoredKeys = [
            'id',
            'created_at',
            'updated_at',
            'password',
            'remember_token',
        ];

        $changes = [];

        $moneyKeys = [
            'price',
            'cost',
            'amount',
            'selling_price',
            'purchase_cost',
            'total_amount',
            'amount_paid',
            'amount_change',
        ];

        $old = is_array($old) ? $old : [];
        $new = is_array($new) ? $new : [];

        if ($event === 'login' || $event === 'logout') {
            return [[
                'field' => 'Authentication Status',
                'old' => 'Inactive',
                'new' => $event === 'login'
                    ? 'Active Session Started'
                    : 'Session Ended',
            ]];
        }

        $formatValue = function ($key, $value) use ($moneyKeys) {
            if ($value === null) {
                return 'None';
            }

            if (is_array($value)) {
                return json_encode($value);
            }

            if (in_array($key, $moneyKeys) && is_numeric($value)) {
                return '₱' . number_format((float) $value, 2);
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            return $value;
        };

        if ($event === 'created') {
            foreach ($new as $key => $value) {
                if (in_array($key, $ignoredKeys)) {
                    continue;
                }

                $changes[] = [
                    'field' => ucwords(str_replace('_', ' ', $key)),
                    'old' => null,
                    'new' => $formatValue($key, $value),
                ];
            }
        } elseif ($event === 'updated') {
            $allKeys = array_unique(array_merge(
                array_keys($old),
                array_keys($new)
            ));

            foreach ($allKeys as $key) {
                if (in_array($key, $ignoredKeys)) {
                    continue;
                }

                $oldVal = $old[$key] ?? null;
                $newVal = $new[$key] ?? null;

                if ($oldVal !== $newVal) {
                    $changes[] = [
                        'field' => ucwords(str_replace('_', ' ', $key)),
                        'old' => $formatValue($key, $oldVal),
                        'new' => $formatValue($key, $newVal),
                    ];
                }
            }
        } else {
            $data = !empty($new) ? $new : $old;

            foreach ($data as $key => $value) {
                if (in_array($key, $ignoredKeys)) {
                    continue;
                }

                $changes[] = [
                    'field' => ucwords(str_replace('_', ' ', $key)),
                    'old' => '—',
                    'new' => $formatValue($key, $value),
                ];
            }
        }

        return $changes;
    }
}
<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Support\ClientContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait AuditsChanges
{
    /**
     * Columns that must NEVER be written into audit_logs (secrets, binary blobs, noisy timestamps).
     */
    protected static array $auditExcludedColumns = [
        'password',
        'action_password',
        'remember_token',
        'product_image_data',
        'avatar_data',
        'updated_at',
        'created_at',
        'email_verified_at',
    ];

    /**
     * Columns that should not trigger standalone audit rows if they are the ONLY thing changed.
     */
    protected static array $auditSilentOnlyColumns = [
        'theme',
        'preferences',
    ];

    public static function bootAuditsChanges()
    {
        static::created(function ($model) {
            $newValues = static::sanitizeAuditPayload($model->getAttributes());
            if (!empty($newValues)) {
                static::logAudit('created', $model, null, $newValues);
            }
        });

        static::updated(function ($model) {
            $changes = static::sanitizeAuditPayload($model->getChanges());
            if (empty($changes)) {
                return;
            }

            // Skip noisy UI-only preference updates (e.g. toggling dark mode)
            $meaningfulKeys = array_diff(array_keys($changes), static::$auditSilentOnlyColumns);
            if (empty($meaningfulKeys)) {
                return;
            }

            // Store ONLY the changed keys in old_values (true diff, zero bloat)
            $original = $model->getOriginal();
            $oldDiff = [];
            foreach ($changes as $key => $val) {
                $oldDiff[$key] = $original[$key] ?? null;
            }

            static::logAudit('updated', $model, $oldDiff, $changes);
        });

        static::deleted(function ($model) {
            $oldValues = static::sanitizeAuditPayload($model->getAttributes());
            static::logAudit('deleted', $model, $oldValues, null);
        });
    }

    protected static function sanitizeAuditPayload(?array $payload): array
    {
        if (empty($payload)) {
            return [];
        }

        $clean = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, static::$auditExcludedColumns, true)) {
                continue;
            }
            // Guard against any unexpectedly huge blob string (> 4KB)
            if (is_string($value) && strlen($value) > 4096) {
                $clean[$key] = '[OMITTED LARGE DATA]';
                continue;
            }
            $clean[$key] = $value;
        }

        return $clean;
    }

    protected static function logAudit($event, $model, $oldValues, $newValues)
    {
        try {
            $user = Auth::user();
            $actorName = $user
                ? trim((string) ($user->full_name ?: ($user->firstname . ' ' . $user->lastname)))
                : 'System / Guest';

            AuditLog::create([
                'user_id'        => $user ? $user->id : null,
                'actor_name'     => $actorName !== '' ? $actorName : 'System / Guest',
                'actor_role'     => $user ? $user->role : null,
                'event'          => $event,
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'old_values'     => $oldValues,
                'new_values'     => $newValues,
                'ip_address'     => ClientContext::ip(),
                'user_agent'     => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Fails gracefully during migrations or console seeding
        }
    }
}

<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Throwable;

class AuditLogger
{
    public static function log(
        ?User $actor,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $metadata = []
    ): void {
        try {
            AuditLog::create([
                'actor_id' => $actor?->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'metadata' => $metadata,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

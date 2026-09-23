<?php

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public static function log(string $action, string $entityType, int|string|null $entityId = null, ?array $oldValues = null, ?array $newValues = null): ?self
    {
        $user = auth_user();
        return self::create([
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId ? (string)$entityId : null,
            'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

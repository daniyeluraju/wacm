<?php

namespace App\Models;

use App\Core\Model;

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'user_id',
        'campaign_id',
        'contact_id',
        'action_type',
        'status',
        'notes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public static function log(string $actionType, string $status = 'Success', ?string $notes = null, ?int $campaignId = null, ?int $contactId = null): ?self
    {
        $user = auth_user();
        return self::create([
            'user_id' => $user['id'] ?? null,
            'campaign_id' => $campaignId,
            'contact_id' => $contactId,
            'action_type' => $actionType,
            'status' => $status,
            'notes' => $notes,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

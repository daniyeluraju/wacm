<?php

namespace App\Models;

use App\Core\Model;

class CleanupHistory extends Model
{
    protected string $table = 'cleanup_history';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'campaign_id',
        'initiated_by',
        'cleanup_type',
        'records_deleted',
        'files_deleted',
        'storage_released_bytes',
        'status',
        'error_message',
        'created_at',
        'completed_at',
    ];
}

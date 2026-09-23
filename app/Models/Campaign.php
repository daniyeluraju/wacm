<?php

namespace App\Models;

use App\Core\Model;

class Campaign extends Model
{
    protected string $table = 'campaigns';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'description',
        'contact_list_id',
        'message_draft_id',
        'status',
        'pacing_min_seconds',
        'pacing_max_seconds',
        'created_by',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at',
    ];
}

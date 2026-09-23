<?php

namespace App\Models;

use App\Core\Model;

class CampaignRecipient extends Model
{
    protected string $table = 'campaign_recipients';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'campaign_id',
        'contact_id',
        'status',
        'prepared_message',
        'action_notes',
        'completed_at',
        'created_at',
        'updated_at',
    ];
}

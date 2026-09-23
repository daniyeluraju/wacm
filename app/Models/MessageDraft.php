<?php

namespace App\Models;

use App\Core\Model;

class MessageDraft extends Model
{
    protected string $table = 'message_drafts';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'title',
        'content',
        'has_attachment',
        'caption',
        'created_by',
        'is_archived',
        'created_at',
        'updated_at',
    ];
}

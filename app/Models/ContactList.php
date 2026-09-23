<?php

namespace App\Models;

use App\Core\Model;

class ContactList extends Model
{
    protected string $table = 'contact_lists';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'description',
        'color',
        'created_by',
        'is_archived',
        'created_at',
        'updated_at',
    ];
}

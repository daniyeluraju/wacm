<?php

namespace App\Models;

use App\Core\Model;

class ContactListMember extends Model
{
    protected string $table = 'contact_list_members';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'list_id',
        'contact_id',
        'added_at',
    ];
}

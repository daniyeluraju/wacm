<?php

namespace App\Models;

use App\Core\Model;

class ConsentRecord extends Model
{
    protected string $table = 'consent_records';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'contact_id',
        'status',
        'source',
        'notes',
        'recorded_by',
        'recorded_at',
    ];
}

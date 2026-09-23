<?php

namespace App\Models;

use App\Core\Model;

class Contact extends Model
{
    protected string $table = 'contacts';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'full_name',
        'phone_raw',
        'country_code',
        'phone_normalized',
        'email',
        'group_name',
        'consent_status',
        'consent_source',
        'consent_date',
        'opt_out_status',
        'opt_out_date',
        'custom_field_1',
        'custom_field_2',
        'notes',
        'is_deleted',
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}

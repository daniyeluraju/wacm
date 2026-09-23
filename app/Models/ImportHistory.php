<?php

namespace App\Models;

use App\Core\Model;

class ImportHistory extends Model
{
    protected string $table = 'import_history';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'user_id',
        'file_name',
        'file_size',
        'total_rows',
        'imported_rows',
        'rejected_rows',
        'error_log_path',
        'status',
        'created_at',
    ];
}

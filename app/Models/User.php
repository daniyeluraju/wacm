<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'status',
        'last_login_at',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [
        'password_hash',
    ];

    public static function findByEmail(string $email): ?self
    {
        return self::findBy('email', strtolower(trim($email)));
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class StorageSetting extends Model
{
    protected string $table = 'storage_settings';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'updated_at',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::findBy('setting_key', $key);
        return $setting ? $setting->setting_value : $default;
    }

    public static function set(string $key, mixed $value, ?string $description = null): bool
    {
        $setting = self::findBy('setting_key', $key);
        if ($setting) {
            return self::update($setting->id, [
                'setting_value' => (string) $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return (bool) self::create([
            'setting_key' => $key,
            'setting_value' => (string) $value,
            'description' => $description ?? $key,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

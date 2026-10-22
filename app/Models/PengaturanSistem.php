<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'kunci',
        'nilai',
        'deskripsi',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('kunci', $key)->first();

        return $setting ? $setting->nilai : $default;
    }

    public static function set(string $key, $value, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['kunci' => $key],
            [
                'nilai' => $value,
                'deskripsi' => $description ?? $key,
            ]
        );
    }
}

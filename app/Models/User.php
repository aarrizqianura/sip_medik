<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'no_wa',
        'password',
        'role',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function dokter(): HasOne
    {
        return $this->hasOne(Dokter::class, 'id_user');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_user');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'id_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminSdm(): bool
    {
        return $this->role === 'admin_sdm';
    }

    public function isVerifikator(): bool
    {
        return $this->role === 'verifikator_komite';
    }

    public function isDokter(): bool
    {
        return $this->role === 'dokter';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin (IT PDE)',
            'admin_sdm' => 'Admin SDM Kepegawaian',
            'verifikator_komite' => 'Verifikator Komite Medik',
            'dokter' => 'Dokter / Staf Medis',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }
}

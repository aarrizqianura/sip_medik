<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DokumenSip extends Model
{
    use HasFactory;

    protected $table = 'dokumen_sip';

    protected $fillable = [
        'id_dokter',
        'nomor_sip',
        'tempat_praktik_ke',
        'tanggal_terbit',
        'tanggal_kadaluarsa',
        'status',
        'status_verifikasi',
        'file_sip',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'verified_at' => 'datetime',
        'tempat_praktik_ke' => 'integer',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_sip');
    }

    public function hitungSisaHari(): int
    {
        if (!$this->tanggal_kadaluarsa) {
            return 0;
        }
        return (int) Carbon::today()->diffInDays(Carbon::parse($this->tanggal_kadaluarsa), false);
    }

    public function updateStatusKadaluarsa(): string
    {
        $selisih = $this->hitungSisaHari();

        if ($selisih < 0) {
            $this->status = 'overdue';
        } elseif ($selisih <= 30) {
            $this->status = 'due';
        } elseif ($selisih <= 90) {
            $this->status = 'due_soon';
        } else {
            $this->status = 'aktif';
        }

        $this->save();
        return $this->status;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif' => 'Aktif (>90 Hari)',
            'due_soon' => 'Due Soon (31-90 Hari)',
            'due' => 'Kritis / Due (≤30 Hari)',
            'overdue' => 'Kadaluarsa / Overdue',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'due_soon' => 'bg-amber-100 text-amber-800 border-amber-300',
            'due' => 'bg-orange-100 text-orange-800 border-orange-300',
            'overdue' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getVerifikasiBadgeClassAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'ditolak' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }
}

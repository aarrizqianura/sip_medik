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
        'fasyankes',
        'alamat_fasyankes',
        'penerbit_sip',
        'status_kecukupan_skp',
        'nomor_rekomendasi_skp',
        'file_rekomendasi_skp',
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
        'status_kecukupan_skp' => 'boolean',
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
        if (! $this->tanggal_kadaluarsa) {
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

    public function isSkpTerpenuhi(): bool
    {
        return $this->status_kecukupan_skp || ! empty($this->file_rekomendasi_skp);
    }

    public function getStatusKecukupanSkpLabelAttribute(): string
    {
        if ($this->status_kecukupan_skp && $this->file_rekomendasi_skp) {
            return 'SKP Terpenuhi & Rekomendasi SatuSehat Diunggah';
        }

        if ($this->status_kecukupan_skp) {
            return 'SKP Terpenuhi (Platform SatuSehat)';
        }

        if ($this->file_rekomendasi_skp) {
            return 'Surat Rekomendasi SKP Terlampir';
        }

        return 'Belum Ditandai / Belum Ada Berkas';
    }

    public function getStatusKecukupanSkpBadgeAttribute(): string
    {
        if ($this->status_kecukupan_skp || $this->file_rekomendasi_skp) {
            return '<span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i> SKP Terpenuhi (SatuSehat)</span>';
        }

        return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i> Belum Terpenuhi</span>';
    }

    public function getStatusVerifikasiAntreanLabelAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'disetujui' => 'Tervalidasi & Disahkan',
            'menunggu' => 'Menunggu Verifikasi (Antrean Sistem)',
            'ditolak' => 'Ditolak (Perlu Revisi)',
            default => ucfirst($this->status_verifikasi),
        };
    }
}

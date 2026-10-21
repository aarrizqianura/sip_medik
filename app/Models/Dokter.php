<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'dokters';

    protected $fillable = [
        'id_user',
        'nip_nrp',
        'id_ksm',
        'spesialisasi',
        'subspesialis',
        'tempat_praktik_ke',
        'status_kepegawaian',
        'status_str',
        'nomor_str',
        'tanggal_terbit_str',
        'tanggal_kadaluarsa_str',
        'file_str',
        'status_verifikasi_str',
        'is_str_locked',
        'verified_str_by',
        'verified_str_at',
        'catatan_verifikasi_str',
        'satusehat_verified_at',
        'satusehat_data',
    ];

    protected $casts = [
        'tanggal_terbit_str' => 'date',
        'tanggal_kadaluarsa_str' => 'date',
        'verified_str_at' => 'datetime',
        'satusehat_verified_at' => 'datetime',
        'satusehat_data' => 'array',
        'is_str_locked' => 'boolean',
        'tempat_praktik_ke' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function ksm(): BelongsTo
    {
        return $this->belongsTo(Ksm::class, 'id_ksm');
    }

    public function verifikatorStr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_str_by');
    }

    public function dokumenSip(): HasMany
    {
        return $this->hasMany(DokumenSip::class, 'id_dokter')->orderByDesc('tanggal_kadaluarsa');
    }

    public function kredensialKlinis(): HasMany
    {
        return $this->hasMany(KredensialKlinis::class, 'id_dokter')->orderByDesc('tanggal_berakhir_spk');
    }

    public function sipTerbaru(): HasOne
    {
        return $this->hasOne(DokumenSip::class, 'id_dokter')->latestOfMany('tanggal_kadaluarsa');
    }

    public function spkTerbaru(): HasOne
    {
        return $this->hasOne(KredensialKlinis::class, 'id_dokter')->latestOfMany('tanggal_berakhir_spk');
    }

    /**
     * Syarat Mutlak (Hard Constraint):
     * Modul pengurusan SIP hanya dapat aktif jika status STR dokter tervalidasi dan nomor STR terkunci.
     */
    public function isStrValidDanTerkunci(): bool
    {
        return $this->status_verifikasi_str === 'tervalidasi' && $this->is_str_locked && ! empty($this->nomor_str);
    }

    public function getTempatPraktikLabelAttribute(): string
    {
        return match ((int) $this->tempat_praktik_ke) {
            1 => 'SIP Ke-1 (Fasilitas Utama)',
            2 => 'SIP Ke-2',
            3 => 'SIP Ke-3',
            default => 'SIP Ke-'.($this->tempat_praktik_ke ?: 1),
        };
    }

    public function getStatusVerifikasiStrLabelAttribute(): string
    {
        return match ($this->status_verifikasi_str) {
            'tervalidasi' => 'Tervalidasi & Terkunci',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'ditolak' => 'Ditolak / Perlu Revisi',
            default => 'Belum Lengkap',
        };
    }

    public function getStatusVerifikasiStrBadgeAttribute(): string
    {
        return match ($this->status_verifikasi_str) {
            'tervalidasi' => '<span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-lock me-1"></i> Tervalidasi (Terkunci)</span>',
            'menunggu_verifikasi' => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Telaah</span>',
            'ditolak' => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Ditolak</span>',
            default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary"><i class="fa-solid fa-file-circle-question me-1"></i> Belum Lengkap</span>',
        };
    }
}

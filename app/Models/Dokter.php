<?php

namespace App\Models;

use Carbon\Carbon;
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
        'status_praktik',
        'is_arsip_locked',
        'arsip_locked_at',
        'arsip_locked_by',
        'nomor_sk_direktur',
        'tanggal_sk_direktur',
        'file_sk_direktur',
        'catatan_pengesahan_direksi',
    ];

    protected $casts = [
        'tanggal_terbit_str' => 'date',
        'tanggal_kadaluarsa_str' => 'date',
        'tanggal_sk_direktur' => 'date',
        'verified_str_at' => 'datetime',
        'arsip_locked_at' => 'datetime',
        'satusehat_verified_at' => 'datetime',
        'satusehat_data' => 'array',
        'is_str_locked' => 'boolean',
        'is_arsip_locked' => 'boolean',
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
        return $this->hasOne(KredensialKlinis::class, 'id_dokter')->latestOfMany('id');
    }

    public function kredensialTerbaru(): HasOne
    {
        return $this->hasOne(KredensialKlinis::class, 'id_dokter')->latestOfMany('id');
    }

    /**
     * Pemicu Alur Rekredensialing Otomatis:
     * Mengecek apakah masa berlaku SPK/RKK melampaui siklus evaluasi (3 tahun)
     * atau ada permohonan penambahan kewenangan klinis baru.
     *
     * @return array{perlu: bool, alasan: string, jenis: string}
     */
    public function evaluasiStatusRekredensial(bool $permohonanBaru = false): array
    {
        if ($permohonanBaru) {
            return [
                'perlu' => true,
                'alasan' => 'Terdapat permohonan penambahan rincian kewenangan klinis baru dari dokter.',
                'jenis' => 'penambahan_kewenangan',
            ];
        }

        $spk = $this->kredensialTerbaru ?: $this->spkTerbaru;

        if (! $spk) {
            return [
                'perlu' => true,
                'alasan' => 'Dokter belum memiliki instrumen Surat Penugasan Klinis (SPK) & RKK terdaftar di sistem.',
                'jenis' => 'kredensial_baru',
            ];
        }

        if ($spk->status_kredensial === 'proses_rekredensial') {
            return [
                'perlu' => true,
                'alasan' => 'Dokter saat ini masih dalam proses alur rekredensialing (menunggu sidang/rekomendasi).',
                'jenis' => $spk->jenis_pengajuan ?: 'rekredensial_berkala',
            ];
        }

        $sisaHari = $spk->hitungSisaHari();
        $siklusTahun = $spk->tanggal_terbit_spk ? Carbon::parse($spk->tanggal_terbit_spk)->diffInYears(Carbon::today()) : 0;

        // Jika telah melampaui masa berlaku (kadaluarsa) atau siklus 3 tahun terlampaui
        if ($sisaHari <= 0 || $siklusTahun >= 3 || $spk->status_kredensial === 'kedaluwarsa') {
            return [
                'perlu' => true,
                'alasan' => 'Masa berlaku instrumen SPK/RKK telah melampaui siklus evaluasi 3 tahun (Kadaluarsa sejak '.($spk->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : '-').').',
                'jenis' => 'rekredensial_berkala',
            ];
        }

        // Jika dalam masa evaluasi berkala 90 hari sebelum habis
        if ($sisaHari <= 90 || $spk->status_kredensial === 'evaluasi') {
            return [
                'perlu' => true,
                'alasan' => "Instrumen SPK/RKK mendekati batas akhir siklus evaluasi 3 tahun (tersisa {$sisaHari} hari).",
                'jenis' => 'rekredensial_berkala',
            ];
        }

        return [
            'perlu' => false,
            'alasan' => 'Instrumen SPK & RKK masih aktif absah dalam siklus 3 tahun.',
            'jenis' => 'aktif',
        ];
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

    public function arsipLockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'arsip_locked_by');
    }

    public function isAktifLegal(): bool
    {
        return $this->status_praktik === 'AKTIF_LEGAL';
    }

    public function isArsipLengkapDanTerkunci(): bool
    {
        $spk = $this->spkTerbaru ?: $this->kredensialTerbaru;
        $sip = $this->sipTerbaru;

        return (bool) $this->is_str_locked
            && $this->status_verifikasi_str === 'tervalidasi'
            && (bool) ($sip?->is_sip_locked || $sip?->status_verifikasi === 'disetujui')
            && (bool) ($spk?->is_spk_locked || $spk?->status_kredensial === 'aktif')
            && (bool) ($this->is_arsip_locked || $this->status_praktik === 'AKTIF_LEGAL');
    }

    public function getStatusPraktikLabelAttribute(): string
    {
        return match ($this->status_praktik) {
            'AKTIF_LEGAL' => 'Hak Praktik Penuh (AKTIF_LEGAL)',
            'MENUNGGU_PENGESAHAN' => 'Menunggu SK Direktur',
            'NONAKTIF' => 'Non-Aktif / Ditangguhkan',
            'DITANGGUHKAN' => 'Ditangguhkan (Peringatan Legal)',
            default => 'Belum Lengkap',
        };
    }

    public function getStatusPraktikBadgeAttribute(): string
    {
        return match ($this->status_praktik) {
            'AKTIF_LEGAL' => '<span class="badge bg-success text-white shadow-xs px-2 py-1"><i class="fa-solid fa-shield-check me-1"></i> AKTIF_LEGAL</span>',
            'MENUNGGU_PENGESAHAN' => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1"><i class="fa-solid fa-file-signature me-1"></i> Menunggu SK Direktur</span>',
            'NONAKTIF' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1"><i class="fa-solid fa-ban me-1"></i> Non-Aktif</span>',
            'DITANGGUHKAN' => '<span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Ditangguhkan</span>',
            default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1"><i class="fa-solid fa-file-circle-exclamation me-1"></i> Belum Lengkap</span>',
        };
    }

    public function getArsipStatusBadgeAttribute(): string
    {
        if ($this->is_arsip_locked || $this->status_praktik === 'AKTIF_LEGAL') {
            return '<span class="badge bg-emerald-subtle text-emerald border border-emerald fs-xs"><i class="fa-solid fa-lock text-success me-1"></i> e-Arsip Terkunci Resmi</span>';
        }

        return '<span class="badge bg-amber-subtle text-amber-emphasis border border-warning fs-xs"><i class="fa-solid fa-lock-open me-1"></i> Arsip Belum Terkunci</span>';
    }
}

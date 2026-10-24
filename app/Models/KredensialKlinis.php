<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KredensialKlinis extends Model
{
    use HasFactory;

    protected $table = 'kredensial_klinis';

    protected $fillable = [
        'id_dokter',
        'nomor_spk',
        'tanggal_terbit_spk',
        'tanggal_berakhir_spk',
        'status_kredensial',
        'file_rkk',
        'jenis_pengajuan',
        'alasan_rekredensial',
        'tahap_rekredensial',
        'tanggal_sidang',
        'nama_mitra_bestari',
        'evaluasi_logbook',
        'evaluasi_portofolio',
        'catatan_mitra_bestari',
        'daftar_rkk',
        'nomor_surat_rekomendasi',
        'tanggal_rekomendasi',
        'ketua_komite_medik',
        'catatan_rekomendasi',
        'status_rekomendasi',
        'is_suspended',
        'suspended_at',
        'alasan_penangguhan',
        'tanggal_sah_spk',
        'nomor_sk_direktur',
        'tanggal_sk_direktur',
        'file_sk_direktur',
        'is_spk_locked',
        'is_rkk_locked',
        'disahkan_oleh_sdm',
    ];

    protected $casts = [
        'tanggal_terbit_spk' => 'date',
        'tanggal_berakhir_spk' => 'date',
        'tanggal_sidang' => 'date',
        'tanggal_rekomendasi' => 'date',
        'tanggal_sah_spk' => 'date',
        'tanggal_sk_direktur' => 'date',
        'suspended_at' => 'datetime',
        'daftar_rkk' => 'array',
        'is_suspended' => 'boolean',
        'is_spk_locked' => 'boolean',
        'is_rkk_locked' => 'boolean',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }

    public function petugasSdmPengesah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disahkan_oleh_sdm');
    }

    public function hitungSisaHari(): int
    {
        if (! $this->tanggal_berakhir_spk) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays(Carbon::parse($this->tanggal_berakhir_spk), false);
    }

    public function isSuspended(): bool
    {
        return (bool) ($this->is_suspended || $this->status_kredensial === 'ditangguhkan' || $this->dokter?->isSuspended());
    }

    public function isWewenangMandiriDiblokir(): bool
    {
        return $this->isSuspended();
    }

    public function updateStatusKredensial(): string
    {
        // Tahap 7: Auto-Suspend jika dokter atau SPK ditangguhkan akibat kedaluwarsa SIP
        if ($this->isSuspended()) {
            $this->status_kredensial = 'ditangguhkan';
            $this->is_suspended = true;
            $this->save();

            return 'ditangguhkan';
        }

        // Jika sedang dalam alur proses rekredensialing atau rekomendasi, pertahankan status alur
        if (in_array($this->status_kredensial, ['proses_rekredensial', 'sidang_komite', 'rekomendasi_terbit'])) {
            return $this->status_kredensial;
        }

        $selisih = $this->hitungSisaHari();

        if ($selisih < 0) {
            $this->status_kredensial = 'kedaluwarsa';
        } elseif ($selisih <= 90) {
            $this->status_kredensial = 'evaluasi';
        } else {
            $this->status_kredensial = 'aktif';
        }

        $this->save();

        return $this->status_kredensial;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isSuspended()) {
            return 'DITANGGUHKAN (Suspended: Pelayanan Mandiri Terblokir)';
        }

        return match ($this->status_kredensial) {
            'ditangguhkan' => 'DITANGGUHKAN (Suspended: Pelayanan Mandiri Terblokir)',
            'proses_rekredensial' => 'Proses Rekredensial (Menunggu Sidang)',
            'sidang_komite' => 'Sidang Kredensial (Telaah Mitra Bestari)',
            'rekomendasi_terbit' => 'Rekomendasi RKK Diterbitkan',
            'aktif' => 'Aktif (Kewenangan Berlaku)',
            'evaluasi' => 'Evaluasi Rekredensialing (≤90 Hari)',
            'kedaluwarsa' => 'Kedaluwarsa (Melampaui 3 Tahun)',
            default => ucfirst(str_replace('_', ' ', $this->status_kredensial)),
        };
    }

    public function isProteksiPelayananAktif(): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        return (bool) $this->dokter?->hasActiveDispensasi();
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->isSuspended()) {
            return '<span class="badge bg-danger text-white border border-danger shadow-xs"><i class="fa-solid fa-ban me-1"></i> SPK & RKK DITANGGUHKAN (Suspended)</span>';
        }

        if ($this->isProteksiPelayananAktif() && $this->status_kredensial === 'aktif') {
            return '<span class="badge bg-teal text-white border border-teal"><i class="fa-solid fa-shield-check me-1"></i> SPK Terproteksi (Grace Period)</span>';
        }

        return match ($this->status_kredensial) {
            'ditangguhkan' => '<span class="badge bg-danger text-white border border-danger shadow-xs"><i class="fa-solid fa-ban me-1"></i> SPK & RKK DITANGGUHKAN (Suspended)</span>',
            'proses_rekredensial' => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-clock-rotate-left me-1"></i> Proses Rekredensial</span>',
            'sidang_komite' => '<span class="badge bg-info-subtle text-info-emphasis border border-info"><i class="fa-solid fa-gavel me-1"></i> Sidang Kredensial</span>',
            'rekomendasi_terbit' => '<span class="badge bg-primary-subtle text-primary border border-primary"><i class="fa-solid fa-file-signature me-1"></i> Rekomendasi Terbit</span>',
            'aktif' => '<span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i> SPK Aktif</span>',
            'evaluasi' => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i> Evaluasi (≤90 Hari)</span>',
            'kedaluwarsa' => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Kedaluwarsa</span>',
            default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary">'.ucfirst($this->status_kredensial).'</span>',
        };
    }

    public function getTahapLabelAttribute(): string
    {
        return match ($this->tahap_rekredensial) {
            'menunggu_sidang' => 'Menunggu Jadwal Sidang Mitra Bestari',
            'sidang_selesai' => 'Sidang Kredensial Selesai (RKK Disetujui)',
            'rekomendasi_terbit' => 'Surat Rekomendasi Dikirim ke Direktur',
            'spk_diterbitkan' => 'SPK & RKK Baru Telah Ditetapkan',
            default => ucfirst(str_replace('_', ' ', $this->tahap_rekredensial ?: 'menunggu_sidang')),
        };
    }

    public function getRkkMandiriCountAttribute(): int
    {
        if (! is_array($this->daftar_rkk)) {
            return 0;
        }

        return count(array_filter($this->daftar_rkk, fn ($item) => ($item['status'] ?? '') === 'mandiri'));
    }

    public function getRkkSupervisiCountAttribute(): int
    {
        if (! is_array($this->daftar_rkk)) {
            return 0;
        }

        return count(array_filter($this->daftar_rkk, fn ($item) => ($item['status'] ?? '') === 'supervisi'));
    }

    public function getRkkTotalCountAttribute(): int
    {
        return is_array($this->daftar_rkk) ? count($this->daftar_rkk) : 0;
    }
}

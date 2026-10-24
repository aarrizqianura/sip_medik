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
        'permohonan_kewenangan_baru',
        'catatan_kewenangan_baru',
        'checklist_keabsahan_fasyankes',
        'checklist_masa_berlaku',
        'checklist_keterbacaan_pindaian',
        'is_sip_locked',
        'is_overdue_locked',
        'overdue_locked_at',
        'arsip_locked_at',
        'tingkat_eskalasi',
        'terakhir_notifikasi_dokter_at',
        'terakhir_eskalasi_sdm_at',
        'log_eskalasi_terakhir',
        'status_dispensasi',
        'nomor_resi_proses',
        'instansi_penerbit_resi',
        'tanggal_pengajuan_resi',
        'file_bukti_proses',
        'keterangan_proses',
        'durasi_dispensasi_hari',
        'tanggal_mulai_dispensasi',
        'tanggal_akhir_dispensasi',
        'catatan_verifikasi_dispensasi',
        'dispensasi_verified_by',
        'dispensasi_verified_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'tanggal_pengajuan_resi' => 'date',
        'tanggal_mulai_dispensasi' => 'date',
        'tanggal_akhir_dispensasi' => 'date',
        'verified_at' => 'datetime',
        'arsip_locked_at' => 'datetime',
        'overdue_locked_at' => 'datetime',
        'terakhir_notifikasi_dokter_at' => 'datetime',
        'terakhir_eskalasi_sdm_at' => 'datetime',
        'dispensasi_verified_at' => 'datetime',
        'tempat_praktik_ke' => 'integer',
        'durasi_dispensasi_hari' => 'integer',
        'is_sip_locked' => 'boolean',
        'is_overdue_locked' => 'boolean',
        'status_kecukupan_skp' => 'boolean',
        'permohonan_kewenangan_baru' => 'boolean',
        'checklist_keabsahan_fasyankes' => 'boolean',
        'checklist_masa_berlaku' => 'boolean',
        'checklist_keterbacaan_pindaian' => 'boolean',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dispensasiVerifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensasi_verified_by');
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

    /**
     * Hitung sisa hari masa dispensasi (Grace Period)
     */
    public function hitungSisaHariDispensasi(): int
    {
        if (! $this->tanggal_akhir_dispensasi) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays(Carbon::parse($this->tanggal_akhir_dispensasi), false);
    }

    /**
     * Cek apakah masa tenggang administratif (Grace Period) sedang aktif berjalan
     */
    public function isGracePeriodAktif(): bool
    {
        if ($this->status_dispensasi !== 'disetujui' || ! $this->tanggal_akhir_dispensasi) {
            return false;
        }

        return Carbon::today()->lte(Carbon::parse($this->tanggal_akhir_dispensasi));
    }

    /**
     * Cek apakah dokter sedang menunggu verifikasi resi bukti proses oleh SDM
     */
    public function isMenungguVerifikasiDispensasi(): bool
    {
        return $this->status_dispensasi === 'diajukan';
    }

    /**
     * Menentukan apakah dokumen SIP memenuhi syarat untuk mengajukan masa tenggang administratif
     */
    public function isBisaAjukanDispensasi(): bool
    {
        if (in_array($this->status_dispensasi, ['diajukan', 'disetujui'])) {
            return false;
        }

        $sisa = $this->hitungSisaHari();

        // Bisa diajukan jika mendekati jatuh tempo (≤90 hari) atau sudah jatuh tempo saat perpanjangan
        return $sisa <= 90 || in_array($this->status, ['due_soon', 'due', 'critical', 'overdue']);
    }

    /**
     * Periksa apakah notifikasi untuk tingkat eskalasi tertentu sudah dikirim hari ini
     */
    public function pernahDikirimNotifikasi(string $tingkatEskalasi, string $tipePenerima = 'dokter'): bool
    {
        return $this->notifikasi()
            ->where('tingkat_eskalasi', $tingkatEskalasi)
            ->where('tipe_penerima', $tipePenerima)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    public function updateStatusKadaluarsa(): string
    {
        // Proteksi Pelayanan: Jika masa dispensasi administratif (Grace Period) aktif disetujui
        if ($this->isGracePeriodAktif()) {
            $this->status = 'dalam_proses';
            $this->tingkat_eskalasi = 'grace_period';
            $this->is_overdue_locked = false;
            $this->save();

            return $this->status;
        }

        // Jika masa dispensasi telah habis batas waktunya
        if ($this->status_dispensasi === 'disetujui' && $this->tanggal_akhir_dispensasi && Carbon::today()->gt(Carbon::parse($this->tanggal_akhir_dispensasi))) {
            $this->status_dispensasi = 'berakhir';
        }

        $selisih = $this->hitungSisaHari();

        // Tahap 7 Kunci Status Kedaluwarsa: Jika sisa_hari <= 0 dan dokter tidak memiliki status dispensasi aktif,
        // sistem otomatis mengunci status SIP menjadi overdue (Merah) dan memicu auto-suspend wewenang klinis.
        if ($selisih <= 0) {
            $this->status = 'overdue';
            $this->tingkat_eskalasi = 'kedaluwarsa';
            $this->is_overdue_locked = true;
            if (! $this->overdue_locked_at) {
                $this->overdue_locked_at = Carbon::now();
            }
            $this->save();

            // Pemicu otomatis penangguhan wewenang klinis (Auto-Suspend SPK/RKK & Dokter)
            $this->dokter?->autoSuspendWewenangKlinis($this);

            return $this->status;
        }

        // Reset kunci overdue jika tanggal diperpanjang/valid
        $this->is_overdue_locked = false;
        $this->overdue_locked_at = null;

        if ($selisih <= 7) {
            $this->status = 'critical';
            $this->tingkat_eskalasi = 'eskalasi_kritis';
        } elseif ($selisih <= 30) {
            $this->status = 'due';
            $this->tingkat_eskalasi = 'peringatan_mendesak';
        } elseif ($selisih <= 90) {
            $this->status = 'due_soon';
            $this->tingkat_eskalasi = 'pengingat_pertama';
        } else {
            $this->status = 'aktif';
            $this->tingkat_eskalasi = 'aman';
        }

        $this->save();

        return $this->status;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->hitungSisaHari() <= 0 && ! $this->isGracePeriodAktif());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dalam_proses' => 'Dalam Proses (Grace Period)',
            'aktif' => 'Aktif (>90 Hari)',
            'due_soon' => 'Due Soon (H-90 s.d H-60)',
            'due' => 'Peringatan Mendesak (H-30)',
            'critical' => 'Eskalasi Kritis (H-7)',
            'overdue' => 'Kedaluwarsa / Overdue (Terkunci)',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'dalam_proses' => 'bg-teal-100 text-teal-800 border-teal-400 font-bold',
            'aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'due_soon' => 'bg-amber-100 text-amber-800 border-amber-300',
            'due' => 'bg-orange-100 text-orange-800 border-orange-300',
            'critical' => 'bg-orange-200 text-orange-950 border-orange-500 font-bold',
            'overdue' => 'bg-rose-600 text-white border-rose-700 font-bold shadow-xs',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'dalam_proses' => '<span class="badge bg-teal text-white border border-teal shadow-2xs"><i class="fa-solid fa-shield-check me-1"></i> Grace Period Aktif</span>',
            'aktif' => '<span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i> Aktif</span>',
            'due_soon' => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="fa-solid fa-clock me-1"></i> Due Soon</span>',
            'due' => '<span class="badge bg-orange-subtle text-orange-emphasis border border-orange"><i class="fa-solid fa-triangle-exclamation me-1"></i> Peringatan Mendesak</span>',
            'critical' => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-bell me-1"></i> Eskalasi Kritis</span>',
            'overdue' => '<span class="badge bg-danger text-white border border-danger shadow-xs"><i class="fa-solid fa-lock me-1"></i> Overdue (Terkunci)</span>',
            default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary">'.ucfirst($this->status).'</span>',
        };
    }

    public function getStatusDispensasiBadgeAttribute(): string
    {
        return match ($this->status_dispensasi) {
            'diajukan' => '<span class="badge bg-warning text-dark border border-warning shadow-2xs"><i class="fa-solid fa-hourglass-half me-1"></i> Resi Diajukan (Menunggu SDM)</span>',
            'disetujui' => '<span class="badge bg-teal text-white border border-teal shadow-2xs"><i class="fa-solid fa-shield-check me-1"></i> Grace Period Aktif</span>',
            'ditolak' => '<span class="badge bg-danger-subtle text-danger border border-danger shadow-2xs"><i class="fa-solid fa-ban me-1"></i> Dispensasi Ditolak</span>',
            'berakhir' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary shadow-2xs"><i class="fa-solid fa-clock-rotate-left me-1"></i> Grace Period Habis</span>',
            default => '<span class="badge bg-light text-muted border fs-2xs">Tanpa Dispensasi</span>',
        };
    }

    public function getProteksiPelayananLabelAttribute(): string
    {
        if ($this->isGracePeriodAktif()) {
            $sisa = $this->hitungSisaHariDispensasi();

            return "Pelayanan Terproteksi (Grace Period tersisa {$sisa} hari s.d. {$this->tanggal_akhir_dispensasi->format('d/m/Y')})";
        }

        return 'Standar Regulasi SIP';
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

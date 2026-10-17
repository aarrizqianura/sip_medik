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
    ];

    protected $casts = [
        'tanggal_terbit_spk' => 'date',
        'tanggal_berakhir_spk' => 'date',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'id_dokter');
    }

    public function hitungSisaHari(): int
    {
        if (!$this->tanggal_berakhir_spk) {
            return 0;
        }
        return (int) Carbon::today()->diffInDays(Carbon::parse($this->tanggal_berakhir_spk), false);
    }

    public function updateStatusKredensial(): string
    {
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
        return match ($this->status_kredensial) {
            'aktif' => 'Aktif (Kewenangan Berlaku)',
            'evaluasi' => 'Evaluasi Rekredensialing (≤90 Hari)',
            'kedaluwarsa' => 'Kedaluwarsa',
            default => ucfirst($this->status_kredensial),
        };
    }
}

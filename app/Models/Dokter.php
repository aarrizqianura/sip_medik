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
        'status_kepegawaian',
        'status_str',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function ksm(): BelongsTo
    {
        return $this->belongsTo(Ksm::class, 'id_ksm');
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
}

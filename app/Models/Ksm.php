<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ksm extends Model
{
    use HasFactory;

    protected $table = 'ksm';

    protected $fillable = [
        'kode_ksm',
        'nama_ksm',
        'keterangan',
    ];

    public function dokters(): HasMany
    {
        return $this->hasMany(Dokter::class, 'id_ksm');
    }
}

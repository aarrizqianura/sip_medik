<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'id_user',
        'id_sip',
        'pesan',
        'kanal',
        'status_terkirim',
        'status_baca',
    ];

    protected $casts = [
        'status_terkirim' => 'boolean',
        'status_baca' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function dokumenSip(): BelongsTo
    {
        return $this->belongsTo(DokumenSip::class, 'id_sip');
    }
}

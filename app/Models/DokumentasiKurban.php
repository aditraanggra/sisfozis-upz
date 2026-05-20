<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumentasiKurban extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_kurban_id',
        'jenis',
        'file_path',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function laporanKurban(): BelongsTo
    {
        return $this->belongsTo(LaporanKurban::class, 'laporan_kurban_id');
    }
}

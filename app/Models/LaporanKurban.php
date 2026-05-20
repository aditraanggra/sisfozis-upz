<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanKurban extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'user_id',
        'tahun_hijriah',
        'tahun_masehi',
        'jml_sapi',
        'jml_kerbau',
        'jml_kambing',
        'jml_domba',
        'total_ekor',
        'total_shohibul',
        'shohibul_dikoreksi',
        'alasan_koreksi',
        'jml_paket_daging',
        'jml_penerima',
        'kelompok_penerima',
        'catatan',
        'status',
        'no_referensi',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tahun_masehi' => 'integer',
        'jml_sapi' => 'integer',
        'jml_kerbau' => 'integer',
        'jml_kambing' => 'integer',
        'jml_domba' => 'integer',
        'total_ekor' => 'integer',
        'total_shohibul' => 'integer',
        'shohibul_dikoreksi' => 'boolean',
        'jml_paket_daging' => 'integer',
        'jml_penerima' => 'integer',
        'kelompok_penerima' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitZis::class, 'unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dokumentasi(): HasMany
    {
        return $this->hasMany(DokumentasiKurban::class, 'laporan_kurban_id');
    }
}

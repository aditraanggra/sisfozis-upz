<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllocationConfig extends Model
{
    use HasFactory;

    protected $table = 'allocation_configs';

    protected $fillable = [
        'zis_type',
        'effective_year',
        'setor_percentage',
        'kelola_percentage',
        'amil_percentage',
        'description',
    ];

    protected $casts = [
        'effective_year' => 'integer',
        'setor_percentage' => 'decimal:2',
        'kelola_percentage' => 'decimal:2',
        'amil_percentage' => 'decimal:2',
    ];

    // ZIS type enum values
    public const TYPE_ZF = 'zf';
    public const TYPE_ZM = 'zm';
    public const TYPE_IFS = 'ifs';

    public const TYPES = [
        self::TYPE_ZF => 'Zakat Fitrah',
        self::TYPE_ZM => 'Zakat Maal',
        self::TYPE_IFS => 'Infak/Sedekah',
    ];

    // Default fallback percentages (matching current hardcoded values)
    public const DEFAULT_SETOR = 30.00;
    public const DEFAULT_KELOLA = 70.00;
    public const DEFAULT_AMIL_ZF_ZM = 12.50;
    public const DEFAULT_AMIL_IFS = 20.00;
}

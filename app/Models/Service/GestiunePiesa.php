<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FacturiFurnizori\FacturaFurnizor;

class GestiunePiesa extends Model
{
    use HasFactory;

    protected $table = 'service_gestiune_piese';

    protected $fillable = [
        'factura_id',
        'denumire',
        'cod',
        'nr_bucati',
        'pret',
        'tva_cota',
        'valoare_tva',
        'pret_brut',
    ];

    protected $casts = [
        'nr_bucati' => 'decimal:2',
        'pret' => 'decimal:2',
        'tva_cota' => 'decimal:2',
        'valoare_tva' => 'decimal:2',
        'pret_brut' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Service\GestiunePiesaFactory::new();
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaFurnizor::class, 'factura_id');
    }
}

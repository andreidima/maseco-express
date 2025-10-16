<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\Service\MasinaServiceEntry;

class GestiunePiesa extends Model
{
    use HasFactory;

    protected $table = 'service_gestiune_piese';

    protected $fillable = [
        'factura_id',
        'denumire',
        'cod',
        'cantitate_initiala',
        'nr_bucati',
        'pret',
        'tva_cota',
        'pret_brut',
    ];

    protected $casts = [
        'cantitate_initiala' => 'decimal:2',
        'nr_bucati' => 'decimal:2',
        'pret' => 'decimal:2',
        'tva_cota' => 'decimal:2',
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

    public function serviceEntries(): HasMany
    {
        return $this->hasMany(MasinaServiceEntry::class, 'gestiune_piesa_id');
    }

    public function getCantitateUtilizataAttribute(): float
    {
        $initial = $this->cantitate_initiala;

        if ($initial === null) {
            $initial = $this->nr_bucati;
        }

        if ($initial === null) {
            return 0.0;
        }

        $remaining = $this->nr_bucati ?? 0.0;
        $used = (float) $initial - (float) $remaining;

        return $used > 0 ? round($used, 2) : 0.0;
    }
}

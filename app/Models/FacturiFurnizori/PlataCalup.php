<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlataCalup extends Model
{
    use HasFactory;

    protected $table = 'service_ff_plati_calupuri';

    protected $fillable = [
        'denumire_calup',
        'data_plata',
        'observatii',
    ];

    protected $casts = [
        'data_plata' => 'date',
    ];

    /**
     * Invoices linked to this payment batch.
     */
    public function facturi(): BelongsToMany
    {
        return $this->belongsToMany(FacturaFurnizor::class, 'service_ff_facturi_plati', 'calup_id', 'factura_id')->withTimestamps();
    }

    public function fisiere(): HasMany
    {
        return $this->hasMany(PlataCalupFisier::class, 'plata_calup_id');
    }
}

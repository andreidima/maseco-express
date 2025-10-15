<?php

namespace App\Models\FacturiFurnizori;

use App\Models\FacturiFurnizori\GestiunePiesa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacturaFurnizor extends Model
{
    use HasFactory;

    protected $table = 'ff_facturi';

    protected $fillable = [
        'denumire_furnizor',
        'numar_factura',
        'data_factura',
        'data_scadenta',
        'suma',
        'moneda',
        'cont_iban',
        'departament_vehicul',
        'observatii',
    ];

    protected $casts = [
        'data_factura' => 'date',
        'data_scadenta' => 'date',
        'suma' => 'decimal:2',
    ];

    /**
     * Batch payments associated with the invoice.
     */
    public function calupuri()
    {
        return $this->belongsToMany(PlataCalup::class, 'ff_facturi_plati', 'factura_id', 'calup_id')->withTimestamps();
    }

    public function piese(): HasMany
    {
        return $this->hasMany(GestiunePiesa::class, 'factura_id');
    }
}

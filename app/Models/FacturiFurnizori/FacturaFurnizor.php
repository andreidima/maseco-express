<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'departament_vehicul',
        'observatii',
        'status',
    ];

    protected $casts = [
        'data_factura' => 'date',
        'data_scadenta' => 'date',
        'suma' => 'decimal:2',
    ];

    public const STATUS_NEPLATITA = 'neplatita';
    public const STATUS_PLATITA = 'platita';

    /**
     * Batch payments associated with the invoice.
     */
    public function calupuri()
    {
        return $this->belongsToMany(PlataCalup::class, 'ff_facturi_plati', 'factura_id', 'calup_id')->withTimestamps();
    }
}

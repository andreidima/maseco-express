<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlataCalup extends Model
{
    use HasFactory;

    protected $table = 'ff_plati_calupuri';

    protected $fillable = [
        'denumire_calup',
        'data_plata',
        'fisier_pdf',
        'observatii',
        'status',
    ];

    protected $casts = [
        'data_plata' => 'date',
    ];

    public const STATUS_DESCHIS = 'deschis';
    public const STATUS_PLATIT = 'platit';
    public const STATUS_ANULAT = 'anulat';

    /**
     * Invoices linked to this payment batch.
     */
    public function facturi()
    {
        return $this->belongsToMany(FacturaFurnizor::class, 'ff_facturi_plati', 'calup_id', 'factura_id')->withTimestamps();
    }
}

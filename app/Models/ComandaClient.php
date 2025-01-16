<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ComandaClient extends Model
{
    use HasFactory;

    protected $table = 'comenzi_clienti';
    protected $guarded = [];

    public function limba()
    {
        return $this->belongsTo(Limba::class, 'limba_id');
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function termenDePlata()
    {
        return $this->belongsTo(termenDePlata::class, 'termen_plata_id');
    }

    public function procentTva()
    {
        return $this->belongsTo(ProcentTVA::class, 'procent_tva_id');
    }

    public function metodaDePlata()
    {
        return $this->belongsTo(MetodaDePlata::class, 'metoda_de_plata_id');
    }

    /**
     * Get the factura associated with the ComandaClient
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class, 'comanda_client_id');
    }

    /**
     * Get the client associated with the ComandaClient
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client(): HasOne
    {
        return $this->hasOne(Firma::class, 'client_id');
    }
}

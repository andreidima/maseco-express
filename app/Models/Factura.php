<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturi';
    protected $guarded = [];

    public function path()
    {
        return "/facturi/{$this->id}";
    }

    // this is the recommended way for declaring event handlers
    public static function boot() {
        parent::boot();
        self::deleting(function($factura) { // before delete() method call this
             $factura->produse()->each(function($produs) {
                $produs->delete(); // <-- direct deletion
             });
             // do the rest of the cleanup...
        });
    }

    /**
     * Get all of the comenzi for the Factura
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comenzi(): HasMany
    {
        return $this->hasMany(Comanda::class, 'factura_id');
    }
    public function comanda()
    {
        return $this->hasOne(Comanda::class, 'factura_id');
    }

    // Daca este factura STORNO, se incarca si factura originala
    public function facturaOriginala()
    {
        return $this->hasOne(Factura::class, 'id', 'stornare_factura_id_originala');
    }

    public function produse()
    {
        return $this->hasMany(FacturaProdus::class, 'factura_id');
    }

    public function chitante()
    {
        return $this->hasMany(FacturaChitanta::class, 'factura_id');
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function procentTva()
    {
        return $this->belongsTo(ProcentTVA::class, 'procent_tva_id');
    }

    public function clientTara()
    {
        return $this->belongsTo(Tara::class, 'client_tara_id');
    }


    // Added on 14.01.2025 - to set more clients to a command, not just one
    /**
     * Get the comandaClient that owns with the Factura
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comandaClient(): BelongsTo
    {
        return $this->belongsTo(ComandaClient::class, 'comanda_client_id',);
    }
}

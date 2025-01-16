<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaClientIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'comenzi_clienti_istoric';
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Firma extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'firme';
    protected $guarded = [];

    public function path($tipPartener = null)
    {
        return "/firme/$tipPartener/{$this->id}";
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function tara()
    {
        return $this->belongsTo(Tara::class);
    }

    public function camioane()
    {
        return $this->hasMany(Camion::class);
    }

    // public function comenziCaSiClient()
    // {
    //     return $this->hasMany(Comanda::class, 'client_client_id');
    // }
    /**
     * Get the comandaPivotInfo associated with the Firma
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function comenziCaSiTransportator()
    {
        return $this->hasMany(Comanda::class, 'transportator_transportator_id');
    }

    public function istoricuri()
    {
        return $this->hasMany(FirmaIstoric::class, 'id');
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->telefon;
        // return '+40749262658';
    }

    public function contracteCcaTrimisePeEmailCatreTransportator()
    {
        return $this->hasMany(MesajTrimisEmail::class, 'firma_id')->where('categorie', 4);
    }
}

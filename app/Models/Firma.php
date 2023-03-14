<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

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

    /**
     * Get all of the camioane for the Firma
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function camioane()
    {
        return $this->hasMany(Camion::class);
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
}

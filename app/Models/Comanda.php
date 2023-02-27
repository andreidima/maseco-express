<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comanda extends Model
{
    use HasFactory;

    protected $table = 'comenzi';
    protected $guarded = [];

    public function path()
    {
        return "/comenzi/{$this->id}";
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function client()
    {
        return $this->belongsTo(Firma::class, 'client_client_id');
    }

    public function transportator()
    {
        return $this->belongsTo(Firma::class, 'transportator_transportator_id');
    }

    public function istoricuri()
    {
        return $this->hasMany(ComandaIstoric::class, 'id');
    }

    public function locuriOperare()
    {
        return $this->belongsToMany(LocOperare::class, 'comenzi_locuri_operare', 'comanda_id', 'loc_operare_id');
    }

    public function locuriOperareIncarcari()
    {
        return $this->locuriOperare()->where('tip', '1')->orderBy('ordine');
    }

    public function locuriOperareDescarcari()
    {
        return $this->locuriOperare()->where('tip', '2')->orderBy('ordine');
    }
}

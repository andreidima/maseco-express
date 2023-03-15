<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocOperare extends Model
{
    use HasFactory;

    protected $table = 'locuri_operare';
    protected $guarded = [];

    public function path()
    {
        return "/locuri-operare/{$this->id}";
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function tara()
    {
        return $this->belongsTo(Tara::class);
    }

    public function istoricuri()
    {
        return $this->hasMany(LocOperareIstoric::class, 'id');
    }

    // public function locuriOperare()
    // {
    //     return $this->belongsToMany(Comanda::class, 'comenzi_locuri_operare', 'loc_operare_id', 'comanda_id')->withPivot('ordine', 'data_ora', 'observatii', 'referinta');
    // }
    public function comenzi()
    {
        return $this->belongsToMany(Comanda::class, 'comenzi_locuri_operare', 'loc_operare_id', 'comanda_id')->withPivot('ordine', 'data_ora', 'observatii', 'referinta');
    }
}

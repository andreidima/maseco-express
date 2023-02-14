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

    public function firma()
    {
        return $this->belongsTo(Firma::class);
    }

    public function istoricuri()
    {
        return $this->hasMany(ComandaIstoric::class, 'id');
    }
}

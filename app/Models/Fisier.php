<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Fisier extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'fisiere';
    protected $guarded = [];

    public function path($categorieFisier = null)
    {
        return "/fisiere/$categorieFisier/{$this->id}";
    }

    public function descarca($categorieFisier = null)
    {
        return "/fisiere/$categorieFisier/{$this->id}/descarca";
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function istoricuri()
    {
        return $this->hasMany(FisierIstoric::class, 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\FirmaIstoric;

class Firma extends Model
{
    use HasFactory;

    protected $table = 'firme';
    protected $guarded = [];

    public function path()
    {
        return "/firme/{$this->id}";
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function istoricuri()
    {
        return $this->hasMany(FirmaIstoric::class, 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firma extends Model
{
    use HasFactory;

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

    public function istoricuri()
    {
        return $this->hasMany(FirmaIstoric::class, 'id');
    }
}

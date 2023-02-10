<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camion extends Model
{
    use HasFactory;

    protected $table = 'camioane';
    protected $guarded = [];

    public function path()
    {
        return "/camioane/{$this->id}";
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function istoricuri()
    {
        return $this->hasMany(CamionIstoric::class, 'id');
    }
}

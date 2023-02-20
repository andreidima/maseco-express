<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodaDePlata extends Model
{
    use HasFactory;

    protected $table = 'metode_de_plata';
    protected $guarded = [];

    public function path()
    {
        return "/metode-de-plata/{$this->id}";
    }
}

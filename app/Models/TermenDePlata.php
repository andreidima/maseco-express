<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermenDePlata extends Model
{
    use HasFactory;

    protected $table = 'termene_de_plata';
    protected $guarded = [];

    public function path()
    {
        return "/termene-de-plata/{$this->id}";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaFisier extends Model
{
    use HasFactory;

    protected $table = 'comenzi_fisiere';
    protected $guarded = [];

    public function path()
    {
        return "/comenzi-fisiere/{$this->id}";
    }
}

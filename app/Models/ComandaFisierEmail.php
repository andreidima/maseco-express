<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaFisierEmail extends Model
{
    use HasFactory;

    protected $table = 'comenzi_fisiere_emailuri';
    protected $guarded = [];
}

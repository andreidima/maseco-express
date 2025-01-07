<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatiePecoIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'statii_peco_istoric';
    protected $primaryKey = 'id_pk';
    protected $guarded = [];
}

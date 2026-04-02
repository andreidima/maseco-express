<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaFisierIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'comenzi_fisiere_istoric';
    protected $primaryKey = 'id_pk';
    protected $guarded = [];
    protected $casts = [
        'validat' => 'integer',
        'este_factura' => 'integer',
    ];

    public function path()
    {
        return "/comenzi-fisiere-istoric/{$this->id}";
    }
}

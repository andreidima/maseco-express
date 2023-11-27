<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaProdus extends Model
{
    use HasFactory;

    protected $table = 'facturi_produse';
    protected $guarded = [];

    public function path()
    {
        return "/facturi-produse/{$this->id}";
    }

    public function facturi()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}

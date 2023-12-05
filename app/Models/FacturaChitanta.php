<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaChitanta extends Model
{
    use HasFactory;

    protected $table = 'facturi_chitante';
    protected $guarded = [];

    public function path()
    {
        return "/facturi-chitante/{$this->id}";
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}

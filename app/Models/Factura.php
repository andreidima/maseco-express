<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturi';
    protected $guarded = [];

    public function path()
    {
        return "/facturi/{$this->id}";
    }

    // this is the recommended way for declaring event handlers
    public static function boot() {
        parent::boot();
        self::deleting(function($factura) { // before delete() method call this
             $factura->produse()->each(function($produs) {
                $produs->delete(); // <-- direct deletion
             });
             // do the rest of the cleanup...
        });
    }

    public function comanda()
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }

    public function produse()
    {
        return $this->hasMany(FacturaProdus::class, 'factura_id');
    }
}
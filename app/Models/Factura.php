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

    public function comenzi()
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }

    public function cursBnr()
    {
        return $this->belongsTo(CursBnr::class, 'curs_bnr_id');
    }
}

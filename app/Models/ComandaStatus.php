<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaStatus extends Model
{
    use HasFactory;

    protected $table = 'comenzi_statusuri';
    protected $guarded = [];

    public function path()
    {
        return "/comenzi-statusuri/{$this->id}";
    }

    public function comanda()
    {
        return $this->belongsTo('App\Models\Comanda', 'comanda_id');
    }
}

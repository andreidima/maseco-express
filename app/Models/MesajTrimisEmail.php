<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MesajTrimisEmail extends Model
{
    use HasFactory;

    protected $table = 'mesaje_trimise_email';
    protected $guarded = [];

    public function path()
    {
        return "/mesaje-trimise-email/{$this->id}";
    }

    public function comanda()
    {
        return $this->belongsTo('App\Models\Comanda', 'comanda_id');
    }

    public function firma()
    {
        return $this->belongsTo('App\Models\Comanda', 'firma_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaCronJob extends Model
{
    use HasFactory;

    protected $table = 'comenzi_cron_jobs';
    protected $guarded = [];

    // public function path()
    // {
    //     return "/tari/{$this->id}";
    // }

    public function comanda()
    {
        return $this->belongsTo(Comanda::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlotaStatusUtilizator extends Model
{
    use HasFactory;

    protected $table = 'flota_statusuri_utilizatori';
    protected $guarded = [];

    public function path()
    {
        return "/flota-statusuri-utilizatori/{$this->id}";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlotaStatusInformatie extends Model
{
    use HasFactory;

    protected $table = 'flota_statusuri_informatii';
    protected $guarded = [];

    public function path()
    {
        return "/flota-statusuri-informatii/{$this->id}";
    }
}

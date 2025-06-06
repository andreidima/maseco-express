<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlotaStatusC extends Model
{
    use HasFactory;

    protected $table = 'flota_statusuri_c';
    protected $guarded = [];

    public function path()
    {
        return "/flota-statusuri-c/{$this->id}";
    }
}

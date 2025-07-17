<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasinaValabilitati extends Model
{
    use HasFactory;

    protected $table = 'masini_valabilitati';
    protected $guarded = [];

    public function path()
    {
        return "/masini-valabilitati/{$this->id}";
    }
}

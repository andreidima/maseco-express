<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Valabilitate extends Model
{
    use HasFactory;

    protected $fillable = [
        'masina_id',
        'referinta',
        'prima_cursa',
        'ultima_cursa',
        'total_curse',
    ];

    protected $casts = [
        'prima_cursa' => 'immutable_datetime',
        'ultima_cursa' => 'immutable_datetime',
        'total_curse' => 'integer',
    ];

    public function masina(): BelongsTo
    {
        return $this->belongsTo(Masina::class);
    }

    public function curse(): HasMany
    {
        return $this->hasMany(ValabilitateCursa::class);
    }
}

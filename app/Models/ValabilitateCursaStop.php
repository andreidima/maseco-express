<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValabilitateCursaStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'valabilitate_cursa_id',
        'type',
        'cod_postal',
        'localitate',
        'position',
    ];

    protected $casts = [
        'valabilitate_cursa_id' => 'integer',
        'position' => 'integer',
    ];

    public function cursa(): BelongsTo
    {
        return $this->belongsTo(ValabilitateCursa::class, 'valabilitate_cursa_id');
    }
}

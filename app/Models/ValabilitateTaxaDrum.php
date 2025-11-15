<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValabilitateTaxaDrum extends Model
{
    use HasFactory;

    protected $table = 'valabilitati_taxe_drum';

    protected $fillable = [
        'valabilitate_id',
        'nume',
        'tara',
        'suma',
        'moneda',
        'data',
        'observatii',
    ];

    protected $casts = [
        'data' => 'date',
        'suma' => 'decimal:2',
    ];

    public function valabilitate(): BelongsTo
    {
        return $this->belongsTo(Valabilitate::class);
    }
}

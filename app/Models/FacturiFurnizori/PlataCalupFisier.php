<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlataCalupFisier extends Model
{
    use HasFactory;

    protected $table = 'ff_plati_calupuri_fisiere';

    protected $fillable = [
        'plata_calup_id',
        'cale',
        'nume_original',
    ];

    public function calup(): BelongsTo
    {
        return $this->belongsTo(PlataCalup::class, 'plata_calup_id');
    }
}

<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GestiunePiesa extends Model
{
    use HasFactory;

    protected $table = 'gestiune_piese';

    protected $fillable = [
        'factura_id',
        'denumire',
        'cod',
        'nr_bucati',
        'pret',
    ];

    protected $casts = [
        'nr_bucati' => 'decimal:2',
        'pret' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaFurnizor::class, 'factura_id');
    }
}

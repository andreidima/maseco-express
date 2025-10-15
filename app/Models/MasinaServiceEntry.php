<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FacturiFurnizori\GestiunePiesa;
use App\Models\User;

class MasinaServiceEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'masina_id',
        'gestiune_piesa_id',
        'user_id',
        'tip',
        'denumire_interventie',
        'cod_piesa',
        'denumire_piesa',
        'cantitate',
        'data_montaj',
        'nume_mecanic',
        'nume_utilizator',
        'observatii',
    ];

    protected $casts = [
        'data_montaj' => 'date',
        'cantitate' => 'decimal:2',
    ];

    public function masina(): BelongsTo
    {
        return $this->belongsTo(Masina::class);
    }

    public function piesa(): BelongsTo
    {
        return $this->belongsTo(GestiunePiesa::class, 'gestiune_piesa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

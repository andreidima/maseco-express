<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValabilitatiDivizie extends Model
{
    use HasFactory;

    protected $table = 'valabilitati_divizii';

    protected $fillable = [
        'nume',
        'pret_km_gol',
        'pret_km_plin',
        'pret_km_cu_taxa',
        'contributie_zilnica',
    ];

    protected $casts = [
        'pret_km_gol' => 'decimal:3',
        'pret_km_plin' => 'decimal:3',
        'pret_km_cu_taxa' => 'decimal:3',
        'contributie_zilnica' => 'decimal:3',
    ];

    public function valabilitati(): HasMany
    {
        return $this->hasMany(Valabilitate::class, 'divizie_id');
    }
}

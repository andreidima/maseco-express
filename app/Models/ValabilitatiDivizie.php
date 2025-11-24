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
        'flash_pret_km_gol',
        'flash_pret_km_plin',
        'flash_pret_km_cu_taxa',
        'flash_contributie_zilnica',
        'timestar_pret_km_bord',
        'timestar_pret_nr_zile_lucrate',
    ];

    protected $casts = [
        'flash_pret_km_gol' => 'decimal:3',
        'flash_pret_km_plin' => 'decimal:3',
        'flash_pret_km_cu_taxa' => 'decimal:3',
        'flash_contributie_zilnica' => 'decimal:3',
        'timestar_pret_km_bord' => 'decimal:3',
        'timestar_pret_nr_zile_lucrate' => 'decimal:3',
    ];

    public function valabilitati(): HasMany
    {
        return $this->hasMany(Valabilitate::class, 'divizie_id');
    }
}

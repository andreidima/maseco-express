<?php

namespace App\Models\FacturiTransportatori;

use App\Models\Comanda;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlataCalup extends Model
{
    use HasFactory;

    protected $table = 'facturi_transportatori_plati_calupuri';

    protected $fillable = [
        'denumire_calup',
        'data_plata',
        'observatii',
    ];

    protected $casts = [
        'data_plata' => 'date',
    ];

    public function comenzi(): BelongsToMany
    {
        return $this->belongsToMany(
            Comanda::class,
            'facturi_transportatori_plati_calupuri_comenzi',
            'calup_id',
            'comanda_id'
        )->withTimestamps();
    }

    public function fisiere(): HasMany
    {
        return $this->hasMany(PlataCalupFisier::class, 'plata_calup_id');
    }
}

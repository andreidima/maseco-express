<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Masina extends Model
{
    use HasFactory;

    protected $fillable = [
        'denumire',
        'numar_inmatriculare',
        'serie_sasiu',
        'observatii',
    ];

    public function serviceEntries(): HasMany
    {
        return $this->hasMany(MasinaServiceEntry::class);
    }
}

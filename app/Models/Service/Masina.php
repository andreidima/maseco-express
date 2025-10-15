<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Masina extends Model
{
    use HasFactory;

    protected $table = 'service_masini';

    protected $fillable = [
        'denumire',
        'numar_inmatriculare',
        'serie_sasiu',
        'observatii',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Service\MasinaFactory::new();
    }

    public function serviceEntries(): HasMany
    {
        return $this->hasMany(MasinaServiceEntry::class);
    }
}

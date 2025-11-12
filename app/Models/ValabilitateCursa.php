<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValabilitateCursa extends Model
{
    use HasFactory;

    protected $table = 'valabilitati_curse';

    protected $fillable = [
        'valabilitate_id',
        'incarcare_localitate',
        'incarcare_cod_postal',
        'descarcare_localitate',
        'descarcare_cod_postal',
        'data_cursa',
        'observatii',
    ];

    protected $casts = [
        'data_cursa' => 'datetime',
    ];

    public function valabilitate(): BelongsTo
    {
        return $this->belongsTo(Valabilitate::class);
    }

    protected static function booted(): void
    {
        static::saved(static function (ValabilitateCursa $cursa): void {
            $cursa->valabilitate?->syncSummary();
        });

        static::deleted(static function (ValabilitateCursa $cursa): void {
            $cursa->valabilitate?->syncSummary();
        });
    }
}

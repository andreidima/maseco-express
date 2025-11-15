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
        'nr_ordine',
        'nr_cursa',
        'incarcare_localitate',
        'incarcare_cod_postal',
        'incarcare_tara_id',
        'descarcare_localitate',
        'descarcare_cod_postal',
        'descarcare_tara_id',
        'data_cursa',
        'observatii',
        'km_bord_incarcare',
        'km_bord_descarcare',
    ];

    protected $casts = [
        'nr_ordine' => 'integer',
        'data_cursa' => 'datetime',
        'km_bord_incarcare' => 'integer',
        'km_bord_descarcare' => 'integer',
    ];

    public function incarcareTara(): BelongsTo
    {
        return $this->belongsTo(Tara::class, 'incarcare_tara_id');
    }

    public function descarcareTara(): BelongsTo
    {
        return $this->belongsTo(Tara::class, 'descarcare_tara_id');
    }

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

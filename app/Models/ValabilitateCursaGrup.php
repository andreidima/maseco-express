<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValabilitateCursaGrup extends Model
{
    use HasFactory;

    public const DEFAULT_COLOR = '#FEF9C3';

    private const COLOR_PALETTE = [
        '#FEF9C3' => 'Galben pal',
        '#E0F2FE' => 'Albastru pastel',
        '#E9D5FF' => 'Lavandă',
        '#FDE68A' => 'Grâu auriu',
        '#FBCFE8' => 'Roz pudrat',
        '#DCFCE7' => 'Verde mentă',
        '#FFE4E6' => 'Trandafiriu',
        '#F5F5F4' => 'Gri cald',
    ];

    private const DOCUMENT_FORMATS = [
        'per_post' => 'Per post',
        'digital' => 'Digital',
    ];

    protected $table = 'valabilitati_cursa_grupuri';

    protected $fillable = [
        'valabilitate_id',
        'nume',
        'format_documente',
        'suma_incasata',
        'suma_calculata',
        'data_factura',
        'numar_factura',
        'culoare_hex',
    ];

    protected $casts = [
        'data_factura' => 'date',
        'suma_incasata' => 'decimal:2',
        'suma_calculata' => 'decimal:2',
    ];

    public static function colorPalette(): array
    {
        return self::COLOR_PALETTE;
    }

    public static function documentFormats(): array
    {
        return self::DOCUMENT_FORMATS;
    }

    public static function defaultColor(): string
    {
        return self::DEFAULT_COLOR;
    }

    public function valabilitate(): BelongsTo
    {
        return $this->belongsTo(Valabilitate::class);
    }

    public function curse(): HasMany
    {
        return $this->hasMany(ValabilitateCursa::class, 'cursa_grup_id');
    }

    public function formatDocumenteLabel(): string
    {
        return self::DOCUMENT_FORMATS[$this->format_documente] ?? $this->format_documente;
    }
}

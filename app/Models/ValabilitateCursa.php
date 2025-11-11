<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ValabilitateCursa extends Model
{
    use HasFactory;

    protected $table = 'valabilitati_curse';

    protected $fillable = [
        'valabilitate_id',
        'localitate_plecare',
        'localitate_sosire',
        'descarcare_tara',
        'plecare_la',
        'sosire_la',
        'ora',
        'km_bord',
        'observatii',
        'ultima_cursa',
    ];

    protected $casts = [
        'plecare_la' => 'immutable_datetime',
        'sosire_la' => 'immutable_datetime',
        'km_bord' => 'integer',
        'ultima_cursa' => 'boolean',
    ];

    protected function descarcareTara(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? strtoupper(trim($value)) : null,
        );
    }

    public function valabilitate(): BelongsTo
    {
        return $this->belongsTo(Valabilitate::class);
    }

    protected static function booted(): void
    {
        static::saved(function (ValabilitateCursa $cursa): void {
            $cursa->valabilitate?->syncSummary();
        });

        static::deleted(function (ValabilitateCursa $cursa): void {
            $cursa->valabilitate?->syncSummary();
        });
    }

    public static function suggestLocalitati(string $term = '', int $limit = 10): Collection
    {
        $term = trim($term);

        $plecari = static::query()
            ->selectRaw('LOWER(TRIM(localitate_plecare)) as slug, TRIM(localitate_plecare) as name')
            ->whereNotNull('localitate_plecare')
            ->whereRaw("TRIM(localitate_plecare) <> ''");

        if ($term !== '') {
            $plecari->where('localitate_plecare', 'like', "%{$term}%");
        }

        $sosiri = static::query()
            ->selectRaw('LOWER(TRIM(localitate_sosire)) as slug, TRIM(localitate_sosire) as name')
            ->whereNotNull('localitate_sosire')
            ->whereRaw("TRIM(localitate_sosire) <> ''");

        if ($term !== '') {
            $sosiri->where('localitate_sosire', 'like', "%{$term}%");
        }

        $union = $plecari->unionAll($sosiri);

        return DB::query()
            ->fromSub($union, 'localitati')
            ->selectRaw('MIN(name) as name')
            ->groupBy('slug')
            ->orderBy('name')
            ->limit($limit)
            ->pluck('name');
    }
}

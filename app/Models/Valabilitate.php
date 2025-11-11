<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Valabilitate extends Model
{
    use HasFactory;

    protected $attributes = [
        'total_curse' => 0,
    ];

    protected $fillable = [
        'masina_id',
        'referinta',
        'prima_cursa',
        'ultima_cursa',
        'total_curse',
    ];

    protected $casts = [
        'prima_cursa' => 'immutable_datetime',
        'ultima_cursa' => 'immutable_datetime',
        'total_curse' => 'integer',
    ];

    public function masina(): BelongsTo
    {
        return $this->belongsTo(Masina::class);
    }

    public function curse(): HasMany
    {
        return $this->hasMany(ValabilitateCursa::class);
    }

    public function syncSummary(): void
    {
        $totalCurse = $this->curse()->count();

        $primaCursa = $this->curse()
            ->orderBy('plecare_la')
            ->value('plecare_la');

        $ultimaSosire = $this->curse()
            ->orderByDesc('sosire_la')
            ->value('sosire_la');

        $ultimaPlecare = $ultimaSosire === null
            ? $this->curse()->orderByDesc('plecare_la')->value('plecare_la')
            : null;

        $this->forceFill([
            'total_curse' => $totalCurse,
            'prima_cursa' => $primaCursa,
            'ultima_cursa' => $ultimaSosire ?? $ultimaPlecare,
        ])->saveQuietly();
    }
}

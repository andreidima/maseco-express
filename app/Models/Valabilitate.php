<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ValabilitatiAlimentare;
use App\Models\User;
use App\Models\ValabilitateTaxaDrum;
use App\Models\ValabilitateCursaGrup;
use App\Models\ValabilitatiDivizie;

class Valabilitate extends Model
{
    use HasFactory;

    protected $table = 'valabilitati';

    protected $fillable = [
        'numar_auto',
        'sofer_id',
        'divizie_id',
        'data_inceput',
        'data_sfarsit',
        'km_plecare',
        'km_sosire',
    ];

    protected $casts = [
        'data_inceput' => 'date',
        'data_sfarsit' => 'date',
        'km_plecare' => 'integer',
        'km_sosire' => 'integer',
    ];

    public function sofer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sofer_id');
    }

    public function divizie(): BelongsTo
    {
        return $this->belongsTo(ValabilitatiDivizie::class, 'divizie_id');
    }

    public function curse(): HasMany
    {
        return $this->hasMany(ValabilitateCursa::class)
            ->with(['cursaGrup'])
            ->orderBy('nr_ordine')
            ->orderBy('id');
    }

    /**
     * Alias required for Laravel's scoped implicit bindings which expect the
     * pluralised child parameter ("cursas") when resolving nested bindings.
     */
    public function cursas(): HasMany
    {
        return $this->curse();
    }

    public function taxeDrum(): HasMany
    {
        return $this->hasMany(ValabilitateTaxaDrum::class)
            ->orderBy('data')
            ->orderBy('id');
    }

    public function alimentari(): HasMany
    {
        return $this->hasMany(ValabilitatiAlimentare::class)
            ->orderByDesc('data_ora_alimentare')
            ->orderByDesc('id');
    }

    public function cursaGrupuri(): HasMany
    {
        return $this->hasMany(ValabilitateCursaGrup::class)
            ->orderBy('nume');
    }

    /**
     * Alias required for Laravel's scoped implicit bindings which expect the
     * pluralised child parameter ("grups") when resolving nested bindings.
     */
    public function grups(): HasMany
    {
        return $this->cursaGrupuri();
    }

    public function syncSummary(): void
    {
        // Funcționalitatea pentru curse va fi adăugată când schema este disponibilă.
    }
}

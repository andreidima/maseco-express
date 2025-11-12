<?php

namespace App\Services\Driver;

use App\Models\Tara;
use App\Models\User;
use App\Models\Valabilitate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class ActiveValabilitatiService
{
    private ?int $cachedRomaniaId = null;

    public function listForUser(User $user): EloquentCollection
    {
        $today = CarbonImmutable::today(Date::now()->timezone);

        return Valabilitate::query()
            ->where('sofer_id', $user->getKey())
            ->whereDate('data_inceput', '<=', $today)
            ->where(function ($query) use ($today): void {
                $query
                    ->whereNull('data_sfarsit')
                    ->orWhereDate('data_sfarsit', '>=', $today);
            })
            ->orderBy('data_sfarsit')
            ->orderBy('data_inceput')
            ->orderBy('denumire')
            ->withCount('curse')
            ->get([
                'id',
                'numar_auto',
                'denumire',
                'data_inceput',
                'data_sfarsit',
            ]);
    }

    public function loadDetail(Valabilitate $valabilitate): Valabilitate
    {
        return $valabilitate->load([
            'curse' => static function ($query): void {
                $query
                    ->orderBy('data_cursa')
                    ->orderBy('created_at');
            },
            'curse.incarcareTara:id,nume',
            'curse.descarcareTara:id,nume',
        ]);
    }

    public function ensureActiveForUser(User $user, Valabilitate $valabilitate): Valabilitate
    {
        if ((int) $valabilitate->sofer_id !== (int) $user->getKey()) {
            throw new ModelNotFoundException();
        }

        $today = CarbonImmutable::today(Date::now()->timezone);

        $isActive = $valabilitate->data_inceput !== null
            && $valabilitate->data_inceput->lte($today)
            && ($valabilitate->data_sfarsit === null || $valabilitate->data_sfarsit->gte($today));

        if (! $isActive) {
            throw new ModelNotFoundException();
        }

        return $valabilitate;
    }

    public function romaniaId(): ?int
    {
        if ($this->cachedRomaniaId !== null) {
            return $this->cachedRomaniaId;
        }

        $this->cachedRomaniaId = Tara::query()
            ->whereRaw('LOWER(nume) = ?', ['romania'])
            ->value('id');

        if ($this->cachedRomaniaId !== null) {
            $this->cachedRomaniaId = (int) $this->cachedRomaniaId;
        }

        return $this->cachedRomaniaId;
    }

    public function requiresRomaniaTime(?int $taraId): bool
    {
        if ($taraId === null) {
            return false;
        }

        $romaniaId = $this->romaniaId();

        if ($romaniaId !== null) {
            return $romaniaId === (int) $taraId;
        }

        $taraName = Tara::query()
            ->whereKey($taraId)
            ->value('nume');

        return $taraName !== null && Str::lower((string) $taraName) === 'romania';
    }
}

<?php

namespace App\Support\Valabilitati;

use App\Models\Valabilitate;
use Illuminate\Http\Request;

class ValabilitatiCurseFilterState
{
    private const SESSION_KEY = 'valabilitati_curse_filters';

    public static function remember(Request $request, Valabilitate $valabilitate, ?array $filters = null): void
    {
        $allFilters = $request->session()->get(self::SESSION_KEY, []);
        $allFilters[$valabilitate->getKey()] = $filters ?? self::extractQuery($request);

        $request->session()->put(self::SESSION_KEY, $allFilters);
    }

    public static function extractQuery(Request $request): array
    {
        return collect($request->query())
            ->except(['page', 'cursor'])
            ->toArray();
    }

    public static function get(Valabilitate $valabilitate): array
    {
        $filters = session(self::SESSION_KEY, []);

        return $filters[$valabilitate->getKey()] ?? [];
    }

    public static function route(Valabilitate $valabilitate): string
    {
        return route(
            'valabilitati.curse.index',
            array_merge(['valabilitate' => $valabilitate->getKey()], self::get($valabilitate))
        );
    }
}

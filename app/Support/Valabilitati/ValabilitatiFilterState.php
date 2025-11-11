<?php

namespace App\Support\Valabilitati;

use Illuminate\Http\Request;

class ValabilitatiFilterState
{
    private const SESSION_KEY = 'valabilitati_filters';

    public static function remember(Request $request, ?array $filters = null): void
    {
        $request->session()->put(self::SESSION_KEY, $filters ?? self::extractQuery($request));
    }

    public static function extractQuery(Request $request): array
    {
        return collect($request->query())
            ->except(['page', 'cursor'])
            ->toArray();
    }

    public static function get(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public static function route(): string
    {
        return route('valabilitati.index', self::get());
    }
}

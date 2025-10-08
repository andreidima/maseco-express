<?php

namespace App\Support\FacturiFurnizori;

use Illuminate\Http\Request;

class FacturiIndexFilterState
{
    public const SESSION_KEY = 'facturi_furnizori_facturi_filters';

    public static function remember(Request $request): void
    {
        $request->session()->put(self::SESSION_KEY, self::extractQuery($request));
    }

    public static function extractQuery(Request $request): array
    {
        return $request->query();
    }

    public static function get(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public static function route(): string
    {
        return route('facturi-furnizori.facturi.index', self::get());
    }
}

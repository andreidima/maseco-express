<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\LocOperareIstoric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocalitateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('term', ''));

        $query = LocOperareIstoric::query()
            ->selectRaw('DISTINCT oras as localitate')
            ->whereNotNull('oras')
            ->orderBy('oras');

        if ($term !== '') {
            $query->whereRaw('LOWER(oras) LIKE ?', ['%' . Str::lower($term) . '%']);
        }

        $localitati = $query
            ->limit(15)
            ->pluck('localitate')
            ->filter()
            ->values();

        return response()->json([
            'localitati' => $localitati,
        ]);
    }
}

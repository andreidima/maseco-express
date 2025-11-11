<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ValabilitateCursa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValabilitateLocalitatiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', '');
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min($limit, 25));

        $localitati = ValabilitateCursa::suggestLocalitati($term, $limit);

        return response()->json($localitati);
    }
}

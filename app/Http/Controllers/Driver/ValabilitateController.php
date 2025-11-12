<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\ValabilitateResource;
use App\Models\Valabilitate;
use App\Services\Driver\ActiveValabilitatiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ValabilitateController extends Controller
{
    public function __construct(private readonly ActiveValabilitatiService $service)
    {
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $valabilitati = $this->service->listForUser($user);

        return response()->json([
            'valabilitati' => ValabilitateResource::collection($valabilitati),
        ]);
    }

    public function show(Valabilitate $valabilitate): JsonResponse
    {
        $user = Auth::user();
        $this->service->ensureActiveForUser($user, $valabilitate);

        $valabilitate = $this->service->loadDetail($valabilitate);

        return response()->json([
            'valabilitate' => new ValabilitateResource($valabilitate),
        ]);
    }
}

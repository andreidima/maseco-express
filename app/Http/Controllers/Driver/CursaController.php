<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\CursaRequest;
use App\Http\Resources\Driver\ValabilitateResource;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Services\Driver\ActiveValabilitatiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CursaController extends Controller
{
    public function __construct(private readonly ActiveValabilitatiService $service)
    {
    }

    public function store(CursaRequest $request, Valabilitate $valabilitate): JsonResponse
    {
        $user = Auth::user();
        $valabilitate = $this->service->ensureActiveForUser($user, $valabilitate);

        $valabilitate->curse()->create($request->validated());

        $valabilitate = $this->service->loadDetail($valabilitate->fresh());

        return response()->json([
            'valabilitate' => new ValabilitateResource($valabilitate),
        ]);
    }

    public function update(CursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): JsonResponse
    {
        $user = Auth::user();
        $valabilitate = $this->service->ensureActiveForUser($user, $valabilitate);

        abort_unless((int) $cursa->valabilitate_id === (int) $valabilitate->id, 404);

        $cursa->update($request->validated());

        $valabilitate = $this->service->loadDetail($valabilitate->fresh());

        return response()->json([
            'valabilitate' => new ValabilitateResource($valabilitate),
        ]);
    }

    public function destroy(Valabilitate $valabilitate, ValabilitateCursa $cursa): JsonResponse
    {
        $user = Auth::user();
        $valabilitate = $this->service->ensureActiveForUser($user, $valabilitate);

        abort_unless((int) $cursa->valabilitate_id === (int) $valabilitate->id, 404);

        $cursa->delete();

        $valabilitate = $this->service->loadDetail($valabilitate->fresh());

        return response()->json([
            'valabilitate' => new ValabilitateResource($valabilitate),
        ]);
    }
}

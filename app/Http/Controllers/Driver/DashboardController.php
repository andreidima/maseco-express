<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Services\Driver\ActiveValabilitatiService;
use App\Models\Tara;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(ActiveValabilitatiService $service): View
    {
        $user = Auth::user();

        $valabilitati = $service->listForUser($user);

        $tari = Tara::query()
            ->orderBy('nume')
            ->get(['id', 'nume']);

        return view('driver.dashboard', [
            'valabilitati' => $valabilitati,
            'tari' => $tari,
            'romaniaId' => $service->romaniaId(),
        ]);
    }
}

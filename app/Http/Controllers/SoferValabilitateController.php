<?php

namespace App\Http\Controllers;

use App\Models\Valabilitate;
use App\Support\CountryList;
use Illuminate\View\View;

class SoferValabilitateController extends Controller
{
    public function show(Valabilitate $valabilitate): View
    {
        $valabilitate->load(['masina', 'curse' => fn ($query) => $query->orderByDesc('plecare_la')->orderByDesc('created_at')]);

        return view('sofer.valabilitate.show', [
            'valabilitate' => $valabilitate,
            'countries' => CountryList::options(),
        ]);
    }
}

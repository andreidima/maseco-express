<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FlotaStatusUtilizator;

class FlotaStatusUtilizatorController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('flotaStatusUtilizatorReturnUrl')
            ?? $request->session()->put('flotaStatusUtilizatorReturnUrl', url()->previous());

        $flotaStatusUtilizator = new FlotaStatusUtilizator;

        return view('flotaStatusuriUtilizatori.create', compact('flotaStatusUtilizator'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $flotaStatusUtilizator = FlotaStatusUtilizator::create($this->validateRequest($request));

        return redirect($request->session()->get('flotaStatusUtilizatorReturnUrl') ?? ('/flota-statusuri'))
            ->with('status', 'Utilizatorul "' . ($flotaStatusUtilizator->nume ?? '') . '" a fost adaugat cu succes!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FlotaStatusUtilizator  $flotaStatusUtilizator
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, FlotaStatusUtilizator $flotaStatusUtilizator)
    {
        $request->session()->get('flotaStatusUtilizatorReturnUrl')
            ?? $request->session()->put('flotaStatusUtilizatorReturnUrl', url()->previous());

        return view('flotaStatusuriUtilizatori.edit', compact('flotaStatusUtilizator'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FlotaStatusUtilizator  $flotaStatusUtilizator
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FlotaStatusUtilizator $flotaStatusUtilizator)
    {
        $flotaStatusUtilizator->update($this->validateRequest($request));

        return redirect($request->session()->get('flotaStatusUtilizatorReturnUrl') ?? ('/flota-statusuri'))
            ->with('status', 'Utilizatorul "' . ($flotaStatusUtilizator->nume ?? '') . '" a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FlotaStatusUtilizator  $flotaStatusUtilizator
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, FlotaStatusUtilizator $flotaStatusUtilizator)
    {
        $flotaStatusUtilizator->delete();

        return back()->with('status', 'Utilizatorul "' . ($flotaStatusUtilizator->nume ?? '') . '" a fost sters cu succes!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate(
            [
                'nume' => 'nullable|max:255',
                'culoare_background' => 'nullable|max:255',
                'culoare_text' => 'nullable|max:255',
                'ordine_afisare' => 'nullable|integer|min:0|max:255',
            ],
            [
                //
            ]
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MasinaValabilitati;
use App\Http\Requests\MasinaValabilitatiRequest;
use Illuminate\Http\Request;

class MasinaValabilitatiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Clear any previous “return to” URL
        $request->session()->forget('masinaValabilitatiReturnUrl');

        // Fetch all records, sorted by the 'nr_auto' column ascending
        $masiniValabilitatiGroupedByDivizie = MasinaValabilitati::
            orderBy('nr_auto', 'asc')
            ->latest()
            ->get()
            ->groupBy('divizie');
// dd($masiniValabilitati);
        return view('masiniValabilitati.index', compact('masiniValabilitatiGroupedByDivizie'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // If no “return URL” is set yet, store the previous URL
        $request->session()->get('masinaValabilitatiReturnUrl')
            ?? $request->session()->put('masinaValabilitatiReturnUrl', url()->previous());

        // Create an empty model instance to pass to the view
        $masinaValabilitati = new MasinaValabilitati();

        return view('masiniValabilitati.save', compact('masinaValabilitati'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\masinaValabilitatiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(masinaValabilitatiRequest $request)
    {
        // Mass‐create using validated data
        $masinaValabilitati = MasinaValabilitati::create($request->validated());

        // Redirect back to whatever page originally led here (or fallback to index)
        return redirect(
                $request->session()->get('masinaValabilitatiReturnUrl')
                    ?? route('flota-statusuri-c.index')
            )
            ->with('status', 'Mașina „' . ($masinaValabilitati->nr_auto ?? '') . '” a fost adăugată cu succes!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Models\masinaValabilitati       $masinaValabilitati
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, MasinaValabilitati $masinaValabilitati)
    {
        // Store the “return to” URL if not already set
        $request->session()->get('masinaValabilitatiReturnUrl')
            ?? $request->session()->put('masinaValabilitatiReturnUrl', url()->previous());

        return view('masiniValabilitati.save', compact('masinaValabilitati'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\masinaValabilitatiRequest  $request
     * @param  \App\Models\masinaValabilitati                $masinaValabilitati
     * @return \Illuminate\Http\Response
     */
    public function update(MasinaValabilitatiRequest $request, MasinaValabilitati $masinaValabilitati)
    {
        // Apply validated changes
        $masinaValabilitati->update($request->validated());

        return redirect(
                $request->session()->get('masinaValabilitatiReturnUrl')
                    ?? route('masini-valabilitati.index')
            )
            ->with('status', 'Mașina „' . ($masinaValabilitati->nr_auto ?? '') . '” a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Models\MasinaValabilitati       $masinaValabilitati
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, MasinaValabilitati $masinaValabilitati)
    {
        $masinaValabilitati->delete();

        return back()->with('status', 'Mașina „' . ($masiniValabilitati->nr_auto ?? '') . '” a fost ștearsă cu succes!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FlotaStatusC;
use App\Http\Requests\FlotaStatusCRequest;
use Illuminate\Http\Request;

class FlotaStatusCController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Clear any previous “return to” URL for the C‐pages
        $request->session()->forget('flotaStatusCReturnUrl');

        // Fetch all records, sorted by the 'ordine' column ascending
        $flotaStatusuriC = FlotaStatusC::orderBy('ordine', 'asc')
                                    ->latest()
                                    ->simplePaginate(100);

        return view('flotaStatusuri_c.index', compact('flotaStatusuriC'));
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
        $request->session()->get('flotaStatusCReturnUrl')
            ?? $request->session()->put('flotaStatusCReturnUrl', url()->previous());

        // Create an empty model instance to pass to the view
        $flotaStatusC = new FlotaStatusC();

        return view('flotaStatusuri_c.save', compact('flotaStatusC'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\FlotaStatusCRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FlotaStatusCRequest $request)
    {
        // Mass‐create using validated data
        $flotaStatusC = FlotaStatusC::create($request->validated());

        // Redirect back to whatever page originally led here (or fallback to index)
        return redirect(
                $request->session()->get('flotaStatusCReturnUrl')
                    ?? route('flota-statusuri-c.index')
            )
            ->with('status', 'Statusul C „' . ($flotaStatusC->nr_auto ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Models\FlotaStatusC       $flotaStatusC
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, FlotaStatusC $flotaStatusC)
    {
        // Store the “return to” URL if not already set
        $request->session()->get('flotaStatusCReturnUrl')
            ?? $request->session()->put('flotaStatusCReturnUrl', url()->previous());

        return view('flotaStatusuri_c.save', compact('flotaStatusC'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\FlotaStatusCRequest  $request
     * @param  \App\Models\FlotaStatusC                $flotaStatusC
     * @return \Illuminate\Http\Response
     */
    public function update(FlotaStatusCRequest $request, FlotaStatusC $flotaStatusC)
    {
        // Apply validated changes
        $flotaStatusC->update($request->validated());

        return redirect(
                $request->session()->get('flotaStatusCReturnUrl')
                    ?? route('flota-statusuri-c.index')
            )
            ->with('status', 'Statusul C „' . ($flotaStatusC->nr_auto ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Models\FlotaStatusC       $flotaStatusC
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, FlotaStatusC $flotaStatusC)
    {
        $flotaStatusC->delete();

        return back()->with('status', 'Statusul C „' . ($flotaStatusC->nr_auto ?? '') . '” a fost șters cu succes!');
    }
}

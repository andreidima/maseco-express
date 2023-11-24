<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Factura;
use App\Models\CursBnr;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('facturaReturnUrl');

        $searchComandaNumar = $request->searchComandaNumar;

        $query = Factura::with('cursBnr')
            // ->when($searchNume, function ($query, $searchNume) {
            //     return $query->where('nume', 'like', '%' . $searchNume . '%');
            // })
            ->latest();

        $facturi = $query->simplePaginate(25);

        return view('facturi.index', compact('facturi', 'searchComandaNumar'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('facturaReturnUrl') ?? $request->session()->put('facturaReturnUrl', url()->previous());

        return view('facturi.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $facturi = Factura::create($this->validateRequest($request));

        return redirect($request->session()->get('facturaReturnUrl') ?? ('/facturi'))->with('status', 'Factura pentru comanda„' . ($factura->comanda->transportator_contract ?? '') . '” a fost adăugată cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Factura $factura)
    {
        $request->session()->get('facturaReturnUrl') ?? $request->session()->put('facturaReturnUrl', url()->previous());

        return view('facturi.show', compact('factura'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Factura $factura)
    {
        $request->session()->get('facturaReturnUrl') ?? $request->session()->put('facturaReturnUrl', url()->previous());

        return view('facturi.edit', compact('factura'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Factura $factura)
    {
        $factura->update($this->validateRequest($request));

        return redirect($request->session()->get('facturaReturnUrl') ?? ('/facturi'))->with('status', 'Factura pentru comanda„' . ($factura->comanda->transportator_contract ?? '') . '” a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Factura $factura)
    {
        $factura->delete();

        return back()->with('status', 'Factura pentru comanda„' . ($factura->comanda->transportator_contract ?? '') . '” a fost ștearsă cu succes!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        // Se adauga userul doar la adaugare, iar la modificare nu se schimba
        // if ($request->isMethod('post')) {
        //     $request->request->add(['user_id' => $request->user()->id]);
        // }

        // if ($request->isMethod('post')) {
        //     $request->request->add(['cheie_unica' => uniqid()]);
        // }

        return $request->validate(
            [
                'nume' => 'required|max:500',
                'telefon' => 'nullable|max:100',
                'email' => 'required|max:500|email:rfc,dns',
                'data_expirare' => '',
                'descriere' => 'nullable|max:10000',
                'observatii' => 'nullable|max:10000',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}

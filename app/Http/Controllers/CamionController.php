<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Camion;
use App\Models\CamionIstoric;
use App\Models\Firma;

class CamionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('camion_return_url');

        $search_numar_inmatriculare = $request->search_numar_inmatriculare;
        $search_nume_sofer = $request->search_nume_sofer;
        $search_telefon_sofer = $request->search_telefon_sofer;

        $query = Camion::
            when($search_numar_inmatriculare, function ($query, $search_numar_inmatriculare) {
                return $query->where('nume', 'like', '%' . $search_numar_inmatriculare . '%');
            })
            ->when($search_nume_sofer, function ($query, $search_nume_sofer) {
                return $query->where('telefon', 'like', '%' . $search_nume_sofer . '%');
            })
            ->when($search_telefon_sofer, function ($query, $search_telefon_sofer) {
                return $query->where('email', 'like', '%' . $search_telefon_sofer . '%');
            })
            ->latest();

        $camioane = $query->simplePaginate(25);

        return view('camioane.index', compact('camioane', 'search_numar_inmatriculare', 'search_nume_sofer', 'search_telefon_sofer'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $firme = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $tipuriCamioane = Camion::select('tip_camion')->distinct()->orderBy('tip_camion')->get();

        $request->session()->get('camion_return_url') ?? $request->session()->put('camion_return_url', url()->previous());

        return view('camioane.create', compact('firme', 'tipuriCamioane'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $camion = Camion::create($this->validateRequest($request));

        // Salvare in istoric
        $camion_istoric = new CamionIstoric;
        $camion_istoric->fill($camion->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $camion_istoric->operare_user_id = auth()->user()->id ?? null;
        $camion_istoric->operare_descriere = 'Adaugare';
        $camion_istoric->save();

        return redirect($request->session()->get('camion_return_url') ?? ('/camioane'))->with('status', 'Camionul „' . ($camion->numar_inmatriculare ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Camion  $camion
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Camion $camion)
    {
        $this->authorize('update', $camion);

        $request->session()->get('camion_return_url') ?? $request->session()->put('camion_return_url', url()->previous());

        return view('camioane.show', compact('camion'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Camion  $camion
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Camion $camion)
    {
        $firme = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $tipuriCamioane = Camion::select('tip_camion')->distinct()->orderBy('tip_camion')->get();

        $request->session()->get('camion_return_url') ?? $request->session()->put('camion_return_url', url()->previous());

        return view('camioane.edit', compact('camion', 'firme', 'tipuriCamioane'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Camion  $camion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Camion $camion)
    {
        $camion->update($this->validateRequest($request));

        // Salvare in istoric
        if ($camion->wasChanged()){
            $camion_istoric = new CamionIstoric;
            $camion_istoric->fill($camion->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $camion_istoric->operare_user_id = auth()->user()->id ?? null;
            $camion_istoric->operare_descriere = 'Modificare';
            $camion_istoric->save();
        }

        return redirect($request->session()->get('camion_return_url') ?? ('/camioane'))->with('status', 'Camionul „' . ($camion->numar_inmatriculare ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Camion  $camion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Camion $camion)
    {
        // Salvare in istoric
        $camion_istoric = new CamionIstoric;
        $camion_istoric->fill($camion->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $camion_istoric->operare_user_id = auth()->user()->id ?? null;
        $camion_istoric->operare_descriere = 'Stergere';
        $camion_istoric->save();

        $camion->delete();

        return back()->with('status', 'Camionul „' . ($camion->numar_inmatriculare ?? '') . '” a fost șters cu succes!');
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
                'tip_camion' => 'required|max:500',
                'numar_inmatriculare' => 'nullable|max:500',
                'nume_sofer' => 'nullable|max:500',
                'telefon_sofer' => 'nullable|max:500',
                'firma_id' => '',
                'observatii' => 'nullable|max:2000',
            ],
            [
                'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }

    // public function restaurareIstoric(Request $request, Camion $camion = null, CamionIstoric $camion_istoric = null){
    //     $camion->nume = $camion_istoric->nume;
    //     $camion->telefon = $camion_istoric->telefon;
    //     $camion->adresa = $camion_istoric->adresa;
    //     $camion->status = $camion_istoric->status;
    //     $camion->intrare = $camion_istoric->intrare;
    //     $camion->lansare = $camion_istoric->lansare;
    //     $camion->oferta_pret = $camion_istoric->oferta_pret;
    //     $camion->avans = $camion_istoric->avans;
    //     $camion->observatii = $camion_istoric->observatii;
    //     $camion->user_id = $camion_istoric->user_id;

    //     $camion->save();

    //     // Salvare in istoric
    //     if ($camion->wasChanged()){
    //         $camion_istoric = new CamionIstoric;
    //         $camion_istoric->fill($camion->makeHidden(['created_at', 'updated_at'])->attributesToArray());
    //         $camion_istoric->operatie = 'Modificare';
    //         $camion_istoric->operatie_user_id = auth()->user()->id ?? null;
    //         $camion_istoric->save();
    //         return redirect($request->session()->get('camion_return_url') ?? ('/camioane'))->with('status', 'Camion „' . ($camion->nume ?? '') . '” a fost restaurată cu succes!');
    //     } else {
    //         return redirect($request->session()->get('camion_return_url') ?? ('/camioane'))->with('warning', 'Camion „' . ($camion->nume ?? '') . '” nu a avut nimic de restaurat!');
    //     }

    // }
}

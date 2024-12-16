<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FlotaStatus;
use App\Models\FlotaStatusUtilizator;
use App\Models\FlotaStatusInformatie;
use Carbon\Carbon;

class FlotaStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('flotaStatusReturnUrl');

        $utilizatori = FlotaStatusUtilizator::select('id' , 'nume', 'culoare', 'ordine_afisare')->orderBy('ordine_afisare')->get();

        $flotaStatusuri = FlotaStatus::with('utilizator')->orderByRaw('FIELD(utilizator_id, ' . implode(',', $utilizatori->pluck('id')->toArray()) . ')')->simplePaginate(100);

        $flotaStatusuriInformatii = FlotaStatusInformatie::simplePaginate(100);

        return view('flotaStatusuri.index', compact('flotaStatusuri', 'utilizatori', 'flotaStatusuriInformatii'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('flotaStatusReturnUrl') ?? $request->session()->put('flotaStatusReturnUrl', url()->previous());

        $flotaStatus = new FlotaStatus;

        // $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();
        $utilizatori = FlotaStatusUtilizator::select('id' , 'nume')->orderBy('nume')->get();

        return view('flotaStatusuri.create', compact('flotaStatus', 'utilizatori'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $flotaStatus = FlotaStatus::create($this->validateRequest($request));

        return redirect($request->session()->get('flotaStatusReturnUrl') ?? ('/flota-statusuri'))->with('status', 'Statusul „' . ($flotaStatus->nr_auto ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FlotaStatus  $flotaStatus
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, FlotaStatus $flotaStatus)
    {
        $request->session()->get('flotaStatusReturnUrl') ?? $request->session()->put('flotaStatusReturnUrl', url()->previous());

        return view('flotaStatusuri.show', compact('flotaStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FlotaStatus  $flotaStatus
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, FlotaStatus $flotaStatus)
    {
        $request->session()->get('flotaStatusReturnUrl') ?? $request->session()->put('flotaStatusReturnUrl', url()->previous());

        // $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();
        $utilizatori = FlotaStatusUtilizator::select('id' , 'nume')->orderBy('nume')->get();

        return view('flotaStatusuri.edit', compact('flotaStatus', 'utilizatori'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FlotaStatus  $flotaStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FlotaStatus $flotaStatus)
    {
        $flotaStatus->update($this->validateRequest($request));

        return redirect($request->session()->get('flotaStatusReturnUrl') ?? ('/flota-statusuri'))->with('status', 'Statusul „' . ($flotaStatus->nr_auto ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FlotaStatus  $flotaStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, FlotaStatus $flotaStatus)
    {
        $flotaStatus->delete();

        return back()->with('status', 'Statusul „' . ($flotaStatus->nr_auto ?? '') . '” a fost șters cu succes!');
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
                'utilizator_id' => 'nullable',
                'nr_auto' => 'nullable|max:255',
                'dimenssions' => 'nullable|max:255',
                'type' => 'nullable|max:255',
                'out_of_eu' => 'nullable|max:255',
                'info' => 'nullable',
                'abilities' => 'nullable|max:255',
                'status_of_the_shipment' => 'nullable|max:255',
                'info_ii' => 'nullable|max:255',
                'info_iii' => 'nullable|max:255',
                'special_info' => 'nullable|max:255',
                'e_km' => 'nullable|max:255',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Intermediere;
use App\Models\Comanda;
use App\Models\User;
use Carbon\Carbon;

class IntermediereController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('intermediereReturnUrl');

        $searchUser = $request->searchUser;
        $searchInterval = $request->searchInterval;
        $searchPredat = $request->searchPredat;

        $query = Comanda::with('intermediere', 'user:id,name', 'client:id,nume', 'transportator:id,nume', 'camion:id,numar_inmatriculare', 'clientMoneda', 'transportatorMoneda',
                                    'factura:id,client_nume,client_contract,seria,numar,data,factura_transportator,data_plata_transportator',
                                    'ultimulEmailPentruFisiereIncarcateDeTransportator:id,comanda_id,tip', 'fisiereTransportatorIncarcateDeOperator'
                                )
            ->when($searchUser, function ($query, $searchUser) {
                return $query->whereHas('user', function ($query) use ($searchUser) {
                    $query->where('id', $searchUser);
                });
            }, function ($q) { // return nothing if there is no user selected
                return $q->where('id', -1);
            })
            ->when($searchInterval, function ($query, $searchInterval) {
                return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
            })
            ->when($searchPredat, function ($query, $searchPredat) {
                if ($searchPredat == 'DA') {
                    return $query->whereHas('intermediere', function ($query) {
                        $query->where('predat_la_contabilitate', 1);
                    });
                } else {
                    $query->where(function ($query) {
                        $query->whereHas('intermediere', function ($query) {
                                $query->whereNull('predat_la_contabilitate')
                                    ->orwhere('predat_la_contabilitate', '<>', 1);
                            })->orWhereDoesntHave('intermediere');
                    });
                }
            })
            ->orderBy('data_creare');

        if ($request->action == "export"){
            if (!$searchInterval) {
                return back()->with('error', 'Trebuie să selectați un interval pentru a putea exporta date.');
            }
            if (Carbon::parse(strtok($searchInterval, ','))->diffInDays(Carbon::parse(strtok( '' ))) > 101){
                return back()->with('error', 'Trebuie să selectați un interval de maxim 100 de zile.');
            }
            $comenzi = $query->get();
            return view('intermedieri.export.exportHtml', compact('comenzi', 'searchUser', 'searchInterval', 'searchPredat'));
        } else {
            $comenzi = $query->simplePaginate(50);
            $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();
            return view('intermedieri.index', compact('comenzi', 'useri', 'searchUser', 'searchInterval', 'searchPredat'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('intermediereReturnUrl') ?? $request->session()->put('intermediereReturnUrl', url()->previous());

        $intermediere = new Intermediere;
        $intermediere->comanda_id = $request->comandaId;

        $intermediere->save();

        return redirect('/intermedieri/' . $intermediere->id . '/modifica');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Intermediere  $intermediere
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Intermediere $intermediere)
    {
        $request->session()->get('intermediereReturnUrl') ?? $request->session()->put('intermediereReturnUrl', url()->previous());

        return view('intermedieri.edit', compact('intermediere'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Intermediere  $intermediere
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Intermediere $intermediere)
    {
        $intermediere->update($this->validateRequest($request));

        return redirect($request->session()->get('intermediereReturnUrl') ?? ('/intermedieri'))->with('status', 'Intermedierea pentru comanda„' . ($intermediere->comanda->transportator_contract ?? '') . '” a fost modificată cu succes!');
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
                'observatii' => 'nullable|max:2000',
                'motis' => 'nullable|numeric|min:-9999999|max:9999999',
                'dkv' => 'nullable|numeric|min:-9999999|max:9999999',
                'astra' => 'nullable|numeric|min:-9999999|max:9999999',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }

    public function schimbaPredatLaContabilitate(Request $request, Comanda $comanda){
        $intermediere = $comanda->intermediere ?? Intermediere::make(['comanda_id' => $comanda->id]);

        if ($intermediere->predat_la_contabilitate == 1) {
            $intermediere->predat_la_contabilitate = 0;
            $intermediere->save();
            // dd('da');
        } else {
            $intermediere->predat_la_contabilitate = 1;
            $intermediere->save();
            // dd($intermediere);
            // dd('nu');
        };

        return back()->with('status', 'Predat la contabilitate a fost modificat cu succes!');
    }

    public function exportHtml(Request $request){
        dd($request);
    }
}

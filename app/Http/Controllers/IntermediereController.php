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
        $searchFacturaMasecoNumar = $request->searchFacturaMasecoNumar;
        $searchCondition = $request->searchCondition;

        $query = Comanda::with('intermediere', 'user:id,name', 'client:id,nume', 'transportator:id,nume', 'camion:id,numar_inmatriculare', 'clientMoneda', 'transportatorMoneda',
                                    'factura:id,client_nume,client_contract,seria,numar,data,factura_transportator,data_plata_transportator',
                                    'ultimulEmailPentruFisiereIncarcateDeTransportator:id,comanda_id,tip', 'fisiereTransportatorIncarcateDeOperator',
                                    'clientiComanda.factura', 'clientiComanda.moneda'
                                )
            ->when($searchUser, function ($query, $searchUser) {
                return $query->whereHas('user', function ($query) use ($searchUser) {
                    $query->where('id', $searchUser);
                });
            // }, function ($q) { // return nothing if there is no user selected
            //     return $q->where('id', -1);
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
            ->when($searchFacturaMasecoNumar, function ($query, $searchFacturaMasecoNumar) {
                return $query->whereHas('clientiComanda.factura', function ($subQuery) use ($searchFacturaMasecoNumar) {
                    $subQuery->where('numar', $searchFacturaMasecoNumar);
                });
            })
            ->when($searchCondition, function ($query, $searchCondition) {
                if ($searchCondition == 'condition1') {
                    $query->whereNotNull('data_plata_transportator') // Ensure `data_plata_transportator` is set
                    ->where('data_plata_transportator', '<=', Carbon::today()); // Compare with today's date
                } elseif ($searchCondition == 'condition2') {
                    $query->whereNotNull('factura_transportator_incarcata')
                        ->where('factura_transportator_incarcata', 1)

                        // Exclude records satisfying condition1
                        ->whereNot(function ($query) {
                            $query->whereNotNull('data_plata_transportator') // Ensure `data_plata_transportator` is set
                            ->where('data_plata_transportator', '<=', Carbon::today()); // Compare with today's date
                        });
                } elseif ($searchCondition == 'condition3') {
                    $query->where(function ($query) {
                        // Exclude records satisfying condition1
                        $query->whereNot(function ($query) {
                            $query->whereNotNull('data_plata_transportator')
                                ->where('data_plata_transportator', '<=', Carbon::today());
                        });
                    })->where(function ($query) {
                        // Exclude records satisfying condition2
                        $query->whereNot(function ($query) {
                            $query->whereNotNull('factura_transportator_incarcata')
                                ->where('factura_transportator_incarcata', 1);
                        });
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
            $comenzi = $query->simplePaginate(100);
            $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();
            return view('intermedieri.index', compact('comenzi', 'useri', 'searchUser', 'searchInterval', 'searchPredat', 'searchFacturaMasecoNumar', 'searchCondition'));
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
                'plata_client' => 'nullable|max:255',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }

    public function schimbaPredatLaContabilitate(Request $request, Comanda $comanda){
        // Ensure $intermediere is retrieved or created in the database
        $intermediere = $comanda->intermediere ?? Intermediere::firstOrCreate(
            ['comanda_id' => $comanda->id],
            ['predat_la_contabilitate' => 0] // Default value if created
        );

        // Toggle the value of predat_la_contabilitate
        $intermediere->predat_la_contabilitate = !$intermediere->predat_la_contabilitate;
        $intermediere->save();

        // return back()->with('status', 'Predat la contabilitate a fost modificat cu succes!');
        return response()->json([
            'success' => true,
            'predat_la_contabilitate' => $intermediere->predat_la_contabilitate,
            'message' => 'Predat la contabilitate a fost modificat cu succes!',
        ]);
    }

    // to delete 01.02.2025
    // public function exportHtml(Request $request){
    //     dd($request);
    // }
}

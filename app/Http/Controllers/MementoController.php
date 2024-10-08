<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Memento;
use App\Models\MementoAlerta;
use Carbon\Carbon;

class MementoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tip)
    {
        $request->session()->forget('mementoReturnUrl');

        $searchNume = $request->searchNume;

        $query = Memento::with('alerte')
            ->when($searchNume, function ($query, $searchNume) {
                return $query->where('nume', 'like', '%' . $searchNume . '%');
            })
            ->where('tip', $tip)
            ->latest();

        $mementouri = $query->simplePaginate(25);

        return view('mementouri.index', compact('mementouri', 'searchNume'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $tip)
    {
        $request->session()->get('mementoReturnUrl') ?? $request->session()->put('mementoReturnUrl', url()->previous());

        $memento = new Memento;
        $memento->tip = $tip;
        $memento->email = 'masecoexpres@gmail.com';
        if ($memento->tip == 2) {
            $memento->telefon = '0741099092';
        } else if ($memento->tip == 3) {
            $memento->telefon = '0748837489';
        }

        return view('mementouri.create', compact('memento'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $memento = Memento::create($this->validateRequest($request));
        if ($request->dateSelectate) {
            foreach ($request->dateSelectate as $data){
                $alerta = new MementoAlerta(['data' => $data]);
                $memento->alerte()->save($alerta);
            }
        }

        return redirect($request->session()->get('mementoReturnUrl') ?? ('/mementouri'))->with('status', 'Mementoul „' . ($memento->nume ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Memento  $memento
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $tip, Memento $memento)
    {
        $request->session()->get('mementoReturnUrl') ?? $request->session()->put('mementoReturnUrl', url()->previous());

        return view('mementouri.show', compact('memento'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Memento  $memento
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $tip, Memento $memento)
    {
        $request->session()->get('mementoReturnUrl') ?? $request->session()->put('mementoReturnUrl', url()->previous());

        $memento = Memento::where('id', $memento->id)->with('alerte')->first();

        return view('mementouri.edit', compact('memento'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Memento  $memento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tip, Memento $memento)
    {
        $memento->update($this->validateRequest($request));

        $memento->alerte()->delete();
        if ($request->dateSelectate) {
            foreach ($request->dateSelectate as $data){
                $alerta = new MementoAlerta(['data' => $data]);
                $memento->alerte()->save($alerta);
            }
        }

        return redirect($request->session()->get('mementoReturnUrl') ?? ('/mementouri'))->with('status', 'Mementoul „' . ($memento->nume ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Memento  $memento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $tip, Memento $memento)
    {
        $memento->alerte()->delete();

        $memento->delete();

        return back()->with('status', 'Mementoul „' . ($memento->nume ?? '') . '” a fost șters cu succes!');
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
                // 'email' => 'required|max:500|email:rfc,dns',
                'email' => 'nullable|max:500|email:rfc,dns',
                'data_expirare' => '',
                'descriere' => 'nullable|max:10000',
                'observatii' => 'nullable|max:10000',
                'tip' => '',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}

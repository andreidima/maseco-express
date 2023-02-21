<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LocOperare;

class AxiosController extends Controller
{

    /**
     * Returnarea oraselor de sosire
     */
    public function orase_rezervari(Request $request)
    {
        $tur_retur = 0;
        $raspuns = '';
        switch ($_GET['request']) {
            case 'judete_plecare':
                $raspuns = Oras::select('id', 'judet', 'tara')
                    ->where('tara', $request->tara)
                    ->orderBy('judet')
                    ->get()
                    ->unique('judet');
                break;
            // case 'orase_plecare':
            //     $raspuns = Oras::select('id', 'oras', 'judet')
            //         ->where('judet', $request->judet)
            //         ->orderBy('oras')
            //         ->get();
            //     break;
            case 'orase_plecare':
                $raspuns = Oras::select('id', 'oras', 'tara')
                    ->where('tara', $request->tara)
                    ->orderBy('oras')
                    ->get();
                break;
            case 'judete_sosire':
                $raspuns = Oras::select('id', 'judet', 'tara')
                    ->where('tara', '<>', $request->tara)
                    ->orderBy('judet')
                    ->get()
                    ->unique('judet');
                break;
            // case 'orase_sosire':
            //     $raspuns = Oras::select('id', 'oras', 'judet')
            //         ->where('judet', $request->judet)
            //         ->orderBy('oras')
            //         ->get();
            //     break;
            case 'orase_sosire':
                $raspuns = Oras::select('id', 'oras', 'tara')
                    ->where('tara', '<>', $request->tara)
                    ->orderBy('oras')
                    ->get();
                break;
            case 'tarife':
                $pret_adult = '';
                $pret_copil = '';
                $pret_adult_tur_retur = '';
                $pret_copil_tur_retur = '';
                $pret_colete_kg = '';

                $preturi_modificate_la_data_string_de_afisat = '';
                $pret_adult_retur = '';
                $pret_copil_retur = '';

                if ($request->data_plecare){
                    $tarife = \App\Models\Tarif::whereDate('de_la_data', '<', $request->data_plecare)->whereDate('pana_la_data', '>', $request->data_plecare)->first() ?? \App\Models\Tarif::latest()->first();
                    $pret_adult = $tarife->adult;
                    $pret_copil = $tarife->copil;
                    $pret_adult_tur_retur = $tarife->adult_tur_retur;
                    $pret_copil_tur_retur = $tarife->copil_tur_retur;
                    $pret_colete_kg = $tarife->colete_kg;
                }

                if (($request->diferenta_date) && ($request->diferenta_date > 15)){
                    $tarife_retur = \App\Models\Tarif::whereDate('de_la_data', '<', $request->data_intoarcere)->whereDate('pana_la_data', '>', $request->data_intoarcere)->first() ?? \App\Models\Tarif::latest()->first();
                    $pret_adult_retur = $tarife_retur->adult;
                    $pret_copil_retur = $tarife_retur->copil;
                    if ($tarife->de_la_data != $tarife_retur->de_la_data) {
                        $preturi_modificate_la_data_string_de_afisat = Carbon::parse($tarife_retur->de_la_data)->isoFormat('DD.MM.YYYY');
                    }
                }

                // $pret_adult = 77;
                return response()->json([
                    'pret_adult' => $pret_adult,
                    'pret_copil' => $pret_copil,
                    'pret_adult_tur_retur' => $pret_adult_tur_retur,
                    'pret_copil_tur_retur' => $pret_copil_tur_retur,
                    'pret_colete_kg' => $pret_colete_kg,

                    'preturi_modificate_la_data_string_de_afisat' => $preturi_modificate_la_data_string_de_afisat,
                    'pret_adult_retur' => $pret_adult_retur,
                    'pret_copil_retur' => $pret_copil_retur,
                ]);
                break;
            default:
                break;
        }
        return response()->json([
            'raspuns' => $raspuns,
        ]);
    }
}

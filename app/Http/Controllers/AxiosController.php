<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LocOperare;

class AxiosController extends Controller
{

    /**
     * Returnarea oraselor de sosire
     */
    public function locuriOperare(Request $request)
    {
        $raspuns = '';
        switch ($_GET['request']) {
            case 'locuriOperare':
                $raspuns = LocOperare::select('id', 'nume', 'oras', 'tara_id', 'adresa')
                    ->with('tara')
                    ->where('nume', 'like', '%' . $request->nume . '%')
                    ->orderBy('nume')
                    ->take(100)
                    ->get();
                break;
            default:
                break;
        }
        return response()->json([
            'raspuns' => $raspuns,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LocOperare;
use App\Models\Firma;
use App\Models\ComandaStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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
                    ->where('oras', 'like', '%' . $request->oras . '%')
                    // ->where(function ($query) use ($request){
                    //     if ($request->categorie === 'nume'){
                    //         $query->where('nume', 'like', '%' . $request->nume . '%');
                    //     }elseif ($request->categorie === 'oras'){
                    //         $query->where('oras', 'like', '%' . $request->nume . '%');
                    //     }
                    // })
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

    /**
     * Get clients "Firme"
     */
    public function clienti(Request $request)
    {
        $raspuns = '';
        switch ($_GET['request']) {
            case 'clienti':
                $raspuns = Firma::select('id', 'nume')
                    ->with('tara')
                    ->where('nume', 'like', '%' . $request->nume . '%')
                    ->where('tip_partener', 1) // just the clients
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

    public function statusuri(Request $request)
    {
        $comada_id = $request->comanda_id;
        $statusuri = ComandaStatus::
            // with('comanda:id,transportator_contract,transportator_transportator_id,client_client_id', 'comanda.transportator:id,nume,persoana_contact,telefon', 'comanda.client:id,nume')
            whereHas('comanda', function ($query) use ($comada_id) {
                $query->where('id', $comada_id);
            })
            // ->with('comanda.transportator:nume')
            // ->whereDate('created_at', '>',  Carbon::today()->subDays(5)) // statusurile din ultimele 3 zile
            ->latest()
            ->get();

        foreach ($statusuri as $status){
            $status->data = Carbon::parse($status->created_at)->isoFormat('DD.MM.YYYY');
            $status->ora = Carbon::parse($status->created_at)->isoFormat('HH:mm');
        }

        return response()->json([
            'raspuns' => $statusuri,
        ]);
    }

    public function trimitereCodAutentificarePrinEmail(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user){
            $raspuns = "<span class='text-danger' style='font-size:80%'>Nu există acest email în baza de date.</span>";

        // This restriction is to stop spamming sending the code to email, because the hosting doesn't allow it (Romarg for example allow maximum 5 emails per minute thorugh PHP scripts)
        } elseif (($user->cod_email) && (Carbon::parse($user->updated_at)->addMinutes(5)->gt(Carbon::now()))) {
            $raspuns = "
                <span class='text-danger' style='font-size:80%'>
                    Ai deja un cod nefolosit trimis pe email.
                    <br>
                    Poți genera un cod nou la fiecare 5 minute.
                    <br><br>
                    Următorul cod poate fi generat la " . Carbon::parse($user->updated_at)->addMinutes(5)->isoFormat('HH:mm DD.MM.YYYY') .
                "</span>";
        } else {
            $user->cod_email = rand(1000, 9999);
            $user->save();

            Mail::mailer('office')->to($user->email)->send(new \App\Mail\TrimitereCodAutentificarePrinEmail($user));

            $emailTrimis = new \App\Models\MesajTrimisEmail;
            $emailTrimis->comanda_id = null;
            $emailTrimis->firma_id = null;
            $emailTrimis->categorie = 6;
            $emailTrimis->email = $user->email;
            $emailTrimis->save();

            $raspuns = "<span class='text-success' style='font-size:80%'>Codul a fost trimis prin email.</span>";
        }

        return response()->json([
            'raspuns' => $raspuns,
        ]);
    }
}

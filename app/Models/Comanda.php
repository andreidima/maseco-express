<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Comanda extends Model
{
    use HasFactory;

    protected $table = 'comenzi';
    protected $guarded = [];

    public function path()
    {
        return "/comenzi/{$this->id}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Firma::class, 'client_client_id');
    }

    public function transportator()
    {
        return $this->belongsTo(Firma::class, 'transportator_transportator_id');
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class, 'camion_id');
    }

    public function transportatorMoneda()
    {
        return $this->belongsTo(Moneda::class, 'transportator_moneda_id');
    }

    public function transportatorMetodaDePlata()
    {
        return $this->belongsTo(MetodaDePlata::class, 'transportator_metoda_de_plata_id');
    }

    public function transportatorTermenDePlata()
    {
        return $this->belongsTo(TermenDePlata::class, 'transportator_termen_plata_id');
    }

    public function istoricuri()
    {
        return $this->hasMany(ComandaIstoric::class, 'id');
    }

    public function locuriOperare()
    {
        return $this->belongsToMany(LocOperare::class, 'comenzi_locuri_operare', 'comanda_id', 'loc_operare_id')->with('tara')->withPivot('id', 'tip', 'ordine', 'data_ora', 'durata', 'observatii', 'referinta');
    }

    public function locuriOperareIncarcari()
    {
        return $this->locuriOperare()->where('tip', '1')->orderBy('ordine');
    }

    public function locuriOperareDescarcari()
    {
        return $this->locuriOperare()->where('tip', '2')->orderBy('ordine');
    }

    public function primaIncarcare()
    {
        return $this->locuriOperare()->where('tip', '1')->orderBy('data_ora')->first();
    }

    public function ultimaDescarcare()
    {
        return $this->locuriOperare()->where('tip', '2')->orderByDesc('data_ora')->first();
    }

    public function statusuri()
    {
        return $this->hasMany(ComandaStatus::class);
    }

    public function ultimulStatus()
    {
        return $this->hasOne(ComandaStatus::class)->latest()->first();
    }

    public function contracteTrimisePeEmailCatreTransportator()
    {
        return $this->hasMany(MesajTrimisEmail::class, 'comanda_id')->where('categorie', 3);
    }

    public function emailInformareIncepereComanda()
    {
        return $this->hasOne(MesajTrimisEmail::class, 'comanda_id')->where('categorie', 1);
    }

    public function emailuriCerereStatusComanda()
    {
        return $this->hasMany(MesajTrimisEmail::class, 'comanda_id')->where('categorie', 2);
    }

     // Se trimite notificare de cerere status doar daca nu este nici una trimisa intr-un interval stabilit de timp
    public function emailuriCerereStatusComandaInUltimaPerioada()
    {
        return $this->hasMany(MesajTrimisEmail::class, 'comanda_id')
            ->where('categorie', 2)
            ->where('created_at', '>=', Carbon::now()->subMinutes(
                $this->interval_notificari ? \Carbon\CarbonInterval::createFromFormat('H:i:s', $this->interval_notificari)->totalMinutes : 180
            )->addMinutes(3)); // se mai adauga inca 3 minute de siguranta
            // {{ $this->interval_notificari ? \Carbon\CarbonInterval::createFromFormat('H:i:s', $comanda->interval_notificari)->totalMinutes : 180 }}

        // $ultimulMesajTrimis = $this->emailuriCerereStatusComanda()->where('categorie', 2)->latest()->first();
        // if ($ultimulMesajTrimis){
        //     if (Carbon::parse($ultimulMesajTrimis->created_at)->diffInHours(Carbon::now()) <= 30){
        //         return true;
        //     }
        // }
        // return false;
    }

    public function mesajeTrimiseEmail()
    {
        return $this->hasMany(MesajTrimisEmail::class, 'comanda_id')->latest();
    }

    public function mesajeTrimiseSms()
    {
        return $this->hasMany(MesajTrimisSms::class, 'referinta_id')->latest();
        // ->where('categorie', 'Comenzi');
    }

    public function cronJob()
    {
        return $this->hasOne(ComandaCronJob::class, 'comanda_id');
    }
}

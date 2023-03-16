<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ComandaLocOperareIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'comenzi_locuri_operare_istoric';
    protected $primaryKey = 'id_pk';
    protected $guarded = [];

    public function path()
    {
        return "/comenzi-locuri-operare-istoric/{$this->id}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

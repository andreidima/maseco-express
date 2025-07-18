<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ComandaIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'comenzi_istoric';
    protected $primaryKey = 'id_pk';
    protected $guarded = [];

    public function path()
    {
        return "/comenzi-istoric/{$this->id}";
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    public function userOperare()
    {
        return $this->belongsTo(User::class, 'operare_user_id');
    }
}

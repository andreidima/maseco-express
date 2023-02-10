<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CamionIstoric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'camioane_istoric';
    protected $primaryKey = 'id_pk';
    protected $guarded = [];

    public function path()
    {
        return "/camioane-istoric/{$this->id}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

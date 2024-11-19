<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatiePeco extends Model
{
    use HasFactory;

    protected $table = 'statii_peco';
    protected $guarded = [];

    public function path()
    {
        return "/statii-peco/{$this->id}";
    }
}

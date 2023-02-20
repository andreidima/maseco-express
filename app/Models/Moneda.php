<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    use HasFactory;

    protected $table = 'monede';
    protected $guarded = [];

    public function path()
    {
        return "/monede/{$this->id}";
    }
}

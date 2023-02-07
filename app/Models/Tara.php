<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tara extends Model
{
    use HasFactory;

    protected $table = 'tari';
    protected $guarded = [];

    public function path()
    {
        return "/tari/{$this->id}";
    }
}

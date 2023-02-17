<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Limba extends Model
{
    use HasFactory;

    protected $table = 'limbi';
    protected $guarded = [];

    public function path()
    {
        return "/limbi/{$this->id}";
    }
}

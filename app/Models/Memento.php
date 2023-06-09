<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memento extends Model
{
    use HasFactory;

    protected $table = 'mementouri';
    protected $guarded = [];

    public function path()
    {
        return "/mementouri/{$this->id}";
    }

    public function mementouriAlerte()
    {
        return $this->hasMany(MementoAlerta::class, 'id');
    }
}

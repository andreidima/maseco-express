<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MementoAlerta extends Model
{
    use HasFactory;

    protected $table = 'mementouri_alerte';
    protected $guarded = [];

    public function path()
    {
        return "/mementouri-alerte/{$this->id}";
    }

    public function mementouri()
    {
        return $this->belongsTo(Memento::class, 'memento_id');
    }
}

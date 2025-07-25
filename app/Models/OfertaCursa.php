<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaCursa extends Model
{
    use HasFactory;

    protected $table = 'oferte_curse';
    protected $guarded = [];

    public function path($action = 'show')
    {
        return match ($action) {
            'edit' => route('oferte-curse.edit', $this->id),
            'destroy' => route('oferte-curse.destroy', $this->id),
            default => route('oferte-curse.show', $this->id),
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\MassPrunable;

class OfertaCursa extends Model
{
    use HasFactory, SoftDeletes, MassPrunable;

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

    public function prunable()
    {
        // delete rows older than 7 days
        return static::where('created_at', '<', now()->subDays(7));
    }
}

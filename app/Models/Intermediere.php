<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intermediere extends Model
{
    use HasFactory;

    protected $table = 'intermedieri';
    protected $guarded = [];

    public function path()
    {
        return "/intermedieri/{$this->id}";
    }

    /**
     * Get the comanda that owns the Intermediere
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }
}

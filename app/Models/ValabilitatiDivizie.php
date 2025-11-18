<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValabilitatiDivizie extends Model
{
    use HasFactory;

    protected $table = 'valabilitati_divizii';

    protected $fillable = [
        'nume',
    ];

    public function valabilitati(): HasMany
    {
        return $this->hasMany(Valabilitate::class, 'divizie_id');
    }
}

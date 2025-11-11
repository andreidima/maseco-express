<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Valabilitate extends Model
{
    use HasFactory;

    protected $fillable = [
        'numar_auto',
        'sofer_id',
        'denumire',
        'data_inceput',
        'data_sfarsit',
    ];

    protected $casts = [
        'data_inceput' => 'date',
        'data_sfarsit' => 'date',
    ];

    public function sofer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sofer_id');
    }

    public function syncSummary(): void
    {
        // Funcționalitatea pentru curse va fi adăugată când schema este disponibilă.
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaFisier extends Model
{
    use HasFactory;

    protected $table = 'comenzi_fisiere';
    protected $guarded = [];

    protected $casts = [
        'validat'    => 'integer',
        'este_factura' => 'integer',
    ];

    public function path()
    {
        return "/comenzi-fisiere/{$this->id}";
    }

    public function getNumeAfisatAttribute(): string
    {
        if (filled($this->nume_original)) {
            return (string) $this->nume_original;
        }

        $filename = (string) ($this->nume ?? '');

        if (preg_match('/^(?<base>.+)\.(?<extension>[^.]+)(?<suffix>3[0-9a-f]{13})(?<trailingDot>\.?)$/iu', $filename, $matches)) {
            return $matches['base'] . '_' . $matches['suffix'] . '.' . $matches['extension'];
        }

        return $filename;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValabilitatiAlimentare extends Model
{
    use HasFactory;

    protected $table = 'valabilitati_alimentari';

    protected $fillable = [
        'valabilitate_id',
        'data_ora_alimentare',
        'litrii',
        'pret_pe_litru',
        'total_pret',
        'observatii',
    ];

    protected $casts = [
        'data_ora_alimentare' => 'datetime',
        'litrii' => 'decimal:2',
        'pret_pe_litru' => 'decimal:4',
        'total_pret' => 'decimal:4',
    ];

    public function valabilitate(): BelongsTo
    {
        return $this->belongsTo(Valabilitate::class);
    }
}

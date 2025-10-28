<?php

namespace App\Models\Masini;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasinaDocumentFisier extends Model
{
    use HasFactory;

    protected $fillable = [
        'cale',
        'nume_fisier',
        'nume_original',
        'mime_type',
        'dimensiune',
    ];

    public function document()
    {
        return $this->belongsTo(MasinaDocument::class, 'document_id');
    }
}

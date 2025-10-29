<?php

namespace App\Models\Masini;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masina extends Model
{
    use HasFactory;

    protected $table = 'masini';

    protected $fillable = [
        'numar_inmatriculare',
        'descriere',
    ];

    public function memento()
    {
        return $this->hasOne(MasinaMemento::class);
    }

    public function documente()
    {
        return $this->hasMany(MasinaDocument::class);
    }

    protected static function booted(): void
    {
        static::created(function (Masina $masina) {
            $masina->memento()->create();
            $masina->syncDefaultDocuments();
        });
    }

    public function syncDefaultDocuments(): void
    {
        $definitions = MasinaDocument::defaultDefinitions();

        foreach ($definitions as $definition) {
            $this->documente()->firstOrCreate(
                [
                    'document_type' => $definition['document_type'],
                    'tara' => $definition['tara'] ?? null,
                ],
                []
            );
        }
    }

    protected static function newFactory()
    {
        return \Database\Factories\Masini\MasinaFactory::new();
    }
}

<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlataCalupFisier extends Model
{
    use HasFactory;

    protected $table = 'service_ff_plati_calupuri_fisiere';

    protected $fillable = [
        'plata_calup_id',
        'cale',
        'nume_original',
    ];

    public function calup(): BelongsTo
    {
        return $this->belongsTo(PlataCalup::class, 'plata_calup_id');
    }

    public function extension(): ?string
    {
        $source = $this->nume_original ?: $this->cale;

        if (!$source) {
            return null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);

        if (!$extension) {
            return null;
        }

        return strtolower($extension);
    }

    public function isPreviewable(): bool
    {
        $extension = $this->extension();

        if (!$extension) {
            return false;
        }

        return in_array($extension, [
            'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg',
            'txt', 'csv',
        ], true);
    }
}

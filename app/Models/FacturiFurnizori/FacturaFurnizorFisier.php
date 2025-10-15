<?php

namespace App\Models\FacturiFurnizori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaFurnizorFisier extends Model
{
    use HasFactory;

    protected $table = 'service_ff_facturi_fisiere';

    protected $fillable = [
        'factura_id',
        'cale',
        'nume_original',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaFurnizor::class, 'factura_id');
    }

    public function extension(): ?string
    {
        $source = $this->nume_original ?: $this->cale;

        if (! $source) {
            return null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);

        if (! $extension) {
            return null;
        }

        return strtolower($extension);
    }

    public function isPreviewable(): bool
    {
        $extension = $this->extension();

        if (! $extension) {
            return false;
        }

        return in_array($extension, ['pdf'], true);
    }
}

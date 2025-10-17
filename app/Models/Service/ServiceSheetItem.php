<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_sheet_id',
        'position',
        'description',
        'quantity',
        'notes',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Service\ServiceSheetItemFactory::new();
    }

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(ServiceSheet::class, 'service_sheet_id');
    }
}

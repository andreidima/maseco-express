<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'masina_id',
        'km_bord',
        'data_service',
    ];

    protected $casts = [
        'data_service' => 'date',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Service\ServiceSheetFactory::new();
    }

    public function masina(): BelongsTo
    {
        return $this->belongsTo(Masina::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceSheetItem::class)->orderBy('position');
    }
}

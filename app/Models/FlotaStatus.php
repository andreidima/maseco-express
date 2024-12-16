<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlotaStatus extends Model
{
    use HasFactory;

    protected $table = 'flota_statusuri';
    protected $guarded = [];

    public function path()
    {
        return "/flota-statusuri/{$this->id}";
    }

    /**
     * Get the utilizator that owns the FlotaStatus
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function utilizator(): BelongsTo
    {
        return $this->belongsTo(FlotaStatusUtilizator::class, 'utilizator_id');
    }
}

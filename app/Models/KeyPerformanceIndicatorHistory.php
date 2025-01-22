<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyPerformanceIndicatorHistory extends Model
{
    use HasFactory;

    protected $table = 'key_performance_indicators_history';

    protected $fillable = [
        'kpi_id',
        'user_id',
        'performed_by_user_id',
        'observatii',
        'data',
        'action',
    ];

    public $timestamps = false; // Disable created_at and updated_at

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiWeeklyMark extends Model
{
    protected $table = 'kpi_weekly_marks';

    protected $fillable = [
        'week_start_date',
        'rated_user_id',
        'rated_by_user_id',
        'mark',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'mark' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}


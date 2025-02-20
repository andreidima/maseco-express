<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentWord extends Model
{
    use HasFactory;

    protected $table = 'documente_word';
    protected $guarded = [];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function path()
    {
        return "/documente-word/{$this->id}";
    }

    /**
     * Get the lockedByUser that owns the DocumentWord
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by', 'id');
    }
}

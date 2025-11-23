<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ValabilitateCursaImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'valabilitate_cursa_id',
        'uploaded_by_user_id',
        'path',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'original_name',
    ];

    protected $casts = [
        'valabilitate_cursa_id' => 'integer',
        'uploaded_by_user_id' => 'integer',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function cursa(): BelongsTo
    {
        return $this->belongsTo(ValabilitateCursa::class, 'valabilitate_cursa_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

<?php

namespace App\Models\Masini;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MasinaFisierGeneral extends Model
{
    use HasFactory;

    public const STORAGE_DISK = 'local';
    public const STORAGE_DIRECTORY = 'masini-fisiere-generale';

    protected $table = 'masini_fisiere_generale';

    protected $fillable = [
        'masina_id',
        'cale',
        'nume_original',
        'mime_type',
        'dimensiune',
        'uploaded_by_id',
        'uploaded_by_name',
        'uploaded_by_email',
    ];

    public function masina()
    {
        return $this->belongsTo(Masina::class, 'masina_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (MasinaFisierGeneral $fisier) {
            if (!$fisier->cale) {
                return;
            }

            Storage::disk(self::STORAGE_DISK)->delete($fisier->cale);
        });
    }
}

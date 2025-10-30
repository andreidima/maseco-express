<?php

namespace App\Models\Masini;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MasinaFisierGeneral extends Model
{
    use HasFactory;

    public const STORAGE_DISK = 'public';
    public const STORAGE_DIRECTORY = 'masini';

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

    public function downloadName(): string
    {
        $name = $this->nume_original ?? basename((string) $this->cale);

        $name = is_string($name) ? trim($name) : '';

        return $name === '' ? 'document' : $name;
    }

    public static function storageDisk(): string
    {
        return static::STORAGE_DISK;
    }

    public static function storageDirectoryForMasina(int $masinaId): string
    {
        $baseDirectory = trim(static::STORAGE_DIRECTORY, '/');

        $prefix = $baseDirectory === '' ? '' : $baseDirectory . '/';

        return $prefix . $masinaId . '/generale';
    }

    public function guessMimeType(): ?string
    {
        if (is_string($this->mime_type) && $this->mime_type !== '') {
            return $this->mime_type;
        }

        $extension = strtolower(pathinfo((string) $this->nume_original ?: (string) $this->cale, PATHINFO_EXTENSION));

        $extensionMatch = match ($extension) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            default => null,
        };

        if ($extensionMatch !== null) {
            return $extensionMatch;
        }

        if ($this->cale) {
            $disk = Storage::disk(static::storageDisk());

            if ($disk->exists($this->cale)) {
                $storageMimeType = $disk->mimeType($this->cale);

                if (is_string($storageMimeType) && $storageMimeType !== '') {
                    return $storageMimeType;
                }
            }
        }

        return null;
    }

    public function isPreviewable(): bool
    {
        $mime = strtolower($this->guessMimeType() ?? '');

        if ($mime === 'application/pdf') {
            return true;
        }

        if (Str::startsWith($mime, 'image/')) {
            return true;
        }

        if (Str::startsWith($mime, 'text/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) $this->nume_original ?: (string) $this->cale, PATHINFO_EXTENSION));

        return in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg', 'txt', 'csv'], true);
    }

    public function iconClass(): string
    {
        $mime = strtolower($this->guessMimeType() ?? '');

        if (Str::startsWith($mime, 'image/')) {
            return 'fa-file-image text-primary';
        }

        if ($mime === 'application/pdf') {
            return 'fa-file-pdf text-danger';
        }

        if (Str::startsWith($mime, 'text/')) {
            return 'fa-file-lines text-info';
        }

        if (Str::contains($mime, 'spreadsheet') || Str::contains($mime, 'excel')) {
            return 'fa-file-excel text-success';
        }

        if (Str::contains($mime, 'presentation') || Str::contains($mime, 'powerpoint')) {
            return 'fa-file-powerpoint text-warning';
        }

        if (Str::contains($mime, 'word') || Str::contains($mime, 'document')) {
            return 'fa-file-word text-primary';
        }

        return 'fa-file-lines text-secondary';
    }

    protected static function booted(): void
    {
        static::deleting(function (MasinaFisierGeneral $fisier) {
            if (!$fisier->cale) {
                return;
            }

            Storage::disk(static::storageDisk())->delete($fisier->cale);
        });
    }
}

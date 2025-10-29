<?php

namespace App\Models\Masini;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MasinaDocumentFisier extends Model
{
    use HasFactory;

    public const STORAGE_DISK = 'local';
    public const STORAGE_DIRECTORY = 'masini-mementouri-documente';

    protected $table = 'masini_documente_fisiere';

    protected $fillable = [
        'cale',
        'nume_fisier',
        'nume_original',
        'mime_type',
        'dimensiune',
    ];

    public function document()
    {
        return $this->belongsTo(MasinaDocument::class, 'document_id');
    }

    public function downloadName(): string
    {
        $name = $this->nume_original
            ?? $this->nume_fisier
            ?? basename((string) $this->cale);

        $name = is_string($name) ? trim($name) : '';

        return $name === '' ? 'document' : $name;
    }

    public function guessMimeType(): ?string
    {
        if (is_string($this->mime_type) && $this->mime_type !== '') {
            return $this->mime_type;
        }

        $extension = strtolower(pathinfo((string) ($this->nume_original ?? $this->nume_fisier ?? $this->cale), PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            default => null,
        };
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

        $extension = strtolower(pathinfo((string) ($this->nume_original ?? $this->nume_fisier ?? $this->cale), PATHINFO_EXTENSION));

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

        return 'fa-file-lines text-secondary';
    }

    protected static function booted(): void
    {
        static::deleting(function (MasinaDocumentFisier $fisier) {
            if (!$fisier->cale) {
                return;
            }

            Storage::disk(self::STORAGE_DISK)->delete($fisier->cale);
        });
    }
}

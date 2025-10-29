<?php

namespace App\Support;

use Illuminate\Support\Str;

class BrowserViewableFile
{
    /**
     * Extensions that are generally safe to render inline in the browser.
     *
     * @var array<int, string>
     */
    protected const VIEWABLE_EXTENSIONS = [
        'pdf',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'bmp',
        'webp',
        'svg',
        'txt',
        'csv',
    ];

    /**
     * Determine if a file is likely viewable directly in the browser.
     */
    public static function isViewable(string $filename, ?string $mimeType = null): bool
    {
        if ($mimeType !== null) {
            if (Str::startsWith($mimeType, ['image/', 'text/'])) {
                return true;
            }

            if ($mimeType === 'application/pdf') {
                return true;
            }
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, self::VIEWABLE_EXTENSIONS, true);
    }

    /**
     * Build a safe Content-Disposition header for the provided disposition type.
     */
    public static function contentDisposition(string $disposition, string $filename): string
    {
        $escaped = addcslashes($filename, "\\\"");

        return sprintf('%s; filename="%s"', $disposition, $escaped);
    }
}

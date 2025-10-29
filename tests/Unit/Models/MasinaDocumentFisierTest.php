<?php

namespace Tests\Unit\Models;

use App\Models\Masini\MasinaDocumentFisier;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasinaDocumentFisierTest extends TestCase
{
    public function test_download_name_falls_back_to_generic_when_missing(): void
    {
        $fisier = new MasinaDocumentFisier();

        $this->assertSame('document', $fisier->downloadName());
    }

    #[DataProvider('downloadNameProvider')]
    public function test_download_name_prefers_original_then_filename(?string $original, ?string $filename, string $expected): void
    {
        $fisier = new MasinaDocumentFisier([
            'nume_original' => $original,
            'nume_fisier' => $filename,
            'cale' => 'masini-documente/1/' . ($filename ?? 'fallback.pdf'),
        ]);

        $this->assertSame($expected, $fisier->downloadName());
    }

    public static function downloadNameProvider(): array
    {
        return [
            'has original name' => ['atestat.pdf', 'internal.pdf', 'atestat.pdf'],
            'missing original uses file name' => [null, 'contract.pdf', 'contract.pdf'],
            'trimmed value' => ['   ', 'contract.pdf', 'contract.pdf'],
        ];
    }

    #[DataProvider('mimeTypeProvider')]
    public function test_guess_mime_type_from_attributes(?string $savedMime, ?string $fileName, ?string $expected): void
    {
        $fisier = new MasinaDocumentFisier([
            'mime_type' => $savedMime,
            'nume_original' => $fileName,
        ]);

        $this->assertSame($expected, $fisier->guessMimeType());
    }

    public static function mimeTypeProvider(): array
    {
        return [
            'uses stored mime type' => ['application/pdf', 'contract.pdf', 'application/pdf'],
            'infers from extension' => [null, 'imagine.PNG', 'image/png'],
            'unknown extension' => [null, 'document.bin', null],
        ];
    }

    #[DataProvider('previewableProvider')]
    public function test_is_previewable_handles_common_types(?string $mime, ?string $fileName, bool $expected): void
    {
        $fisier = new MasinaDocumentFisier([
            'mime_type' => $mime,
            'nume_original' => $fileName,
        ]);

        $this->assertSame($expected, $fisier->isPreviewable());
    }

    public static function previewableProvider(): array
    {
        return [
            'pdf mime type' => ['application/pdf', 'atestat.pdf', true],
            'image mime type' => ['image/jpeg', 'poza.jpg', true],
            'text mime type' => ['text/plain', 'note.txt', true],
            'extension fallback' => [null, 'fisier.csv', true],
            'non previewable type' => ['application/vnd.ms-excel', 'date.xls', false],
        ];
    }

    #[DataProvider('iconClassProvider')]
    public function test_icon_class_matches_mime_type(string $mime, string $expected): void
    {
        $fisier = new MasinaDocumentFisier([
            'mime_type' => $mime,
        ]);

        $this->assertSame($expected, $fisier->iconClass());
    }

    public static function iconClassProvider(): array
    {
        return [
            'image' => ['image/png', 'fa-file-image text-primary'],
            'pdf' => ['application/pdf', 'fa-file-pdf text-danger'],
            'text' => ['text/plain', 'fa-file-lines text-info'],
            'other' => ['application/octet-stream', 'fa-file-lines text-secondary'],
        ];
    }
}

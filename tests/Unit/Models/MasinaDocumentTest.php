<?php

namespace Tests\Unit\Models;

use App\Models\Masini\MasinaDocument;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasinaDocumentTest extends TestCase
{
    #[DataProvider('colorClassProvider')]
    public function test_color_class_returns_expected_value(?string $date, string $expected): void
    {
        Carbon::setTestNow(Carbon::create(2024, 5, 1));

        $document = new MasinaDocument();
        $document->data_expirare = $date ? Carbon::parse($date) : null;

        $this->assertSame($expected, $document->colorClass());

        Carbon::setTestNow();
    }

    public static function colorClassProvider(): array
    {
        return [
            'missing date' => [null, 'bg-secondary-subtle'],
            'expired date' => ['2024-04-30', 'bg-danger text-white'],
            'expires in 1 day' => ['2024-05-02', 'bg-danger text-white'],
            'expires in 10 days' => ['2024-05-11', 'bg-expiring-15 text-white'],
            'expires in 20 days' => ['2024-05-21', 'bg-warning'],
            'expires in 45 days' => ['2024-06-15', 'bg-success text-white'],
            'long term' => ['2024-07-15', ''],
        ];
    }

    public function test_days_until_expiry_returns_expected_difference(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 5, 10));

        $document = new MasinaDocument();
        $document->data_expirare = Carbon::parse('2024-05-25');

        $this->assertSame(15, $document->daysUntilExpiry());

        Carbon::setTestNow();
    }

    #[DataProvider('labelProvider')]
    public function test_label_generation(string $type, ?string $country, string $expected): void
    {
        $document = new MasinaDocument([
            'document_type' => $type,
            'tara' => $country,
        ]);

        $this->assertSame($expected, $document->label());
    }

    public static function labelProvider(): array
    {
        return [
            'itp' => [MasinaDocument::TYPE_ITP, null, 'ITP'],
            'vignette known country' => [MasinaDocument::TYPE_VIGNETA, 'hu', 'Vignetă HU'],
            'vignette brennero' => [MasinaDocument::TYPE_VIGNETA, 'brennero', 'Brennero'],
            'vignette unknown country' => [MasinaDocument::TYPE_VIGNETA, 'de', 'Vignetă DE'],
            'talon' => [MasinaDocument::TYPE_TALON, null, 'Talon'],
            'fallback' => ['neidentificat', null, 'Neidentificat'],
        ];
    }
}

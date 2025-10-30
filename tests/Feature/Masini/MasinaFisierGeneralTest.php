<?php

namespace Tests\Feature\Masini;

use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaFisierGeneral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasinaFisierGeneralTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);
    }

    public function test_general_file_upload_stores_file_on_public_disk(): void
    {
        Storage::fake(MasinaFisierGeneral::storageDisk());

        $user = User::factory()->create();
        $masina = Masina::factory()->create();

        $file = UploadedFile::fake()->create('atestat.pdf', 256, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.fisiere-generale.index', $masina))
            ->post(route('masini-mementouri.fisiere-generale.store', $masina), [
                'fisier' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', __('Fișierul a fost încărcat.'));

        $masina->refresh();
        $fisier = $masina->fisiereGenerale()->first();

        $this->assertNotNull($fisier);

        $expectedDirectory = MasinaFisierGeneral::storageDirectoryForMasina($masina->id);

        $this->assertTrue(Str::startsWith($fisier->cale, $expectedDirectory . '/'));

        Storage::disk(MasinaFisierGeneral::storageDisk())->assertExists($fisier->cale);

        $this->assertDatabaseHas('masini_fisiere_generale', [
            'masina_id' => $masina->id,
            'id' => $fisier->id,
            'nume_original' => 'atestat.pdf',
        ]);
    }

    public function test_general_file_download_returns_stream_with_content_type(): void
    {
        Storage::fake(MasinaFisierGeneral::storageDisk());

        $user = User::factory()->create();
        $masina = Masina::factory()->create();

        $path = MasinaFisierGeneral::storageDirectoryForMasina($masina->id) . '/atestat.pdf';
        $content = '%PDF-1.4';

        Storage::disk(MasinaFisierGeneral::storageDisk())->put($path, $content);

        $fisier = $masina->fisiereGenerale()->create([
            'cale' => $path,
            'nume_original' => 'atestat.pdf',
            'mime_type' => null,
            'dimensiune' => strlen($content),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.fisiere-generale.download', [$masina, $fisier]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertDownload($fisier->downloadName());
    }

    public function test_general_file_preview_is_available_for_previewable_files(): void
    {
        Storage::fake(MasinaFisierGeneral::storageDisk());

        $user = User::factory()->create();
        $masina = Masina::factory()->create();

        $path = MasinaFisierGeneral::storageDirectoryForMasina($masina->id) . '/imagine.png';
        $content = 'PNG DATA';

        Storage::disk(MasinaFisierGeneral::storageDisk())->put($path, $content);

        $fisier = $masina->fisiereGenerale()->create([
            'cale' => $path,
            'nume_original' => 'imagine.png',
            'mime_type' => 'image/png',
            'dimensiune' => strlen($content),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.fisiere-generale.preview', [$masina, $fisier]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertStringContainsString('inline', strtolower($response->headers->get('content-disposition')));
    }

    public function test_general_file_preview_is_blocked_for_non_previewable_files_but_download_is_available(): void
    {
        Storage::fake(MasinaFisierGeneral::storageDisk());

        $user = User::factory()->create();
        $masina = Masina::factory()->create();

        $path = MasinaFisierGeneral::storageDirectoryForMasina($masina->id) . '/contract.docx';
        $content = 'DOCX DATA';

        Storage::disk(MasinaFisierGeneral::storageDisk())->put($path, $content);

        $fisier = $masina->fisiereGenerale()->create([
            'cale' => $path,
            'nume_original' => 'contract.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dimensiune' => strlen($content),
        ]);

        $previewResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.fisiere-generale.preview', [$masina, $fisier]));

        $previewResponse->assertNotFound();

        $downloadResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.fisiere-generale.download', [$masina, $fisier]));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('attachment', strtolower($downloadResponse->headers->get('content-disposition')));
    }

    public function test_general_file_download_and_preview_return_not_found_for_mismatched_vehicle(): void
    {
        Storage::fake(MasinaFisierGeneral::storageDisk());

        $user = User::factory()->create();
        $masinaOne = Masina::factory()->create();
        $masinaTwo = Masina::factory()->create();

        $path = MasinaFisierGeneral::storageDirectoryForMasina($masinaTwo->id) . '/atestat.pdf';
        Storage::disk(MasinaFisierGeneral::storageDisk())->put($path, '%PDF');

        $fisier = $masinaTwo->fisiereGenerale()->create([
            'cale' => $path,
            'nume_original' => 'atestat.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 128,
        ]);

        $previewResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.fisiere-generale.preview', [$masinaOne, $fisier]));

        $previewResponse->assertNotFound();

        $downloadResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.fisiere-generale.download', [$masinaOne, $fisier]));

        $downloadResponse->assertNotFound();
    }

    public function test_deleting_general_file_removes_record_and_file(): void
    {
        Storage::fake(MasinaFisierGeneral::storageDisk());

        $user = User::factory()->create();
        $masina = Masina::factory()->create();

        $path = MasinaFisierGeneral::storageDirectoryForMasina($masina->id) . '/atestat.pdf';
        Storage::disk(MasinaFisierGeneral::storageDisk())->put($path, 'PDF content');

        $fisier = $masina->fisiereGenerale()->create([
            'cale' => $path,
            'nume_original' => 'atestat.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 64,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('masini-mementouri.fisiere-generale.destroy', [$masina, $fisier]));

        $response->assertRedirect();
        $response->assertSessionHas('status', __('Fișierul a fost șters.'));

        Storage::disk(MasinaFisierGeneral::storageDisk())->assertMissing($path);
        $this->assertDatabaseMissing('masini_fisiere_generale', ['id' => $fisier->id]);
    }
}

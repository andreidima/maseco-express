<?php

namespace Tests\Feature\Masini;

use App\Http\Controllers\CronJobController;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\MementoAlerta;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\Masini\MasinaDocumentFisier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasiniMementouriTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_vehicle_populates_default_documents(): void
    {
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'CT12ABC']);
        $masina->load('documente', 'memento');

        $this->assertNotNull($masina->memento);
        $this->assertCount(count(MasinaDocument::defaultDefinitions()), $masina->documente);
    }

    public function test_vehicle_store_and_update_persist_additional_fields(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();

        $payload = [
            'numar_inmatriculare' => 'B11NEW',
            'descriere' => 'Cap tractor',
            'marca_masina' => 'Volvo',
            'serie_sasiu' => 'YS2R4X20005399401',
            'email_notificari' => 'test@example.com',
            'observatii' => 'Note inițiale',
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('masini-mementouri.store'), $payload);

        $masina = Masina::where('numar_inmatriculare', 'B11NEW')->first();
        $this->assertNotNull($masina);

        $response->assertRedirect(route('masini-mementouri.show', $masina));
        $this->assertDatabaseHas('masini', [
            'numar_inmatriculare' => 'B11NEW',
            'marca_masina' => 'Volvo',
            'serie_sasiu' => 'YS2R4X20005399401',
        ]);

        $this->assertEquals('Note inițiale', optional($masina->memento)->observatii);
        $this->assertEquals('test@example.com', optional($masina->memento)->email_notificari);

        $updatePayload = [
            'numar_inmatriculare' => 'B11NEW',
            'descriere' => 'Basculantă',
            'marca_masina' => 'DAF',
            'serie_sasiu' => 'XLRTE47MS0E123456',
            'email_notificari' => 'actualizat@example.com',
            'observatii' => 'Actualizat',
            'redirect' => 'index',
        ];

        $response = $this
            ->actingAs($user)
            ->put(route('masini-mementouri.update', $masina), $updatePayload);

        $response->assertRedirect(route('masini-mementouri.index'));

        $masina->refresh();
        $this->assertSame('Basculantă', $masina->descriere);
        $this->assertSame('DAF', $masina->marca_masina);
        $this->assertSame('XLRTE47MS0E123456', $masina->serie_sasiu);
        $this->assertSame('actualizat@example.com', optional($masina->memento)->email_notificari);
        $this->assertSame('Actualizat', optional($masina->memento)->observatii);
    }

    public function test_update_redirects_to_show_when_no_redirect_is_provided(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create([
            'numar_inmatriculare' => 'CT88SHOW',
            'descriere' => 'Test',
        ]);

        $payload = [
            'numar_inmatriculare' => 'CT88SHOW',
            'descriere' => 'Actualizată',
            'marca_masina' => $masina->marca_masina,
            'serie_sasiu' => $masina->serie_sasiu,
            'email_notificari' => 'redirect@example.com',
            'observatii' => 'Observații redirect',
        ];

        $response = $this
            ->actingAs($user)
            ->put(route('masini-mementouri.update', $masina), $payload);

        $response->assertRedirect(route('masini-mementouri.show', $masina));
    }

    public function test_index_document_date_links_to_edit_page(): void
    {
        $this->withoutMiddleware([EnsurePermission::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B55LINK']);
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_ITP)->first();

        $document->update([
            'data_expirare' => Carbon::create(2025, 3, 15),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.index'));

        $response->assertOk();
        $response->assertSee('Legendă culori');
        $response->assertSee('Asigurare CMR');
        $response->assertSee('Brennero');
        $response->assertSee('<a href="' . route('masini-mementouri.show', $masina) . '"', false);
        $response->assertSee('<a href="' . route('masini-mementouri.create') . '"', false);
        $response->assertSee('aria-label="Editează ITP pentru ' . $masina->numar_inmatriculare . '"', false);
        $response->assertSee($document->data_expirare->format('d.m.Y'));
    }

    public function test_document_inline_update_returns_json_and_resets_notifications(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_RCA)->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(120)->toDateString(),
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $payload = [
            'data_expirare' => Carbon::now()->addDays(10)->toDateString(),
        ];

        $response = $this->actingAs($user)->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), $payload);

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'message' => __('Modificarea a fost salvată.'),
        ]);
        $response->assertJsonStructure([
            'status',
            'color_class',
            'days_until_expiry',
            'formatted_date',
            'readable_date',
            'message',
        ]);

        $document->refresh();

        $this->assertEquals(Carbon::parse($payload['data_expirare'])->toDateString(), optional($document->data_expirare)->toDateString());
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);

        $this->assertSame($document->colorClass(), $response->json('color_class'));
        $this->assertSame($document->daysUntilExpiry(), $response->json('days_until_expiry'));
        $this->assertSame($document->data_expirare?->format('Y-m-d'), $response->json('formatted_date'));
        $this->assertSame($document->readableExpiryDate(), $response->json('readable_date'));
    }

    public function test_document_inline_update_preserves_notifications_when_date_is_unchanged(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_ITP)->first();

        $existingDate = Carbon::now()->addDays(45)->toDateString();

        $document->update([
            'data_expirare' => $existingDate,
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), [
                'data_expirare' => $existingDate,
            ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);

        $document->refresh();

        $this->assertSame($existingDate, optional($document->data_expirare)->toDateString());
        $this->assertTrue($document->notificare_60_trimisa);
        $this->assertTrue($document->notificare_30_trimisa);
        $this->assertTrue($document->notificare_15_trimisa);
        $this->assertTrue($document->notificare_1_trimisa);
    }

    public function test_document_inline_update_allows_clearing_date_and_resets_notifications(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_COPIE_CONFORMA)->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(30)->toDateString(),
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), [
                'data_expirare' => null,
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'formatted_date' => null,
            'readable_date' => '—',
            'color_class' => 'bg-secondary-subtle',
        ]);

        $document->refresh();

        $this->assertNull($document->data_expirare);
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
    }

    public function test_document_inline_update_can_mark_document_without_expiry(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_VIGNETA)->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(12)->toDateString(),
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), [
                'fara_expirare' => true,
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'formatted_date' => null,
            'readable_date' => 'FĂRĂ',
            'fara_expirare' => true,
        ]);

        $document->refresh();

        $this->assertTrue($document->isWithoutExpiry());
        $this->assertNull($document->data_expirare);
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
        $this->assertSame($document->colorClass(), $response->json('color_class'));
    }

    public function test_document_inline_update_can_restore_expiry_after_marking_without_expiry(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_ITP)->first();

        $document->update([
            'fara_expirare' => true,
            'data_expirare' => null,
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $newDate = Carbon::now()->addDays(45)->toDateString();

        $response = $this
            ->actingAs($user)
            ->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), [
                'fara_expirare' => false,
                'data_expirare' => $newDate,
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'formatted_date' => $newDate,
            'readable_date' => Carbon::parse($newDate)->format('d.m.Y'),
            'fara_expirare' => false,
        ]);

        $document->refresh();

        $this->assertFalse($document->isWithoutExpiry());
        $this->assertSame($newDate, optional($document->data_expirare)->toDateString());
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
        $this->assertSame($document->colorClass(), $response->json('color_class'));
    }

    public function test_document_inline_update_handles_invalid_payloads(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $originalDate = $document->data_expirare;

        $response = $this
            ->actingAs($user)
            ->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), [
                'data_expirare' => 'not-a-date',
                'email_notificare' => 'invalid-email',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data_expirare', 'email_notificare']);

        $document->refresh();

        $this->assertEquals($originalDate?->toDateString(), optional($document->data_expirare)->toDateString());
        $this->assertNull($document->email_notificare);
    }

    public function test_document_update_via_standard_form_redirects_back_with_flash_message(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B66FORM']);
        $document = $masina->documente()->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(5)->toDateString(),
            'email_notificare' => 'old-alert@example.com',
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $newDate = Carbon::now()->addDays(45)->toDateString();

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.edit', $masina))
            ->patch(route('masini-mementouri.documente.update', [$masina, $document]), [
                'data_expirare' => $newDate,
                'email_notificare' => 'alerts@example.com',
            ]);

        $response->assertRedirect(route('masini-mementouri.edit', $masina));
        $response->assertSessionHas('status', 'Documentul a fost actualizat.');

        $document->refresh();

        $this->assertSame($newDate, optional($document->data_expirare)->toDateString());
        $this->assertSame('alerts@example.com', $document->email_notificare);
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
    }

    public function test_document_update_via_standard_form_can_mark_document_without_expiry(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B90FARA']);
        $document = $masina->documente()->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(20)->toDateString(),
            'email_notificare' => 'initial@example.com',
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.edit', $masina))
            ->patch(route('masini-mementouri.documente.update', [$masina, $document]), [
                'data_expirare' => '',
                'fara_expirare' => '1',
                'email_notificare' => 'final@example.com',
            ]);

        $response->assertRedirect(route('masini-mementouri.edit', $masina));
        $response->assertSessionHas('status', 'Documentul a fost actualizat.');

        $document->refresh();

        $this->assertTrue($document->isWithoutExpiry());
        $this->assertNull($document->data_expirare);
        $this->assertSame('final@example.com', $document->email_notificare);
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
    }

    public function test_document_file_upload_returns_json_payload(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $firstFile = UploadedFile::fake()->create('atestat.pdf', 128, 'application/pdf');
        $secondFile = UploadedFile::fake()->create('atestat-2.pdf', 256, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->postJson(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'fisier' => [$firstFile, $secondFile],
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'message' => __('Fișierele au fost încărcate.'),
        ]);
        $response->assertJsonStructure([
            'status',
            'message',
            'files_html',
        ]);

        $filesHtml = $response->json('files_html');

        $this->assertIsString($filesHtml);
        $this->assertStringContainsString('data-document-files-list', $filesHtml);
        $this->assertStringContainsString($firstFile->getClientOriginalName(), $filesHtml);
        $this->assertStringContainsString($secondFile->getClientOriginalName(), $filesHtml);
        $this->assertStringContainsString('data-document-delete', $filesHtml);

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $firstFile->hashName());
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $secondFile->hashName());
    }

    public function test_document_file_upload_validation_errors_return_json_response(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $response = $this
            ->actingAs($user)
            ->postJson(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'fisier' => [],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fisier']);
    }

    public function test_document_file_upload_requires_file_when_modifying_date(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $newDate = Carbon::now()->addDays(15)->format('Y-m-d');

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.documente.edit', [$masina, $document]))
            ->post(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'data_expirare' => $newDate,
            ]);

        $response->assertRedirect(route('masini-mementouri.documente.edit', [
            $masina,
            MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ]));

        $response->assertSessionHasErrors(['fisier', 'data_expirare']);

        $document->refresh();
        $this->assertNull($document->data_expirare);
    }

    public function test_document_file_upload_with_date_updates_document_and_resets_notifications(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(5)->toDateString(),
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $newDate = Carbon::now()->addDays(90)->format('Y-m-d');
        $file = UploadedFile::fake()->create('atestat.pdf', 128, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.documente.edit', [$masina, $document]))
            ->post(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'data_expirare' => $newDate,
                'fisier' => [$file],
            ]);

        $response->assertRedirect(route('masini-mementouri.documente.edit', [
            $masina,
            MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ]));
        $response->assertSessionHas('status', 'Fișierul a fost încărcat.');

        $document->refresh();
        $this->assertSame($newDate, optional($document->data_expirare)->toDateString());
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(
            MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $file->hashName()
        );
    }

    public function test_document_file_upload_can_mark_document_without_expiry(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(25)->toDateString(),
            'fara_expirare' => false,
            'notificare_60_trimisa' => true,
            'notificare_30_trimisa' => true,
            'notificare_15_trimisa' => true,
            'notificare_1_trimisa' => true,
        ]);

        $file = UploadedFile::fake()->create('atestat.pdf', 128, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.documente.edit', [$masina, $document]))
            ->post(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'data_expirare' => '',
                'fara_expirare' => '1',
                'fisier' => [$file],
            ]);

        $response->assertRedirect(route('masini-mementouri.documente.edit', [
            $masina,
            MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ]));
        $response->assertSessionHas('status', 'Fișierul a fost încărcat.');

        $document->refresh();

        $this->assertTrue($document->isWithoutExpiry());
        $this->assertNull($document->data_expirare);
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(
            MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $file->hashName()
        );
    }

    public function test_document_file_upload_via_standard_form_redirects_to_edit_page(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B77FORM']);
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_RCA)->first();

        $file = UploadedFile::fake()->create('polita.pdf', 128, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.documente.edit', [$masina, $document]))
            ->post(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'fisier' => [$file],
            ]);

        $response->assertRedirect(route('masini-mementouri.documente.edit', [
            $masina,
            MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ]));
        $response->assertSessionHas('status', 'Fișierul a fost încărcat.');

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $file->hashName());

        $this->assertDatabaseHas('masini_documente_fisiere', [
            'document_id' => $document->id,
            'nume_original' => $file->getClientOriginalName(),
        ]);
    }

    public function test_document_file_upload_via_standard_form_handles_multiple_files(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $firstFile = UploadedFile::fake()->create('atestat.pdf', 64, 'application/pdf');
        $secondFile = UploadedFile::fake()->create('atestat-extra.pdf', 32, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.documente.edit', [$masina, $document]))
            ->post(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), [
                'fisier' => [$firstFile, $secondFile],
            ]);

        $response->assertRedirect(route('masini-mementouri.documente.edit', [
            $masina,
            MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ]));
        $response->assertSessionHas('status', 'Fișierele au fost încărcate.');

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $firstFile->hashName());
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists(MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/' . $secondFile->hashName());

        $this->assertDatabaseHas('masini_documente_fisiere', [
            'document_id' => $document->id,
            'nume_original' => $firstFile->getClientOriginalName(),
        ]);

        $this->assertDatabaseHas('masini_documente_fisiere', [
            'document_id' => $document->id,
            'nume_original' => $secondFile->getClientOriginalName(),
        ]);
    }

    public function test_document_file_upload_via_standard_form_redirects_back_with_errors(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $response = $this
            ->actingAs($user)
            ->from(route('masini-mementouri.documente.edit', [$masina, $document]))
            ->post(route('masini-mementouri.documente.fisiere.store', [$masina, $document]), []);

        $response->assertRedirect(route('masini-mementouri.documente.edit', [
            $masina,
            MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ]));
        $response->assertSessionHasErrors('fisier');
    }

    public function test_document_file_delete_returns_json_payload(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $path = MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/contract.pdf';
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->put($path, 'PDF content');

        $fisier = $document->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => 'contract.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 1200,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('masini-mementouri.documente.fisiere.destroy', [$masina, $document, $fisier]));

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
        $response->assertJsonStructure([
            'status',
            'message',
            'files_html',
        ]);

        $this->assertDatabaseMissing('masini_documente_fisiere', ['id' => $fisier->id]);
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertMissing($path);
    }

    public function test_deleting_vehicle_removes_document_files_from_storage(): void
    {
        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $masina = Masina::factory()->create();
        $document = $masina->documente()->first();

        $path = MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/atestat.pdf';
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->put($path, 'PDF content');

        $fisier = $document->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => 'atestat.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 2048,
        ]);

        $masina->delete();

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertMissing($path);
        $this->assertDatabaseMissing('masini_documente', ['id' => $document->id]);
        $this->assertDatabaseMissing('masini_documente_fisiere', ['id' => $fisier->id]);
    }

    public function test_document_file_delete_validates_document_belongs_to_vehicle(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        Storage::fake(MasinaDocumentFisier::STORAGE_DISK);

        $user = User::factory()->create();
        $masinaOne = Masina::factory()->create();
        $masinaTwo = Masina::factory()->create();

        $documentOne = $masinaOne->documente()->first();
        $documentTwo = $masinaTwo->documente()->first();

        $path = MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $documentTwo->id . '/nepotrivit.pdf';
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->put($path, 'PDF content');

        $fisier = $documentTwo->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => 'nepotrivit.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 512,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('masini-mementouri.documente.fisiere.destroy', [$masinaOne, $documentTwo, $fisier]));

        $response->assertNotFound();

        $this->assertDatabaseHas('masini_documente_fisiere', ['id' => $fisier->id]);
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->assertExists($path);
    }

    public function test_cron_job_sends_vehicle_alerts_once_per_threshold(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 3, 24));
        config(['variabile.cron_job_key' => 'test-key']);

        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B99XYZ']);
        $masina->memento->update(['email_notificari' => 'alerta@example.com']);
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_ITP)->first();
        $document->update(['data_expirare' => Carbon::now()->addDays(59)]);

        $controller = app(CronJobController::class);

        ob_start();
        $controller->trimiteMementoAlerte('test-key');
        ob_end_clean();

        Mail::assertSent(MementoAlerta::class, 1);

        Mail::assertSent(MementoAlerta::class, function (MementoAlerta $mail) use ($masina) {
            return str_contains($mail->subiect, $masina->numar_inmatriculare);
        });

        $document->refresh();
        $this->assertTrue($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
    }

    public function test_cron_job_sends_15_day_alert(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 3, 24));
        config(['variabile.cron_job_key' => 'test-key']);

        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B15DAY']);
        $masina->memento->update(['email_notificari' => 'alerta@example.com']);
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_ITP)->first();
        $document->update(['data_expirare' => Carbon::now()->addDays(15)]);

        $controller = app(CronJobController::class);

        ob_start();
        $controller->trimiteMementoAlerte('test-key');
        ob_end_clean();

        Mail::assertSent(MementoAlerta::class, 1);
        Mail::assertSent(MementoAlerta::class, function (MementoAlerta $mail) use ($masina) {
            return str_contains($mail->subiect, $masina->numar_inmatriculare)
                && str_contains($mail->mesaj, 'Prag alertă: 15 zile');
        });

        $document->refresh();
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertTrue($document->notificare_15_trimisa);
        $this->assertFalse($document->notificare_1_trimisa);
    }

    public function test_cron_job_does_not_send_less_urgent_thresholds_after_one_day_alert(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 3, 24));
        config(['variabile.cron_job_key' => 'test-key']);

        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B01URG']);
        $masina->memento->update(['email_notificari' => 'alerta@example.com']);
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_ITP)->first();
        $document->update(['data_expirare' => Carbon::now()->addDay()]);

        $controller = app(CronJobController::class);

        ob_start();
        $controller->trimiteMementoAlerte('test-key');
        ob_end_clean();

        Mail::assertSent(MementoAlerta::class, 1);

        $document->refresh();
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertTrue($document->notificare_1_trimisa);

        Mail::fake();
        Carbon::setTestNow(Carbon::now()->addDay());

        ob_start();
        $controller->trimiteMementoAlerte('test-key');
        ob_end_clean();

        Mail::assertNothingSent();

        $document->refresh();
        $this->assertFalse($document->notificare_60_trimisa);
        $this->assertFalse($document->notificare_30_trimisa);
        $this->assertFalse($document->notificare_15_trimisa);
        $this->assertTrue($document->notificare_1_trimisa);
    }

    public function test_update_from_modal_redirects_back_to_index(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B11XYZ']);

        $response = $this
            ->actingAs($user)
            ->put(route('masini-mementouri.update', $masina), [
                'numar_inmatriculare' => 'B22XYZ',
                'descriere' => 'Test modificare',
                'email_notificari' => 'masecoexpres@gmail.com',
                'observatii' => 'Obs',
                'redirect' => 'index',
            ]);

        $response->assertRedirect(route('masini-mementouri.index'));
        $masina->refresh();

        $this->assertSame('B22XYZ', $masina->numar_inmatriculare);
        $this->assertSame('Test modificare', $masina->descriere);
        $this->assertSame('masecoexpres@gmail.com', optional($masina->memento)->email_notificari);
        $this->assertSame('Obs', optional($masina->memento)->observatii);
    }

    public function test_preview_streams_inline_for_previewable_files(): void
    {
        $this->withoutMiddleware([EnsurePermission::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B33PDF']);
        $document = $masina->documente()->first();

        $path = MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/atestare.pdf';
        $content = '%PDF-1.4 Sample';
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->put($path, $content);

        $fisier = $document->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => 'atestare.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => strlen($content),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $fisier]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', strtolower($response->headers->get('content-disposition')));

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->delete($path);
    }

    public function test_preview_is_blocked_for_non_previewable_files_but_download_is_available(): void
    {
        $this->withoutMiddleware([EnsurePermission::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B44DOC']);
        $document = $masina->documente()->first();

        $path = MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/contract.docx';
        $content = 'Fake DOCX binary';
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->put($path, $content);

        $fisier = $document->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => 'contract.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dimensiune' => strlen($content),
        ]);

        $previewResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $fisier]));

        $previewResponse->assertNotFound();

        $downloadResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.documente.fisiere.download', [$masina, $document, $fisier]));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('attachment', strtolower($downloadResponse->headers->get('content-disposition')));

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->delete($path);
    }

    public function test_download_and_preview_return_not_found_for_mismatched_vehicle(): void
    {
        $this->withoutMiddleware([EnsurePermission::class]);

        $user = User::factory()->create();
        $masinaOne = Masina::factory()->create(['numar_inmatriculare' => 'B88MISM']);
        $masinaTwo = Masina::factory()->create(['numar_inmatriculare' => 'B99MISM']);

        $documentTwo = $masinaTwo->documente()->first();

        $path = MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $documentTwo->id . '/atestare.pdf';
        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->put($path, '%PDF');

        $fisier = $documentTwo->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => 'atestare.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 64,
        ]);

        $previewResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.documente.fisiere.preview', [$masinaOne, $documentTwo, $fisier]));

        $previewResponse->assertNotFound();

        $downloadResponse = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.documente.fisiere.download', [$masinaOne, $documentTwo, $fisier]));

        $downloadResponse->assertNotFound();

        Storage::disk(MasinaDocumentFisier::STORAGE_DISK)->delete($path);
    }

    public function test_edit_page_displays_correct_preview_and_download_actions(): void
    {
        $this->withoutMiddleware([EnsurePermission::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B77EDIT']);
        $document = $masina->documente()->first();

        $previewable = $document->fisiere()->create([
            'cale' => MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/atestat.pdf',
            'nume_fisier' => 'atestat.pdf',
            'nume_original' => 'atestat.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 2048,
        ]);

        $nonPreviewable = $document->fisiere()->create([
            'cale' => MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/contract.docx',
            'nume_fisier' => 'contract.docx',
            'nume_original' => 'contract.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dimensiune' => 4096,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.edit', $masina));

        $response->assertOk();
        $response->assertSee('<a href="' . route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $previewable]) . '"', false);
        $response->assertSee('<a href="' . route('masini-mementouri.documente.fisiere.download', [$masina, $document, $previewable]) . '"', false);
        $response->assertSee('<a href="' . route('masini-mementouri.documente.fisiere.download', [$masina, $document, $nonPreviewable]) . '"', false);
        $response->assertDontSee('<a href="' . route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $nonPreviewable]) . '"', false);
    }

    public function test_show_page_displays_correct_preview_and_download_actions(): void
    {
        $this->withoutMiddleware([EnsurePermission::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create(['numar_inmatriculare' => 'B77SHOW']);
        $document = $masina->documente()->first();

        $previewable = $document->fisiere()->create([
            'cale' => MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/atestat.pdf',
            'nume_fisier' => 'atestat.pdf',
            'nume_original' => 'atestat.pdf',
            'mime_type' => 'application/pdf',
            'dimensiune' => 1024,
        ]);

        $nonPreviewable = $document->fisiere()->create([
            'cale' => MasinaDocumentFisier::STORAGE_DIRECTORY . '/' . $document->id . '/contract.docx',
            'nume_fisier' => 'contract.docx',
            'nume_original' => 'contract.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dimensiune' => 2048,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('masini-mementouri.show', $masina));

        $response->assertOk();
        $response->assertSee('<a href="' . route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $previewable]) . '"', false);
        $response->assertSee('<a href="' . route('masini-mementouri.documente.fisiere.download', [$masina, $document, $previewable]) . '"', false);
        $response->assertSee('<a href="' . route('masini-mementouri.documente.fisiere.download', [$masina, $document, $nonPreviewable]) . '"', false);
        $response->assertDontSee('<a href="' . route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $nonPreviewable]) . '"', false);
    }
}

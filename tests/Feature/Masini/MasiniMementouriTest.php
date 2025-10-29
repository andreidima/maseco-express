<?php

namespace Tests\Feature\Masini;

use App\Http\Controllers\CronJobController;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\MementoAlerta;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function test_document_inline_update_returns_json_and_resets_notifications(): void
    {
        $this->withoutMiddleware([EnsurePermission::class, VerifyCsrfToken::class]);

        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $document = $masina->documente()->where('document_type', MasinaDocument::TYPE_RCA)->first();

        $document->update([
            'data_expirare' => Carbon::now()->addDays(120)->toDateString(),
            'notificare_60_trimisa' => true,
        ]);

        $payload = [
            'data_expirare' => Carbon::now()->addDays(10)->toDateString(),
        ];

        $response = $this->actingAs($user)->patchJson(route('masini-mementouri.documente.update', [$masina, $document]), $payload);

        $response->assertOk();
        $response->assertJson(["status" => 'ok']);

        $document->refresh();

        $this->assertEquals(Carbon::parse($payload['data_expirare'])->toDateString(), optional($document->data_expirare)->toDateString());
        $this->assertFalse($document->notificare_60_trimisa);
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
        $this->assertFalse($document->notificare_1_trimisa);
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
}

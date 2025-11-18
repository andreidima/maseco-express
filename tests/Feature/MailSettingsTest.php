<?php

namespace Tests\Feature;

use App\Mail\TestMailConfiguration;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_mail_settings_screen(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('tech.mail-settings.index'));

        $response->assertOk();
        $response->assertSee('Configurare email');
    }

    public function test_user_four_can_view_mail_settings_screen(): void
    {
        $user = User::factory()->create(['id' => 4]);

        $response = $this->actingAs($user)->get(route('tech.mail-settings.index'));

        $response->assertOk();
    }

    public function test_regular_user_cannot_view_mail_settings_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tech.mail-settings.index'));

        $response->assertForbidden();
    }

    public function test_updating_mail_settings_persists_values_and_updates_config(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $payload = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_username' => 'demo-user',
            'mail_password' => 'demo-pass',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Maseco QA',
            'mail_reply_to_address' => 'support@example.com',
            'mail_reply_to_name' => 'Support',
        ];

        $response = $this->actingAs($superAdmin)->post(route('tech.mail-settings.update'), $payload);

        $response->assertRedirect(route('tech.mail-settings.index'));
        $response->assertSessionHas('status');

        $this->assertSame('smtp', SystemSetting::where('key', 'MAIL_MAILER')->value('value'));
        $this->assertSame('smtp.mailtrap.io', SystemSetting::where('key', 'MAIL_HOST')->value('value'));
        $this->assertSame('2525', SystemSetting::where('key', 'MAIL_PORT')->value('value'));
        $this->assertSame('demo-user', config('mail.mailers.smtp.username'));
        $this->assertSame('demo-pass', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));
        $this->assertSame('Support', data_get(config('mail.reply_to'), 'name'));
    }

    public function test_send_test_email_dispatches_message(): void
    {
        Mail::fake();
        $superAdmin = $this->createSuperAdmin();

        $payload = [
            'test_recipient' => 'qa@example.com',
            'test_subject' => 'Verificare SMTP',
            'test_message' => 'Mesaj de test.',
        ];

        $response = $this->actingAs($superAdmin)->post(route('tech.mail-settings.test'), $payload);

        $response->assertRedirect(route('tech.mail-settings.index'));
        $response->assertSessionHas('test_status');

        Mail::assertSent(TestMailConfiguration::class, function (TestMailConfiguration $mail) {
            return $mail->hasTo('qa@example.com')
                && $mail->subjectLine === 'Verificare SMTP'
                && $mail->messageBody === 'Mesaj de test.';
        });
    }

    private function createSuperAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to the technical toolbox.',
            ]
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

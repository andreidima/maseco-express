<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Mail\TestMailConfiguration;
use App\Support\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MailSettingsController extends Controller
{
    public function index(MailSettings $mailSettings): View
    {
        return view('tech.mail-settings.index', [
            'mailSettings' => $mailSettings->get(),
        ]);
    }

    public function update(Request $request, MailSettings $mailSettings): RedirectResponse
    {
        $data = $request->validate([
            'mail_mailer' => ['required', 'string', 'max:50'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'max:50'],
            'mail_from_address' => ['required', 'email'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_reply_to_address' => ['nullable', 'email'],
            'mail_reply_to_name' => ['nullable', 'string', 'max:255'],
        ]);

        $mailSettings->update($data);

        return redirect()
            ->route('tech.mail-settings.index')
            ->with('status', 'Setările de email au fost actualizate și vor fi folosite imediat.');
    }

    public function sendTest(Request $request, MailSettings $mailSettings): RedirectResponse
    {
        $data = $request->validate([
            'test_recipient' => ['required', 'email'],
            'test_subject' => ['nullable', 'string', 'max:255'],
            'test_message' => ['nullable', 'string', 'max:5000'],
        ]);

        $subject = $data['test_subject'] ?: 'Test configurare email Maseco';
        $messageBody = $data['test_message'] ?: 'Acesta este un email de test trimis din aplicația Maseco pentru a verifica configurarea SMTP.';

        $mailSettings->applyToConfig();

        try {
            Mail::to($data['test_recipient'])->send(new TestMailConfiguration($subject, $messageBody));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('tech.mail-settings.index')
                ->with('test_error', 'Trimiterea emailului de test a eșuat: ' . $exception->getMessage());
        }

        return redirect()
            ->route('tech.mail-settings.index')
            ->with('test_status', 'Emailul de test a fost trimis către ' . $data['test_recipient'] . '.');
    }
}

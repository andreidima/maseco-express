<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

use Illuminate\Mail\Mailables\Headers;

class TrimitereCodAutentificarePrinEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userCodEmail;
    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
    )
    {
        $this->userCodEmail = $user->cod_email;
    }

    /**
     * Get the message headers.
     */
    // public function headers(): Headers
    // {
    //     return new Headers(
            // messageId: 'custom-message-id@example.com',
            // messageId: \Carbon\Carbon::now()->todatetimestring(),
            // references: ['previous-message@example.com'],
            // text: [
            //     'X-Custom-Header' => 'Custom Value',
            // ],
    //     );
    // }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $user = $this->user;

        return new Envelope(
            // subject: 'Codul tău de logare în aplicația Maseco Express este ' . $user->cod_email .
            //  '. Următorul cod poate fi generat la ' . Carbon::parse($user->updated_at)->addMinutes(5)->isoFormat('HH:mm DD.MM.YYYY') ,
            subject: 'Codul tau de logare in aplicatia Maseco Express este ' . $user->cod_email,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailuri.trimitereCodAutentificarePrinEmail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

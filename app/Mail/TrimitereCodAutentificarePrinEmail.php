<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Headers;

class TrimitereCodAutentificarePrinEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
    )
    {
        //
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
        return new Envelope(
            subject: 'Codul tău de logare în aplicația Maseco Express.',
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

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
use App\Models\Firma;

use Illuminate\Mail\Mailables\Headers;

class InformareAdaugareClientNouInDB extends Mailable
{
    use Queueable, SerializesModels;

    public $userCodEmail;
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Firma $firma,
    )
    {}

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
            subject: 'A fost adăugat un nou client în aplicația Maseco Express. Asigură-te că ai verificat noul client și că are Bonitate.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailuri.firme.informareAdaugareClientNouInDB',
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

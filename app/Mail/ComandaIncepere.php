<?php

namespace App\Mail;

use App\Models\Comanda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Headers;

class ComandaIncepere extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Comanda $comanda,
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
            subject: 'Începe comanda ' . $this->comanda->transportator_contract . ' - ' . ($this->comanda->data_ora ?? \Carbon\Carbon::parse($this->comanda->data_ora)->isoFormat('HH:mm DD.MM.YYYY')) . ' (' .  uniqid() . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailuri.comenzi.incepere',
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

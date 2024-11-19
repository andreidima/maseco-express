<?php

namespace App\Mail;

use App\Models\Comanda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComandaTransportatorDocumente extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Comanda $comanda,
        public $tipEmail,
        public $mesaj = null,
    )
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject:
                ($this->tipEmail == 'transportatorCatreMaseco') ?
                    ($this->comanda->transportator->nume ?? 'Transportatorul') . ' a încărcat documentele necesare la comanda ' . $this->comanda->transportator_contract
                    :
                    (
                        ($this->tipEmail == 'MasecoCatreTransportatorGoodDocuments') ?
                        'Documentele comenzii ' . $this->comanda->transportator_contract . ' sunt corecte.'
                        :
                        (
                            ($this->tipEmail == 'MasecoCatreTransportatorBadDocuments') ?
                            'Documentele comenzii ' . $this->comanda->transportator_contract . ' nu sunt corecte.'
                            :
                            $this->tipEmail
                        )
                    )
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailuri.comenzi.trimitereEmailTransportatorCatreMasecoDocumenteIncarcate',
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

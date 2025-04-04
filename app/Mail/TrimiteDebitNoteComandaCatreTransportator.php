<?php

namespace App\Mail;

use App\Models\Comanda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Attachment;

class TrimiteDebitNoteComandaCatreTransportator extends Mailable
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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DEBIT NOTE Comandă ' . $this->comanda->transportator_contract,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailuri.comenzi.trimiteDebitNoteComandaCatreTransportator',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $comanda = $this->comanda;

            $pdf = \PDF::loadView('comenzi.export.comandaDebitNotePdf', compact('comanda'))
                ->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option("enable_php", true);

            // $pdf->download('Contract ' . $comanda->transportator_contract . '.pdf');

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Debit note contract ' . $comanda->transportator_contract . '.pdf'),
        ];
    }
}

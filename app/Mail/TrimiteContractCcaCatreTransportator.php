<?php

namespace App\Mail;

use App\Models\Firma;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;

use Illuminate\Mail\Mailables\Attachment;

class TrimiteContractCcaCatreTransportator extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Firma $firma,
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
            subject: 'Contract Cadru Maseco Expres',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emailuri.comenzi.trimiteContractCcaCatreTransportator',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $firma = $this->firma;

        if (is_null($firma->contract_nr)){
            $firma->contract_nr = (Firma::max('contract_nr') ?? '0') + 1;
            $firma->contract_data = Carbon::now();
            $firma->save();
        }

            $pdf = \PDF::loadView('firme.export.contractCadruPdf', compact('firma'))
                ->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option("enable_php", true);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Contract Cadru Maseco Expres.pdf'),
        ];
    }
}

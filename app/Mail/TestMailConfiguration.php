<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMailConfiguration extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageBody;
    public string $subjectLine;

    public function __construct(string $subjectLine, string $messageBody)
    {
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.test-mail-configuration')
            ->with([
                'body' => $this->messageBody,
            ]);
    }
}

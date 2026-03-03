<?php

namespace App\Mail;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbsenceSignatureLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Absence $absence,
        public User $student,
        public User $recipient,
        public string $signatureLink
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Firma assenza - ' . $this->student->name)
            ->view('emails.absence-signature-link');
    }
}

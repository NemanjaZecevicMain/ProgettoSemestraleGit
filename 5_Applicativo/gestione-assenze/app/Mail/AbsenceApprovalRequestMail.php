<?php

namespace App\Mail;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbsenceApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Absence $absence,
        public User $student,
        public User $recipient,
        public string $approvalsUrl,
        public string $targetRole
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Nuova richiesta assenza - ' . $this->student->name)
            ->view('emails.absence-approval-request');
    }
}

<?php

namespace App\Mail;

use App\Models\MonthlyReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public MonthlyReport $report,
        public string $absolutePath
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Report mensile assenze e ritardi - ' . $this->report->month)
            ->view('emails.monthly-report')
            ->attach($this->absolutePath, [
                'as' => 'report_' . $this->student->id . '_' . $this->report->month . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}

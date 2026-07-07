<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies the on-duty officer(s) of a new emergency report (ToR §M3). Queued so
 * the public POST /reports response is not blocked on mail delivery.
 */
class NewReportNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Report $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая заявка о ЧС: '.$this->report->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.new',
        );
    }
}

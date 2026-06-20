<?php

namespace App\Mail;

use App\Models\EventAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $event
     */
    public function __construct(
        public array $event,
        public EventAttendee $attendee,
        public string $leadTimeLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: {$this->event['title']} starts in {$this->leadTimeLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendees.reminder',
        );
    }
}

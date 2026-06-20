<?php

namespace App\Mail;

use App\Models\EventAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventAttendanceConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $event
     */
    public function __construct(
        public array $event,
        public EventAttendee $attendee,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're on the list for {$this->event['title']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendees.confirmed',
        );
    }
}

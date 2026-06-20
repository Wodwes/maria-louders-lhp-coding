<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\EventAttendee;
use App\Support\EventPresenter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send 3-day and 24-hour event reminders to attendees.';

    public function handle(EventPresenter $presenter): int
    {
        $threeDaySent = $this->sendWindow(
            presenter: $presenter,
            sentAtColumn: 'three_day_reminder_sent_at',
            start: CarbonImmutable::now('UTC')->addDays(3),
            end: CarbonImmutable::now('UTC')->addDays(3)->addHour(),
            label: '3 days',
        );

        $dayBeforeSent = $this->sendWindow(
            presenter: $presenter,
            sentAtColumn: 'day_before_reminder_sent_at',
            start: CarbonImmutable::now('UTC')->addDay(),
            end: CarbonImmutable::now('UTC')->addDay()->addHour(),
            label: '24 hours',
        );

        $this->info("Sent {$threeDaySent} three-day reminders and {$dayBeforeSent} 24-hour reminders.");

        return self::SUCCESS;
    }

    private function sendWindow(
        EventPresenter $presenter,
        string $sentAtColumn,
        CarbonImmutable $start,
        CarbonImmutable $end,
        string $label,
    ): int {
        $sent = 0;

        EventAttendee::query()
            ->with('event.user')
            ->whereNull($sentAtColumn)
            ->whereHas('event', function ($query) use ($start, $end) {
                $query
                    ->whereIn('status', ['published', 'sold_out'])
                    ->whereBetween('created_time', [$start->timestamp, $end->timestamp]);
            })
            ->orderBy('id')
            ->chunkById(200, function ($attendees) use ($label, $presenter, $sentAtColumn, &$sent) {
                foreach ($attendees as $attendee) {
                    $event = $attendee->event;

                    if (! $event) {
                        continue;
                    }

                    Mail::to($attendee->email)->send(
                        new EventReminderMail($presenter->present($event), $attendee, $label),
                    );

                    $attendee->forceFill([$sentAtColumn => now()])->save();
                    $sent++;
                }
            });

        return $sent;
    }
}

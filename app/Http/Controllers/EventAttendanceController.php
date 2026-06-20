<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventAttendeeRequest;
use App\Mail\EventAttendanceConfirmedMail;
use App\Models\Event;
use App\Support\EventPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class EventAttendanceController extends Controller
{
    public function store(
        StoreEventAttendeeRequest $request,
        Event $event,
        EventPresenter $presenter,
    ): RedirectResponse {
        if (! in_array($event->status, ['published', 'sold_out'], true)) {
            return back()->withErrors([
                'registration' => 'Registrations are only open for published events.',
            ]);
        }

        $attendee = $event->attendees()->create($request->validated());

        Mail::to($attendee->email)->send(
            new EventAttendanceConfirmedMail($presenter->present($event->loadMissing('user')), $attendee),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "You're on the list for {$presenter->present($event)['title']}.",
        ]);

        return to_route('events.show', $event);
    }
}

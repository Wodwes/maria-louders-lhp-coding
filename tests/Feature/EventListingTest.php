<?php

use App\Mail\EventAttendanceConfirmedMail;
use App\Mail\EventReminderMail;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function eventPayload(string $title, int $startsAt, string $venue = 'The Grand Hall'): array
{
    return [
        'name' => $title,
        'description' => "Join us for {$title}, a live event built for discovery.",
        'organizer' => ['name' => 'Events Team', 'verified' => true],
        'venue' => ['name' => $venue, 'capacity' => 1200],
        'location' => ['lat' => 40.7128, 'lng' => -74.0060],
        'schedule' => ['starts_at' => $startsAt, 'ends_at' => $startsAt + 7200],
        'pricing' => ['currency' => 'USD', 'min_price' => 25],
    ];
}

it('renders the events overview with visual launch data', function () {
    $startsAt = now()->addWeek()->timestamp;

    Event::factory()->create([
        'type' => 'concert',
        'status' => 'published',
        'created_time' => $startsAt,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => eventPayload('Global Jazz Night', $startsAt),
    ]);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/Index')
            ->has('statuses', 4)
            ->has('locations')
            ->has('featuredEvents', 1)
            ->where('featuredEvents.0.title', 'Global Jazz Night')
            ->has('featuredEvents.0.images', 3)
            ->has('visuals', 2)
        );
});

it('returns presented event data for the json endpoint', function () {
    $startsAt = now()->addDays(10)->timestamp;

    Event::factory()->create([
        'type' => 'concert',
        'status' => 'published',
        'created_time' => $startsAt,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => eventPayload('Future Sound Session', $startsAt),
    ]);

    $this->getJson(route('events.data'))
        ->assertOk()
        ->assertJsonStructure([
            'events' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'location' => ['label', 'city', 'country', 'timezone'],
                    'schedule' => ['starts_at', 'local_label', 'timezone'],
                    'images',
                    'attendee_count',
                ],
            ],
            'filters',
            'locations',
            'pagination' => ['current_page', 'has_more_pages', 'next_page_url', 'previous_page_url'],
            'summary',
        ])
        ->assertJsonPath('events.0.title', 'Future Sound Session')
        ->assertJsonPath('events.0.location.city', 'New York');
});

it('filters visual pages by location and date', function () {
    $startsAt = CarbonImmutable::now('UTC')->addDays(8);

    Event::factory()->create([
        'type' => 'conference',
        'status' => 'published',
        'created_time' => $startsAt->timestamp,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => eventPayload('New York Design Forum', $startsAt->timestamp),
    ]);

    Event::factory()->create([
        'type' => 'conference',
        'status' => 'published',
        'created_time' => $startsAt->timestamp,
        'latitude' => 34.0522,
        'longitude' => -118.2437,
        'payload' => eventPayload('Los Angeles Design Forum', $startsAt->timestamp),
    ]);

    $this->get(route('events.visual1', [
        'location' => 'new-york',
        'from' => $startsAt->subDay()->toDateString(),
        'to' => $startsAt->addDay()->toDateString(),
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/VisualOne')
            ->has('events', 1)
            ->where('events.0.title', 'New York Design Forum')
            ->where('summary.location_label', 'New York, United States')
        );

    $this->get(route('events.visual2', [
        'location' => 'new-york',
        'from' => $startsAt->addDay()->toDateString(),
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/VisualTwo')
            ->has('events', 0)
        );
});

it('shows event detail data with attendees and related events', function () {
    $startsAt = now()->addDays(6)->timestamp;
    $event = Event::factory()->create([
        'type' => 'festival',
        'status' => 'published',
        'created_time' => $startsAt,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => eventPayload('Aurora City Festival', $startsAt),
    ]);

    $event->attendees()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.test']);

    Event::factory()->create([
        'type' => 'festival',
        'status' => 'published',
        'created_time' => now()->addDays(7)->timestamp,
        'latitude' => 40.7300,
        'longitude' => -73.9900,
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/Show')
            ->where('event.id', $event->id)
            ->where('event.title', 'Aurora City Festival')
            ->where('event.attendee_count', 1)
            ->has('event.attendees', 1)
            ->has('event.images', 3)
            ->has('relatedEvents', 1)
        );
});

it('registers an attendee and sends a confirmation email', function () {
    Mail::fake();

    $startsAt = now()->addDays(5)->timestamp;
    $event = Event::factory()->create([
        'status' => 'published',
        'created_time' => $startsAt,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => eventPayload('Registration Test Night', $startsAt),
    ]);

    $this->post(route('events.attendees.store', $event), [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.test',
    ])
        ->assertRedirect(route('events.show', $event));

    $this->assertDatabaseHas('event_attendees', [
        'event_id' => $event->id,
        'name' => 'Grace Hopper',
        'email' => 'grace@example.test',
    ]);

    Mail::assertSent(
        EventAttendanceConfirmedMail::class,
        fn (EventAttendanceConfirmedMail $mail) => $mail->attendee->email === 'grace@example.test'
            && $mail->event['title'] === 'Registration Test Night',
    );
});

it('prevents duplicate attendee emails for the same event', function () {
    $event = Event::factory()->create(['status' => 'published']);
    $event->attendees()->create(['name' => 'First Guest', 'email' => 'guest@example.test']);

    $this->from(route('events.show', $event))
        ->post(route('events.attendees.store', $event), [
            'name' => 'Second Guest',
            'email' => 'guest@example.test',
        ])
        ->assertRedirect(route('events.show', $event))
        ->assertSessionHasErrors('email');

    expect($event->attendees()->where('email', 'guest@example.test')->count())->toBe(1);
});

it('sends three-day and 24-hour reminder emails once', function () {
    Mail::fake();

    $threeDayEvent = Event::factory()->create([
        'status' => 'published',
        'created_time' => CarbonImmutable::now('UTC')->addDays(3)->addMinutes(20)->timestamp,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);
    $dayBeforeEvent = Event::factory()->create([
        'status' => 'sold_out',
        'created_time' => CarbonImmutable::now('UTC')->addDay()->addMinutes(20)->timestamp,
        'latitude' => 34.0522,
        'longitude' => -118.2437,
    ]);

    $threeDayAttendee = $threeDayEvent->attendees()->create(['name' => 'Three Day', 'email' => 'three@example.test']);
    $dayBeforeAttendee = $dayBeforeEvent->attendees()->create(['name' => 'Day Before', 'email' => 'day@example.test']);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertSent(EventReminderMail::class, 2);
    Mail::assertSent(EventReminderMail::class, fn (EventReminderMail $mail) => $mail->leadTimeLabel === '3 days');
    Mail::assertSent(EventReminderMail::class, fn (EventReminderMail $mail) => $mail->leadTimeLabel === '24 hours');

    expect($threeDayAttendee->refresh()->three_day_reminder_sent_at)->not->toBeNull()
        ->and($dayBeforeAttendee->refresh()->day_before_reminder_sent_at)->not->toBeNull();
});

it('renders the dashboard without authentication', function () {
    $this->get(route('dashboard'))->assertOk();
});

<?php

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EventPresenter
{
    public function __construct(
        private readonly EventLocationDirectory $locations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Event $event): array
    {
        $payload = $event->payload ?? [];
        $location = $this->locations->nearest($event->latitude, $event->longitude);
        $startsAt = $this->timestampToCarbon(Arr::get($payload, 'schedule.starts_at', $event->created_time));
        $endsAt = $this->timestampToCarbon(Arr::get($payload, 'schedule.ends_at'));
        $timezone = $location['timezone'] ?? 'UTC';
        $title = Arr::get($payload, 'name', Str::headline($event->type).' Spotlight');
        $description = Arr::get($payload, 'description', "Join us for {$title}.");
        $venue = Arr::get($payload, 'venue.name', 'Venue to be announced');
        $pricing = Arr::get($payload, 'pricing', []);
        $minPrice = $pricing['min_price'] ?? null;
        $images = $this->imageSetFor($event, $title);

        return [
            'id' => $event->id,
            'type' => $event->type,
            'type_label' => Str::headline($event->type),
            'status' => $event->status,
            'title' => $title,
            'description' => Str::of($description)->squish()->toString(),
            'excerpt' => Str::limit(Str::of($description)->squish()->toString(), 150),
            'organizer' => Arr::get($payload, 'organizer.name', $event->user?->name ?? 'Event team'),
            'venue' => $venue,
            'location' => [
                'slug' => $location['slug'] ?? null,
                'label' => $location ? "{$venue}, {$location['city']}, {$location['country']}" : $venue,
                'city' => $location['city'] ?? 'Global',
                'country' => $location['country'] ?? 'Online',
                'timezone' => $timezone,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
            ],
            'schedule' => [
                'starts_at' => $startsAt?->toIso8601String(),
                'ends_at' => $endsAt?->toIso8601String(),
                'timezone' => $timezone,
                'local_label' => $startsAt?->setTimezone($timezone)->format('D, M j - g:i A T'),
                'date_label' => $startsAt?->setTimezone($timezone)->format('l, F j'),
                'time_label' => $startsAt?->setTimezone($timezone)->format('g:i A T'),
                'viewer_hint' => $startsAt?->format('D, M j - g:i A').' in your local time',
            ],
            'pricing' => [
                'currency' => $pricing['currency'] ?? 'USD',
                'min_price' => $minPrice,
                'label' => $this->formatPrice($minPrice, $pricing['currency'] ?? 'USD'),
            ],
            'capacity' => Arr::get($payload, 'venue.capacity'),
            'images' => $images,
            'primary_image' => $images[0] ?? null,
            'attendee_count' => (int) ($event->attendees_count ?? $event->attendees?->count() ?? 0),
            'is_registration_open' => in_array($event->status, ['published', 'sold_out'], true),
        ];
    }

    /**
     * @return array<int, array{src: string, alt: string}>
     */
    private function imageSetFor(Event $event, string $title): array
    {
        /** @var array<string, array<int, string>> $catalog */
        $catalog = config('event_images', []);
        $paths = $catalog[$event->type] ?? $catalog['default'] ?? [];

        if (count($paths) < 2) {
            return [];
        }

        $rotation = abs(crc32($event->id)) % count($paths);
        $rotated = array_merge(array_slice($paths, $rotation), array_slice($paths, 0, $rotation));

        return array_map(
            fn (string $src, int $index) => ['src' => $src, 'alt' => "{$title} visual ".($index + 1)],
            array_slice($rotated, 0, 3),
            array_keys(array_slice($rotated, 0, 3)),
        );
    }

    private function timestampToCarbon(mixed $timestamp): ?CarbonImmutable
    {
        if (! is_numeric($timestamp)) {
            return null;
        }

        return CarbonImmutable::createFromTimestampUTC((int) $timestamp);
    }

    private function formatPrice(mixed $amount, string $currency): string
    {
        if (! is_numeric($amount)) {
            return 'TBA';
        }

        $amount = (float) $amount;

        if ($amount <= 0) {
            return 'Free';
        }

        return $currency.' '.number_format($amount, 2);
    }
}

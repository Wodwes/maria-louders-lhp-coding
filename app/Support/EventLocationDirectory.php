<?php

namespace App\Support;

class EventLocationDirectory
{
    /**
     * @return array<string, array{city: string, country: string, timezone: string, latitude: float, longitude: float}>
     */
    public function all(): array
    {
        /** @var array<string, array{city: string, country: string, timezone: string, latitude: float, longitude: float}> $locations */
        $locations = config('event_locations', []);

        return $locations;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->all() as $slug => $location) {
            $options[] = [
                'value' => $slug,
                'label' => "{$location['city']}, {$location['country']}",
            ];
        }

        return $options;
    }

    /**
     * @return array{slug: string, city: string, country: string, timezone: string, latitude: float, longitude: float}|null
     */
    public function find(?string $slug): ?array
    {
        if (! $slug) {
            return null;
        }

        $location = $this->all()[$slug] ?? null;

        if (! $location) {
            return null;
        }

        return ['slug' => $slug, ...$location];
    }

    /**
     * @return array{slug: string, city: string, country: string, timezone: string, latitude: float, longitude: float}|null
     */
    public function nearest(?float $latitude, ?float $longitude): ?array
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $closest = null;
        $closestDistance = null;

        foreach ($this->all() as $slug => $location) {
            $distance = (($latitude - $location['latitude']) ** 2) + (($longitude - $location['longitude']) ** 2);

            if ($closestDistance === null || $distance < $closestDistance) {
                $closestDistance = $distance;
                $closest = ['slug' => $slug, ...$location];
            }
        }

        return $closest;
    }

    /**
     * @return array{min_latitude: float, max_latitude: float, min_longitude: float, max_longitude: float}|null
     */
    public function bounds(?string $slug, float $padding = 0.7): ?array
    {
        $location = $this->find($slug);

        if (! $location) {
            return null;
        }

        return [
            'min_latitude' => $location['latitude'] - $padding,
            'max_latitude' => $location['latitude'] + $padding,
            'min_longitude' => $location['longitude'] - $padding,
            'max_longitude' => $location['longitude'] + $padding,
        ];
    }
}

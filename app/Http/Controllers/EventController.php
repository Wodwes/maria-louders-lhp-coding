<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\EventLocationDirectory;
use App\Support\EventPresenter;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private readonly EventLocationDirectory $locations,
        private readonly EventPresenter $presenter,
    ) {}

    public function index(Request $request): Response
    {
        $events = $this->filteredQuery($request)
            ->limit(6)
            ->get()
            ->map(fn (Event $event) => $this->presenter->present($event))
            ->values();

        return Inertia::render('Events/Index', [
            'filters' => $this->filtersFromRequest($request),
            'statuses' => $this->statusOptions(),
            'locations' => $this->locations->options(),
            'featuredEvents' => $events,
            'visuals' => [
                [
                    'title' => 'Event Visual 1',
                    'href' => route('events.visual1', $request->query()),
                    'description' => 'Editorial cards with a browsing-first layout for discovery.',
                ],
                [
                    'title' => 'Event Visual 2',
                    'href' => route('events.visual2', $request->query()),
                    'description' => 'A timeline view for comparing where and when events happen.',
                ],
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->browsePayload($request));
    }

    public function show(Event $event): Response
    {
        $event->load([
            'user:id,name',
            'attendees' => fn ($query) => $query->latest()->limit(18),
        ])->loadCount('attendees');

        $presentedEvent = $this->presenter->present($event);
        $relatedEvents = Event::query()
            ->with('user:id,name')
            ->withCount('attendees')
            ->whereKeyNot($event->id)
            ->when(
                $presentedEvent['location']['slug'],
                function (Builder $query, string $slug) {
                    $bounds = $this->locations->bounds($slug);

                    if ($bounds) {
                        $query
                            ->whereBetween('latitude', [$bounds['min_latitude'], $bounds['max_latitude']])
                            ->whereBetween('longitude', [$bounds['min_longitude'], $bounds['max_longitude']]);
                    }
                },
                fn (Builder $query) => $query->where('type', $event->type),
            )
            ->orderBy('created_time')
            ->limit(3)
            ->get()
            ->map(fn (Event $related) => $this->presenter->present($related))
            ->values();

        return Inertia::render('Events/Show', [
            'event' => [
                ...$presentedEvent,
                'attendees' => $event->attendees->map(fn ($attendee) => [
                    'name' => $attendee->name,
                    'email' => $attendee->email,
                    'registered_at' => $attendee->created_at?->toIso8601String(),
                ])->values(),
            ],
            'relatedEvents' => $relatedEvents,
        ]);
    }

    public function visualOne(Request $request): Response
    {
        return Inertia::render('Events/VisualOne', $this->browsePayload($request));
    }

    public function visualTwo(Request $request): Response
    {
        return Inertia::render('Events/VisualTwo', $this->browsePayload($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function browsePayload(Request $request): array
    {
        $events = $this->filteredQuery($request)
            ->simplePaginate(18)
            ->withQueryString();

        $items = collect($events->items())
            ->map(fn (Event $event) => $this->presenter->present($event))
            ->values();
        $filters = $this->filtersFromRequest($request);
        $selectedLocation = $this->locations->find($filters['location']);

        return [
            'events' => $items,
            'filters' => $filters,
            'statuses' => $this->statusOptions(),
            'locations' => $this->locations->options(),
            'pagination' => $this->paginationData($events),
            'summary' => [
                'showing' => $items->count(),
                'location_label' => $selectedLocation ? "{$selectedLocation['city']}, {$selectedLocation['country']}" : 'All destinations',
                'active_filter_count' => collect($filters)->filter(fn ($value) => filled($value))->count(),
            ],
        ];
    }

    private function filteredQuery(Request $request): Builder
    {
        $filters = $this->filtersFromRequest($request);
        $query = Event::query()
            ->with('user:id,name')
            ->withCount('attendees')
            ->whereIn('status', $filters['status'] ? [$filters['status']] : ['published', 'sold_out', 'cancelled']);

        if ($filters['from']) {
            $query->where('created_time', '>=', CarbonImmutable::parse($filters['from'], 'UTC')->startOfDay()->timestamp);
        }

        if ($filters['to']) {
            $query->where('created_time', '<=', CarbonImmutable::parse($filters['to'], 'UTC')->endOfDay()->timestamp);
        }

        if ($bounds = $this->locations->bounds($filters['location'])) {
            $query
                ->whereBetween('latitude', [$bounds['min_latitude'], $bounds['max_latitude']])
                ->whereBetween('longitude', [$bounds['min_longitude'], $bounds['max_longitude']]);
        }

        return $query
            ->orderBy('created_time')
            ->orderBy('id');
    }

    /**
     * @return array{status: string|null, location: string|null, from: string|null, to: string|null}
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'status' => $request->string('status')->toString() ?: null,
            'location' => $request->string('location')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return [
            ['value' => 'published', 'label' => 'Published'],
            ['value' => 'sold_out', 'label' => 'Sold out'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
            ['value' => 'draft', 'label' => 'Draft'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paginationData(Paginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'has_more_pages' => $paginator->hasMorePages(),
            'next_page_url' => $paginator->nextPageUrl(),
            'previous_page_url' => $paginator->previousPageUrl(),
        ];
    }
}

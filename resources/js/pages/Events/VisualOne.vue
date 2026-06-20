<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatLocalTime, formatViewerTime, statusVariant } from '@/lib/events';
import type { EventCardData, EventFilters, EventOption, EventPagination, EventSummary } from '@/types';

const props = defineProps<{
    events: EventCardData[];
    filters: EventFilters;
    statuses: EventOption[];
    locations: EventOption[];
    pagination: EventPagination;
    summary: EventSummary;
}>();

const form = reactive({
    status: props.filters.status ?? '',
    location: props.filters.location ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

function queryPayload() {
    return {
        ...(form.status ? { status: form.status } : {}),
        ...(form.location ? { location: form.location } : {}),
        ...(form.from ? { from: form.from } : {}),
        ...(form.to ? { to: form.to } : {}),
    };
}

function applyFilters() {
    router.get('/events-visual-1', queryPayload(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

const featuredEvent = computed(() => props.events[0] ?? null);
const supportingEvents = computed(() => props.events.slice(1));
</script>

<template>
    <Head title="Event Visual 1" />

    <div class="min-h-full bg-[#f4f8f5] text-slate-950">
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.16),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(20,184,166,0.12),_transparent_24%)]" />

            <div class="relative mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <Link href="/events" class="text-sm text-slate-600 transition hover:text-slate-950">Overview</Link>
                    <span class="text-slate-400">/</span>
                    <span class="text-sm font-medium text-emerald-800">Visual 1</span>
                </div>

                <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-800">Editorial Browse</p>
                        <h1 class="max-w-4xl text-3xl font-semibold tracking-tight sm:text-4xl">
                            Discover events through bold posters and quick comparisons.
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-slate-700 sm:text-base">
                            This layout leans into artwork, spotlighting each event as a destination while still keeping
                            time, city, and registration context close at hand.
                        </p>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-emerald-900/10 bg-white p-3 shadow-sm">
                                <p class="text-sm text-slate-500">Showing now</p>
                                <p class="mt-1 text-xl font-semibold">{{ summary.showing }}</p>
                            </div>
                            <div class="rounded-lg border border-emerald-900/10 bg-white p-3 shadow-sm">
                                <p class="text-sm text-slate-500">Location focus</p>
                                <p class="mt-1 text-base font-semibold">{{ summary.location_label }}</p>
                            </div>
                            <div class="rounded-lg border border-emerald-900/10 bg-white p-3 shadow-sm">
                                <p class="text-sm text-slate-500">Active filters</p>
                                <p class="mt-1 text-xl font-semibold">{{ summary.active_filter_count }}</p>
                            </div>
                        </div>
                    </div>

                    <form
                        class="rounded-xl border border-emerald-900/10 bg-white p-4 shadow-sm"
                        @submit.prevent="applyFilters"
                    >
                        <div class="mb-4">
                            <p class="text-sm font-medium text-slate-950">Refine the collection</p>
                            <p class="text-sm text-slate-600">Filter by destination, date, and event state.</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">Location</span>
                                <select
                                    v-model="form.location"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm text-slate-950"
                                >
                                    <option value="">All destinations</option>
                                    <option v-for="location in locations" :key="location.value" :value="location.value">
                                        {{ location.label }}
                                    </option>
                                </select>
                            </label>

                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">Status</span>
                                <select
                                    v-model="form.status"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm text-slate-950"
                                >
                                    <option value="">All public statuses</option>
                                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </label>

                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">From</span>
                                <input
                                    v-model="form.from"
                                    type="date"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm text-slate-950"
                                />
                            </label>

                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">To</span>
                                <input
                                    v-model="form.to"
                                    type="date"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm text-slate-950"
                                />
                            </label>

                            <div class="sm:col-span-2 flex flex-wrap gap-3 pt-2">
                                <Button type="submit" class="rounded-lg bg-[#17433b] text-white hover:bg-[#21584d]">
                                    Apply filters
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    class="rounded-lg border border-emerald-900/10 bg-white text-slate-800 hover:bg-emerald-50"
                                    @click="router.get('/events-visual-1')"
                                >
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
            <div v-if="featuredEvent" class="mb-6 overflow-hidden rounded-xl border border-emerald-900/10 bg-white shadow-lg">
                <div class="grid lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="relative min-h-[18rem]">
                        <img
                            :src="featuredEvent.primary_image?.src ?? featuredEvent.images[0]?.src"
                            :alt="featuredEvent.primary_image?.alt ?? featuredEvent.title"
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/45 to-transparent" />
                        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                            <div class="mb-3 flex flex-wrap items-center gap-3">
                                <Badge :variant="statusVariant(featuredEvent.status)">
                                    {{ featuredEvent.status.replace('_', ' ') }}
                                </Badge>
                                <span class="text-xs uppercase tracking-[0.18em] text-emerald-100">{{ featuredEvent.type_label }}</span>
                            </div>
                            <h2 class="max-w-3xl text-2xl font-semibold sm:text-3xl">{{ featuredEvent.title }}</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-200">
                                {{ featuredEvent.description }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 p-5 sm:p-6">
                        <div class="rounded-lg border border-emerald-900/10 bg-[#f6fbf8] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Event context</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-700">
                                <p><span class="text-slate-500">Where</span><br>{{ featuredEvent.location.label }}</p>
                                <p><span class="text-slate-500">Local time</span><br>{{ formatLocalTime(featuredEvent.schedule) }}</p>
                                <p><span class="text-slate-500">Your time</span><br>{{ formatViewerTime(featuredEvent.schedule) }}</p>
                                <p><span class="text-slate-500">Attendance</span><br>{{ featuredEvent.attendee_count }} on the list</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <img
                                v-for="image in featuredEvent.images"
                                :key="image.src"
                                :src="image.src"
                                :alt="image.alt"
                                class="h-20 rounded-lg border border-emerald-900/10 object-cover"
                            />
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Link
                                :href="`/events/${featuredEvent.id}#attendee-registration`"
                                class="inline-flex rounded-lg bg-[#17433b] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#21584d]"
                            >
                                View details & register
                            </Link>
                            <Link
                                href="/events-visual-2"
                                class="inline-flex rounded-lg border border-emerald-900/10 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-emerald-50"
                            >
                                Compare in timeline
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="(event, index) in supportingEvents"
                    :key="event.id"
                    class="animate-in slide-in-from-bottom-4 fade-in overflow-hidden rounded-xl border border-emerald-900/10 bg-white transition duration-700 hover:-translate-y-1 hover:shadow-lg"
                    :style="{ animationDelay: `${index * 70}ms` }"
                >
                    <img
                        :src="event.primary_image?.src ?? event.images[0]?.src"
                        :alt="event.primary_image?.alt ?? event.title"
                        class="h-44 w-full object-cover"
                    />

                    <div class="space-y-4 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <Badge :variant="statusVariant(event.status)">{{ event.status.replace('_', ' ') }}</Badge>
                            <span class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ event.type_label }}</span>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold text-slate-950">{{ event.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ event.excerpt }}</p>
                        </div>

                        <div class="space-y-2 text-sm text-slate-700">
                            <p>{{ event.location.city }}, {{ event.location.country }}</p>
                            <p>{{ formatLocalTime(event.schedule) }}</p>
                            <p>{{ event.pricing.label }} - {{ event.attendee_count }} attending</p>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <img
                                v-for="image in event.images"
                                :key="image.src"
                                :src="image.src"
                                :alt="image.alt"
                                class="h-14 rounded-lg border border-emerald-900/10 object-cover"
                            />
                        </div>

                        <Link :href="`/events/${event.id}#attendee-registration`" class="inline-flex text-sm font-medium text-emerald-800 hover:text-emerald-950">
                            Explore & register
                        </Link>
                    </div>
                </article>
            </div>

            <div v-if="events.length === 0" class="rounded-xl border border-dashed border-emerald-900/15 bg-white p-6 text-center text-slate-600">
                No events match the current filters yet.
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-emerald-900/10 bg-white p-4">
                <p class="text-sm text-slate-600">Page {{ pagination.current_page }} in the rolling event stream.</p>
                <div class="flex gap-3">
                    <Link
                        v-if="pagination.previous_page_url"
                        :href="pagination.previous_page_url"
                        class="inline-flex rounded-lg border border-emerald-900/10 px-4 py-2 text-sm font-medium text-slate-800 transition hover:bg-emerald-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="pagination.next_page_url"
                        :href="pagination.next_page_url"
                        class="inline-flex rounded-lg bg-[#17433b] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#21584d]"
                    >
                        More events
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>

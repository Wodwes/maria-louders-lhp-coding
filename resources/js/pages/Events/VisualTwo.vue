<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatLocalTime, formatViewerTime, groupEventsByDay, statusVariant } from '@/lib/events';
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
    router.get('/events-visual-2', queryPayload(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

const groupedEvents = computed(() => groupEventsByDay(props.events));
const topCities = computed(() => Array.from(new Set(props.events.map((event) => event.location.city))).slice(0, 4));
</script>

<template>
    <Head title="Event Visual 2" />

    <div class="min-h-full bg-[#f4f8f5] text-slate-950">
        <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3 text-sm text-slate-600">
                <Link href="/events" class="transition hover:text-slate-950">Overview</Link>
                <span>/</span>
                <span class="font-medium text-slate-900">Visual 2</span>
            </div>

            <div class="grid gap-5 lg:grid-cols-[0.34fr_0.66fr]">
                <aside class="space-y-4 lg:sticky lg:top-5 lg:self-start">
                    <div class="rounded-xl border border-emerald-900/10 bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-800">Timeline Browse</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight">
                            A day-by-day reading of the event stream.
                        </h1>
                        <p class="mt-3 text-sm leading-6 text-slate-700">
                            This view puts schedule and place first, making it easier to compare what is happening
                            across cities on the same date.
                        </p>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-lg bg-[#17433b] p-3 text-white">
                                <p class="text-sm text-slate-300">Events on this page</p>
                                <p class="mt-1 text-2xl font-semibold">{{ summary.showing }}</p>
                            </div>
                            <div class="rounded-lg border border-emerald-900/10 bg-[#f6fbf8] p-3">
                                <p class="text-sm text-slate-500">Current destination focus</p>
                                <p class="mt-1 text-base font-semibold">{{ summary.location_label }}</p>
                            </div>
                        </div>
                    </div>

                    <form class="rounded-xl border border-emerald-900/10 bg-white p-5 shadow-sm" @submit.prevent="applyFilters">
                        <div class="mb-4">
                            <p class="text-base font-semibold">Refine the agenda</p>
                            <p class="text-sm text-slate-600">Filters update the full timeline below.</p>
                        </div>

                        <div class="space-y-3">
                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">Location</span>
                                <select
                                    v-model="form.location"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm"
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
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm"
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
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm"
                                />
                            </label>

                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">To</span>
                                <input
                                    v-model="form.to"
                                    type="date"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm"
                                />
                            </label>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <Button type="submit" class="rounded-lg bg-[#17433b] text-white hover:bg-[#21584d]">
                                Update timeline
                            </Button>
                            <Button type="button" variant="secondary" class="rounded-lg" @click="router.get('/events-visual-2')">
                                Reset
                            </Button>
                        </div>
                    </form>

                    <div class="rounded-xl border border-emerald-900/10 bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Cities in this slice</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="city in topCities"
                                :key="city"
                                class="rounded-full border border-slate-300 bg-slate-50 px-3 py-1 text-sm text-slate-700"
                            >
                                {{ city }}
                            </span>
                        </div>
                    </div>
                </aside>

                <div class="space-y-6">
                    <section
                        v-for="(group, groupIndex) in groupedEvents"
                        :key="group.key"
                        class="rounded-xl border border-emerald-900/10 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Date block</p>
                                <h2 class="mt-1 text-xl font-semibold">{{ group.label }}</h2>
                            </div>
                            <div class="rounded-full bg-[#17433b] px-4 py-2 text-sm font-medium text-white">
                                {{ group.items.length }} events
                            </div>
                        </div>

                        <div class="space-y-4">
                            <article
                                v-for="(event, index) in group.items"
                                :key="event.id"
                                class="animate-in slide-in-from-right-4 fade-in grid gap-4 rounded-lg border border-emerald-900/10 bg-[#f6fbf8] p-4 duration-700 md:grid-cols-[160px_1fr_auto]"
                                :style="{ animationDelay: `${(groupIndex * 3 + index) * 55}ms` }"
                            >
                                <img
                                    :src="event.primary_image?.src ?? event.images[0]?.src"
                                    :alt="event.primary_image?.alt ?? event.title"
                                    class="h-32 w-full rounded-lg object-cover"
                                />

                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <Badge :variant="statusVariant(event.status)">{{ event.status.replace('_', ' ') }}</Badge>
                                        <span class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ event.type_label }}</span>
                                    </div>

                                    <div>
                                        <h3 class="text-xl font-semibold">{{ event.title }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ event.excerpt }}</p>
                                    </div>

                                    <div class="grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
                                        <p><span class="text-slate-500">Location</span><br>{{ event.location.label }}</p>
                                        <p><span class="text-slate-500">Ticketing</span><br>{{ event.pricing.label }}</p>
                                        <p><span class="text-slate-500">Local</span><br>{{ formatLocalTime(event.schedule) }}</p>
                                        <p><span class="text-slate-500">Your time</span><br>{{ formatViewerTime(event.schedule) }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start justify-between gap-3 md:items-end">
                                    <div class="rounded-lg bg-[#17433b] px-4 py-3 text-white">
                                        <p class="text-xs uppercase tracking-[0.16em] text-emerald-100">Attendees</p>
                                        <p class="mt-1 text-xl font-semibold">{{ event.attendee_count }}</p>
                                    </div>

                                    <Link
                                        :href="`/events/${event.id}#attendee-registration`"
                                        class="inline-flex rounded-lg border border-emerald-900/15 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-[#17433b] hover:text-white"
                                    >
                                        Open & register
                                    </Link>
                                </div>
                            </article>
                        </div>
                    </section>

                    <div v-if="events.length === 0" class="rounded-xl border border-dashed border-emerald-900/15 bg-white p-6 text-center text-slate-600">
                        No events matched the current timeline filters.
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-emerald-900/10 bg-white p-4 shadow-sm">
                        <p class="text-sm text-slate-600">Page {{ pagination.current_page }} in the filtered stream.</p>
                        <div class="flex gap-3">
                            <Link
                                v-if="pagination.previous_page_url"
                                :href="pagination.previous_page_url"
                                class="inline-flex rounded-lg border border-emerald-900/15 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-emerald-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="pagination.next_page_url"
                                :href="pagination.next_page_url"
                                class="inline-flex rounded-lg bg-[#17433b] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#21584d]"
                            >
                                Next block
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

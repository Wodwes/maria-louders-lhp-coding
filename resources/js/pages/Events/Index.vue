<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatLocalTime, statusVariant } from '@/lib/events';
import type { EventCardData, EventFilters, EventOption } from '@/types';

const props = defineProps<{
    filters: EventFilters;
    statuses: EventOption[];
    locations: EventOption[];
    featuredEvents: EventCardData[];
    visuals: Array<{ title: string; href: string; description: string }>;
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
    router.get('/events', queryPayload(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

const queryString = computed(() => new URLSearchParams(queryPayload() as Record<string, string>).toString());
const visualHref = (path: string) => (queryString.value ? `${path}?${queryString.value}` : path);
</script>

<template>
    <Head title="Event Overview" />

    <div class="min-h-full bg-[#f4f8f5] text-slate-950">
        <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-emerald-900/10 bg-[#17433b] p-5 text-white shadow-lg sm:p-6">
                <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="space-y-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-emerald-100/80">Assessment Overview</p>
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight sm:text-4xl">
                            Two distinct ways to browse a global event dataset.
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-slate-200/90 sm:text-base">
                            The event catalogue now supports local artwork, city-aware locations, timezone-friendly
                            schedules, and public attendance registration with confirmation and reminder emails.
                        </p>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-white/10 bg-white/10 p-3">
                                <p class="text-sm text-emerald-50/80">Visual directions</p>
                                <p class="mt-1 text-xl font-semibold">2</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-white/10 p-3">
                                <p class="text-sm text-emerald-50/80">Local images per event</p>
                                <p class="mt-1 text-xl font-semibold">3</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-white/10 p-3">
                                <p class="text-sm text-emerald-50/80">Reminder windows</p>
                                <p class="mt-1 text-xl font-semibold">3d / 24h</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-[#f6fbf8] p-4 text-slate-950">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-950">Filter the experience</p>
                                <p class="text-sm text-slate-600">These filters carry through to both visual pages.</p>
                            </div>
                            <Badge variant="outline" class="border-emerald-700/30 bg-emerald-50 text-emerald-900">
                                Shared controls
                            </Badge>
                        </div>

                        <form class="grid gap-3 sm:grid-cols-2" @submit.prevent="applyFilters">
                            <label class="space-y-2 text-sm">
                                <span class="text-slate-700">Location</span>
                                <select
                                    v-model="form.location"
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm text-slate-950 outline-none ring-0"
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
                                    class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm text-slate-950 outline-none ring-0"
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

                            <div class="sm:col-span-2 flex flex-wrap gap-3 pt-1">
                                <Button type="submit" class="rounded-lg bg-[#17433b] text-white hover:bg-[#21584d]">
                                    Update preview
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    class="rounded-lg border border-emerald-900/10 bg-white text-slate-800 hover:bg-emerald-50"
                                    @click="router.get('/events')"
                                >
                                    Reset
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Launch points</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-950">Choose a visual direction</h2>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <Link
                            :href="visualHref('/events-visual-1')"
                            class="group block rounded-lg border border-emerald-900/10 bg-[#17433b] p-4 text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <p class="text-xs uppercase tracking-[0.18em] text-emerald-100">Visual 1</p>
                            <h3 class="mt-2 text-xl font-semibold">Editorial discovery grid</h3>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">
                                Large posters, featured moments, and collection-style browsing for discovery-first flows.
                            </p>
                        </Link>

                        <Link
                            :href="visualHref('/events-visual-2')"
                            class="group block rounded-lg border border-emerald-900/10 bg-[#e9f4ee] p-4 text-slate-950 transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-600">Visual 2</p>
                            <h3 class="mt-2 text-xl font-semibold">Schedule-led timeline</h3>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-700">
                                A day-by-day agenda view that makes temporal comparison and scanning much faster.
                            </p>
                        </Link>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Preview slice</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-950">Upcoming filtered events</h2>
                        </div>
                        <Link :href="visualHref('/events-visual-1')" class="text-sm font-medium text-slate-600 hover:text-slate-950">
                            Browse all
                        </Link>
                    </div>

                    <div v-if="featuredEvents.length > 0" class="grid gap-4 sm:grid-cols-2">
                        <article
                            v-for="event in featuredEvents"
                            :key="event.id"
                            class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                        >
                            <img
                                :src="event.primary_image?.src ?? event.images[0]?.src"
                                :alt="event.primary_image?.alt ?? event.title"
                                class="h-32 w-full object-cover"
                            />

                            <div class="space-y-3 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <Badge :variant="statusVariant(event.status)">{{ event.status.replace('_', ' ') }}</Badge>
                                    <span class="text-xs uppercase tracking-[0.16em] text-slate-500">{{ event.type_label }}</span>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-slate-950">{{ event.title }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ event.excerpt }}</p>
                                </div>

                                <div class="space-y-1 text-sm text-slate-600">
                                    <p>{{ event.location.city }}, {{ event.location.country }}</p>
                                    <p>{{ formatLocalTime(event.schedule) }}</p>
                                </div>

                                <Link :href="`/events/${event.id}#attendee-registration`" class="inline-flex text-sm font-medium text-slate-950 hover:text-slate-700">
                                    View details & register
                                </Link>
                            </div>
                        </article>
                    </div>

                    <div
                        v-else
                        class="rounded-lg border border-dashed border-emerald-900/15 bg-[#f6fbf8] p-6 text-center"
                    >
                        <p class="text-sm font-semibold text-slate-950">No events match these filters.</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Clear the date/location filters to show the seeded event catalogue and open registration.
                        </p>
                        <Button
                            type="button"
                            class="mt-4 rounded-lg bg-[#17433b] text-white hover:bg-[#21584d]"
                            @click="router.get('/events')"
                        >
                            Clear filters
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CalendarCheck, MailCheck, MapPinned, Sparkles } from '@lucide/vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const highlights = [
    {
        title: 'Two visual browse pages',
        description: 'Poster grid and schedule timeline experiences are available from the sidebar.',
        icon: Sparkles,
    },
    {
        title: 'Date and location filters',
        description: 'Shared controls filter the event dataset by city, status, and date range.',
        icon: MapPinned,
    },
    {
        title: 'Attendee registration',
        description: 'Event details support validated attendee signup with duplicate email protection.',
        icon: CalendarCheck,
    },
    {
        title: 'Confirmation and reminders',
        description: 'Mailables are wired for registration confirmation and scheduled reminder windows.',
        icon: MailCheck,
    },
];

const quickLinks = [
    {
        title: 'Event Overview',
        description: 'Start here for the assessment summary and shared filters.',
        href: '/events',
    },
    {
        title: 'Poster Grid',
        description: 'A visual card-led browsing direction with local artwork.',
        href: '/events-visual-1',
    },
    {
        title: 'Schedule Timeline',
        description: 'A chronological browsing direction focused on event timing.',
        href: '/events-visual-2',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-full bg-[#f4f8f5] px-4 py-5 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-5">
            <section class="overflow-hidden rounded-2xl border border-emerald-900/10 bg-[#17433b] text-white shadow-lg shadow-emerald-950/10">
                <div class="grid gap-6 p-6 lg:grid-cols-[1.35fr_0.65fr] lg:p-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-100/70">
                            Assessment dashboard
                        </p>
                        <h1 class="mt-3 max-w-3xl text-3xl font-semibold tracking-tight sm:text-4xl">
                            Event Visuals is ready to review.
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/85 sm:text-base">
                            This hub summarizes the Laravel/Vue implementation and gives reviewers quick access to the two browse directions, event details, attendee signup, and email workflow.
                        </p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-white/10 p-4">
                        <p class="text-sm font-semibold">Manual QA focus</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-emerald-50/85">
                            <li>Check filters on both visual pages.</li>
                            <li>Open an event and submit attendee registration.</li>
                            <li>Run the reminder command for scheduled email coverage.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <Link
                    v-for="item in quickLinks"
                    :key="item.title"
                    :href="item.href"
                    class="group rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/8"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-emerald-950">{{ item.title }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ item.description }}</p>
                        </div>
                        <ArrowRight class="mt-1 size-5 text-emerald-700 transition group-hover:translate-x-1" />
                    </div>
                </Link>
            </section>

            <section class="rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <article
                        v-for="item in highlights"
                        :key="item.title"
                        class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div class="flex gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800">
                                <component :is="item.icon" class="size-5" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-emerald-950">{{ item.title }}</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ item.description }}</p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </div>
</template>

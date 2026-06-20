<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { formatLocalTime, formatViewerTime, statusVariant } from '@/lib/events';
import type { EventCardData, EventDetail } from '@/types';

const props = defineProps<{
    event: EventDetail;
    relatedEvents: EventCardData[];
}>();

const form = useForm({
    name: '',
    email: '',
});

const registrationError = computed(() => (form.errors as Record<string, string | undefined>).registration);

function submitRegistration() {
    form.post(`/events/${props.event.id}/attendees`, {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'email'),
    });
}
</script>

<template>
    <Head :title="event.title" />

    <div class="min-h-full bg-[#f4f8f5]">
        <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <Link href="/events-visual-1" class="inline-flex text-sm text-slate-600 transition hover:text-slate-950">
                Back to visual browse
            </Link>

            <div class="mt-4 overflow-hidden rounded-xl border border-emerald-900/10 bg-white text-white shadow-lg">
                <div class="grid lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="relative min-h-[18rem]">
                        <img
                            :src="event.primary_image?.src ?? event.images[0]?.src"
                            :alt="event.primary_image?.alt ?? event.title"
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/45 to-transparent" />
                        <div class="absolute inset-x-0 bottom-0 space-y-3 p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-3">
                                <Badge :variant="statusVariant(event.status)">{{ event.status.replace('_', ' ') }}</Badge>
                                <span class="text-xs uppercase tracking-[0.18em] text-emerald-100">{{ event.type_label }}</span>
                            </div>
                            <h1 class="max-w-3xl text-3xl font-semibold tracking-tight sm:text-4xl">
                                {{ event.title }}
                            </h1>
                            <p class="max-w-2xl text-sm leading-6 text-slate-200">
                                {{ event.description }}
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a
                                    href="#attendee-registration"
                                    class="inline-flex rounded-lg bg-[#f7cf72] px-4 py-2 text-sm font-semibold text-[#102820] transition hover:bg-[#ffe08d]"
                                >
                                    Register attendee
                                </a>
                                <span
                                    class="inline-flex rounded-lg border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white"
                                >
                                    {{ event.is_registration_open ? 'Registration open' : 'Registration closed' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 p-5 text-slate-950 sm:p-6">
                        <div
                            id="attendee-registration"
                            class="scroll-mt-20 rounded-lg border border-emerald-900/10 bg-white p-4 shadow-sm"
                        >
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Register attendee</p>
                                    <h2 class="mt-1 text-xl font-semibold text-slate-950">Join this event</h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Add a name and email to create an attendee record.
                                    </p>
                                </div>
                                <Badge :variant="event.is_registration_open ? 'secondary' : 'destructive'">
                                    {{ event.is_registration_open ? 'Open' : 'Closed' }}
                                </Badge>
                            </div>

                            <form class="grid gap-3" @submit.prevent="submitRegistration">
                                <label class="space-y-2 text-sm">
                                    <span class="text-slate-700">Name</span>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm"
                                        placeholder="Ada Lovelace"
                                    />
                                    <InputError :message="form.errors.name" />
                                </label>

                                <label class="space-y-2 text-sm">
                                    <span class="text-slate-700">Email</span>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="h-10 w-full rounded-lg border border-emerald-900/15 bg-white px-3 text-sm"
                                        placeholder="ada@example.com"
                                    />
                                    <InputError :message="form.errors.email" />
                                </label>

                                <InputError :message="registrationError" />

                                <Button
                                    type="submit"
                                    class="w-full rounded-lg bg-[#17433b] text-white hover:bg-[#21584d]"
                                    :disabled="form.processing || !event.is_registration_open"
                                >
                                    {{ form.processing ? 'Registering...' : 'Join attendee list' }}
                                </Button>

                                <p class="text-xs leading-5 text-slate-500">
                                    Confirmation email sends immediately. Reminders are scheduled for 3 days and 24 hours before the event.
                                </p>
                            </form>
                        </div>

                        <div class="rounded-lg border border-emerald-900/10 bg-[#f6fbf8] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Event details</p>
                            <div class="mt-3 grid gap-3 text-sm text-slate-700">
                                <p><span class="text-slate-500">Location</span><br>{{ event.location.label }}</p>
                                <p><span class="text-slate-500">Local time</span><br>{{ formatLocalTime(event.schedule) }}</p>
                                <p><span class="text-slate-500">Your time</span><br>{{ formatViewerTime(event.schedule) }}</p>
                                <p><span class="text-slate-500">Organizer</span><br>{{ event.organizer }}</p>
                                <p><span class="text-slate-500">Pricing</span><br>{{ event.pricing.label }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <img
                                v-for="image in event.images"
                                :key="image.src"
                                :src="image.src"
                                :alt="image.alt"
                                class="h-20 rounded-lg border border-emerald-900/10 object-cover"
                            />
                        </div>

                        <div class="rounded-lg border border-emerald-900/10 bg-[#17433b] p-4 text-sm text-white">
                            <p class="text-xs uppercase tracking-[0.18em] text-emerald-100/80">Attendance pulse</p>
                            <p class="mt-1 text-2xl font-semibold">{{ event.attendee_count }}</p>
                            <p class="mt-1 text-emerald-100/80">People already on the list</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-[0.78fr_0.22fr]">
                <div class="space-y-5">
                    <div class="rounded-xl border border-emerald-900/10 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Attendees</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-950">Current list</h2>
                        </div>

                        <div v-if="event.attendees.length > 0" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <div
                                v-for="attendee in event.attendees"
                                :key="`${attendee.email}-${attendee.registered_at}`"
                                class="rounded-lg border border-emerald-900/10 bg-[#f6fbf8] p-4"
                            >
                                <p class="font-medium text-slate-950">{{ attendee.name }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ attendee.email }}</p>
                                <p class="mt-3 text-xs uppercase tracking-[0.22em] text-slate-500">
                                    Joined {{ attendee.registered_at ? new Date(attendee.registered_at).toLocaleDateString() : 'recently' }}
                                </p>
                            </div>
                        </div>

                        <p v-else class="text-sm text-slate-600">No one has registered yet. Be the first attendee.</p>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-xl border border-emerald-900/10 bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Nearby picks</p>
                        <div class="mt-4 space-y-4">
                            <Link
                                v-for="related in relatedEvents"
                                :key="related.id"
                                :href="`/events/${related.id}`"
                                class="block rounded-lg border border-emerald-900/10 bg-[#f6fbf8] p-3 transition hover:-translate-y-0.5 hover:bg-emerald-50"
                            >
                                <img
                                    :src="related.primary_image?.src ?? related.images[0]?.src"
                                    :alt="related.primary_image?.alt ?? related.title"
                                    class="h-24 w-full rounded-lg object-cover"
                                />
                                <p class="mt-3 font-medium text-slate-950">{{ related.title }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ related.location.city }}, {{ related.location.country }}</p>
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</template>

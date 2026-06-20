import type { EventCardData } from '@/types/events';

export function statusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'published':
            return 'default';
        case 'sold_out':
            return 'secondary';
        case 'cancelled':
            return 'destructive';
        default:
            return 'outline';
    }
}

export function formatLocalTime(schedule: EventCardData['schedule']): string {
    if (!schedule.starts_at) {
        return 'Schedule to be announced';
    }

    return new Intl.DateTimeFormat('en', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: schedule.timezone,
    }).format(new Date(schedule.starts_at));
}

export function formatViewerTime(schedule: EventCardData['schedule']): string {
    if (!schedule.starts_at) {
        return 'Time pending';
    }

    return new Intl.DateTimeFormat('en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(schedule.starts_at));
}

export function groupEventsByDay(events: EventCardData[]): Array<{
    key: string;
    label: string;
    items: EventCardData[];
}> {
    const groups = new Map<string, { key: string; label: string; items: EventCardData[] }>();

    for (const event of events) {
        const key = event.schedule.date_label ?? 'Flexible schedule';
        const existing = groups.get(key);

        if (existing) {
            existing.items.push(event);
            continue;
        }

        groups.set(key, {
            key,
            label: key,
            items: [event],
        });
    }

    return Array.from(groups.values());
}

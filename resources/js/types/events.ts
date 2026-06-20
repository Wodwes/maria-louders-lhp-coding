export interface EventOption {
    value: string;
    label: string;
}

export interface EventFilters {
    status: string | null;
    location: string | null;
    from: string | null;
    to: string | null;
}

export interface EventImage {
    src: string;
    alt: string;
}

export interface EventLocation {
    slug: string | null;
    label: string;
    city: string;
    country: string;
    timezone: string;
    latitude: number | null;
    longitude: number | null;
}

export interface EventSchedule {
    starts_at: string | null;
    ends_at: string | null;
    timezone: string;
    local_label: string | null;
    date_label: string | null;
    time_label: string | null;
    viewer_hint: string | null;
}

export interface EventPricing {
    currency: string;
    min_price: number | null;
    label: string;
}

export interface EventCardData {
    id: string;
    type: string;
    type_label: string;
    status: string;
    title: string;
    description: string;
    excerpt: string;
    organizer: string;
    venue: string;
    location: EventLocation;
    schedule: EventSchedule;
    pricing: EventPricing;
    capacity: number | null;
    images: EventImage[];
    primary_image: EventImage | null;
    attendee_count: number;
    is_registration_open: boolean;
}

export interface EventAttendee {
    name: string;
    email: string;
    registered_at: string | null;
}

export interface EventDetail extends EventCardData {
    attendees: EventAttendee[];
}

export interface EventPagination {
    current_page: number;
    has_more_pages: boolean;
    next_page_url: string | null;
    previous_page_url: string | null;
}

export interface EventSummary {
    showing: number;
    location_label: string;
    active_filter_count: number;
}

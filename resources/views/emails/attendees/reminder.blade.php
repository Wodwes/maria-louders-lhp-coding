<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
    <p>Hi {{ $attendee->name }},</p>

    <p>{{ $event['title'] }} starts in {{ $leadTimeLabel }}.</p>

    <p>
        When: {{ $event['schedule']['local_label'] ?? 'Schedule to be announced' }}<br>
        Where: {{ $event['location']['label'] ?? 'Location to be announced' }}
    </p>

    <p>Looking forward to seeing you there.</p>
</div>

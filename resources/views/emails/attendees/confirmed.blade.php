<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
    <p>Hi {{ $attendee->name }},</p>

    <p>You're confirmed for <strong>{{ $event['title'] }}</strong>.</p>

    <p>
        When: {{ $event['schedule']['local_label'] ?? 'Schedule to be announced' }}<br>
        Where: {{ $event['location']['label'] ?? 'Location to be announced' }}
    </p>

    <p>We'll send you another reminder as the event gets closer.</p>
</div>

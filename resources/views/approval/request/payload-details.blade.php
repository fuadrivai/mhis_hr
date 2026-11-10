@foreach ($orderedPayload as $field => $value)
    <div class="approval-info-row">
        <dt>{{ $schemaLabels->get($field, ucfirst(str_replace('_', ' ', $field))) }}</dt>
        <dd>{{ is_array($value) ? implode(', ', $value) : (in_array($field, ['start_time', 'end_time'], true) && !$value ? 'Not provided' : $value) }}</dd>
    </div>
@endforeach
@foreach ($timeFields as $field)
    @if (!array_key_exists($field['name'], $payload))
        <div class="approval-info-row">
            <dt>{{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}</dt>
            <dd>Not provided</dd>
        </div>
    @endif
@endforeach
@if ($hasTimeFields)
    <div class="approval-info-row">
        <dt>Duration</dt>
        <dd>{{ $timeDuration === 'invalid' ? 'Invalid time range' : ($timeDuration ?? 'Not available') }}</dd>
    </div>
@endif
@if ((!$hasTimeFields || (!empty($payload['end_date']) && ($payload['start_date'] ?? $payload['date'] ?? null) !== $payload['end_date'])) && $scheduledDays !== null)
    <div class="approval-info-row">
        <dt>Scheduled days</dt>
        <dd>{{ $scheduledDays === 'invalid' ? 'Invalid date range' : $scheduledDays . ' day(s)' }}</dd>
    </div>
@endif

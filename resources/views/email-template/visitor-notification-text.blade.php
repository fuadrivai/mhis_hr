{{ strtoupper($title) }}

Assalamu'alaikum {{ $recipientName }},
{{ $intro }}

@foreach ($details as $label => $value)
{{ $label }}: {{ $value }}
@endforeach
@if (!empty($qrPng))

Please show the QR code in the HTML email or your visit code at the security desk.
@endif
@if (!empty($actionLabel))

{{ $actionLabel }}: {{ $actionUrl }}
@if (!empty($actionNote))
{{ $actionNote }}
@endif
@endif

Warm regards,
Mutiara Harapan Islamic School
Home of The Champions

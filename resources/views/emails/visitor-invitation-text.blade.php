VISITOR INVITATION

Hello {{ $visit->visitor->full_name }}, you are invited to visit us.
Visit date: {{ $visit->visit_date->format('d M Y') }}
Visit time: {{ $visit->expected_arrival_at?->format('H:i') ?? '—' }} - {{ $visit->expected_departure_at?->format('H:i') ?? '—' }}
Meeting location: {{ $visit->branch->name }}
Host: {{ $visit->host->user?->name ?? 'School contact' }}
Purpose: {{ $visit->purpose->name }}
Visit code: {{ $visit->visit_code }}

Please show the QR code in the HTML email or your visit code at the security desk.
Mutiara Harapan Islamic School
Home of The Champions

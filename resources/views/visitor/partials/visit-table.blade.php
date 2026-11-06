<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead><tr><th>Visit code</th><th>Visitor</th><th>Branch / Host</th><th>Category / Purpose</th><th>Date</th><th>Check-in / Check-out</th><th>Status</th></tr></thead>
        <tbody>
            @forelse ($visits as $visit)
                <tr>
                    <td>{{ $visit->visit_code }}</td>
                    <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->visitor->company }}</small></td>
                    <td>{{ $visit->branch->name }}<br>{{ $visit->host->user?->name ?? '—' }}</td>
                    <td>{{ $visit->category->name }}<br>{{ $visit->purpose->name }}</td>
                    <td>{{ $visit->visit_date->format('d M Y') }}</td>
                    <td>{{ $visit->check_in_at?->format('d M H:i') ?? '—' }}<br>{{ $visit->check_out_at?->format('d M H:i') ?? '—' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($visit->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No visitor records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

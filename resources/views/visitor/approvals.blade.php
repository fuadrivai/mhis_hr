@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="x_panel">
            <div class="x_title"><h2>Pending Visitor Approvals</h2><div class="clearfix"></div></div>
            <div class="x_content table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Visitor</th><th>Visit details</th><th>Host</th><th>Notes</th><th>Decision</th></tr></thead>
                    <tbody>
                        @forelse ($approvals as $approval)
                            @php($visit = $approval->visit)
                            <tr>
                                <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->visitor->company }} · {{ $visit->visitor->phone }}</small></td>
                                <td>{{ $visit->branch->name }} · {{ $visit->visit_date->format('d M Y') }}<br>{{ $visit->purpose->name }} / {{ $visit->category->name }}</td>
                                <td>{{ $visit->host->user?->name ?? '—' }}</td>
                                <td>{{ $visit->notes ?: '—' }}@if ($visit->visitor_notes)<br><small>Visitor info: {{ $visit->visitor_notes }}</small>@endif</td>
                                <td>
                                    <form method="POST" action="{{ route('visitor.approval.process', $visit) }}">
                                        @csrf
                                        <input name="notes" class="form-control input-sm" placeholder="Approval notes">
                                        <button class="btn btn-xs btn-success" name="decision" value="approve">Approve</button>
                                        <button class="btn btn-xs btn-danger" name="decision" value="reject" onclick="return confirm('Reject this visit?')">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">There are no pending approvals.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $approvals->links() }}
            </div>
        </div>
    </div>
@endsection

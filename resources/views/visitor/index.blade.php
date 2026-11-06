@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('visitor_qr_token'))
            <div class="alert alert-info">
                <strong>Invitation created: {{ session('visitor_visit_code') }}</strong>
                <p>One-time QR/scanner token. Save it securely; it will not be shown again.</p>
                <img src="{{ session('visitor_qr_image') }}" alt="Visitor QR code" style="width:160px;height:160px;background:#fff;padding:6px">
                <code id="new-visitor-token">{{ session('visitor_qr_token') }}</code>
                <button type="button" class="btn btn-xs btn-default" onclick="navigator.clipboard.writeText(document.getElementById('new-visitor-token').textContent)">Copy token</button>
            </div>
        @endif
        <div class="x_panel">
            <div class="x_title">
                <h2>My Invitations / Visits</h2>
                <ul class="nav navbar-right panel_toolbox">@if (auth()->user()->employee)<li><a class="btn btn-success btn-sm" href="{{ route('visitor.create') }}"><i class="fa fa-plus"></i> New Invitation</a></li>@endif</ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-inline" method="POST" action="{{ route('visitor.search-list') }}" style="margin-bottom:15px">
                    @csrf
                    <input class="form-control" name="search" value="{{ $searchTerm }}" placeholder="Visitor, visit code, badge">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a class="btn btn-default" href="{{ route('visitor.index', ['clear_search' => 1]) }}">Clear search</a>
                </form>
                <form class="form-inline" method="GET" action="{{ route('visitor.index') }}" style="margin-bottom:15px">
                    <select class="form-control" name="status"><option value="">All statuses</option>@foreach ($statuses as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select>
                    <input class="form-control" type="date" name="from" value="{{ request('from') }}" aria-label="From date">
                    <input class="form-control" type="date" name="to" value="{{ request('to') }}" aria-label="To date">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </form>
                <a class="btn btn-default" href="{{ route('visitor.history') }}">Visit History</a>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead><tr><th>Visit</th><th>Visitor</th><th>Branch / Host</th><th>Date / Time</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse ($visits as $visit)
                                <tr>
                                    <td>{{ $visit->visit_code }}<br><small>{{ str_replace('_', ' ', ucfirst($visit->registration_method)) }}</small></td>
                                    <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->visitor->company }}</small></td>
                                    <td>{{ $visit->branch->name }}<br>{{ $visit->host->user?->name ?? '—' }}</td>
                                    <td>{{ $visit->visit_date->format('d M Y') }}<br><small>{{ $visit->expected_arrival_at?->format('H:i') }} – {{ $visit->expected_departure_at?->format('H:i') }}</small></td>
                                    <td>{{ str_replace('_', ' ', ucfirst($visit->status)) }}</td>
                                    <td>
                                        @if ($visit->registered_by === auth()->id() && in_array($visit->status, ['expected', 'pending_confirmation'], true))
                                            <form method="POST" action="{{ route('visitor.cancel', $visit) }}" onsubmit="return confirm('Cancel this visit?')">
                                                @csrf
                                                <input name="reason" class="form-control input-sm" placeholder="Cancellation reason" required maxlength="2000">
                                                <button class="btn btn-xs btn-danger" type="submit">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No visits found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $visits->links() }}
            </div>
        </div>
    </div>
@endsection

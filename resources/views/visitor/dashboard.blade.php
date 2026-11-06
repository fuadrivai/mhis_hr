@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="x_panel">
            <div class="x_title">
                <h2>Visitor Management Dashboard</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    @foreach ([
                        ['Today Visits', $todayCount, 'fa-calendar'],
                        ['Currently Inside', $insideCount, 'fa-sign-in'],
                        ['Expected Today', $expectedCount, 'fa-clock-o'],
                        ['Pending Approvals', $pendingCount, 'fa-check-square-o'],
                    ] as [$label, $count, $icon])
                        <div class="col-md-3 col-sm-6">
                            <div class="x_panel tile">
                                <div class="x_content">
                                    <h4><i class="fa {{ $icon }}"></i> {{ $label }}</h4>
                                    <h2>{{ $count }}</h2>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="x_panel">
                            <div class="x_title"><h2>Currently Inside</h2><div class="clearfix"></div></div>
                            <div class="x_content table-responsive">
                                <table class="table table-striped">
                                    <thead><tr><th>Visitor</th><th>Host</th><th>Branch</th><th>Check-in</th></tr></thead>
                                    <tbody>
                                        @forelse ($currentlyInside as $visit)
                                            <tr>
                                                <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->visitor->company }}</small></td>
                                                <td>{{ $visit->host->user?->name ?? '—' }}</td>
                                                <td>{{ $visit->branch->name }}</td>
                                                <td>{{ $visit->check_in_at?->format('H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">No visitors currently inside.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="x_panel">
                            <div class="x_title"><h2>Upcoming Visitors</h2><div class="clearfix"></div></div>
                            <div class="x_content table-responsive">
                                <table class="table table-striped">
                                    <thead><tr><th>Visitor</th><th>Host</th><th>Arrival</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @forelse ($upcomingVisits as $visit)
                                            <tr>
                                                <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->branch->name }}</small></td>
                                                <td>{{ $visit->host->user?->name ?? '—' }}</td>
                                                <td>{{ $visit->expected_arrival_at?->format('d M, H:i') ?? '—' }}</td>
                                                <td><span class="label label-info">{{ str_replace('_', ' ', ucfirst($visit->status)) }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">No upcoming visitors.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($overstayVisits->isNotEmpty())
                    <div class="x_panel">
                        <div class="x_title"><h2><i class="fa fa-exclamation-triangle text-warning"></i> Overstay Alerts</h2><div class="clearfix"></div></div>
                        <div class="x_content table-responsive">
                            <table class="table table-striped">
                                <thead><tr><th>Visitor</th><th>Host / Branch</th><th>Expected departure</th><th>Status</th></tr></thead>
                                <tbody>
                                    @foreach ($overstayVisits as $visit)
                                        <tr>
                                            <td>{{ $visit->visitor->full_name }}</td>
                                            <td>{{ $visit->host->user?->name ?? '—' }} / {{ $visit->branch->name }}</td>
                                            <td>{{ $visit->expected_departure_at->format('d M Y H:i') }}</td>
                                            <td>Still checked in; status is unchanged</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <div class="x_panel">
                    <div class="x_title"><h2>Recent Activity</h2><div class="clearfix"></div></div>
                    <div class="x_content table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Time</th><th>Visit</th><th>Activity</th><th>User</th></tr></thead>
                            <tbody>
                                @forelse ($recentActivity as $activity)
                                    <tr>
                                        <td>{{ $activity->created_at?->format('d M Y H:i') }}</td>
                                        <td>{{ $activity->visit->visit_code }} · {{ $activity->visit->visitor->full_name }}</td>
                                        <td>{{ str_replace('_', ' ', ucfirst($activity->action)) }}{{ $activity->description ? ': ' . $activity->description : '' }}</td>
                                        <td>{{ $activity->user?->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No visitor activity yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="btn-group">
                    @if (auth()->user()->employee)
                        <a class="btn btn-success" href="{{ route('visitor.create') }}"><i class="fa fa-plus"></i> New Invitation</a>
                    @endif
                    @if ($canUseDesk)
                        <a class="btn btn-primary" href="{{ route('visitor.security-desk') }}"><i class="fa fa-id-badge"></i> Unified Visitor Desk</a>
                    @endif
                    <a class="btn btn-default" href="{{ route('visitor.history') }}">Visit History</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.main-layout')

@section('content-child')
    <div class="x_panel">
        <div class="x_title">
            <h2>{{ $group->name }}</h2>
            <div class="pull-right">
                <a href="{{ route('setting.hourly-time-off.edit', $group->id) }}" class="btn btn-primary btn-sm"><i
                        class="fa fa-pencil"></i> Edit</a>
                <a href="{{ route('setting.hourly-time-off.index') }}" class="btn btn-default btn-sm">Back</a>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Maximum:</strong>
                    {{ $group->maximum_hours === null ? 'Unlimited' : number_format((float) $group->maximum_hours, 2) . ' hours' }}
                </div>
                <div class="col-md-3"><strong>Period:</strong> {{ ucfirst($group->period_type) }}</div>
                <div class="col-md-3"><strong>Current period:</strong>
                    {{ \Carbon\Carbon::parse($period['period_start'])->format('d M Y') }}
                    @if ($period['period_end'])
                        - {{ \Carbon\Carbon::parse($period['period_end'])->format('d M Y') }}
                    @endif
                </div>
                <div class="col-md-3"><strong>Status:</strong> {{ $group->is_active ? 'Active' : 'Inactive' }}</div>
            </div>
            <h4>Time Off Types</h4>
            <p>{{ $group->timeoffs->pluck('name')->join(', ') ?: 'No Time Off types assigned.' }}</p>
            <h4 class="mt-4">Current Employee Balances</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Allocated</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($balances as $balance)
                            <tr>
                                <td>{{ optional($balance->employee->personal)->fullname ?? 'Employee ' . $balance->employee_id }}
                                </td>
                                <td class="text-right">{{ number_format((float) $balance->allocated_hours, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $balance->used_hours, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $balance->remaining_hours, 2) }}</td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-info" title="View history"
                                        href="{{ route('setting.hourly-time-off.employee-balance', [$group->id, $balance->employee_id]) }}">
                                        <i class="fa fa-list"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No balances have been created for this
                                    period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

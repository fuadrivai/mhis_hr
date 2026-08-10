@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Today's Attendance History</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Date & Time</th>
                                <th>Shift</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $log->employee->personal->fullname ?? ($log->fullname ?? '-') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $log->type === 'check_in' ? 'success' : 'info' }}">
                                            {{ $log->type === 'check_in' ? 'Clock In' : 'Clock Out' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($log->clock_datetime)->format('d M Y H:i:s') }}</td>
                                    <td>{{ $log->shift_name ?? '-' }}</td>
                                    <td>
                                        @if ($log->latitude !== null && $log->longitude !== null)
                                            {{ $log->latitude }}, {{ $log->longitude }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No attendance logs recorded today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

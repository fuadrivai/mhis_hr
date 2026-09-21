@extends('layouts.main-layout')

@section('content-class')
<link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
@php
function formatSeconds($seconds) {
    if ($seconds === null || $seconds === '') return '-';
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $sec = $seconds % 60;
    
    $parts = [];
    if ($hours > 0) $parts[] = $hours . 'h';
    if ($minutes > 0) $parts[] = $minutes . 'm';
    if ($sec > 0 || empty($parts)) $parts[] = $sec . 's';
    
    return implode(' ', $parts);
}
@endphp
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Aggregated Metrics</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <table id="stats-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Total Replies</th>
                        <th>Unique Contacts Handled</th>
                        <th>Average Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats as $stat)
                    <tr>
                        <td>{{ $stat['name'] }}</td>
                        <td>{{ $stat['total_replies'] }}</td>
                        <td>{{ $stat['unique_contacts'] }}</td>
                        <td>{{ formatSeconds($stat['avg_response_time']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Monitoring</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <table id="monitoring-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Date Time</th>
                        <th>Employee</th>
                        <th>Contact Number</th>
                        <th>Message</th>
                        <th>Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->employee->user->name ?? 'System' }}</td>
                        <td>{{ $log->contact_number }}</td>
                        <td>{{ nl2br(e($log->message)) }}</td>
                        <td>{{ formatSeconds($log->response_time_seconds) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('content-script')
<script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#monitoring-table').DataTable({
            order: [[0, 'desc']]
        });
        $('#stats-table').DataTable({
            order: [[1, 'desc']]
        });
    });
</script>
@endsection

@extends('layouts.main-layout')

@section('content-class')
<link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12">
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->employee->user->name ?? 'System' }}</td>
                        <td>{{ $log->contact_number }}</td>
                        <td>{{ nl2br(e($log->message)) }}</td>
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
    });
</script>
@endsection

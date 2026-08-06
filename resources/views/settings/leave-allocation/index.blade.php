@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Level</th>
                                    <th>Position</th>
                                    <th>Remaining Balance</th>
                                    <th>#</th>
                                </tr>
                            </thead>

                            <body>
                                @foreach ($leaveAllocations as $index => $leaveAllocation)
                                    <tr>
                                        <td class="text-right">{{ $index + 1 }}</td>
                                        <td>{{ $leaveAllocation->employee->personal->fullname ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->organization->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->job_level->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->job_position->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $leaveAllocation->remaining }}</td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </body>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('settings.modal-general')
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/moment/min/moment.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tbl-datatable').DataTable();
        });
    </script>
@endsection

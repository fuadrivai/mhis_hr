@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .progress {
            margin-bottom: 0;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .progress-bar {
            line-height: 20px;
            color: white;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
@endsection
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-book"></i> {{ $title }} - {{ $subject->name }}</h2>
                <a href="{{ route('employee.lesson-plan.monitoring.show', $target->id) }}" class="btn btn-default btn-sm pull-right"><i class="fa fa-arrow-left"></i> Back to Subject List</a>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;" class="bg-light">Category</th>
                                <td>{{ $subject->subjectCategory->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Target Deadline</th>
                                <td>{{ \Carbon\Carbon::parse($target->deadline_date)->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h4 style="margin-top:0;"><i class="fa fa-users"></i> Employee Breakdown</h4>
                <div class="table-responsive">
                    <table id="employeeDetailsTable" class="table table-striped table-bordered datatable">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Employee</th>
                                <th style="width: 20%;">Class</th>
                                <th style="width: 50%;">Approval Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                                <tr>
                                    <td class="align-middle"><strong>{{ $detail['employee_name'] }}</strong></td>
                                    <td class="align-middle"><span class="badge bg-blue">{{ $detail['class_name'] }}</span></td>
                                    <td class="align-middle">
                                        <div class="progress progress-striped active" style="margin-bottom: 5px; height: 20px;">
                                            <div class="progress-bar {{ $detail['progress'] == 100 ? 'progress-bar-success' : 'progress-bar-info' }}" style="width: {{ $detail['progress'] }}%;"></div>
                                        </div>
                                        <div class="text-center">
                                            <strong>{{ $detail['approved_count'] }} / {{ $detail['expected_count'] }}</strong> Approved ({{ $detail['progress'] }}%)
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable();
        });
    </script>
@endsection

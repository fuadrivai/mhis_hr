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
                <h2><i class="fa fa-list-alt"></i> {{ $title }}</h2>
                <a href="{{ route('employee.lesson-plan.monitoring.index') }}" class="btn btn-default btn-sm pull-right"><i class="fa fa-arrow-left"></i> Back to Monitoring</a>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                
                @if(empty($groupedData))
                    <div class="alert alert-info">No data available for the monitored categories.</div>
                @else
                    <div class="table-responsive">
                        <table id="subjectsTable" class="table table-striped table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Subject Name</th>
                                    <th style="width: 40%;">Overall Progress</th>
                                    <th style="width: 15%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedData as $subjectId => $data)
                                    <tr>
                                        <td class="align-middle"><span class="label label-info">{{ $data['category_name'] }}</span></td>
                                        <td class="align-middle"><strong>{{ $data['subject_name'] }}</strong></td>
                                        <td class="align-middle">
                                            <div class="progress progress-striped active">
                                                <div class="progress-bar progress-bar-success" role="progressbar" 
                                                     style="width: {{ $data['progress'] }}%">
                                                    @if($data['progress'] > 0)
                                                        {{ $data['progress'] }}% ({{ $data['total_approved'] }}/{{ $data['total_expected'] }})
                                                    @endif
                                                </div>
                                            </div>
                                            @if($data['progress'] == 0)
                                                <div class="text-center" style="font-size: 12px; margin-top: -20px; font-weight: bold;">
                                                    0% (0/{{ $data['total_expected'] }})
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('employee.lesson-plan.monitoring.subject', ['id' => $target->id, 'subject_id' => $subjectId]) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
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

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
                                        <div class="progress" style="margin-bottom: 10px;">
                                            <div class="progress-bar bg-success" style="width: {{ $detail['progress_approved'] }}%" title="Approved">
                                                @if($detail['progress_approved'] > 0)
                                                    {{ $detail['approved_count'] }}
                                                @endif
                                            </div>
                                            <div class="progress-bar bg-warning" style="width: {{ $detail['progress_revision'] }}%" title="Need Revision">
                                                @if($detail['progress_revision'] > 0)
                                                    {{ $detail['revision_count'] }}
                                                @endif
                                            </div>
                                            <div class="progress-bar bg-primary" style="width: {{ $detail['progress_submitted'] }}%" title="Submitted">
                                                @if($detail['progress_submitted'] > 0)
                                                    {{ $detail['submitted_count'] }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center" style="font-size: 11px; margin-bottom: 10px; color: #555;">
                                            Approved: <strong>{{ $detail['approved_count'] }}</strong> | Revision: <strong>{{ $detail['revision_count'] }}</strong> | Submitted: <strong>{{ $detail['submitted_count'] }}</strong> | Expected: <strong>{{ $detail['expected_count'] }}</strong>
                                        </div>
                                        
                                        @if(count($detail['submissions']) > 0)
                                            <div style="font-size: 12px; margin-top: 15px;">
                                                <strong>Submissions Details:</strong>
                                                <table class="table table-bordered table-condensed" style="background-color: #fff; margin-top: 5px; font-size: 11px;">
                                                    <thead>
                                                        <tr style="background-color: #f9f9f9;">
                                                            <th style="width: 25%;">Document</th>
                                                            <th style="width: 25%;">Status</th>
                                                            <th style="width: 50%;">Approver</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($detail['submissions'] as $sub)
                                                            <tr>
                                                                <td class="align-middle">
                                                                    <a href="{{ $sub->file_link }}" target="_blank"><i class="fa fa-file-pdf-o"></i> Week {{ $sub->week_number }}</a>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <span class="label {{ $sub->status == 'approved' ? 'label-success' : ($sub->status == 'need_revision' ? 'label-warning' : 'label-primary') }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span>
                                                                </td>
                                                                <td class="align-middle">
                                                                    @if($sub->approvals->isNotEmpty())
                                                                        {{ $sub->approvals->last()->approverEmployee->user->name ?? 'Unknown' }}
                                                                    @else
                                                                        <span class="text-muted"><em>Pending</em></span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
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

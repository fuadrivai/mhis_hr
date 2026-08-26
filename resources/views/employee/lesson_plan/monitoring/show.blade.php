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
                    <div class="well">
                        <form action="{{ route('employee.lesson-plan.monitoring.print', $target->id) }}" method="POST" target="_blank">
                            @csrf
                            <h4><i class="fa fa-print"></i> Print Report</h4>
                            <p>Select categories to print:</p>
                            <div class="row">
                                @foreach($monitorRoles as $role)
                                    @if($role->subjectCategory)
                                    <div class="col-md-3 col-sm-4 col-xs-6">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="category_ids[]" value="{{ $role->subject_category_id }}" checked>
                                            {{ $role->subjectCategory->name }}
                                        </label>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="form-group" style="margin-top: 15px;">
                                <label for="whatsapp_numbers"><i class="fa fa-whatsapp"></i> WhatsApp Numbers to Notify (comma-separated, start with 62):</label>
                                <input type="text" name="whatsapp_numbers" id="whatsapp_numbers" class="form-control" placeholder="e.g. 6281383151326, 6281234567890">
                                <small class="text-muted">Only used when sending notifications. Messages are sent sequentially to all listed numbers.</small>
                            </div>
                            <hr>
                            <button type="submit" class="btn btn-primary" formaction="{{ route('employee.lesson-plan.monitoring.print', $target->id) }}" formtarget="_blank"><i class="fa fa-print"></i> Print Selected Categories</button>
                            <button type="submit" class="btn btn-success" formaction="{{ route('employee.lesson-plan.monitoring.notify', $target->id) }}" formtarget="_self"><i class="fa fa-whatsapp"></i> Send WhatsApp Notification</button>
                        </form>
                    </div>

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
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: {{ $data['progress_approved'] }}%" title="Approved">
                                                    @if($data['progress_approved'] > 0)
                                                        {{ $data['total_approved'] }}
                                                    @endif
                                                </div>
                                                <div class="progress-bar bg-warning" style="width: {{ $data['progress_revision'] }}%" title="Need Revision">
                                                    @if($data['progress_revision'] > 0)
                                                        {{ $data['total_revision'] }}
                                                    @endif
                                                </div>
                                                <div class="progress-bar bg-primary" style="width: {{ $data['progress_submitted'] }}%" title="Submitted">
                                                    @if($data['progress_submitted'] > 0)
                                                        {{ $data['total_submitted'] }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-center" style="font-size: 11px; margin-top: 5px; color: #555;">
                                                Approved: <strong>{{ $data['total_approved'] }}</strong> | Revision: <strong>{{ $data['total_revision'] }}</strong> | Submitted: <strong>{{ $data['total_submitted'] }}</strong> | Expected: <strong>{{ $data['total_expected'] }}</strong>
                                            </div>
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

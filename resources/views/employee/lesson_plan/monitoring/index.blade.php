@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-eye"></i> {{ $title }}</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="alert alert-info">
                    <strong>Monitoring Categories:</strong>
                    @foreach($monitorRoles as $role)
                        <span class="label label-primary">{{ $role->subjectCategory->name ?? '' }}</span>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Deadline</th>
                                <th>Lesson Plan Month</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($targets as $target)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($target->deadline_date)->format('d M Y') }}</td>
                                    <td>
                                        @foreach($target->months as $month)
                                            <span class="badge bg-green">{{ $month->month }} {{ $month->year }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ route('employee.lesson-plan.monitoring.show', $target->id) }}" class="btn btn-info btn-sm"><i class="fa fa-folder-open"></i> View Details</a>
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
            $(".datatable").DataTable({
                "order": [[1, "desc"]]
            });
        });
    </script>
@endsection

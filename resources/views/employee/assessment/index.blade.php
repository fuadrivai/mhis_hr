@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>My Assessment Targets</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Target Description</th>
                            <th>Deadline</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $target)
                            @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $target->description }}</td>
                                <td>{{ $target->deadline_date }}</td>
                                <td>{{ $assignment->subject->name ?? '' }} ({{ $assignment->subject->subjectCategory->name ?? '' }})</td>
                                <td>{{ $assignment->schoolClass->name ?? '' }}</td>
                                <td>
                                    <a href="{{ route('employee.assessment.submit-form', ['targetId' => $target->id, 'assignmentId' => $assignment->id]) }}" class="btn btn-primary btn-sm">View & Submit</a>
                                </td>
                            </tr>
                            @endforeach
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
            $(".datatable").DataTable();
        });
    </script>
@endsection

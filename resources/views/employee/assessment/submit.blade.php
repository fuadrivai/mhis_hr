@extends('layouts.main-layout')
@section('content-class')
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Submit Assessment <small>{{ $assignment->subject->name ?? '' }} - {{ $assignment->schoolClass->name ?? '' }}</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                
                <h4>Target: {{ $target->description }} (Due: {{ $target->deadline_date }})</h4>
                <hr>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Assessment Submission</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>File Link</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sub = $submission;
                                @endphp
                                <tr>
                                    @if($sub && in_array($sub->status, ['submitted', 'approved']))
                                        <td>{{ $sub->title }}</td>
                                        <td><a href="{{ $sub->file_link }}" target="_blank">View File</a></td>
                                        <td>
                                            @if($sub->status == 'approved')
                                                <span class="label label-success">Approved</span>
                                            @else
                                                <span class="label label-primary">In Review (Level {{ $sub->current_approval_level }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sub->status == 'approved')
                                                <em>Completed</em>
                                            @else
                                                <em>Pending Approval</em>
                                            @endif
                                        </td>
                                    @else
                                        <form action="{{ route('employee.assessment.submit', ['targetId' => $target->id, 'assignmentId' => $assignment->id]) }}" method="POST">
                                            @csrf
                                            <td>
                                                <input type="text" name="title" class="form-control input-sm" value="{{ $sub->title ?? '' }}" required placeholder="Assessment Title">
                                            </td>
                                            <td>
                                                <input type="url" name="file_link" class="form-control input-sm" value="{{ $sub->file_link ?? '' }}" required placeholder="https://docs.google.com/...">
                                            </td>
                                            <td>
                                                @if($sub && $sub->status == 'need_revision')
                                                    <span class="label label-danger">Needs Revision</span><br>
                                                    @if($sub->approvals->count() > 0)
                                                    <small>Notes: {{ $sub->approvals->last()->notes }}</small>
                                                    @endif
                                                @else
                                                    <span class="label label-default">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-success btn-sm">Submit</button>
                                            </td>
                                        </form>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
@endsection

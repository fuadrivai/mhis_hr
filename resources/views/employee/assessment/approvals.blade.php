@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-modern {
            border-radius: 12px;
            padding: 3px 10px;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-check-square-o"></i> Assessment Approvals <small>Pending Your Review</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        {{ $errors->first() }}
                    </div>
                @endif

                <table class="table table-striped table-bordered datatable" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="width: 15%">Submitter</th>
                            <th style="width: 20%">Subject & Class</th>
                            <th style="width: 15%">Target Deadline</th>
                            <th style="width: 20%">Assessment Title</th>
                            <th style="width: 10%">Link</th>
                            <th style="width: 20%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingSubmissions as $sub)
                        <tr>
                            <td><strong>{{ $sub->assignment->employee->user->name ?? 'Unknown User' }}</strong></td>
                            <td>{{ $sub->assignment->subject->name ?? '' }} <br><span class="label label-default">{{ $sub->assignment->schoolClass->name ?? '' }}</span></td>
                            <td><span class="badge bg-blue">{{ $sub->target->deadline_date }}</span></td>
                            <td>{{ $sub->title }}</td>
                            <td><a href="{{ $sub->file_link }}" target="_blank" class="btn btn-info btn-xs btn-modern"><i class="fa fa-external-link"></i> Open File</a></td>
                            <td>
                                <div class="action-buttons">
                                    <form action="{{ route('assessment.approvals.process', $sub->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-xs btn-modern" onclick="return confirm('Approve this assessment?')"><i class="fa fa-check"></i> Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-xs btn-modern" data-toggle="modal" data-target="#revisionModal{{ $sub->id }}"><i class="fa fa-times"></i> Revision</button>
                                </div>
                                
                                <!-- Revision Modal -->
                                <div class="modal fade" id="revisionModal{{ $sub->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('assessment.approvals.process', $sub->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="need_revision">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Request Revision</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Notes / Reason</label>
                                                        <textarea name="notes" class="form-control" required rows="4" placeholder="Explain what needs to be changed..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger"><i class="fa fa-paper-plane"></i> Submit Request</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
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
            $(".datatable").DataTable();
        });
    </script>
@endsection

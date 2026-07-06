@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-calendar-check-o"></i> Assessment Targets</h2>
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

                <div class="form-section">
                    <form action="{{ route('assessment-target.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <label>Target Title / Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" placeholder="e.g. August 16 Target for Sept/Oct" required>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <label>Submission Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="deadline_date" class="form-control" required>
                            </div>
                        </div>
                        

                        
                        <hr style="border-top: 1px solid #ccc;">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Target</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table class="table table-striped table-bordered datatable" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="width: 35%">Description</th>
                            <th style="width: 20%">Deadline Date</th>
                            <th style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $target)
                        <tr>
                            <td><strong>{{ $target->description }}</strong></td>
                            <td><span class="badge bg-red"><i class="fa fa-clock-o"></i> {{ $target->deadline_date }}</span></td>

                            <td>
                                <form action="{{ route('assessment-target.destroy', $target->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this target?');"><i class="fa fa-trash"></i> Delete</button>
                                </form>
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

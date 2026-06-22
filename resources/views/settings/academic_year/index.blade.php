@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection
@section('content-child')
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
                <h2>Academic Years Management</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal"><i class="fa fa-plus"></i> Add Academic Year</button>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-striped table-bordered" id="datatable" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($years as $year)
                        <tr>
                            <td>{{ $year->name }}</td>
                            <td>
                                @if($year->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if(!$year->is_active)
                                    <form action="{{ route('academic-year.active', $year->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-check"></i> Set Active</button>
                                    </form>
                                @endif
                                <form action="{{ route('academic-year.destroy', $year->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Academic Year?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" {{ $year->is_active ? 'disabled' : '' }}><i class="fa fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('academic-year.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Academic Year</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Academic Year Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. 2025-2026" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('content-script')
<script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable();
    });
</script>
@endsection

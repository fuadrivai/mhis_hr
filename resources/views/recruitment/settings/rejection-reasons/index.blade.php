@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Master Rejection Reasons <small>Candidate rejection reasons and dispositions</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <button type="button" class="btn btn-success btn-sm text-white" data-toggle="modal" data-target="#addReasonModal">
                            <i class="fa fa-plus"></i> Add Rejection Reason
                        </button>
                    </li>
                    <li>
                        <a href="{{ route('recruitment.candidates') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Database
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        <ul style="margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="rejection-reasons-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Order</th>
                                <th>Reason Name</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 120px;">Created At</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reasons as $reason)
                                <tr>
                                    <td>{{ $reason->sort_order }}</td>
                                    <td><strong>{{ $reason->name }}</strong></td>
                                    <td>
                                        @if ($reason->is_active)
                                            <span class="badge badge-success" style="background-color: #26B99A;">Active</span>
                                        @else
                                            <span class="badge badge-secondary" style="background-color: #777;">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $reason->created_at ? $reason->created_at->format('d M Y') : '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#editReasonModal-{{ $reason->id }}">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('recruitment.settings.rejection-reasons.toggle', $reason) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-xs {{ $reason->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $reason->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fa {{ $reason->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('recruitment.settings.rejection-reasons.destroy', $reason) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this rejection reason?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editReasonModal-{{ $reason->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('recruitment.settings.rejection-reasons.update', $reason) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                                    <h4 class="modal-title">Edit Rejection Reason</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">Reason Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $reason->name }}" required maxlength="255">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Sort Order</label>
                                                        <input type="number" name="sort_order" class="form-control" value="{{ $reason->sort_order }}" min="0">
                                                    </div>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" name="is_active" value="1" {{ $reason->is_active ? 'checked' : '' }}> Active
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No rejection reasons found. Click 'Add Rejection Reason' to create one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div class="modal fade" id="addReasonModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('recruitment.settings.rejection-reasons.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                        <h4 class="modal-title">Add Rejection Reason</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Reason Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Salary expectation mismatch" required maxlength="255">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ ($reasons->max('sort_order') ?? 0) + 1 }}" min="0">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" checked> Active
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Reason</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rejection-reasons-table').DataTable({
                pageLength: 25,
                order: [[0, 'asc']]
            });
        });
    </script>
@endsection


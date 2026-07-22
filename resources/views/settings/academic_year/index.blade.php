@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Academic Years Management</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-striped table-bordered table-sm" id="tbl-ay" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($years as $year)
                                    <tr>
                                        <td>{{ $year->name }}</td>
                                        <td class="text-center">
                                            {{ $year->start_date ? \Carbon\Carbon::parse($year->start_date)->format('d M Y') : '-' }}
                                            -
                                            {{ $year->end_date ? \Carbon\Carbon::parse($year->end_date)->format('d M Y') : '-' }}
                                        </td>
                                        <td>
                                            @if ($year->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-nowrap align-items-center">
                                                <button type="button" class="btn btn-primary btn-sm mr-1 btn-edit-year"
                                                    data-toggle="modal" data-target="#editModal"
                                                    data-update-url="{{ route('academic-year.update', $year->id) }}"
                                                    data-name="{{ $year->name }}"
                                                    data-start-date="{{ $year->start_date }}"
                                                    data-end-date="{{ $year->end_date }}">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </button>
                                                @if (!$year->is_active)
                                                    <form action="{{ route('academic-year.active', $year->id) }}"
                                                        method="POST" class="mb-0 mr-1 flex-shrink-0">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-info btn-sm"><i
                                                                class="fa fa-check"></i> Set Active</button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('academic-year.destroy', $year->id) }}"
                                                    method="POST" class="mb-0 flex-shrink-0"
                                                    onsubmit="return confirm('Delete this Academic Year?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        {{ $year->is_active ? 'disabled' : '' }}><i
                                                            class="fa fa-trash"></i>
                                                        Delete</button>
                                                </form>
                                            </div>
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
            <form action="{{ route('academic-year.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Academic Year</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Academic Year Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. 2025-2026"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" name="start_date" class="form-control date-picker" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" name="end_date" class="form-control date-picker" required>
                                </div>
                            </div>
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

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="editAcademicYearForm" action="" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Academic Year</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Academic Year Name</label>
                                    <input type="text" id="edit_name" name="name" class="form-control"
                                        placeholder="e.g. 2025-2026" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" id="edit_start_date" name="start_date"
                                        class="form-control date-picker" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" id="edit_end_date" name="end_date"
                                        class="form-control date-picker" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tbl-ay').DataTable({
                ordering: false,
                dom: '<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip',
                buttons: [{
                    text: 'Add Academic Year <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-add'
                    },
                    className: 'btn btn-success ml-2 btn-sm font-weight-bold',
                    action: function() {
                        $('#addModal').modal('show');
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
            });

            $(document).on('click', '.btn-edit-year', function() {
                var button = $(this);

                $('#editAcademicYearForm').attr('action', button.data('update-url'));
                $('#edit_name').val(button.data('name'));
                $('#edit_start_date').val(button.data('start-date'));
                $('#edit_end_date').val(button.data('end-date'));
            });
        });
    </script>
@endsection

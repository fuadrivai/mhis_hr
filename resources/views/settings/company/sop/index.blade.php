@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <button type="button" class="btn btn-success btn-sm text-white btn-add" data-toggle="modal" data-target="#modal-data">
                            <i class="fa fa-plus"></i> Add SOP
                        </button>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Link</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sops as $sop)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sop->title }}</td>
                                        <td>
                                            <a href="{{ $sop->link }}" target="_blank" rel="noopener noreferrer">
                                                {{ $sop->link }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($sop->is_active)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-default">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-success btn-sm btn-data"
                                                    data-id="{{ $sop->id }}"
                                                    data-title="{{ $sop->title }}"
                                                    data-link="{{ $sop->link }}"
                                                    data-is-active="{{ $sop->is_active ? 1 : 0 }}"
                                                    data-toggle="modal"
                                                    data-target="#modal-data">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <form action="{{ route('setting.company.sop.destroy', $sop->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this SOP?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
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

    <div class="modal fade" id="modal-data" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-data" method="POST">
                    @csrf
                    <div id="form-method"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">SOP Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="link">Link</label>
                            <input type="url" id="link" class="form-control" name="link" placeholder="https://example.com" required>
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="is_active" value="0">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="is_active" name="is_active" value="1"> Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tbl-datatable').DataTable();

            $('.btn-add').on('click', function () {
                $('#form-method').html('');
                $('#form-data').attr('action', "{{ route('setting.company.sop.store') }}");
                $('#form-data').trigger('reset');
                $('#is_active').prop('checked', true);
                $('#modal-dataLabel').text('Create SOP');
            });

            $('#tbl-datatable').on('click', '.btn-data', function () {
                const id = $(this).attr('data-id');
                const title = $(this).attr('data-title');
                const link = $(this).attr('data-link');
                const isActive = $(this).attr('data-is-active') === '1';

                $('#form-method').html('@method("PUT")');
                $('#form-data').attr('action', `{{ url('setting/company/sop') }}/${id}`);
                $('#title').val(title);
                $('#link').val(link);
                $('#is_active').prop('checked', isActive);
                $('#modal-dataLabel').text('Edit SOP');
            });
        });
    </script>
@endsection

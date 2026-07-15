@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->description ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $item->is_active == 1 ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $item->is_active == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)" class="btn btn-sm btn-primary btn-edit-category"
                                                data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                data-description="{{ e($item->description ?? '') }}"
                                                data-active="{{ $item->is_active == 1 ? 1 : 0 }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-category"
                                                data-id="{{ $item->id }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
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

    <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="categoryForm" action="/announcement/category" method="POST">
                    @csrf
                    <div id="form-method">
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Category Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label for="category_name">Name</label>
                            <input type="text" required class="form-control" id="category_name" name="name"
                                placeholder="Input category name">
                        </div>

                        <div class="form-group">
                            <label for="category_description">Description</label>
                            <textarea required class="form-control" id="category_description" name="description" rows="3"
                                placeholder="Input description"></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="d-block mb-2">Status</label>
                            <label class="switch mb-0">
                                <input type="checkbox" id="category_is_active" name="is_active" checked>
                                <span class="slider round"></span>
                            </label>
                            <span class="ml-2">Is Active</span>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteCategoryForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>

    <script>
        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                pageLength: 25,
                ordering: false,
                responsive: true,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Category <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-category'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        $('#categoryModalLabel').text('Add Category');
                        $('#categoryForm')[0].reset();
                        $('#id').val('');
                        $('#form-method').html('');
                        $('#categoryForm').attr('action', '/announcement/category');
                        $('#category_is_active').prop('checked', true);
                        $('#categoryModal').modal('show');
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
            });

            $(document).on('click', '.btn-edit-category', function() {
                $('#categoryModalLabel').text('Edit Category');
                $('#id').val($(this).data('id'));
                $('#category_name').val($(this).data('name'));
                $('#category_description').val($(this).data('description'));
                $('#category_is_active').prop('checked', $(this).data('active') == 1 ? true : false);
                $('#form-method').html('<input type="hidden" name="_method" value="PUT">');
                let id = $(this).data('id');
                $('#categoryForm').attr('action', `/announcement/category/${id}`);
                $('#categoryModal').modal('show');
            });

            $(document).on('click', '.btn-delete-category', function() {
                let id = $(this).data('id');
                let isConfirmed = confirm('Are you sure you want to delete this category?');

                if (!isConfirmed) {
                    return;
                }

                $('#deleteCategoryForm').attr('action', `/announcement/category/${id}`);
                $('#deleteCategoryForm').submit();
            });
        })
    </script>
@endsection

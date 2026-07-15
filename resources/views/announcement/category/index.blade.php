@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .2s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: #fff;
            transition: .2s;
        }

        input:checked+.slider {
            background-color: #26b99a;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
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
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->description ?? 'N/A' }}</td>
                                        <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-primary btn-edit-category"
                                                data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                data-description="{{ e($item->description ?? '') }}"
                                                data-active="{{ $item->status == 1 ? 1 : 0 }}">
                                                <i class="fa fa-eye"></i> Edit
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete-category"
                                                data-id="{{ $item->id }}">
                                                <i class="fa fa-trash"></i> Delete
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
                        $('#category_id').val('');
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
                $('#category_is_active').prop('checked', $(this).data('active') == 1);
                $('#form-method').append(`@method('put')`);
                $('#form-data').attr('action', `/announcements/category/${id}`)
                $('#categoryModal').modal('show');
            });


            $('#btn-category').on('click', function() {
                $('#form-method').append(`@method('post')`);
                $('#form-data').attr('action', "/announcements/category")
            });
        })
    </script>
@endsection

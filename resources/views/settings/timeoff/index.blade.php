@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-12">
                    <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Active</th>
                                <th>#</th>
                            </tr>
                        </thead>

                        <body></body>
                    </table>
                </div>
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
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/setting/timeoff/datatable",
                    type: "GET",
                },
                pageLength: 25,
                ordering: false,
                responsive: true,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Time Off <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-timeoff'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        window.location.href = "/setting/timeoff/create";
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
                columns: [{
                        data: "code",
                        defaultContent: "--",
                    },
                    {
                        data: "name",
                        defaultContent: "--",
                    },
                    {
                        data: "is_active",
                        defaultContent: "--",
                        className: "text-center",
                        mRender: function(data, type, full) {
                            return `<span class="badge badge-${data ? 'success' : 'secondary'}"> ${data ? 'Active' : 'Inactive'}</span>`
                        }
                    },
                    {
                        data: "id",
                        className: "text-center",
                        mRender: function(data, type, full) {
                            return `
                                <a href="/setting/timeoff/${data}/edit" class="btn btn-sm btn-primary btn-edit" title="Edit Time Off">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-warning btn-copy" data-id="${data}" title="Copy Time Off">
                                    <i class="fa fa-copy text-white"></i>
                                </button>
                            `
                        }
                    },
                ],
            });

            $('#tbl-datatable tbody').on('click', '.btn-edit', function() {
                var data = tbldata.row($(this).parents('tr')).data();
                $('#id').val(data.id);
                $('#code').val(data.code);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#deduct_from_leave').prop('checked', data.deduct_from_leave == 1 ? true : false);
                $('#is_paid').prop('checked', data.is_paid == 1 ? true : false);
                $('#need_attachment').prop('checked', data.need_attachment == 1 ? true : false);
            });

            $('#tbl-datatable tbody').on('click', '.btn-copy', function() {
                var data = tbldata.row($(this).parents('tr')).data();
                // Store the data in sessionStorage to be used in the create form
                sessionStorage.setItem('copy_timeoff_data', JSON.stringify({
                    code: data.code + '_copy',
                    name: data.name + ' (Copy)',
                    schema: data.schema
                }));
                // Redirect to create form
                window.location.href = '/setting/timeoff/create';
            });

            $('#form-timeoff').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $('#modal-timeoff').modal('hide');
                $.blockUI();
                let id = $('#id').val();
                let method = id ? "PUT" : "POST";
                let url = (id != null && id !== "") ? `/setting/timeoff/${id}` : `/setting/timeoff`;
                ajax(
                    formData,
                    url,
                    method,
                    function(json) {
                        sweetAlert("Success", "Time Off has been saved successfully.", "success");
                        setTimeout(() => window.location.href = "/setting/timeoff", 1000);
                    },
                    function(json) {
                        sweetAlert("Failed", json, "error");
                    }
                );
            });
        })
    </script>
@endsection

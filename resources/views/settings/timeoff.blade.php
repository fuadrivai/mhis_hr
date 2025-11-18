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
                        <button data-toggle="modal" data-target="#modal-timeoff" type="button"
                            class="btn btn-success btn-sm text-white btn-add"><i class="fa fa-plus"></i> Add Time
                            Off</button>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Is Paid</th>
                                    <th>Deduct</th>
                                    <th>Need Attachement</th>
                                    <th>#</th>
                                </tr>
                            </thead>

                            <body>

                            </body>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-timeoff" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form autocomplete="off" action="/setting/timeoff" method="POST" id="form-timeoff">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Time Off Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="hidden" name="id" id="id" class="form-control ">
                                    <label for="code">Code</label>
                                    <input type="text" name="code" id="code" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" class="form-control" id="description" cols="5" rows="3"></textarea>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="deduct_from_leave"
                                            name="deduct_from_leave" value="1">
                                        <label class="custom-control-label" for="deduct_from_leave">
                                            Deduct From Leave
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mt-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_paid" name="is_paid"
                                            value="1">
                                        <label class="custom-control-label" for="is_paid">
                                            Is Paid
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mt-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="need_attachment"
                                            name="need_attachment" value="1">
                                        <label class="custom-control-label" for="need_attachment">
                                            Need Attachment
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary mt-3">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>
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
        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/setting/timeoff/datatable",
                    type: "GET",
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
                        data: "is_paid",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `<span class="badge badge-${data ? 'info' : 'secondary'}"> ${data ? 'Yes' : 'No'}</span>`
                        }
                    },
                    {
                        data: "deduct_from_leave",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `<span class="badge badge-${data ? 'info' : 'secondary'}"> ${data ? 'Yes' : 'No'}</span>`
                        }
                    },
                    {
                        data: "need_attachment",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `<span class="badge badge-${data ? 'info' : 'secondary'}"> ${data ? 'Yes' : 'No'}</span>`
                        }
                    },
                    {
                        data: "id",
                        mRender: function(data, type, full) {
                            return `
                                <button data-toggle="modal" data-target="#modal-timeoff" type="button" class="btn btn-primary btn-edit"><i class="fa fa-edit"></i></button>
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

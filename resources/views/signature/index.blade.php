@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>List Signature Document</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="/signature/create" class="btn btn-success btn-sm text-white btn-add-signature"><i
                                class="fa fa-plus"></i> Add
                            Signature</a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-signature" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Created By</th>
                                    <th>Sign date</th>
                                    <th>Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/moment/min/moment.min.js"></script>

    <script>
        $(document).ready(function() {
            tblSignature = $("#tbl-signature").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('signature.index') }}",
                    type: "GET",
                },
                columns: [{
                        data: "code",
                        defaultContent: "--"
                    },
                    {
                        data: "title",
                        defaultContent: "--"
                    },
                    {
                        data: "created_by",
                        defaultContent: "--",
                    },
                    {
                        data: "sign_date",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return moment(data).format("DD MMMM YYYY")
                        }
                    },
                    {
                        data: "created_at",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return moment(data).format("DD MMMM YYYY")
                        }
                    },
                    {
                        data: 'id',
                        mRender: function(data, type, full) {
                            return `<a data-toggle="modal" data-target="#modalUser" title="Edit" class="btn btn-sm btn-info text-white btn-edit-user"><i class="fa fa-pencil"></i> Edit</a>
                        <a data-toggle="modal" data-target="#modalReset"  title="Edit" class="btn btn-sm btn-danger text-white btn-reset-password"><i class="fa fa-refresh"></i> Reset Password</a>`
                        }
                    }
                ],
                columnDefs: [{
                    className: "text-center",
                    targets: [2, 3]
                }, ],
                order: [
                    [2, 'desc']
                ]
            });
        })
    </script>
@endsection

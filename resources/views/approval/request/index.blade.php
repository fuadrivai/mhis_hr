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
                                <th>ID</th>
                                <th>Time Off Type</th>
                                <th>Requester</th>
                                <th>Status</th>
                                <th>Step</th>
                                <th>Created At</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
                    url: "/time/request/datatable",
                    type: "GET",
                },
                pageLength: 25,
                ordering: false,
                responsive: true,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Request <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-approval-request'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        window.location.href = "/time/request/create";
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
                columns: [{
                        data: "id",
                        defaultContent: "--",
                    },
                    {
                        data: "type",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return data && data.name ? data.name : '--';
                        }
                    },
                    {
                        data: "requester_employee_id",
                        defaultContent: "--",
                    },
                    {
                        data: "status",
                        defaultContent: "--",
                        className: "text-center",
                        mRender: function(data) {
                            const badge = data === 'approved' ? 'success' : data === 'rejected' ?
                                'danger' : data === 'cancelled' ? 'secondary' : 'warning';
                            return `<span class="badge badge-${badge}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : '--'}</span>`;
                        }
                    },
                    {
                        data: "current_step",
                        className: "text-center",
                        defaultContent: "--",
                    },
                    {
                        data: "created_at",
                        defaultContent: "--",
                    },
                    {
                        data: "id",
                        className: "text-center",
                        mRender: function(data) {
                            return `
                                <a href="/time/request/${data}/edit" class="btn btn-sm btn-primary" title="Edit Request">
                                    <i class="fa fa-edit"></i>
                                </a>
                            `;
                        }
                    },
                ],
            });
        })
    </script>
@endsection

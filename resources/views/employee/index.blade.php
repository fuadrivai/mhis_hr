@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if ($errors->has('file'))
        <div class="alert alert-danger">
            <strong>Import gagal. Periksa file dan baris berikut:</strong>
            <ul class="mb-0">
                @foreach ($errors->get('file') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a data-toggle="modal" data-target="#importExcel" href="/employee/create"
                            class="btn btn-info btn-sm text-white btn-add-employee"><i class="fa fa-upload"></i> Import
                            Excel</a>
                    </li>
                    <li>
                        <a href="/employee/create" class="btn btn-success btn-sm text-white btn-add-employee"><i
                                class="fa fa-plus"></i> Add User</a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-employee" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Level</th>
                                    <th>Organization</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="/employee/import" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Import Employees</h5>
                    </div>
                    <div class="modal-body">

                        {{ csrf_field() }}

                        <p>Download the template and keep its column headers unchanged. Required fields: Employee ID, Full Name, Organization, Job Position, Job Level, Employment Status, Email, Branch, Religion, and Gender.</p>
                        <p>Use dates in <code>YYYY-MM-DD</code> format. Employment Status: permanent, contract, probation, or freelance. Gender: Male or Female. Marital Status: Single, Married, Widow, or Widower. Blood Type: A, B, AB, or O. Keep ID, barcode, and phone values as text to preserve leading zeroes.</p>
                        <a href="{{ route('employee.import-template') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-download"></i> Download CSV Template
                        </a>

                        <label for="employee-import-file" class="mt-3">Choose CSV or Excel file</label>
                        <div class="form-group">
                            <input id="employee-import-file" type="file" name="file" accept=".csv,.xls,.xlsx" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import</button>
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
            tblUser = $("#tbl-employee").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('/employee') }}",
                    type: "GET",
                },
                columns: [{
                        data: "employment.employee_id",
                        defaultContent: "--"
                    },
                    {
                        data: "personal.fullname",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `${data}<br> ${full.personal.email}`
                        }
                    },
                    {
                        data: "employment.branch_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.job_level_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.organization_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.job_position_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.employment_status",
                        defaultContent: "--",
                        className: "text-center",
                        mRender: function(data, type, full) {
                            let color = "";
                            switch (data) {
                                case "contract":
                                    color = "danger"
                                    break;
                                case "permanent":
                                    color = "primary"
                                    break;
                                case "freelance":
                                    color = "warning"
                                    break;
                                default:
                                    color = "secondary"
                                    break;
                            }
                            return `<span class="badge badge-${color}">${data}</span>`
                        }
                    },
                    {
                        data: 'id',
                        mRender: function(data, type, full) {
                            return `<a title="Edit" class="btn btn-sm btn-info text-white btn-edit-employee"><i class="fa fa-pencil"></i> Edit</a>`
                        }
                    }
                ],
                order: [
                    [1, "asc"]
                ]
            });
        })
    </script>
@endsection

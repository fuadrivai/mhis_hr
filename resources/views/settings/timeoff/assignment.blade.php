@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/side-drawer-modal-bootstrap/bootstrap-side-modals.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="timeoff_type_id">Time Off Type</label>
                        <select disabled name="timeoff_type_id" id="timeoff_type_id" class="form-control select2">
                            <option value="">Select Time Off Type</option>
                            @foreach ($timeoff as $type)
                                <option {{ $type->id == $id ? 'selected' : '' }} value="{{ $type->id }}">
                                    {{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="academic_year_id">Academic Year</label>
                        <select required name="academic_year_id" id="academic_year_id" class="form-control select2">
                            <option value="">Select Academic Year</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 d-flex justify-content-end">
        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#right-modal-user"
            id="btn-assign">
            <i class="fa fa-plus"></i> Add Employee
        </button>
    </div>
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-12">
                    <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Organization</th>
                                <th>Level</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <body>

                        </body>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-right fade" id="right-modal-user" tabindex="-1" role="dialog"
        aria-labelledby="right_modal_lg">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table id="tbl-employee" class="table table-striped table-bordered table-sm"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="checkAll" class="checkAll"></th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Level</th>
                                        <th>Organization</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-fixed">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-submit-employee">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/loadingoverlay/loadingoverlay.min.js"></script>
    <script>
        let selectedEmployess = [];
        let objLeaveAllocation = {
            timeoff_id: {{ $id }},
            academic_year_id: null,
            employees: []
        };
        $(document).ready(function() {
            tblSelectedEmployees = $('#tbl-datatable').DataTable({
                data: selectedEmployess,
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'personal.fullname',
                        defaultContent: '-'
                    },
                    {
                        data: 'employment.branch.name',
                        defaultContent: '-'
                    },
                    {
                        data: 'employment.organization_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'employment.job_level_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'employment.job_position_name',
                        defaultContent: '-'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `<button class="btn btn-danger btn-sm btn-delete-employee" data-id="${data.id}"><i class="fa fa-trash"></i></button>`;
                        }
                    }
                ]
            });

            $('#right-modal-user').on('hidden.bs.modal', function(e) {
                $("#tbl-employee").DataTable().destroy();
            })

            $('#btn-submit-employee').on('click', function() {
                objLeaveAllocation.employees = [];
                selectedEmployess.forEach(emp => {
                    let isExist = objLeaveAllocation.employees.some(item => item.id == emp.id)
                    if (!isExist) {
                        objLeaveAllocation.employees.push(emp);
                    }
                });
                reloadJsonDataTable(tblSelectedEmployees, objLeaveAllocation.employees);
                $("#right-modal-user").modal('hide')
            })

            $('#right-modal-user').on('show.bs.modal', function(e) {
                selectedEmployess = objLeaveAllocation.employees.map(emp => ({
                    ...emp
                }));
                tblUser = $("#tbl-employee").DataTable({
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    ajax: {
                        url: "{{ URL::to('employee/active') }}",
                        type: "GET",
                    },
                    columns: [{
                            data: "id",
                            defaultContent: "--",
                            mRender: function(data, type, full) {
                                return `<input type="checkbox" class="input-check" data-id="${data}">`
                            }
                        },
                        {
                            data: "personal.fullname",
                            defaultContent: "--",
                            mRender: function(data, type, full) {
                                return `<strong>${data}</strong><br>${full.personal.email}`
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
                            defaultContent: "--"
                        },
                    ],
                    drawCallback: function(settings) {
                        var api = this.api();
                        var node = api.rows().nodes()
                        for (var i = 0; i < node.length; i++) {
                            let empId = $(node[i]).find('input').attr('data-id')
                            let isExist = objLeaveAllocation.employees.some(item => item.id ==
                                empId)
                            if (isExist) {
                                $(node[i]).find('input').prop('checked', true)
                            }
                        }
                    },
                });
            })

            $('#checkAll').on('click', function() {
                let checked = this.checked;
                $('#tbl-employee .input-check').prop('checked', checked);
                let rowsData = tblUser.rows().data().toArray();
                if (checked) {
                    rowsData.forEach(employee => {
                        if (!selectedEmployess.find(e => e.id === employee.id)) {
                            selectedEmployess.push(employee);
                        }
                    });
                } else {
                    let idsInPage = rowsData.map(emp => emp.id);
                    selectedEmployess = selectedEmployess.filter(emp => !idsInPage.includes(emp.id));
                }
            });

            $('#tbl-employee').on('change', 'td input[type="checkbox"]', function() {
                let employee = tblUser.row($(this).parents('tr')).data();
                let val = $(this).prop('checked');
                if (val == true) {
                    selectedEmployess.push(employee)
                } else {
                    selectedEmployess = selectedEmployess.filter(emp => emp.id !== employee.id);
                }
            })

            $("#tbl-datatable").on('click', '.btn-delete-employee', function() {
                let data = tblSelectedEmployees.row($(this).parents('tr')).index();
                objLeaveAllocation.employees.splice(data, 1);
                reloadJsonDataTable(tblSelectedEmployees, objLeaveAllocation.employees);
            })
        });
    </script>
@endsection

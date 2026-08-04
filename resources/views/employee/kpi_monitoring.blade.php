@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel employee-filter-panel">
            <div class="x_title employee-filter-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="employee-section-title">Form Filter</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="#filterFormCollapse" class="text-dark employee-filter-toggle" data-toggle="collapse"
                            role="button" aria-expanded="true" aria-controls="filterFormCollapse"
                            id="filterCollapseToggle">
                            <i class="fa fa-chevron-up" id="filterCollapseIcon"></i>
                            <span id="filterCollapseLabel">Hide Filter</span>
                        </a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content collapse show employee-filter-body" id="filterFormCollapse">
                <div class="row employee-filter-grid">
                    <div class="col-md-4">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Insert Name or Email</label>
                            <input type="text" name="search" id="search" class="form-control"
                                placeholder="Full name or email">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Organization</label>
                            <select name="organization" id="organization" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($organizations as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Position</label>
                            <select name="position" id="position" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($positions as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Level</label>
                            <select name="level" id="level" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($levels as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Status</label>
                            <select name="status" id="status" class="select2 form-control">
                                <option value="all">All</option>
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Branch</label>
                            <select name="branch" id="branch" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($branches as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>KPI Monitoring - Active Academic Year: {{ $activeYear ? $activeYear->name : 'None' }}</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ url('/employee') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Employee List</a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-kpi-monitoring" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Position</th>
                                    <th>KPI Score</th>
                                    <th>Managerial Doc</th>
                                    <th>TAL Doc</th>
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

    <script>
        $(document).ready(function() {
            tblUser = $("#tbl-kpi-monitoring").DataTable({
                processing: true,
                serverSide: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                ajax: {
                    url: "{{ route('employee.kpi-monitoring') }}",
                    type: "GET",
                    data: function(d) {
                        d.organization = $('#organization').val();
                        d.position = $('#position').val();
                        d.level = $('#level').val();
                        d.branch = $('#branch').val();
                        d.status = $('#status').val();
                    }
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
                        data: "employment.organization_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.job_position_name",
                        defaultContent: "--"
                    },
                    {
                        data: "kpis",
                        defaultContent: "--",
                        className: "text-center",
                        searchable: false,
                        orderable: false,
                        mRender: function(data, type, full) {
                            if (data && data.length > 0) {
                                let score = data[0].final_score !== null ? data[0].final_score : 'N/A';
                                let badgeColor = 'secondary';
                                if (score !== 'N/A') {
                                    if (score >= 90) badgeColor = 'success';
                                    else if (score >= 75) badgeColor = 'primary';
                                    else if (score >= 60) badgeColor = 'warning';
                                    else badgeColor = 'danger';
                                    return `<span class="badge badge-${badgeColor}" style="font-size: 14px;">${score}</span>`;
                                }
                                return `<span class="badge badge-secondary">N/A</span>`;
                            }
                            return `<span class="badge badge-secondary">No KPI</span>`;
                        }
                    },
                    {
                        data: "kpis",
                        defaultContent: "--",
                        className: "text-center",
                        searchable: false,
                        orderable: false,
                        mRender: function(data, type, full) {
                            if (data && data.length > 0 && data[0].managerial_file_url) {
                                return `<a href="${data[0].managerial_file_url}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-link"></i> Link</a>`;
                            }
                            return `--`;
                        }
                    },
                    {
                        data: "kpis",
                        defaultContent: "--",
                        className: "text-center",
                        searchable: false,
                        orderable: false,
                        mRender: function(data, type, full) {
                            if (data && data.length > 0 && data[0].tal_file_url) {
                                return `<a href="${data[0].tal_file_url}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-link"></i> Link</a>`;
                            }
                            return `--`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        mRender: function(data, type, full) {
                            return `<a title="View KPI Detail" href="/profile/kpi/${full.employee_id_pk}" class="btn btn-sm btn-info text-white"><i class="fa fa-eye"></i> View</a>`
                        }
                    }
                ],
                order: [
                    [1, "asc"]
                ]
            });

            $('#search').on('keyup', function() {
                tblUser.search(this.value).draw();
            });

            $('#organization, #position, #level, #branch, #status').on('change', function() {
                tblUser.draw();
            });

            $('#filterCollapseToggle').on('click', function() {
                let icon = $('#filterCollapseIcon');
                let label = $('#filterCollapseLabel');
                if ($('#filterFormCollapse').hasClass('show')) {
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    label.text('Show Filter');
                } else {
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    label.text('Hide Filter');
                }
            });
        });
    </script>
@endsection

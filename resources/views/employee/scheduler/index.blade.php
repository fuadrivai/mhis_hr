@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        td img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 8px;
        }

        td .employee-name {
            vertical-align: middle;
        }

        .scheduler-calendar-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
            position: relative;
        }

        .scheduler-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }

        .scheduler-toolbar .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .scheduler-toolbar .period-label {
            font-weight: 700;
            color: #1f2937;
            font-size: 1.05rem;
            letter-spacing: 0.01em;
        }

        .scheduler-toolbar .btn-group .btn {
            min-width: 38px;
            height: 34px;
            border-radius: 8px;
            border-color: #d1d5db;
            background: #fff;
            color: #374151;
        }

        .scheduler-toolbar .calendar-date-picker {
            width: 190px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 7px 10px;
            font-size: 0.9rem;
        }

        .scheduler-toolbar .view-toggle .btn {
            border-radius: 8px;
            min-width: 90px;
            font-size: 0.83rem;
            padding: 7px 12px;
        }

        .scheduler-toolbar .view-toggle .btn.active {
            background: #1f6feb;
            border-color: #1f6feb;
            color: #fff;
        }

        .scheduler-toolbar .toolbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-left: auto;
        }

        #schedulerEmployeeSearch {
            width: 210px;
        }

        .scheduler-toolbar .calendar-filter {
            width: 150px;
            height: 34px;
        }

        .scheduler-loading {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.78);
            color: #1f6feb;
            font-weight: 600;
        }

        .scheduler-loading .fa {
            margin-right: 8px;
        }

        .datepicker-dropdown {
            z-index: 1065 !important;
            min-width: 220px;
        }

        .scheduler-calendar-scroll {
            overflow: auto;
            max-height: 70vh;
            background: #fff;
        }

        #scheduleCalendarTable {
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        #scheduleCalendarTable thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #eef2f7;
            color: #374151;
            padding: 10px 8px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 2;
            min-width: 128px;
        }

        #scheduleCalendarTable thead th:first-child,
        #scheduleCalendarTable tbody td:first-child {
            position: sticky;
            left: 0;
            z-index: 4;
            background: #fff;
            min-width: 260px;
        }

        #scheduleCalendarTable thead th:first-child {
            z-index: 5;
            background: #f8fafc;
        }

        #scheduleCalendarTable tbody td {
            border-right: 1px solid #edf2f7;
            border-bottom: 1px solid #edf2f7;
            padding: 6px;
            vertical-align: top;
            background: #fff;
            min-width: 128px;
        }

        .employee-cell {
            padding: 12px 14px !important;
            min-width: 260px !important;
            background: #fff;
        }

        .employee-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 40px;
        }

        .employee-meta img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #dfe7f5;
        }

        .employee-detail {
            min-width: 0;
        }

        .employee-name {
            font-size: 0.92rem;
            font-weight: 600;
            color: #172033;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .employee-code {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .date-header-day {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .date-header-date {
            display: block;
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .date-header.is-today {
            background: #eaf3ff !important;
        }

        .date-header.is-today .date-header-date {
            color: #1d4ed8;
        }

        .schedule-chip {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 8px 7px;
            text-align: center;
            color: #0d3b7c;
            background: linear-gradient(180deg, #ebf3ff 0%, #dfeeff 100%);
            border-color: #b8d0ff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.15s ease;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .schedule-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(59, 130, 246, 0.1);
        }

        .schedule-chip.dayoff,
        .schedule-chip.holiday {
            background: linear-gradient(180deg, #f5f7fa 0%, #eef2f6 100%);
            border-color: #d8dfe9;
            color: #4b5563;
        }

        .schedule-chip.override {
            background: linear-gradient(180deg, #e9fbf2 0%, #d6f5e4 100%);
            border-color: #8dd5aa;
            color: #166534;
        }

        .schedule-chip .chip-time {
            font-size: 0.76rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .schedule-chip .chip-label {
            font-size: 0.72rem;
            margin-top: 4px;
            line-height: 1.3;
            word-break: break-word;
        }

        .calendar-empty {
            font-size: 0.8rem;
            color: #64748b;
            text-align: center;
            padding: 18px 10px;
        }

        @media (max-width: 768px) {
            .scheduler-toolbar {
                align-items: flex-start;
            }

            .scheduler-toolbar .toolbar-right {
                width: 100%;
                margin-left: 0;
            }

            #schedulerEmployeeSearch,
            .scheduler-toolbar .calendar-date-picker,
            .scheduler-toolbar .calendar-filter {
                width: 100%;
            }

            .schedule-chip {
                min-height: 58px;
            }
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="" role="tabpanel" data-example-id="togglable-tabs">
            <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="index-tab" data-toggle="tab" href="#index-content" role="tab"
                        aria-controls="index-content" aria-selected="true">
                        Index
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="schedule-tab" data-toggle="tab" href="#schedule-content" role="tab"
                        aria-controls="schedule-content" aria-selected="false">
                        Schedule
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="schedulerTabContent">
                <div role="tabpanel" class="tab-pane fade show active" id="index-content" aria-labelledby="index-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="row mb-3" id="indexEmployeeFilters">
                                <div class="col-md-3">
                                    <label for="indexBranchFilter">Branch</label>
                                    <select id="indexBranchFilter" class="form-control form-control-sm index-filter"
                                        data-column="3">
                                        <option value="">All Branches</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="indexOrganizationFilter">Organization</label>
                                    <select id="indexOrganizationFilter" class="form-control form-control-sm index-filter"
                                        data-column="4">
                                        <option value="">All Organizations</option>
                                        @foreach ($organizations as $organization)
                                            <option value="{{ $organization->name }}">{{ $organization->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="indexLevelFilter">Level</label>
                                    <select id="indexLevelFilter" class="form-control form-control-sm index-filter"
                                        data-column="5">
                                        <option value="">All Levels</option>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->name }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="indexPositionFilter">Job Position</label>
                                    <select id="indexPositionFilter" class="form-control form-control-sm index-filter"
                                        data-column="6">
                                        <option value="">All Positions</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->name }}">{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table id="tbl-datatable" class="table table-striped table-bordered table-sm"
                                        style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="checkAll"></th>
                                                <th>Employee name</th>
                                                <th>Employee ID</th>
                                                <th>Branch</th>
                                                <th>Organization</th>
                                                <th>Level</th>
                                                <th>Job Position</th>
                                                <th>Schedule</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <body>
                                            @foreach ($data as $item)
                                                <tr>
                                                    <td><input type="checkbox" class="row-check"
                                                            value="{{ $item->id }}"></td>
                                                    <td><img class="img"
                                                            src="{{ optional($item->personal)->avatar ? asset('storage/' . $item->personal->avatar) : asset('images/user.png') }}"
                                                            alt="profile">
                                                        <span class="employee-name">{{ $item->personal->fullname }}</span>
                                                    </td>
                                                    <td>{{ $item->employment->employee_id }}</td>
                                                    <td>{{ $item->employment->branch->name }}</td>
                                                    <td>{{ $item->employment->organization->name }}</td>
                                                    <td>{{ $item->employment->job_level->name }}</td>
                                                    <td>{{ $item->employment->job_position->name }}</td>
                                                    <td><a class="btn-schedule" href="#"
                                                            data-schedule="{{ optional($item->activeSchedule)->schedule_id }}"
                                                            data-toggle="modal"
                                                            data-target="#modal-shift">{{ optional($item->activeSchedule)->schedule_name }}</a>
                                                    </td>
                                                    <td>
                                                        <button type="button" data-id="{{ $item->id }}"
                                                            class="btn btn-info btn-sm btn-assign">
                                                            Assign</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </body>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="schedule-content" aria-labelledby="schedule-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="scheduler-calendar-card">
                                <div class="scheduler-loading d-none" id="schedulerLoading" aria-live="polite">
                                    <span><i class="fa fa-spinner fa-spin"></i>Loading schedule...</span>
                                </div>
                                <div class="scheduler-toolbar">
                                    <div class="toolbar-left">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-default btn-sm" id="calendarPrevBtn"
                                                title="Previous period">
                                                <i class="fa fa-chevron-left"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm" id="calendarNextBtn"
                                                title="Next period">
                                                <i class="fa fa-chevron-right"></i>
                                            </button>
                                        </div>
                                        {{-- <div class="period-label" id="calendarPeriodLabel">Weekly</div> --}}
                                        <input type="text" id="schedulerDatePicker" class="calendar-date-picker"
                                            placeholder="Select date" readonly>
                                        <div class="btn-group view-toggle" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-default btn-sm active"
                                                data-view="weekly">Weekly</button>
                                            <button type="button" class="btn btn-default btn-sm"
                                                data-view="biweekly">Biweekly</button>
                                            <button type="button" class="btn btn-default btn-sm"
                                                data-view="monthly">Monthly</button>
                                        </div>
                                        <a class="btn btn-success btn-sm" id="calendarExportBtn"
                                            title="Export schedule to Excel">
                                            <i class="fa fa-download text-white"></i>
                                        </a>
                                    </div>

                                    <div class="toolbar-right">
                                        <input type="search" id="schedulerEmployeeSearch" class="calendar-date-picker"
                                            placeholder="Search employee" aria-label="Search employee schedule">
                                        <button type="button" class="btn btn-default btn-sm" id="calendarFilterBtn">
                                            <i class="fa fa-filter"></i> Filters
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" id="calendarAssignBtn">
                                            <i class="fa fa-plus"></i> Assign Shift
                                        </button>
                                    </div>
                                </div>

                                <div class="scheduler-calendar-scroll">
                                    <table id="scheduleCalendarTable" class="table table-bordered table-sm">
                                        <thead id="scheduleCalendarHead"></thead>
                                        <tbody id="scheduleCalendarBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="calendarFilterModal" tabindex="-1" role="dialog"
        aria-labelledby="calendarFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calendarFilterModalLabel">Filter Employees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="calendarBranchFilter">Branch</label>
                        <select id="calendarBranchFilter" class="form-control calendar-filter">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ strtolower($branch->name) }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="calendarOrganizationFilter">Organization</label>
                        <select id="calendarOrganizationFilter" class="form-control calendar-filter">
                            <option value="">All Organizations</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ strtolower($organization->name) }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="calendarLevelFilter">Level</label>
                        <select id="calendarLevelFilter" class="form-control calendar-filter">
                            <option value="">All Levels</option>
                            @foreach ($levels as $level)
                                <option value="{{ strtolower($level->name) }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="calendarPositionFilter">Job Position</label>
                        <select id="calendarPositionFilter" class="form-control calendar-filter">
                            <option value="">All Positions</option>
                            @foreach ($positions as $position)
                                <option value="{{ strtolower($position->name) }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="clearCalendarFilters">Clear</button>
                    <button type="button" class="btn btn-primary" id="applyCalendarFilters">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-data" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-data" autocomplete="OFF" method="POST">
                    @csrf
                    <div id="form-method">
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Assign Schedule</h5>
                        <button type="button" aria-hidden="true" class="close" data-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="employee_id" class="form-control d-none" name="employee_id" />
                        <div class="form-group d-none" id="calendarEmployeePickerGroup">
                            <label for="calendar_employee_picker">Employee</label>
                            <select id="calendar_employee_picker" class="form-control">
                                <option value="" selected>--Select Employee--</option>
                                @foreach ($data as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->personal->fullname }} ({{ $employee->employment->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Work Schedule</label>
                            <select name="schedule_id" id="schedule_id" class="form-control" required>
                                <option value="" disabled selected>--Select Schedule--</option>
                                @foreach ($schedules as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Effective Date</label>
                            <input required type="text" id="effective_date" class="form-control date-picker"
                                name="effective_date" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-shift" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="form-shift">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Shift</h5>
                        <button type="button" aria-hidden="true" class="close" data-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="tbl-shift" class="table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="text-center">Shift name</th>
                                    <th class="text-center">Schedule in</th>
                                    <th class="text-center">Schedule out</th>
                                    <th class="text-center">Break start</th>
                                    <th class="text-center">Break end</th>
                                </tr>
                            </thead>

                            <body></body>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary"> Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="calendarCellModal" tabindex="-1" role="dialog"
        aria-labelledby="calendarCellModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calendarCellModalLabel">Schedule Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <small class="text-muted">Employee</small>
                        <div id="calendarCellEmployee" class="font-weight-bold"></div>
                    </div>
                    <div class="form-group mb-2">
                        <small class="text-muted">Date</small>
                        <div id="calendarCellDate" class="font-weight-bold"></div>
                    </div>
                    <div class="form-group mb-2">
                        <small class="text-muted">Schedule</small>
                        <div id="calendarCellSchedule" class="font-weight-bold"></div>
                    </div>
                    <div class="form-group mb-0">
                        <small class="text-muted">Shift</small>
                        <div id="calendarCellShift" class="font-weight-bold"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="calendarAssignShiftBtn">
                        <i class="fa fa-plus"></i> Assign Shift
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
        let checkedList = new Set();
        let scheduleDetails = [];
        let calendarState = {
            view: 'weekly',
            date: moment().format('YYYY-MM-DD'),
            search: '',
            branch: '',
            organization: '',
            level: '',
            position: ''
        };

        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                lengthMenu: [
                    [10, 25, 50, 100, 500],
                    [10, 25, 50, 100, 500]
                ],
                pageLength: 25,
                ordering: false,
                dom: '<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip',
                buttons: [{
                    text: '<span id="selected-info" class="ml-2 text-white"></span> - Assign Schedule  <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-assign'
                    },
                    className: 'btn-bulk-assign btn btn-success ml-2 btn-sm font-weight-bold d-none',
                    action: function() {
                        $("#employee_id").val(Array.from(checkedList).join(','));
                        $('#calendarEmployeePickerGroup').addClass('d-none');
                        $('#modal-data').modal('show');
                    }
                }]
            });
            $('.index-filter').on('change', function() {
                tbldata.column($(this).data('column')).search($(this).val()).draw();
            });
            $('#checkAll').click(function() {
                let checked = this.checked;
                $('#tbl-datatable .row-check').prop('checked', checked);
                $('#tbl-datatable .row-check').each(function() {
                    let id = $(this).val();
                    if (checked) {
                        checkedList.add(id);
                    } else {
                        checkedList.delete(id);
                    }
                });
                updateInfo();
            });

            $('#tbl-datatable').on('change', '.row-check', function() {
                let checked = this.checked;
                let id = $(this).val();
                if (checked) {
                    checkedList.add(id);
                } else {
                    checkedList.delete(id);
                }
                updateInfo();
            });

            $("#tbl-datatable").on('click', '.btn-assign', function() {
                let id = $(this).data('id');
                $("#employee_id").val(id);
                $('#calendarEmployeePickerGroup').addClass('d-none');
                $("#modal-data").modal('show');
            });

            $("#form-data").submit(function(e) {
                e.preventDefault();
                if (!$("#employee_id").val()) {
                    sweetAlert("Error", "Please select an employee.", "error");
                    return;
                }
                let employeeSchedule = {
                    employee_id: $("#employee_id").val(),
                    schedule_id: $("#schedule_id").val(),
                    schedule_name: $("#schedule_id option:selected").text(),
                    effective_start_date: moment($("#effective_date").val(), "DD MMMM YYYY").format(
                        "YYYY-MM-DD"),
                };
                let url = "{{ route('scheduler.store') }}";
                ajax(employeeSchedule, url, "POST",
                    function(json) {
                        $("#modal-data").modal('hide');
                        sweetAlert("Success", 'Schedule assigned successfully.');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    },
                    function(xhr) {
                        let res = xhr.responseJSON;
                        $("#modal-data").modal('hide');
                        if (res && res.message) {
                            sweetAlert("Error", res.message, "error");
                        } else {
                            sweetAlert("Error", "Something went wrong", "error");
                        }
                    });
            });
            tblShift = $("#tbl-shift").DataTable({
                paging: false,
                searching: false,
                length: false,
                info: false,
                data: scheduleDetails,
                columns: [{
                        data: "shift_name",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.schedule_in",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.schedule_out",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.break_start",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.break_end",
                        defaultContent: "-",
                    },
                ],
                order: [
                    [0, "desc"]
                ],
                columnDefs: [{
                    targets: "_all",
                    className: "text-center align-middle"
                }]
            });

            $("#tbl-datatable").on('click', '.btn-schedule', function() {
                let id = $(this).data('schedule');
                getSchedule(id);
            });

            initSchedulerCalendar();
        });

        function getSchedule(id) {
            let url = `{{ URL::to('setting/schedule/') }}/${id}`;
            ajax({}, url, "GET", function(json) {
                scheduleDetails = json.details;
                reloadJsonDataTable(tblShift, scheduleDetails);
            });
        }

        function updateInfo() {
            let count = checkedList.size;
            let btnAssign = $('.x_panel').find(".btn-bulk-assign");
            btnAssign.toggleClass('d-none', count === 0);
            btnAssign.find('#selected-info').text(count > 0 ? `${count} Employee Selected` : '');
        }

        function initSchedulerCalendar() {
            $('#schedulerDatePicker').datepicker({
                format: 'dd MM yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom left'
            });

            $('#schedulerDatePicker').on('changeDate', function(e) {
                calendarState.date = moment(e.date).format('YYYY-MM-DD');
                loadScheduleCalendar();
            });

            $('.view-toggle .btn').on('click', function() {
                calendarState.view = $(this).data('view');
                $('.view-toggle .btn').removeClass('active');
                $(this).addClass('active');
                loadScheduleCalendar();
            });

            $('#calendarNextBtn').on('click', function() {
                const unit = calendarState.view === 'monthly' ? 'months' : 'days';
                calendarState.date = moment(calendarState.date).add(getCalendarStep(), unit).format('YYYY-MM-DD');
                loadScheduleCalendar();
            });

            $('#calendarPrevBtn').off('click').on('click', function() {
                const unit = calendarState.view === 'monthly' ? 'months' : 'days';
                calendarState.date = moment(calendarState.date).subtract(getCalendarStep(), unit).format(
                    'YYYY-MM-DD');
                loadScheduleCalendar();
            });

            let searchTimer;
            $('#schedulerEmployeeSearch').on('input', function() {
                clearTimeout(searchTimer);
                calendarState.search = $(this).val().trim();
                searchTimer = setTimeout(loadScheduleCalendar, 250);
            });

            $('#calendarFilterBtn').on('click', function() {
                $('#calendarFilterModal').modal('show');
            });

            $('#applyCalendarFilters').on('click', function() {
                calendarState.branch = $('#calendarBranchFilter').val();
                calendarState.organization = $('#calendarOrganizationFilter').val();
                calendarState.level = $('#calendarLevelFilter').val();
                calendarState.position = $('#calendarPositionFilter').val();
                $('#calendarFilterModal').modal('hide');
                loadScheduleCalendar();
            });

            $('#clearCalendarFilters').on('click', function() {
                $('#calendarFilterModal .calendar-filter').val('');
                calendarState.branch = '';
                calendarState.organization = '';
                calendarState.level = '';
                calendarState.position = '';
                $('#calendarFilterModal').modal('hide');
                loadScheduleCalendar();
            });

            $('#calendarAssignBtn').on('click', function() {
                window.location.href =
                    `{{ route('scheduler.override.create') }}?date=${encodeURIComponent(calendarState.date)}`;
            });

            $('#calendar_employee_picker').on('change', function() {
                $('#employee_id').val($(this).val());
            });

            $('#calendarExportBtn').on('click', function() {
                const url =
                    `{{ route('scheduler.calendar.export') }}?date=${encodeURIComponent(calendarState.date)}&view=${encodeURIComponent(calendarState.view)}&search=${encodeURIComponent(calendarState.search)}&branch=${encodeURIComponent(calendarState.branch)}&organization=${encodeURIComponent(calendarState.organization)}&level=${encodeURIComponent(calendarState.level)}&position=${encodeURIComponent(calendarState.position)}`;
                window.location.href = url;
            });

            $('#schedulerDatePicker').datepicker('update', moment(calendarState.date, 'YYYY-MM-DD').toDate());
            loadScheduleCalendar();
        }

        function getCalendarStep() {
            if (calendarState.view === 'monthly') {
                return 1;
            }
            return calendarState.view === 'biweekly' ? 14 : 7;
        }

        function loadScheduleCalendar() {
            const url =
                `{{ route('scheduler.calendar') }}?date=${encodeURIComponent(calendarState.date)}&view=${encodeURIComponent(calendarState.view)}&search=${encodeURIComponent(calendarState.search)}&branch=${encodeURIComponent(calendarState.branch)}&organization=${encodeURIComponent(calendarState.organization)}&level=${encodeURIComponent(calendarState.level)}&position=${encodeURIComponent(calendarState.position)}`;
            $('#schedulerLoading').removeClass('d-none');
            $('.scheduler-toolbar button, .scheduler-toolbar input, .scheduler-toolbar select').prop('disabled', true);
            $.getJSON(url)
                .done(function(response) {
                    renderSchedulerCalendar(response);
                })
                .fail(function() {
                    $('#scheduleCalendarHead').html('');
                    $('#scheduleCalendarBody').html(
                        '<tr><td colspan="10" class="calendar-empty">Unable to load schedule calendar.</td></tr>');
                })
                .always(function() {
                    $('#schedulerLoading').addClass('d-none');
                    $('.scheduler-toolbar button, .scheduler-toolbar input, .scheduler-toolbar select').prop('disabled',
                        false);
                });
        }

        function renderSchedulerCalendar(data) {
            const dates = data.dates || [];
            const employees = data.employees || [];

            $('#calendarPeriodLabel').text(data.range_label || 'Calendar');
            $('#schedulerDatePicker').datepicker('update', moment(data.selected_date, 'YYYY-MM-DD').toDate());

            let headerHtml = '<tr><th class="employee-cell-column">Employee</th>';
            dates.forEach(function(date) {
                const isToday = date.is_today ? 'is-today' : '';
                headerHtml += `
                    <th class="date-header ${isToday}">
                        <span class="date-header-day">${date.day_name}</span>
                        <span class="date-header-date">${date.day_number}</span>
                    </th>
                `;
            });
            headerHtml += '</tr>';
            $('#scheduleCalendarHead').html(headerHtml);

            if (!employees.length) {
                $('#scheduleCalendarBody').html('<tr><td colspan="' + (dates.length + 1) +
                    '" class="calendar-empty">No employees found.</td></tr>');
                return;
            }

            let bodyHtml = '';
            employees.forEach(function(employee) {
                bodyHtml += '<tr>';
                bodyHtml += `
                    <td class="employee-cell">
                        <div class="employee-meta">
                            <img src="${employee.avatar || '/images/user.png'}" alt="${employee.name}">
                            <div class="employee-detail">
                                <div class="employee-name">${employee.name}</div>
                                <div class="employee-code">${employee.employee_code || ''}</div>
                                        <div class="employee-code">${employee.branch || '-'} / ${employee.organization || '-'}</div>
                            </div>
                        </div>
                    </td>
                `;

                (employee.cells || []).forEach(function(cell) {
                    const chipClass = cell.type === 'schedule' ? 'schedule' : cell.type;
                    const cellMeta = cell.schedule_name ? `${cell.schedule_name}` : '';
                    const cellLabel = cell.label || 'dayoff';
                    const buttonTitle = `${employee.name} - ${cell.date}`;

                    bodyHtml += `
                        <td class="calendar-cell">
                            <button type="button" class="schedule-chip ${chipClass}"
                                data-employee-id="${employee.id}"
                                data-employee-name="${employee.name}"
                                data-date="${cell.date}"
                                data-employee-schedule-id="${cell.employee_schedule_id || ''}"
                                data-schedule-id="${cell.schedule_id || ''}"
                                data-schedule-detail-id="${cell.schedule_detail_id || ''}"
                                data-shift-id="${cell.shift_id || ''}"
                                data-schedule-name="${cellMeta}"
                                data-shift-name="${cell.shift_name || ''}"
                                data-time-text="${cell.time_text || '00:00 - 00:00'}"
                                title="${buttonTitle}">
                                <span class="chip-time">${cell.time_text || '00:00 - 00:00'}</span>
                                <span class="chip-label">${cellLabel}</span>
                            </button>
                        </td>
                    `;
                });

                bodyHtml += '</tr>';
            });

            $('#scheduleCalendarBody').html(bodyHtml);

            $('.schedule-chip').on('click', function() {
                const employeeName = $(this).data('employee-name');
                const date = $(this).data('date');
                const scheduleName = $(this).data('schedule-name') || 'N/A';
                const shiftName = $(this).data('shift-name') || 'N/A';
                const timeText = $(this).data('time-text') || '00:00 - 00:00';

                $('#calendarCellEmployee').text(employeeName);
                $('#calendarCellDate').text(moment(date, 'YYYY-MM-DD').format('dddd, DD MMMM YYYY'));
                $('#calendarCellSchedule').text(scheduleName);
                $('#calendarCellShift').text(`${timeText} / ${shiftName}`);
                $('#calendarAssignShiftBtn').data('employee-id', $(this).data('employee-id'));
                $('#calendarAssignShiftBtn').data('date', date);
                $('#calendarCellModal').modal('show');
            });

            $('#calendarAssignShiftBtn').off('click').on('click', function() {
                const employeeId = $(this).data('employee-id');
                const date = $(this).data('date');
                window.location.href =
                    `{{ route('scheduler.override.create') }}?date=${encodeURIComponent(date)}&employee_ids[]=${encodeURIComponent(employeeId)}`;
            });
        }
    </script>
@endsection

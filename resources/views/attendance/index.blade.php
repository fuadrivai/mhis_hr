@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        .attendance-summary-card {
            width: 100%;
            border: 0;
            cursor: pointer;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12  ">
        <div class="x_panel">
            <div class="x_title">
                <h2>Insert Filter</h2>
                <div class="col-4">

                </div>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <div class="form-group">
                            <input type="text" id="filter-date" class="form-control date-picker">
                        </div>
                    </li>
                    <li>
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch" id="branch" class="form-control select2" style="width: 100%">
                                <option value="all">All branch</option>
                                @foreach ($branches as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Organization</label>
                            <select name="organization" id="organization" class="form-control select2" style="width: 100%">
                                <option value="all">All organization</option>
                                @foreach ($organizations as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Position</label>
                            <select name="position" id="position" class="form-control select2" style="width: 100%">
                                <option value="all">All position</option>
                                @foreach ($positions as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Level</label>
                            <select name="level" id="level" class="form-control select2" style="width: 100%">
                                <option value="all">All level</option>
                                @foreach ($levels as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <button type="button" class="x_panel attendance-summary-card" data-summary-type="present">
                    <div class="x_content text-center">
                        <small class="text-muted">Total Present</small>
                        <h3 class="mb-0 text-success" id="summary-present">0</h3>
                    </div>
                </button>
            </div>
            <div class="col-md-4 col-sm-6">
                <button type="button" class="x_panel attendance-summary-card" data-summary-type="absent">
                    <div class="x_content text-center">
                        <small class="text-muted">Total Absent</small>
                        <h3 class="mb-0 text-secondary" id="summary-absent">0</h3>
                    </div>
                </button>
            </div>
            <div class="col-md-4 col-sm-12">
                <button type="button" class="x_panel attendance-summary-card" data-summary-type="late">
                    <div class="x_content text-center">
                        <small class="text-muted">Late Attendance</small>
                        <h3 class="mb-0 text-danger" id="summary-late">0</h3>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="attendance-summary-modal" tabindex="-1" role="dialog"
        aria-labelledby="attendance-summary-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendance-summary-modal-title">Attendance Summary</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="attendance-summary-loading" class="text-center">Loading...</div>
                    <div class="table-responsive d-none" id="attendance-summary-content">
                        <table class="table table-striped table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Position</th>
                                    <th>Schedule In</th>
                                    <th>Clock In</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-summary-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <table id="tbl-location" class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Schedule In</th>
                            <th>Schedule Out</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            function isLateAttendance(checkIn, scheduleIn) {
                if (!checkIn || !scheduleIn) {
                    return false;
                }

                const scheduleParts = String(scheduleIn).match(/(\d{1,2}):(\d{2})(?::(\d{2}))?/);
                const checkInTime = moment(checkIn, 'HH:mm');

                if (!scheduleParts || !checkInTime.isValid()) {
                    return false;
                }

                const scheduleInSeconds = (Number(scheduleParts[1]) * 3600) +
                    (Number(scheduleParts[2]) * 60) + Number(scheduleParts[3] || 0);
                const checkInSeconds = (checkInTime.hours() * 3600) +
                    (checkInTime.minutes() * 60) + checkInTime.seconds();

                return checkInSeconds > scheduleInSeconds;
            }

            function loadAttendanceSummary() {
                $.get('/time/attendance/summary', {
                    date: $('#filter-date').val(),
                    branch: $('#branch').val(),
                    organization: $('#organization').val(),
                    position: $('#position').val(),
                    level: $('#level').val()
                }).done(function(summary) {
                    $('#summary-present').text(summary.present);
                    $('#summary-absent').text(summary.absent);
                    $('#summary-late').text(summary.late);
                });
            }

            $('.attendance-summary-card').on('click', function() {
                const type = $(this).data('summary-type');
                const title = type.charAt(0).toUpperCase() + type.slice(1) + ' Employees';

                $('#attendance-summary-modal-title').text(title);
                $('#attendance-summary-loading').text('Loading...').removeClass('d-none');
                $('#attendance-summary-content').addClass('d-none');
                $('#attendance-summary-modal').modal('show');

                $.get('/time/attendance/summary/' + type, {
                    date: $('#filter-date').val(),
                    branch: $('#branch').val(),
                    organization: $('#organization').val(),
                    position: $('#position').val(),
                    level: $('#level').val()
                }).done(function(employees) {
                    const rows = employees.length ? employees.map(function(employee) {
                            return '<tr><td>' + employee.name + '</td><td>' + employee.branch +
                                '</td><td>' +
                                employee.organization + '</td><td>' + employee.position +
                                '</td><td>' +
                                employee.schedule_in + '</td><td>' + employee.check_in +
                                '</td></tr>';
                        }).join('') :
                        '<tr><td colspan="6" class="text-center">No employees found.</td></tr>';

                    $('#attendance-summary-list').html(rows);
                    $('#attendance-summary-loading').addClass('d-none');
                    $('#attendance-summary-content').removeClass('d-none');
                }).fail(function() {
                    $('#attendance-summary-loading').text('Unable to load employee list.');
                });
            });

            $('#filter-date').val(moment().format('DD MMMM YYYY'))
            tblAttendance = $("#tbl-location").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/time/attendance",
                    type: "GET",
                    data: function(d) {
                        d.date = $('#filter-date').val();
                        d.branch = $('#branch').val();
                        d.organization = $('#organization').val();
                        d.position = $('#position').val();
                        d.level = $('#level').val();
                    }
                },
                columns: [{
                        data: "fullname",
                        defaultContent: "--",
                    },
                    {
                        data: "date",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `${moment(data).format('ddd, DD MMM YYYY')}`
                        }
                    },
                    {
                        data: "shift_name",
                        defaultContent: "--",
                    },
                    {
                        data: "schedule_in",
                        defaultContent: "--",
                    },
                    {
                        data: "schedule_out",
                        defaultContent: "--",
                    },
                    {
                        data: "check_in",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            if (!data) {
                                return '--';
                            }

                            const time = moment(data, 'HH:mm').format('HH:mm');
                            return isLateAttendance(data, full.schedule_in) ?
                                `<span class="text-danger font-weight-bold">${time}</span>` : time;
                        }
                    },
                    {
                        data: "check_out",
                        defaultContent: "--",
                    },
                    {
                        data: "status",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            if (isLateAttendance(full.check_in, full.schedule_in)) {
                                return `<span class="text-danger font-weight-bold">${data || '--'}</span>`;
                            }

                            return data || '--';
                        }
                    },
                    {
                        data: "id",
                        defaultContent: "--",
                        mRender: function() {
                            return `
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Edit</a>
                                    <a class="dropdown-item" href="#">View history log</a>
                                    <a class="dropdown-item" href="#">Lock attendance</a>
                                    <a class="dropdown-item" href="#">Delete</a>
                                </div>
                            </div>`
                        }
                    },
                ]
            })

            $('#filter-date, #branch, #organization, #position, #level').on('change', function() {
                tblAttendance.ajax.reload();
                loadAttendanceSummary();
            });

            loadAttendanceSummary();
        })
    </script>
@endsection

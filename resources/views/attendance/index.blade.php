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

        .attendance-modal .modal-content {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(31, 45, 61, 0.24);
            overflow: hidden;
        }

        .attendance-modal .modal-header {
            align-items: center;
            background: #1f6f8b;
            border: 0;
            color: #fff;
            padding: 16px 20px;
        }

        .attendance-modal .modal-title {
            font-weight: 600;
        }

        .attendance-modal .modal-header .close {
            color: #fff;
            opacity: 0.9;
            text-shadow: none;
        }

        .attendance-modal .modal-body {
            background: #f6f8fa;
            padding: 20px;
        }

        .attendance-modal .table-responsive {
            background: #fff;
            border: 1px solid #dfe5ea;
            border-radius: 6px;
        }

        .attendance-modal .table {
            margin-bottom: 0;
        }

        .attendance-modal .table thead th {
            background: #edf4f6;
            border-bottom: 1px solid #d7e2e6;
            color: #36515c;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .attendance-log-detail-grid {
            background: #fff;
            border: 1px solid #dfe5ea;
            border-radius: 6px;
            margin: 0;
            padding: 4px 16px;
        }

        .attendance-log-detail-grid dt,
        .attendance-log-detail-grid dd {
            border-bottom: 1px solid #edf0f2;
            margin-bottom: 0;
            padding: 11px 0;
        }

        .attendance-log-detail-grid dt {
            color: #60727b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .attendance-log-detail-grid dd {
            color: #253238;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .attendance-log-detail-grid .attendance-photo-row {
            padding: 14px 0;
        }

        .attendance-log-detail-grid .attendance-photo-row dt,
        .attendance-log-detail-grid .attendance-photo-row dd {
            border: 0;
            padding: 0;
        }

        #log-detail-photo {
            background: #f6f8fa;
            border: 1px solid #dfe5ea;
            border-radius: 6px;
            object-fit: cover;
        }

        #log-detail-no-photo {
            color: #7b8b92;
            font-style: italic;
        }

        @media (max-width: 767px) {
            .attendance-modal .modal-dialog {
                margin: 10px;
            }

            .attendance-modal .modal-header,
            .attendance-modal .modal-body {
                padding: 14px;
            }

            .attendance-log-detail-grid {
                padding: 4px 12px;
            }

            .attendance-log-detail-grid dt {
                border-bottom: 0;
                padding-bottom: 2px;
            }

            .attendance-log-detail-grid dd {
                padding-top: 2px;
            }

            .attendance-log-detail-grid .attendance-photo-row dd {
                padding-top: 10px;
            }

            #log-detail-photo {
                max-width: 100% !important;
                height: auto;
            }
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

    <div class="modal fade attendance-modal" id="attendance-summary-modal" tabindex="-1" role="dialog"
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

    <div class="modal fade attendance-modal" id="attendance-log-modal" tabindex="-1" role="dialog"
        aria-labelledby="attendance-log-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendance-log-modal-title"><i class="fa fa-history"></i> Attendance
                        History Log</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tbl-attendance-log" class="table table-striped table-bordered table-sm"
                            style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Type</th>
                                    <th>Clock Date</th>
                                    <th>Clock Time</th>
                                    <th>Has Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade attendance-modal" id="attendance-log-detail-modal" tabindex="-1" role="dialog"
        aria-labelledby="attendance-log-detail-modal-title" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendance-log-detail-modal-title"><i class="fa fa-id-card"></i>
                        Attendance Log Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row attendance-log-detail-grid">
                        <div class="col-12 row attendance-photo-row">
                            <dt class="col-sm-4">Photo</dt>
                            <dd class="col-sm-8">
                                <img id="log-detail-photo" class="img-thumbnail d-none" alt="Attendance photo"
                                    style="max-width: 180px; max-height: 180px;">
                                <span id="log-detail-no-photo">No photo recorded</span>
                            </dd>
                        </div>
                        <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8" id="log-detail-name"></dd>
                        <dt class="col-sm-4">Shift</dt>
                        <dd class="col-sm-8" id="log-detail-shift"></dd>
                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8" id="log-detail-type"></dd>
                        <dt class="col-sm-4">Has Location</dt>
                        <dd class="col-sm-8" id="log-detail-has-location"></dd>
                        <dt class="col-sm-4">Clock Date</dt>
                        <dd class="col-sm-8" id="log-detail-date"></dd>
                        <dt class="col-sm-4">Clock Time</dt>
                        <dd class="col-sm-8" id="log-detail-time"></dd>
                        <dt class="col-sm-4">Latitude</dt>
                        <dd class="col-sm-8" id="log-detail-latitude"></dd>
                        <dt class="col-sm-4">Longitude</dt>
                        <dd class="col-sm-8" id="log-detail-longitude"></dd>
                        <dt class="col-sm-4">Radius</dt>
                        <dd class="col-sm-8" id="log-detail-radius"></dd>
                    </dl>
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
            let attendanceLogTable;

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
                        mRender: function(data) {
                            return `
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Edit</a>
                                    <button type="button" class="dropdown-item btn-view-attendance-logs"
                                        data-attendance-id="${data}">View history log</button>
                                    <a class="dropdown-item" href="#">Lock attendance</a>
                                    <a class="dropdown-item" href="#">Delete</a>
                                </div>
                            </div>`
                        }
                    },
                ]
            })

            $('#tbl-location').on('click', '.btn-view-attendance-logs', function() {
                const attendanceId = $(this).data('attendance-id');

                if ($.fn.DataTable.isDataTable('#tbl-attendance-log')) {
                    $('#tbl-attendance-log').DataTable().destroy();
                }

                attendanceLogTable = $('#tbl-attendance-log').DataTable({
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    searching: false,
                    paging: false,
                    ajax: '/time/attendance/' + attendanceId + '/logs',
                    columns: [{
                            data: 'fullname',
                            defaultContent: '--'
                        },
                        {
                            data: 'type',
                            defaultContent: '--',
                            render: function(data) {
                                return data === 'check_in' ? 'Clock In' : 'Clock Out';
                            }
                        },
                        {
                            data: 'clock_date',
                            defaultContent: '--'
                        },
                        {
                            data: 'time',
                            defaultContent: '--'
                        },
                        {
                            data: 'has_location',
                            defaultContent: false,
                            render: function(data) {
                                return Number(data) === 1 ? 'Yes' : 'No';
                            }
                        },
                        {
                            data: 'id',
                            render: function(data) {
                                return '<button type="button" class="btn btn-info btn-sm btn-log-detail" data-log-id="' +
                                    data +
                                    '"><i class="fa fa-eye"></i> View detail</button>';
                            }
                        }
                    ]
                });

                $('#attendance-log-modal').modal('show');
            });

            $('#tbl-attendance-log').on('click', '.btn-log-detail', function() {
                const logId = $(this).data('log-id');
                const log = attendanceLogTable.rows().data().toArray().find(function(item) {
                    return Number(item.id) === Number(logId);
                });

                if (!log) {
                    return;
                }

                $('#log-detail-name').text(log.fullname || '--');
                $('#log-detail-shift').text(log.shift_name || '--');
                $('#log-detail-type').text(log.type === 'check_in' ? 'Clock In' : 'Clock Out');
                $('#log-detail-has-location').text(Number(log.has_location) === 1 ? 'Yes' : 'No');
                $('#log-detail-date').text(log.clock_date || '--');
                $('#log-detail-time').text(log.time || '--');
                $('#log-detail-latitude').text(log.latitude !== null ? log.latitude : '--');
                $('#log-detail-longitude').text(log.longitude !== null ? log.longitude : '--');
                $('#log-detail-radius').text(log.radius !== null ? log.radius + ' meters' : '--');
                $('#log-detail-photo').attr('src', log.photo || '').toggleClass('d-none', !log.photo);
                $('#log-detail-no-photo').toggleClass('d-none', Boolean(log.photo));
                $('#attendance-log-detail-modal').modal('show');
            });

            $('#filter-date, #branch, #organization, #position, #level').on('change', function() {
                tblAttendance.ajax.reload();
                loadAttendanceSummary();
            });

            loadAttendanceSummary();
        })
    </script>
@endsection

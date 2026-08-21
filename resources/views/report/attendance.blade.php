@extends('layouts.main-layout')
@section('content-class')
    <style>
        .monthly-report-table-wrap {
            overflow: auto;
            max-height: 70vh;
        }

        .monthly-report-table {
            min-width: max-content;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .monthly-report-table th,
        .monthly-report-table td {
            min-width: 58px;
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
        }

        .monthly-report-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f1f5f7;
            color: #36515c;
            font-size: 11px;
            text-transform: uppercase;
        }

        .monthly-report-table .employee-column {
            min-width: 220px;
            text-align: left;
        }

        .monthly-report-table .sticky-column {
            position: sticky;
            left: 0;
            z-index: 1;
            background: #fff;
        }

        .monthly-report-table thead .sticky-column {
            z-index: 3;
            background: #f1f5f7;
        }

        .monthly-report-table .summary-column {
            min-width: 88px;
        }

        .date-header-day {
            display: block;
            color: #7b8b92;
            font-size: 10px;
            font-weight: 500;
            text-transform: none;
        }

        .monthly-report-cell {
            width: 34px;
            height: 30px;
            padding: 0;
            border: 0;
            border-radius: 6px;
            background: transparent;
            font-weight: 700;
        }

        .monthly-report-cell:hover,
        .monthly-report-cell:focus {
            background: #e8f1f5;
            outline: none;
        }

        .monthly-status-p {
            color: #198754;
        }

        .monthly-status-l,
        .monthly-status-a {
            color: #dc3545;
        }

        .monthly-status-to {
            color: #d39e00;
        }

        .monthly-status-off,
        .monthly-status-h {
            color: #6c757d;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group"><label for="month">Month</label><input type="month" class="form-control"
                                id="month" name="month" value="{{ now()->format('Y-m') }}"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="branch-filter">Branch</label>
                            <select id="branch-filter" class="form-control">
                                <option value="all">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="organization-filter">Organization</label>
                            <select id="organization-filter" class="form-control">
                                <option value="all">All Organizations</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="level-filter">Level</label>
                            <select id="level-filter" class="form-control">
                                <option value="all">All Levels</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="position-filter">Position</label>
                            <select id="position-filter" class="form-control">
                                <option value="all">All Positions</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
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
                <h2 id="monthly-report-title">Monthly Employee Attendance</h2>
                <div class="justify-content-end d-flex">
                    <div class="form-group">
                        <input type="search" class="form-control" id="employee-search" placeholder="Search employee name">
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div id="monthly-report-loading" class="text-center py-4">
                    <i class="fa fa-spinner fa-spin mr-1"></i> Loading attendance...
                </div>
                <div class="table-responsive monthly-report-table-wrap d-none" id="monthly-report-content">
                    <table class="table table-striped table-bordered table-sm monthly-report-table">
                        <thead id="monthly-report-head"></thead>
                        <tbody id="monthly-report-body"></tbody>
                    </table>
                </div>
                <div id="monthly-report-empty" class="text-center text-muted py-4 d-none">No employees found.</div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 mb-3">
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="modal fade" id="monthly-detail-modal" tabindex="-1" role="dialog"
        aria-labelledby="monthly-detail-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthly-detail-modal-title">Attendance Detail</h5><button type="button"
                        class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="monthly-detail-loading" class="text-center py-3">Loading...</div>
                    <dl class="row mb-0 d-none" id="monthly-detail-content">
                        <dt class="col-sm-5">Employee</dt>
                        <dd class="col-sm-7" id="detail-employee"></dd>
                        <dt class="col-sm-5">Date</dt>
                        <dd class="col-sm-7" id="detail-date"></dd>
                        <dt class="col-sm-5">Shift</dt>
                        <dd class="col-sm-7" id="detail-shift"></dd>
                        <dt class="col-sm-5">Schedule In</dt>
                        <dd class="col-sm-7" id="detail-schedule-in"></dd>
                        <dt class="col-sm-5">Schedule Out</dt>
                        <dd class="col-sm-7" id="detail-schedule-out"></dd>
                        <dt class="col-sm-5">Clock In</dt>
                        <dd class="col-sm-7" id="detail-clock-in"></dd>
                        <dt class="col-sm-5">Clock Out</dt>
                        <dd class="col-sm-7" id="detail-clock-out"></dd>
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7" id="detail-status"></dd>
                        <dt class="col-sm-5 d-none" id="detail-timeoff-label">Timeoff Type</dt>
                        <dd class="col-sm-7 d-none" id="detail-timeoff-type"></dd>
                    </dl>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-info d-none" id="monthly-view-logs">View
                        Attendance Logs</button><button type="button" class="btn btn-secondary"
                        data-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="monthly-logs-modal" tabindex="-1" role="dialog"
        aria-labelledby="monthly-logs-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthly-logs-modal-title">Attendance Logs</h5><button type="button"
                        class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="monthly-logs-loading" class="text-center py-3">Loading...</div>
                    <div class="table-responsive d-none" id="monthly-logs-content">
                        <table class="table table-striped table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Has Location</th>
                                    <th>Coordinates</th>
                                </tr>
                            </thead>
                            <tbody id="monthly-logs-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        let monthlyDetailState = {
            employeeId: null,
            date: null
        };
        $(document).ready(function() {
            $('#btn-filter').on('click', loadMonthlyReport);
            $('#month, #branch-filter, #organization-filter, #level-filter, #position-filter').on('change',
                loadMonthlyReport);
            $('#employee-search').on('input', filterEmployees);
            $('#monthly-view-logs').on('click', loadMonthlyLogs);
            loadMonthlyReport();
        });

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '-' : value).html();
        }

        function loadMonthlyReport() {
            const month = $('#month').val();
            if (!month) return;
            setMonthlyReportLoading(true);
            $('#monthly-report-content, #monthly-report-empty').addClass('d-none');
            $.getJSON('{{ route('report.attendance.monthly') }}', {
                month: month,
                branch: $('#branch-filter').val(),
                organization: $('#organization-filter').val(),
                level: $('#level-filter').val(),
                position: $('#position-filter').val()
            }).done(renderMonthlyReport).fail(function() {
                sweetAlert('Error', 'Unable to load monthly attendance.', 'error');
            }).always(function() {
                setMonthlyReportLoading(false);
            });
        }

        function setMonthlyReportLoading(isLoading) {
            $('#monthly-report-loading').toggleClass('d-none', !isLoading);
            $('#month, #branch-filter, #organization-filter, #level-filter, #position-filter, #btn-filter')
                .prop('disabled', isLoading);
        }

        function renderMonthlyReport(report) {
            $('#monthly-report-title').text('Monthly Employee Attendance - ' + report.month);
            let head =
                '<tr><th class="employee-column sticky-column">Employee</th><th class="summary-column">Total Late</th><th class="summary-column">Total Absent</th><th class="summary-column">Total Timeoff</th>';
            report.dates.forEach(function(date) {
                head += '<th>' + date.day + '<span class="date-header-day">' + escapeHtml(date.weekday) +
                    '</span></th>';
            });
            $('#monthly-report-head').html(head + '</tr>');
            if (!report.employees.length) {
                $('#monthly-report-empty').removeClass('d-none');
                return;
            }
            let body = '';
            report.employees.forEach(function(employee) {
                body += '<tr><td class="employee-column sticky-column font-weight-bold">' + escapeHtml(employee
                        .name) + '</td><td>' + escapeHtml(employee.late) + '</td><td>' + employee.absent +
                    '</td><td>' + employee.timeoff + '</td>';
                employee.cells.forEach(function(cell) {
                    const label = cell.status === 'P' ? 'Present' : cell.label;
                    body += '<td><button type="button" class="monthly-report-cell monthly-status-' + cell
                        .status.toLowerCase() + '" title="' + escapeHtml(label) + '" data-employee-id="' +
                        employee.id + '" data-date="' + cell.date + '">' + escapeHtml(cell.status) +
                        '</button></td>';
                });
                body += '</tr>';
            });
            $('#monthly-report-body').html(body);
            $('#monthly-report-content').removeClass('d-none');
            filterEmployees();
        }

        function filterEmployees() {
            const search = String($('#employee-search').val() || '').trim().toLowerCase();
            $('#monthly-report-body tr').each(function() {
                const employeeName = $(this).find('.employee-column').text().toLowerCase();
                $(this).toggle(!search || employeeName.includes(search));
            });
        }
        $('#monthly-report-body').on('click', '.monthly-report-cell', function() {
            monthlyDetailState.employeeId = $(this).data('employee-id');
            monthlyDetailState.date = $(this).data('date');
            $('#monthly-detail-loading').removeClass('d-none');
            $('#monthly-detail-content').addClass('d-none');
            $('#monthly-view-logs').addClass('d-none');
            $('#monthly-detail-modal').modal('show');
            $.getJSON('/report/attendance/monthly/' + monthlyDetailState.employeeId + '/detail', {
                date: monthlyDetailState.date
            }).done(function(detail) {
                $('#monthly-detail-modal-title').text('Attendance Detail - ' + detail.date);
                $('#detail-employee').text(detail.employee);
                $('#detail-date').text(detail.date);
                $('#detail-shift').text(detail.shift);
                $('#detail-schedule-in').text(detail.schedule_in);
                $('#detail-schedule-out').text(detail.schedule_out);
                $('#detail-clock-in').text(detail.clock_in);
                $('#detail-clock-out').text(detail.clock_out);
                $('#detail-status').text(detail.status);
                $('#detail-timeoff-label, #detail-timeoff-type').toggleClass('d-none', !detail
                    .timeoff_type);
                $('#detail-timeoff-type').text(detail.timeoff_type || '');
                $('#monthly-view-logs').toggleClass('d-none', !detail.attendance_id);
                $('#monthly-detail-content').removeClass('d-none');
            }).fail(function() {
                $('#detail-status').text('Unable to load details.');
                $('#monthly-detail-content').removeClass('d-none');
            }).always(function() {
                $('#monthly-detail-loading').addClass('d-none');
            });
        });

        function loadMonthlyLogs() {
            $('#monthly-logs-loading').removeClass('d-none');
            $('#monthly-logs-content').addClass('d-none');
            $('#monthly-logs-modal-title').text('Attendance Logs - ' + $('#detail-employee').text() + ' - ' + $(
                '#detail-date').text());
            $('#monthly-logs-modal').modal('show');
            $.getJSON('/report/attendance/monthly/' + monthlyDetailState.employeeId + '/logs', {
                date: monthlyDetailState.date
            }).done(function(logs) {
                const rows = logs.length ? logs.map(function(log) {
                    const coordinates = log.latitude !== null && log.longitude !== null ? escapeHtml(log
                        .latitude + ', ' + log.longitude) : '-';
                    return '<tr><td>' + escapeHtml(log.time) + '</td><td>' + escapeHtml(log.type) +
                        '</td><td>' + (Number(log.has_location) ? 'Yes' : 'No') + '</td><td>' +
                        coordinates + '</td></tr>';
                }).join('') : '<tr><td colspan="4" class="text-center">No attendance logs.</td></tr>';
                $('#monthly-logs-list').html(rows);
                $('#monthly-logs-content').removeClass('d-none');
            }).always(function() {
                $('#monthly-logs-loading').addClass('d-none');
            });
        }
    </script>
@endsection

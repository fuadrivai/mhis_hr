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
            width: 54px;
            min-height: 64px;
            padding: 0;
            border: 0;
            border-radius: 6px;
            background: transparent;
            font-weight: 700;
            line-height: 1.25;
        }

        .monthly-report-cell .monthly-clock-time {
            display: block;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
        }

        .monthly-report-cell .monthly-clock-status {
            display: block;
            margin-top: 2px;
        }

        .monthly-report-cell .monthly-clock-missing {
            color: #dc3545;
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
        <div class="" role="tabpanel" data-example-id="togglable-tabs">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" role="tablist" style="border-bottom: 2px solid #e0e0e0;">
                <li class="nav-item">
                    <a class="nav-link active" id="index-tab" data-toggle="tab" href="#index-pane" role="tab"
                        aria-controls="index-pane" aria-selected="true">
                        <i class="fa fa-list"></i> Index
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="timeoff-issue-tab" data-toggle="tab" href="#timeoff-issue-pane" role="tab"
                        aria-controls="timeoff-issue-pane" aria-selected="false">
                        <i class="fa fa-exclamation-triangle"></i> Timeoff Issue
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="index-pane" class="tab-pane fade show active" role="tabpanel" aria-labelledby="index-tab">

                    <div class="x_panel">
                        <div class="x_title">
                            <div class="d-flex align-items-center">
                                <h2 id="monthly-report-title" class="mb-0">Monthly Employee Attendance</h2>
                                <button type="button" class="btn btn-success btn-sm ml-3" id="export-monthly-report"
                                    disabled>
                                    <i class="fa fa-file-excel-o"></i> Export to Excel
                                </button>
                            </div>
                            <div class="justify-content-end d-flex">
                                <div class="form-group">
                                    <input type="search" class="form-control" id="employee-search"
                                        placeholder="Search employee name">
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
                            <div id="monthly-report-empty" class="text-center text-muted py-4 d-none">No employees
                                found.
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
                <div id="timeoff-issue-pane" class="tab-pane fade" role="tabpanel" aria-labelledby="timeoff-issue-tab">

                    <div class="x_panel">
                        <div class="x_title">
                            <div class="d-flex align-items-center">
                                <h2>Attendance Exception Report</h2>
                                <button type="button" class="btn btn-success btn-sm ml-3" id="export-exception-report"
                                    disabled>
                                    <i class="fa fa-file-excel-o"></i> Export to Excel
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div id="exception-period" class="alert alert-info d-none" role="alert"></div>
                            <div id="exception-report-loading" class="text-center py-4">
                                <i class="fa fa-spinner fa-spin mr-1"></i> Loading exceptions...
                            </div>
                            <div class="table-responsive monthly-report-table-wrap d-none" id="exception-report-content">
                                <table class="table table-striped table-bordered table-sm mb-0 monthly-report-table"
                                    id="exception-report-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 40px;">No</th>
                                            <th style="min-width: 100px;">Employee ID</th>
                                            <th style="min-width: 150px;">Name</th>
                                            <th style="min-width: 150px;">Branch</th>
                                            <th style="min-width: 150px;">Organization</th>
                                            <th style="min-width: 100px;">Level</th>
                                            <th style="min-width: 150px;">Position</th>
                                            <th style="min-width: 120px;">Date</th>
                                            <th style="min-width: 200px;">Issue</th>
                                            <th style="min-width: 100px;">Time</th>
                                            <th style="min-width: 150px;">Duration</th>
                                            <th style="min-width: 80px;">Time Off</th>
                                        </tr>
                                    </thead>
                                    <tbody id="exception-report-body"></tbody>
                                </table>
                            </div>
                            <div id="exception-report-empty" class="text-center text-muted py-4 d-none">No attendance
                                exceptions found for the selected period.</div>
                        </div>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="no-clock-modal" tabindex="-1" role="dialog" aria-labelledby="no-clock-modal-title"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="no-clock-modal-title">Missing Clock In/Out</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="no-clock-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals (outside tab-content) -->
    <div class="modal fade" id="no-clock-modal" tabindex="-1" role="dialog" aria-labelledby="no-clock-modal-title"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="no-clock-modal-title">Missing Clock In/Out</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="no-clock-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
        let monthlyNoClockDates = {};
        let monthlySummaryDates = {
            late: {},
            absent: {},
            timeoff: {}
        };
        let monthlyReportData = null;
        let exceptionReportData = null;
        $(document).ready(function() {
            $('#btn-filter').on('click', function() {
                loadMonthlyReport();
                if ($('#timeoff-issue-tab').hasClass('active')) {
                    loadExceptionReport();
                }
            });
            $('#month, #branch-filter, #organization-filter, #level-filter, #position-filter').on('change', function() {
                loadMonthlyReport();
                if ($('#timeoff-issue-tab').hasClass('active')) {
                    loadExceptionReport();
                }
            });
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
            $('#export-monthly-report').prop('disabled', isLoading || !monthlyReportData);
        }

        function summaryButton(type, employee, value) {
            const dates = employee[type + '_dates'] || [];
            if (!dates.length) {
                return escapeHtml(value);
            }

            return '<button type="button" class="btn btn-link p-0 summary-details" data-summary-type="' + type +
                '" data-employee-name="' + escapeHtml(employee.name) + '" data-employee-id="' + employee.id + '">' +
                escapeHtml(value) + '</button>';
        }

        function renderMonthlyReport(report) {
            monthlyReportData = report;
            monthlyNoClockDates = {};
            monthlySummaryDates = {
                late: {},
                absent: {},
                timeoff: {}
            };
            $('#monthly-report-title').text('Monthly Employee Attendance - ' + report.month);
            let head =
                '<tr><th class="employee-column sticky-column">Employee</th><th class="summary-column">Late</th><th class="summary-column">Absent</th><th class="summary-column">Timeoff</th><th class="summary-column">No clock in/out</th>';
            report.dates.forEach(function(date) {
                head += '<th>' + date.day + '<span class="date-header-day">' + escapeHtml(date.weekday) +
                    '</span></th>';
            });
            $('#monthly-report-head').html(head + '</tr>');
            if (!report.employees.length) {
                $('#export-monthly-report').prop('disabled', true);
                $('#monthly-report-empty').removeClass('d-none');
                return;
            }
            $('#export-monthly-report').prop('disabled', false);
            let body = '';
            report.employees.forEach(function(employee) {
                monthlyNoClockDates[employee.id] = employee.no_clock_dates || [];
                monthlySummaryDates.late[employee.id] = employee.late_dates || [];
                monthlySummaryDates.absent[employee.id] = employee.absent_dates || [];
                monthlySummaryDates.timeoff[employee.id] = employee.timeoff_dates || [];
                body += '<tr><td class="employee-column sticky-column font-weight-bold">' + escapeHtml(employee
                        .name) + '</td><td>' + summaryButton('late', employee, employee.late) + '</td><td>' +
                    summaryButton('absent', employee, employee.absent) + '</td><td>' + summaryButton('timeoff',
                        employee, employee.timeoff) + '</td><td>';
                if (employee.no_clock_in_out > 0) {
                    body +=
                        '<button type="button" class="btn btn-link p-0 summary-details" data-summary-type="no-clock" data-employee-name="' +
                        escapeHtml(employee.name) + '" data-employee-id="' + employee.id + '">' + employee
                        .no_clock_in_out + '</button>';
                } else {
                    body += '0';
                }
                body += '</td>';
                employee.cells.forEach(function(cell) {
                    const displayStatus = cell.display_status || cell.status;
                    const label = cell.status === 'P' ? 'Present' : (cell.status === 'TO' ? cell
                        .timeoff_type : cell.label);
                    const hasClockData = cell.clock_in !== '-' || cell.clock_out !== '-';
                    const showTimes = hasClockData || !['A', 'OFF', 'TO', 'H'].includes(cell.status);
                    const clockIn = cell.clock_in === '-' ? 'X' : cell.clock_in;
                    const clockOut = cell.clock_out === '-' ? 'X' : cell.clock_out;
                    const missingClockIn = clockIn === 'X';
                    const missingClockOut = clockOut === 'X';
                    const missingDescription = missingClockIn && missingClockOut ?
                        'Missing clock in and clock out' : missingClockIn ? 'Missing clock in' :
                        'Missing clock out';
                    const cellContent = showTimes ? '<span class="monthly-clock-time" title="' + (
                            missingClockIn || missingClockOut ? missingDescription :
                            'Clock in and clock out') + '">' + escapeHtml(clockIn) + ' - ' + escapeHtml(
                            clockOut) + (missingClockIn || missingClockOut ?
                            ' <span class="monthly-clock-missing">!</span>' : '') +
                        '</span><span class="monthly-clock-status">' + escapeHtml(displayStatus) +
                        '</span>' : '<span class="monthly-clock-status">' + escapeHtml(displayStatus) +
                        '</span>';
                    body += '<td><button type="button" class="monthly-report-cell monthly-status-' + cell
                        .status.toLowerCase() + '" title="' + escapeHtml(label) + '" data-employee-id="' +
                        employee.id + '" data-date="' + cell.date + '">' + cellContent + '</button></td>';
                });
                body += '</tr>';
            });
            $('#monthly-report-body').html(body);
            $('#monthly-report-content').removeClass('d-none');
            filterEmployees();
        }

        function exportMonthlyReport() {
            if (!monthlyReportData) {
                return;
            }

            const report = monthlyReportData;
            let table =
                '<table><thead><tr><th>Employee</th><th>Total Late</th><th>Total Absent</th><th>Total Timeoff</th><th>No clock in/out</th>';
            report.dates.forEach(function(date) {
                table += '<th>' + escapeHtml(date.day + ' ' + date.weekday) + '</th>';
            });
            table += '</tr></thead><tbody>';

            report.employees.forEach(function(employee) {
                table += '<tr><td>' + escapeHtml(employee.name) + '</td><td>' + escapeHtml(employee.late) +
                    '</td><td>' + employee.absent + '</td><td>' + employee.timeoff + '</td><td>' + employee
                    .no_clock_in_out + '</td>';
                employee.cells.forEach(function(cell) {
                    const displayStatus = cell.display_status || cell.status;
                    const hasClockData = cell.clock_in !== '-' || cell.clock_out !== '-';
                    const showTimes = hasClockData || !['A', 'OFF', 'TO', 'H'].includes(cell.status);
                    const value = showTimes ? cell.clock_in + ' - ' + cell.clock_out + ' / ' +
                        displayStatus : displayStatus;
                    table += '<td>' + escapeHtml(value) + '</td>';
                });
                table += '</tr>';
            });
            table += '</tbody></table>';

            const blob = new Blob(['\ufeff', table], {
                type: 'application/vnd.ms-excel'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'monthly-attendance-' + report.month.replace(/\s+/g, '-').toLowerCase() + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        $('#export-monthly-report').on('click', exportMonthlyReport);

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

        $('#monthly-report-body').on('click', '.summary-details', function() {
            const type = $(this).data('summary-type');
            const employeeId = $(this).data('employee-id');
            const dates = type === 'no-clock' ? monthlyNoClockDates[employeeId] || [] : monthlySummaryDates[type][
                employeeId
            ] || [];

            const rows = dates.map(function(item) {
                return '<tr><td>' + escapeHtml(moment(item.date).format('dddd, DD MMM YYYY')) +
                    '</td><td>' + escapeHtml(item.clock_in === '-' ? 'X' : item.clock_in) + '</td><td>' +
                    escapeHtml(item.clock_out === '-' ? 'X' : item.clock_out) + '</td><td>' + escapeHtml(
                        item.display_status || item.status) + '</td></tr>';
            }).join('');
            const titles = {
                late: 'Late Attendance',
                absent: 'Absent Attendance',
                timeoff: 'Timeoff Dates',
                'no-clock': 'Missing Clock In/Out'
            };
            $('#no-clock-modal-title').text(titles[type] + ' - ' + $(this).data('employee-name'));
            $('#no-clock-list').html(rows || '<tr><td colspan="4" class="text-center">No dates found.</td></tr>');
            $('#no-clock-modal').modal('show');
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

        // Exception Report Handler
        function loadExceptionReport() {
            const month = $('#month').val();
            if (!month) return;
            setExceptionReportLoading(true);
            $('#exception-report-content, #exception-report-empty, #exception-period').addClass('d-none');
            $.getJSON('{{ route('report.attendance.exception') }}', {
                month: month,
                branch: $('#branch-filter').val(),
                organization: $('#organization-filter').val(),
                level: $('#level-filter').val(),
                position: $('#position-filter').val()
            }).done(renderExceptionReport).fail(function() {
                sweetAlert('Error', 'Unable to load exception report.', 'error');
            }).always(function() {
                setExceptionReportLoading(false);
            });
        }

        function setExceptionReportLoading(isLoading) {
            $('#exception-report-loading').toggleClass('d-none', !isLoading);
            $('#export-exception-report').prop('disabled', isLoading || !exceptionReportData);
        }

        function renderExceptionReport(report) {
            exceptionReportData = report;
            $('#exception-period').text('Period: ' + report.period).removeClass('d-none');

            if (!report.exceptions || report.exceptions.length === 0) {
                $('#export-exception-report').prop('disabled', true);
                $('#exception-report-empty').removeClass('d-none');
                $('#exception-report-content').addClass('d-none');
                return;
            }

            $('#export-exception-report').prop('disabled', false);
            let body = '';
            report.exceptions.forEach(function(exception, index) {
                body += '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(exception.employee_id) + '</td>' +
                    '<td>' + escapeHtml(exception.employee_name) + '</td>' +
                    '<td>' + escapeHtml(exception.branch) + '</td>' +
                    '<td>' + escapeHtml(exception.organization) + '</td>' +
                    '<td>' + escapeHtml(exception.level) + '</td>' +
                    '<td>' + escapeHtml(exception.position) + '</td>' +
                    '<td>' + escapeHtml(exception.date) + '</td>' +
                    '<td>' + escapeHtml(exception.issue) + '</td>' +
                    '<td>' + escapeHtml(exception.time) + '</td>' +
                    '<td>' + escapeHtml(exception.duration) + '</td>' +
                    '<td>' + escapeHtml(exception.has_timeoff) + '</td>' +
                    '</tr>';
            });

            $('#exception-report-body').html(body);
            $('#exception-report-content').removeClass('d-none');
        }

        function exportExceptionReport() {
            if (!exceptionReportData || !exceptionReportData.exceptions || !exceptionReportData.exceptions.length) {
                return;
            }

            const report = exceptionReportData;
            let table = '<table><thead><tr>' +
                '<th>No</th><th>Employee ID</th><th>Name</th><th>Branch</th>' +
                '<th>Organization</th><th>Level</th><th>Position</th><th>Date</th>' +
                '<th>Issue</th><th>Time</th><th>Duration</th><th>Time Off</th>' +
                '</tr></thead><tbody>';

            report.exceptions.forEach(function(exception, index) {
                table += '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(exception.employee_id) + '</td>' +
                    '<td>' + escapeHtml(exception.employee_name) + '</td>' +
                    '<td>' + escapeHtml(exception.branch) + '</td>' +
                    '<td>' + escapeHtml(exception.organization) + '</td>' +
                    '<td>' + escapeHtml(exception.level) + '</td>' +
                    '<td>' + escapeHtml(exception.position) + '</td>' +
                    '<td>' + escapeHtml(exception.date) + '</td>' +
                    '<td>' + escapeHtml(exception.issue) + '</td>' +
                    '<td>' + escapeHtml(exception.time) + '</td>' +
                    '<td>' + escapeHtml(exception.duration) + '</td>' +
                    '<td>' + escapeHtml(exception.has_timeoff) + '</td>' +
                    '</tr>';
            });
            table += '</tbody></table>';

            const blob = new Blob(['\ufeff', table], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'attendance-exceptions-' + report.period.replace(/\s+|-/g, '-').toLowerCase() + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        $('#export-exception-report').on('click', exportExceptionReport);

        // Load exception report when tab is clicked
        $('#timeoff-issue-tab').on('click', function() {
            if ($('#exception-report-body').html() === '') {
                loadExceptionReport();
            }
        });
    </script>
@endsection

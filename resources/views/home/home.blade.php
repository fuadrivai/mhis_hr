@extends('layouts.main-layout')

@if(auth()->user()->hasRole('admin'))
@section('content-class')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
@endsection

@section('content-child')
    <div class="hr-dashboard">

        <div class="dash-shell">
            <div class="dash-top">
                <div class="dash-title">
                    <p>Snapshot for {{ \Carbon\Carbon::now()->format('l, F j, Y') }}</p>
                </div>
                <div class="dash-pill">
                    Last refreshed: {{ \Carbon\Carbon::now()->format('h:i A') }}
                </div>
            </div>

            <div class="kpi-grid" style="margin-bottom: 20px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="kpi-card accent-a">
                    <span class="kpi-label">Total Employees</span>
                    <p class="kpi-value">{{ $totalActive }}</p>
                    <div class="kpi-trend up">+{{ $newThisMonth }} this month</div>
                </div>
                <div class="kpi-card accent-b">
                    <span class="kpi-label">Present Today</span>
                    <p class="kpi-value">{{ $presentToday }}</p>
                    <div class="kpi-trend up">{{ $presentPercentage }}% attendance</div>
                </div>
            </div>

            @php
                $colors = ['#00aadd', '#ff9900', '#6633cc', '#dc3912', '#109618', '#990099', '#3b3eac', '#0099c6', '#dd4477', '#66aa00'];
            @endphp

            <div class="row">
                <!-- Employment Status -->
                <div class="col-md-3">
                    <div class="panel" style="padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff; height: 100%;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 style="margin: 0; font-size: 14px; font-weight: bold;">Employment Status <i class="fa fa-info-circle text-muted"></i></h5>
                            <i class="fa fa-ellipsis-v text-muted"></i>
                        </div>
                        <div id="emp_status_chart" style="width: 100%; height: 50px;"></div>
                        <div style="margin-top: 15px;">
                            <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px 0; font-weight: bold;">Total</td>
                                    <td style="padding: 8px 0; text-align: right; color: #666;">{{ $totalActive }}</td>
                                    <td style="padding: 8px 0; text-align: right; width: 40px;"></td>
                                </tr>
                                @foreach($empStatusData as $index => $data)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px 0;">
                                        <span style="display:inline-block; width:8px; height:8px; background:{{ $colors[$index % count($colors)] }}; margin-right:8px; border-radius:1px;"></span>
                                        <strong>{{ $data['name'] }}</strong>
                                    </td>
                                    <td style="padding: 8px 0; text-align: right; color: #666;">{{ $data['count'] }}</td>
                                    <td style="padding: 8px 0; text-align: right; color: #999;">{{ $data['percentage'] }}%</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <div style="margin-top: 15px; font-size: 12px; color: #999;">Filter <i class="fa fa-caret-down"></i></div>
                    </div>
                </div>

                <!-- Length of Service -->
                <div class="col-md-3">
                    <div class="panel" style="padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff; height: 100%;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 style="margin: 0; font-size: 14px; font-weight: bold;">Length of Service <i class="fa fa-info-circle text-muted"></i></h5>
                            <i class="fa fa-ellipsis-v text-muted"></i>
                        </div>
                        <div id="length_of_service_chart" style="width: 100%; height: 250px;"></div>
                        <div style="margin-top: 15px; font-size: 12px; color: #999;">Filter <i class="fa fa-caret-down"></i></div>
                    </div>
                </div>

                <!-- Job Level -->
                <div class="col-md-3">
                    <div class="panel" style="padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff; height: 100%;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 style="margin: 0; font-size: 14px; font-weight: bold;">Job Level <i class="fa fa-info-circle text-muted"></i></h5>
                            <i class="fa fa-ellipsis-v text-muted"></i>
                        </div>
                        <div id="job_level_chart" style="width: 100%; height: 50px;"></div>
                        <div style="margin-top: 15px;">
                            <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px 0; font-weight: bold;">Total</td>
                                    <td style="padding: 8px 0; text-align: right; color: #666;">{{ $totalActive }}</td>
                                    <td style="padding: 8px 0; text-align: right; width: 40px;"></td>
                                </tr>
                                @foreach($jobLevelData as $index => $data)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px 0;">
                                        <span style="display:inline-block; width:8px; height:8px; background:{{ $colors[$index % count($colors)] }}; margin-right:8px; border-radius:1px;"></span>
                                        <strong>{{ $data['name'] }}</strong>
                                    </td>
                                    <td style="padding: 8px 0; text-align: right; color: #666;">{{ $data['count'] }}</td>
                                    <td style="padding: 8px 0; text-align: right; color: #999;">{{ $data['percentage'] }}%</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <div style="margin-top: 15px; font-size: 12px; color: #999;">Filter <i class="fa fa-caret-down"></i></div>
                    </div>
                </div>

                <!-- Gender Diversity -->
                <div class="col-md-3">
                    <div class="panel" style="padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff; height: 100%;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 style="margin: 0; font-size: 14px; font-weight: bold;">Gender Diversity <i class="fa fa-info-circle text-muted"></i></h5>
                            <i class="fa fa-ellipsis-v text-muted"></i>
                        </div>
                        <div style="position: relative;">
                            <div id="gender_diversity_chart" style="width: 100%; height: 180px;"></div>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 16px; color: #666;">
                                {{ $totalActive }}
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                                @foreach($genderData as $index => $data)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px 0;">
                                        <span style="display:inline-block; width:8px; height:8px; background:{{ $data['name'] == 'Female' ? '#00aadd' : '#0055aa' }}; margin-right:8px; border-radius:1px;"></span>
                                        <strong>{{ $data['name'] }}</strong>
                                    </td>
                                    <td style="padding: 8px 0; text-align: right; color: #666;">{{ $data['count'] }}</td>
                                    <td style="padding: 8px 0; text-align: right; color: #999;">{{ $data['percentage'] }}%</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <div style="margin-top: 15px; font-size: 12px; color: #999;">Filter <i class="fa fa-caret-down"></i></div>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <div class="panel" style="padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 style="margin: 0; font-size: 14px; font-weight: bold;">Company Schedule <i class="fa fa-calendar text-muted"></i></h5>
                        </div>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>

@section('content-script')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            if(calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 600,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    events: function(fetchInfo, successCallback, failureCallback) {
                        $.ajax({
                            url: 'https://calendar.mutiaraharapan.sch.id/api/schedule?branch=1,2,3&category=internal,leadership',
                            type: 'GET',
                            success: function(response) {
                                // Fallback mapping in case of various JSON formats from the API
                                var events = response.data || response;
                                if (!Array.isArray(events)) {
                                    events = [];
                                }
                                var parsedEvents = events.map(function(evt) {
                                    return {
                                        title: evt.subject || evt.title || evt.name || evt.event_name || 'Scheduled Event',
                                        start: evt.starttime || evt.start_date || evt.start || evt.date || evt.start_time,
                                        end: evt.endtime || evt.end_date || evt.end || evt.end_time,
                                        color: evt.color || '#00aadd',
                                        description: evt.description || '',
                                        allDay: evt.is_all_day !== undefined ? evt.is_all_day : (evt.all_day !== undefined ? evt.all_day : true)
                                    };
                                });
                                successCallback(parsedEvents);
                            },
                            error: function(err) {
                                console.error('Failed to load schedule', err);
                                failureCallback(err);
                            }
                        });
                    },
                    eventClick: function(info) {
                        if (info.event.extendedProps.description) {
                            alert(info.event.title + '\n' + info.event.extendedProps.description);
                        }
                    }
                });
                calendar.render();
            }
        });

        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var colors = ['#00aadd', '#ff9900', '#6633cc', '#dc3912', '#109618', '#990099', '#3b3eac', '#0099c6', '#dd4477', '#66aa00'];

            // Employment Status - Stacked Horizontal Bar
            var empStatusData = {!! json_encode($empStatusData) !!};
            var empStatusChartData = new google.visualization.DataTable();
            empStatusChartData.addColumn('string', 'Category');
            var empCols = [];
            for(var i=0; i<empStatusData.length; i++){
                empStatusChartData.addColumn('number', empStatusData[i].name);
                empCols.push(empStatusData[i].count);
            }
            empStatusChartData.addRows([ ['Total'].concat(empCols) ]);

            var empStatusOptions = {
                legend: { position: 'none' },
                isStacked: 'percent',
                colors: colors,
                hAxis: { textPosition: 'out', baselineColor: 'none', gridlines: {color: 'transparent'}, format: '#%' },
                vAxis: { textPosition: 'none', baselineColor: 'none', gridlines: {color: 'transparent'} },
                chartArea: { width: '100%', height: '20px', top: 10 }
            };
            var empStatusChart = new google.visualization.BarChart(document.getElementById('emp_status_chart'));
            empStatusChart.draw(empStatusChartData, empStatusOptions);


            // Length of Service - Column Chart
            var lengthOfServiceData = {!! json_encode($lengthOfService) !!};
            var lengthOfServiceChartData = new google.visualization.DataTable();
            lengthOfServiceChartData.addColumn('string', 'Range');
            lengthOfServiceChartData.addColumn('number', 'Staff');
            lengthOfServiceChartData.addRows(lengthOfServiceData);

            var lengthOfServiceOptions = {
                legend: { position: 'none' },
                bar: { groupWidth: "70%" },
                colors: ['#0066cc'],
                vAxis: { minValue: 0 },
                chartArea: { width: '80%', height: '70%', top: 10 }
            };
            var lengthOfServiceChart = new google.visualization.ColumnChart(document.getElementById('length_of_service_chart'));
            lengthOfServiceChart.draw(lengthOfServiceChartData, lengthOfServiceOptions);


            // Job Level - Stacked Horizontal Bar
            var jobLevelData = {!! json_encode($jobLevelData) !!};
            var jobLevelChartData = new google.visualization.DataTable();
            jobLevelChartData.addColumn('string', 'Category');
            var jobCols = [];
            for(var i=0; i<jobLevelData.length; i++){
                jobLevelChartData.addColumn('number', jobLevelData[i].name);
                jobCols.push(jobLevelData[i].count);
            }
            jobLevelChartData.addRows([ ['Total'].concat(jobCols) ]);

            var jobLevelOptions = {
                legend: { position: 'none' },
                isStacked: 'percent',
                colors: colors,
                hAxis: { textPosition: 'out', baselineColor: 'none', gridlines: {color: 'transparent'}, format: '#%' },
                vAxis: { textPosition: 'none', baselineColor: 'none', gridlines: {color: 'transparent'} },
                chartArea: { width: '100%', height: '20px', top: 10 }
            };
            var jobLevelChart = new google.visualization.BarChart(document.getElementById('job_level_chart'));
            jobLevelChart.draw(jobLevelChartData, jobLevelOptions);


            // Gender Diversity - Donut Chart
            var genderData = {!! json_encode($genderData) !!};
            var genderChartData = new google.visualization.DataTable();
            genderChartData.addColumn('string', 'Gender');
            genderChartData.addColumn('number', 'Count');
            
            var formattedGenderData = genderData.map(function(item) { return [item.name, item.count]; });
            genderChartData.addRows(formattedGenderData);

            var genderOptions = {
                pieHole: 0.6,
                legend: { position: 'none' },
                pieSliceText: 'none',
                colors: ['#00aadd', '#0055aa'],
                chartArea: { width: '90%', height: '90%', top: 10 }
            };
            
            for(var i=0; i<genderData.length; i++){
                if(genderData[i].name === 'Female') genderOptions.colors[i] = '#00aadd';
                if(genderData[i].name === 'Male') genderOptions.colors[i] = '#0055aa';
            }

            var genderChart = new google.visualization.PieChart(document.getElementById('gender_diversity_chart'));
            genderChart.draw(genderChartData, genderOptions);
        }
        
        $(window).resize(function(){
            drawCharts();
        });
    </script>

@endsection
    </div>
@endsection

@else
@section('content-class')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content-child')
    <div class="hr-dashboard">
        <div class="dash-shell">
            <div class="dash-top">
                <div class="dash-title">
                    <p>Snapshot for Friday, May 8, 2026 • Branch: Main Office</p>
                </div>
                <div class="dash-pill">
                    Last refreshed: 09:42 AM
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card accent-a">
                    <span class="kpi-label">Total Employees</span>
                    <p class="kpi-value">248</p>
                    <div class="kpi-trend up">+12 this month</div>
                </div>
                <div class="kpi-card accent-b">
                    <span class="kpi-label">Present Today</span>
                    <p class="kpi-value">231</p>
                    <div class="kpi-trend up">93.1% attendance</div>
                </div>
                <div class="kpi-card accent-c">
                    <span class="kpi-label">Open Requests</span>
                    <p class="kpi-value">37</p>
                    <div class="kpi-trend down">8 pending approval</div>
                </div>
                <div class="kpi-card accent-d">
                    <span class="kpi-label">Payroll Progress</span>
                    <p class="kpi-value">76%</p>
                    <div class="kpi-trend up">Cutoff in 4 days</div>
                </div>
            </div>

            <div class="dash-grid">
                <div style="display:grid; gap:16px;">
                    <section class="panel">
                        <div class="panel-head">
                            <h5>Department Attendance</h5>
                            <small>Today</small>
                        </div>
                        <div class="chart-wrap">
                            <ul class="bar-list">
                                <li class="bar-item">
                                    <span>IT</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 96%"></div>
                                    </div>
                                    <strong>96</strong>
                                </li>
                                <li class="bar-item">
                                    <span>HR</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 90%"></div>
                                    </div>
                                    <strong>90</strong>
                                </li>
                                <li class="bar-item">
                                    <span>Finance</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 87%"></div>
                                    </div>
                                    <strong>87</strong>
                                </li>
                                <li class="bar-item">
                                    <span>Operations</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 93%"></div>
                                    </div>
                                    <strong>93</strong>
                                </li>
                                <li class="bar-item">
                                    <span>Admin</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 82%"></div>
                                    </div>
                                    <strong>82</strong>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-head">
                            <h5>Recent Requests</h5>
                            <small>Latest 5</small>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Ahmad Rizki</td>
                                        <td>Leave Request</td>
                                        <td>08 May</td>
                                        <td><span class="tag pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Dina Kartika</td>
                                        <td>Overtime Claim</td>
                                        <td>08 May</td>
                                        <td><span class="tag approved">Approved</span></td>
                                    </tr>
                                    <tr>
                                        <td>Rahmat S.</td>
                                        <td>Shift Change</td>
                                        <td>07 May</td>
                                        <td><span class="tag revision">Need Revision</span></td>
                                    </tr>
                                    <tr>
                                        <td>Lina Pratiwi</td>
                                        <td>Travel Order</td>
                                        <td>07 May</td>
                                        <td><span class="tag pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Andi Putra</td>
                                        <td>Reimbursement</td>
                                        <td>06 May</td>
                                        <td><span class="tag approved">Approved</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside class="aside-cards">
                    <section class="panel">
                        <div class="panel-head">
                            <h5>Quick Summary</h5>
                            <small>Current Month</small>
                        </div>
                        <ul class="summary-list">
                            <li class="summary-item">
                                <span>New Hires</span>
                                <strong>14</strong>
                            </li>
                            <li class="summary-item">
                                <span>Resignations</span>
                                <strong>3</strong>
                            </li>
                            <li class="summary-item">
                                <span>Training Hours</span>
                                <strong>219</strong>
                            </li>
                            <li class="summary-item">
                                <span>Late Cases</span>
                                <strong>17</strong>
                            </li>
                        </ul>
                    </section>

                    <section class="panel">
                        <div class="panel-head">
                            <h5>Announcements</h5>
                            <small>Internal</small>
                        </div>
                        <div style="padding: 0 18px 18px;">
                            <div class="announce-item">
                                <h6>Company Gathering 2026</h6>
                                <p>Registration closes on May 15, 2026.</p>
                            </div>
                            <div class="announce-item">
                                <h6>Policy Update: Leave Submission</h6>
                                <p>Submit leave requests at least 3 business days ahead.</p>
                            </div>
                            <div class="announce-item">
                                <h6>Payroll Verification</h6>
                                <p>All bank details must be verified before May 20, 2026.</p>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-head">
                            <h5>Priority Tasks</h5>
                            <small>HR Team</small>
                        </div>
                        <div style="padding: 0 18px 14px;">
                            <div class="task-item">
                                <span><i class="dot high"></i>Finalize payroll discrepancy report</span>
                                <strong>Today</strong>
                            </div>
                            <div class="task-item">
                                <span><i class="dot medium"></i>Review 8 probation evaluations</span>
                                <strong>May 10</strong>
                            </div>
                            <div class="task-item">
                                <span><i class="dot low"></i>Publish internship onboarding kit</span>
                                <strong>May 12</strong>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection

@endif

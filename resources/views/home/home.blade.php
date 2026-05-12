@extends('layouts.main-layout')

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

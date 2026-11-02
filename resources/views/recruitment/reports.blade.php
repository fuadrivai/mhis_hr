@extends('layouts.main-layout')

@section('content-class')
    <style>
        .ats-report-wrap {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
        }
        .ats-filter-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px 10px 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
        }
        .ats-card-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }
        .ats-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 10px;
        }
        .ats-panel-title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ats-report-tabs .nav-tabs {
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 22px;
        }
        .ats-report-tabs .nav-tabs > li > a {
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            font-weight: 700;
            font-size: 13px;
            color: #64748b;
            padding: 12px 18px;
            background: transparent;
            transition: all 0.2s ease;
        }
        .ats-report-tabs .nav-tabs > li > a:hover {
            color: #2563eb;
            background: transparent;
            border-bottom-color: #cbd5e1;
        }
        .ats-report-tabs .nav-tabs > li.active > a,
        .ats-report-tabs .nav-tabs > li.active > a:focus,
        .ats-report-tabs .nav-tabs > li.active > a:hover {
            color: #2563eb;
            background: transparent;
            border: none;
            border-bottom: 3px solid #2563eb;
        }
        .report-mini-kpi {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 18px;
        }
        .report-mini-kpi .kpi-num {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .report-mini-kpi .kpi-lbl {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin: 0;
        }
        .table-ats {
            width: 100%;
            margin-bottom: 0;
        }
        .table-ats th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 14px;
        }
        .table-ats td {
            padding: 12px 14px;
            vertical-align: middle !important;
            border-top: 1px solid #f1f5f9 !important;
            font-size: 13px;
        }
        .table-ats tbody tr:hover {
            background-color: #f8fafc;
        }
        .ats-badge-soft {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .soft-blue { background: #eff6ff; color: #1e40af; }
        .soft-green { background: #ecfdf5; color: #065f46; }
        .soft-amber { background: #fffbeb; color: #92400e; }
        .soft-red { background: #fef2f2; color: #991b1b; }
        .soft-purple { background: #faf5ff; color: #6b21a8; }
    </style>
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12 col-xs-12 ats-report-wrap">
    {{-- Header with Title & Back Navigation --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0;">Recruitment Reports & Analytics</h2>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 13px;">
                Comprehensive hiring metrics, source effectiveness, SLA time-to-hire & conversion analysis
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('recruitment.index', ['section' => 'dashboard']) }}" class="btn btn-default" style="border-radius: 8px; font-weight: 600;">
                <i class="fa fa-arrow-left"></i> Dashboard
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px; font-weight: 600;">
                    <i class="fa fa-download"></i> Export Data CSV <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" style="border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-color: #e2e8f0;">
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'funnel'] + request()->query()) }}"><i class="fa fa-filter text-primary"></i> Pipeline Funnel CSV</a></li>
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'time_to_hire'] + request()->query()) }}"><i class="fa fa-clock-o text-info"></i> Time to Hire CSV</a></li>
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'sources'] + request()->query()) }}"><i class="fa fa-globe text-success"></i> Source Effectiveness CSV</a></li>
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'offers'] + request()->query()) }}"><i class="fa fa-file-text-o text-warning"></i> Offer Metrics CSV</a></li>
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'rejections'] + request()->query()) }}"><i class="fa fa-ban text-danger"></i> Rejection Distribution CSV</a></li>
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'headcount'] + request()->query()) }}"><i class="fa fa-building text-primary"></i> Headcount Fill Rate CSV</a></li>
                    <li><a href="{{ route('recruitment.reports.export', ['type' => 'ai_scores'] + request()->query()) }}"><i class="fa fa-bolt text-purple"></i> AI CV Matching Scores CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="ats-filter-box">
        <form method="GET" action="{{ route('recruitment.index', ['section' => 'reports']) }}" class="form-inline">
            <input type="hidden" name="section" value="reports">
            <input type="hidden" name="active_tab" id="activeTabInput" value="{{ $filters['active_tab'] ?? 'time_to_hire' }}">
            <div class="row">
                {{-- Branch Filter --}}
                @if (empty($filterOptions['branchScopeId']))
                    <div class="col-md-3 col-sm-6 col-xs-12 form-group" style="margin-bottom: 12px;">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Branch</label>
                        <select name="branch_id" class="form-control select2" style="width: 100%;">
                            <option value="">All Branches</option>
                            @foreach ($filterOptions['branches'] as $b)
                                <option value="{{ $b->id }}" {{ ($filters['branch_id'] ?? '') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Job Opening Filter --}}
                <div class="col-md-{{ empty($filterOptions['branchScopeId']) ? '3' : '4' }} col-sm-6 col-xs-12 form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Job Opening</label>
                    <select name="job_opening_id" class="form-control select2" style="width: 100%;">
                        <option value="">All Openings</option>
                        @foreach ($filterOptions['openings'] as $op)
                            <option value="{{ $op->id }}" {{ ($filters['job_opening_id'] ?? '') == $op->id ? 'selected' : '' }}>
                                {{ $op->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range --}}
                <div class="col-md-{{ empty($filterOptions['branchScopeId']) ? '2' : '3' }} col-sm-6 col-xs-12 form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">From Date</label>
                    <input type="date" name="start_date" class="form-control" style="width: 100%; border-radius: 6px;" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-{{ empty($filterOptions['branchScopeId']) ? '2' : '3' }} col-sm-6 col-xs-12 form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">To Date</label>
                    <input type="date" name="end_date" class="form-control" style="width: 100%; border-radius: 6px;" value="{{ $filters['end_date'] ?? '' }}">
                </div>

                {{-- Actions --}}
                <div class="col-md-2 col-sm-12 col-xs-12 form-group" style="margin-bottom: 12px; padding-top: 22px; display: flex; gap: 6px;">
                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-filter"></i> Apply
                    </button>
                    @if (!empty($filters['branch_id']) || !empty($filters['job_opening_id']) || !empty($filters['start_date']) || !empty($filters['end_date']))
                        <a href="{{ route('recruitment.index', ['section' => 'reports']) }}" class="btn btn-default btn-sm" title="Clear filter" style="border-radius: 6px;">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Modern Tabbed Reports Container --}}
    <div class="ats-report-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="{{ ($filters['active_tab'] ?? 'time_to_hire') === 'time_to_hire' ? 'active' : '' }}">
                <a href="#tab_time_to_hire" aria-controls="tab_time_to_hire" role="tab" data-toggle="tab" data-tab-name="time_to_hire">
                    <i class="fa fa-clock-o"></i> Time in Stage & Time-to-Hire
                </a>
            </li>
            <li role="presentation" class="{{ ($filters['active_tab'] ?? '') === 'sources' ? 'active' : '' }}">
                <a href="#tab_sources" aria-controls="tab_sources" role="tab" data-toggle="tab" data-tab-name="sources">
                    <i class="fa fa-globe"></i> Source Effectiveness
                </a>
            </li>
            <li role="presentation" class="{{ ($filters['active_tab'] ?? '') === 'offers' ? 'active' : '' }}">
                <a href="#tab_offers" aria-controls="tab_offers" role="tab" data-toggle="tab" data-tab-name="offers">
                    <i class="fa fa-check-square-o"></i> Offer Acceptance
                </a>
            </li>
            <li role="presentation" class="{{ ($filters['active_tab'] ?? '') === 'rejections' ? 'active' : '' }}">
                <a href="#tab_rejections" aria-controls="tab_rejections" role="tab" data-toggle="tab" data-tab-name="rejections">
                    <i class="fa fa-ban"></i> Rejection Distribution
                </a>
            </li>
            <li role="presentation" class="{{ ($filters['active_tab'] ?? '') === 'headcount' ? 'active' : '' }}">
                <a href="#tab_headcount" aria-controls="tab_headcount" role="tab" data-toggle="tab" data-tab-name="headcount">
                    <i class="fa fa-building"></i> Headcount Fill Rate & Aging
                </a>
            </li>
            <li role="presentation" class="{{ ($filters['active_tab'] ?? '') === 'ai_scores' ? 'active' : '' }}">
                <a href="#tab_ai_scores" aria-controls="tab_ai_scores" role="tab" data-toggle="tab" data-tab-name="ai_scores">
                    <i class="fa fa-bolt"></i> AI CV Score vs Outcome
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: Time in Stage & Time to Hire --}}
            <div role="tabpanel" class="tab-pane {{ ($filters['active_tab'] ?? 'time_to_hire') === 'time_to_hire' ? 'active' : '' }}" id="tab_time_to_hire">
                {{-- KPI Row --}}
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Average Time to Hire</div>
                            <div class="kpi-num">{{ $timeReport['time_to_hire']['avg_days'] }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">days</span></div>
                            <small class="text-muted">From apply date to join</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Fastest Hire</div>
                            <div class="kpi-num">{{ $timeReport['time_to_hire']['min_days'] }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">days</span></div>
                            <small class="text-muted">Minimum recorded</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Slowest Hire</div>
                            <div class="kpi-num">{{ $timeReport['time_to_hire']['max_days'] }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">days</span></div>
                            <small class="text-muted">Maximum recorded</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Total Hired (Sample)</div>
                            <div class="kpi-num">{{ $timeReport['time_to_hire']['total_hired'] }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">candidates</span></div>
                            <small class="text-muted">Successfully joined</small>
                        </div>
                    </div>
                </div>

                {{-- Chart: Time in Stage --}}
                <div class="ats-card-panel">
                    <div class="ats-panel-header">
                        <h4 class="ats-panel-title">
                            <i class="fa fa-bar-chart text-primary"></i> Average Days Spent in Each Selection Stage
                        </h4>
                        <a href="{{ route('recruitment.reports.export', ['type' => 'time_to_hire'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-download"></i> Export CSV
                        </a>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="stageDurationChart"></canvas>
                    </div>
                </div>

                {{-- Table: Hired Breakdown --}}
                <div class="ats-card-panel">
                    <div class="ats-panel-header">
                        <h4 class="ats-panel-title">
                            <i class="fa fa-table text-info"></i> Individual Candidates Time-to-Hire Records
                        </h4>
                    </div>
                    @if (empty($timeReport['hired_breakdown']))
                        <p class="text-muted text-center" style="padding: 20px;">No candidates have reached the Joined stage for the selected filters yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-ats">
                                <thead>
                                    <tr>
                                        <th>Candidate Name</th>
                                        <th>Job Position</th>
                                        <th>Branch</th>
                                        <th>Applied Date</th>
                                        <th>Joined Date</th>
                                        <th style="text-align: right;">Time to Hire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($timeReport['hired_breakdown'] as $row)
                                        <tr>
                                            <td><strong style="color: #0f172a;">{{ $row['candidate_name'] }}</strong></td>
                                            <td>{{ $row['position_title'] }}</td>
                                            <td>{{ $row['branch_name'] }}</td>
                                            <td>{{ $row['applied_date'] }}</td>
                                            <td>{{ $row['joined_date'] }}</td>
                                            <td style="text-align: right;">
                                                <span class="ats-badge-soft soft-blue">{{ $row['days_to_hire'] }} days</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB 2: Source Effectiveness --}}
            <div role="tabpanel" class="tab-pane {{ ($filters['active_tab'] ?? '') === 'sources' ? 'active' : '' }}" id="tab_sources">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="ats-card-panel">
                            <div class="ats-panel-header">
                                <h4 class="ats-panel-title">
                                    <i class="fa fa-pie-chart text-success"></i> Applicant Volume by Channel
                                </h4>
                            </div>
                            <div style="position: relative; height: 260px;">
                                <canvas id="sourceShareChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="ats-card-panel">
                            <div class="ats-panel-header">
                                <h4 class="ats-panel-title">
                                    <i class="fa fa-line-chart text-primary"></i> Hire Conversion Rate (%) by Channel
                                </h4>
                            </div>
                            <div style="position: relative; height: 260px;">
                                <canvas id="sourceConversionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table: Source Summary --}}
                <div class="ats-card-panel">
                    <div class="ats-panel-header">
                        <h4 class="ats-panel-title">
                            <i class="fa fa-table text-primary"></i> Source Channel Performance Matrix
                        </h4>
                        <a href="{{ route('recruitment.reports.export', ['type' => 'sources'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-download"></i> Export CSV
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-ats">
                            <thead>
                                <tr>
                                    <th>Source Channel</th>
                                    <th>Total Applied</th>
                                    <th>Shortlisted</th>
                                    <th>Hired</th>
                                    <th>Shortlist Rate</th>
                                    <th>Hire Conversion Rate</th>
                                    <th style="text-align: right;">Share of Applicants</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sourceReport['sources'] as $s)
                                    <tr>
                                        <td><strong style="color: #0f172a;">{{ $s['source'] }}</strong></td>
                                        <td>{{ number_format($s['applied']) }}</td>
                                        <td>{{ number_format($s['shortlisted']) }}</td>
                                        <td><span class="ats-badge-soft soft-green">{{ number_format($s['hired']) }}</span></td>
                                        <td>{{ $s['shortlist_rate'] }}%</td>
                                        <td><strong>{{ $s['conversion_rate'] }}%</strong></td>
                                        <td style="text-align: right;">{{ $s['share_of_total'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 3: Offer Metrics & Acceptance --}}
            <div role="tabpanel" class="tab-pane {{ ($filters['active_tab'] ?? '') === 'offers' ? 'active' : '' }}" id="tab_offers">
                {{-- KPI Row --}}
                <div class="row">
                    <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Total Offers</div>
                            <div class="kpi-num">{{ number_format($offerReport['total_offers']) }}</div>
                            <small class="text-muted">Drafted or sent</small>
                        </div>
                    </div>
                    <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Offers Sent</div>
                            <div class="kpi-num">{{ number_format($offerReport['sent_offers']) }}</div>
                            <small class="text-muted">Delivered to candidate</small>
                        </div>
                    </div>
                    <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Accepted</div>
                            <div class="kpi-num" style="color: #059669;">{{ number_format($offerReport['accepted_offers']) }}</div>
                            <small class="text-muted">Ready for onboarding</small>
                        </div>
                    </div>
                    <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Declined</div>
                            <div class="kpi-num" style="color: #dc2626;">{{ number_format($offerReport['declined_offers']) }}</div>
                            <small class="text-muted">Candidate refused</small>
                        </div>
                    </div>
                    <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Acceptance Rate</div>
                            <div class="kpi-num" style="color: #2563eb;">{{ $offerReport['acceptance_rate'] }}%</div>
                            <small class="text-muted">Accepted vs Decided</small>
                        </div>
                    </div>
                </div>

                {{-- Decline Reasons --}}
                <div class="row">
                    <div class="col-md-5 col-sm-12 col-xs-12">
                        <div class="ats-card-panel">
                            <div class="ats-panel-header">
                                <h4 class="ats-panel-title">
                                    <i class="fa fa-pie-chart text-danger"></i> Reasons for Offer Decline
                                </h4>
                            </div>
                            @if (empty($offerReport['decline_breakdown']))
                                <p class="text-muted text-center" style="padding: 20px;">No offers have been declined yet.</p>
                            @else
                                <div style="position: relative; height: 230px;">
                                    <canvas id="offerDeclineChart"></canvas>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-7 col-sm-12 col-xs-12">
                        <div class="ats-card-panel">
                            <div class="ats-panel-header">
                                <h4 class="ats-panel-title">
                                    <i class="fa fa-list text-info"></i> Recent Offer Status History
                                </h4>
                                <a href="{{ route('recruitment.reports.export', ['type' => 'offers'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                                    <i class="fa fa-download"></i> Export CSV
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-ats">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Position</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                            <th>Reason / Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($offerReport['recent_offers'] as $of)
                                            <tr>
                                                <td><strong style="color: #0f172a;">{{ $of->candidateApplication?->candidate?->full_name }}</strong></td>
                                                <td>{{ $of->position_title }}</td>
                                                <td>
                                                    <span class="ats-badge-soft {{ $of->status === 'accepted' ? 'soft-green' : ($of->status === 'declined' ? 'soft-red' : 'soft-amber') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $of->status)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $of->proposed_start_date?->format('d M Y') ?? '-' }}</td>
                                                <td>{{ $of->decline_reason ?: ($of->notes ?? '-') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: Rejection Distribution --}}
            <div role="tabpanel" class="tab-pane {{ ($filters['active_tab'] ?? '') === 'rejections' ? 'active' : '' }}" id="tab_rejections">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="ats-card-panel">
                            <div class="ats-panel-header">
                                <h4 class="ats-panel-title">
                                    <i class="fa fa-pie-chart text-danger"></i> Rejection by Master Reason
                                </h4>
                            </div>
                            <div style="position: relative; height: 260px;">
                                <canvas id="rejectionReasonChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="ats-card-panel">
                            <div class="ats-panel-header">
                                <h4 class="ats-panel-title">
                                    <i class="fa fa-sitemap text-warning"></i> Rejection by Stage Occurred
                                </h4>
                            </div>
                            <div style="position: relative; height: 260px;">
                                <canvas id="rejectionStageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ats-card-panel">
                    <div class="ats-panel-header">
                        <h4 class="ats-panel-title">
                            <i class="fa fa-table text-primary"></i> Rejection Breakdown Details
                        </h4>
                        <a href="{{ route('recruitment.reports.export', ['type' => 'rejections'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-download"></i> Export CSV
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-ats">
                            <thead>
                                <tr>
                                    <th>Reason Master</th>
                                    <th>Total Rejections</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rejectionReport['by_reason'] as $r)
                                    <tr>
                                        <td><strong style="color: #0f172a;">{{ $r['name'] }}</strong></td>
                                        <td>{{ number_format($r['count']) }}</td>
                                        <td>
                                            <span class="ats-badge-soft soft-red">{{ $r['percentage'] }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 5: Headcount Fill Rate & Aging --}}
            <div role="tabpanel" class="tab-pane {{ ($filters['active_tab'] ?? '') === 'headcount' ? 'active' : '' }}" id="tab_headcount">
                {{-- KPI Row --}}
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Total Openings</div>
                            <div class="kpi-num">{{ number_format($headcountReport['total_openings']) }}</div>
                            <small class="text-muted">In scope</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Target Headcount</div>
                            <div class="kpi-num">{{ number_format($headcountReport['total_headcount']) }}</div>
                            <small class="text-muted">Total seats required</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Filled Headcount</div>
                            <div class="kpi-num" style="color: #059669;">{{ number_format($headcountReport['total_filled']) }}</div>
                            <small class="text-muted">Hired & joined</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Overall Fill Rate</div>
                            <div class="kpi-num" style="color: #2563eb;">{{ $headcountReport['overall_fill_rate'] }}%</div>
                            <small class="text-muted">Fulfillment index</small>
                        </div>
                    </div>
                </div>

                {{-- Table: Openings List --}}
                <div class="ats-card-panel">
                    <div class="ats-panel-header">
                        <h4 class="ats-panel-title">
                            <i class="fa fa-briefcase text-primary"></i> Job Openings Headcount & Aging Summary
                        </h4>
                        <a href="{{ route('recruitment.reports.export', ['type' => 'headcount'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-download"></i> Export CSV
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-ats">
                            <thead>
                                <tr>
                                    <th>Opening Title</th>
                                    <th>Branch</th>
                                    <th>Target Headcount</th>
                                    <th>Filled</th>
                                    <th>Fill Rate</th>
                                    <th>Status</th>
                                    <th>Opened Date</th>
                                    <th style="text-align: right;">Aging (Days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($headcountReport['openings'] as $op)
                                    <tr>
                                        <td>
                                            <a href="{{ route('recruitment.job-openings.show', ['jobOpening' => $op['id']]) }}">
                                                <strong style="color: #0f172a;">{{ $op['title'] }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $op['branch'] }}</td>
                                        <td>{{ $op['headcount'] }}</td>
                                        <td><strong style="color: #059669;">{{ $op['filled'] }}</strong></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="flex-grow: 1; background: #e2e8f0; height: 6px; border-radius: 4px; overflow: hidden;">
                                                    <div style="width: {{ $op['fill_rate'] }}%; height: 100%; background: {{ $op['fill_rate'] >= 100 ? '#10b981' : '#2563eb' }};"></div>
                                                </div>
                                                <span style="font-size: 12px; font-weight: 600;">{{ $op['fill_rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="ats-badge-soft {{ $op['status'] === 'open' ? 'soft-green' : ($op['status'] === 'filled' ? 'soft-blue' : 'soft-amber') }}">
                                                {{ ucfirst($op['status']) }}
                                            </span>
                                        </td>
                                        <td>{{ $op['opened_at'] }}</td>
                                        <td style="text-align: right;">
                                            <span class="ats-badge-soft {{ $op['aging_days'] > 60 ? 'soft-red' : ($op['aging_days'] > 30 ? 'soft-amber' : 'soft-blue') }}">
                                                {{ $op['aging_days'] }} days
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 6: AI CV Matching Insights --}}
            <div role="tabpanel" class="tab-pane {{ ($filters['active_tab'] ?? '') === 'ai_scores' ? 'active' : '' }}" id="tab_ai_scores">
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Total AI Scored Resumes</div>
                            <div class="kpi-num">{{ number_format($aiReport['total_scored']) }}</div>
                            <small class="text-muted">Completed matches</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Avg Score: Hired Candidates</div>
                            <div class="kpi-num" style="color: #059669;">
                                {{ $aiReport['avg_hired'] !== null ? $aiReport['avg_hired'] . ' / 100' : 'N/A' }}
                            </div>
                            <small class="text-muted">Score of accepted hires</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="report-mini-kpi">
                            <div class="kpi-lbl">Avg Score: Rejected Candidates</div>
                            <div class="kpi-num" style="color: #dc2626;">
                                {{ $aiReport['avg_rejected'] !== null ? $aiReport['avg_rejected'] . ' / 100' : 'N/A' }}
                            </div>
                            <small class="text-muted">Score of rejected applicants</small>
                        </div>
                    </div>
                </div>

                {{-- Chart & Breakdown --}}
                <div class="ats-card-panel">
                    <div class="ats-panel-header">
                        <div>
                            <h4 class="ats-panel-title">
                                <i class="fa fa-bar-chart text-purple"></i> AI Score Tiers vs Final Pipeline Outcomes
                            </h4>
                            <p style="color: #64748b; font-size: 12px; margin: 3px 0 0 0;">
                                Reference indicator only: Human evaluation remains the decisive factor in ATS hiring.
                            </p>
                        </div>
                        <a href="{{ route('recruitment.reports.export', ['type' => 'ai_scores'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-download"></i> Export CSV
                        </a>
                    </div>
                    <div style="position: relative; height: 280px; margin-bottom: 24px;">
                        <canvas id="aiScoreTierChart"></canvas>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-ats">
                            <thead>
                                <tr>
                                    <th>Score Tier Range</th>
                                    <th>Total Candidates</th>
                                    <th>Hired / Joined</th>
                                    <th>In Progress</th>
                                    <th>Rejected / Withdrawn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($aiReport['buckets'] as $b)
                                    <tr>
                                        <td><strong>{{ $b['range'] }}</strong></td>
                                        <td>{{ number_format($b['total']) }}</td>
                                        <td><span class="ats-badge-soft soft-green">{{ number_format($b['hired']) }}</span></td>
                                        <td><span class="ats-badge-soft soft-blue">{{ number_format($b['in_progress']) }}</span></td>
                                        <td><span class="ats-badge-soft soft-red">{{ number_format($b['rejected']) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content-script')
<script src="/plugins/Chart.js/dist/Chart.bundle.min.js"></script>
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2();
    }

    // Retain active tab on filter form submit
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tabName = $(e.target).data('tab-name');
        if (tabName) {
            $('#activeTabInput').val(tabName);
        }
    });

    // 1. Stage Duration Chart
    var durCtx = document.getElementById('stageDurationChart');
    if (durCtx) {
        var durationData = @json(array_values($timeReport['stages_duration']));
        new Chart(durCtx, {
            type: 'bar',
            data: {
                labels: durationData.map(function(d) { return d.label; }),
                datasets: [{
                    label: 'Avg Days',
                    data: durationData.map(function(d) { return d.avg_days; }),
                    backgroundColor: durationData.map(function(d) { return d.color; }),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true },
                        scaleLabel: { display: true, labelString: 'Days' }
                    }]
                }
            }
        });
    }

    // 2. Source Share Doughnut
    var srcShareCtx = document.getElementById('sourceShareChart');
    if (srcShareCtx) {
        var srcData = @json($sourceReport['sources']);
        var colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];
        new Chart(srcShareCtx, {
            type: 'doughnut',
            data: {
                labels: srcData.map(function(s) { return s.source; }),
                datasets: [{
                    data: srcData.map(function(s) { return s.applied; }),
                    backgroundColor: colors.slice(0, srcData.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right' }
            }
        });
    }

    // 3. Source Conversion Bar
    var srcConvCtx = document.getElementById('sourceConversionChart');
    if (srcConvCtx) {
        var srcData = @json($sourceReport['sources']);
        new Chart(srcConvCtx, {
            type: 'bar',
            data: {
                labels: srcData.map(function(s) { return s.source; }),
                datasets: [{
                    label: 'Conversion Rate %',
                    data: srcData.map(function(s) { return s.conversion_rate; }),
                    backgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, max: 100 },
                        scaleLabel: { display: true, labelString: 'Hired Rate (%)' }
                    }]
                }
            }
        });
    }

    // 4. Offer Decline Doughnut
    var decCtx = document.getElementById('offerDeclineChart');
    if (decCtx) {
        var declineData = @json($offerReport['decline_breakdown']);
        new Chart(decCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(declineData),
                datasets: [{
                    data: Object.values(declineData),
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#94a3b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
    }

    // 5. Rejection Reason Pie
    var rejCtx = document.getElementById('rejectionReasonChart');
    if (rejCtx) {
        var rejData = @json($rejectionReport['by_reason']);
        new Chart(rejCtx, {
            type: 'pie',
            data: {
                labels: rejData.map(function(r) { return r.name; }),
                datasets: [{
                    data: rejData.map(function(r) { return r.count; }),
                    backgroundColor: ['#ef4444', '#f97316', '#f59e0b', '#6366f1', '#ec4899', '#64748b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right' }
            }
        });
    }

    // 6. Rejection Stage Doughnut
    var rejStgCtx = document.getElementById('rejectionStageChart');
    if (rejStgCtx) {
        var rejStageData = @json($rejectionReport['by_stage']);
        new Chart(rejStgCtx, {
            type: 'doughnut',
            data: {
                labels: rejStageData.map(function(r) { return r.stage; }),
                datasets: [{
                    data: rejStageData.map(function(r) { return r.count; }),
                    backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899', '#10b981', '#64748b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right' }
            }
        });
    }

    // 7. AI Score Tier Stacked Bar
    var aiCtx = document.getElementById('aiScoreTierChart');
    if (aiCtx) {
        var buckets = @json($aiReport['buckets']);
        new Chart(aiCtx, {
            type: 'bar',
            data: {
                labels: buckets.map(function(b) { return b.range; }),
                datasets: [
                    {
                        label: 'Hired',
                        data: buckets.map(function(b) { return b.hired; }),
                        backgroundColor: '#10b981'
                    },
                    {
                        label: 'In Progress',
                        data: buckets.map(function(b) { return b.in_progress; }),
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Rejected',
                        data: buckets.map(function(b) { return b.rejected; }),
                        backgroundColor: '#ef4444'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{ stacked: true }],
                    yAxes: [{
                        stacked: true,
                        ticks: { beginAtZero: true }
                    }]
                }
            }
        });
    }
});
</script>
@endsection


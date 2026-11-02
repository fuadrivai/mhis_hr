@extends('layouts.main-layout')

@section('content-class')
    <style>
        .ats-dash-wrap {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
        }
        .ats-kpi-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px 22px;
            margin-bottom: 22px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: all 0.22s ease-in-out;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ats-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }
        .ats-kpi-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .ats-kpi-info .kpi-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .ats-kpi-info .kpi-subtext {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }
        .ats-kpi-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-indigo { background: #eef2ff; color: #4f46e5; }
        .icon-amber { background: #fffbeb; color: #d97706; }
        .icon-teal { background: #f0fdf4; color: #059669; }
        .icon-emerald { background: #ecfdf5; color: #047857; }

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
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
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
        .stage-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: #ffffff;
        }
        .ats-action-tabs .nav-pills > li > a {
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 14px;
            margin-right: 8px;
            transition: all 0.2s ease;
        }
        .ats-action-tabs .nav-pills > li.active > a,
        .ats-action-tabs .nav-pills > li.active > a:focus,
        .ats-action-tabs .nav-pills > li.active > a:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        }
        .badge-counter {
            background: rgba(0, 0, 0, 0.12);
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 6px;
        }
        .nav-pills > li.active > a .badge-counter {
            background: #ffffff;
            color: #2563eb;
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
        .days-badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
        }
        .days-warning { background: #fef3c7; color: #92400e; }
        .days-danger { background: #fee2e2; color: #991b1b; }
        .empty-action-box {
            text-align: center;
            padding: 38px 20px;
            color: #64748b;
        }
        .empty-action-box i {
            font-size: 42px;
            color: #10b981;
            margin-bottom: 12px;
        }
        .ats-progress-wrap {
            background: #f1f5f9;
            border-radius: 8px;
            height: 10px;
            overflow: hidden;
            margin-top: 6px;
        }
        .ats-progress-bar {
            height: 100%;
            border-radius: 8px;
            transition: width 0.4s ease;
        }
    </style>
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12 col-xs-12 ats-dash-wrap">
    {{-- Header with Title & Quick Action Shortcuts --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0;">Recruitment Dashboard</h2>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 13px;">
                Real-time applicant tracking pipeline, KPI overview & urgent action triggers
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('recruitment.job-openings.create') }}" class="btn btn-default" style="border-radius: 8px; font-weight: 600;">
                <i class="fa fa-plus-circle text-primary"></i> Create Opening
            </a>
            <a href="{{ route('recruitment.candidates.form') }}" class="btn btn-default" style="border-radius: 8px; font-weight: 600;">
                <i class="fa fa-user-plus text-success"></i> Add Candidate
            </a>
            <a href="{{ route('recruitment.index', ['section' => 'reports']) }}" class="btn btn-primary" style="border-radius: 8px; font-weight: 600;">
                <i class="fa fa-bar-chart"></i> Reports & Analytics
            </a>
        </div>
    </div>

    {{-- Modern Filter Bar --}}
    <div class="ats-filter-box">
        <form method="GET" action="{{ route('recruitment.index', ['section' => 'dashboard']) }}" class="form-inline">
            <input type="hidden" name="section" value="dashboard">
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

                {{-- Filter Action Buttons --}}
                <div class="col-md-2 col-sm-12 col-xs-12 form-group" style="margin-bottom: 12px; padding-top: 22px; display: flex; gap: 6px;">
                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('recruitment.index', ['section' => 'dashboard', 'refresh' => 1]) }}" class="btn btn-default btn-sm" title="Refresh metrics" style="border-radius: 6px;">
                        <i class="fa fa-refresh"></i>
                    </a>
                    @if (!empty($filters['branch_id']) || !empty($filters['job_opening_id']) || !empty($filters['start_date']) || !empty($filters['end_date']))
                        <a href="{{ route('recruitment.index', ['section' => 'dashboard']) }}" class="btn btn-default btn-sm" title="Clear filter" style="border-radius: 6px;">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Top Metric Cards (5 Core KPIs) --}}
    <div class="row">
        {{-- Card 1: Active Openings --}}
        <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
            <a href="{{ route('recruitment.job-openings.index', ['status' => 'open']) }}" style="text-decoration: none;">
                <div class="ats-kpi-card">
                    <div class="ats-kpi-info">
                        <div class="kpi-label">Active Openings</div>
                        <h3>{{ number_format($summary['active_openings']) }}</h3>
                        <p class="kpi-subtext">Published positions</p>
                    </div>
                    <div class="ats-kpi-icon icon-blue">
                        <i class="fa fa-briefcase"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 2: Active Candidates --}}
        <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
            <a href="{{ route('recruitment.candidates') }}" style="text-decoration: none;">
                <div class="ats-kpi-card">
                    <div class="ats-kpi-info">
                        <div class="kpi-label">Active Candidates</div>
                        <h3>{{ number_format($summary['active_candidates']) }}</h3>
                        <p class="kpi-subtext">In-pipeline selection</p>
                    </div>
                    <div class="ats-kpi-icon icon-indigo">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 3: Pending Offers --}}
        <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
            <a href="{{ route('recruitment.my-approvals.index') }}" style="text-decoration: none;">
                <div class="ats-kpi-card">
                    <div class="ats-kpi-info">
                        <div class="kpi-label">Pending Offers</div>
                        <h3>{{ number_format($summary['pending_offers']) }}</h3>
                        <p class="kpi-subtext">Awaiting approval</p>
                    </div>
                    <div class="ats-kpi-icon icon-amber">
                        <i class="fa fa-file-text-o"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 4: Awaiting Evaluation --}}
        <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
            <div class="ats-kpi-card">
                <div class="ats-kpi-info">
                    <div class="kpi-label">Awaiting Eval</div>
                    <h3>{{ number_format($summary['awaiting_evaluations']) }}</h3>
                    <p class="kpi-subtext">Interview feedbacks</p>
                </div>
                <div class="ats-kpi-icon icon-teal">
                    <i class="fa fa-comments-o"></i>
                </div>
            </div>
        </div>

        {{-- Card 5: Upcoming Joinings (30d) --}}
        <div class="col-md-2-4 col-sm-6 col-xs-12" style="width: 20%;">
            <div class="ats-kpi-card">
                <div class="ats-kpi-info">
                    <div class="kpi-label">Joining 30 Days</div>
                    <h3>{{ number_format($summary['upcoming_joinings_30d']) }}</h3>
                    <p class="kpi-subtext">Signed & confirmed</p>
                </div>
                <div class="ats-kpi-icon icon-emerald">
                    <i class="fa fa-user-plus"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Pipeline Funnel Visualization & Stage Breakdown --}}
    <div class="row">
        {{-- Chart Col --}}
        <div class="col-md-7 col-sm-12 col-xs-12">
            <div class="ats-card-panel">
                <div class="ats-panel-header">
                    <div>
                        <h4 class="ats-panel-title">
                            <i class="fa fa-sitemap text-primary"></i> Recruitment Funnel Pipeline
                        </h4>
                        @if ($funnel['selected_opening'])
                            <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
                                Filtered Opening: <strong style="color: #0f172a;">{{ $funnel['selected_opening']['title'] }}</strong>
                                (Target Headcount: {{ $funnel['selected_opening']['headcount'] }} &bull; Filled: {{ $funnel['selected_opening']['filled_count'] }})
                            </div>
                        @else
                            <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
                                Total Applications Tracked: <strong style="color: #0f172a;">{{ number_format($funnel['total_applications']) }}</strong>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('recruitment.reports.export', ['type' => 'funnel'] + request()->query()) }}" class="btn btn-default btn-xs" style="border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-download"></i> CSV
                    </a>
                </div>

                <div style="position: relative; height: 320px;">
                    <canvas id="recruitmentFunnelChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Stage Conversion Stats Col --}}
        <div class="col-md-5 col-sm-12 col-xs-12">
            <div class="ats-card-panel">
                <div class="ats-panel-header">
                    <h4 class="ats-panel-title">
                        <i class="fa fa-list-ol text-info"></i> Pipeline Stage Breakdown
                    </h4>
                    <span class="badge" style="background: #e2e8f0; color: #475569; font-weight: 600;">
                        {{ $funnel['total_applications'] }} Total
                    </span>
                </div>

                <div style="max-height: 320px; overflow-y: auto; padding-right: 4px;">
                    @foreach ($funnel['stages'] as $key => $st)
                        <div style="margin-bottom: 13px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $st['color'] }};"></span>
                                    <span>{{ $st['label'] }}</span>
                                </div>
                                <div>
                                    <span style="color: #0f172a;">{{ number_format($st['count']) }}</span>
                                    <span style="color: #94a3b8; font-weight: 500; font-size: 12px; margin-left: 4px;">({{ $st['percentage'] }}%)</span>
                                </div>
                            </div>
                            <div class="ats-progress-wrap">
                                <div class="ats-progress-bar" style="width: {{ $st['percentage'] }}%; background: {{ $st['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Action Required Section (Action Items Trigger) --}}
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="ats-card-panel">
                <div class="ats-panel-header">
                    <div>
                        <h4 class="ats-panel-title">
                            <i class="fa fa-exclamation-triangle text-warning"></i> Action Required (Bottlenecks & Follow-ups)
                        </h4>
                        <p style="color: #64748b; font-size: 12px; margin: 3px 0 0 0;">
                            Items exceeding target SLA: Evaluations > {{ $actionRequired['thresholds']['eval_days'] }} days, Offers pending > {{ $actionRequired['thresholds']['offer_days'] }} days, Inactive candidates > {{ $actionRequired['thresholds']['inactive_days'] }} days.
                        </p>
                    </div>
                    <span class="badge" style="background: {{ $actionRequired['total_actions'] > 0 ? '#ef4444' : '#10b981' }}; color: #fff; font-size: 12px; padding: 4px 10px; border-radius: 12px;">
                        {{ $actionRequired['total_actions'] }} Urgent Item{{ $actionRequired['total_actions'] == 1 ? '' : 's' }}
                    </span>
                </div>

                <div class="ats-action-tabs">
                    <ul class="nav nav-pills" role="tablist" style="margin-bottom: 18px;">
                        <li role="presentation" class="active">
                            <a href="#tab_evaluations" aria-controls="tab_evaluations" role="tab" data-toggle="tab">
                                <i class="fa fa-clock-o"></i> Pending Evaluations
                                <span class="badge-counter">{{ $actionRequired['pending_evaluations']->count() }}</span>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_offers" aria-controls="tab_offers" role="tab" data-toggle="tab">
                                <i class="fa fa-file-text-o"></i> Offers Pending Approval
                                <span class="badge-counter">{{ $actionRequired['pending_offers']->count() }}</span>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_inactive" aria-controls="tab_inactive" role="tab" data-toggle="tab">
                                <i class="fa fa-user-times"></i> Inactive Candidates
                                <span class="badge-counter">{{ $actionRequired['inactive_candidates']->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Tab 1: Pending Evaluations --}}
                        <div role="tabpanel" class="tab-pane active" id="tab_evaluations">
                            @if ($actionRequired['pending_evaluations']->isEmpty())
                                <div class="empty-action-box">
                                    <i class="fa fa-check-circle"></i>
                                    <h4 style="font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">All caught up!</h4>
                                    <p style="margin: 0;">No interview evaluations are currently lagging beyond {{ $actionRequired['thresholds']['eval_days'] }} days.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-ats">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Job Position</th>
                                                <th>Round</th>
                                                <th>Assigned Interviewer</th>
                                                <th>Pending Days</th>
                                                <th style="text-align: right;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($actionRequired['pending_evaluations'] as $item)
                                                <tr>
                                                    <td>
                                                        <strong style="color: #0f172a;">{{ $item['candidate_name'] }}</strong>
                                                    </td>
                                                    <td>{{ $item['job_title'] }}</td>
                                                    <td>
                                                        <span class="label label-default" style="border-radius: 4px;">
                                                            Round {{ $item['round_no'] }} ({{ ucfirst($item['round_type']) }})
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="fa fa-user-circle text-muted"></i> {{ $item['interviewer_name'] }}
                                                    </td>
                                                    <td>
                                                        <span class="days-badge {{ $item['days_pending'] > 7 ? 'days-danger' : 'days-warning' }}">
                                                            {{ $item['days_pending'] }} days ago
                                                        </span>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <a href="{{ $item['url'] }}" class="btn btn-primary btn-xs" style="border-radius: 6px;">
                                                            View Candidate <i class="fa fa-arrow-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- Tab 2: Pending Offers --}}
                        <div role="tabpanel" class="tab-pane" id="tab_offers">
                            @if ($actionRequired['pending_offers']->isEmpty())
                                <div class="empty-action-box">
                                    <i class="fa fa-check-circle"></i>
                                    <h4 style="font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">No pending bottlenecks</h4>
                                    <p style="margin: 0;">All candidate offers are moving on schedule without approval delays.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-ats">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Position Offered</th>
                                                <th>Awaiting Approver</th>
                                                <th>Submitted Date</th>
                                                <th>Pending Aging</th>
                                                <th style="text-align: right;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($actionRequired['pending_offers'] as $item)
                                                <tr>
                                                    <td>
                                                        <strong style="color: #0f172a;">{{ $item['candidate_name'] }}</strong>
                                                    </td>
                                                    <td>{{ $item['job_title'] }}</td>
                                                    <td>
                                                        <span class="label label-info" style="border-radius: 4px;">
                                                            {{ $item['approver_name'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $item['created_at'] }}</td>
                                                    <td>
                                                        <span class="days-badge {{ $item['days_pending'] > 7 ? 'days-danger' : 'days-warning' }}">
                                                            {{ $item['days_pending'] }} days pending
                                                        </span>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <a href="{{ $item['url'] }}" class="btn btn-primary btn-xs" style="border-radius: 6px;">
                                                            Review Offer <i class="fa fa-arrow-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- Tab 3: Inactive Candidates --}}
                        <div role="tabpanel" class="tab-pane" id="tab_inactive">
                            @if ($actionRequired['inactive_candidates']->isEmpty())
                                <div class="empty-action-box">
                                    <i class="fa fa-check-circle"></i>
                                    <h4 style="font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Pipeline is actively maintained</h4>
                                    <p style="margin: 0;">No active candidate applications have been dormant for over {{ $actionRequired['thresholds']['inactive_days'] }} days.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-ats">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Job Position</th>
                                                <th>Current Stage</th>
                                                <th>Last Activity</th>
                                                <th>Dormant Period</th>
                                                <th style="text-align: right;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($actionRequired['inactive_candidates'] as $item)
                                                <tr>
                                                    <td>
                                                        <strong style="color: #0f172a;">{{ $item['candidate_name'] }}</strong>
                                                    </td>
                                                    <td>{{ $item['job_title'] }}</td>
                                                    <td>
                                                        <span class="stage-pill" style="background: #3b82f6;">
                                                            {{ $item['stage'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $item['last_activity_at'] }}</td>
                                                    <td>
                                                        <span class="days-badge days-danger">
                                                            {{ $item['days_pending'] }} days inactive
                                                        </span>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <a href="{{ $item['url'] }}" class="btn btn-default btn-xs" style="border-radius: 6px;">
                                                            Open Profile <i class="fa fa-external-link"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
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

    // Chart.js Pipeline Funnel Bar Chart
    var funnelCtx = document.getElementById('recruitmentFunnelChart');
    if (funnelCtx) {
        var stageData = @json(array_values($funnel['stages']));
        var labels = stageData.map(function(s) { return s.label; });
        var counts = stageData.map(function(s) { return s.count; });
        var colors = stageData.map(function(s) { return s.color; });

        new Chart(funnelCtx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Candidates',
                    data: counts,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1,
                    hoverOpacity: 0.85
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        },
                        gridLines: {
                            color: '#f1f5f9'
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto',
                            fontSize: 12,
                            fontColor: '#475569'
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var val = tooltipItem.xLabel;
                            var stage = stageData[tooltipItem.index];
                            return ' Candidates: ' + val + ' (' + stage.percentage + '% of total)';
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection


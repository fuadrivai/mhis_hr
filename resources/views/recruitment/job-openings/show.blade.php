@extends('layouts.main-layout')

@section('content-class')
    <style>
        .stage-pill { margin-bottom: 8px; }
        .summary-metric { padding: 10px 15px; border-radius: 4px; background: #f7f7f7; text-align: center; }
        .summary-metric .metric-val { font-size: 24px; font-weight: bold; margin-bottom: 2px; }
        .summary-metric .metric-lbl { font-size: 11px; text-transform: uppercase; color: #777; }
        .table-candidates td { vertical-align: middle !important; }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                {{ session('success') }}
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>
                    {{ $opening->title }}
                    @php
                        $badgeClass = match ($opening->status) {
                            'open' => 'label-success',
                            'filled' => 'label-primary',
                            'closed' => 'label-danger',
                            default => 'label-default',
                        };
                    @endphp
                    <span class="label {{ $badgeClass }}">{{ strtoupper($opening->status) }}</span>
                </h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.job-openings.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> All Openings
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recruitment.job-openings.edit', $opening) }}" class="btn btn-primary btn-sm text-white">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                    </li>
                    @if ($opening->status === 'open')
                        <li>
                            <a href="{{ route('recruitment.candidates.form', ['job_opening_id' => $opening->id]) }}" class="btn btn-success btn-sm text-white">
                                <i class="fa fa-user-plus"></i> Add Candidate
                            </a>
                        </li>
                    @endif
                    @if ($opening->jobRequirement || $opening->position_id)
                        <li>
                            <form method="POST" action="{{ route('recruitment.job-openings.batch-match', $opening) }}" style="display:inline-block; margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm text-white" title="Trigger AI CV Matching for all applicants in this opening" onclick="return confirm('Run AI CV evaluation for all applicants with CVs in this opening?')">
                                    <i class="fa fa-magic"></i> Batch AI Match
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                {{-- Metrics Overview --}}
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-metric">
                            <div class="metric-val text-primary">{{ $opening->headcount }}</div>
                            <div class="metric-lbl">Target Headcount</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-metric">
                            <div class="metric-val {{ $filledCount >= $opening->headcount ? 'text-success' : 'text-info' }}">
                                {{ $filledCount }}
                            </div>
                            <div class="metric-lbl">Joined / Filled</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-metric">
                            <div class="metric-val text-warning">{{ $activeCount }}</div>
                            <div class="metric-lbl">Active Candidates</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-metric">
                            <div class="metric-val text-muted">{{ $totalCount }}</div>
                            <div class="metric-lbl">Total Applicants</div>
                        </div>
                    </div>
                </div>

                {{-- Opening Details Table --}}
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 35%;">Branch</th>
                                    <td>{{ $opening->branch?->name ?: '--' }}</td>
                                </tr>
                                <tr>
                                    <th>Division / Dept</th>
                                    <td>{{ $opening->division ?: '--' }}</td>
                                </tr>
                                <tr>
                                    <th>Linked Position</th>
                                    <td>{{ $opening->position?->name ?: '--' }}</td>
                                </tr>
                                <tr>
                                    <th>Hiring Manager</th>
                                    <td>{{ $opening->hiringManager?->personal?->fullname ?: ($opening->hiringManager?->user?->name ?: '--') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 35%;">Opened At</th>
                                    <td>{{ $opening->opened_at ? $opening->opened_at->format('d F Y') : '--' }}</td>
                                </tr>
                                <tr>
                                    <th>Closes At</th>
                                    <td>{{ $opening->closes_at ? $opening->closes_at->format('d F Y') : 'Open until filled' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $opening->creator?->name ?: '--' }} ({{ $opening->created_at->format('d M Y H:i') }})</td>
                                </tr>
                                @if ($opening->status === 'closed' && $opening->closed_reason)
                                    <tr class="danger">
                                        <th>Closed Reason</th>
                                        <td>{{ $opening->closed_reason }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Requirement Template if linked --}}
                @if ($opening->jobRequirement)
                    <div class="panel panel-default" style="margin-top: 10px;">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="fa fa-file-text-o"></i> Job Requirement Template: {{ $opening->jobRequirement->title }}
                            </h4>
                        </div>
                        <div class="panel-body">
                            <p>{!! nl2br(e($opening->jobRequirement->description)) !!}</p>
                            @if ($opening->jobRequirement->details->isNotEmpty())
                                <h5>Requirements:</h5>
                                <ul>
                                    @foreach ($opening->jobRequirement->details as $item)
                                        <li>{{ $item->requirement }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="ln_solid"></div>

                {{-- Candidates by Stage Tabs --}}
                <h3><i class="fa fa-users"></i> Candidates Pipeline ({{ $totalCount }})</h3>

                <div class="" role="tabpanel" data-example-id="togglable-tabs">
                    <ul id="stageTabs" class="nav nav-tabs bar_tabs" role="tablist">
                        @foreach ($stages as $stageKey => $stageLabel)
                            <?php $count = count($applicationsByStage[$stageKey] ?? []); ?>
                            <li role="presentation" class="{{ $loop->first ? 'active' : '' }}">
                                <a href="#tab_{{ $stageKey }}" id="{{ $stageKey }}-tab" role="tab" data-toggle="tab" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $stageLabel }}
                                    <span class="badge {{ $count > 0 ? 'bg-green' : '' }}">{{ $count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div id="stageTabsContent" class="tab-content" style="padding-top: 15px;">
                        @foreach ($stages as $stageKey => $stageLabel)
                            <?php $apps = $applicationsByStage[$stageKey] ?? []; ?>
                            <div role="tabpanel" class="tab-pane fade {{ $loop->first ? 'active in' : '' }}" id="tab_{{ $stageKey }}">
                                @if (count($apps) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover table-candidates">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Candidate Name</th>
                                                    <th>Contact</th>
                                                    <th>Applied At</th>
                                                    <th>AI Score</th>
                                                    <th>Sub-status</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($apps as $app)
                                                    <tr>
                                                        <td>{{ $app->candidate?->candidate_code ?: '--' }}</td>
                                                        <td>
                                                            <strong>
                                                                <a href="{{ route('recruitment.candidates.show', $app->candidate) }}">
                                                                    {{ $app->candidate?->full_name }}
                                                                </a>
                                                            </strong>
                                                        </td>
                                                        <td>
                                                            <small>
                                                                <i class="fa fa-envelope-o"></i> {{ $app->candidate?->email }}<br>
                                                                <i class="fa fa-phone"></i> {{ $app->candidate?->phone }}
                                                            </small>
                                                        </td>
                                                        <td>{{ $app->applied_at ? $app->applied_at->format('d M Y H:i') : '--' }}</td>
                                                        <td>
                                                            <?php $match = $app->latestCvMatchResult; ?>
                                                            @if ($match && $match->status === 'completed')
                                                                <?php
                                                                    $score = $match->effective_score;
                                                                    $scoreBadge = $score >= 75 ? 'label-success' : ($score >= 50 ? 'label-warning' : 'label-danger');
                                                                ?>
                                                                <span class="label {{ $scoreBadge }}" style="font-size: 11px;">
                                                                    <i class="fa fa-bullseye"></i> {{ $score }}%
                                                                </span>
                                                                @if ($match->latestOverride)
                                                                    <br><small class="text-muted"><i class="fa fa-user"></i> Override</small>
                                                                @endif
                                                                @if ($match->mandatory_missing)
                                                                    <br><small class="text-danger"><i class="fa fa-warning"></i> Capped</small>
                                                                @endif
                                                            @elseif ($match && $match->status === 'needs_manual_review')
                                                                <span class="label label-warning" style="font-size: 10px;">Review</span>
                                                            @elseif ($match && $match->status === 'failed')
                                                                <span class="label label-default" style="font-size: 10px;">Failed</span>
                                                            @elseif ($match)
                                                                <span class="label label-info" style="font-size: 10px;"><i class="fa fa-spinner fa-spin"></i> Queued</span>
                                                            @elseif ($app->resume_path)
                                                                <span class="text-muted" style="font-size: 11px;">Pending</span>
                                                            @else
                                                                <span class="text-muted" style="font-size: 11px;">--</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($app->stage_substatus)
                                                                <span class="label label-info">{{ ucfirst(str_replace('_', ' ', $app->stage_substatus)) }}</span>
                                                            @else
                                                                <span class="text-muted">--</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('recruitment.candidates.show', $app->candidate) }}" class="btn btn-primary btn-xs">
                                                                <i class="fa fa-eye"></i> View Profile
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted" style="padding: 30px;">
                                        <i class="fa fa-folder-open-o fa-2x"></i>
                                        <p style="margin-top: 10px;">No candidates in <strong>{{ $stageLabel }}</strong> stage.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

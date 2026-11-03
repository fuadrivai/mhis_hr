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
                                    <div style="margin-bottom: 10px;" class="text-right">
                                        <button type="button" class="btn btn-warning btn-sm btnOpenBulkScheduleModal" style="display: none;" data-toggle="modal" data-target="#bulkScheduleInterviewModal">
                                            <i class="fa fa-calendar-plus-o"></i> Bulk Schedule Interview (<span class="bulkSelectedCount">0</span>)
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover table-candidates">
                                            <thead>
                                                <tr>
                                                    <th style="width: 35px;" class="text-center">
                                                        <input type="checkbox" class="bulkSelectAllCandidates" title="Select all in this tab">
                                                    </th>
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
                                                        <td class="text-center">
                                                            <input type="checkbox" class="candidate-row-select" value="{{ $app->id }}" data-candidate-name="{{ $app->candidate?->full_name }}" data-code="{{ $app->candidate?->candidate_code }}" data-position="{{ $opening->title }}">
                                                        </td>
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

    <!-- Modal Bulk Schedule Interview -->
    <div class="modal fade" id="bulkScheduleInterviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('recruitment.interviews.bulk-schedule') }}" id="bulkScheduleForm">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">
                            <i class="fa fa-calendar-plus-o text-primary"></i> Bulk Schedule Interview Rounds & Evaluations
                        </h4>
                    </div>
                    <div class="modal-body">
                        <!-- Selected Candidates List -->
                        <div class="form-group">
                            <label>Selected Candidates (<span id="modalSelectedCount">0</span> candidates)</label>
                            <div id="bulkSelectedCandidatesContainer" style="max-height: 120px; overflow-y: auto; padding: 10px; background: #f9f9f9; border: 1px solid #e5e5e5; border-radius: 4px;">
                                <!-- Populated by JS -->
                            </div>
                            <div id="bulkApplicationIdsInputs"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>Round Type <span class="text-danger">*</span></label>
                                    <select name="round_type" class="form-control" required>
                                        <option value="user" selected>User / Hiring Manager</option>
                                        <option value="hr">HR Interview</option>
                                        <option value="panel">Panel Interview</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>Start Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control" required value="{{ now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d\TH:i') }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>Time Slot Interval</label>
                                    <select name="slot_interval_minutes" class="form-control">
                                        <option value="0">Same Time for All (Simultaneous / Panel)</option>
                                        <option value="15">15 mins interval per candidate</option>
                                        <option value="30" selected>30 mins interval per candidate</option>
                                        <option value="45">45 mins interval per candidate</option>
                                        <option value="60">60 mins interval (1 hour each)</option>
                                    </select>
                                    <small class="text-muted">Stagger candidate interview times automatically.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Select Interviewers (Employees) <span class="text-danger">*</span> <small class="text-muted">(You can select more than one interviewer)</small></label>
                            <select name="interviewers[]" class="form-control select2-bulk-interviewers" multiple="multiple" style="width: 100%;" required data-placeholder="Choose interviewers (employees with system accounts)...">
                                @if (!empty($assignableInterviewers))
                                    @foreach ($assignableInterviewers as $emp)
                                        @php
                                            $pName = trim(($emp->personal?->first_name ?? '') . ' ' . ($emp->personal?->last_name ?? ''));
                                            $eName = !empty($pName) ? $pName : ($emp->user?->name ?? "Employee #{$emp->id}");
                                            $posName = $emp->employment?->job_position?->name ?? $emp->job_position?->name ?? '';
                                        @endphp
                                        <option value="{{ $emp->id }}">
                                            {{ $eName }} {{ $posName ? "({$posName})" : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">An interview evaluation draft will be automatically generated for every selected interviewer.</small>
                        </div>

                        <div class="form-group">
                            <label>Meeting Location or Online Link</label>
                            <input type="text" name="location_or_link" class="form-control" placeholder="e.g. Meeting Room 2, Campus or https://meet.google.com/xyz-abcd-efg" maxlength="500">
                        </div>

                        <div class="form-group">
                            <label>Notes / Briefing for Interviewers</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Focus competencies, questions to ask, or candidate evaluation guidelines"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitBulkSchedule">
                            <i class="fa fa-calendar-check-o"></i> Schedule Interviews & Set Evaluations
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
<script>
$(function() {
    function updateBulkState() {
        var $checked = $('.candidate-row-select:checked');
        var count = $checked.length;
        $('.bulkSelectedCount').text(count);
        $('#modalSelectedCount').text(count);

        if (count > 0) {
            $('.btnOpenBulkScheduleModal').show();
        } else {
            $('.btnOpenBulkScheduleModal').hide();
        }

        var $container = $('#bulkSelectedCandidatesContainer');
        var $inputs = $('#bulkApplicationIdsInputs');
        $container.empty();
        $inputs.empty();

        $checked.each(function() {
            var appId = $(this).val();
            var name = $(this).data('candidate-name');
            var code = $(this).data('code');
            var pos = $(this).data('position');

            $container.append(
                '<span class="label label-primary" style="display: inline-block; margin: 3px; font-size: 12px; padding: 5px 8px; font-weight: normal;">' +
                '<strong>' + name + '</strong> (' + (code || 'ID:' + appId) + ')' +
                (pos ? ' - ' + pos : '') +
                '</span>'
            );

            $inputs.append('<input type="hidden" name="application_ids[]" value="' + appId + '">');
        });
    }

    $(document).on('change', '.bulkSelectAllCandidates', function() {
        var $tab = $(this).closest('.tab-pane');
        var isChecked = $(this).is(':checked');
        $tab.find('.candidate-row-select').prop('checked', isChecked);
        updateBulkState();
    });

    $(document).on('change', '.candidate-row-select', function() {
        updateBulkState();
    });

    $('#bulkScheduleInterviewModal').on('shown.bs.modal', function() {
        if ($.fn.select2) {
            $('.select2-bulk-interviewers').select2({
                dropdownParent: $('#bulkScheduleInterviewModal'),
                width: '100%'
            });
        }
    });
});
</script>
@endsection

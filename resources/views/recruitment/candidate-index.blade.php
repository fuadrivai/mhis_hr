@extends('layouts.main-layout')

@section('content-class')
    <style>
        .candidate-filter .form-group { margin-bottom: 12px; }
        .candidate-filter .form-control { min-width: 130px; }
        .candidate-actions { min-width: 170px; white-space: nowrap; }
        .candidate-actions .btn { margin: 2px 0; }
        .candidate-sort { color: inherit; }
        .candidate-sort:hover { color: #990000; text-decoration: none; }
    </style>
@endsection

@section('content-child')
    @php
        $sortUrl = function ($column) {
            $direction = request('sort') === $column && request('direction', 'desc') === 'asc' ? 'desc' : 'asc';

            return route('recruitment.candidates', array_merge(request()->query(), [
                'sort' => $column,
                'direction' => $direction,
            ]));
        };
        $stageLabels = $stages ?? $filterData['stages'] ?? [];
        $statusLabels = $statuses ?? $filterData['statuses'] ?? [];
        $branches = $branches ?? $filterData['branches'] ?? [];
        $openings = $openings ?? $filterData['openings'] ?? [];
        $positions = $positions ?? $filterData['positions'] ?? [];
        $sources = $sources ?? $filterData['sources'] ?? [];
        $hrPics = $hrPics ?? $filterData['hrPics'] ?? [];
    @endphp

    <div class="col-md-12 col-sm-12 col-xs-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
                {{ session('success') }}
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>Candidate Database <small>One row per candidate; application data reflects the latest matching application</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.candidates.form') }}" class="btn btn-success btn-sm text-white">
                            <i class="fa fa-user-plus"></i> Add Candidate
                        </a>
                    </li>
                    <li style="margin-left: 5px;">
                        <a href="{{ route('recruitment.candidates.form', ['tab' => 'bulk_cv']) }}" class="btn btn-primary btn-sm text-white">
                            <i class="fa fa-folder-open"></i> Bulk Upload CVs
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="GET" action="{{ route('recruitment.candidates') }}" class="candidate-filter">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 form-group">
                            <label for="q">Search</label>
                            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control"
                                placeholder="Name, email, phone, candidate code">
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="branch_id">Branch</label>
                            <select id="branch_id" name="branch_id" class="form-control">
                                <option value="">All branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="job_opening_id">Job Opening</label>
                            <select id="job_opening_id" name="job_opening_id" class="form-control">
                                <option value="">All Job Openings</option>
                                @foreach ($openings as $opening)
                                    <option value="{{ $opening->id }}" @selected((string) ($filters['job_opening_id'] ?? '') === (string) $opening->id)>
                                        {{ $opening->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="position">Position</label>
                            <select id="position" name="position" class="form-control">
                                <option value="">All positions</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position }}" @selected(($filters['position'] ?? '') === $position)>
                                        {{ $position }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="stage">Current Stage</label>
                            <select id="stage" name="stage" class="form-control">
                                <option value="">All stages</option>
                                @foreach ($stageLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['stage'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="status">Application Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All statuses</option>
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="source">Candidate Source</label>
                            <select id="source" name="source" class="form-control">
                                <option value="">All sources</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>
                                        {{ $source }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="hr_pic_id">HR PIC</label>
                            <select id="hr_pic_id" name="hr_pic_id" class="form-control">
                                <option value="">All HR PIC</option>
                                @foreach ($hrPics as $pic)
                                    <option value="{{ $pic->id }}" @selected((string) ($filters['hr_pic_id'] ?? '') === (string) $pic->id)>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="applied_from">Applied From</label>
                            <input type="date" id="applied_from" name="applied_from" class="form-control"
                                value="{{ $filters['applied_from'] ?? '' }}">
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="applied_to">Applied To</label>
                            <input type="date" id="applied_to" name="applied_to" class="form-control"
                                value="{{ $filters['applied_to'] ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="psychotest_result">Psychotest</label>
                            <select id="psychotest_result" name="psychotest_result" class="form-control">
                                <option value="">All results</option>
                                <option value="recommended" @selected(($filters['psychotest_result'] ?? '') === 'recommended')>Recommended</option>
                                <option value="considered" @selected(($filters['psychotest_result'] ?? '') === 'considered')>Considered</option>
                                <option value="not_recommended" @selected(($filters['psychotest_result'] ?? '') === 'not_recommended')>Not Recommended</option>
                                <option value="untested" @selected(($filters['psychotest_result'] ?? '') === 'untested')>Untested</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="interview_status">Interview</label>
                            <select id="interview_status" name="interview_status" class="form-control">
                                <option value="">All interviews</option>
                                <option value="scheduled" @selected(($filters['interview_status'] ?? '') === 'scheduled')>Scheduled</option>
                                <option value="awaiting_evaluation" @selected(($filters['interview_status'] ?? '') === 'awaiting_evaluation')>Awaiting Evaluation</option>
                                <option value="done" @selected(($filters['interview_status'] ?? '') === 'done')>Done</option>
                                <option value="not_scheduled" @selected(($filters['interview_status'] ?? '') === 'not_scheduled')>Not Scheduled</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="inactive_days">No activity for</label>
                            <select id="inactive_days" name="inactive_days" class="form-control">
                                <option value="">Any activity</option>
                                @foreach ([30, 60, 90, 180] as $days)
                                    <option value="{{ $days }}" @selected((string) ($filters['inactive_days'] ?? '') === (string) $days)>
                                        {{ $days }}+ days
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" class="form-control">
                                @foreach ([25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((string) ($filters['per_page'] ?? '25') === (string) $size)>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12 form-group text-right" style="padding-top: 25px;">
                            <button type="button" class="btn btn-warning" id="btnOpenBulkScheduleModal" style="display: none;" data-toggle="modal" data-target="#bulkScheduleInterviewModal">
                                <i class="fa fa-calendar-plus-o"></i> Bulk Schedule Interviews (<span id="bulkSelectedCount">0</span>)
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('recruitment.candidates') }}" class="btn btn-default">Reset</a>
                            <a href="{{ route('recruitment.candidates.export', request()->query()) }}"
                                class="btn btn-success">
                                <i class="fa fa-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">
                                    <input type="checkbox" id="bulkSelectAllCandidates" title="Select all on this page">
                                </th>
                                <th>Candidate Code</th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('candidate') }}">Candidate Name</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('position') }}">Opening / Position</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('branch') }}">Branch</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('stage') }}">Current Stage</a></th>
                                <th>AI Score</th>
                                <th>Psychotest</th>
                                <th>Interview</th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('updated_at') }}">Updated At</a></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($candidates as $candidate)
                                <?php $application = $candidate->applications->first(); ?>
                                <tr>
                                    <td class="text-center">
                                        @if ($application)
                                            <input type="checkbox" class="candidate-row-select" value="{{ $application->id }}" data-candidate-name="{{ $candidate->full_name }}" data-code="{{ $candidate->candidate_code }}" data-position="{{ $application->jobOpening?->title ?: ($application->position_applied ?: '') }}">
                                        @endif
                                    </td>
                                    <td>{{ $candidate->candidate_code ?: '--' }}</td>
                                    <td>
                                        <a href="{{ route('recruitment.candidates.show', $candidate) }}" style="font-weight: 600;">
                                            {{ $candidate->full_name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            {{ $candidate->email }} · {{ $candidate->phone }}
                                        </small>
                                        <br>
                                        @if ($candidate->applications_count > 1)
                                            <span class="label label-info">Returning applicant ({{ $candidate->applications_count }})</span>
                                        @else
                                            <span class="label label-default">First application</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($application?->jobOpening)
                                            <a href="{{ route('recruitment.job-openings.show', $application->jobOpening) }}">
                                                {{ $application->jobOpening->title }}
                                            </a>
                                        @else
                                            {{ $application?->position_applied ?: ($application?->position?->name ?: '--') }}
                                        @endif
                                    </td>
                                    <td>{{ $application?->branch?->name ?: '--' }}</td>
                                    <td>
                                        @php
                                            $stage = $application?->stage ?? 'applied';
                                            $badgeClass = match ($stage) {
                                                'applied' => 'label-default',
                                                'screening' => 'label-info',
                                                'psychotest', 'interview' => 'label-primary',
                                                'offer' => 'label-warning',
                                                'contract_signing' => 'label-info',
                                                'joined' => 'label-success',
                                                'rejected' => 'label-danger',
                                                'withdrawn' => 'label-default',
                                                default => 'label-primary',
                                            };
                                        @endphp
                                        <span class="label {{ $badgeClass }}">
                                            {{ $stageLabels[$stage] ?? ucfirst(str_replace('_', ' ', $stage)) }}
                                        </span>
                                        @if ($application?->stage_substatus)
                                            <br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $application->stage_substatus)) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <?php $match = $application?->latestCvMatchResult; ?>
                                        @if ($match && $match->status === 'completed')
                                            @php
                                                $score = $match->effective_score;
                                                $scoreBadge = $score >= 75 ? 'label-success' : ($score >= 50 ? 'label-warning' : 'label-danger');
                                            @endphp
                                            <span class="label {{ $scoreBadge }}" style="font-size: 11px;">
                                                <i class="fa fa-bullseye"></i> {{ $score }}%
                                            </span>
                                            @if ($match->latestOverride)
                                                <br><small class="text-muted" title="HR Override: {{ $match->latestOverride->note }}"><i class="fa fa-user"></i> Override</small>
                                            @endif
                                            @if ($match->mandatory_missing)
                                                <br><small class="text-danger" title="Mandatory missing - capped at 60"><i class="fa fa-warning"></i> Capped</small>
                                            @endif
                                        @elseif ($match && $match->status === 'needs_manual_review')
                                            <span class="label label-warning" title="CV text unreadable / needs review" style="font-size: 10px;">Review</span>
                                        @elseif ($match && $match->status === 'failed')
                                            <span class="label label-default" title="{{ $match->error_message }}" style="font-size: 10px;">Failed</span>
                                        @elseif ($match)
                                            <span class="label label-info" style="font-size: 10px;"><i class="fa fa-spinner fa-spin"></i> Queued</span>
                                        @elseif ($application?->resume_path)
                                            <span class="text-muted" style="font-size: 11px;">Pending</span>
                                        @else
                                            <span class="text-muted" style="font-size: 11px;">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $assessment = $application?->latestAssessment;
                                        @endphp
                                        @if ($assessment)
                                            <span class="label label-{{ $assessment->conclusion_badge_class }}" style="font-size: 11px;">
                                                {{ $assessment->conclusion_label }}
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-size: 11px;">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $round = $application?->latestInterviewRound;
                                        @endphp
                                        @if ($round)
                                            @if ($round->status === 'done')
                                                <span class="label label-success" style="font-size: 11px;">
                                                    <i class="fa fa-check"></i> Done (R{{ $round->round_no }})
                                                </span>
                                            @elseif ($round->status === 'cancelled')
                                                <span class="label label-default" style="font-size: 11px;">Cancelled</span>
                                            @elseif ($application?->stage_substatus === 'awaiting_evaluation')
                                                <span class="label label-warning" style="font-size: 11px;">
                                                    <i class="fa fa-clock-o"></i> Awaiting Eval
                                                </span>
                                            @else
                                                <span class="label label-info" style="font-size: 11px;">
                                                    <i class="fa fa-calendar"></i> R{{ $round->round_no }} Scheduled
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted" style="font-size: 11px;">--</span>
                                        @endif
                                    </td>
                                    <td>{{ $candidate->updated_at?->format('d M Y H:i') ?: '--' }}</td>
                                    <td class="text-center candidate-actions">
                                        <a href="{{ route('recruitment.candidates.show', $candidate) }}"
                                            class="btn btn-primary btn-xs" title="View profile">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('recruitment.candidates.edit', $candidate) }}"
                                            class="btn btn-default btn-xs" title="Edit candidate">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted" style="padding: 30px;">
                                        No candidates found matching the search and filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right">
                    {{ $candidates->links() }}
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
        $('#bulkSelectedCount').text(count);
        $('#modalSelectedCount').text(count);

        if (count > 0) {
            $('#btnOpenBulkScheduleModal').show();
        } else {
            $('#btnOpenBulkScheduleModal').hide();
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

    $('#bulkSelectAllCandidates').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.candidate-row-select').prop('checked', isChecked);
        updateBulkState();
    });

    $(document).on('change', '.candidate-row-select', function() {
        var total = $('.candidate-row-select').length;
        var checked = $('.candidate-row-select:checked').length;
        $('#bulkSelectAllCandidates').prop('checked', total > 0 && total === checked);
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

@extends('layouts.main-layout')



@section('content-class')

    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">

    <style>

        .application-pipeline-form { min-width: 190px; }

        .timeline-widget { list-style: none; padding-left: 20px; border-left: 2px solid #e5e5e5; margin-left: 10px; margin-top: 15px; }

        .timeline-widget li { position: relative; margin-bottom: 20px; }

        .timeline-widget li .timeline-dot { position: absolute; left: -27px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: #26B99A; border: 2px solid #fff; }

        .timeline-widget li .timeline-dot.danger { background: #d9534f; }

        .timeline-widget li .timeline-dot.warning { background: #f0ad4e; }

        .timeline-widget li .timeline-dot.primary { background: #337ab7; }

        .timeline-content { background: #fafafa; border: 1px solid #e8e8e8; border-radius: 4px; padding: 10px 14px; }

        .timeline-time { font-size: 11px; color: #888; }

    </style>

@endsection



@section('content-child')

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">

            <div class="x_title">

                <h2>Candidate Profile <small>{{ $candidate->candidate_code ?: 'Candidate' }}</small></h2>

                <ul class="nav navbar-right panel_toolbox">

                    <li>

                        <a href="{{ route('recruitment.candidates.applications.create', $candidate) }}"

                            class="btn btn-success btn-sm text-white">

                            <i class="fa fa-plus"></i> Add Application

                        </a>

                    </li>

                    <li>

                        <a href="{{ route('recruitment.candidates.edit', $candidate) }}"

                            class="btn btn-default btn-sm">

                            <i class="fa fa-pencil"></i> Edit Candidate

                        </a>

                    </li>

                    <li>

                        <a href="{{ route('recruitment.candidates') }}" class="btn btn-default btn-sm">

                            <i class="fa fa-arrow-left"></i> Back to Database

                        </a>

                    </li>

                </ul>

                <div class="clearfix"></div>

            </div>

            <div class="x_content">

                @if (session('success'))

                    <div class="alert alert-success alert-dismissible fade in" role="alert">

                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>

                        {{ session('success') }}

                    </div>

                @endif

                @if ($errors->any())

                    <div class="alert alert-danger alert-dismissible fade in" role="alert">

                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>

                        <ul style="margin-bottom: 0;">

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif

                <div class="row">

                    <div class="col-md-4 col-sm-6">

                        <strong>Full Name</strong>

                        <p>{{ $candidate->full_name }}</p>

                    </div>

                    <div class="col-md-4 col-sm-6">

                        <strong>Email</strong>

                        <p><a href="mailto:{{ $candidate->email }}">{{ $candidate->email }}</a></p>

                    </div>

                    <div class="col-md-4 col-sm-6">

                        <strong>Phone Number</strong>

                        <p><a href="tel:{{ $candidate->phone }}">{{ $candidate->phone }}</a></p>

                    </div>

                    <div class="col-md-4 col-sm-6">

                        <strong>Current City</strong>

                        <p>{{ $candidate->current_city ?: '--' }}</p>

                    </div>

                    <div class="col-md-4 col-sm-6">

                        <strong>Candidate Code</strong>

                        <p>{{ $candidate->candidate_code ?: '--' }}</p>

                    </div>

                    <div class="col-md-4 col-sm-6">

                        <strong>Total Applications</strong>

                        <p><span class="badge">{{ $candidate->applications->count() }}</span></p>

                    </div>

                </div>

            </div>

        </div>



        {{-- Applications Table --}}

        <div class="x_panel">

            <div class="x_title">

                <h2>Application Records</h2>

                <div class="clearfix"></div>

            </div>

            <div class="x_content table-responsive">

                <table id="candidate-applications-table" class="table table-striped table-bordered" style="width: 100%">

                    <thead>

                        <tr>

                            <th>Job Opening / Position</th>

                            <th>Branch</th>

                            <th>Current Stage</th>

                            <th>Sub-status</th>

                            <th>Applied At</th>

                            <th>CV / Resume</th>

                            <th class="text-center">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($candidate->applications as $application)

                            <tr>

                                <td>

                                    @if ($application->jobOpening)

                                        <a href="{{ route('recruitment.job-openings.show', $application->jobOpening) }}" style="font-weight: 600;">

                                            {{ $application->jobOpening->title }}

                                        </a>

                                        <br><small class="text-muted"><i class="fa fa-briefcase"></i> Job Opening</small>

                                    @else

                                        <strong>{{ $application->position_applied ?: ($application->position->name ?? '--') }}</strong>

                                        <br><small class="text-muted">Legacy Position</small>

                                    @endif

                                </td>

                                <td>{{ $application->branch->name ?? '--' }}</td>

                                <td>

                                    @php

                                        $badgeClass = match ($application->stage) {

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

                                    <span class="label {{ $badgeClass }}">{{ $stages[$application->stage] ?? ucfirst(str_replace('_', ' ', $application->stage)) }}</span>

                                </td>

                                <td>

                                    {{ $application->stage_substatus ? ucfirst(str_replace('_', ' ', $application->stage_substatus)) : '--' }}

                                </td>

                                <td>{{ $application->applied_at?->format('d M Y H:i') ?: '--' }}</td>

                                <td>

                                    @if ($application->resume_path)

                                        <a href="{{ route('recruitment.candidates.resume', [$candidate, $application]) }}" class="btn btn-default btn-xs">

                                            <i class="fa fa-download"></i> Download CV

                                        </a>

                                    @else

                                        <span class="text-muted">No CV attached</span>

                                    @endif

                                </td>

                                <td class="text-center">

                                    <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#updateStageModal-{{ $application->id }}">

                                        <i class="fa fa-pencil-square-o"></i> Update Stage

                                    </button>



                                    {{-- Update Stage Modal --}}

                                    <div class="modal fade" id="updateStageModal-{{ $application->id }}" tabindex="-1" role="dialog" aria-hidden="true">

                                        <div class="modal-dialog">

                                            <div class="modal-content text-left">

                                                <form method="POST" action="{{ route('recruitment.candidates.applications.pipeline', [$candidate, $application]) }}">

                                                    @csrf

                                                    @method('PATCH')

                                                    <div class="modal-header">

                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>

                                                        <h4 class="modal-title">Update Pipeline Stage</h4>

                                                    </div>

                                                    <div class="modal-body">

                                                        <div class="form-group">

                                                            <label for="stage_{{ $application->id }}">Target Stage <span class="text-danger">*</span></label>

                                                            <select name="stage" id="stage_{{ $application->id }}" class="form-control" required>

                                                                @foreach ($stages as $value => $label)

                                                                    <option value="{{ $value }}" @selected($application->stage === $value)>

                                                                        {{ $label }}

                                                                    </option>

                                                                @endforeach

                                                            </select>

                                                        </div>



                                                        <div class="form-group">

                                                            <label for="substatus_{{ $application->id }}">Sub-status <small class="text-muted">(Optional, e.g. scheduled, done, pending, signed)</small></label>

                                                            <input type="text" name="stage_substatus" id="substatus_{{ $application->id }}" class="form-control"

                                                                value="{{ old('stage_substatus', $application->stage_substatus) }}" placeholder="e.g. scheduled, done, sent, signed">

                                                        </div>



                                                        <div class="form-group">

                                                            <label for="hr_pic_id_{{ $application->id }}">Assign HR PIC</label>

                                                            <select name="hr_pic_id" id="hr_pic_id_{{ $application->id }}" class="form-control">

                                                                <option value="">-- Unassigned --</option>

                                                                @foreach ($assignableHrPics as $pic)

                                                                    <option value="{{ $pic->id }}" @selected((string) $application->hr_pic_id === (string) $pic->id)>

                                                                        {{ $pic->name }}

                                                                    </option>

                                                                @endforeach

                                                            </select>

                                                        </div>



                                                        <div class="form-group">

                                                            <label for="reason_{{ $application->id }}">

                                                                Reason / Notes

                                                                <small class="text-danger">(Mandatory for Rejection, Withdrawal, or moving back to an earlier stage)</small>

                                                            </label>

                                                            <textarea name="reason" id="reason_{{ $application->id }}" class="form-control" rows="3"

                                                                placeholder="Explain the reason for this stage change..."></textarea>

                                                        </div>

                                                    </div>

                                                    <div class="modal-footer">

                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>

                                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Stage Transition</button>

                                                    </div>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>



        {{-- AI CV Matching Evaluation Panel --}}

        @foreach ($candidate->applications as $application)

            @php

                $match = $application->latestCvMatchResult;

            @endphp

            <div class="x_panel">

                <div class="x_title">

                    <h2>

                        <i class="fa fa-magic text-primary"></i> AI CV Matching Evaluation —

                        <small>

                            {{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Application #' . $application->id) }}

                            ({{ $application->branch?->name }})

                        </small>

                    </h2>

                    <ul class="nav navbar-right panel_toolbox">

                        @if ($application->resume_path)

                            <li>

                                <form method="POST" action="{{ route('recruitment.candidates.match-cv', [$candidate, $application]) }}" style="margin: 0; display: inline-block;">

                                    @csrf

                                    <button type="submit" class="btn btn-primary btn-xs" style="color: #fff; margin-top: 5px;">

                                        <i class="fa fa-refresh"></i> {{ $match ? 'Re-run AI Matching' : 'Run AI CV Matching' }}

                                    </button>

                                </form>

                            </li>

                        @endif

                        @if ($match && $match->status === 'completed')

                            <li>

                                <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#overrideScoreModal-{{ $match->id }}" style="color: #fff; margin-top: 5px; margin-left: 5px;">

                                    <i class="fa fa-pencil"></i> Override Score

                                </button>

                            </li>

                        @endif

                    </ul>

                    <div class="clearfix"></div>

                </div>

                <div class="x_content">

                    @if (!$application->resume_path)

                        <div class="alert alert-default text-muted" style="background: #f9f9f9; border: 1px dashed #ccc;">

                            <i class="fa fa-file-text-o"></i> No CV or resume uploaded for this application. Please upload a CV to enable AI criteria evaluation.

                        </div>

                    @elseif (!$match)

                        <div class="alert alert-info" style="background: #eef7ff; border-color: #d0e3f7; color: #1e5a8a;">

                            <p style="margin: 0 0 10px 0;">

                                <i class="fa fa-info-circle"></i> A CV is attached for this candidate, but AI matching has not been processed yet.

                            </p>

                            <form method="POST" action="{{ route('recruitment.candidates.match-cv', [$candidate, $application]) }}">

                                @csrf

                                <button type="submit" class="btn btn-primary btn-sm">

                                    <i class="fa fa-magic"></i> Evaluate CV against Job Requirements Now

                                </button>

                            </form>

                        </div>

                    @elseif ($match->status === 'failed')

                        <div class="alert alert-danger">

                            <i class="fa fa-exclamation-triangle"></i> <strong>Matching Failed:</strong> {{ $match->error_message ?: 'AI evaluation could not be completed.' }}

                        </div>

                    @elseif ($match->status === 'needs_manual_review')

                        <div class="alert alert-warning">

                            <h4 style="margin-top: 0;"><i class="fa fa-exclamation-triangle"></i> Needs Manual Review</h4>

                            <p>{{ $match->summary ?: 'CV text could not be extracted cleanly (poor scan or non-standard format). Please review candidate CV manually.' }}</p>

                        </div>

                    @elseif ($match->status === 'completed')

                        {{-- Score Summary Banner --}}

                        <div class="row" style="margin-bottom: 20px;">

                            <div class="col-md-3 col-sm-6 text-center">

                                <div class="well" style="padding: 15px; margin-bottom: 10px; background: #fff; border: 2px solid {{ $match->effective_score >= 75 ? '#26B99A' : ($match->effective_score >= 50 ? '#f0ad4e' : '#d9534f') }}; border-radius: 6px;">

                                    <div style="font-size: 32px; font-weight: 800; color: {{ $match->effective_score >= 75 ? '#26B99A' : ($match->effective_score >= 50 ? '#f0ad4e' : '#d9534f') }};">

                                        {{ $match->effective_score }}<span style="font-size: 16px; font-weight: normal; color: #777;">/100</span>

                                    </div>

                                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: #666; margin-top: 4px;">

                                        Final Screening Score

                                    </div>

                                    @if ($match->latestOverride)

                                        <span class="label label-warning" style="font-size: 10px; margin-top: 4px; display: inline-block;">

                                            HR Overridden (AI: {{ $match->total_score }})

                                        </span>

                                    @endif

                                </div>

                            </div>

                            <div class="col-md-9 col-sm-6">

                                @if ($match->mandatory_missing)

                                    <div class="alert alert-danger" style="margin-bottom: 10px; padding: 10px 14px;">

                                        <strong><i class="fa fa-ban"></i> Mandatory Requirement Missing:</strong> One or more mandatory criteria were not satisfied. Under MHIS ATS screening rules, total score is capped at 60.

                                    </div>

                                @endif



                                <div style="background: #fafafa; border: 1px solid #e5e5e5; border-radius: 4px; padding: 12px 16px;">

                                    <strong style="color: #333;"><i class="fa fa-quote-left text-muted"></i> Executive Summary:</strong>

                                    <p style="margin: 6px 0 0 0; color: #444; line-height: 1.5;">

                                        {{ $match->summary ?: 'No summary provided.' }}

                                    </p>

                                </div>

                            </div>

                        </div>



                        {{-- Red Flags & Suggested Questions --}}

                        <div class="row" style="margin-bottom: 20px;">

                            @if (!empty($match->red_flags))

                                <div class="col-md-6 col-sm-12">

                                    <div class="panel panel-danger">

                                        <div class="panel-heading" style="padding: 8px 12px; font-weight: 600;">

                                            <i class="fa fa-flag"></i> Screening Red Flags & Risks

                                        </div>

                                        <div class="panel-body" style="padding: 10px 15px;">

                                            <ul style="margin: 0; padding-left: 20px; color: #a94442;">

                                                @foreach ($match->red_flags as $flag)

                                                    <li style="margin-bottom: 4px;">{{ $flag }}</li>

                                                @endforeach

                                            </ul>

                                        </div>

                                    </div>

                                </div>

                            @endif



                            @if (!empty($match->interview_questions))

                                <div class="{{ !empty($match->red_flags) ? 'col-md-6' : 'col-md-12' }} col-sm-12">

                                    <div class="panel panel-info">

                                        <div class="panel-heading" style="padding: 8px 12px; font-weight: 600;">

                                            <i class="fa fa-comments-o"></i> Recommended Interview Questions

                                        </div>

                                        <div class="panel-body" style="padding: 10px 15px;">

                                            <ol style="margin: 0; padding-left: 20px; color: #31708f;">

                                                @foreach ($match->interview_questions as $q)

                                                    <li style="margin-bottom: 4px;">{{ $q }}</li>

                                                @endforeach

                                            </ol>

                                        </div>

                                    </div>

                                </div>

                            @endif

                        </div>



                        {{-- Criteria Breakdown Table --}}

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped" style="margin-bottom: 15px;">

                                <thead>

                                    <tr style="background: #f5f5f5;">

                                        <th style="width: 28%;">Job Requirement Criteria</th>

                                        <th style="width: 10%;">Category</th>

                                        <th class="text-center" style="width: 10%;">Importance</th>

                                        <th class="text-center" style="width: 12%;">AI Evaluation</th>

                                        <th style="width: 25%;">Extracted CV Evidence</th>

                                        <th style="width: 15%;">Reasoning</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse ($match->requirementResults as $item)

                                        <tr>

                                            <td>

                                                <strong>{{ $item->requirement_text }}</strong>

                                                @if ($item->is_mandatory)

                                                    <br><span class="label label-danger" style="font-size: 10px;">Mandatory</span>

                                                @endif

                                            </td>

                                            <td>

                                                <span class="label label-default" style="text-transform: capitalize;">{{ $item->category }}</span>

                                            </td>

                                            <td class="text-center">

                                                <span title="Weight: {{ $item->weight }}/5">

                                                    @for ($w = 1; $w <= 5; $w++)

                                                        <i class="fa fa-star {{ $w <= $item->weight ? 'text-warning' : 'text-muted' }}" style="font-size: 11px;"></i>

                                                    @endfor

                                                </span>

                                            </td>

                                            <td class="text-center">

                                                @php

                                                    $stClass = match ($item->status) {

                                                        'met' => 'label-success',

                                                        'partial' => 'label-warning',

                                                        'not_met' => 'label-danger',

                                                        default => 'label-default',

                                                    };

                                                @endphp

                                                <span class="label {{ $stClass }}" style="font-size: 11px; text-transform: uppercase;">

                                                    {{ str_replace('_', ' ', $item->status) }}

                                                </span>

                                            </td>

                                            <td>

                                                @if ($item->evidence)

                                                    <small style="color: #444;">"{{ $item->evidence }}"</small>

                                                    @if ($item->evidence_verified)

                                                        <br><span class="label label-success" style="font-size: 9px;"><i class="fa fa-check"></i> Verified in CV</span>

                                                    @else

                                                        <br><span class="label label-default" style="font-size: 9px;"><i class="fa fa-question-circle"></i> Unverified excerpt</span>

                                                    @endif

                                                @else

                                                    <span class="text-muted" style="font-size: 11px;">No direct evidence found in CV</span>

                                                @endif

                                            </td>

                                            <td>

                                                <small class="text-muted">{{ $item->reason ?: '--' }}</small>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="6" class="text-center text-muted">No criteria details recorded.</td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>



                        {{-- Override Modal --}}

                        <div class="modal fade" id="overrideScoreModal-{{ $match->id }}" tabindex="-1" role="dialog" aria-hidden="true">

                            <div class="modal-dialog">

                                <div class="modal-content text-left">

                                    <form method="POST" action="{{ route('recruitment.candidates.cv-match.override', $match) }}">

                                        @csrf

                                        <div class="modal-header">

                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>

                                            <h4 class="modal-title"><i class="fa fa-pencil"></i> Override AI Matching Score</h4>

                                        </div>

                                        <div class="modal-body">

                                            <p class="text-muted" style="font-size: 12px;">

                                                HR recruiters and Hiring Managers may override the algorithmic match score when justified by domain context, candidate background, or manual verification.

                                            </p>

                                            <div class="form-group">

                                                <label for="override_score_{{ $match->id }}">New Score (0 - 100) *</label>

                                                <input type="number" min="0" max="100" name="override_score" id="override_score_{{ $match->id }}" class="form-control"

                                                    value="{{ old('override_score', $match->effective_score) }}" required>

                                            </div>

                                            <div class="form-group">

                                                <label for="override_note_{{ $match->id }}">Reason for Override * <small class="text-danger">(Mandatory justification)</small></label>

                                                <textarea name="note" id="override_note_{{ $match->id }}" class="form-control" rows="3" required

                                                    placeholder="Explain why you are adjusting the score (e.g. Cambridge certification confirmed via phone screen, equivalent experience verified)...">{{ old('note') }}</textarea>

                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>

                                            <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Save Override</button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>



                        {{-- Overrides History if any --}}

                        @if ($match->overrides->isNotEmpty())

                            <div class="well well-sm" style="background: #fdfdfd; margin-top: 10px;">

                                <h5 style="margin-top: 0; font-weight: 700;"><i class="fa fa-history"></i> Score Override Audit Trail</h5>

                                <ul style="margin-bottom: 0; padding-left: 20px;">

                                    @foreach ($match->overrides as $ovr)

                                        <li>

                                            <strong>Score: {{ $ovr->override_score }}/100</strong> by {{ $ovr->user?->name ?: 'HR User' }} on {{ $ovr->created_at->format('d M Y H:i') }}:

                                            <em>"{{ $ovr->note }}"</em>

                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif

                    @endif



                    {{-- Permanent Decision Support Advisory --}}

                    <div class="alert alert-info" style="margin-top: 15px; margin-bottom: 0; font-size: 12px; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">

                        <i class="fa fa-balance-scale"></i> <strong>Decision Support Advisory:</strong>

                        AI CV matching and criteria evaluations serve solely as screening assistance. In accordance with MHIS HR Governance, final decisions to advance, interview, or reject candidates remain exclusively with authorized human HR personnel and Hiring Managers.

                    </div>

                </div>

            </div>

        @endforeach



        {{-- Stage History Timeline Panel --}}

        @foreach ($candidate->applications as $application)

            <div class="x_panel">

                <div class="x_title">

                    <h2>

                        <i class="fa fa-history"></i> Stage History Timeline —

                        <small>

                            {{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Application #' . $application->id) }}

                            ({{ $application->branch?->name }})

                        </small>

                    </h2>

                    <div class="clearfix"></div>

                </div>

                <div class="x_content">

                    @if ($application->stageHistories->isNotEmpty())

                        <ul class="timeline-widget">

                            @foreach ($application->stageHistories as $history)

                                @php

                                    $dotClass = match ($history->to_stage) {

                                        'joined' => 'primary',

                                        'rejected', 'withdrawn' => 'danger',

                                        'offer' => 'warning',

                                        default => '',

                                    };

                                @endphp

                                <li>

                                    <span class="timeline-dot {{ $dotClass }}"></span>

                                    <div class="timeline-content">

                                        <div class="row">

                                            <div class="col-md-8 col-sm-7">

                                                <strong>

                                                    @if ($history->from_stage)

                                                        {{ $stages[$history->from_stage] ?? ucfirst($history->from_stage) }}

                                                        <i class="fa fa-arrow-right text-muted" style="margin: 0 4px;"></i>

                                                    @endif

                                                    <span class="text-primary">{{ $stages[$history->to_stage] ?? ucfirst($history->to_stage) }}</span>

                                                </strong>

                                                @if ($history->to_substatus)

                                                    <span class="label label-info" style="margin-left: 6px;">{{ ucfirst(str_replace('_', ' ', $history->to_substatus)) }}</span>

                                                @endif



                                                @if ($history->reason)

                                                    <p style="margin-top: 6px; margin-bottom: 2px; color: #444;">

                                                        <i class="fa fa-quote-left text-muted" style="font-size: 10px;"></i>

                                                        {{ $history->reason }}

                                                    </p>

                                                @endif

                                            </div>

                                            <div class="col-md-4 col-sm-5 text-right timeline-time">

                                                <i class="fa fa-clock-o"></i> {{ $history->changed_at ? $history->changed_at->format('d M Y H:i') : '--' }}<br>

                                                <small class="text-muted">By: {{ $history->changer?->name ?: 'System' }}</small>

                                            </div>

                                        </div>

                                    </div>

                                </li>

                            @endforeach

                        </ul>

                    @else

                        <p class="text-muted text-center" style="padding: 15px;">No stage history recorded yet.</p>

                    @endif

                </div>

            </div>

        @endforeach

    </div>

@endsection



@section('content-script')

    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>

    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <script>

        $(function() {

            $('#candidate-applications-table').DataTable({

                pageLength: 25,

                ordering: true,

                responsive: true,

                pagingType: 'simple',

                language: {

                    emptyTable: 'This candidate has no application history.'

                }

            });

        });

    </script>

@endsection


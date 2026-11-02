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
                                                            <select name="stage" id="stage_{{ $application->id }}" class="form-control stage-selector" data-target="{{ $application->id }}" required>
                                                                @foreach ($stages as $value => $label)
                                                                    <option value="{{ $value }}" @selected($application->stage === $value)>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group rejection-group-{{ $application->id }}" style="display: {{ $application->stage === 'rejected' ? 'block' : 'none' }};">
                                                            <label for="rejection_reason_id_{{ $application->id }}">Rejection Reason <span class="text-danger">*</span></label>
                                                            <select name="rejection_reason_id" id="rejection_reason_id_{{ $application->id }}" class="form-control">
                                                                <option value="">-- Select Master Rejection Reason --</option>
                                                                @foreach ($rejectionReasons as $rReason)
                                                                    <option value="{{ $rReason->id }}">{{ $rReason->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="alert alert-info gate-notice-{{ $application->id }}" style="display: none; font-size: 12px; margin-bottom: 15px;">
                                                            <i class="fa fa-info-circle"></i> <span class="gate-notice-text"></span>
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



        {{-- Phase 3: Psychotest Assessments Panel --}}
        @foreach ($candidate->applications as $application)
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        <i class="fa fa-file-text-o text-primary"></i> Psychotest Assessments —
                        <small>
                            {{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Application #' . $application->id) }}
                            ({{ $application->branch?->name }})
                        </small>
                    </h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li>
                            <button type="button" class="btn btn-success btn-xs" style="color: #fff; margin-top: 5px;" data-toggle="modal" data-target="#uploadPsychotestModal-{{ $application->id }}">
                                <i class="fa fa-upload"></i> Upload Psychotest Report
                            </button>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content table-responsive">
                    @if ($application->assessments->isNotEmpty())
                        <table class="table table-striped table-bordered table-sm" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Assessed Date</th>
                                    <th style="width: 160px;" class="text-center">Conclusion</th>
                                    <th>Notes / Summary</th>
                                    <th style="width: 220px;">Report Document</th>
                                    <th style="width: 160px;">Uploaded By</th>
                                    <th style="width: 80px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($application->assessments as $assessment)
                                    <tr>
                                        <td>{{ $assessment->assessed_at ? $assessment->assessed_at->format('d M Y') : '-' }}</td>
                                        <td class="text-center">
                                            <span class="label label-{{ $assessment->conclusion_badge_class }}" style="font-size: 11px;">
                                                {{ $assessment->conclusion_label }}
                                            </span>
                                        </td>
                                        <td>{{ $assessment->notes ?: '-' }}</td>
                                        <td>
                                            <a href="{{ route('recruitment.assessments.download', [$candidate->id, $application->id, $assessment->id]) }}" class="btn btn-primary btn-xs" target="_blank">
                                                <i class="fa fa-file-pdf-o"></i> {{ $assessment->original_filename }}
                                            </a>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $assessment->uploadedBy?->name ?: 'System' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('recruitment.assessments.destroy', [$candidate->id, $application->id, $assessment->id]) }}" style="display:inline;" onsubmit="return confirm('Delete this psychotest assessment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" title="Delete assessment">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center" style="padding: 15px;">No psychotest assessment uploaded yet for this application.</p>
                    @endif
                </div>
            </div>

            <!-- Modal Upload Psychotest -->
            <div class="modal fade" id="uploadPsychotestModal-{{ $application->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('recruitment.assessments.store', [$candidate->id, $application->id]) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                                <h4 class="modal-title">Upload Psychotest Assessment Report</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Assessment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="assessed_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Psychological Assessment Conclusion <span class="text-danger">*</span></label>
                                    <select name="conclusion" class="form-control" required>
                                        <option value="">-- Select Conclusion --</option>
                                        <option value="recommended">Recommended (Disarankan)</option>
                                        <option value="considered">Considered (Dipertimbangkan)</option>
                                        <option value="not_recommended">Not Recommended (Tidak Disarankan)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Assessment Notes / Highlights</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Summary of cognitive, personality, or behavioral observations from psychologist"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Psychotest Report PDF <span class="text-danger">*</span> <small class="text-muted">(Max 10MB, PDF only)</small></label>
                                    <input type="file" name="assessment_file" class="form-control" accept=".pdf" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Upload Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Phase 3: Interview Rounds & Panel Evaluations Panel --}}
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        <i class="fa fa-users text-primary"></i> Interview Rounds & Evaluations —
                        <small>
                            {{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Application #' . $application->id) }}
                            ({{ $application->branch?->name }})
                        </small>
                    </h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li>
                            <button type="button" class="btn btn-success btn-xs" style="color: #fff; margin-top: 5px;" data-toggle="modal" data-target="#scheduleInterviewModal-{{ $application->id }}">
                                <i class="fa fa-calendar-plus-o"></i> Schedule Interview Round
                            </button>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    @if ($application->interviewRounds->isNotEmpty())
                        @foreach ($application->interviewRounds as $round)
                            <div class="panel panel-default" style="margin-bottom: 20px; border-left: 4px solid #2A3F54;">
                                <div class="panel-heading" style="padding: 10px 15px;">
                                    <div class="row">
                                        <div class="col-md-8 col-sm-8">
                                            <h4 style="margin: 0; font-size: 15px;">
                                                <strong>Round {{ $round->round_no }}: {{ $round->round_type_label }}</strong>
                                                <span class="label label-{{ $round->status_badge_class }}" style="margin-left: 8px;">
                                                    {{ ucfirst($round->status) }}
                                                </span>
                                            </h4>
                                            <small class="text-muted" style="margin-top: 4px; display: inline-block;">
                                                <i class="fa fa-calendar"></i> {{ $round->scheduled_at ? $round->scheduled_at->format('d M Y H:i') : '-' }} WIB
                                                @if ($round->location_or_link)
                                                    &nbsp;|&nbsp; <i class="fa fa-map-marker"></i>
                                                    @if (str_starts_with($round->location_or_link, 'http'))
                                                        <a href="{{ $round->location_or_link }}" target="_blank" rel="noopener noreferrer">Join Meeting</a>
                                                    @else
                                                        {{ $round->location_or_link }}
                                                    @endif
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-md-4 col-sm-4 text-right">
                                            @if ($round->status === 'scheduled')
                                                <form method="POST" action="{{ route('recruitment.interviews.cancel-round', [$candidate->id, $application->id, $round->id]) }}" style="display:inline;" onsubmit="return confirm('Cancel this interview round?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-default btn-xs text-danger" title="Cancel this round">
                                                        <i class="fa fa-times"></i> Cancel Round
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body" style="padding: 12px 15px;">
                                    @if ($round->notes)
                                        <p style="margin-bottom: 12px; font-size: 12px; color: #555;">
                                            <strong>Round Notes:</strong> {{ $round->notes }}
                                        </p>
                                    @endif

                                    <table class="table table-bordered table-striped table-sm" style="margin-bottom: 0;">
                                        <thead>
                                            <tr class="bg-light">
                                                <th style="width: 220px;">Assigned Interviewer</th>
                                                <th style="width: 140px;" class="text-center">Evaluation Status</th>
                                                <th style="width: 180px;" class="text-center">Recommendation</th>
                                                <th style="width: 100px;" class="text-center">Avg Score</th>
                                                <th>Feedback Summary</th>
                                                <th style="width: 140px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($round->interviewers as $interviewer)
                                                @php
                                                    $eval = $round->evaluations->firstWhere('interviewer_employee_id', $interviewer->id);
                                                    $isSubmitted = $eval && $eval->status === 'submitted';
                                                    $pName = trim(($interviewer->personal?->first_name ?? '') . ' ' . ($interviewer->personal?->last_name ?? ''));
                                                    $empName = !empty($pName) ? $pName : ($interviewer->user?->name ?? "Employee #{$interviewer->id}");
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $empName }}</strong><br>
                                                        <small class="text-muted">{{ $interviewer->employment?->job_position?->name ?? ($interviewer->employment?->job_position_name ?? 'Staff') }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($isSubmitted)
                                                            <span class="label label-success"><i class="fa fa-check"></i> Submitted</span><br>
                                                            <small class="text-muted">{{ $eval->submitted_at ? $eval->submitted_at->format('d/m/Y H:i') : '' }}</small>
                                                        @else
                                                            <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending Draft</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($eval && $eval->overall_recommendation)
                                                            <span class="label label-{{ $eval->recommendation_badge_class }}">
                                                                {{ $eval->recommendation_label }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center font-weight-bold">
                                                        @if ($eval && $eval->averageScore())
                                                            <span class="badge">{{ $eval->averageScore() }} / 5</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($eval && ($eval->strengths || $eval->concerns))
                                                            @if ($eval->strengths)
                                                                <small class="text-success d-block"><strong>Strengths:</strong> {{ Str::limit($eval->strengths, 80) }}</small>
                                                            @endif
                                                            @if ($eval->concerns)
                                                                <small class="text-danger d-block"><strong>Concerns:</strong> {{ Str::limit($eval->concerns, 80) }}</small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No feedback recorded yet</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($eval)
                                                            @if ($eval->attachment_path)
                                                                <a href="{{ route('recruitment.interviews.evaluations.download-attachment', [$candidate->id, $application->id, $eval->id]) }}" class="btn btn-default btn-xs" target="_blank" title="Download physical form attachment">
                                                                    <i class="fa fa-paperclip"></i>
                                                                </a>
                                                            @else
                                                                <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#uploadAttachmentModal-{{ $eval->id }}" title="Upload physical form scan">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            @endif

                                                            @if ($isSubmitted)
                                                                <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#reopenEvalModal-{{ $eval->id }}" title="Reopen evaluation for changes">
                                                                    <i class="fa fa-undo"></i> Reopen
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if ($eval)
                                                    <!-- Modal Upload Attachment -->
                                                    <div class="modal fade" id="uploadAttachmentModal-{{ $eval->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('recruitment.interviews.evaluations.upload-attachment', [$candidate->id, $application->id, $eval->id]) }}" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                                                                        <h4 class="modal-title">Attach Physical Form Scan for {{ $empName }}</h4>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="form-group">
                                                                            <label>Select Document File <small class="text-muted">(PDF or Image, max 10MB)</small></label>
                                                                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Upload Attachment</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Modal Reopen Evaluation -->
                                                    <div class="modal fade" id="reopenEvalModal-{{ $eval->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('recruitment.interviews.evaluations.reopen', [$candidate->id, $application->id, $eval->id]) }}">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                                                                        <h4 class="modal-title">Reopen Interview Evaluation</h4>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p class="text-muted">
                                                                            Reopening will return this evaluation to <strong>Draft</strong> status, allowing the interviewer to edit their scores and recommendation.
                                                                        </p>
                                                                        <div class="form-group">
                                                                            <label>Mandatory Reason for Reopening <span class="text-danger">*</span></label>
                                                                            <textarea name="reason" class="form-control" rows="3" placeholder="e.g. Additional panel discussion required or typo correction requested" required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-warning">Reopen Evaluation</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No interviewers assigned to this round.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center" style="padding: 15px;">No interview rounds scheduled yet for this application.</p>
                    @endif
                </div>
            </div>

            <!-- Modal Schedule Interview Round -->
            <div class="modal fade" id="scheduleInterviewModal-{{ $application->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('recruitment.interviews.store-round', [$candidate->id, $application->id]) }}">
                            @csrf
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                                <h4 class="modal-title">Schedule Interview Round</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Round Type <span class="text-danger">*</span></label>
                                            <select name="round_type" class="form-control" required>
                                                <option value="hr">HR Interview</option>
                                                <option value="user">User / Hiring Manager</option>
                                                <option value="panel">Panel Interview</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Schedule Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="scheduled_at" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Select Interviewers <span class="text-danger">*</span> <small class="text-muted">(Employees with system login account)</small></label>
                                    <select name="interviewers[]" class="form-control select2-interviewer" multiple="multiple" style="width: 100%;" required data-placeholder="Search and select interviewers...">
                                        @foreach ($assignableInterviewers as $emp)
                                            @php
                                                $pName = trim(($emp->personal?->first_name ?? '') . ' ' . ($emp->personal?->last_name ?? ''));
                                                $eName = !empty($pName) ? $pName : ($emp->user?->name ?? "Employee #{$emp->id}");
                                            @endphp
                                            <option value="{{ $emp->id }}">{{ $eName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Meeting Location or Online Link</label>
                                    <input type="text" name="location_or_link" class="form-control" placeholder="e.g. Meeting Room 2, Bintaro Campus or https://meet.google.com/xyz-abcd-efg" maxlength="500">
                                </div>

                                <div class="form-group">
                                    <label>Notes / Briefing for Interviewers</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Focus areas, duration, or special instructions"></textarea>
                                </div>

                                {{-- AI Suggested Interview Questions (from Phase 2 matching result) --}}
                                @if ($application->latestCvMatchResult && !empty($application->latestCvMatchResult->interview_questions))
                                    <div class="panel panel-info" style="margin-top: 15px; margin-bottom: 0;">
                                        <div class="panel-heading" style="padding: 6px 12px; font-size: 13px;">
                                            <i class="fa fa-lightbulb-o"></i> <strong>AI Suggested Interview Questions (Screening Insights):</strong>
                                        </div>
                                        <div class="panel-body" style="padding: 10px 15px; background-color: #f7fbfe;">
                                            <ol style="margin-bottom: 0; padding-left: 20px;">
                                                @foreach ($application->latestCvMatchResult->interview_questions as $question)
                                                    <li style="margin-bottom: 4px; font-size: 12px; color: #23527c;">{{ $question }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Schedule Interview</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Phase 4: Job Offers & Approval Workflow Panel --}}
        @include('recruitment.partials.offer-panel')

        {{-- Phase 4: Contract & Onboarding Panel --}}
        @include('recruitment.partials.onboarding-panel')

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

            // Prevent Bootstrap 3 focus fighting with Select2 in modals
            if ($.fn.modal && $.fn.modal.Constructor) {
                $.fn.modal.Constructor.prototype.enforceFocus = function() {};
            }

            // Prevent clicks inside modal-dialog from bubbling up to backdrop
            $(document).on('click', '.modal-dialog', function(e) {
                e.stopPropagation();
            });

            $('.modal').on('shown.bs.modal', function() {
                var $modal = $(this);
                var $parent = $modal.find('.modal-content').length ? $modal.find('.modal-content') : $modal;
                $modal.find('.select2-interviewer, .select2-approver').each(function() {
                    var $sel = $(this);
                    if (!$sel.hasClass('select2-hidden-accessible')) {
                        $sel.select2({
                            width: '100%',
                            dropdownParent: $parent,
                            placeholder: $sel.data('placeholder') || 'Search and select...'
                        });
                    }
                });
            });

            // Stage selection change event
            $(document).on('change', '.stage-selector', function() {
                var appId = $(this).data('target');
                var val = $(this).val();
                var $rejectionGroup = $('.rejection-group-' + appId);
                var $gateNotice = $('.gate-notice-' + appId);
                var $noticeText = $gateNotice.find('.gate-notice-text');

                if (val === 'rejected') {
                    $rejectionGroup.slideDown(200);
                } else {
                    $rejectionGroup.slideUp(200);
                }

                if (val === 'contract_signing') {
                    $noticeText.text('Gate Notice: Moving to Contract Signing requires an accepted offer. If missing, a mandatory reason is required to override.');
                    $gateNotice.slideDown(200);
                } else if (val === 'joined') {
                    $noticeText.text('Gate Notice: Moving to Joined requires a signed contract and join date. If missing, a mandatory reason is required to override.');
                    $gateNotice.slideDown(200);
                } else {
                    $gateNotice.slideUp(200);
                }
            });

            // Dynamic approver row addition
            $(document).on('click', '.btn-add-approver', function() {
                var appId = $(this).data('target');
                var $container = $('#approver-rows-' + appId);
                var count = $container.find('.approver-row').length;
                var nextLevel = count + 1;

                var $firstRow = $container.find('.approver-row').first();
                if ($firstRow.length) {
                    var $clone = $firstRow.clone();
                    $clone.find('input[type="number"]').attr('name', 'approvers[' + count + '][level]').val(nextLevel);
                    $clone.find('select').attr('name', 'approvers[' + count + '][employee_id]').val('');
                    // Remove any select2 containers that were cloned
                    $clone.find('.select2-container').remove();
                    $clone.find('select').removeClass('select2-hidden-accessible');
                    $container.append($clone);

                    // Reinitialize select2
                    var $modal = $container.closest('.modal');
                    var $parent = $modal.find('.modal-content').length ? $modal.find('.modal-content') : $modal;
                    $clone.find('.select2-approver').select2({
                        width: '100%',
                        dropdownParent: $parent,
                        placeholder: 'Choose Approver (Employee)'
                    });
                }
            });

            // Remove approver row
            $(document).on('click', '.btn-remove-approver', function() {
                var $row = $(this).closest('.approver-row');
                var $container = $row.closest('[id^="approver-rows-"]');
                if ($container.find('.approver-row').length > 1) {
                    $row.remove();
                } else {
                    $row.find('select').val('').trigger('change');
                }
            });
        });
    </script>

@endsection


@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div style="margin-bottom: 15px;">
            <a href="{{ route('recruitment.my-interviews.index') }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back to My Interviews
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="status">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please correct the following errors:</strong>
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $isSubmitted = $myEvaluation && $myEvaluation->status === 'submitted';
        @endphp

        <!-- Candidate & Interview Info Header -->
        <div class="x_panel">
            <div class="x_title">
                <h2>Interview Evaluation: {{ $candidate->full_name }} <small>Round {{ $round->round_no }} ({{ $round->round_type_label }})</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <table class="table table-sm table-borderless" style="margin-bottom: 0;">
                            <tr>
                                <th style="width: 140px;" class="text-muted">Candidate Name:</th>
                                <td><strong>{{ $candidate->full_name }}</strong> ({{ $candidate->candidate_code }})</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Position Applied:</th>
                                <td>{{ $application->jobOpening->title ?? $application->position_applied ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Branch:</th>
                                <td>{{ $application->branch->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <table class="table table-sm table-borderless" style="margin-bottom: 0;">
                            <tr>
                                <th style="width: 140px;" class="text-muted">Interview Schedule:</th>
                                <td><strong>{{ $round->scheduled_at ? $round->scheduled_at->format('l, d F Y - H:i') : '-' }} WIB</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Meeting Location/Link:</th>
                                <td>
                                    @if ($round->location_or_link)
                                        @if (str_starts_with($round->location_or_link, 'http'))
                                            <a href="{{ $round->location_or_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-xs">
                                                <i class="fa fa-video-camera"></i> Join Online Meeting
                                            </a>
                                        @else
                                            <span>{{ $round->location_or_link }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Candidate Resume:</th>
                                <td>
                                    @if ($application->resume_path)
                                        <a href="{{ route('recruitment.my-interviews.cv', $round->id) }}" class="btn btn-primary btn-xs" target="_blank">
                                            <i class="fa fa-file-pdf-o"></i> View / Download CV
                                        </a>
                                    @else
                                        <span class="text-muted">No CV attached</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if ($isSubmitted)
            <!-- Read-only View for Submitted Evaluation -->
            <div class="x_panel">
                <div class="x_title">
                    <h2>
                        <i class="fa fa-check-circle text-success"></i> Your Submitted Recommendation
                        <small>Submitted on {{ $myEvaluation->submitted_at ? $myEvaluation->submitted_at->format('d M Y H:i') : '-' }}</small>
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="alert alert-info" style="margin-bottom: 20px;">
                        <strong>Overall Recommendation:</strong>
                        <span class="label label-{{ $myEvaluation->recommendation_badge_class }}" style="font-size: 13px; margin-left: 8px;">
                            {{ $myEvaluation->recommendation_label }}
                        </span>
                        @if ($myEvaluation->averageScore())
                            <span style="margin-left: 15px;">Average Competency Score: <strong>{{ $myEvaluation->averageScore() }} / 5.0</strong></span>
                        @endif
                    </div>

                    <h4>Competency Ratings</h4>
                    <table class="table table-bordered table-striped table-sm" style="margin-bottom: 20px;">
                        <thead>
                            <tr class="bg-light">
                                <th style="width: 250px;">Competency</th>
                                <th style="width: 100px;" class="text-center">Score (1-5)</th>
                                <th>Interviewer Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeCompetencies as $comp)
                                <tr>
                                    <td>
                                        <strong>{{ $comp->name }}</strong><br>
                                        <small class="text-muted">{{ $comp->description }}</small>
                                    </td>
                                    <td class="text-center font-weight-bold" style="font-size: 14px;">
                                        {{ $existingScores[$comp->id] ?? '-' }}
                                    </td>
                                    <td>{{ $existingComments[$comp->id] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Key Strengths</strong></div>
                                <div class="panel-body">{!! nl2br(e($myEvaluation->strengths ?? 'None provided')) !!}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Areas of Concern / Development</strong></div>
                                <div class="panel-body">{!! nl2br(e($myEvaluation->concerns ?? 'None provided')) !!}</div>
                            </div>
                        </div>
                    </div>

                    @if ($myEvaluation->notes)
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong>Additional Observations</strong></div>
                            <div class="panel-body">{!! nl2br(e($myEvaluation->notes)) !!}</div>
                        </div>
                    @endif

                    @if ($myEvaluation->attachment_path)
                        <p>
                            <strong>Uploaded Form Attachment:</strong>
                            <a href="{{ route('recruitment.interviews.evaluations.download-attachment', ['candidate' => $candidate->id, 'application' => $application->id, 'evaluation' => $myEvaluation->id]) }}" class="btn btn-default btn-xs" target="_blank">
                                <i class="fa fa-paperclip"></i> {{ $myEvaluation->original_attachment_name ?? 'Download Attachment' }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Peer Evaluation Summary (Blind Review rule: visible only AFTER submit) -->
            @if ($peerSummary && !empty($peerSummary['peer_evaluations']) && count($peerSummary['peer_evaluations']) > 0)
                <div class="x_panel">
                    <div class="x_title">
                        <h2><i class="fa fa-users text-primary"></i> Other Interviewers on this Round <small>Summary of evaluations from fellow panel members</small></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        @foreach ($peerSummary['peer_evaluations'] as $peerEval)
                            @php
                                $peerName = $peerEval->interviewerEmployee->personal ? ($peerEval->interviewerEmployee->personal->first_name . ' ' . $peerEval->interviewerEmployee->personal->last_name) : 'Panel Member';
                            @endphp
                            <div class="panel panel-default" style="margin-bottom: 16px;">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <strong>{{ $peerName }}</strong>
                                            <span class="text-muted" style="margin-left: 10px;">Submitted on {{ $peerEval->submitted_at ? $peerEval->submitted_at->format('d M Y H:i') : '-' }}</span>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="label label-{{ $peerEval->recommendation_badge_class }}">
                                                {{ $peerEval->recommendation_label }}
                                            </span>
                                            @if ($peerEval->averageScore())
                                                <span class="badge" style="margin-left: 6px;">Avg: {{ $peerEval->averageScore() }} / 5</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted"><strong>Strengths:</strong></small>
                                            <p style="margin-bottom: 8px;">{{ $peerEval->strengths ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted"><strong>Concerns:</strong></small>
                                            <p style="margin-bottom: 8px;">{{ $peerEval->concerns ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <!-- Evaluation Form (Draft / Input Mode) -->
            <form id="evaluation-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Interview Recommendation Form <small>Rate competencies (1-5) and submit final hiring recommendation</small></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Rating Scale:</strong> 1 = Poor / Unsatisfactory, 2 = Below Expectations, 3 = Meets Expectations, 4 = Exceeds Expectations, 5 = Outstanding / Role Model.
                        </div>

                        <h4>1. Competency Assessment <span class="text-danger">*</span></h4>
                        <div class="table-responsive" style="margin-bottom: 24px;">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr class="bg-light">
                                        <th style="width: 300px;">Competency & Description</th>
                                        <th style="width: 220px;" class="text-center">Rating (1 to 5) <span class="text-danger">*</span></th>
                                        <th>Specific Evidence / Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeCompetencies as $comp)
                                        @php
                                            $currScore = old("scores.{$comp->id}", $existingScores[$comp->id] ?? '');
                                            $currComment = old("comments.{$comp->id}", $existingComments[$comp->id] ?? '');
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $comp->name }}</strong>
                                                <small class="text-muted d-block" style="margin-top: 4px;">{{ $comp->description }}</small>
                                            </td>
                                            <td class="text-center" style="vertical-align: middle;">
                                                <div style="display: flex; justify-content: center; gap: 10px;">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <label style="font-weight: normal; margin-bottom: 0; cursor: pointer;">
                                                            <input type="radio" name="scores[{{ $comp->id }}]" value="{{ $i }}" {{ (string)$currScore === (string)$i ? 'checked' : '' }}>
                                                            {{ $i }}
                                                        </label>
                                                    @endfor
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="comments[{{ $comp->id }}]" class="form-control input-sm" placeholder="Observation or example..." value="{{ $currComment }}" maxlength="500">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h4>2. Qualitative Feedback</h4>
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <label>Key Strengths</label>
                                    <textarea name="strengths" class="form-control" rows="4" placeholder="What are the candidate's strongest capabilities, accomplishments, or domain strengths?">{{ old('strengths', $myEvaluation->strengths ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <label>Areas of Concern / Red Flags</label>
                                    <textarea name="concerns" class="form-control" rows="4" placeholder="Any hesitations, culture mismatches, knowledge gaps, or risks observed?">{{ old('concerns', $myEvaluation->concerns ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>General Notes / Observations</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional context or interview dialogue notes">{{ old('notes', $myEvaluation->notes ?? '') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <label>Overall Recommendation <span class="text-danger">*</span></label>
                                    @php
                                        $selectedRec = old('overall_recommendation', $myEvaluation->overall_recommendation ?? '');
                                    @endphp
                                    <select name="overall_recommendation" class="form-control" required>
                                        <option value="">-- Select Final Recommendation --</option>
                                        <option value="strongly_recommend" {{ $selectedRec === 'strongly_recommend' ? 'selected' : '' }}>Strongly Recommend (Exceptional candidate)</option>
                                        <option value="recommend" {{ $selectedRec === 'recommend' ? 'selected' : '' }}>Recommend (Good fit, meets requirements)</option>
                                        <option value="hold" {{ $selectedRec === 'hold' ? 'selected' : '' }}>Hold (Marginal / Backup candidate)</option>
                                        <option value="not_recommend" {{ $selectedRec === 'not_recommend' ? 'selected' : '' }}>Not Recommend (Does not fit requirement)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <label>Attach Physical Form / Notes Scan <small class="text-muted">(Optional, PDF/Image up to 10MB)</small></label>
                                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    @if ($myEvaluation && $myEvaluation->attachment_path)
                                        <small class="text-success d-block" style="margin-top: 4px;">
                                            <i class="fa fa-check"></i> Current file: {{ $myEvaluation->original_attachment_name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="ln_solid" style="margin: 24px 0 16px 0;"></div>

                        <div class="form-group text-right">
                            <button type="submit" formaction="{{ route('recruitment.my-interviews.draft', $round->id) }}" class="btn btn-default">
                                <i class="fa fa-save"></i> Save as Draft
                            </button>
                            <button type="submit" formaction="{{ route('recruitment.my-interviews.submit', $round->id) }}" class="btn btn-success" onclick="return confirm('Are you sure you want to submit your final interview evaluation? Once submitted, it cannot be modified without HR Administrator approval.');">
                                <i class="fa fa-send"></i> Submit Final Recommendation
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection


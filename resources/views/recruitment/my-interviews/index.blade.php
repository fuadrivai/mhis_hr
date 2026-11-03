@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="status">{{ session('error') }}</div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>My Assigned Interviews <small>Candidate interview rounds assigned to you as an interviewer</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content table-responsive">
                <table class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Position / Opening</th>
                            <th>Psychotest</th>
                            <th>Round</th>
                            <th>Schedule</th>
                            <th>Location / Link</th>
                            <th class="text-center">My Status</th>
                            <th class="text-center" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rounds as $round)
                            @php
                                $candidate = $round->candidateApplication->candidate ?? null;
                                $myEval = $round->evaluations->first();
                                $isSubmitted = $myEval && $myEval->status === 'submitted';
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $candidate->full_name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $candidate->candidate_code ?? '' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $round->candidateApplication->jobOpening->title ?? $round->candidateApplication->position_applied ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $round->candidateApplication->branch->name ?? '' }}</small>
                                </td>
                                <td>
                                    @php
                                        $assessItem = $round->candidateApplication->assessments?->first();
                                    @endphp
                                    @if ($assessItem)
                                        <span class="label label-{{ $assessItem->conclusion_badge_class }}" style="font-size: 11px;">
                                            {{ $assessItem->conclusion_label }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">--</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="label label-info">{{ $round->round_type_label }}</span>
                                    <small class="text-muted d-block">Round {{ $round->round_no }}</small>
                                </td>
                                <td>
                                    <strong>{{ $round->scheduled_at ? $round->scheduled_at->format('d M Y') : '-' }}</strong><br>
                                    <small class="text-muted">{{ $round->scheduled_at ? $round->scheduled_at->format('H:i') : '' }} WIB</small>
                                </td>
                                <td>
                                    @if ($round->location_or_link)
                                        @if (str_starts_with($round->location_or_link, 'http'))
                                            <a href="{{ $round->location_or_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-xs">
                                                <i class="fa fa-video-camera"></i> Join Meeting
                                            </a>
                                        @else
                                            <span>{{ $round->location_or_link }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($isSubmitted)
                                        <span class="label label-success" style="font-size: 11px;">
                                            <i class="fa fa-check-circle"></i> Submitted
                                        </span><br>
                                        <small class="text-muted">{{ $myEval->submitted_at ? $myEval->submitted_at->format('d/m/Y H:i') : '' }}</small>
                                    @else
                                        <span class="label label-warning" style="font-size: 11px;">
                                            <i class="fa fa-pencil"></i> Pending Evaluation
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('recruitment.my-interviews.show', $round->id) }}" class="btn {{ $isSubmitted ? 'btn-default' : 'btn-success' }} btn-sm">
                                        <i class="fa {{ $isSubmitted ? 'fa-eye' : 'fa-edit' }}"></i> {{ $isSubmitted ? 'View Form' : 'Evaluate' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 24px;">
                                    <i class="fa fa-calendar-o fa-2x text-muted" style="margin-bottom: 8px; display: block;"></i>
                                    You currently have no interview rounds assigned to you.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection


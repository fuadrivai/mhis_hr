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
                        <div class="col-md-8 col-sm-12 form-group text-right" style="padding-top: 25px;">
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
                                <th>Candidate Code</th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('candidate') }}">Candidate Name</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('position') }}">Opening / Position</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('branch') }}">Branch</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('stage') }}">Current Stage</a></th>
                                <th>AI Score</th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('updated_at') }}">Updated At</a></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($candidates as $candidate)
                                <?php $application = $candidate->applications->first(); ?>
                                <tr>
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
                                    <td colspan="8" class="text-center text-muted" style="padding: 30px;">
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
@endsection

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
        $stageLabels = $filterData['stages'];
        $statusLabels = $filterData['statuses'];
    @endphp

    <div class="col-md-12 col-sm-12 col-xs-12">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
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
                                @foreach ($filterData['branches'] as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label for="position">Position</label>
                            <select id="position" name="position" class="form-control">
                                <option value="">All positions</option>
                                @foreach ($filterData['positions'] as $position)
                                    <option value="{{ $position }}" @selected(($filters['position'] ?? '') === $position)>
                                        {{ $position }}
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
                            <label for="source">Candidate Source</label>
                            <select id="source" name="source" class="form-control">
                                <option value="">All sources</option>
                                @foreach ($filterData['sources'] as $source)
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
                                @foreach ($filterData['hrPics'] as $pic)
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
                    </div>
                    <div class="row">
                        <div class="col-md-2 col-sm-4 form-group">
                            <label for="per_page">Rows per page</label>
                            <select id="per_page" name="per_page" class="form-control">
                                @foreach ([25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((string) ($filters['per_page'] ?? '25') === (string) $size)>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-10 col-sm-8 form-group text-right" style="padding-top: 25px;">
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
                                <th><a class="candidate-sort" href="{{ $sortUrl('position') }}">Position Applied</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('branch') }}">Branch</a></th>
                                <th>Source</th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('stage') }}">Current Stage</a></th>
                                <th>HR PIC</th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('last_activity_at') }}">Last Activity</a></th>
                                <th><a class="candidate-sort" href="{{ $sortUrl('updated_at') }}">Updated At</a></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($candidates as $candidate)
                                @php($application = $candidate->applications->first())
                                <tr>
                                    <td>{{ $candidate->candidate_code ?: '--' }}</td>
                                    <td>
                                        <a href="{{ route('recruitment.candidates.show', $candidate) }}">
                                            {{ $candidate->full_name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            {{ $candidate->email }} · {{ $candidate->phone }}
                                        </small>
                                        <br>
                                        @if ($candidate->applications_count > 1)
                                            <span class="label label-info">Returning applicant</span>
                                        @else
                                            <span class="label label-default">First application</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $application?->position_applied ?: $application?->position?->name ?: '--' }}
                                    </td>
                                    <td>{{ $application?->branch?->name ?: '--' }}</td>
                                    <td>{{ $application?->source ?: '--' }}</td>
                                    <td>
                                        @php($stage = $application?->stage ?? 'received')
                                        <span class="label {{ in_array($stage, ['rejected', 'withdrawn']) ? 'label-danger' : ($stage === 'hired' ? 'label-success' : 'label-primary') }}">
                                            {{ $stageLabels[$stage] ?? ucfirst(str_replace('_', ' ', $stage)) }}
                                        </span>
                                    </td>
                                    <td>{{ $application?->hrPic?->name ?: '--' }}</td>
                                    <td>{{ $application?->last_activity_at?->format('d M Y H:i') ?: '--' }}</td>
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
                                        <a href="{{ route('recruitment.candidates.applications.create', $candidate) }}"
                                            class="btn btn-success btn-xs" title="Add application">
                                            <i class="fa fa-plus"></i> Add Application
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No candidates match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <p class="text-muted">
                            Showing {{ $candidates->firstItem() ?? 0 }}–{{ $candidates->lastItem() ?? 0 }}
                            of {{ $candidates->total() }} candidates
                        </p>
                    </div>
                    <div class="col-sm-6 text-right">
                        {{ $candidates->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

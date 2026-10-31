@extends('layouts.main-layout')

@section('content-class')
    <style>
        .opening-filter .form-group { margin-bottom: 12px; }
        .opening-actions { min-width: 170px; white-space: nowrap; }
        .opening-actions .btn { margin: 2px 1px; }
        .label-open { background-color: #26B99A; }
        .label-filled { background-color: #34495E; }
        .label-closed { background-color: #d9534f; }
        .label-draft { background-color: #777; }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <ul style="margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>Job Openings <small>Manage headcount and recruitment vacancies</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.job-openings.create') }}" class="btn btn-success btn-sm text-white">
                            <i class="fa fa-plus"></i> Create Job Opening
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="GET" action="{{ route('recruitment.job-openings.index') }}" class="opening-filter">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 form-group">
                            <label for="search">Search Title / Division</label>
                            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                                placeholder="Search opening title or division...">
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="branch_id">Branch</label>
                            <select id="branch_id" name="branch_id" class="form-control">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="open" @selected(($filters['status'] ?? '') === 'open')>Open</option>
                                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                                <option value="filled" @selected(($filters['status'] ?? '') === 'filled')>Filled</option>
                                <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group" style="padding-top: 25px;">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                            <a href="{{ route('recruitment.job-openings.index') }}" class="btn btn-default">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Opening Title</th>
                                <th>Branch</th>
                                <th>Headcount</th>
                                <th>Filled / Total</th>
                                <th>Active Candidates</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($openings as $opening)
                                <tr>
                                    <td>
                                        <a href="{{ route('recruitment.job-openings.show', $opening) }}" style="font-weight: 600;">
                                            {{ $opening->title }}
                                        </a>
                                        @if ($opening->division)
                                            <br><small class="text-muted"><i class="fa fa-building-o"></i> {{ $opening->division }}</small>
                                        @endif
                                        @if ($opening->jobRequirement)
                                            <br><small class="text-info"><i class="fa fa-file-text-o"></i> Template: {{ $opening->jobRequirement->title }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $opening->branch?->name ?: '--' }}</td>
                                    <td><span class="badge">{{ $opening->headcount }}</span></td>
                                    <td>
                                        <strong>{{ $opening->filled_count }}</strong> / {{ $opening->headcount }}
                                        @if ($opening->filled_count >= $opening->headcount)
                                            <span class="text-success"><i class="fa fa-check-circle"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('recruitment.candidates', ['job_opening_id' => $opening->id]) }}" class="badge bg-blue" title="View active candidates">
                                            {{ $opening->active_count }} Active
                                        </a>
                                        <small class="text-muted">({{ $opening->total_applications_count }} Total)</small>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match ($opening->status) {
                                                'open' => 'label-success',
                                                'filled' => 'label-primary',
                                                'closed' => 'label-danger',
                                                default => 'label-default',
                                            };
                                        @endphp
                                        <span class="label {{ $badgeClass }}">{{ strtoupper($opening->status) }}</span>
                                        @if ($opening->status === 'closed' && $opening->closed_reason)
                                            <br><small class="text-muted" title="{{ $opening->closed_reason }}">
                                                <i class="fa fa-info-circle"></i> {{ \Illuminate\Support\Str::limit($opening->closed_reason, 25) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Open:</strong> {{ $opening->opened_at ? $opening->opened_at->format('d M Y') : '--' }}<br>
                                            <strong>Close:</strong> {{ $opening->closes_at ? $opening->closes_at->format('d M Y') : 'Open until filled' }}
                                        </small>
                                    </td>
                                    <td class="text-center opening-actions">
                                        <a href="{{ route('recruitment.job-openings.show', $opening) }}" class="btn btn-primary btn-xs" title="View details">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('recruitment.job-openings.edit', $opening) }}" class="btn btn-default btn-xs" title="Edit">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>

                                        @if ($opening->status === 'open')
                                            <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#closeModal-{{ $opening->id }}" title="Close Opening">
                                                <i class="fa fa-times"></i> Close
                                            </button>
                                        @elseif (in_array($opening->status, ['closed', 'filled']))
                                            <form action="{{ route('recruitment.job-openings.reopen', $opening) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reopen this job opening?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs" title="Reopen Opening">
                                                    <i class="fa fa-folder-open-o"></i> Reopen
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Close Modal --}}
                                        <div class="modal fade" id="closeModal-{{ $opening->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-sm">
                                                <div class="modal-content text-left">
                                                    <form action="{{ route('recruitment.job-openings.close', $opening) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                            <h4 class="modal-title">Close Job Opening</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>{{ $opening->title }}</strong></p>
                                                            <div class="form-group">
                                                                <label for="closed_reason_{{ $opening->id }}">Reason for Closing <span class="text-danger">*</span></label>
                                                                <textarea name="closed_reason" id="closed_reason_{{ $opening->id }}" class="form-control" rows="3" required placeholder="e.g. Budget on hold, position cancelled, hired externally..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted" style="padding: 30px;">
                                        No job openings found matching the criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right">
                    {{ $openings->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection


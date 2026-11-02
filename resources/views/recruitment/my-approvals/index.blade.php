@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>My Offer Approvals <small>Job offers awaiting your review and approval</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ url()->previous() }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        <ul style="margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="my-approvals-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Position Offered</th>
                                <th>Branch</th>
                                <th>Level</th>
                                <th>Basic Salary</th>
                                <th>Document Link</th>
                                <th>Submitted By</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingSteps as $step)
                                @php
                                    $offer = $step->offer;
                                    $app = $offer->candidateApplication;
                                    $candidate = $app->candidate;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $candidate->full_name }}</strong><br>
                                        <small class="text-muted">{{ $candidate->candidate_code ?: 'Candidate' }} &bull; {{ $candidate->email }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $offer->position_title }}</strong>
                                        @if ($offer->grade_level)
                                            <span class="badge badge-info">{{ $offer->grade_level }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $app->branch?->name ?: '-' }}</td>
                                    <td>
                                        <span class="badge badge-primary">Level {{ $step->level }}</span>
                                    </td>
                                    <td>
                                        @if ($offer->canViewCompensation($user))
                                            <strong>{{ $offer->getFormattedBasicSalaryFor($user) }}</strong>
                                        @else
                                            <span class="text-muted"><i class="fa fa-lock"></i> Restricted</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($offer->offer_letter_url)
                                            <a href="{{ $offer->offer_letter_url }}" target="_blank" class="btn btn-xs btn-default" title="View Document">
                                                <i class="fa fa-external-link"></i> View File
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $offer->creator?->name ?: 'HR' }}<br>
                                        <small class="text-muted">{{ $offer->created_at ? $offer->created_at->format('d M Y H:i') : '-' }}</small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#viewOfferModal-{{ $offer->id }}">
                                            <i class="fa fa-eye"></i> Details
                                        </button>
                                        <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#approveModal-{{ $offer->id }}">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#rejectModal-{{ $offer->id }}">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>

                                {{-- Details Modal --}}
                                <div class="modal fade" id="viewOfferModal-{{ $offer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                                <h4 class="modal-title">Offer Details: {{ $candidate->full_name }} (Version {{ $offer->version }})</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-bordered table-sm">
                                                            <tr><th style="width: 40%;">Position Title</th><td><strong>{{ $offer->position_title }}</strong></td></tr>
                                                            <tr><th>Grade / Level</th><td>{{ $offer->grade_level ?: '-' }}</td></tr>
                                                            <tr><th>Branch</th><td>{{ $app->branch?->name ?: '-' }}</td></tr>
                                                            <tr><th>Proposed Start Date</th><td>{{ $offer->proposed_start_date ? $offer->proposed_start_date->format('d M Y') : 'TBD' }}</td></tr>
                                                            <tr><th>Valid Until</th><td>{{ $offer->valid_until ? $offer->valid_until->format('d M Y') : '-' }}</td></tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <table class="table table-bordered table-sm">
                                                            <tr>
                                                                <th style="width: 40%;">Basic Salary</th>
                                                                <td>
                                                                    @if ($offer->canViewCompensation($user))
                                                                        <strong class="text-success">{{ $offer->getFormattedBasicSalaryFor($user) }}</strong>
                                                                    @else
                                                                        <span class="text-muted"><i class="fa fa-lock"></i> Restricted</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Allowances</th>
                                                                <td>
                                                                    @if ($offer->canViewCompensation($user))
                                                                        @php $allowances = $offer->getFormattedAllowancesFor($user); @endphp
                                                                        @if (is_array($allowances) && count($allowances) > 0)
                                                                            <ul style="padding-left: 20px; margin-bottom: 0;">
                                                                                @foreach ($allowances as $item)
                                                                                    <li>{{ $item['name'] ?? 'Allowance' }}: Rp {{ number_format((float) ($item['amount'] ?? 0), 0, ',', '.') }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @else
                                                                            <em>None specified</em>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted"><i class="fa fa-lock"></i> Restricted</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Document Link</th>
                                                                <td>
                                                                    @if ($offer->offer_letter_url)
                                                                        <a href="{{ $offer->offer_letter_url }}" target="_blank" class="btn btn-xs btn-primary">
                                                                            <i class="fa fa-external-link"></i> Open Document in New Tab
                                                                        </a>
                                                                    @else
                                                                        <span class="text-muted">None attached</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                @if ($offer->other_benefits)
                                                    <div class="well well-sm">
                                                        <strong>Other Benefits:</strong>
                                                        <p style="margin-bottom: 0;">{!! nl2br(e($offer->other_benefits)) !!}</p>
                                                    </div>
                                                @endif

                                                @if ($offer->notes)
                                                    <div class="well well-sm">
                                                        <strong>Internal Notes:</strong>
                                                        <p style="margin-bottom: 0;">{!! nl2br(e($offer->notes)) !!}</p>
                                                    </div>
                                                @endif

                                                <h5>Approval Steps Progress:</h5>
                                                <table class="table table-bordered table-striped table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Level</th>
                                                            <th>Approver</th>
                                                            <th>Status</th>
                                                            <th>Acted At</th>
                                                            <th>Comments</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($offer->approvalSteps as $s)
                                                            <tr class="{{ $s->id === $step->id ? 'warning' : '' }}">
                                                                <td>Level {{ $s->level }}</td>
                                                                <td>{{ $s->approver?->personal?->first_name }} {{ $s->approver?->personal?->last_name }}</td>
                                                                <td>{!! $s->status_badge !!}</td>
                                                                <td>{{ $s->acted_at ? $s->acted_at->format('d M Y H:i') : '-' }}</td>
                                                                <td>{{ $s->comment ?: '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="{{ route('recruitment.offers.print', [$candidate, $app, $offer]) }}" target="_blank" class="btn btn-default pull-left">
                                                    <i class="fa fa-print"></i> Printable Summary
                                                </a>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Approve Modal --}}
                                <div class="modal fade" id="approveModal-{{ $offer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('recruitment.my-approvals.approve', $offer) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                                    <h4 class="modal-title">Approve Offer for {{ $candidate->full_name }}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to approve this job offer for <strong>{{ $offer->position_title }}</strong> (Level {{ $step->level }})?</p>
                                                    <div class="form-group">
                                                        <label class="control-label">Comments (Optional):</label>
                                                        <textarea name="comment" class="form-control" rows="3" placeholder="Add approval remarks or stipulations..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fa fa-check"></i> Confirm Approval
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="rejectModal-{{ $offer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('recruitment.my-approvals.reject', $offer) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                                    <h4 class="modal-title text-danger"><i class="fa fa-exclamation-triangle"></i> Reject Offer</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Rejecting this offer will return it to <strong>Draft</strong> status for HR revision. A new version will be generated upon resubmission.</p>
                                                    <div class="form-group">
                                                        <label class="control-label">Rejection Reason / Notes <span class="text-danger">*</span></label>
                                                        <textarea name="comment" class="form-control" rows="3" placeholder="Specify why the offer is rejected or needs revision..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa fa-times"></i> Reject Offer
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted" style="padding: 30px;">
                                        <i class="fa fa-check-circle" style="font-size: 32px; color: #26B99A;"></i>
                                        <p style="margin-top: 10px;">You have no offers pending your approval at this time.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#my-approvals-table').DataTable({
                pageLength: 20
            });
        });
    </script>
@endsection


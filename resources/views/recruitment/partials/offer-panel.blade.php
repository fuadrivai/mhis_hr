{{-- Job Offers & Approval Workflow Panel --}}
@foreach ($candidate->applications as $application)
    @php
        $offers = $application->offers;
        $activeOffer = $application->activeOffer ?: $application->latestOffer;
    @endphp

    <div class="x_panel">
        <div class="x_title">
            <h2>
                <i class="fa fa-handshake-o"></i> Job Offers & Approvals —
                <small>{{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Application #' . $application->id) }}</small>
            </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <button type="button" class="btn btn-success btn-sm text-white" data-toggle="modal" data-target="#createOfferModal-{{ $application->id }}">
                        <i class="fa fa-plus"></i> Create Job Offer
                    </button>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            @if ($activeOffer)
                <div class="row">
                    <div class="col-md-7 col-sm-12">
                        <div class="panel panel-default" style="border-left: 4px solid #26B99A;">
                            <div class="panel-heading" style="background-color: #f7fbfe;">
                                <div class="pull-right">
                                    <span class="badge" style="background-color: #2A3F54;">Version {{ $activeOffer->version }}</span>
                                    @php
                                        $statusClass = match ($activeOffer->status) {
                                            'approved', 'accepted' => 'badge-success',
                                            'declined', 'cancelled' => 'badge-danger',
                                            'pending_approval' => 'badge-warning',
                                            default => 'badge-info',
                                        };
                                        $badgeBg = match ($activeOffer->status) {
                                            'approved', 'accepted' => '#26B99A',
                                            'declined', 'cancelled' => '#d9534f',
                                            'pending_approval' => '#f0ad4e',
                                            default => '#337ab7',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}" style="background-color: {{ $badgeBg }};">
                                        {{ strtoupper(str_replace('_', ' ', $activeOffer->status)) }}
                                    </span>
                                </div>
                                <h4 class="panel-title" style="font-weight: bold; color: #2A3F54;">
                                    {{ $activeOffer->position_title }}
                                    @if ($activeOffer->grade_level)
                                        <small>({{ $activeOffer->grade_level }})</small>
                                    @endif
                                </h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered table-sm" style="margin-bottom: 12px;">
                                    <tr>
                                        <th style="width: 35%; background-color: #fafafa;">Basic Salary</th>
                                        <td>
                                            @if ($activeOffer->canViewCompensation(auth()->user()))
                                                <strong class="text-success" style="font-size: 15px;">
                                                    {{ $activeOffer->getFormattedBasicSalaryFor(auth()->user()) }}
                                                </strong> / month
                                            @else
                                                <span class="text-muted"><i class="fa fa-lock"></i> Confidential / Restricted</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #fafafa;">Allowances</th>
                                        <td>
                                            @if ($activeOffer->canViewCompensation(auth()->user()))
                                                @php $allowances = $activeOffer->getFormattedAllowancesFor(auth()->user()); @endphp
                                                @if (is_array($allowances) && count($allowances) > 0)
                                                    <ul style="padding-left: 18px; margin-bottom: 0;">
                                                        @foreach ($allowances as $item)
                                                            <li>{{ $item['name'] ?? 'Allowance' }}: Rp {{ number_format((float) ($item['amount'] ?? 0), 0, ',', '.') }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">Standard / None</span>
                                                @endif
                                            @else
                                                <span class="text-muted"><i class="fa fa-lock"></i> Confidential / Restricted</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($activeOffer->other_benefits)
                                        <tr>
                                            <th style="background-color: #fafafa;">Other Benefits</th>
                                            <td>{!! nl2br(e($activeOffer->other_benefits)) !!}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th style="background-color: #fafafa;">Proposed Start Date</th>
                                        <td>{{ $activeOffer->proposed_start_date ? $activeOffer->proposed_start_date->format('d M Y') : 'To be mutually agreed' }}</td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #fafafa;">Validity / Response</th>
                                        <td>
                                            Valid until: {{ $activeOffer->valid_until ? $activeOffer->valid_until->format('d M Y') : 'Open' }}
                                            @if ($activeOffer->sent_at)
                                                <br><small class="text-muted">Sent on: {{ $activeOffer->sent_at->format('d M Y H:i') }}</small>
                                            @endif
                                            @if ($activeOffer->responded_at)
                                                <br><small class="text-muted">Responded on: {{ $activeOffer->responded_at->format('d M Y H:i') }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #fafafa;">Document Reference</th>
                                        <td>
                                            @if ($activeOffer->offer_letter_url)
                                                <a href="{{ $activeOffer->offer_letter_url }}" target="_blank" class="btn btn-xs btn-primary">
                                                    <i class="fa fa-external-link"></i> View Document / Drive Link
                                                </a>
                                            @else
                                                <span class="text-muted">No external document link attached</span>
                                            @endif
                                            <a href="{{ route('recruitment.offers.print', [$candidate, $application, $activeOffer]) }}" target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-print"></i> Printable Summary
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                @if ($activeOffer->notes)
                                    <div class="alert alert-warning" style="padding: 8px 12px; margin-bottom: 12px; font-size: 12px;">
                                        <strong>Internal Notes:</strong> {{ $activeOffer->notes }}
                                    </div>
                                @endif

                                @if ($activeOffer->status === 'declined' && $activeOffer->decline_reason)
                                    <div class="alert alert-danger" style="padding: 8px 12px; margin-bottom: 12px;">
                                        <strong>Decline Reason:</strong> {{ $activeOffer->decline_reason }}
                                    </div>
                                @endif

                                {{-- Offer State Action Buttons --}}
                                <div class="btn-group pull-left" style="margin-top: 5px;">
                                    @if ($activeOffer->status === 'draft')
                                        <form action="{{ route('recruitment.offers.submit', [$candidate, $application, $activeOffer]) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa fa-paper-plane"></i> Submit for Approval
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#editOfferModal-{{ $activeOffer->id }}">
                                            <i class="fa fa-pencil"></i> Edit Offer
                                        </button>
                                        <form action="{{ route('recruitment.offers.cancel', [$candidate, $application, $activeOffer]) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Cancel this offer?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-ban"></i> Cancel
                                            </button>
                                        </form>
                                    @elseif ($activeOffer->status === 'pending_approval')
                                        <span class="text-muted" style="margin-right: 10px; line-height: 30px;">
                                            <i class="fa fa-hourglass-half text-warning"></i> Awaiting approval at Level {{ $activeOffer->currentActiveLevel() }}
                                        </span>
                                        @if (auth()->user()?->employee && $activeOffer->canEmployeeApprove(auth()->user()->employee->id))
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#quickApproveModal-{{ $activeOffer->id }}">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#quickRejectModal-{{ $activeOffer->id }}">
                                                <i class="fa fa-times"></i> Reject
                                            </button>
                                        @endif
                                    @elseif ($activeOffer->status === 'approved')
                                        <button type="button" class="btn btn-info btn-sm text-white" data-toggle="modal" data-target="#markSentModal-{{ $activeOffer->id }}">
                                            <i class="fa fa-send"></i> Mark as Sent to Candidate
                                        </button>
                                    @elseif ($activeOffer->status === 'sent')
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#recordResponseModal-{{ $activeOffer->id }}">
                                            <i class="fa fa-check-square-o"></i> Record Candidate Response
                                        </button>
                                    @elseif ($activeOffer->status === 'accepted')
                                        <span class="text-success font-weight-bold" style="line-height: 30px;">
                                            <i class="fa fa-check-circle"></i> Offer accepted! Proceed to Contract & Onboarding below.
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Approval Workflow Steps --}}
                    <div class="col-md-5 col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background-color: #fafafa;">
                                <h4 class="panel-title" style="font-weight: bold; color: #2A3F54;">
                                    <i class="fa fa-tasks"></i> Approval Workflow
                                </h4>
                            </div>
                            <div class="panel-body" style="padding: 10px;">
                                @if ($activeOffer->approvalSteps->isNotEmpty())
                                    <table class="table table-striped table-bordered table-sm" style="margin-bottom: 0;">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">Level</th>
                                                <th>Approver</th>
                                                <th style="width: 80px;">Status</th>
                                                <th>Acted At / Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activeOffer->approvalSteps as $step)
                                                @php
                                                    $isCurrentLevel = $activeOffer->status === 'pending_approval' && $activeOffer->currentActiveLevel() === $step->level;
                                                @endphp
                                                <tr class="{{ $isCurrentLevel ? 'warning' : '' }}">
                                                    <td><strong>L{{ $step->level }}</strong></td>
                                                    <td>
                                                        {{ $step->approver?->personal?->first_name }} {{ $step->approver?->personal?->last_name }}
                                                        @if ($isCurrentLevel)
                                                            <br><small class="text-warning font-weight-bold">Active Reviewer</small>
                                                        @endif
                                                    </td>
                                                    <td>{!! $step->status_badge !!}</td>
                                                    <td>
                                                        @if ($step->acted_at)
                                                            <small>{{ $step->acted_at->format('d M Y H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                        @if ($step->comment)
                                                            <br><small class="text-muted"><em>"{{ $step->comment }}"</em></small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center" style="margin-bottom: 0;">No approvers assigned to this offer.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Offer History Table if multiple versions exist --}}
                        @if ($offers->count() > 1)
                            <div class="panel panel-default" style="margin-top: 15px;">
                                <div class="panel-heading" style="background-color: #fafafa; padding: 6px 12px;">
                                    <h5 class="panel-title" style="font-size: 13px; font-weight: bold;">Offer Revision History</h5>
                                </div>
                                <div class="panel-body" style="padding: 8px;">
                                    <ul style="padding-left: 18px; margin-bottom: 0;">
                                        @foreach ($offers as $histOffer)
                                            <li>
                                                <strong>Version {{ $histOffer->version }}:</strong>
                                                <span class="badge">{{ strtoupper($histOffer->status) }}</span>
                                                — {{ $histOffer->created_at ? $histOffer->created_at->format('d M Y') : '' }}
                                                @if ($histOffer->id === $activeOffer->id)
                                                    <span class="text-success">(Current)</span>
                                                @else
                                                    <a href="{{ route('recruitment.offers.print', [$candidate, $application, $histOffer]) }}" target="_blank" class="btn btn-xs btn-link">View</a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Approve Modal --}}
                <div class="modal fade" id="quickApproveModal-{{ $activeOffer->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('recruitment.my-approvals.approve', $activeOffer) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                    <h4 class="modal-title">Approve Offer (Level {{ $activeOffer->currentActiveLevel() }})</h4>
                                </div>
                                <div class="modal-body">
                                    <p>Approve offer for <strong>{{ $activeOffer->position_title }}</strong>?</p>
                                    <div class="form-group">
                                        <label>Comment (Optional):</label>
                                        <textarea name="comment" class="form-control" rows="2" placeholder="Approval remarks..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Approve Offer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Quick Reject Modal --}}
                <div class="modal fade" id="quickRejectModal-{{ $activeOffer->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('recruitment.my-approvals.reject', $activeOffer) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                    <h4 class="modal-title text-danger"><i class="fa fa-times"></i> Reject Offer</h4>
                                </div>
                                <div class="modal-body">
                                    <p>Rejecting will return the offer to <strong>Draft</strong> status for HR revision.</p>
                                    <div class="form-group">
                                        <label>Rejection Reason / Required Changes <span class="text-danger">*</span></label>
                                        <textarea name="comment" class="form-control" rows="3" placeholder="Provide mandatory explanation..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Reject Offer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Mark Sent Modal --}}
                <div class="modal fade" id="markSentModal-{{ $activeOffer->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('recruitment.offers.mark-sent', [$candidate, $application, $activeOffer]) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                    <h4 class="modal-title">Mark Offer as Sent</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Sent Date & Time</label>
                                        <input type="datetime-local" name="sent_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Document / Google Drive Link <small class="text-muted">(clickable for candidate view)</small></label>
                                        <input type="url" name="offer_letter_url" class="form-control" value="{{ $activeOffer->offer_letter_url }}" placeholder="https://drive.google.com/file/d/...">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> Confirm Sent</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Record Response Modal --}}
                <div class="modal fade" id="recordResponseModal-{{ $activeOffer->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('recruitment.offers.response', [$candidate, $application, $activeOffer]) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                    <h4 class="modal-title">Record Candidate Response</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Candidate Decision <span class="text-danger">*</span></label>
                                        <select name="status" id="response_status_{{ $activeOffer->id }}" class="form-control" required onchange="toggleDeclineReason_{{ $activeOffer->id }}(this.value)">
                                            <option value="accepted">Accepted (Proceed to Contract & Onboarding)</option>
                                            <option value="declined">Declined (Candidate Rejected the Offer)</option>
                                            <option value="negotiating">Negotiating (Terms Counter-Offered)</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="decline_reason_group_{{ $activeOffer->id }}" style="display: none;">
                                        <label>Decline Reason <span class="text-danger">*</span></label>
                                        <textarea name="decline_reason" class="form-control" rows="3" placeholder="Explain candidate's reason for declining..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Response</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <script>
                    function toggleDeclineReason_{{ $activeOffer->id }}(val) {
                        var group = document.getElementById('decline_reason_group_{{ $activeOffer->id }}');
                        if (group) {
                            group.style.display = (val === 'declined') ? 'block' : 'none';
                        }
                    }
                </script>

                {{-- Edit Draft Offer Modal --}}
                <div class="modal fade" id="editOfferModal-{{ $activeOffer->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('recruitment.offers.update', [$candidate, $application, $activeOffer]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                    <h4 class="modal-title">Edit Draft Offer (Version {{ $activeOffer->version }})</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Position Title <span class="text-danger">*</span></label>
                                                <input type="text" name="position_title" class="form-control" value="{{ $activeOffer->position_title }}" required maxlength="255">
                                            </div>
                                            <div class="form-group">
                                                <label>Grade / Level</label>
                                                <input type="text" name="grade_level" class="form-control" value="{{ $activeOffer->grade_level }}" placeholder="e.g. Senior / Level 3">
                                            </div>
                                            <div class="form-group">
                                                <label>Basic Salary (IDR) <span class="text-danger">*</span></label>
                                                <input type="number" name="basic_salary" class="form-control" value="{{ $activeOffer->basic_salary }}" required min="0">
                                            </div>
                                            <div class="form-group">
                                                <label>Proposed Start Date</label>
                                                <input type="date" name="proposed_start_date" class="form-control" value="{{ $activeOffer->proposed_start_date ? $activeOffer->proposed_start_date->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Valid Until</label>
                                                <input type="date" name="valid_until" class="form-control" value="{{ $activeOffer->valid_until ? $activeOffer->valid_until->format('Y-m-d') : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Document / Google Drive Link</label>
                                                <input type="url" name="offer_letter_url" class="form-control" value="{{ $activeOffer->offer_letter_url }}" placeholder="https://drive.google.com/file/d/...">
                                            </div>
                                            <div class="form-group">
                                                <label>Other Benefits</label>
                                                <textarea name="other_benefits" class="form-control" rows="3">{{ $activeOffer->other_benefits }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Internal Notes</label>
                                                <textarea name="notes" class="form-control" rows="2">{{ $activeOffer->notes }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="submit_for_approval" value="1"> Submit immediately for approval after saving
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center text-muted" style="padding: 25px;">
                    <i class="fa fa-briefcase" style="font-size: 32px; color: #ccc;"></i>
                    <p style="margin-top: 10px;">No job offer has been created for this application yet.</p>
                    <button type="button" class="btn btn-success btn-sm text-white" data-toggle="modal" data-target="#createOfferModal-{{ $application->id }}">
                        <i class="fa fa-plus"></i> Create Job Offer
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Create Offer Modal --}}
    <div class="modal fade" id="createOfferModal-{{ $application->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('recruitment.offers.store', [$candidate, $application]) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                        <h4 class="modal-title">Create Job Offer — {{ $candidate->full_name }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Position Title <span class="text-danger">*</span></label>
                                    <input type="text" name="position_title" class="form-control" value="{{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Employee') }}" required maxlength="255">
                                </div>
                                <div class="form-group">
                                    <label>Grade / Level</label>
                                    <input type="text" name="grade_level" class="form-control" placeholder="e.g. Senior / Level 3">
                                </div>
                                <div class="form-group">
                                    <label>Basic Salary (IDR) <span class="text-danger">*</span></label>
                                    <input type="number" name="basic_salary" class="form-control" placeholder="e.g. 10000000" required min="0">
                                </div>
                                <div class="form-group">
                                    <label>Proposed Start Date</label>
                                    <input type="date" name="proposed_start_date" class="form-control" value="{{ now()->addDays(14)->format('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label>Offer Valid Until</label>
                                    <input type="date" name="valid_until" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Document / Google Drive Link <small class="text-muted">(clickable file link)</small></label>
                                    <input type="url" name="offer_letter_url" class="form-control" placeholder="https://drive.google.com/file/d/...">
                                </div>
                                <div class="form-group">
                                    <label>Other Benefits & Perks</label>
                                    <textarea name="other_benefits" class="form-control" rows="3" placeholder="Health insurance, laptop allowance, annual bonus, etc."></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Internal Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Private internal HR remarks"></textarea>
                                </div>
                            </div>
                        </div>

                        <hr style="margin: 15px 0;">
                        <h5><strong>Flexible Approval Workflow (Sequential Levels)</strong></h5>
                        <p class="text-muted" style="font-size: 12px;">Add approvers in sequence. Level 2 can only approve after Level 1 approves.</p>

                        <div id="approver-rows-{{ $application->id }}">
                            <div class="row approver-row" style="margin-bottom: 8px;">
                                <div class="col-xs-3">
                                    <input type="number" name="approvers[0][level]" class="form-control input-sm" value="1" min="1" placeholder="Level (e.g. 1)">
                                </div>
                                <div class="col-xs-8">
                                    <select name="approvers[0][employee_id]" class="form-control input-sm select2-approver" data-placeholder="Choose Approver (Employee)">
                                        <option value="">-- Choose Approver --</option>
                                        @foreach ($assignableApprovers as $appr)
                                            <option value="{{ $appr->id }}">
                                                {{ $appr->personal?->first_name }} {{ $appr->personal?->last_name }} ({{ $appr->employment?->job_position?->name ?: 'Staff' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xs-1 text-center">
                                    <button type="button" class="btn btn-xs btn-danger btn-remove-approver" style="margin-top: 4px;"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <div class="row approver-row" style="margin-bottom: 8px;">
                                <div class="col-xs-3">
                                    <input type="number" name="approvers[1][level]" class="form-control input-sm" value="2" min="1" placeholder="Level (e.g. 2)">
                                </div>
                                <div class="col-xs-8">
                                    <select name="approvers[1][employee_id]" class="form-control input-sm select2-approver" data-placeholder="Choose Approver (Employee)">
                                        <option value="">-- Choose Approver --</option>
                                        @foreach ($assignableApprovers as $appr)
                                            <option value="{{ $appr->id }}">
                                                {{ $appr->personal?->first_name }} {{ $appr->personal?->last_name }} ({{ $appr->employment?->job_position?->name ?: 'Staff' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xs-1 text-center">
                                    <button type="button" class="btn btn-xs btn-danger btn-remove-approver" style="margin-top: 4px;"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-xs btn-default btn-add-approver" data-target="{{ $application->id }}" style="margin-top: 5px;">
                            <i class="fa fa-plus"></i> Add Approver Step
                        </button>

                        <div class="checkbox" style="margin-top: 15px;">
                            <label>
                                <input type="checkbox" name="submit_for_approval" value="1" checked> Submit immediately for approval
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Create & Save Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach


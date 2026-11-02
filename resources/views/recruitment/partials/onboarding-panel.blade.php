{{-- Contract & Onboarding Panel --}}
@foreach ($candidate->applications as $application)
    @php
        $onboarding = $application->onboarding;
    @endphp

    <div class="x_panel">
        <div class="x_title">
            <h2>
                <i class="fa fa-file-text-o"></i> Contract & Joining (Onboarding) —
                <small>{{ $application->jobOpening?->title ?: ($application->position_applied ?: 'Application #' . $application->id) }}</small>
            </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#updateOnboardingModal-{{ $application->id }}">
                        <i class="fa fa-pencil"></i> Update Contract & Joining
                    </button>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <div class="row">
                {{-- Contract Status Card --}}
                <div class="col-md-6 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #fafafa;">
                            <h4 class="panel-title" style="font-weight: bold; color: #2A3F54;">
                                <i class="fa fa-file-pdf-o"></i> Employment Contract
                            </h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered table-sm" style="margin-bottom: 10px;">
                                <tr>
                                    <th style="width: 40%; background-color: #fafafa;">Contract Status</th>
                                    <td>
                                        @if ($onboarding)
                                            {!! $onboarding->contract_status_badge !!}
                                        @else
                                            <span class="badge badge-default" style="background-color: #777;">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="background-color: #fafafa;">Sent Date</th>
                                    <td>{{ $onboarding?->contract_sent_at ? $onboarding->contract_sent_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th style="background-color: #fafafa;">Signed Date</th>
                                    <td>{{ $onboarding?->contract_signed_at ? $onboarding->contract_signed_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th style="background-color: #fafafa;">Signed PDF Document</th>
                                    <td>
                                        @if ($onboarding?->contract_file_path)
                                            <a href="{{ route('recruitment.onboarding.contract.download', [$candidate, $application]) }}" class="btn btn-xs btn-success">
                                                <i class="fa fa-download"></i> Download Signed Contract
                                            </a>
                                        @else
                                            <span class="text-muted">No PDF document uploaded</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="background-color: #fafafa;">External File Link</th>
                                    <td>
                                        @if ($onboarding?->contract_url)
                                            <a href="{{ $onboarding->contract_url }}" target="_blank" class="btn btn-xs btn-primary">
                                                <i class="fa fa-external-link"></i> Open Drive / Cloud Link
                                            </a>
                                        @else
                                            <span class="text-muted">None attached</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Joining & Employee Draft Card --}}
                <div class="col-md-6 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #fafafa;">
                            <h4 class="panel-title" style="font-weight: bold; color: #2A3F54;">
                                <i class="fa fa-user-plus"></i> Joining & Employee Status
                            </h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered table-sm" style="margin-bottom: 10px;">
                                <tr>
                                    <th style="width: 40%; background-color: #fafafa;">Joining Status</th>
                                    <td>
                                        @if ($onboarding)
                                            {!! $onboarding->join_status_badge !!}
                                        @else
                                            <span class="badge badge-default" style="background-color: #777;">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="background-color: #fafafa;">Official Join Date</th>
                                    <td>
                                        @if ($onboarding?->join_date)
                                            <strong>{{ $onboarding->join_date->format('d F Y') }}</strong>
                                        @else
                                            <span class="text-muted">Not confirmed yet</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($onboarding?->join_status === 'postponed')
                                    <tr>
                                        <th style="background-color: #fafafa;">Postponed To</th>
                                        <td class="text-warning font-weight-bold">
                                            {{ $onboarding->postponed_to ? $onboarding->postponed_to->format('d F Y') : '-' }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th style="background-color: #fafafa;">Employee Draft</th>
                                    <td>
                                        @if ($onboarding?->employee_id)
                                            <span class="badge badge-success" style="background-color: #26B99A;">
                                                <i class="fa fa-check"></i> Draft Created (#{{ $onboarding->employee_id }})
                                            </span>
                                            <a href="/employee/{{ $onboarding->employee_id }}/edit" target="_blank" class="btn btn-xs btn-primary pull-right">
                                                <i class="fa fa-external-link"></i> Edit Employee Profile
                                            </a>
                                        @else
                                            <span class="text-muted">Not created yet</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Create Employee Draft Button (Only available if join_status == joined and no employee draft yet) --}}
                            @if ($onboarding && $onboarding->canCreateEmployeeDraft())
                                <div class="alert alert-success" style="padding: 10px; margin-top: 15px; margin-bottom: 0;">
                                    <p style="margin-bottom: 8px;">
                                        <i class="fa fa-info-circle"></i> Candidate has officially joined! You can now generate an Employee Draft in the system.
                                    </p>
                                    <form action="{{ route('recruitment.onboarding.create-draft', [$candidate, $application]) }}" method="POST" onsubmit="return confirm('Generate Employee Draft for {{ $candidate->full_name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                            <i class="fa fa-user-plus"></i> Create Employee Draft
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Contract & Onboarding Modal --}}
    <div class="modal fade" id="updateOnboardingModal-{{ $application->id }}" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    <h4 class="modal-title">Update Contract & Joining Details</h4>
                </div>
                <div class="modal-body">
                    {{-- 1. Contract Form --}}
                    <form action="{{ route('recruitment.onboarding.contract', [$candidate, $application]) }}" method="POST" enctype="multipart/form-data" style="margin-bottom: 25px;">
                        @csrf
                        <h5 style="font-weight: bold; color: #2A3F54; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                            1. Employment Contract Document
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract Status <span class="text-danger">*</span></label>
                                    <select name="contract_status" class="form-control" required>
                                        <option value="pending" @selected(($onboarding?->contract_status ?? 'pending') === 'pending')>Pending (Drafting / Awaiting)</option>
                                        <option value="sent" @selected(($onboarding?->contract_status ?? '') === 'sent')>Sent to Candidate</option>
                                        <option value="signed" @selected(($onboarding?->contract_status ?? '') === 'signed')>Signed by Both Parties</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Upload Signed Contract PDF <small class="text-muted">(Max 10 MB)</small></label>
                                    <input type="file" name="contract_file" class="form-control" accept="application/pdf">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Document / Google Drive URL <small class="text-muted">(Cloud Link)</small></label>
                                    <input type="url" name="contract_url" class="form-control" value="{{ $onboarding?->contract_url }}" placeholder="https://drive.google.com/file/d/...">
                                </div>
                                <div class="form-group">
                                    <label>Contract Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Contract signing notes or terms">{{ $onboarding?->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Contract Details</button>
                    </form>

                    {{-- 2. Joining Form --}}
                    <form action="{{ route('recruitment.onboarding.join', [$candidate, $application]) }}" method="POST">
                        @csrf
                        <h5 style="font-weight: bold; color: #2A3F54; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                            2. Candidate Joining & Onboarding Status
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Join Status <span class="text-danger">*</span></label>
                                    <select name="join_status" id="join_status_{{ $application->id }}" class="form-control" required onchange="togglePostponed_{{ $application->id }}(this.value)">
                                        <option value="pending" @selected(($onboarding?->join_status ?? 'pending') === 'pending')>Pending (Awaiting Join Date)</option>
                                        <option value="joined" @selected(($onboarding?->join_status ?? '') === 'joined')>Joined (First Day Completed)</option>
                                        <option value="postponed" @selected(($onboarding?->join_status ?? '') === 'postponed')>Postponed (Rescheduled)</option>
                                        <option value="no_show" @selected(($onboarding?->join_status ?? '') === 'no_show')>No Show (Did not arrive)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Confirmed Join Date</label>
                                    <input type="date" name="join_date" class="form-control" value="{{ $onboarding?->join_date ? $onboarding->join_date->format('Y-m-d') : ($application->activeOffer?->proposed_start_date ? $application->activeOffer->proposed_start_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="postponed_group_{{ $application->id }}" style="display: {{ ($onboarding?->join_status ?? '') === 'postponed' ? 'block' : 'none' }};">
                                    <label>Postponed To Date <span class="text-danger">*</span></label>
                                    <input type="date" name="postponed_to" class="form-control" value="{{ $onboarding?->postponed_to ? $onboarding->postponed_to->format('Y-m-d') : '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Status Change Reason / Explanation</label>
                                    <textarea name="reason" class="form-control" rows="3" placeholder="Provide explanation or onboarding check-in remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Save Joining Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglePostponed_{{ $application->id }}(val) {
            var el = document.getElementById('postponed_group_{{ $application->id }}');
            if (el) {
                el.style.display = (val === 'postponed') ? 'block' : 'none';
            }
        }
    </script>
@endforeach


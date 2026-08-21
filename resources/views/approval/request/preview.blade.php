@extends('layouts.main-layout')

@section('content-class')
    <link rel="stylesheet" href="{{ asset('css/approval-request-preview.css') }}">
@endsection

@section('content-child')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $requester = $approvalRequest->requester;
        $personal = optional($requester)->personal;
        $employment = optional($requester)->employment;
        $requesterName = optional($personal)->fullname ?? 'Employee ' . $approvalRequest->requester_employee_id;
        $initials = collect(explode(' ', $requesterName))
            ->filter()
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');
        $payload = optional($approvalRequest->data)->payload ?? [];
        $schemaLabels = collect(optional($approvalRequest->type)->schema ?? [])->mapWithKeys(
            fn($field) => [$field['name'] => $field['label'] ?? $field['name']],
        );
        $attachments = $approvalRequest->attachments;
        $firstAttachment = $attachments->first();
        $fieldOrder = ['start_date', 'end_date', 'start_time', 'end_time'];
        $orderedPayload = collect($payload)->sortBy(
            fn($value, $field) => array_search($field, $fieldOrder, true) === false
                ? count($fieldOrder)
                : array_search($field, $fieldOrder, true),
        );
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->hasRole('admin') || $currentUser->roles->contains('id', 1));
        $isAssignedApprover = $approvalRequest->approvals->contains(
            fn($approval) => $approval->status === 'pending' &&
                $approval->show_action &&
                $approval->approver_employee_id === optional($currentUser->employee)->id,
        );
        $canProcessRequest = $isAdmin || $isAssignedApprover;
        $actionHistories = $approvalRequest->histories
            ->whereIn('action', ['approved', 'rejected'])
            ->keyBy(fn($history) => $history->step_order . ':' . $history->action);
    @endphp

    <div class="x_panel approval-detail-panel">
        <div class="approval-detail-body">
            <div class="row">
                <div class="col-lg-8 approval-preview-area">
                    <div class="approval-preview-frame" id="attachment-preview">
                        @if ($firstAttachment)
                            @php $firstUrl = asset('storage/' . $firstAttachment->file_path); @endphp
                            @if (str_starts_with((string) $firstAttachment->mime_type, 'image/'))
                                <img src="{{ $firstUrl }}" alt="{{ $firstAttachment->file_name }}">
                            @elseif ($firstAttachment->mime_type === 'application/pdf')
                                <embed src="{{ $firstUrl }}" type="application/pdf">
                            @else
                                <div class="approval-preview-file"><i
                                        class="fa fa-file-o"></i><small>{{ $firstAttachment->file_name }}</small></div>
                            @endif
                        @else
                            <div class="approval-preview-empty"><i class="fa fa-paperclip fa-2x"></i>
                                <p class="mt-3 mb-0">No attachment provided</p>
                            </div>
                        @endif
                    </div>

                    @if ($attachments->count() > 1)
                        <div class="approval-thumbnail-list">
                            @foreach ($attachments as $attachment)
                                @php $attachmentUrl = asset('storage/' . $attachment->file_path); @endphp
                                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener"
                                    class="approval-thumbnail {{ $loop->first ? 'active' : '' }}"
                                    title="Open {{ $attachment->file_name }}">
                                    @if (str_starts_with((string) $attachment->mime_type, 'image/'))
                                        <img src="{{ $attachmentUrl }}" alt="{{ $attachment->file_name }}">
                                    @elseif ($attachment->mime_type === 'application/pdf')
                                        <i class="fa fa-file-pdf-o text-danger fa-lg"></i>
                                    @else
                                        <i class="fa fa-file-o fa-lg"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <aside class="approval-info-panel">
                        <h2 class="approval-info-title">Request information</h2>
                        <div class="approval-requester">
                            <span class="approval-avatar">{{ $initials }}</span>
                            <div>
                                <div class="approval-requester-name">{{ $requesterName }}</div>
                                <div class="approval-requester-role">
                                    {{ optional($employment->job_position)->name ?? 'Employee' }}{{ optional($employment->branch)->name ? ' | ' . $employment->branch->name : '' }}
                                </div>
                            </div>
                        </div>

                        <dl class="approval-info-list">
                            <div class="approval-info-row">
                                <dt>Request ID</dt>
                                <dd>#{{ $approvalRequest->id }}</dd>
                            </div>
                            <div class="approval-info-row">
                                <dt>Requested date</dt>
                                <dd>{{ optional($approvalRequest->created_at)->format('d M Y') ?? '-' }}</dd>
                            </div>
                            <div class="approval-info-row">
                                <dt>Policy</dt>
                                <dd>{{ optional($approvalRequest->type)->name ?? '-' }}</dd>
                            </div>
                            @foreach ($orderedPayload as $field => $value)
                                <div class="approval-info-row">
                                    <dt>{{ $schemaLabels->get($field, ucfirst(str_replace('_', ' ', $field))) }}</dt>
                                    <dd>{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                                </div>
                            @endforeach
                            @if ($approvalRequest->note)
                                <div class="approval-info-row">
                                    <dt>Note</dt>
                                    <dd>{{ $approvalRequest->note }}</dd>
                                </div>
                            @endif
                            <div class="approval-info-row">
                                <dt>Request status</dt>
                                <dd
                                    class="approval-status {{ $approvalRequest->status }} text-bold text-white text-capitalize justify-content-center">
                                    {{ ucfirst($approvalRequest->status) }}</dd>
                            </div>
                        </dl>

                        @if ($approvalRequest->approvals->isNotEmpty())
                            <h3 class="approval-section-label">Approval progress</h3>
                            <ul class="approval-steps">
                                @foreach ($approvalRequest->approvals->sortBy('step_order') as $approval)
                                    @php
                                        $actionHistory = $actionHistories->get(
                                            $approval->step_order . ':' . $approval->status,
                                        );
                                    @endphp
                                    <li class="approval-step {{ $approval->status }}">
                                        <span class="approval-step-icon"><i
                                                class="fa {{ $approval->status === 'approved' ? 'fa-check-circle' : ($approval->status === 'rejected' ? 'fa-times-circle' : 'fa-clock-o') }}"></i></span>
                                        <span>Step {{ $approval->step_order }}:
                                            {{ optional(optional($approval->approver)->personal)->fullname ?? 'Approver' }}
                                            ({{ ucfirst($approval->status) }})
                                            @if ($actionHistory)
                                                by
                                                {{ optional(optional($actionHistory->approver)->personal)->fullname ?? 'Approver' }}
                                                on {{ optional($actionHistory->created_at)->format('d M Y H:i') }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </aside>
                </div>
            </div>
        </div>

        <div class="approval-detail-footer">
            <a onclick="window.history.back()" class="btn btn-outline-secondary">Back to list</a>
            @if ($approvalRequest->status === 'pending' && $canProcessRequest)
                <div class="approval-detail-actions">
                    <button type="button" class="btn btn-outline-danger btn-request-action" data-action="rejected"
                        data-toggle="modal" data-target="#requestActionModal" title="Reject request">
                        <i class="fa fa-times"></i> Reject
                    </button>
                    <button type="button" class="btn btn-primary btn-request-action" data-action="approved"
                        data-toggle="modal" data-target="#requestActionModal" title="Approve request">
                        <i class="fa fa-check"></i> Approve
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="requestActionModal" tabindex="-1" role="dialog" aria-labelledby="requestActionModalTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('time.request.action', $approvalRequest) }}" class="modal-content">
                @csrf
                <input type="hidden" name="action" id="request-action-value">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestActionModalTitle">Confirm action</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="request-action-note">Note</label>
                        <textarea class="form-control" id="request-action-note" name="note" rows="4"
                            placeholder="Add a note (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="request-action-submit">Confirm</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $('#requestActionModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const action = button.data('action');
            const isApproval = action === 'approved';
            const modal = $(this);

            modal.find('#request-action-value').val(action);
            modal.find('#request-action-note').val('').attr('placeholder',
                `Add a note for this ${isApproval ? 'approval' : 'rejection'} (optional)`);
            modal.find('#requestActionModalTitle').text(isApproval ? 'Approve request' : 'Reject request');
            modal.find('#request-action-submit')
                .toggleClass('btn-primary', isApproval)
                .toggleClass('btn-danger', !isApproval)
                .text(isApproval ? 'Approve' : 'Reject');
        });
    </script>
@endsection

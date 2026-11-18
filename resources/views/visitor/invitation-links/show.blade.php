@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title"><h2>{{ $batch->name }}</h2><div class="clearfix"></div></div>
            <div class="x_content">
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                @if (session('email_failures'))
                    <div class="alert alert-warning">
                        Email could not be sent to: {{ implode(', ', session('email_failures')) }}.
                        Copy and share the corresponding links below instead.
                    </div>
                @endif
                <p>
                    {{ $batch->visit_date->format('d M Y') }},
                    {{ $batch->expected_arrival_at->format('H:i') }} - {{ $batch->expected_departure_at->format('H:i') }}
                    · {{ $batch->purpose->name }} · {{ $batch->branch->name }}
                </p>
                <h4>Registration links</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Recipient</th><th>One-time registration link</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
                        <tbody>
                            @php $manualLinkIndex = 0; @endphp
                            @forelse ($batch->links as $index => $link)
                                @php
                                    $status = $link->submitted_at
                                        ? 'Submitted'
                                        : ($link->revoked_at
                                            ? 'Revoked'
                                            : ($batch->expires_at->isPast() ? 'Expired' : 'Waiting for guest'));
                                    $generatedLink = $generatedLinks->firstWhere('id', $link->id);
                                    if (!$link->recipient_email) {
                                        $manualLinkIndex++;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        @if ($link->recipient_email)
                                            {{ $link->recipient_email }}
                                        @elseif (!$link->submitted_at && !$link->revoked_at && $batch->expires_at->isFuture())
                                            <form method="POST" action="{{ route('visitor.invitation-links.recipient-email', $link->id) }}" class="form-inline">
                                                @csrf
                                                <div class="input-group">
                                                    <input
                                                        type="email"
                                                        name="recipient_email"
                                                        class="form-control"
                                                        placeholder="Share manually #{{ $manualLinkIndex }}"
                                                        aria-label="Email for manually shared link"
                                                        maxlength="255"
                                                        required
                                                    >
                                                    <span class="input-group-btn"><button type="submit" class="btn btn-default">Save</button></span>
                                                </div>
                                            </form>
                                        @else
                                            Share manually #{{ $manualLinkIndex }}
                                        @endif
                                        @if ($link->submitted_at && $link->visits->isNotEmpty())
                                            <div class="registered-attendees">
                                                <strong>Registered attendees</strong>
                                                @foreach ($link->visits as $registeredVisit)
                                                    <div>{{ $registeredVisit->visitor->full_name }} <span>({{ $registeredVisit->visit_code }})</span></div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                    @if ($generatedLink)
                                        <div class="input-group">
                                            <input class="form-control registration-link-input" readonly value="{{ $generatedLink['url'] }}" aria-label="Registration link">
                                            <span class="input-group-btn"><button type="button" class="btn btn-default copy-registration-link">Copy link</button></span>
                                        </div>
                                        <span class="copy-link-status" role="status" aria-live="polite"></span>
                                    @else
                                        <span class="text-muted">Link unavailable</span>
                                    @endif
                                    </td>
                                    <td>{{ $status }}</td>
                                    <td>{{ $link->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td>
                                        @if ($link->submitted_at && $link->visit?->visitor)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-info view-participant-details"
                                                data-full-name="{{ $link->visit->visitor->full_name }}"
                                                data-phone="{{ $link->visit->visitor->phone }}"
                                                data-email="{{ $link->visit->visitor->email }}"
                                                data-company="{{ $link->visit->visitor->company }}"
                                                data-visit-code="{{ $link->visit->visit_code }}"
                                            ><i class="fa fa-user"></i> View details</button>
                                        @elseif (!$link->submitted_at && !$link->revoked_at && $batch->expires_at->isFuture())
                                            <form method="POST" action="{{ route('visitor.invitation-links.revoke', $link->id) }}" onsubmit="return confirm('Revoke this registration link?')">
                                                @csrf
                                                <button class="btn btn-xs btn-danger" type="submit">Revoke</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No registration links in this batch.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a class="btn btn-default" href="{{ route('visitor.invitation-links.index') }}">Back to batches</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="participant-details-modal" tabindex="-1" role="dialog" aria-labelledby="participant-details-title">
        <div class="modal-dialog participant-modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header participant-modal-header">
                    <h4 class="modal-title" id="participant-details-title"><i class="fa fa-user-circle"></i> Participant details</h4>
                    <button type="button" class="close participant-modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="participant-detail-grid">
                        <div class="participant-detail-card participant-detail-name">
                            <span class="participant-detail-label">Full name</span>
                            <strong id="participant-detail-name">—</strong>
                        </div>
                        <div class="participant-detail-card">
                            <span class="participant-detail-label">Phone</span>
                            <span id="participant-detail-phone">—</span>
                        </div>
                        <div class="participant-detail-card">
                            <span class="participant-detail-label">Email</span>
                            <span id="participant-detail-email">—</span>
                        </div>
                        <div class="participant-detail-card">
                            <span class="participant-detail-label">Company / organization</span>
                            <span id="participant-detail-company">—</span>
                        </div>
                        <div class="participant-detail-card">
                            <span class="participant-detail-label">Visit code</span>
                            <span id="participant-detail-code" class="participant-visit-code">—</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer participant-modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <style>
        .registered-attendees {
            margin-top: 8px;
            color: #536a7b;
            font-size: 12px;
            line-height: 1.6;
        }

        .registered-attendees strong {
            display: block;
        }

        .registered-attendees span {
            color: #827a73;
        }

        #participant-details-modal .participant-modal-dialog {
            width: 560px;
            max-width: calc(100% - 24px);
            margin: 7vh auto;
        }

        #participant-details-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 18px 55px rgba(0, 0, 0, .24);
        }

        #participant-details-modal .participant-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e5e5;
            background: #fff;
            color: #73879c;
        }

        #participant-details-modal .modal-title {
            margin: 0;
            color: #73879c;
            font-size: 18px;
            font-weight: 600;
        }

        #participant-details-modal .modal-title .fa {
            margin-right: 7px;
            color: #73879c;
        }

        #participant-details-modal .participant-modal-close {
            float: none;
            margin-left: 16px;
            color: #73879c;
            opacity: .7;
            text-shadow: none;
        }

        #participant-details-modal .participant-modal-close:hover {
            color: #434a54;
            opacity: 1;
        }

        #participant-details-modal .modal-body {
            padding: 24px 26px;
            background: #f6f8fa;
        }

        #participant-details-modal .participant-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        #participant-details-modal .participant-detail-card {
            min-width: 0;
            padding: 13px 15px;
            border: 1px solid #dfe5ea;
            border-radius: 8px;
            background: #fff;
        }

        #participant-details-modal .participant-detail-name {
            grid-column: 1 / -1;
            border-left: 3px solid #73879c;
        }

        #participant-details-modal .participant-detail-card > span,
        #participant-details-modal .participant-detail-card > strong {
            display: block;
            overflow-wrap: anywhere;
        }

        #participant-details-modal .participant-detail-label {
            margin-bottom: 4px;
            color: #827a73;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        #participant-details-modal .participant-detail-card > span:not(.participant-detail-label),
        #participant-details-modal .participant-detail-card > strong {
            color: #302b27;
            font-size: 14px;
        }

        #participant-details-modal .participant-detail-name > strong {
            color: #434a54;
            font-size: 17px;
        }

        #participant-details-modal .participant-visit-code {
            color: #434a54 !important;
            font-weight: 700;
            letter-spacing: .08em;
        }

        #participant-details-modal .participant-modal-footer {
            padding: 14px 26px;
            border-top: 1px solid #dfe5ea;
            background: #fff;
        }

        @media (max-width: 480px) {
            #participant-details-modal .participant-modal-dialog {
                margin: 16px auto;
            }

            #participant-details-modal .participant-modal-header,
            #participant-details-modal .modal-body {
                padding-right: 18px;
                padding-left: 18px;
            }

            #participant-details-modal .participant-detail-grid {
                grid-template-columns: 1fr;
            }

            #participant-details-modal .participant-detail-name {
                grid-column: auto;
            }
        }
    </style>
    <script>
        $(function () {
            $('.view-participant-details').on('click', function () {
                var button = $(this);
                $('#participant-detail-name').text(button.data('full-name') || '—');
                $('#participant-detail-phone').text(button.data('phone') || '—');
                $('#participant-detail-email').text(button.data('email') || '—');
                $('#participant-detail-company').text(button.data('company') || '—');
                $('#participant-detail-code').text(button.data('visit-code') || '—');
                $('#participant-details-modal').modal('show');
            });

            $('.copy-registration-link').on('click', function () {
                var button = this;
                var $row = $(button).closest('tr');
                var input = $row.find('.registration-link-input')[0];
                var $status = $row.find('.copy-link-status');

                function copyFallback() {
                    input.focus();
                    input.select();
                    input.setSelectionRange(0, input.value.length);
                    var copied = document.execCommand('copy');
                    $status.text(copied ? ' Link copied.' : ' Link selected; copy it manually.');
                    $(button).text(copied ? 'Copied' : 'Copy link');
                }

                if (!navigator.clipboard || !navigator.clipboard.writeText) {
                    copyFallback();
                    return;
                }

                try {
                    navigator.clipboard.writeText(input.value).then(function () {
                        $(button).text('Copied');
                        $status.text(' Link copied.');
                    }).catch(copyFallback);
                } catch (error) {
                    copyFallback();
                }
            });
        });
    </script>
@endsection

@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 visitor-list-page">
        @if (session('success'))
            <div class="alert alert-success visitor-flash">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('visitor_qr_token'))
            <section class="visitor-created-card">
                <div class="visitor-created-copy">
                    <span class="visitor-created-icon"><i class="fa fa-check"></i></span>
                    <div>
                        <p class="visitor-eyebrow">Invitation created</p>
                        <h3>{{ session('visitor_visit_code') }}</h3>
                        <p class="visitor-muted">Save the one-time QR/scanner token securely. It will not be shown again.</p>
                    </div>
                </div>
                <div class="visitor-created-qr">
                    <div id="visitor-qr-code" data-token="{{ session('visitor_qr_token') }}" role="img" aria-label="Visitor QR code"></div>
                    <div class="visitor-token-wrap">
                        <code id="new-visitor-token">{{ session('visitor_qr_token') }}</code>
                        <button type="button" class="btn btn-default btn-sm" id="copy-visitor-token" aria-label="Copy invitation token">
                            <i class="fa fa-copy"></i> Copy token
                        </button>
                        <span id="copy-visitor-token-status" class="visitor-copy-status" role="status" aria-live="polite"></span>
                    </div>
                </div>
            </section>
        @endif

        <section class="visitor-page-heading">
            <div>
                <p class="visitor-eyebrow">Visitor Management</p>
                <h1>My Invitations / Visits</h1>
                <p class="visitor-muted">Review invitations you created and visits assigned to you or shared with you for monitoring.</p>
            </div>
            <div class="visitor-heading-actions">
                <a class="btn btn-default" href="{{ route('visitor.history') }}"><i class="fa fa-history"></i> Visit history</a>
                @if (auth()->user()->employee)
                    <a class="btn btn-success" href="{{ route('visitor.create') }}"><i class="fa fa-plus"></i> New invitation</a>
                @endif
            </div>
        </section>

        <section class="visitor-filter-card">
            <form method="POST" action="{{ route('visitor.search-list') }}" class="visitor-search-form">
                @csrf
                <label for="visitor-search">Search visits</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input id="visitor-search" class="form-control" name="search" value="{{ $searchTerm }}" placeholder="Visitor name, visit code, phone, email, or badge">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </span>
                </div>
                @if ($searchTerm !== '')
                    <a class="visitor-clear-search" href="{{ route('visitor.index', ['clear_search' => 1]) }}">Clear search</a>
                @endif
            </form>

            <form method="GET" action="{{ route('visitor.index') }}" class="visitor-filter-form">
                <div class="visitor-filter-field">
                    <label for="visitor-status">Status</label>
                    <select id="visitor-status" class="form-control" name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="visitor-filter-field">
                    <label for="visitor-from">Visit date from</label>
                    <input id="visitor-from" class="form-control" type="date" name="from" value="{{ request('from') }}">
                </div>
                <div class="visitor-filter-field">
                    <label for="visitor-to">Visit date to</label>
                    <input id="visitor-to" class="form-control" type="date" name="to" value="{{ request('to') }}">
                </div>
                <div class="visitor-filter-actions">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> Apply filters</button>
                    @if (request()->filled('status') || request()->filled('from') || request()->filled('to'))
                        <a class="btn btn-default" href="{{ route('visitor.index') }}">Reset</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="x_panel visitor-table-panel">
            <div class="x_title visitor-table-heading">
                <div>
                    <h2>Visit records</h2>
                    <small class="visitor-muted">
                        Showing {{ $visits->firstItem() ?? 0 }}–{{ $visits->lastItem() ?? 0 }} of {{ $visits->total() }} visits
                    </small>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table visitor-table">
                        <thead>
                            <tr>
                                <th>Visit</th>
                                <th>Visitor</th>
                                <th>Branch / host</th>
                                <th>Date / time</th>
                                <th>Status</th>
                                <th class="visitor-action-column">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visits as $visit)
                                @php
                                    $statusClass = match ($visit->status) {
                                        'expected' => 'visitor-status-success',
                                        'pending_confirmation' => 'visitor-status-warning',
                                        'rejected', 'cancelled', 'no_show' => 'visitor-status-muted',
                                        'checked_in' => 'visitor-status-info',
                                        'completed' => 'visitor-status-complete',
                                        default => 'visitor-status-muted',
                                    };
                                @endphp
                                <tr>
                                    <td data-label="Visit">
                                        <span class="visitor-code">{{ $visit->visit_code }}</span>
                                        <span class="visitor-table-meta">{{ str_replace('_', ' ', ucfirst($visit->registration_method)) }}</span>
                                    </td>
                                    <td data-label="Visitor">
                                        <strong>{{ $visit->visitor->full_name }}</strong>
                                        @if ($visit->visitor->company)
                                            <span class="visitor-table-meta">{{ $visit->visitor->company }}</span>
                                        @endif
                                    </td>
                                    <td data-label="Branch / host">
                                        <strong>{{ $visit->branch->name }}</strong>
                                        <span class="visitor-table-meta">Host: {{ $visit->host->user?->name ?? '—' }}</span>
                                    </td>
                                    <td data-label="Date / time">
                                        <strong>{{ $visit->visit_date->format('d M Y') }}</strong>
                                        <span class="visitor-table-meta">
                                            {{ $visit->expected_arrival_at?->format('H:i') ?? '—' }}
                                            – {{ $visit->expected_departure_at?->format('H:i') ?? '—' }}
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="visitor-status {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($visit->status)) }}</span>
                                    </td>
                                    <td data-label="Action" class="visitor-action-cell">
                                        @if ($visit->registered_by === auth()->id() && in_array($visit->status, ['expected', 'pending_confirmation'], true))
                                            <form method="POST" action="{{ route('visitor.cancel', $visit) }}" class="visitor-cancel-form" onsubmit="return confirm('Cancel this visit?')">
                                                @csrf
                                                <input name="reason" class="form-control input-sm" placeholder="Cancellation reason" required maxlength="2000" aria-label="Cancellation reason">
                                                <button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-times"></i> Cancel</button>
                                            </form>
                                        @else
                                            <span class="visitor-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="visitor-empty-state">
                                            <span><i class="fa fa-calendar-o"></i></span>
                                            <strong>No visits found</strong>
                                            <p>Try changing your search or filters, or create a new invitation.</p>
                                            @if (auth()->user()->employee)
                                                <a class="btn btn-success btn-sm" href="{{ route('visitor.create') }}"><i class="fa fa-plus"></i> New invitation</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="visitor-pagination">{{ $visits->links() }}</div>
            </div>
        </section>
    </div>
@endsection

@section('content-script')
    <style>
        .visitor-list-page {
            --visitor-ink: #2a3f54;
            --visitor-muted: #73879c;
            --visitor-line: #e6eaed;
            --visitor-surface: #fff;
            padding-bottom: 20px;
        }

        .visitor-list-page .visitor-flash {
            border-radius: 6px;
        }

        .visitor-page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin: 4px 0 18px;
        }

        .visitor-page-heading h1 {
            margin: 0;
            color: var(--visitor-ink);
            font-size: 24px;
            font-weight: 600;
        }

        .visitor-page-heading p {
            margin: 5px 0 0;
        }

        .visitor-eyebrow {
            margin: 0 0 4px !important;
            color: #1f6f8b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .visitor-muted {
            color: var(--visitor-muted);
        }

        .visitor-heading-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .visitor-heading-actions .btn {
            margin: 0;
        }

        .visitor-created-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 20px;
            padding: 18px 22px;
            border: 1px solid #d5e9dd;
            border-left: 4px solid #26b99a;
            border-radius: 6px;
            background: #f7fcf9;
        }

        .visitor-created-copy,
        .visitor-created-qr {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .visitor-created-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 50%;
            background: #e3f4ed;
            color: #16856f;
            font-size: 18px;
        }

        .visitor-created-card h3 {
            margin: 0;
            color: var(--visitor-ink);
            font-size: 20px;
            font-weight: 700;
        }

        .visitor-created-card .visitor-muted {
            margin: 4px 0 0;
        }

        .visitor-created-qr #visitor-qr-code {
            display: block;
            width: 160px;
            height: 160px;
            padding: 5px;
            border: 1px solid var(--visitor-line);
            border-radius: 5px;
            background: #fff;
        }

        .visitor-created-qr #visitor-qr-code svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .visitor-token-wrap {
            display: grid;
            gap: 7px;
            min-width: 0;
        }

        .visitor-token-wrap code {
            max-width: 360px;
            overflow-wrap: anywhere;
            color: var(--visitor-ink);
        }

        .visitor-token-wrap .btn {
            justify-self: start;
            margin: 0;
        }

        .visitor-copy-status {
            color: #16856f;
            font-size: 12px;
        }

        .visitor-filter-card {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) minmax(0, 1.4fr);
            gap: 22px;
            margin-bottom: 18px;
            padding: 18px;
            border: 1px solid var(--visitor-line);
            border-radius: 6px;
            background: var(--visitor-surface);
            box-shadow: 0 2px 8px rgba(42, 63, 84, .04);
        }

        .visitor-search-form label,
        .visitor-filter-field label {
            display: block;
            margin-bottom: 6px;
            color: var(--visitor-ink);
            font-size: 12px;
            font-weight: 600;
        }

        .visitor-search-form .input-group-addon {
            border-color: #dfe3e7;
            background: #f7f9fa;
            color: var(--visitor-muted);
        }

        .visitor-search-form .form-control,
        .visitor-filter-field .form-control {
            height: 36px;
            border-color: #dfe3e7;
            box-shadow: none;
        }

        .visitor-search-form .form-control:focus,
        .visitor-filter-field .form-control:focus {
            border-color: #1f6f8b;
            box-shadow: 0 0 0 2px rgba(31, 111, 139, .1);
        }

        .visitor-search-form .btn,
        .visitor-filter-actions .btn {
            height: 36px;
            margin: 0;
        }

        .visitor-clear-search {
            display: inline-block;
            margin-top: 6px;
            font-size: 12px;
        }

        .visitor-filter-form {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }

        .visitor-filter-field {
            flex: 1 1 130px;
            min-width: 0;
        }

        .visitor-filter-actions {
            display: flex;
            gap: 6px;
        }

        .visitor-table-panel {
            border: 1px solid var(--visitor-line);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(42, 63, 84, .04);
        }

        .visitor-table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 12px;
        }

        .visitor-table-heading h2 {
            float: none;
            margin: 0 0 5px;
            color: var(--visitor-ink);
            font-size: 17px;
            font-weight: 600;
        }

        .visitor-table {
            margin-bottom: 0;
        }

        .visitor-table > thead > tr > th {
            padding: 11px 12px;
            border-bottom: 1px solid #e3e7ea;
            background: #f7f9fa;
            color: var(--visitor-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .visitor-table > tbody > tr > td {
            padding: 13px 12px;
            border-top: 1px solid #edf0f2;
            color: #434a54;
            vertical-align: middle;
        }

        .visitor-table > tbody > tr:hover {
            background: #fbfcfd;
        }

        .visitor-code {
            display: inline-block;
            color: #1f6f8b;
            font-family: Consolas, monospace;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .visitor-table-meta {
            display: block;
            margin-top: 3px;
            color: var(--visitor-muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .visitor-status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .visitor-status-success { background: #e8f6f1; color: #16856f; }
        .visitor-status-warning { background: #fff5df; color: #9a6b0b; }
        .visitor-status-muted { background: #eef1f4; color: #6b7785; }
        .visitor-status-info { background: #e8f2f8; color: #287ba3; }
        .visitor-status-complete { background: #e9edf8; color: #5869a8; }

        .visitor-action-column {
            width: 230px;
        }

        .visitor-cancel-form {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 210px;
        }

        .visitor-cancel-form .form-control {
            min-width: 0;
            height: 30px;
            box-shadow: none;
        }

        .visitor-cancel-form .btn {
            flex: 0 0 auto;
            margin: 0;
        }

        .visitor-empty-state {
            padding: 42px 15px;
            color: var(--visitor-muted);
            text-align: center;
        }

        .visitor-empty-state > span {
            display: block;
            margin-bottom: 10px;
            color: #9cabb8;
            font-size: 30px;
        }

        .visitor-empty-state strong {
            display: block;
            color: var(--visitor-ink);
            font-size: 16px;
        }

        .visitor-empty-state p {
            margin: 5px 0 14px;
        }

        .visitor-empty-state .btn {
            margin: 0;
        }

        .visitor-pagination {
            padding-top: 14px;
        }

        .visitor-pagination .pagination {
            margin: 0;
        }

        @media (max-width: 991px) {
            .visitor-filter-card {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        @media (max-width: 767px) {
            .visitor-page-heading,
            .visitor-created-card {
                align-items: stretch;
                flex-direction: column;
            }

            .visitor-page-heading {
                gap: 12px;
            }

            .visitor-heading-actions .btn {
                flex: 1;
            }

            .visitor-created-qr {
                align-items: flex-start;
            }

            .visitor-filter-form {
                align-items: stretch;
            }

            .visitor-filter-field {
                flex-basis: calc(50% - 6px);
            }

            .visitor-filter-actions {
                width: 100%;
            }

            .visitor-filter-actions .btn {
                flex: 1;
            }

            .visitor-table-responsive,
            .visitor-table,
            .visitor-table tbody,
            .visitor-table tr,
            .visitor-table td {
                display: block;
                width: 100%;
            }

            .visitor-table thead {
                display: none;
            }

            .visitor-table > tbody > tr {
                margin-bottom: 12px;
                border: 1px solid var(--visitor-line);
                border-radius: 6px;
                background: #fff;
            }

            .visitor-table > tbody > tr > td {
                display: grid;
                grid-template-columns: minmax(96px, 30%) minmax(0, 1fr);
                gap: 10px;
                padding: 10px 12px;
                border-top: 1px solid #edf0f2;
                text-align: left;
            }

            .visitor-table > tbody > tr > td:first-child {
                border-top: 0;
            }

            .visitor-table > tbody > tr > td:before {
                content: attr(data-label);
                color: var(--visitor-muted);
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .03em;
                text-transform: uppercase;
            }

            .visitor-table > tbody > tr > td[colspan]:before {
                display: none;
            }

            .visitor-action-column {
                width: auto;
            }

            .visitor-cancel-form {
                min-width: 0;
            }

            .visitor-cancel-form .form-control {
                flex: 1;
            }

            .visitor-pagination {
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            .visitor-list-page {
                padding-right: 8px;
                padding-left: 8px;
            }

            .visitor-page-heading h1 {
                font-size: 21px;
            }

            .visitor-filter-card {
                padding: 13px;
            }

            .visitor-created-qr {
                gap: 10px;
            }

            .visitor-created-qr #visitor-qr-code {
                width: 136px;
                height: 136px;
            }

            .visitor-token-wrap code {
                font-size: 11px;
            }

            .visitor-table > tbody > tr > td {
                grid-template-columns: 88px minmax(0, 1fr);
                gap: 7px;
                padding: 9px;
            }

            .visitor-cancel-form {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
    <script src="{{ asset('plugins/html5-qrcode/html5-qrcode.min.js') }}"></script>
    <script>
        $(function () {
            var qrContainer = document.getElementById('visitor-qr-code');
            if (qrContainer) {
                var library = window.__Html5QrcodeLibrary__;
                var writer = library && library.ZXing && library.ZXing.BrowserQRCodeSvgWriter;
                if (writer) {
                    var svg = new writer().write(qrContainer.dataset.token, 220, 220);
                    svg.setAttribute('viewBox', '0 0 ' + svg.getAttribute('width') + ' ' + svg.getAttribute('height'));
                    svg.setAttribute('shape-rendering', 'crispEdges');
                    qrContainer.appendChild(svg);
                } else {
                    qrContainer.textContent = 'QR code could not be generated.';
                }
            }

            $('#copy-visitor-token').on('click', function () {
                var button = this;
                var token = document.getElementById('new-visitor-token').textContent.trim();
                var $status = $('#copy-visitor-token-status');

                function copyFallback() {
                    var input = document.createElement('textarea');
                    input.value = token;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    var copied = document.execCommand('copy');
                    document.body.removeChild(input);
                    $(button).html(copied ? '<i class="fa fa-check"></i> Copied' : '<i class="fa fa-copy"></i> Copy token');
                    $status.text(copied ? ' Token copied.' : ' Could not copy token.');
                }

                if (!navigator.clipboard || !navigator.clipboard.writeText) {
                    copyFallback();
                    return;
                }

                navigator.clipboard.writeText(token).then(function () {
                    $(button).html('<i class="fa fa-check"></i> Copied');
                    $status.text(' Token copied.');
                }).catch(copyFallback);
            });
        });
    </script>
@endsection

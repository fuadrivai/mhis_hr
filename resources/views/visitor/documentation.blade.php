@extends('layouts.main-layout')

@section('content-class')
    <style>
        .visitor-docs {
            --docs-ink: #20344a;
            --docs-muted: #63778b;
            --docs-line: #e4ebf1;
            --docs-blue: #2874a6;
            --docs-teal: #138b83;
            --docs-surface: #fff;
            padding-bottom: 30px;
            color: var(--docs-ink);
        }

        .visitor-docs * {
            box-sizing: border-box;
        }

        .visitor-docs-hero {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(260px, .8fr);
            gap: 30px;
            overflow: hidden;
            margin: 8px 0 24px;
            padding: 38px 42px;
            border-radius: 18px;
            background:
                radial-gradient(circle at 82% 20%, rgba(86, 207, 190, .26), transparent 30%),
                linear-gradient(120deg, #173451 0%, #205b79 58%, #168b85 100%);
            box-shadow: 0 18px 42px rgba(27, 65, 91, .18);
            color: #fff;
        }

        .visitor-docs-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 13px;
            color: #b6e9e2;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .visitor-docs-hero h1 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 34px;
            font-weight: 700;
            line-height: 1.2;
        }

        .visitor-docs-hero p {
            max-width: 650px;
            margin: 0;
            color: rgba(255, 255, 255, .82);
            font-size: 15px;
            line-height: 1.75;
        }

        .visitor-docs-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 23px;
        }

        .visitor-docs-actions .btn {
            padding: 9px 14px;
            border: 0;
            border-radius: 7px;
            font-weight: 600;
        }

        .visitor-docs-actions .btn-primary {
            background: #fff;
            color: #205b79;
        }

        .visitor-docs-actions .btn-default {
            border: 1px solid rgba(255, 255, 255, .4);
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .visitor-docs-hero-art {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 190px;
        }

        .visitor-docs-orbit {
            position: relative;
            display: grid;
            width: 184px;
            height: 184px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            box-shadow: 0 0 0 20px rgba(255, 255, 255, .035), 0 0 0 42px rgba(255, 255, 255, .025);
        }

        .visitor-docs-orbit:before,
        .visitor-docs-orbit:after {
            position: absolute;
            width: 45px;
            height: 45px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 13px;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            content: "\f00c";
            font-family: FontAwesome;
            font-size: 18px;
            line-height: 43px;
            text-align: center;
        }

        .visitor-docs-orbit:before {
            top: 2px;
            right: 3px;
        }

        .visitor-docs-orbit:after {
            bottom: 4px;
            left: 0;
            content: "\f041";
        }

        .visitor-docs-orbit .fa {
            color: #fff;
            font-size: 67px;
            text-shadow: 0 8px 18px rgba(0, 0, 0, .18);
        }

        .visitor-docs-flow-label {
            position: absolute;
            right: -52px;
            bottom: -19px;
            width: 226px;
            padding: 10px 13px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 9px;
            background: rgba(17, 47, 68, .68);
            color: #fff;
            text-align: center;
        }

        .visitor-docs-flow-label span {
            display: block;
            margin-bottom: 3px;
            color: #a9ddd7;
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .visitor-docs-flow-label strong {
            font-size: 12px;
        }

        .visitor-docs-layout {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            gap: 22px;
            align-items: start;
        }

        .visitor-docs-nav,
        .visitor-docs-card {
            border: 1px solid var(--docs-line);
            border-radius: 12px;
            background: var(--docs-surface);
            box-shadow: 0 5px 18px rgba(32, 52, 74, .045);
        }

        .visitor-docs-nav {
            position: sticky;
            top: 18px;
            padding: 15px 11px;
        }

        .visitor-docs-nav-title {
            margin: 2px 9px 10px;
            color: #8a9aab;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .visitor-docs-nav a {
            display: block;
            margin: 2px 0;
            padding: 8px 10px;
            border-radius: 6px;
            color: #52677d;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .visitor-docs-nav a:hover,
        .visitor-docs-nav a:focus {
            background: #edf6f8;
            color: var(--docs-blue);
        }

        .visitor-docs-content {
            min-width: 0;
        }

        .visitor-docs-card {
            margin-bottom: 18px;
            padding: 24px;
            scroll-margin-top: 18px;
        }

        .visitor-docs-section-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 17px;
        }

        .visitor-docs-icon {
            display: grid;
            flex: 0 0 40px;
            width: 40px;
            height: 40px;
            place-items: center;
            border-radius: 11px;
            background: #eaf4f8;
            color: var(--docs-blue);
            font-size: 17px;
        }

        .visitor-docs-section-heading h2 {
            margin: 1px 0 5px;
            color: var(--docs-ink);
            font-size: 20px;
            font-weight: 700;
        }

        .visitor-docs-section-heading p {
            margin: 0;
            color: var(--docs-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .visitor-docs-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .visitor-docs-step {
            position: relative;
            min-height: 142px;
            padding: 16px 14px;
            border: 1px solid var(--docs-line);
            border-radius: 9px;
            background: #fbfcfd;
        }

        .visitor-docs-step-number {
            display: grid;
            width: 27px;
            height: 27px;
            margin-bottom: 12px;
            place-items: center;
            border-radius: 50%;
            background: #e7f4f2;
            color: var(--docs-teal);
            font-size: 12px;
            font-weight: 700;
        }

        .visitor-docs-step h3,
        .visitor-docs-mini-card h3 {
            margin: 0 0 7px;
            color: var(--docs-ink);
            font-size: 14px;
            font-weight: 700;
        }

        .visitor-docs-step p,
        .visitor-docs-mini-card p {
            margin: 0;
            color: var(--docs-muted);
            font-size: 12px;
            line-height: 1.65;
        }

        .visitor-docs-grid-2,
        .visitor-docs-grid-3 {
            display: grid;
            gap: 13px;
        }

        .visitor-docs-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .visitor-docs-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .visitor-docs-mini-card {
            padding: 17px;
            border: 1px solid var(--docs-line);
            border-radius: 9px;
            background: #fbfcfd;
        }

        .visitor-docs-mini-card .fa {
            margin-bottom: 11px;
            color: var(--docs-teal);
            font-size: 18px;
        }

        .visitor-docs-list {
            margin: 0;
            padding-left: 19px;
            color: var(--docs-muted);
            font-size: 13px;
            line-height: 1.85;
        }

        .visitor-docs-list strong {
            color: var(--docs-ink);
        }

        .visitor-docs-note {
            display: flex;
            gap: 11px;
            margin-top: 15px;
            padding: 13px 15px;
            border: 1px solid #d5e9f2;
            border-radius: 8px;
            background: #f1f8fb;
            color: #486679;
            font-size: 12px;
            line-height: 1.65;
        }

        .visitor-docs-note .fa {
            margin-top: 2px;
            color: var(--docs-blue);
        }

        .visitor-docs-status-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border: 1px solid var(--docs-line);
            border-radius: 8px;
        }

        .visitor-docs-status-table th,
        .visitor-docs-status-table td {
            padding: 11px 13px;
            border-bottom: 1px solid var(--docs-line);
            font-size: 12px;
            text-align: left;
            vertical-align: top;
        }

        .visitor-docs-status-table th {
            background: #f5f8fa;
            color: #52677d;
            font-size: 10px;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .visitor-docs-status-table tr:last-child td {
            border-bottom: 0;
        }

        .visitor-docs-status-table td:first-child {
            width: 190px;
            color: var(--docs-ink);
            font-weight: 700;
        }

        .visitor-docs-tag {
            display: inline-block;
            margin: 2px 4px 2px 0;
            padding: 5px 8px;
            border: 1px solid #dce8ee;
            border-radius: 20px;
            background: #f6fafb;
            color: #466276;
            font-size: 11px;
        }

        .visitor-docs-link {
            color: var(--docs-blue);
            font-weight: 700;
            text-decoration: none;
        }

        .visitor-docs-link:hover {
            color: #14577f;
            text-decoration: underline;
        }

        .visitor-docs-faq details {
            padding: 13px 0;
            border-bottom: 1px solid var(--docs-line);
        }

        .visitor-docs-faq details:last-child {
            border-bottom: 0;
        }

        .visitor-docs-faq summary {
            color: var(--docs-ink);
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
        }

        .visitor-docs-faq p {
            margin: 9px 0 0;
            color: var(--docs-muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .visitor-docs-footer {
            padding: 16px 4px 4px;
            color: #8a9aab;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 991px) {
            .visitor-docs-hero {
                grid-template-columns: minmax(0, 1fr) 220px;
                padding: 30px;
            }

            .visitor-docs-layout {
                grid-template-columns: 190px minmax(0, 1fr);
            }

            .visitor-docs-steps {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .visitor-docs-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .visitor-docs-hero {
                grid-template-columns: 1fr;
                padding: 25px 22px;
            }

            .visitor-docs-hero h1 {
                font-size: 28px;
            }

            .visitor-docs-hero-art {
                display: none;
            }

            .visitor-docs-layout {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .visitor-docs-nav {
                position: static;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 2px;
            }

            .visitor-docs-nav-title {
                grid-column: 1 / -1;
            }

            .visitor-docs-card {
                padding: 18px;
            }

            .visitor-docs-grid-2 {
                grid-template-columns: 1fr;
            }

            .visitor-docs-status-table td:first-child {
                width: auto;
            }
        }

        @media (max-width: 480px) {
            .visitor-docs-steps {
                grid-template-columns: 1fr;
            }

            .visitor-docs-status-table th,
            .visitor-docs-status-table td {
                padding: 9px 8px;
                font-size: 11px;
            }
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 visitor-docs">
        <header class="visitor-docs-hero">
            <div>
                <p class="visitor-docs-eyebrow"><i class="fa fa-book"></i> Help Center · Visitor Management</p>
                <h1>Visitor Management Guide</h1>
                <p>
                    Follow each step from creating an invitation to checking a visitor out. This guide covers
                    the workflow, visit statuses, and access available to each user role.
                </p>
                <div class="visitor-docs-actions">
                    @if ($hasEmployeeProfile)
                        <a class="btn btn-primary" href="{{ route('visitor.create') }}">
                            <i class="fa fa-plus"></i> Create an invitation
                        </a>
                    @endif
                    @if ($canUseDesk)
                        <a class="btn btn-default" href="{{ route('visitor.security-desk') }}">
                            <i class="fa fa-id-badge"></i> Open Visitor Desk
                        </a>
                    @endif
                    <a class="btn btn-default" href="{{ route('visitor.index') }}">
                        <i class="fa fa-list"></i> View visits
                    </a>
                </div>
            </div>
            <div class="visitor-docs-hero-art" aria-hidden="true">
                <div class="visitor-docs-orbit">
                    <i class="fa fa-id-card-o"></i>
                    <div class="visitor-docs-flow-label">
                        <span>Visit workflow</span>
                        <strong>Invite · Approve · Check in · Check out</strong>
                    </div>
                </div>
            </div>
        </header>

        <div class="visitor-docs-layout">
            <nav class="visitor-docs-nav" aria-label="Guide contents">
                <p class="visitor-docs-nav-title">Contents</p>
                <a href="#workflow">Quick workflow</a>
                <a href="#invitations">Create an invitation</a>
                <a href="#guest-links">Guest registration links</a>
                @if ($canUseDesk)
                    <a href="#walk-in">Walk-ins &amp; Visitor Desk</a>
                @endif
                <a href="#approvals-statuses">Approvals &amp; statuses</a>
                <a href="#records-reports">History &amp; reports</a>
                <a href="#user-access">User access</a>
                <a href="#security">Security tips</a>
            </nav>

            <main class="visitor-docs-content">
                <section id="workflow" class="visitor-docs-card">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-road"></i></span>
                        <div>
                            <h2>The visit workflow in four steps</h2>
                            <p>Follow these steps to make sure every visitor is properly recorded.</p>
                        </div>
                    </div>
                    <div class="visitor-docs-steps">
                        <article class="visitor-docs-step">
                            <span class="visitor-docs-step-number">01</span>
                            <h3>Register</h3>
                            <p>Create an invitation, share a registration link, or register a walk-in visitor.</p>
                        </article>
                        <article class="visitor-docs-step">
                            <span class="visitor-docs-step-number">02</span>
                            <h3>Approve</h3>
                            <p>The host or an authorized approver reviews requests that require approval.</p>
                        </article>
                        <article class="visitor-docs-step">
                            <span class="visitor-docs-step-number">03</span>
                            <h3>Check-in</h3>
                            <p>Desk staff find the QR code or visit code, verify the visitor's identity, and record arrival.</p>
                        </article>
                        <article class="visitor-docs-step">
                            <span class="visitor-docs-step-number">04</span>
                            <h3>Check-out</h3>
                            <p>Record the visitor's departure and badge return status, if a badge was issued.</p>
                        </article>
                    </div>
                </section>

                <section id="invitations" class="visitor-docs-card">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-envelope-o"></i></span>
                        <div>
                            <h2>Create an invitation</h2>
                            <p>Available to employees with an employee profile in the system.</p>
                        </div>
                    </div>
                    <div class="visitor-docs-grid-2">
                        <div class="visitor-docs-mini-card">
                            <i class="fa fa-pencil-square-o"></i>
                            <h3>Enter visit details</h3>
                            <ul class="visitor-docs-list">
                                <li>Visitor name and email are required; company and phone can also be added.</li>
                                <li>Select the host, branch or meeting location, visit date, and time.</li>
                                <li>Add a category, purpose, notes, or an attachment to provide more context.</li>
                                <li>If the category requires additional information, enter the required notes.</li>
                            </ul>
                        </div>
                        <div class="visitor-docs-mini-card">
                            <i class="fa fa-qrcode"></i>
                            <h3>Send and track</h3>
                            <ul class="visitor-docs-list">
                                <li>Select <strong>Create Invitation</strong> to save the invitation.</li>
                                <li>The QR code and visit token are shown after creation; store the token securely.</li>
                                <li>Invitations that do not require approval can be sent to the visitor immediately.</li>
                                <li>If approval is required, the invitation is sent after the request is approved.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="visitor-docs-note">
                        <i class="fa fa-info-circle"></i>
                        <span>Approval is required if either the category <em>or</em> the visit purpose is marked “Approval required”. Track the request on <strong>My Invitations / Visits</strong>.</span>
                    </div>
                </section>

                <section id="guest-links" class="visitor-docs-card">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-link"></i></span>
                        <div>
                            <h2>Guest registration links</h2>
                            <p>Useful for events or groups of visitors who need to enter their own details.</p>
                        </div>
                    </div>
                    <div class="visitor-docs-grid-3">
                        <article class="visitor-docs-mini-card">
                            <i class="fa fa-calendar-check-o"></i>
                            <h3>1. Set up the event</h3>
                            <p>Open <strong>Guest Registration Links → Create Links</strong>. Enter the event name, host, branch, category, purpose, date, and times.</p>
                        </article>
                        <article class="visitor-docs-mini-card">
                            <i class="fa fa-paper-plane-o"></i>
                            <h3>2. Share the links</h3>
                            <p>Enter recipient email addresses to send links, or create links to share yourself. Set an expiry date if needed.</p>
                        </article>
                        <article class="visitor-docs-mini-card">
                            <i class="fa fa-users"></i>
                            <h3>3. Track registrations</h3>
                            <p>Visitors enter their name and contact details. Each link can be used once; check registration status and attendees in the batch details.</p>
                        </article>
                    </div>
                    <div class="visitor-docs-note">
                        <i class="fa fa-shield"></i>
                        <span>Do not send private links to unintended recipients. Unused links can be revoked from the batch details page.</span>
                    </div>
                </section>

                @if ($canUseDesk)
                    <section id="walk-in" class="visitor-docs-card">
                        <div class="visitor-docs-section-heading">
                            <span class="visitor-docs-icon"><i class="fa fa-id-badge"></i></span>
                            <div>
                                <h2>Walk-ins &amp; Unified Visitor Desk</h2>
                                <p>Manage scheduled arrivals and visitors who arrive without an invitation.</p>
                            </div>
                        </div>
                        <div class="visitor-docs-grid-2">
                            <div class="visitor-docs-mini-card">
                                <i class="fa fa-search"></i>
                                <h3>Find a visit</h3>
                                <p>At the <strong>Unified Visitor Desk</strong>, scan a QR code or search by visit code, name, phone number, or badge number. Search only displays the visit; it does not change the status.</p>
                            </div>
                            <div class="visitor-docs-mini-card">
                                <i class="fa fa-check-square-o"></i>
                                <h3>Check in and check out</h3>
                                <p>The visit must be <strong>Expected</strong> and scheduled for today. Verify the visitor's identity before check-in. At check-out, confirm departure and record the badge status if applicable.</p>
                            </div>
                            <div class="visitor-docs-mini-card">
                                <i class="fa fa-user-plus"></i>
                                <h3>Register a walk-in</h3>
                                <p>Select the host and branch, then enter the visitor's name and at least an email address or phone number. Walk-ins always require host approval before check-in.</p>
                            </div>
                            <div class="visitor-docs-mini-card">
                                <i class="fa fa-users"></i>
                                <h3>Groups</h3>
                                <p>The walk-in form can include companions. Enter a name for each person; every group member requires host approval.</p>
                            </div>
                        </div>
                        <div class="visitor-docs-actions">
                            <a class="visitor-docs-link" href="{{ route('visitor.security-desk') }}">Open Unified Visitor Desk <i class="fa fa-arrow-right"></i></a>
                            <span aria-hidden="true">·</span>
                            <a class="visitor-docs-link" href="{{ route('visitor.public-walk-in') }}" target="_blank" rel="noopener">Open public walk-in form <i class="fa fa-external-link"></i></a>
                        </div>
                    </section>
                @endif

                    <section id="approvals-statuses" class="visitor-docs-card">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-tasks"></i></span>
                        <div>
                            <h2>Approvals and visit statuses</h2>
                            <p>Requests that need approval appear under <strong>Approvals</strong>. Choose approve or reject, and add notes if needed.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="visitor-docs-status-table">
                            <thead><tr><th>Status</th><th>Meaning</th></tr></thead>
                            <tbody>
                                <tr><td>Pending Confirmation</td><td>Waiting for a host or approver decision.</td></tr>
                                <tr><td>Expected</td><td>Approved and ready; check-in is available on the visit date.</td></tr>
                                <tr><td>Checked In</td><td>The visitor has arrived and has not checked out.</td></tr>
                                <tr><td>Completed</td><td>The visitor has checked out.</td></tr>
                                <tr><td>Rejected</td><td>The visit request was rejected.</td></tr>
                                <tr><td>Cancelled</td><td>The invitation was cancelled by its creator or an administrator.</td></tr>
                                <tr><td>No Show</td><td>The visitor did not attend the scheduled visit.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="records-reports" class="visitor-docs-card">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-bar-chart"></i></span>
                        <div>
                            <h2>Visit history and reports</h2>
                            <p>Find past records or download a visit summary for a selected date range.</p>
                        </div>
                    </div>
                    <div class="visitor-docs-grid-2">
                        <div class="visitor-docs-mini-card">
                            <i class="fa fa-filter"></i>
                            <h3>My Invitations / Visits</h3>
                            <p>Search by name, visit code, phone, email, or badge. Filter by status and date, or open <strong>Visit History</strong> to find visits that have ended.</p>
                        </div>
                        @if ($canManageVisits)
                            <div class="visitor-docs-mini-card">
                                <i class="fa fa-download"></i>
                                <h3>Reports</h3>
                                <p>Set a From/To date range, then select <strong>Export CSV</strong>. The report includes visit code, visitor, host, branch, category/purpose, check-in/out times, and status.</p>
                            </div>
                        @else
                            <div class="visitor-docs-mini-card">
                                <i class="fa fa-history"></i>
                                <h3>Visit History</h3>
                                <p>Use a date range to review completed, rejected, cancelled, or no-show visits.</p>
                            </div>
                        @endif
                    </div>
                    <div class="visitor-docs-actions">
                        <a class="visitor-docs-link" href="{{ route('visitor.index') }}">Open visit list <i class="fa fa-arrow-right"></i></a>
                        @if ($canManageVisits)
                            <span aria-hidden="true">·</span>
                            <a class="visitor-docs-link" href="{{ route('visitor.reports') }}">Open Reports <i class="fa fa-arrow-right"></i></a>
                        @endif
                    </div>
                </section>

                <section id="user-access" class="visitor-docs-card">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-key"></i></span>
                        <div>
                            <h2>Access by role</h2>
                            <p>Available menus depend on your role, branch, and employee profile.</p>
                        </div>
                    </div>
                    <div class="visitor-docs-grid-2">
                        <article class="visitor-docs-mini-card">
                            <h3>Employees and hosts</h3>
                            <p>Create invitations, view visits you created or were asked to monitor, and approve requests assigned to you.</p>
                        </article>
                        <article class="visitor-docs-mini-card">
                            <h3>Security, GA, and HRD</h3>
                            <p>Use the Visitor Desk to search, register walk-ins, and check visitors in or out. Operational data access follows your branch assignment.</p>
                        </article>
                        <article class="visitor-docs-mini-card">
                            <h3>Visitor Managers</h3>
                            <p>Manage visitor records and reports within the assigned branch scope.</p>
                        </article>
                        <article class="visitor-docs-mini-card">
                            <h3>Administrators</h3>
                            <p>Access all branches, manage visitor categories and purposes, and handle check-out corrections.</p>
                        </article>
                    </div>
                    <div class="visitor-docs-note">
                        <i class="fa fa-lock"></i>
                        <span>Visible records are limited by your access permissions. If a menu or visit is missing, contact an administrator to check your role and branch assignment.</span>
                    </div>
                    @if ($isAdministrator)
                        <div class="visitor-docs-actions">
                            <a class="visitor-docs-link" href="{{ route('visitor.settings') }}">Open Visitor Settings <i class="fa fa-arrow-right"></i></a>
                        </div>
                    @endif
                </section>

                <section id="security" class="visitor-docs-card visitor-docs-faq">
                    <div class="visitor-docs-section-heading">
                        <span class="visitor-docs-icon"><i class="fa fa-shield"></i></span>
                        <div>
                            <h2>Security tips and FAQs</h2>
                            <p>Simple steps to keep visitor data and visit records accurate.</p>
                        </div>
                    </div>
                    <details>
                        <summary>I cannot find a visitor in the Visitor Desk.</summary>
                        <p>Check the visit code, name, phone number, or badge number. Confirm that the visit is in a branch you can access and that the search term is correct.</p>
                    </details>
                    <details>
                        <summary>The QR code was scanned. Is the visitor automatically checked in?</summary>
                        <p>No. Scanning only finds and displays the visit. Staff must verify the visitor's identity and perform the check-in action separately.</p>
                    </details>
                    <details>
                        <summary>Why is check-in unavailable?</summary>
                        <p>The visit must have Expected status and be scheduled for today. Requests that are still awaiting approval cannot be checked in.</p>
                    </details>
                    <details>
                        <summary>What if I forgot to record a check-out?</summary>
                        <p>Contact HRD, GA, a Visitor Manager, or an administrator. A check-out correction requires a reason and is recorded in the activity log.</p>
                    </details>
                    <details>
                        <summary>How should I protect QR codes and visitor information?</summary>
                        <p>Store QR codes and tokens securely, share them only with relevant people, verify identity on arrival, and do not leave visitor information visible in public areas.</p>
                    </details>
                </section>

                <footer class="visitor-docs-footer">
                    This guide describes the Visitor Management workflows available in the application. Menu access depends on each user's role and branch assignment.
                </footer>
            </main>
        </div>
    </div>
@endsection

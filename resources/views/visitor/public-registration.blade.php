<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#800000">
    <title>Complete Visitor Registration | MHIS HUB</title>
    <style>
        :root {
            color-scheme: light;
            --maroon: #800000;
            --maroon-dark: #5f0000;
            --gold: #d4af37;
            --ink: #292524;
            --muted: #716b67;
            --line: #eee8e2;
            --surface: #fff;
            --page: #f7f4f0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: Arial, "Helvetica Neue", sans-serif;
            line-height: 1.55;
        }

        .top-accent { height: 6px; background: linear-gradient(90deg, var(--maroon), var(--gold), var(--maroon)); }
        .page-shell { width: min(100% - 32px, 760px); margin: 44px auto; }
        .brand { display: flex; justify-content: center; margin: 0 0 22px; padding: 10px 20px; border-radius: 12px; background: var(--maroon); }
        .brand-logo { display: block; width: min(100%, 320px); height: auto; object-fit: contain; }
        .card { overflow: hidden; border: 1px solid #e8e1da; border-radius: 16px; background: var(--surface); box-shadow: 0 18px 50px rgba(55, 37, 24, .08); }
        .card-heading { padding: 30px 36px 24px; border-bottom: 1px solid var(--line); }
        .eyebrow { margin: 0 0 8px; color: #947a36; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--maroon); font-size: clamp(24px, 5vw, 32px); line-height: 1.2; }
        .intro { margin: 10px 0 0; color: var(--muted); }
        .card-body { padding: 28px 36px 34px; }
        .event-card { margin-bottom: 28px; padding: 20px; border: 1px solid var(--line); border-left: 4px solid var(--gold); border-radius: 10px; background: #fcfaf8; }
        .event-title { margin: 0 0 15px; color: var(--maroon); font-size: 18px; font-weight: 700; }
        .event-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 22px; }
        .event-item { min-width: 0; }
        .event-label { display: block; margin-bottom: 3px; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .event-value { color: var(--ink); font-size: 14px; font-weight: 600; overflow-wrap: anywhere; }
        .section-title { margin: 0 0 4px; font-size: 18px; }
        .section-help { margin: 0 0 20px; color: var(--muted); font-size: 14px; }
        .alert { margin-bottom: 18px; padding: 12px 15px; border: 1px solid #f1c5c5; border-radius: 8px; background: #fff5f5; color: #8f1d1d; }
        .alert ul { margin: 0; padding-left: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 16px; }
        .form-group { min-width: 0; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 7px; color: #45403d; font-size: 13px; font-weight: 700; }
        .required { color: var(--maroon); }
        input {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            border: 1px solid #dcd5cf;
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font: inherit;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128, 0, 0, .1); }
        .contact-note { margin: 18px 0; padding: 12px 14px; border-radius: 8px; background: #f8f5ef; color: #655b4c; font-size: 13px; }
        .submit-button {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            padding: 0 24px;
            border: 0;
            border-radius: 8px;
            background: var(--maroon);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            transition: background .15s, transform .15s;
        }
        .submit-button:hover { transform: translateY(-1px); background: var(--maroon-dark); }
        .footer { margin: 22px 0 0; color: var(--muted); font-size: 12px; text-align: center; }

        @media (max-width: 560px) {
            .page-shell { width: min(100% - 20px, 760px); margin: 22px auto; }
            .card-heading { padding: 24px 20px 20px; }
            .card-body { padding: 22px 20px 26px; }
            .event-card { padding: 16px; }
            .event-grid, .form-grid { grid-template-columns: 1fr; gap: 14px; }
            .form-group.full { grid-column: auto; }
            .submit-button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="top-accent"></div>
    <main class="page-shell">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/logo mh menyamping putih-01-01.png') }}" alt="Mutiara Harapan Islamic School">
        </div>

        <section class="card">
            <header class="card-heading">
                <p class="eyebrow">Visitor Management</p>
                <h1>Complete your registration</h1>
                <p class="intro">Your host has prepared this invitation. Confirm your contact details to complete your visitor registration.</p>
            </header>

            <div class="card-body">
                <section class="event-card" aria-label="Invitation details">
                    <h2 class="event-title">{{ $batch->name }}</h2>
                    <div class="event-grid">
                        <div class="event-item">
                            <span class="event-label">Visit date</span>
                            <span class="event-value">{{ $batch->visit_date->format('l, d M Y') }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Time</span>
                            <span class="event-value">{{ $batch->expected_arrival_at->format('H:i') }} - {{ $batch->expected_departure_at->format('H:i') }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Purpose</span>
                            <span class="event-value">{{ $batch->purpose->name }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Meeting location</span>
                            <span class="event-value">{{ $batch->branch->name }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Host</span>
                            <span class="event-value">{{ $batch->host->user?->name ?? 'School contact' }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Registration closes</span>
                            <span class="event-value">{{ $batch->expires_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </section>

                @if ($errors->any())
                    <div class="alert" role="alert">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <h2 class="section-title">Your contact information</h2>
                <p class="section-help">This link can only be used once. Please provide at least a phone number or email address.</p>
                <form method="POST" action="{{ route('visitor.public-registration.submit', ['token' => $token]) }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="full_name">Full name <span class="required">*</span></label>
                            <input id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="255" autocomplete="name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone number</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email">
                        </div>
                        <div class="form-group full">
                            <label for="company">Company / organization</label>
                            <input id="company" name="company" value="{{ old('company') }}" maxlength="255" autocomplete="organization">
                        </div>
                    </div>
                    <div class="contact-note">Your personal information will be shared with the host for this visit.</div>
                    <button class="submit-button" type="submit">Submit registration</button>
                </form>
            </div>
        </section>
        <p class="footer">Mutiara Harapan Islamic School · Secure visitor registration</p>
    </main>
</body>
</html>

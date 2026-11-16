<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#800000">
    <title>Registration Submitted | MHIS HUB</title>
    <style>
        :root {
            color-scheme: light;
            --maroon: #800000;
            --gold: #d4af37;
            --ink: #292524;
            --muted: #716b67;
            --line: #eee8e2;
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
        .page-shell { width: min(100% - 32px, 680px); margin: 44px auto; }
        .brand { display: flex; justify-content: center; margin: 0 0 22px; padding: 10px 20px; border-radius: 12px; background: var(--maroon); }
        .brand-logo { display: block; width: min(100%, 320px); height: auto; object-fit: contain; }
        .card { overflow: hidden; border: 1px solid #e8e1da; border-radius: 16px; background: #fff; box-shadow: 0 18px 50px rgba(55, 37, 24, .08); }
        .card-body { padding: 44px 40px 40px; text-align: center; }
        .status-icon {
            display: grid;
            width: 68px;
            height: 68px;
            margin: 0 auto 20px;
            place-items: center;
            border: 1px solid #e8dfce;
            border-radius: 50%;
            background: #faf7f0;
            color: var(--maroon);
        }
        .status-icon svg { width: 32px; height: 32px; }
        .eyebrow { margin: 0 0 7px; color: #947a36; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--maroon); font-size: clamp(25px, 5vw, 32px); line-height: 1.2; }
        .message { max-width: 470px; margin: 12px auto 28px; color: var(--muted); }
        .visit-code {
            max-width: 420px;
            margin: 0 auto;
            padding: 18px 20px;
            border: 1px solid var(--line);
            border-left: 4px solid var(--gold);
            border-radius: 10px;
            background: #fcfaf8;
        }
        .visit-code-label { display: block; margin-bottom: 5px; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .visit-code-value { display: block; color: var(--maroon); font-size: 25px; font-weight: 700; letter-spacing: .12em; overflow-wrap: anywhere; }
        .code-note { margin: 10px 0 0; color: var(--muted); font-size: 13px; }
        .footer { margin: 22px 0 0; color: var(--muted); font-size: 12px; text-align: center; }

        @media (max-width: 560px) {
            .page-shell { width: min(100% - 20px, 680px); margin: 24px auto; }
            .card-body { padding: 34px 20px 28px; }
            .visit-code { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="top-accent"></div>
    <main class="page-shell">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/logo mh menyamping putih-01-01.png') }}" alt="Mutiara Harapan Islamic School">
        </div>

        <section class="card" aria-labelledby="registration-title">
            <div class="card-body">
                <div class="status-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m5 12.5 4.2 4.2L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <p class="eyebrow">Visitor Management</p>
                <h1 id="registration-title">
                    {{ $visit->status === 'pending_confirmation' ? 'Registration received' : 'Registration complete' }}
                </h1>
                <p class="message">
                    @if ($visit->status === 'pending_confirmation')
                        Your information has been sent to the host for approval. You can use the visit code below for reference.
                    @else
                        Your visitor registration is complete. Please keep the visit code below for reference.
                    @endif
                </p>

                <div class="visit-code" role="status" aria-label="Your visit code">
                    <span class="visit-code-label">Visit code</span>
                    <strong class="visit-code-value">{{ $visit->visit_code }}</strong>
                </div>
                <p class="code-note">Please show or mention this code when you arrive.</p>
            </div>
        </section>
        <p class="footer">Mutiara Harapan Islamic School · Secure visitor registration</p>
    </main>
</body>
</html>

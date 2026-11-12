<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f3f4">
    <title>Account inactive | MHIS HUB</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #1d2b37;
            --muted: #78858f;
            --accent: #9f2344;
            --accent-dark: #811b37;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 28px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 12% 12%, rgba(159, 35, 68, .10), transparent 28%),
                radial-gradient(circle at 88% 90%, rgba(129, 27, 55, .07), transparent 30%),
                #f7f3f4;
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .status-card {
            display: grid;
            grid-template-columns: minmax(260px, .85fr) minmax(360px, 1.15fr);
            width: min(900px, 100%);
            min-height: 480px;
            overflow: hidden;
            border: 1px solid rgba(29, 43, 55, .06);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 30px 90px rgba(74, 24, 40, .15);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 34px clamp(28px, 4vw, 48px);
            isolation: isolate;
            background:
                radial-gradient(ellipse at 38% 35%, rgba(215, 72, 111, .24), transparent 53%),
                linear-gradient(145deg, #7d1b3b 0%, #861c3e 48%, #a52b4c 100%);
            color: #fff;
        }

        .brand-panel::before {
            position: absolute;
            z-index: -1;
            top: -170px;
            left: -180px;
            width: 500px;
            aspect-ratio: 1;
            border: 1px solid rgba(255, 213, 223, .18);
            border-radius: 50%;
            background: radial-gradient(circle at 34% 30%, rgba(255, 207, 220, .35), transparent 68%);
            box-shadow: 0 0 0 35px rgba(255, 255, 255, .025);
            content: "";
        }

        .brand-name {
            position: relative;
            z-index: 1;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .16em;
        }

        .brand-copy {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            display: grid;
            width: 116px;
            height: 116px;
            margin-bottom: 30px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .7);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(45, 7, 23, .22);
        }

        .brand-logo img {
            display: block;
            width: 88%;
            height: auto;
        }

        .brand-copy h1 {
            margin: 0 0 9px;
            color: #fff;
            font-size: clamp(26px, 3vw, 34px);
            font-weight: 700;
            letter-spacing: -.035em;
            line-height: 1.15;
        }

        .brand-copy p {
            margin: 0;
            color: #f0c4cf;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .content-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 54px clamp(34px, 6vw, 68px);
        }

        .content {
            width: 100%;
            max-width: 390px;
        }

        .status-icon {
            display: grid;
            width: 58px;
            height: 58px;
            margin-bottom: 26px;
            place-items: center;
            border: 1px solid #f3d9df;
            border-radius: 18px;
            background: #fff4f6;
            color: var(--accent);
        }

        .status-icon svg {
            width: 27px;
            height: 27px;
        }

        .eyebrow {
            margin: 0 0 12px;
            color: var(--accent);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        h2 {
            margin: 0 0 12px;
            color: var(--ink);
            font-size: clamp(27px, 3vw, 34px);
            font-weight: 700;
            letter-spacing: -.035em;
            line-height: 1.18;
        }

        .message {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.75;
        }

        .help-note {
            display: flex;
            gap: 12px;
            margin-top: 26px;
            padding: 15px 16px;
            border: 1px solid #e8ecee;
            border-radius: 12px;
            background: #f8fafb;
            color: #586772;
            font-size: 12px;
            line-height: 1.6;
        }

        .help-note svg {
            flex: 0 0 17px;
            width: 17px;
            height: 17px;
            margin-top: 1px;
            color: var(--accent);
        }

        .login-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 48px;
            margin-top: 28px;
            padding: 0 20px;
            border-radius: 9px;
            background: var(--accent);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .login-link:hover,
        .login-link:focus-visible {
            background: var(--accent-dark);
            box-shadow: 0 8px 18px rgba(129, 27, 55, .2);
            color: #fff;
        }

        .login-link:active {
            transform: translateY(1px);
        }

        .login-link:focus-visible {
            outline: 3px solid rgba(159, 35, 68, .28);
            outline-offset: 3px;
        }

        .copyright {
            margin: 30px 0 0;
            color: #9aa4aa;
            font-size: 11px;
        }

        @media (max-width: 700px) {
            body {
                padding: 18px;
            }

            .status-card {
                grid-template-columns: 1fr;
                width: min(480px, 100%);
                min-height: 0;
                border-radius: 19px;
            }

            .brand-panel {
                min-height: 235px;
                padding: 24px 28px;
            }

            .brand-logo {
                position: absolute;
                top: 50%;
                right: 28px;
                width: 82px;
                height: 82px;
                margin: 0;
                border-radius: 17px;
                transform: translateY(-50%);
            }

            .brand-copy {
                margin-top: 35px;
            }

            .brand-copy h1 {
                max-width: 230px;
                font-size: 26px;
            }

            .brand-copy p {
                font-size: 10px;
            }

            .content-panel {
                padding: 35px 28px 32px;
            }

            .status-icon {
                width: 52px;
                height: 52px;
                margin-bottom: 22px;
            }

            .copyright {
                margin-top: 24px;
            }
        }

        @media (max-width: 380px) {
            body {
                padding: 12px;
            }

            .brand-panel {
                padding-right: 20px;
                padding-left: 20px;
            }

            .brand-logo {
                right: 20px;
                width: 68px;
                height: 68px;
            }

            .content-panel {
                padding-right: 22px;
                padding-left: 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>
    <main class="status-card">
        <section class="brand-panel" aria-label="MHIS HUB">
            <span class="brand-name">MUTIARA HARAPAN</span>
            <div class="brand-copy">
                <div class="brand-logo">
                    <img src="/images/logo-mh.png" alt="Mutiara Harapan Islamic School">
                </div>
                <h1>People &amp; culture, connected.</h1>
                <p>MHIS HUB</p>
            </div>
        </section>

        <section class="content-panel" aria-labelledby="status-title">
            <div class="content">
                <div class="status-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7.5v5"></path>
                        <path d="M12 16.5h.01"></path>
                    </svg>
                </div>
                <p class="eyebrow">Account access</p>
                <h2 id="status-title">Your account is inactive</h2>
                <p class="message">
                    Your account is currently inactive. Please contact the administrator for assistance.
                </p>
                <div class="help-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M9.6 9a2.5 2.5 0 0 1 4.8 1c0 1.7-2.4 2-2.4 3.8"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                    <span>If you believe this is a mistake, please reach out to your administrator to restore access.</span>
                </div>
                <a class="login-link" href="{{ route('login-page') }}">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M5 12h14"></path>
                    </svg>
                    <span>Return to sign in</span>
                </a>
                <p class="copyright">&copy; {{ date('Y') }} Mutiara Harapan Islamic School</p>
            </div>
        </section>
    </main>
</body>

</html>

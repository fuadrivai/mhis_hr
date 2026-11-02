<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f3f4">

    <title>MHIS HUB</title>

    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
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

        body.login-page {
            min-height: 100vh;
            margin: 0;
            padding: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 12% 12%, rgba(159, 35, 68, .10), transparent 28%),
                radial-gradient(circle at 88% 90%, rgba(129, 27, 55, .07), transparent 30%),
                #f7f3f4;
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .login-card {
            display: grid;
            grid-template-columns: minmax(280px, .9fr) minmax(360px, 1.1fr);
            width: min(1000px, 100%);
            min-height: 560px;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(29, 43, 55, .06);
            border-radius: 24px;
            box-shadow: 0 30px 90px rgba(74, 24, 40, .15);
        }

        .login-brand {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 560px;
            overflow: hidden;
            padding: 34px clamp(28px, 4vw, 54px) 35px;
            isolation: isolate;
            background:
                radial-gradient(ellipse at 38% 35%, rgba(215, 72, 111, .24), transparent 53%),
                linear-gradient(145deg, #7d1b3b 0%, #861c3e 48%, #a52b4c 100%);
            color: #fff;
        }

        .login-brand::before {
            position: absolute;
            z-index: -1;
            top: -190px;
            left: -175px;
            width: min(590px, 90vw);
            aspect-ratio: 1;
            border: 1px solid rgba(255, 213, 223, .18);
            border-radius: 50%;
            background:
                radial-gradient(circle at 34% 30%, rgba(255, 207, 220, .42), rgba(225, 119, 148, .2) 22%, rgba(169, 44, 82, .08) 49%, transparent 72%);
            box-shadow:
                inset -28px -22px 65px rgba(40, 4, 23, .12),
                0 0 0 35px rgba(255, 255, 255, .025);
            content: "";
        }

        .login-brand::after {
            position: absolute;
            z-index: -1;
            inset: 0;
            background-image:
                radial-gradient(circle, rgba(255, 236, 241, .82) 1px, transparent 1.7px),
                radial-gradient(circle, rgba(255, 236, 241, .48) 1px, transparent 1.8px),
                radial-gradient(ellipse at 48% 35%, transparent 34%, rgba(255, 222, 230, .11) 34.2%, transparent 34.6%, transparent 40%, rgba(255, 222, 230, .08) 40.2%, transparent 40.6%);
            background-position: 12% 18%, 73% 27%, center;
            background-size: 113px 127px, 173px 181px, 100% 100%;
            content: "";
            pointer-events: none;
        }

        .brand-top,
        .brand-copy {
            position: relative;
            z-index: 1;
        }

        .brand-name {
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .16em;
        }

        .brand-logo {
            position: absolute;
            z-index: 1;
            top: 50%;
            left: 50%;
            display: grid;
            width: clamp(150px, 18vw, 205px);
            aspect-ratio: 1;
            place-items: center;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .7);
            border-radius: 25px;
            background: #fff;
            box-shadow: 0 22px 55px rgba(45, 7, 23, .25), 0 0 0 10px rgba(255, 255, 255, .08);
            transform: translate(-50%, -50%);
            transition: box-shadow .25s ease, transform .25s ease;
        }

        .brand-logo:hover {
            box-shadow: 0 26px 62px rgba(45, 7, 23, .32), 0 0 0 13px rgba(255, 255, 255, .11);
            transform: translate(-50%, -50%) scale(1.035);
        }

        .brand-logo img {
            display: block;
            width: 88%;
            height: auto;
        }

        .brand-copy {
            padding-top: 40px;
        }

        .brand-copy::before {
            display: block;
            width: 38px;
            height: 3px;
            margin-bottom: 20px;
            border-radius: 3px;
            background: #ee829b;
            box-shadow: 0 0 20px rgba(238, 130, 155, .6);
            content: "";
        }

        .brand-copy h1 {
            margin: 0 0 9px;
            color: #fff;
            font-size: clamp(28px, 3vw, 39px);
            font-weight: 700;
            letter-spacing: -.035em;
            line-height: 1.12;
        }

        .brand-copy p {
            margin: 0;
            color: #f0c4cf;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .login-form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 58px clamp(36px, 6vw, 74px);
            background: #fff;
        }

        .login-form-content {
            width: 100%;
            max-width: 380px;
        }

        .form-heading {
            margin-bottom: 34px;
        }

        .form-heading .eyebrow {
            margin: 0 0 12px;
            color: var(--accent);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .form-heading h2 {
            margin: 0 0 9px;
            color: var(--ink);
            font-size: clamp(25px, 3vw, 31px);
            font-weight: 700;
            letter-spacing: -.025em;
            line-height: 1.2;
        }

        .form-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .login-field {
            margin-bottom: 20px;
        }

        .login-field label {
            display: block;
            margin-bottom: 8px;
            color: #34434e;
            font-size: 12px;
            font-weight: 600;
        }

        .login-field .input-wrap {
            position: relative;
        }

        .login-field .input-wrap .fa {
            position: absolute;
            top: 50%;
            left: 14px;
            color: #97a2aa;
            font-size: 14px;
            transform: translateY(-50%);
        }

        .login-field input {
            display: block;
            width: 100%;
            height: 48px;
            padding: 0 14px 0 40px;
            border: 1px solid #dfe5e8;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font-size: 13px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .login-field input::placeholder {
            color: #a7b0b6;
        }

        .login-field input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(159, 35, 68, .11);
        }

        .login-field input.is-invalid {
            border-color: #d9534f;
        }

        .invalid-feedback {
            display: block;
            margin-top: 6px;
            color: #c33f3b;
            font-size: 12px;
        }

        .login-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            height: 48px;
            margin-top: 7px;
            border: 0;
            border-radius: 9px;
            background: var(--accent);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .login-submit:hover,
        .login-submit:focus {
            background: var(--accent-dark);
            color: #fff;
            box-shadow: 0 8px 18px rgba(129, 27, 55, .2);
        }

        .login-submit:active {
            transform: translateY(1px);
        }

        .login-error {
            margin: 14px 0 0;
            padding: 10px 12px;
            border: 1px solid #f1c8c6;
            border-radius: 8px;
            background: #fff5f4;
            color: #a93632;
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 767px) {
            body.login-page {
                padding: 20px;
            }

            .login-card {
                grid-template-columns: 1fr;
                width: min(480px, 100%);
                min-height: 0;
                border-radius: 18px;
            }

            .login-brand {
                min-height: 330px;
                padding: 24px 28px 25px;
            }

            .login-brand::before {
                top: -285px;
                left: -130px;
                width: 470px;
            }

            .login-brand::after {
                background-size: 91px 101px, 137px 143px, 100% 100%;
            }

            .brand-logo {
                width: 140px;
                border-radius: 21px;
            }

            .brand-copy {
                padding-top: 24px;
            }

            .brand-copy::before {
                width: 30px;
                margin-bottom: 13px;
            }

            .brand-copy h1 {
                font-size: 27px;
            }

            .brand-copy p {
                font-size: 10px;
            }

            .login-form-panel {
                padding: 38px 30px 42px;
            }

            .form-heading {
                margin-bottom: 27px;
            }
        }

        @media (max-width: 380px) {
            body.login-page {
                padding: 12px;
            }

            .login-brand {
                min-height: 310px;
                padding-right: 22px;
                padding-left: 22px;
            }

            .brand-logo {
                width: 125px;
            }

            .login-form-panel {
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

<body class="login-page">
    <main class="login-card">
        <section class="login-brand" aria-label="MHIS HUB">
            <div class="brand-top">
                <span class="brand-name"></span>
            </div>
            <div class="brand-logo">
                <img src="/images/logo-mh.png" alt="HUB logo">
            </div>
            <div class="brand-copy">
                <h1>Welcome Back!</h1>
                <p>MHIS HUB</p>
            </div>
        </section>

        <section class="login-form-panel" aria-labelledby="login-title">
            <div class="login-form-content">
                <header class="form-heading">
                    {{-- <p class="eyebrow">Welcome back</p> --}}
                    <h2 id="login-title">Sign in to your account</h2>
                    <p>Enter your email and password to continue.</p>
                </header>

                <form action="/login" method="post">
                    @csrf
                    <div class="login-field">
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <i class="fa fa-envelope-o" aria-hidden="true"></i>
                            <input id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                name="email" placeholder="name@email.com" autocomplete="username" required
                                aria-describedby="email-error">
                        </div>
                        @error('email')
                            <div class="invalid-feedback" id="email-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="login-field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                placeholder="Enter your password" autocomplete="current-password" required
                                aria-describedby="password-error">
                        </div>
                        @error('password')
                            <div class="invalid-feedback" id="password-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn login-submit">
                        <span>Sign in</span>
                    </button>
                    @if (session()->has('LoginError'))
                        <p class="login-error" role="alert">{{ session('LoginError') }}</p>
                    @endif
                </form>
            </div>
        </section>
    </main>
</body>

</html>

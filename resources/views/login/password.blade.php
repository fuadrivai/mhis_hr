<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f3f4">
    <title>{{ $mode === 'request' ? 'Forgot password' : 'Reset password' }} | MHIS HUB</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: grid;
            place-items: center;
            background: #f7f3f4;
            color: #1d2b37;
            font-family: "Segoe UI", Arial, sans-serif;
        }
        main {
            width: min(100%, 440px);
            padding: 40px;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 20px 60px rgba(74, 24, 40, .12);
        }
        h1 { margin: 0 0 10px; font-size: 27px; }
        p { color: #78858f; line-height: 1.6; }
        label { display: block; margin: 20px 0 7px; font-weight: 600; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5d9dc;
            border-radius: 8px;
            font: inherit;
        }
        .password-field { position: relative; }
        .password-field input { padding-right: 48px; }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            display: grid;
            width: 36px;
            height: 36px;
            margin: 0;
            padding: 7px;
            place-items: center;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #78858f;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .password-toggle:hover,
        .password-toggle:focus-visible {
            background: #f7f3f4;
            color: #9f2344;
            outline: 2px solid #9f2344;
            outline-offset: 1px;
        }
        .password-toggle svg { width: 20px; height: 20px; }
        .password-toggle .eye-off { display: none; }
        .password-toggle[aria-pressed="true"] .eye { display: none; }
        .password-toggle[aria-pressed="true"] .eye-off { display: block; }
        button {
            width: 100%;
            margin-top: 24px;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #9f2344;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        a { color: #9f2344; }
        .message { margin: 16px 0; color: #276749; }
        .error { margin-top: 6px; color: #b42318; font-size: 13px; }
        .back-link { display: inline-block; margin-top: 20px; }
        @media (max-width: 480px) { main { padding: 28px 22px; } }
    </style>
</head>

<body>
    <main>
        <h1>{{ $mode === 'request' ? 'Forgot your password?' : 'Create a new password' }}</h1>
        <p>
            {{ $mode === 'request'
                ? 'Enter your account email and we will send you a password reset link.'
                : 'Choose a new password for your account.' }}
        </p>

        @if (session('status'))
            <p class="message" role="status">{{ session('status') }}</p>
        @endif

        @if ($mode === 'request')
            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
                <button type="submit">Send reset link</button>
            </form>
        @else
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
                <label for="password">New password</label>
                <div class="password-field">
                    <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                    <button class="password-toggle" type="button" aria-label="Show new password" aria-pressed="false" aria-controls="password">
                        <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m3 3 18 18"></path>
                            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                            <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.9"></path>
                            <path d="M6.6 6.6C3.6 8.5 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4-.8"></path>
                        </svg>
                    </button>
                </div>
                @error('password') <div class="error">{{ $message }}</div> @enderror
                <label for="password_confirmation">Confirm new password</label>
                <div class="password-field">
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                    <button class="password-toggle" type="button" aria-label="Show password confirmation" aria-pressed="false" aria-controls="password_confirmation">
                        <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m3 3 18 18"></path>
                            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                            <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.9"></path>
                            <path d="M6.6 6.6C3.6 8.5 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4-.8"></path>
                        </svg>
                    </button>
                </div>
                <button type="submit">Reset password</button>
            </form>
        @endif

        <a class="back-link" href="{{ route('login-page') }}">Back to sign in</a>
    </main>
    <script>
        document.querySelectorAll('.password-toggle').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const password = document.getElementById(toggle.getAttribute('aria-controls'));
                const isVisible = password.type === 'text';

                password.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!isVisible));
                toggle.setAttribute(
                    'aria-label',
                    isVisible
                        ? (password.id === 'password' ? 'Show new password' : 'Show password confirmation')
                        : (password.id === 'password' ? 'Hide new password' : 'Hide password confirmation')
                );
            });
        });
    </script>
</body>

</html>

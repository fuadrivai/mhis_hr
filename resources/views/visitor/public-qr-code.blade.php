<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#800000">
    <title>Visitor QR Code | MHIS HUB</title>
    <style>
        :root {
            color-scheme: light;
            --maroon: #800000;
            --gold: #d4af37;
            --ink: #292524;
            --muted: #716b67;
            --page: #f7f4f0;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--page); color: var(--ink); font-family: Arial, "Helvetica Neue", sans-serif; line-height: 1.55; }
        .top-accent { height: 6px; background: linear-gradient(90deg, var(--maroon), var(--gold), var(--maroon)); }
        .page-shell { width: min(100% - 28px, 480px); margin: 34px auto; }
        .brand { display: flex; justify-content: center; margin-bottom: 20px; padding: 10px 20px; border-radius: 12px; background: var(--maroon); }
        .brand-logo { display: block; width: min(100%, 300px); height: auto; object-fit: contain; }
        .card { padding: 30px 24px; border: 1px solid #e8e1da; border-radius: 16px; background: #fff; box-shadow: 0 18px 50px rgba(55, 37, 24, .08); text-align: center; }
        .eyebrow { margin: 0 0 7px; color: #947a36; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--maroon); font-size: 26px; }
        .intro { margin: 8px 0 20px; color: var(--muted); }
        #visitor-qr-code { display: inline-flex; min-width: 240px; min-height: 240px; align-items: center; justify-content: center; padding: 10px; border: 1px solid #eee8e2; border-radius: 10px; background: #fff; }
        #visitor-qr-code svg { display: block; width: 220px; height: 220px; }
        #qr-error { color: #8f1d1d; }
        .visit-code { margin: 18px 0 0; color: var(--muted); font-size: 12px; }
        .visit-code strong { display: block; margin-top: 3px; color: var(--maroon); font-size: 24px; letter-spacing: .12em; }
        .footer { margin: 20px 0 0; color: var(--muted); font-size: 12px; text-align: center; }
        @media (max-width: 480px) { .page-shell { margin: 20px auto; } .card { padding: 24px 16px; } }
    </style>
</head>
<body>
    <div class="top-accent"></div>
    <main class="page-shell">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/logo mh menyamping putih-01-01.png') }}" alt="Mutiara Harapan Islamic School">
        </div>
        <section class="card" aria-labelledby="qr-title">
            <p class="eyebrow">Visitor Management</p>
            <h1 id="qr-title">Your visitor QR code</h1>
            <p class="intro">Show this QR code or provide the visit code at the security desk.</p>
            <div id="visitor-qr-code" data-token="{{ $token }}" role="img" aria-label="Visitor QR code">
                <span id="qr-error" hidden>QR code could not be generated. Please use the visit code below.</span>
            </div>
            <p class="visit-code">Visit code<strong>{{ $visit->visit_code }}</strong></p>
        </section>
        <p class="footer">Mutiara Harapan Islamic School · Secure visitor registration</p>
    </main>
    <script src="{{ asset('plugins/html5-qrcode/html5-qrcode.min.js') }}"></script>
    <script>
        (function() {
            var container = document.getElementById('visitor-qr-code');
            var library = window.__Html5QrcodeLibrary__;
            var writer = library && library.ZXing && library.ZXing.BrowserQRCodeSvgWriter;
            if (!writer) {
                document.getElementById('qr-error').hidden = false;
                return;
            }

            var svg = new writer().write(container.dataset.token, 220, 220);
            svg.setAttribute('viewBox', '0 0 ' + svg.getAttribute('width') + ' ' + svg.getAttribute('height'));
            svg.setAttribute('shape-rendering', 'crispEdges');
            svg.setAttribute('aria-hidden', 'true');
            container.insertBefore(svg, document.getElementById('qr-error'));
        })();
    </script>
</body>
</html>

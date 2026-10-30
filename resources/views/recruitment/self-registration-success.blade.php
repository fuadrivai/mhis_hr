<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#990000">
    <title>Application Received | MHIS</title>
    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 24px;
            color: #354052;
            background: #f3f5f8;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .success-card {
            width: 100%;
            max-width: 700px;
            overflow: hidden;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 18px 55px rgba(37, 48, 65, .12);
            text-align: center;
        }

        .success-hero {
            padding: 34px 24px 28px;
            background: linear-gradient(135deg, #780000, #a90000 58%, #bd1010);
        }

        .success-hero img {
            width: min(100%, 280px);
            height: 68px;
            object-fit: contain;
        }

        .success-content {
            padding: 42px 32px 48px;
        }

        .success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            color: #fff;
            background: #26b99a;
            border-radius: 50%;
            font-size: 34px;
        }

        .success-content h1 {
            margin: 22px 0 12px;
            font-size: 30px;
            font-weight: 700;
        }

        .success-content p {
            max-width: 490px;
            margin: 0 auto;
            color: #788394;
            font-size: 16px;
            line-height: 1.7;
        }

        @media (max-width: 600px) {
            .success-content {
                padding: 34px 22px 38px;
            }

            .success-content h1 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <main class="success-card">
        <header class="success-hero">
            <img src="{{ asset('images/logo mh menyamping putih-01-01.png') }}"
                alt="Mutiara Harapan Islamic School">
        </header>
        <section class="success-content">
            <span class="success-icon" aria-hidden="true"><i class="fa fa-check"></i></span>
            <h1>Application Submitted</h1>
            <p>Thank you for applying to MHIS. Our recruitment team has received your application and will review it
                soon.</p>
        </section>
    </main>
</body>

</html>

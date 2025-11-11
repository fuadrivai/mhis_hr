<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MHIS || HRIS</title>

    <!-- Bootstrap -->
    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="/plugins/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="/plugins/animate.css/animate.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="/build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
    <div>
      <a class="hiddenanchor" id="signup"></a>
      <a class="hiddenanchor" id="signin"></a>

      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form action="/login" method="post">
                @csrf
              <img src="/images/logo-mh.png" alt="" width="100">
              <hr>
              <h1><b>Login Form</b></h1>
              <div>
                <input type="text" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" name="email" placeholder="email@example.com" required="" />
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
              </div>
              <div>
                <input type="password"  class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password">
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
              </div>
              <div>
                <button type="submit" class="btn btn-success btn-md btn-block">Log in</button>
                @if (session()->has('LoginError'))
                    <p class="text-danger"> {{session('LoginError') }}</p>
                @endif
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                  <p>©2024 All Rights Reserved</p>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </body>
</html>

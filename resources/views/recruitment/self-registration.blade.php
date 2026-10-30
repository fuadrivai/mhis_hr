<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#990000">
    <title>Candidate Registration | MHIS</title>
    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        :root {
            --mhis-red: #990000;
            --mhis-red-dark: #780000;
            --mhis-ink: #354052;
            --mhis-muted: #788394;
            --mhis-border: #e4e8ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 38px 20px;
            color: var(--mhis-ink);
            background: #f3f5f8;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .application-shell {
            max-width: 1040px;
            margin: 0 auto;
            overflow: hidden;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 18px 55px rgba(37, 48, 65, .12);
        }

        .application-hero {
            padding: 38px 48px 42px;
            color: #fff;
            text-align: center;
            background: linear-gradient(135deg, var(--mhis-red-dark), #a90000 58%, #bd1010);
        }

        .application-logo {
            display: block;
            width: min(100%, 310px);
            height: 78px;
            margin: 0 auto 20px;
            object-fit: contain;
        }

        .application-hero h1 {
            margin: 0 0 10px;
            color: #fff;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: -.5px;
        }

        .application-hero p {
            max-width: 680px;
            margin: 0 auto;
            color: rgba(255, 255, 255, .9);
            font-size: 16px;
            line-height: 1.6;
        }

        .application-content {
            padding: 38px 48px 44px;
        }

        .section-heading {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 28px;
        }

        .section-icon {
            display: inline-flex;
            flex: 0 0 48px;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            color: var(--mhis-red);
            background: #fff1f1;
            border-radius: 14px;
            font-size: 21px;
        }

        .section-heading h2 {
            margin: 1px 0 5px;
            color: var(--mhis-ink);
            font-size: 23px;
            font-weight: 700;
        }

        .section-heading p {
            margin: 0;
            color: var(--mhis-muted);
            font-size: 14px;
        }

        .application-form .form-group {
            margin-bottom: 22px;
        }

        .application-form label {
            display: block;
            margin-bottom: 8px;
            color: #424e60;
            font-size: 14px;
            font-weight: 600;
        }

        .required-mark {
            color: var(--mhis-red);
        }

        .application-form .form-control {
            height: 46px;
            padding: 10px 13px;
            color: var(--mhis-ink);
            background-color: #fff;
            border: 1px solid var(--mhis-border);
            border-radius: 9px;
            box-shadow: none;
            font-size: 14px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .application-form textarea.form-control {
            height: auto;
            min-height: 110px;
            resize: vertical;
        }

        .application-form input[type="file"].form-control {
            height: auto;
            min-height: 46px;
            padding: 9px 11px;
        }

        .application-form .form-control:focus {
            border-color: #bd6868;
            box-shadow: 0 0 0 3px rgba(153, 0, 0, .09);
        }

        .application-form .form-control::placeholder {
            color: #a0a8b4;
        }

        .field-help {
            display: block;
            margin-top: 7px;
            color: var(--mhis-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .resume-preview {
            display: none;
            align-items: center;
            gap: 12px;
            margin-top: 12px;
            padding: 12px 14px;
            background: #fafbfc;
            border: 1px solid var(--mhis-border);
            border-radius: 10px;
        }

        .resume-preview-icon {
            color: var(--mhis-red);
            font-size: 24px;
        }

        .resume-preview-info {
            flex: 1;
            min-width: 0;
        }

        .resume-preview-name {
            overflow: hidden;
            color: var(--mhis-ink);
            font-size: 13px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .resume-preview-meta {
            margin-top: 3px;
            color: var(--mhis-muted);
            font-size: 12px;
        }

        .resume-preview-open {
            display: none;
            flex: 0 0 auto;
            padding: 7px 10px;
            color: var(--mhis-red);
            background: #fff;
            border: 1px solid #ead2d2;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
        }

        .resume-preview-open:hover,
        .resume-preview-open:focus {
            color: var(--mhis-red-dark);
            background: #fff5f5;
            text-decoration: none;
        }

        .privacy-consent {
            display: flex !important;
            align-items: flex-start;
            gap: 11px;
            margin: 2px 0 24px !important;
            padding: 16px;
            background: #fafbfc;
            border: 1px solid var(--mhis-border);
            border-radius: 10px;
            color: #596577 !important;
            font-weight: 400 !important;
            line-height: 1.55;
        }

        .privacy-consent input {
            flex: 0 0 auto;
            width: 17px;
            height: 17px;
            margin: 2px 0 0;
            accent-color: var(--mhis-red);
        }

        .submit-application {
            min-height: 48px;
            padding: 12px 24px;
            color: #fff;
            background: var(--mhis-red);
            border: 0;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .submit-application:hover,
        .submit-application:focus {
            color: #fff;
            background: var(--mhis-red-dark);
            box-shadow: 0 7px 18px rgba(153, 0, 0, .2);
            transform: translateY(-1px);
        }

        .form-alert {
            margin-bottom: 26px;
            border: 0;
            border-radius: 10px;
        }

        .form-alert ul {
            padding-left: 20px;
        }

        .application-footer {
            padding: 18px 24px;
            color: var(--mhis-muted);
            background: #fafbfc;
            border-top: 1px solid #edf0f3;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 767px) {
            body {
                padding: 16px 12px;
            }

            .application-shell {
                border-radius: 15px;
            }

            .application-hero {
                padding: 30px 22px 34px;
            }

            .application-logo {
                height: 64px;
                margin-bottom: 16px;
            }

            .application-hero h1 {
                font-size: 28px;
            }

            .application-hero p {
                font-size: 14px;
            }

            .application-content {
                padding: 28px 22px 32px;
            }

            .application-form .form-group {
                margin-bottom: 18px;
            }
        }
    </style>
</head>

<body>
    <main class="application-shell">
        <header class="application-hero">
            <img class="application-logo" src="{{ asset('images/logo mh menyamping putih-01-01.png') }}"
                alt="Mutiara Harapan Islamic School">
            <h1>Candidate Registration</h1>
            <p>Join the MHIS team and help us create an inspiring learning environment for every student.</p>
        </header>

        <section class="application-content">
            @if ($errors->any())
                <div class="alert alert-danger form-alert" role="alert">
                    <strong>Please review the following information:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="section-heading">
                <span class="section-icon" aria-hidden="true"><i class="fa fa-user"></i></span>
                <div>
                    <h2>Your Application</h2>
                    <p>Share your details and preferred position with our recruitment team.</p>
                </div>
            </div>

            <form class="application-form" method="POST" action="{{ route('recruitment.self-registration.store') }}"
                enctype="multipart/form-data">
                @csrf
                <div style="position: absolute; left: -10000px;" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="full_name">Full Name <span class="required-mark">*</span></label>
                        <input id="full_name" name="full_name" class="form-control" required maxlength="255"
                            autocomplete="name" placeholder="Enter your full name" value="{{ old('full_name') }}">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="email">Email Address <span class="required-mark">*</span></label>
                        <input id="email" name="email" type="email" class="form-control" required maxlength="255"
                            autocomplete="email" placeholder="you@example.com" value="{{ old('email') }}">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="phone">Phone Number <span class="required-mark">*</span></label>
                        <input id="phone" name="phone" type="tel" class="form-control" required maxlength="50"
                            autocomplete="tel" placeholder="Enter your phone number" value="{{ old('phone') }}">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="current_city">Current City</label>
                        <input id="current_city" name="current_city" class="form-control" maxlength="255"
                            autocomplete="address-level2" placeholder="Enter your city" value="{{ old('current_city') }}">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="position_applied">Position Applied <span class="required-mark">*</span></label>
                        <input id="position_applied" name="position_applied" class="form-control" required
                            maxlength="255" placeholder="e.g. Preschool Teacher"
                            value="{{ old('position_applied') }}">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="branch_id">Preferred Branch <span class="required-mark">*</span></label>
                        <select id="branch_id" name="branch_id" class="form-control" required>
                            <option value="">Select a branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 form-group">
                        <label for="source_detail">How did you hear about this opportunity?</label>
                        <input id="source_detail" name="source_detail" class="form-control" maxlength="255"
                            placeholder="e.g. LinkedIn, referral, or job posting"
                            value="{{ old('source_detail') }}">
                    </div>
                    <div class="col-sm-12 form-group">
                        <label for="resume">CV / Resume</label>
                        <input id="resume" name="resume" type="file" class="form-control"
                            accept=".pdf,.doc,.docx">
                        <span class="field-help">Optional. Accepted formats: PDF, DOC, or DOCX. Maximum file size: 5 MB.</span>
                        <div id="resumePreview" class="resume-preview" aria-live="polite">
                            <i id="resumePreviewIcon" class="fa fa-file-o resume-preview-icon" aria-hidden="true"></i>
                            <div class="resume-preview-info">
                                <div id="resumePreviewName" class="resume-preview-name"></div>
                                <div id="resumePreviewMeta" class="resume-preview-meta"></div>
                            </div>
                            <a id="resumePreviewOpen" class="resume-preview-open" target="_blank" rel="noopener">
                                <i class="fa fa-eye"></i> Preview
                            </a>
                        </div>
                    </div>
                </div>

                <label class="privacy-consent">
                    <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }}>
                    <span>I consent to MHIS storing and processing my personal data and CV for recruitment purposes.
                        <span class="required-mark">*</span></span>
                </label>

                <div class="text-center">
                    <button type="submit" class="btn submit-application">
                        <i class="fa fa-paper-plane"></i> Submit Application
                    </button>
                </div>
            </form>
        </section>

        <footer class="application-footer">
            Mutiara Harapan Islamic School &middot; Recruitment
        </footer>
    </main>
    <script>
        (function() {
            var fileInput = document.getElementById('resume');
            var preview = document.getElementById('resumePreview');
            var previewIcon = document.getElementById('resumePreviewIcon');
            var previewName = document.getElementById('resumePreviewName');
            var previewMeta = document.getElementById('resumePreviewMeta');
            var previewOpen = document.getElementById('resumePreviewOpen');
            var previewUrl = null;

            fileInput.addEventListener('change', function() {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                var file = fileInput.files && fileInput.files[0];
                if (!file) {
                    preview.style.display = 'none';
                    previewOpen.style.display = 'none';
                    return;
                }

                var extension = file.name.split('.').pop().toLowerCase();
                var isPdf = extension === 'pdf' && file.type === 'application/pdf';
                var isDoc = extension === 'doc' || extension === 'docx';

                preview.style.display = 'flex';
                previewName.textContent = file.name;
                previewMeta.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                previewIcon.className = 'fa ' + (isPdf ? 'fa-file-pdf-o' : (isDoc ? 'fa-file-word-o' : 'fa-file-o')) +
                    ' resume-preview-icon';

                if (isPdf) {
                    previewUrl = URL.createObjectURL(file);
                    previewOpen.href = previewUrl;
                    previewOpen.style.display = 'inline-block';
                    previewMeta.textContent += ' · PDF preview available';
                } else {
                    previewOpen.removeAttribute('href');
                    previewOpen.style.display = 'none';
                    previewMeta.textContent += isDoc ? ' · Document selected' : ' · Unsupported file type';
                }
            });

            window.addEventListener('beforeunload', function() {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
            });
        })();
    </script>
</body>

</html>

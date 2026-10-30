@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>{{ $candidate ? 'Add Application' : 'Candidate Form' }}
                    <small>{{ $candidate ? 'Create a new application for this candidate' : 'Add or import candidates, or share self-registration' }}</small>
                </h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.candidates') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-list"></i> Candidate Database
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <ul class="nav nav-tabs candidate-registration-tabs" role="tablist">
                    <li class="{{ $activeTab === 'add' ? 'active' : '' }}">
                        <a href="#addCandidate" role="tab"
                            aria-selected="{{ $activeTab === 'add' ? 'true' : 'false' }}">A. Add Candidate</a>
                    </li>
                    <li class="{{ $activeTab === 'import' ? 'active' : '' }}">
                        <a href="#importCandidates" role="tab"
                            aria-selected="{{ $activeTab === 'import' ? 'true' : 'false' }}">B. Import Candidates</a>
                    </li>
                    <li class="{{ $activeTab === 'self' ? 'active' : '' }}">
                        <a href="#selfRegistration" role="tab"
                            aria-selected="{{ $activeTab === 'self' ? 'true' : 'false' }}">C. Candidate Self-Registration</a>
                    </li>
                </ul>

                <div class="tab-content candidate-registration-content">
                    <div class="tab-pane {{ $activeTab === 'add' ? 'active' : '' }}" id="addCandidate">
                        <p class="text-muted">
                            Manually add candidates received through email, WhatsApp, referrals, walk-ins, or other sources.
                        </p>
                        @if ($candidate)
                            <div class="alert alert-info">
                                Candidate identity is prefilled. Saving creates a separate application and preserves previous applications.
                            </div>
                        @endif
                        <form method="POST" action="{{ route('recruitment.candidates.store') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="active_tab" value="add">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input id="full_name" name="full_name" class="form-control" required maxlength="255"
                                        value="{{ old('full_name', $candidate?->full_name) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email *</label>
                                    <input id="email" name="email" type="email" class="form-control" required
                                        maxlength="255" value="{{ old('email', $candidate?->email) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input id="phone" name="phone" type="tel" class="form-control" required
                                        maxlength="50" value="{{ old('phone', $candidate?->phone) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="current_city">Current City</label>
                                    <input id="current_city" name="current_city" class="form-control" maxlength="255"
                                        value="{{ old('current_city', $candidate?->current_city) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="position_applied">Position Applied *</label>
                                    <input id="position_applied" name="position_applied" class="form-control"
                                        required maxlength="255" value="{{ old('position_applied') }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="branch_id">Branch Preference *</label>
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
                                <div class="col-md-6 form-group">
                                    <label for="source">Application Source *</label>
                                    <select id="source" name="source" class="form-control" required>
                                        <option value="">Select a candidate source</option>
                                        @foreach ($sources as $source)
                                            <option value="{{ $source }}"
                                                {{ old('source') === $source ? 'selected' : '' }}>{{ $source }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="source_detail">Source Detail</label>
                                    <input id="source_detail" name="source_detail" class="form-control" maxlength="255"
                                        placeholder="Job post URL, campaign, or referral details"
                                        value="{{ old('source_detail') }}">
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for="resume">CV / Resume (PDF, DOC, DOCX; maksimal 5 MB)</label>
                                    <input id="resume" name="resume" type="file" class="form-control"
                                        accept=".pdf,.doc,.docx">
                                    <p class="help-block">Optional. PDF, DOC, or DOCX, up to 5 MB.</p>
                                </div>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="consent" value="1" required
                                        {{ old('consent') ? 'checked' : '' }}>
                                    I confirm that the candidate has consented to the storage and processing of their
                                    data under the recruitment privacy notice. *
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa {{ $candidate ? 'fa-plus' : 'fa-user-plus' }}"></i>
                                {{ $candidate ? 'Save Application' : 'Save Candidate' }}
                            </button>
                        </form>
                    </div>

                    <div class="tab-pane {{ $activeTab === 'import' ? 'active' : '' }}" id="importCandidates">
                        <p class="text-muted">
                            Upload a CSV or Excel file. The system checks columns, required data, positions and
                            branches, consent, and duplicate applications before saving valid rows.
                        </p>
                        <p class="text-muted">
                            Kolom Consent harus bernilai <code>yes</code>. Application Source harus salah satu:
                            {{ implode(', ', $sources) }}.
                        </p>
                        <p>
                            <a class="btn btn-default" href="{{ route('recruitment.candidates.import.template') }}">
                                <i class="fa fa-download"></i> Download Import Template
                            </a>
                        </p>
                        <form method="POST" action="{{ route('recruitment.candidates.import.preview') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="active_tab" value="import">
                            <div class="form-group">
                                <label for="candidate_import_file">Candidate file (CSV/XLSX, max 5 MB and 500 rows)</label>
                                <input id="candidate_import_file" name="file" type="file" class="form-control"
                                    accept=".csv,.xlsx" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Validate & Preview
                            </button>
                        </form>

                        @isset($importPreview)
                            <hr>
                            <h4>Import Preview</h4>
                            <p>
                                Total: <strong>{{ $importPreview['total'] }}</strong> |
                                Valid: <strong class="text-success">{{ $importPreview['valid_count'] }}</strong> |
                                Needs review: <strong class="text-danger">{{ $importPreview['invalid_count'] }}</strong>
                            </p>
                            <p class="text-muted">Only valid rows will be saved. Rows with errors will be skipped.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Excel Row</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Current City</th>
                                            <th>Position</th>
                                            <th>Branch</th>
                                            <th>Source</th>
                                            <th>Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (array_slice($importPreview['rows'], 0, 30) as $row)
                                            <tr>
                                                <td>{{ $row['line'] }}</td>
                                                <td>{{ $row['data']['full_name'] ?? '' }}</td>
                                                <td>{{ $row['data']['email'] ?? '' }}</td>
                                                <td>{{ $row['data']['phone_number'] ?? '' }}</td>
                                                <td>{{ $row['data']['current_city'] ?? '' }}</td>
                                                <td>{{ $row['data']['position_applied'] ?? '' }}</td>
                                                <td>{{ $row['data']['branch_preference'] ?? '' }}</td>
                                                <td>{{ $row['data']['source'] ?? '' }}</td>
                                                <td>
                                                    @if ($row['valid'])
                                                        <span class="text-success">Ready to import</span>
                                                    @else
                                                        <ul class="text-danger">
                                                            @foreach ($row['errors'] as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if (count($importPreview['rows']) > 30)
                                <p class="text-muted">Showing the first 30 of {{ count($importPreview['rows']) }} rows.</p>
                            @endif
                            @if ($importPreview['valid_count'] > 0)
                                <form method="POST" action="{{ route('recruitment.candidates.import.commit') }}">
                                    @csrf
                                    <input type="hidden" name="active_tab" value="import">
                                    <input type="hidden" name="import_token" value="{{ $importToken }}">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-check"></i> Import {{ $importPreview['valid_count'] }} Valid Rows
                                    </button>
                                </form>
                            @endif
                        @endisset
                    </div>

                    <div class="tab-pane {{ $activeTab === 'self' ? 'active' : '' }}" id="selfRegistration">
                        <h4>Public Candidate Registration Link</h4>
                        <p>
                            Share this link on social media. Candidates can submit their details directly, and their
                            applications will be recorded with the <strong>Career Page</strong> source.
                        </p>
                        <div class="input-group">
                            <input id="selfRegistrationUrl" type="text" class="form-control" readonly
                                value="{{ $selfRegistrationUrl }}">
                            <span class="input-group-btn">
                                <button id="copySelfRegistrationUrl" class="btn btn-default" type="button">
                                    <i class="fa fa-copy"></i> Copy Link
                                </button>
                            </span>
                        </div>
                        <p class="help-block">
                            Candidates provide their name, email, phone number, city, position, branch, optional CV,
                            and privacy consent.
                        </p>
                        <a href="{{ $selfRegistrationUrl }}" target="_blank" rel="noopener" class="btn btn-primary">
                            <i class="fa fa-external-link"></i> Preview Registration Form
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('content-script')
    <style>
        .candidate-registration-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 0;
            border-bottom: 2px solid #e6e9ed;
        }

        .candidate-registration-tabs > li {
            float: none;
            margin-bottom: -2px;
        }

        .candidate-registration-tabs > li > a {
            display: block;
            margin-right: 0;
            padding: 12px 18px;
            color: #52616f;
            font-weight: 600;
            background: #f5f7f9;
            border: 1px solid #e6e9ed;
            border-bottom-color: #e6e9ed;
            border-radius: 5px 5px 0 0;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .candidate-registration-tabs > li > a:hover,
        .candidate-registration-tabs > li > a:focus {
            color: #52616f;
            background: #fff;
            border-color: #d7dddf;
            text-decoration: none;
        }

        .candidate-registration-tabs > li.active > a,
        .candidate-registration-tabs > li.active > a:hover,
        .candidate-registration-tabs > li.active > a:focus {
            color: #1abb9c;
            background: #fff;
            border: 1px solid #e6e9ed;
            border-top: 3px solid #1abb9c;
            border-bottom-color: #fff;
            padding-top: 10px;
            cursor: default;
        }

        .candidate-registration-content {
            padding: 22px 18px 18px;
            border: 1px solid #e6e9ed;
            border-top: 0;
            background: #fff;
        }

        @media (max-width: 600px) {
            .candidate-registration-tabs {
                gap: 4px;
            }

            .candidate-registration-tabs > li {
                flex: 1 1 100%;
                margin-bottom: 0;
            }

            .candidate-registration-tabs > li > a {
                border-radius: 4px;
            }

            .candidate-registration-tabs > li.active > a,
            .candidate-registration-tabs > li.active > a:hover,
            .candidate-registration-tabs > li.active > a:focus {
                border: 1px solid #1abb9c;
                padding: 12px 18px;
            }
        }
    </style>
    <script>
        $(document).on('click', '.candidate-registration-tabs a[role="tab"]', function(event) {
            event.preventDefault();

            var tab = $(this);
            var content = tab.closest('.x_content');
            var target = tab.attr('href');

            content.find('.candidate-registration-tabs li').removeClass('active');
            tab.closest('li').addClass('active');
            content.find('.candidate-registration-tabs a[role="tab"]').attr('aria-selected', 'false');
            tab.attr('aria-selected', 'true');
            content.find('.candidate-registration-content .tab-pane').removeClass('active');
            content.find('.candidate-registration-content ' + target).addClass('active');
        });

        $(document).on('click', '#copySelfRegistrationUrl', function() {
            var input = document.getElementById('selfRegistrationUrl');
            input.select();
            input.setSelectionRange(0, 99999);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }
        });
    </script>
@endsection

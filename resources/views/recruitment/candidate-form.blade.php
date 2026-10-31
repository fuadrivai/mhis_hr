@extends('layouts.main-layout')



@section('content-child')

    <div class="col-md-12 col-sm-12 col-xs-12">

        @if (session('success'))

            <div class="alert alert-success alert-dismissible fade in" role="alert">

                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>

                {{ session('success') }}

            </div>

        @endif

        @if ($errors->any())

            <div class="alert alert-danger alert-dismissible fade in" role="alert">

                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>

                <ul style="margin-bottom: 0;">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif



        <div class="x_panel">

            <div class="x_title">

                <h2>Candidate Registration <small>Add candidates manually, upload bulk files, or share public link</small></h2>

                <ul class="nav navbar-right panel_toolbox">

                    <li>

                        <a href="{{ route('recruitment.candidates') }}" class="btn btn-default btn-sm">

                            <i class="fa fa-arrow-left"></i> Back to Candidate Database

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

                    <li class="{{ in_array($activeTab, ['bulk', 'bulk_cv']) ? 'active' : '' }}">

                        <a href="#bulkCvUpload" role="tab"

                            aria-selected="{{ in_array($activeTab, ['bulk', 'bulk_cv']) ? 'true' : 'false' }}"><i class="fa fa-folder-open text-primary"></i> B. Bulk CV Upload</a>

                    </li>

                    <li class="{{ $activeTab === 'import' ? 'active' : '' }}">

                        <a href="#importCandidates" role="tab"

                            aria-selected="{{ $activeTab === 'import' ? 'true' : 'false' }}">C. Import Candidates (Excel/CSV)</a>

                    </li>

                    <li class="{{ $activeTab === 'self' ? 'active' : '' }}">

                        <a href="#selfRegistration" role="tab"

                            aria-selected="{{ $activeTab === 'self' ? 'true' : 'false' }}">D. Candidate Self-Registration</a>

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

                        @else

                            <div class="alert alert-info" style="margin-bottom: 15px; padding: 10px 14px; background: #eef7ff; border: 1px solid #cce5ff; color: #004085; border-radius: 4px;">

                                <i class="fa fa-folder-open text-primary"></i> <strong>Have multiple CV files to process?</strong> 

                                Use <a href="#bulkCvUpload" class="btn-switch-bulk-tab" style="font-weight: 700; text-decoration: underline; color: #0056b3;">Bulk CV Upload</a> to automatically parse and register multiple candidate resumes at once instead of one by one.

                            </div>

                        @endif

                        <form method="POST" action="{{ route('recruitment.candidates.store') }}"

                            enctype="multipart/form-data">

                            @csrf

                            <input type="hidden" name="active_tab" value="add">

                        <div class="well well-sm" style="background: linear-gradient(135deg, #eef7ff 0%, #f4fbf7 100%); border: 1px solid #c7e2f5; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px;">

                            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">

                                <div class="col-sm-8">

                                    <h4 style="margin: 0 0 4px 0; color: #1e5a8a; font-weight: 700; font-size: 14px;">

                                        <i class="fa fa-magic text-primary"></i> Fast AI CV Auto-Fill

                                    </h4>

                                    <p style="margin: 0; color: #555; font-size: 12px;">

                                        Upload candidate's CV here. AI will extract and automatically populate <strong>Full Name</strong>, <strong>Email</strong>, <strong>Phone</strong>, and <strong>City</strong> below.

                                    </p>

                                </div>

                                <div class="col-sm-4 text-right">

                                    <button type="button" id="btnTriggerCvUpload" class="btn btn-info btn-sm" style="font-weight: 600; margin-top: 4px;">

                                        <i class="fa fa-cloud-upload"></i> Upload CV & Auto-Fill

                                    </button>

                                </div>

                            </div>

                            <div id="aiParseLoading" style="display: none; margin-top: 10px; padding: 8px 12px; background: #fff; border-radius: 4px; border: 1px dashed #337ab7;">

                                <span class="text-primary font-weight-bold">

                                    <i class="fa fa-spinner fa-spin fa-fw"></i> Analyzing CV with AI and extracting candidate identity... Please wait.

                                </span>

                            </div>

                            <div id="aiParseMessage" style="display: none; margin-top: 10px;"></div>

                        </div>

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



                                {{-- Job Opening Dropdown --}}

                                <div class="col-md-6 form-group">

                                    <label for="job_opening_id">Job Opening <span class="text-danger">*</span></label>

                                    <select id="job_opening_id" name="job_opening_id" class="form-control select2" required>

                                        <option value="">-- Select Active Job Opening --</option>

                                        @foreach ($openings as $opening)

                                            <option value="{{ $opening->id }}"

                                                data-branch-id="{{ $opening->branch_id }}"

                                                data-branch-name="{{ $opening->branch?->name }}"

                                                data-title="{{ $opening->title }}"

                                                @selected((string) old('job_opening_id', request('job_opening_id')) === (string) $opening->id)>

                                                {{ $opening->title }} ({{ $opening->branch?->name }}) — Headcount: {{ $opening->filled_count }}/{{ $opening->headcount }}

                                            </option>

                                        @endforeach

                                    </select>

                                    <span class="help-block">Select an open job vacancy. The branch will be set automatically.</span>

                                </div>



                                <div class="col-md-6 form-group">

                                    <label for="branch_id">Branch <span class="text-danger">*</span></label>

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

                                    <label for="resume">CV / Resume (PDF, DOC, DOCX, PNG, JPG; max 10 MB)</label>

                                    <input id="resume" name="resume" type="file" class="form-control"

                                        accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg">

                                    <p class="help-block">Optional. PDF, Word, or Image (PNG/JPG), up to 10 MB. Scanned/image CVs supported.</p>

                                </div>

                            </div>

                            <div class="checkbox">

                                <label>

                                    <input type="checkbox" name="consent" value="1" required

                                        {{ old('consent') ? 'checked' : '' }}>

                                    I confirm that the candidate has consented to the storage, processing, and AI CV evaluation of their data under the recruitment privacy notice (<strong>candidate-privacy-v2</strong>). *

                                </label>

                            </div>

                            <button type="submit" class="btn btn-primary">

                                <i class="fa {{ $candidate ? 'fa-plus' : 'fa-user-plus' }}"></i>

                                {{ $candidate ? 'Save Application' : 'Save Candidate' }}

                            </button>

                        </form>

                    </div>



                    <div class="tab-pane {{ in_array($activeTab, ['bulk', 'bulk_cv']) ? 'active' : '' }}" id="bulkCvUpload">

                        <div class="row" style="margin-bottom: 16px;">

                            <div class="col-md-9 col-sm-8">

                                <h3 style="margin-top: 0; font-size: 18px; color: #2c3e50; font-weight: 700;">

                                    <i class="fa fa-folder-open text-primary"></i> Bulk CV Upload & AI Auto-Registration

                                </h3>

                                <p class="text-muted" style="margin-bottom: 0;">

                                    Select or drop multiple candidate CVs (PDF, Word, or scanned images). 

                                    The system automatically extracts candidate identities (Full Name, Email, Phone, City), creates candidate records, attaches their CVs, and queues AI CV screening in the background.

                                </p>

                            </div>

                            <div class="col-md-3 col-sm-4 text-right">

                                <span class="label label-info" style="font-size: 12px; padding: 6px 10px; display: inline-block;">

                                    <i class="fa fa-bolt"></i> AI Screening Automated

                                </span>

                            </div>

                        </div>



                        @if (session('bulk_results'))

                            <div class="alert alert-{{ count(session('bulk_results.failed', [])) > 0 ? 'warning' : 'success' }} alert-dismissible fade in" role="alert">

                                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>

                                <h4 style="margin-top: 0;"><i class="fa fa-info-circle"></i> Bulk Upload Results:</h4>

                                <p style="margin-bottom: 5px;">

                                    <strong>{{ count(session('bulk_results.successful', [])) }}</strong> candidates registered successfully | 

                                    <strong>{{ count(session('bulk_results.failed', [])) }}</strong> files skipped or failed.

                                </p>

                                @if (count(session('bulk_results.failed', [])) > 0)

                                    <ul style="margin-bottom: 0; padding-left: 20px;">

                                        @foreach (session('bulk_results.failed', []) as $f)

                                            <li><strong>{{ $f['filename'] }}</strong>: {{ $f['reason'] }}</li>

                                        @endforeach

                                    </ul>

                                @endif

                            </div>

                        @endif



                        <form id="bulkCvUploadForm" method="POST" action="{{ route('recruitment.candidates.bulk-upload') }}" enctype="multipart/form-data">

                            @csrf

                            <input type="hidden" name="active_tab" value="bulk_cv">



                            {{-- Step 1: Shared Application Settings --}}

                            <div class="well well-sm" style="background: #f8fafc; border: 1px solid #d9e2ec; border-radius: 6px; padding: 16px; margin-bottom: 20px;">

                                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #1e5a8a;">

                                    <i class="fa fa-sliders text-primary"></i> 1. Select Target Job Vacancy & Application Details

                                </h4>

                                <div class="row">

                                    <div class="col-md-6 form-group">

                                        <label for="bulk_job_opening_id">Job Opening <span class="text-danger">*</span></label>

                                        <select id="bulk_job_opening_id" name="job_opening_id" class="form-control select2" required style="width: 100%;">

                                            <option value="">-- Select Active Job Opening --</option>

                                            @foreach ($openings as $opening)

                                                <option value="{{ $opening->id }}"

                                                    data-branch-id="{{ $opening->branch_id }}"

                                                    data-branch-name="{{ $opening->branch?->name }}"

                                                    data-title="{{ $opening->title }}"

                                                    @selected((string) old('job_opening_id', request('job_opening_id')) === (string) $opening->id)>

                                                    {{ $opening->title }} ({{ $opening->branch?->name }}) — Headcount: {{ $opening->filled_count }}/{{ $opening->headcount }}

                                                </option>

                                            @endforeach

                                        </select>

                                        <span class="help-block" style="margin-bottom: 0;">Branch and Position will be assigned automatically based on the selected opening.</span>

                                    </div>



                                    <div class="col-md-3 form-group">

                                        <label for="bulk_source">Application Source <span class="text-danger">*</span></label>

                                        <select id="bulk_source" name="source" class="form-control" required>

                                            @foreach ($sources as $source)

                                                <option value="{{ $source }}" {{ old('source', 'LinkedIn') === $source ? 'selected' : '' }}>

                                                    {{ $source }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>



                                    <div class="col-md-3 form-group">

                                        <label for="bulk_source_detail">Source Detail (Optional)</label>

                                        <input id="bulk_source_detail" name="source_detail" class="form-control" maxlength="255"

                                            placeholder="e.g. Sourcing batch, campaign, referral"

                                            value="{{ old('source_detail') }}">

                                    </div>

                                </div>



                                <div class="checkbox" style="margin-top: 6px; margin-bottom: 0;">

                                    <label style="font-weight: 500;">

                                        <input type="checkbox" id="bulk_consent" name="consent" value="1" required checked>

                                        I confirm candidate consent has been obtained for storage, processing, and AI CV evaluation under the recruitment privacy notice (<strong>candidate-privacy-v2</strong>). *

                                    </label>

                                </div>

                            </div>



                            {{-- Step 2: File Dropzone --}}

                            <div class="well well-sm" style="background: #ffffff; border: 1px solid #d9e2ec; border-radius: 6px; padding: 16px; margin-bottom: 20px;">

                                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #1e5a8a;">

                                    <i class="fa fa-cloud-upload text-primary"></i> 2. Choose or Drop Multiple CV Files

                                </h4>



                                <div id="bulkCvDropzone" style="border: 2px dashed #3498db; border-radius: 8px; background: #fdfefe; padding: 32px 20px; text-align: center; cursor: pointer; transition: all 0.2s ease;">

                                    <i class="fa fa-cloud-upload fa-3x" style="color: #3498db; margin-bottom: 10px;"></i>

                                    <h4 style="margin: 0 0 6px 0; font-weight: 600; color: #2c3e50;">

                                        Drag & drop multiple CV files here, or <span class="text-primary" style="text-decoration: underline;">click to browse</span>

                                    </h4>

                                    <p class="text-muted" style="margin: 0; font-size: 13px;">

                                        Supported formats: <strong>PDF, DOC, DOCX, TXT, PNG, JPG, JPEG</strong> (Up to 10 MB per file, max 50 files per batch)

                                    </p>

                                    <input type="file" id="bulk_cv_files" name="cv_files[]" multiple

                                        accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg" style="display: none;">

                                </div>



                                {{-- Queue Controls and Stats --}}

                                <div id="bulkActionBar" style="display: none; margin-top: 18px; padding-top: 14px; border-top: 1px solid #edf2f7;">

                                    <div class="row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">

                                        <div class="col-sm-6">

                                            <span style="font-weight: 600; font-size: 14px; color: #2c3e50;">

                                                <i class="fa fa-files-o text-primary"></i> <span id="bulkFileCount">0</span> CV files in queue

                                            </span>

                                            <span id="bulkBadgeSummary" style="margin-left: 10px;"></span>

                                        </div>

                                        <div class="col-sm-6 text-right">

                                            <button type="button" id="btnClearBulkQueue" class="btn btn-default btn-sm" style="margin-right: 6px;">

                                                <i class="fa fa-trash text-danger"></i> Clear All

                                            </button>

                                            <button type="button" id="btnStartBulkUpload" class="btn btn-primary" style="font-weight: 600;">

                                                <i class="fa fa-play"></i> Start Bulk Upload & Auto-Register

                                            </button>

                                        </div>

                                    </div>



                                    {{-- Progress Bar --}}

                                    <div id="bulkProgressWrapper" style="display: none; margin-top: 15px;">

                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 600;">

                                            <span id="bulkProgressLabel">Processing CVs with AI...</span>

                                            <span id="bulkProgressPercent">0%</span>

                                        </div>

                                        <div class="progress" style="height: 20px; margin-bottom: 8px; border-radius: 4px;">

                                            <div id="bulkProgressBar" class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%; min-width: 2em; background-color: #26B99A;">

                                                0%

                                            </div>

                                        </div>

                                    </div>

                                </div>



                                {{-- Queue Table --}}

                                <div id="bulkQueueContainer" style="display: none; margin-top: 16px;">

                                    <div class="table-responsive">

                                        <table class="table table-bordered table-striped" id="bulkQueueTable" style="margin-bottom: 0;">

                                            <thead style="background: #f5f7f9;">

                                                <tr>

                                                    <th style="width: 45px; text-align: center;">#</th>

                                                    <th>CV File</th>

                                                    <th>Extracted Candidate</th>

                                                    <th>Contact Details</th>

                                                    <th style="width: 280px;">Status / Result</th>

                                                    <th style="width: 90px; text-align: center;">Action</th>

                                                </tr>

                                            </thead>

                                            <tbody id="bulkQueueTbody">

                                            </tbody>

                                        </table>

                                    </div>

                                </div>



                                {{-- Completion Summary Banner --}}

                                <div id="bulkCompleteBanner" style="display: none; margin-top: 20px;" class="alert alert-success">

                                    <div class="row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">

                                        <div class="col-sm-8">

                                            <h4 style="margin: 0 0 5px 0; font-weight: 700;">

                                                <i class="fa fa-check-circle"></i> Bulk Upload Finished!

                                            </h4>

                                            <p style="margin: 0;" id="bulkCompleteText">

                                                All candidates have been registered and background AI CV screening has been queued.

                                            </p>

                                        </div>

                                        <div class="col-sm-4 text-right">

                                            <a href="{{ route('recruitment.candidates') }}" class="btn btn-default" style="font-weight: 600;">

                                                <i class="fa fa-users"></i> View Candidate Database

                                            </a>

                                        </div>

                                    </div>

                                </div>



                            </div>

                        </form>

                    </div>



                    <div class="tab-pane {{ $activeTab === 'import' ? 'active' : '' }}" id="importCandidates">

                        <p class="text-muted">

                            Upload a CSV or Excel file. The system checks columns, required data, job openings, consent, and duplicate applications before saving valid rows.

                        </p>

                        <p class="text-muted">

                            Column <code>Position Applied</code> will be matched against open Job Opening titles. Kolom Consent harus bernilai <code>yes</code>. Application Source harus salah satu:

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

                                            <th>Position Applied</th>

                                            <th>Branch Preference</th>

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

                            Share this link on career sites and social media. Candidates can choose open vacancies and submit their details directly. Their applications will be recorded with the <strong>Career Page</strong> source.

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

                            Candidates select an active Job Opening and provide their contact details, optional CV, and privacy consent.

                        </p>

                        <a href="{{ $selfRegistrationUrl }}" target="_blank" rel="noopener" class="btn btn-primary">

                            <i class="fa fa-external-link"></i> Preview Public Registration Form

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

        $(document).ready(function() {

            if ($.fn.select2) {

                $('.select2').select2({ width: '100%' });

            }



            function syncBranchFromOpening() {

                var selected = $('#job_opening_id').find(':selected');

                var branchId = selected.data('branch-id');

                if (branchId) {

                    $('#branch_id').val(branchId).trigger('change');

                }

            }



            $('#job_opening_id').on('change', syncBranchFromOpening);



            $('#btnTriggerCvUpload').on('click', function() {

                $('#resume').click();

            });



            $('#resume').on('change', function() {

                var file = this.files[0];

                if (!file) return;



                var formData = new FormData();

                formData.append('resume', file);

                formData.append('_token', '{{ csrf_token() }}');



                $('#aiParseLoading').slideDown();

                $('#aiParseMessage').hide();



                $.ajax({

                    url: '{{ route("recruitment.candidates.parse-cv") }}',

                    type: 'POST',

                    data: formData,

                    contentType: false,

                    processData: false,

                    success: function(res) {

                        $('#aiParseLoading').slideUp();

                        if (res.status === 'success' && res.data) {

                            var d = res.data;

                            var populated = [];

                            if (d.full_name) {

                                $('#full_name').val(d.full_name).css('border-color', '#26B99A');

                                populated.push('Full Name');

                            }

                            if (d.email) {

                                $('#email').val(d.email).css('border-color', '#26B99A');

                                populated.push('Email');

                            }

                            if (d.phone) {

                                $('#phone').val(d.phone).css('border-color', '#26B99A');

                                populated.push('Phone');

                            }

                            if (d.current_city) {

                                $('#current_city').val(d.current_city).css('border-color', '#26B99A');

                                populated.push('City');

                            }



                            var summary = populated.length > 0 ? populated.join(', ') : 'No identity details detected';

                            $('#aiParseMessage')

                                .html('<div class="alert alert-success" style="margin-bottom: 0; padding: 8px 12px;"><i class="fa fa-check-circle"></i> <strong>CV Parsed!</strong> Auto-filled: ' + summary + '. Please verify before saving.</div>')

                                .slideDown();

                        } else {

                            $('#aiParseMessage')

                                .html('<div class="alert alert-warning" style="margin-bottom: 0; padding: 8px 12px;"><i class="fa fa-info-circle"></i> AI completed with empty identity fields. Please fill manually.</div>')

                                .slideDown();

                        }

                    },

                    error: function(xhr) {

                        $('#aiParseLoading').slideUp();

                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to parse CV automatically.';

                        $('#aiParseMessage')

                            .html('<div class="alert alert-warning" style="margin-bottom: 0; padding: 8px 12px;"><i class="fa fa-exclamation-triangle"></i> ' + msg + '</div>')

                            .slideDown();

                    }

                });

            });



            // Run on load if pre-selected

            if ($('#job_opening_id').val()) {

                syncBranchFromOpening();

            }

        });



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



        // Switch tab helper

        $(document).on('click', '.btn-switch-bulk-tab', function(e) {

            e.preventDefault();

            $('.candidate-registration-tabs a[href="#bulkCvUpload"]').trigger('click');

        });



        // Activate tab from URL hash on load

        if (window.location.hash && $('.candidate-registration-tabs a[href="' + window.location.hash + '"]').length) {

            $('.candidate-registration-tabs a[href="' + window.location.hash + '"]').trigger('click');

        }



        // --- BULK CV UPLOAD LOGIC ---

        (function() {

            var queueFiles = [];

            var isProcessing = false;



            function escapeHtml(str) {

                if (!str) return '';

                return $('<div>').text(str).html();

            }



            function formatBytes(bytes) {

                if (bytes === 0) return '0 Bytes';

                var k = 1024, dm = 1, sizes = ['Bytes', 'KB', 'MB', 'GB'];

                var i = Math.floor(Math.log(bytes) / Math.log(k));

                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];

            }



            var dropzone = $('#bulkCvDropzone');

            var fileInput = $('#bulk_cv_files');



            dropzone.on('click', function(e) {

                if (isProcessing) return;

                fileInput.trigger('click');

            });



            dropzone.on('dragover dragenter', function(e) {

                e.preventDefault();

                e.stopPropagation();

                if (!isProcessing) {

                    dropzone.css({ 'background': '#eef7ff', 'border-color': '#1abb9c' });

                }

            });



            dropzone.on('dragleave dragend', function(e) {

                e.preventDefault();

                e.stopPropagation();

                dropzone.css({ 'background': '#fdfefe', 'border-color': '#3498db' });

            });



            dropzone.on('drop', function(e) {

                e.preventDefault();

                e.stopPropagation();

                dropzone.css({ 'background': '#fdfefe', 'border-color': '#3498db' });

                if (isProcessing) return;

                var dt = e.originalEvent.dataTransfer;

                if (dt && dt.files && dt.files.length) {

                    addFilesToQueue(dt.files);

                }

            });



            fileInput.on('change', function() {

                if (this.files && this.files.length) {

                    addFilesToQueue(this.files);

                    fileInput.val('');

                }

            });



            function addFilesToQueue(files) {

                var allowed = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg'];

                var added = 0;

                var skipped = 0;



                for (var i = 0; i < files.length; i++) {

                    var f = files[i];

                    var ext = f.name.split('.').pop().toLowerCase();

                    if (allowed.indexOf(ext) === -1) {

                        skipped++;

                        continue;

                    }

                    if (f.size > 10 * 1024 * 1024) {

                        skipped++;

                        continue;

                    }

                    queueFiles.push({

                        id: 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),

                        file: f,

                        completed: false,

                        failed: false,

                        overrideEmail: null,

                        overrideName: null

                    });

                    added++;

                }



                if (skipped > 0) {

                    alert(skipped + ' file(s) were skipped because they exceed 10 MB or are not supported.');

                }



                renderQueue();

            }



            function renderQueue() {

                var tbody = $('#bulkQueueTbody');

                tbody.empty();



                if (queueFiles.length === 0) {

                    $('#bulkActionBar').hide();

                    $('#bulkQueueContainer').hide();

                    $('#bulkCompleteBanner').hide();

                    return;

                }



                $('#bulkFileCount').text(queueFiles.length);

                $('#bulkActionBar').show();

                $('#bulkQueueContainer').show();

                $('#bulkCompleteBanner').hide();



                for (var i = 0; i < queueFiles.length; i++) {

                    var item = queueFiles[i];

                    var row = $('<tr id="queue-row-' + i + '" data-index="' + i + '"></tr>');



                    row.append('<td style="text-align: center; vertical-align: middle;"><strong>' + (i + 1) + '</strong></td>');

                    row.append('<td style="vertical-align: middle;"><i class="fa fa-file-text-o text-primary"></i> <strong>' + escapeHtml(item.file.name) + '</strong><br><small class="text-muted">' + formatBytes(item.file.size) + '</small></td>');

                    row.append('<td class="queue-candidate" style="vertical-align: middle;"><span class="text-muted">Will extract...</span></td>');

                    row.append('<td class="queue-contact" style="vertical-align: middle;"><span class="text-muted">—</span></td>');

                    row.append('<td class="queue-status" style="vertical-align: middle;"><span class="label label-default">Ready</span></td>');

                    

                    var actionCell = $('<td class="queue-action" style="text-align: center; vertical-align: middle;"></td>');

                    if (!isProcessing) {

                        actionCell.html('<button type="button" class="btn btn-xs btn-danger btn-remove-row" data-index="' + i + '" title="Remove from queue"><i class="fa fa-trash"></i></button>');

                    }

                    row.append(actionCell);



                    tbody.append(row);

                }

            }



            $(document).on('click', '.btn-remove-row', function() {

                if (isProcessing) return;

                var idx = parseInt($(this).data('index'), 10);

                queueFiles.splice(idx, 1);

                renderQueue();

            });



            $('#btnClearBulkQueue').on('click', function() {

                if (isProcessing) return;

                queueFiles = [];

                renderQueue();

            });



            function updateProgress(processed, total, successCount, failCount) {

                var pct = total > 0 ? Math.round((processed / total) * 100) : 0;

                $('#bulkProgressBar').css('width', pct + '%').text(pct + '%');

                $('#bulkProgressPercent').text(pct + '%');

                $('#bulkProgressLabel').text('Processed ' + processed + ' of ' + total + ' CVs (' + successCount + ' registered, ' + failCount + ' need attention)');

                $('#bulkBadgeSummary').html('<span class="label label-success">' + successCount + ' registered</span> <span class="label label-danger">' + failCount + ' failed</span>');

            }



            $('#btnStartBulkUpload').on('click', function() {

                if (isProcessing) return;



                var openingId = $('#bulk_job_opening_id').val();

                if (!openingId) {

                    alert('Please select a Target Job Opening before starting bulk upload.');

                    $('#bulk_job_opening_id').focus();

                    return;

                }



                if (!$('#bulk_consent').is(':checked')) {

                    alert('Please confirm candidate privacy consent before uploading.');

                    $('#bulk_consent').focus();

                    return;

                }



                if (queueFiles.length === 0) {

                    alert('Please choose or drop at least one CV file.');

                    return;

                }



                isProcessing = true;

                $('#btnStartBulkUpload').prop('disabled', true);

                $('#btnClearBulkQueue').prop('disabled', true);

                $('.btn-remove-row').hide();

                $('#bulkProgressWrapper').slideDown();

                $('#bulkCompleteBanner').hide();



                var total = queueFiles.length;

                var processed = 0;

                var successCount = 0;

                var failCount = 0;



                updateProgress(0, total, 0, 0);



                function processIndex(idx) {

                    if (idx >= total) {

                        isProcessing = false;

                        $('#bulkProgressBar').removeClass('active');

                        $('#btnStartBulkUpload').prop('disabled', false);

                        $('#btnClearBulkQueue').prop('disabled', false);

                        $('#bulkCompleteText').html('Batch complete! <strong>' + successCount + '</strong> candidates registered successfully, <strong>' + failCount + '</strong> files need attention.');

                        $('#bulkCompleteBanner').slideDown();

                        return;

                    }



                    var item = queueFiles[idx];

                    var row = $('#queue-row-' + idx);



                    row.find('.queue-status').html('<span class="label label-info"><i class="fa fa-spinner fa-spin"></i> Parsing CV & Registering...</span>');



                    var fd = new FormData();

                    fd.append('cv_file', item.file);

                    fd.append('job_opening_id', openingId);

                    fd.append('source', $('#bulk_source').val());

                    fd.append('source_detail', $('#bulk_source_detail').val());

                    fd.append('consent', '1');

                    fd.append('_token', '{{ csrf_token() }}');



                    if (item.overrideEmail) {

                        fd.append('email', item.overrideEmail);

                    }

                    if (item.overrideName) {

                        fd.append('full_name', item.overrideName);

                    }



                    $.ajax({

                        url: '{{ route("recruitment.candidates.bulk-upload-item") }}',

                        type: 'POST',

                        data: fd,

                        contentType: false,

                        processData: false,

                        success: function(res) {

                            processed++;

                            successCount++;

                            item.completed = true;



                            row.find('.queue-candidate').html(

                                '<strong>' + escapeHtml(res.full_name) + '</strong><br>' +

                                '<span class="label label-success" style="font-size: 10px;">' + escapeHtml(res.candidate_code || 'CAN') + '</span>'

                            );

                            row.find('.queue-contact').html(

                                '<small><i class="fa fa-envelope"></i> ' + escapeHtml(res.email) + '<br>' +

                                '<i class="fa fa-phone"></i> ' + escapeHtml(res.phone || '-') + '</small>'

                            );

                            row.find('.queue-status').html(

                                '<span class="text-success" style="font-weight: 700;"><i class="fa fa-check-circle"></i> Registered</span><br>' +

                                '<small class="text-muted"><i class="fa fa-magic"></i> AI Screening Queued</small>'

                            );

                            row.find('.queue-action').html(

                                '<a href="' + res.profile_url + '" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-external-link"></i> View</a>'

                            );



                            updateProgress(processed, total, successCount, failCount);

                            processIndex(idx + 1);

                        },

                        error: function(xhr) {

                            processed++;

                            failCount++;

                            item.failed = true;

                            var res = xhr.responseJSON || {};



                            if (res.code === 'MISSING_EMAIL') {

                                var extName = res.extracted && res.extracted.full_name ? res.extracted.full_name : '';

                                if (extName) {

                                    row.find('.queue-candidate').html('<strong>' + escapeHtml(extName) + '</strong>');

                                }

                                row.find('.queue-status').html(

                                    '<div class="text-warning" style="font-weight: 600; font-size: 11px; margin-bottom: 4px;"><i class="fa fa-exclamation-triangle"></i> Email missing in CV</div>' +

                                    '<div class="input-group input-group-sm" style="max-width: 250px;">' +

                                    '<input type="email" class="form-control inline-retry-email" placeholder="Enter candidate email">' +

                                    '<span class="input-group-btn">' +

                                    '<button type="button" class="btn btn-warning btn-inline-retry" data-index="' + idx + '" title="Save Candidate"><i class="fa fa-check"></i></button>' +

                                    '</span></div>'

                                );

                                row.find('.queue-action').html('<span class="label label-warning">Needs Email</span>');

                            } else {

                                var msg = res.message || 'Registration failed.';

                                row.find('.queue-status').html('<span class="text-danger" style="font-weight: 600; font-size: 12px;"><i class="fa fa-times-circle"></i> ' + escapeHtml(msg) + '</span>');

                                row.find('.queue-action').html('<span class="label label-danger">Failed</span>');

                            }



                            updateProgress(processed, total, successCount, failCount);

                            processIndex(idx + 1);

                        }

                    });

                }



                processIndex(0);

            });



            // Inline retry for items with missing email

            $(document).on('click', '.btn-inline-retry', function() {

                var btn = $(this);

                var idx = parseInt(btn.data('index'), 10);

                var item = queueFiles[idx];

                var row = $('#queue-row-' + idx);

                var emailInput = row.find('.inline-retry-email');

                var emailVal = $.trim(emailInput.val());



                if (!emailVal || emailVal.indexOf('@') === -1) {

                    alert('Please enter a valid email address.');

                    emailInput.focus();

                    return;

                }



                item.overrideEmail = emailVal;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                row.find('.queue-status').html('<span class="label label-info"><i class="fa fa-spinner fa-spin"></i> Registering...</span>');



                var fd = new FormData();

                fd.append('cv_file', item.file);

                fd.append('job_opening_id', $('#bulk_job_opening_id').val());

                fd.append('source', $('#bulk_source').val());

                fd.append('source_detail', $('#bulk_source_detail').val());

                fd.append('email', emailVal);

                fd.append('consent', '1');

                fd.append('_token', '{{ csrf_token() }}');



                $.ajax({

                    url: '{{ route("recruitment.candidates.bulk-upload-item") }}',

                    type: 'POST',

                    data: fd,

                    contentType: false,

                    processData: false,

                    success: function(res) {

                        item.completed = true;

                        item.failed = false;



                        row.find('.queue-candidate').html(

                            '<strong>' + escapeHtml(res.full_name) + '</strong><br>' +

                            '<span class="label label-success" style="font-size: 10px;">' + escapeHtml(res.candidate_code || 'CAN') + '</span>'

                        );

                        row.find('.queue-contact').html(

                            '<small><i class="fa fa-envelope"></i> ' + escapeHtml(res.email) + '<br>' +

                            '<i class="fa fa-phone"></i> ' + escapeHtml(res.phone || '-') + '</small>'

                        );

                        row.find('.queue-status').html(

                            '<span class="text-success" style="font-weight: 700;"><i class="fa fa-check-circle"></i> Registered</span><br>' +

                            '<small class="text-muted"><i class="fa fa-magic"></i> AI Screening Queued</small>'

                        );

                        row.find('.queue-action').html(

                            '<a href="' + res.profile_url + '" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-external-link"></i> View</a>'

                        );

                    },

                    error: function(xhr) {

                        var res = xhr.responseJSON || {};

                        var msg = res.message || 'Retry failed.';

                        alert('Could not register candidate: ' + msg);

                        btn.prop('disabled', false).html('<i class="fa fa-check"></i>');

                    }

                });

            });



        })();

    </script>

@endsection


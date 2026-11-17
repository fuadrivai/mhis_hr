@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 visitor-invitation-page">
        <div class="visitor-form-heading">

            <a class="btn btn-default" href="{{ route('visitor.index') }}"><i class="fa fa-arrow-left"></i> Back to visits</a>
        </div>

        <div class="x_panel visitor-form-panel">
            <div class="x_content">
                @if ($errors->any())
                    <div class="alert alert-danger visitor-form-errors" role="alert">
                        <strong><i class="fa fa-exclamation-circle"></i> Please check the form details.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @php
                    $selectedHostId = old('host_employee_id', $defaultHostId);
                    $defaultMonitorIds = old('monitor_employee_ids_submitted')
                        ? []
                        : ((int) $selectedHostId !== (int) $defaultHostId
                            ? [$defaultHostId]
                            : []);
                    $selectedMonitorIds = old('monitor_employee_ids', $defaultMonitorIds);
                @endphp

                <form method="POST" action="{{ route('visitor.store') }}" enctype="multipart/form-data"
                    class="visitor-invitation-form">
                    @csrf
                    <input type="hidden" name="monitor_employee_ids_submitted" value="1">

                    <section class="visitor-form-section">
                        <div class="visitor-section-heading">
                            <span class="visitor-section-icon"><i class="fa fa-user"></i></span>
                            <div>
                                <h2>Visitor information</h2>
                                <p>Provide the visitor's contact and organization details.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="visitor-full-name">Visitor full name <span
                                        class="visitor-required">*</span></label>
                                <input id="visitor-full-name" name="full_name" class="form-control"
                                    value="{{ old('full_name') }}" required maxlength="255" autocomplete="name"
                                    placeholder="Enter visitor's full name">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="visitor-company">Company / organization</label>
                                <input id="visitor-company" name="company" class="form-control" value="{{ old('company') }}"
                                    maxlength="255" autocomplete="organization" placeholder="Company or organization">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="visitor-email">Email <span class="visitor-required">*</span></label>
                                <input id="visitor-email" type="email" name="email" class="form-control"
                                    value="{{ old('email') }}" maxlength="255" autocomplete="email" required
                                    placeholder="name@example.com">
                                <small class="visitor-field-help">The invitation and QR code will be sent to this email.</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="visitor-phone">Phone</label>
                                <input id="visitor-phone" name="phone" class="form-control" value="{{ old('phone') }}"
                                    maxlength="40" autocomplete="tel" placeholder="Phone number">
                            </div>
                        </div>
                    </section>

                    <section class="visitor-form-section visitor-host-section">
                        <div class="visitor-section-heading">
                            <span class="visitor-section-icon"><i class="fa fa-users"></i></span>
                            <div>
                                <h2>Host and monitoring</h2>
                                <p>Choose the host and optionally share this invitation with monitoring employees.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="visitor-host">Host employee <span class="visitor-required">*</span></label>
                                <select id="visitor-host" name="host_employee_id" class="form-control select2"
                                    style="width: 100%" data-current-employee-id="{{ $defaultHostId }}" required>
                                    @foreach ($hosts as $host)
                                        <option value="{{ $host->id }}" @selected($selectedHostId == $host->id)>
                                            {{ $host->user?->name ?? 'Employee #' . $host->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group" id="monitor-employees-group" style="display:none">
                                <label for="monitor-employee-ids">Employees to monitor this invitation <span
                                        class="visitor-optional">Optional</span></label>
                                <select name="monitor_employee_ids[]" id="monitor-employee-ids" class="form-control select2"
                                    style="width: 100%" multiple>
                                    @foreach ($hosts as $host)
                                        <option value="{{ $host->id }}" @selected(in_array($host->id, (array) $selectedMonitorIds))>
                                            {{ $host->user?->name ?? 'Employee #' . $host->id }}</option>
                                    @endforeach
                                </select>
                                <small class="visitor-field-help" id="monitor-help">Select employees who should see this
                                    invitation in their monitoring list.</small>
                            </div>
                        </div>
                    </section>

                    <section class="visitor-form-section">
                        <div class="visitor-section-heading">
                            <span class="visitor-section-icon"><i class="fa fa-map-marker"></i></span>
                            <div>
                                <h2>Visit details</h2>
                                <p>Choose the location, visit type, and expected schedule.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="visitor-branch">Branch / meeting location <span
                                        class="visitor-required">*</span></label>
                                <select id="visitor-branch" name="branch_id" class="form-control" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected(old('branch_id', $defaultBranchId) == $branch->id)>
                                            {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="visitor-category">Visitor category <span
                                        class="visitor-optional">Optional</span></label>
                                <select id="visitor-category" name="visitor_category_id" class="form-control select2"
                                    style="width: 100%">
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('visitor_category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="visitor-purpose">Visit purpose <span
                                        class="visitor-optional">Optional</span></label>
                                <select id="visitor-purpose" name="visitor_purpose_id" class="form-control select2"
                                    style="width: 100%">
                                    <option value="">Select purpose</option>
                                    @foreach ($purposes as $purpose)
                                        <option value="{{ $purpose->id }}" @selected(old('visitor_purpose_id') == $purpose->id)>
                                            {{ $purpose->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="visit-date-picker">Visit date <span class="visitor-required">*</span></label>
                                <input type="text" id="visit-date-picker" class="form-control date-picker"
                                    data-date-format="dd MM yyyy" data-date-language="en"
                                    data-date-start-date="{{ now()->format('d F Y') }}"
                                    value="{{ \Carbon\Carbon::parse(old('visit_date', now()->toDateString()))->format('d F Y') }}"
                                    autocomplete="off" readonly required>
                                <input type="hidden" id="visit-date-value" name="visit_date"
                                    value="{{ old('visit_date', now()->toDateString()) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="arrival-time-picker">Expected arrival <span
                                        class="visitor-required">*</span></label>
                                <input type="text" id="arrival-time-picker" name="expected_arrival_time"
                                    class="form-control time-picker" value="{{ old('expected_arrival_time') }}"
                                    autocomplete="off" placeholder="Select arrival time" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="departure-date-picker">Departure date <span
                                        class="visitor-optional">Optional</span></label>
                                <input type="text" id="departure-date-picker" name="expected_departure_date"
                                    class="form-control date-picker" data-date-format="yyyy-mm-dd"
                                    data-date-language="en" value="{{ old('expected_departure_date') }}"
                                    autocomplete="off" readonly>
                                <small class="visitor-field-help">Only if different from the visit date.</small>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="departure-time-picker">Departure time <span
                                        class="visitor-optional">Optional</span></label>
                                <input type="text" id="departure-time-picker" name="expected_departure_time"
                                    class="form-control time-picker" value="{{ old('expected_departure_time') }}"
                                    autocomplete="off" placeholder="Select departure time">
                            </div>
                            {{-- <div class="col-md-6 form-group">
                                <label for="visitor-attachment">Attachment</label>
                                <input id="visitor-attachment" type="file" name="attachment"
                                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                    class="form-control">
                                <small class="visitor-field-help">Optional. PDF, JPG, JPEG, or PNG. Maximum 5 MB.</small>
                                <div id="visitor-attachment-preview" class="visitor-attachment-preview" hidden
                                    aria-live="polite">
                                    <div class="visitor-attachment-preview-heading">
                                        <span id="visitor-attachment-preview-name"></span>
                                        <button id="visitor-attachment-remove" class="btn btn-default btn-xs"
                                            type="button"><i class="fa fa-times"></i> Remove</button>
                                    </div>
                                    <img id="visitor-attachment-preview-image" alt="Selected attachment preview" hidden>
                                    <iframe id="visitor-attachment-preview-pdf" title="Selected PDF attachment preview"
                                        hidden></iframe>
                                </div>
                            </div> --}}
                            <div class="col-md-12 form-group">
                                <label for="visitor-notes">Visit notes</label>
                                <textarea id="visitor-notes" name="notes" class="form-control" rows="3" maxlength="3000"
                                    placeholder="Add helpful details for the host or reception">{{ old('notes') }}</textarea>
                                <small class="visitor-field-help">Some visitor categories require notes.</small>
                            </div>
                        </div>
                    </section>

                    <div class="visitor-form-footer">
                        <a class="btn btn-default" href="{{ route('visitor.index') }}">Cancel</a>
                        <button class="btn btn-success" type="submit"><i class="fa fa-send"></i> Submit
                            invitation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <style>
        .visitor-invitation-page {
            --visitor-form-ink: #2a3f54;
            --visitor-form-muted: #73879c;
            --visitor-form-line: #e6eaed;
            padding-bottom: 20px;
        }

        .visitor-form-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 4px 0 18px;
        }

        .visitor-form-heading h1 {
            margin: 0;
            color: var(--visitor-form-ink);
            font-size: 24px;
            font-weight: 600;
        }

        .visitor-form-heading .btn {
            flex: 0 0 auto;
            margin: 0;
        }

        .visitor-form-eyebrow {
            margin: 0 0 4px;
            color: #1f6f8b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .visitor-form-subtitle {
            margin: 5px 0 0;
            color: var(--visitor-form-muted);
        }

        .visitor-form-panel {
            border: 1px solid var(--visitor-form-line);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(42, 63, 84, .04);
        }

        .visitor-form-errors {
            border-radius: 5px;
        }

        .visitor-form-errors ul {
            margin: 7px 0 0;
            padding-left: 20px;
        }

        .visitor-form-section {
            padding: 8px 0 18px;
            border-bottom: 1px solid var(--visitor-form-line);
        }

        .visitor-form-section+.visitor-form-section {
            padding-top: 21px;
        }

        .visitor-section-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 0 0 20px;
        }

        .visitor-section-icon {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 50%;
            background: #eaf3f6;
            color: #1f6f8b;
            font-size: 15px;
        }

        .visitor-section-heading h2 {
            margin: 1px 0 3px;
            color: var(--visitor-form-ink);
            font-size: 16px;
            font-weight: 600;
        }

        .visitor-section-heading p {
            margin: 0;
            color: var(--visitor-form-muted);
            font-size: 12px;
        }

        .visitor-invitation-form .form-group {
            margin-bottom: 17px;
        }

        .visitor-invitation-form .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #46596b;
            font-size: 12px;
            font-weight: 600;
        }

        .visitor-invitation-form .form-control {
            min-height: 36px;
            border-color: #dfe3e7;
            border-radius: 4px;
            box-shadow: none;
        }

        .visitor-invitation-form .form-control:focus {
            border-color: #1f6f8b;
            box-shadow: 0 0 0 2px rgba(31, 111, 139, .1);
        }

        .visitor-invitation-form textarea.form-control {
            min-height: 82px;
            resize: vertical;
        }

        .visitor-required {
            color: #c0392b;
        }

        .visitor-optional {
            margin-left: 4px;
            color: var(--visitor-form-muted);
            font-size: 10px;
            font-weight: 400;
        }

        .visitor-field-help {
            display: block;
            margin-top: 5px;
            color: var(--visitor-form-muted);
            font-size: 11px;
            line-height: 1.4;
        }

        .visitor-attachment-preview {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid var(--visitor-form-line);
            border-radius: 5px;
            background: #f8fafb;
        }

        .visitor-attachment-preview-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 12px;
        }

        .visitor-attachment-preview-heading span {
            overflow-wrap: anywhere;
        }

        .visitor-attachment-preview img,
        .visitor-attachment-preview iframe {
            display: block;
            width: 100%;
            max-height: 420px;
            border: 0;
            border-radius: 3px;
            background: #fff;
            object-fit: contain;
        }

        .visitor-attachment-preview iframe {
            height: 420px;
        }

        .visitor-form-notice {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-top: 18px;
            padding: 12px 14px;
            border: 1px solid #dceaf0;
            border-radius: 5px;
            background: #f5fafc;
            color: #536a7b;
            font-size: 12px;
        }

        .visitor-form-notice .fa {
            margin-top: 1px;
            color: #1f6f8b;
            font-size: 15px;
        }

        .visitor-form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 18px;
        }

        .visitor-form-footer .btn {
            margin: 0;
        }

        .visitor-form-footer .btn-success {
            min-width: 165px;
        }

        @media (max-width: 767px) {
            .visitor-form-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .visitor-form-heading h1 {
                font-size: 21px;
            }

            .visitor-form-heading .btn {
                align-self: flex-start;
            }

            .visitor-form-panel>.x_content {
                padding: 12px;
            }

            .visitor-section-heading {
                margin-bottom: 16px;
            }

            .visitor-form-footer {
                position: sticky;
                bottom: 0;
                z-index: 2;
                margin: 16px -12px -12px;
                padding: 12px;
                border-top: 1px solid var(--visitor-form-line);
                background: rgba(255, 255, 255, .97);
            }

            .visitor-form-footer .btn {
                flex: 1;
            }
        }
    </style>
    <script>
        $(function() {
            var $host = $('#visitor-host');
            var $monitors = $('#monitor-employee-ids');
            var currentEmployeeId = String($host.data('current-employee-id'));
            var previousHostId = String($host.val() || '');
            var attachmentPreviewUrl = null;

            function clearAttachmentPreview() {
                if (attachmentPreviewUrl) {
                    URL.revokeObjectURL(attachmentPreviewUrl);
                    attachmentPreviewUrl = null;
                }

                $('#visitor-attachment-preview').prop('hidden', true);
                $('#visitor-attachment-preview-image').prop('hidden', true).removeAttr('src');
                $('#visitor-attachment-preview-pdf').prop('hidden', true).removeAttr('src');
                $('#visitor-attachment-preview-name').text('');
            }

            $('#visitor-attachment').on('change', function() {
                clearAttachmentPreview();
                var file = this.files && this.files[0];
                if (!file) {
                    return;
                }

                var extension = file.name.split('.').pop().toLowerCase();
                if (['pdf', 'jpg', 'jpeg', 'png'].indexOf(extension) === -1) {
                    return;
                }

                attachmentPreviewUrl = URL.createObjectURL(file);
                $('#visitor-attachment-preview-name').text(file.name);
                $('#visitor-attachment-preview').prop('hidden', false);

                if (extension === 'pdf') {
                    $('#visitor-attachment-preview-pdf').attr('src', attachmentPreviewUrl).prop('hidden',
                        false);
                } else {
                    $('#visitor-attachment-preview-image').attr('src', attachmentPreviewUrl).prop('hidden',
                        false);
                }
            });

            $('#visitor-attachment-remove').on('click', function() {
                $('#visitor-attachment').val('');
                clearAttachmentPreview();
            });

            function updateMonitorOptions() {
                var hostId = String($host.val() || '');
                var selectedMonitorIds = ($monitors.val() || []).map(String);

                if (hostId === currentEmployeeId) {
                    selectedMonitorIds = selectedMonitorIds.filter(function(id) {
                        return id !== currentEmployeeId;
                    });
                    $monitors.find('option[value="' + currentEmployeeId + '"]').prop('disabled', true);
                    $('#monitor-help').text('Optional. Select anyone except yourself to monitor this invitation.');
                } else {
                    $monitors.find('option[value="' + currentEmployeeId + '"]').prop('disabled', false);
                    if (previousHostId === currentEmployeeId && selectedMonitorIds.indexOf(currentEmployeeId) === -
                        1) {
                        selectedMonitorIds.push(currentEmployeeId);
                    }
                    $('#monitor-help').text(
                        'Optional. You are included by default; remove yourself or add any employees.');
                }

                $monitors.val(selectedMonitorIds).trigger('change.select2');
                $('#monitor-employees-group').toggle(Boolean(hostId));
                previousHostId = hostId;
            }

            $host.on('change', updateMonitorOptions);
            updateMonitorOptions();
        });
    </script>
@endsection

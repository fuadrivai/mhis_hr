@extends('layouts.main-layout')

@section('content-class')
    <link rel="stylesheet" href="/css/timeoff.css">
@endsection

@section('content-child')
    <div class="x_panel approval-form-panel">
        <div class="approval-form-body">
            <div class="approval-form-surface">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form id="approval-request-form" action="/time/request" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group row">
                        <label for="requester_employee_id" class="col-sm-3 col-form-label">Employee <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control select2" id="requester_employee_id" name="requester_employee_id"
                                required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    @if (isset($approvalRequest) && $approvalRequest->requester_employee_id == $employee->id)
                                        <option value="{{ $employee->id }}" selected>
                                            {{ optional($employee->personal)->fullname ?? 'Employee ' . $employee->id }}
                                        </option>
                                    @else
                                        <option value="{{ $employee->id }}">
                                            {{ optional($employee->personal)->fullname ?? 'Employee ' . $employee->id }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="timeoff_id" class="col-sm-3 col-form-label">Time Off Type <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control select2" id="timeoff_id" name="timeoff_id" required>
                                <option value="">Select timeoff type</option>
                                @foreach ($timeoffs as $timeoff)
                                    @if (isset($approvalRequest) && $approvalRequest->timeoff_id == $timeoff->id)
                                        <option value="{{ $timeoff->id }}" selected>{{ $timeoff->name }}</option>
                                    @else
                                        <option value="{{ $timeoff->id }}">{{ $timeoff->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">The selected timeoff determines the dynamic form
                                fields.</small>
                        </div>
                    </div>

                    <div id="dynamic-fields" class="mb-3">
                        <h5>Request Details</h5>
                        <div id="dynamic-field-container"></div>
                    </div>

                    <div class="form-group row">
                        <label for="attachments" class="col-sm-3 col-form-label">Attachment</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                            <small class="form-text text-muted">Upload related files for this approval request.</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="note" class="col-sm-3 col-form-label">Note</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3 approval-form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> Submit Approval Request
                            </button>
                            <a href="/setting/approval/request" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to list
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        const timeoffSchemas = @json(
            $timeoffs->mapWithKeys(function ($timeoff) {
                return [$timeoff->id => $timeoff->schema];
            }));

        $(document).ready(function() {
            $('#timeoff_id').change(function() {
                const selectedId = $(this).val();
                const schema = timeoffSchemas[selectedId] || [];
                renderDynamicFields(schema);
            });

            $('#dynamic-field-container input, #dynamic-field-container select, #dynamic-field-container textarea')
                .on('change input', function() {
                    updateConditionalFields();
                });

            if ($('#timeoff_id').val()) {
                $('#timeoff_id').trigger('change');
            }
        });

        function renderDynamicFields(schema) {
            const container = $('#dynamic-field-container');
            container.empty();

            if (!schema || !schema.length) {
                $('#dynamic-fields').hide();
                return;
            }

            schema.forEach(field => {
                const fieldId = 'field_' + field.name;
                const isRequired = field.required ? 'required' : '';
                const conditionalClass = field.show_if ? ' d-none' : '';
                let fieldHtml =
                    `<div class="form-group row dynamic-field-group${conditionalClass}" data-field-name="${field.name}"`;

                if (field.show_if) {
                    fieldHtml += ` data-show-if='${JSON.stringify(field.show_if)}'`;
                }

                fieldHtml +=
                    `>
                        <label for="${fieldId}" class="col-sm-3 col-form-label">${field.label}${field.required ? ' <span class="text-danger">*</span>' : ''}</label>
                        <div class="col-sm-9">
                            <div class="dynamic-input-wrap">`;

                switch (field.type) {
                    case 'textarea':
                        fieldHtml +=
                            `<textarea class="form-control" id="${fieldId}" name="dynamic_fields[${field.name}]" rows="3" ${isRequired}></textarea>`;
                        break;
                    case 'select':
                        fieldHtml +=
                            `<select class="form-control" id="${fieldId}" name="dynamic_fields[${field.name}]" ${isRequired}><option value="">Select ${field.label}</option>`;
                        field.options.forEach(option => {
                            fieldHtml += `<option value="${option}">${option}</option>`;
                        });
                        fieldHtml += `</select>`;
                        break;
                    case 'radio':
                        field.options.forEach((option, index) => {
                            fieldHtml += `<div class="form-check">
                                    <input class="form-check-input" type="radio" name="dynamic_fields[${field.name}]" id="${fieldId}_${index}" value="${option}" ${isRequired}>
                                    <label class="form-check-label" for="${fieldId}_${index}">${option}</label>
                                </div>`;
                        });
                        break;
                    case 'checkbox':
                        field.options.forEach((option, index) => {
                            fieldHtml += `<div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dynamic_fields[${field.name}][]" id="${fieldId}_${index}" value="${option}">
                                    <label class="form-check-label" for="${fieldId}_${index}">${option}</label>
                                </div>`;
                        });
                        break;
                    case 'date':
                    case 'time':
                    case 'number':
                    default:
                        fieldHtml +=
                            `<input type="${field.type}" class="form-control" id="${fieldId}" name="dynamic_fields[${field.name}]" ${isRequired}>`;
                        break;
                }

                fieldHtml += `</div></div></div>`;
                container.append(fieldHtml);
            });

            $('#dynamic-fields').show();
            updateConditionalFields();
        }

        function updateConditionalFields() {
            $('.dynamic-field-group').each(function() {
                const $group = $(this);
                const showIfData = $group.attr('data-show-if');

                if (!showIfData) {
                    return;
                }

                let showIf;
                try {
                    showIf = JSON.parse(showIfData);
                } catch (e) {
                    showIf = null;
                }

                if (!showIf) {
                    return;
                }

                const targetName = `dynamic_fields[${showIf.field}]`;
                const targetCheckboxName = `dynamic_fields[${showIf.field}][]`;
                const $targetRadio = $(`[name="${targetName}"]`);
                const $targetCheckbox = $(`[name="${targetCheckboxName}"]`);
                let targetValue = '';

                if ($targetRadio.is(':radio')) {
                    targetValue = $(`input[name="${targetName}"]:checked`).val() || '';
                } else if ($targetCheckbox.length > 0) {
                    const values = $(`input[name="${targetCheckboxName}"]:checked`).map(function() {
                        return $(this).val();
                    }).get();
                    targetValue = values.join(',');
                } else {
                    targetValue = $targetRadio.val() || '';
                }

                const shouldShow = String(targetValue) === String(showIf.value);
                if (shouldShow) {
                    $group.removeClass('d-none');
                } else {
                    $group.addClass('d-none');
                    $group.find('input, textarea, select').val('');
                    $group.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
                }
            });
        }
    </script>
@endsection

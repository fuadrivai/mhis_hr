@extends('layouts.main-layout')

@section('content-class')
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-12">
                    <form id="approval-form" method="POST"
                        action="{{ isset($timeOff) ? route('timeoff.update', $timeOff->id) : route('timeoff.store') }}">
                        @csrf
                        @if (isset($timeOff))
                            @method('PUT')
                        @endif
                        <input type="hidden" name="id" id="id" value="{{ $timeOff->id ?? '' }}">
                        <!-- Code Field -->
                        <div class="form-group row">
                            <label for="code" class="col-sm-3 col-form-label">Code <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="code" name="code"
                                    value="{{ $timeOff->code ?? '' }}" required>
                                <small class="form-text text-muted">Unique code for the approval request type.</small>
                            </div>
                        </div>

                        <!-- Name Field -->
                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-form-label">Name <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $timeOff->name ?? '' }}" required>
                                <small class="form-text text-muted">Display name for the approval request type.</small>
                            </div>
                        </div>

                        <!-- Schema Builder -->
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Schema Builder</label>
                            <div class="col-sm-9">
                                <div id="schema-builder" class="border p-3 bg-light">
                                    <h5>Form Fields</h5>
                                    <button type="button" class="btn btn-success btn-sm mb-3" id="add-field-btn">
                                        <i class="fa fa-plus"></i> Add Field
                                    </button>
                                    <div id="fields-container">
                                    </div>
                                </div>
                                <input type="hidden" name="schema" id="schema-input">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="button" class="btn btn-info" id="preview-btn">
                                    <i class="fa fa-eye"></i> Preview Form
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Adding/Editing Field -->
    <div class="modal fade" id="field-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Field</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="field-label">Label</label>
                        <input type="text" class="form-control" id="field-label" required>
                        <small class="form-text text-muted">Display label for the field</small>
                    </div>
                    <div class="form-group">
                        <label for="field-name">Name</label>
                        <input readonly type="text" class="form-control" id="field-name" required>
                        <small class="form-text text-muted">Field name (used as identifier)</small>
                    </div>
                    <div class="form-group">
                        <label for="field-type">Type</label>
                        <select class="form-control" id="field-type" required>
                            <option value="text">Text Input</option>
                            <option value="textarea">Textarea</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="radio">Radio Button</option>
                            <option value="select">Select Dropdown</option>
                            <option value="date">Date Picker</option>
                            <option value="time">Time Picker</option>
                            <option value="number">Number Input</option>
                        </select>
                    </div>
                    <div class="form-group" id="options-group" style="display: none;">
                        <label for="field-options">Options (one per line)</label>
                        <textarea class="form-control" id="field-options" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="field-required">
                        <label class="form-check-label" for="field-required">Required</label>
                    </div>
                    <!-- Conditional Visibility -->
                    <div class="form-group">
                        <label>Conditional Visibility (Optional)</label>
                        <div class="row">
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="show-if-field" placeholder="Field name">
                            </div>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="show-if-value" placeholder="Value">
                            </div>
                        </div>
                        <small class="form-text text-muted">Show this field only when the specified field has the specified
                            value</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-field-btn">Save Field</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        let fields = @json($timeOff->schema ?? []);
        let editingIndex = -1;
        $(document).ready(function() {
            // Load existing fields if editing
            if (fields.length > 0) {
                renderFields();
                updateSchemaInput();
            }

            // Check for copied data
            const copiedData = sessionStorage.getItem('copy_timeoff_data');
            if (copiedData && !{{ isset($timeOff) ? 'true' : 'false' }}) {
                const data = JSON.parse(copiedData);
                $('#code').val(data.code);
                $('#name').val(data.name);
                if (data.schema && Array.isArray(data.schema)) {
                    fields = data.schema;
                    renderFields();
                    updateSchemaInput();
                }
                // Clear the copied data
                sessionStorage.removeItem('copy_timeoff_data');
            }

            $('#add-field-btn').click(function() {
                editingIndex = -1;
                $('#field-modal').modal('show');
                resetModal();
            });

            $('#field-type').change(function() {
                const type = $(this).val();
                if (type === 'radio' || type === 'select' || type === 'checkbox') {
                    $('#options-group').show();
                } else {
                    $('#options-group').hide();
                }
            });

            $('#save-field-btn').click(function() {
                const name = $('#field-name').val();
                const label = $('#field-label').val();
                const type = $('#field-type').val();
                const required = $('#field-required').is(':checked');
                const options = $('#field-options').val().split('\n').filter(opt => opt.trim() !== '');
                const showIfField = $('#show-if-field').val().trim();
                const showIfValue = $('#show-if-value').val().trim();

                if (!name) {
                    alert('Name is required');
                    return;
                }
                if (!label) {
                    alert('Label is required');
                    return;
                }

                const field = {
                    name,
                    label,
                    type,
                    required,
                    options: type === 'radio' || type === 'select' || type === 'checkbox' ? options : []
                };

                if (showIfField && showIfValue) {
                    field.show_if = {
                        field: showIfField,
                        value: showIfValue
                    };
                }

                if (editingIndex >= 0) {
                    fields[editingIndex] = field;
                } else {
                    fields.push(field);
                }

                renderFields();
                $('#field-modal').modal('hide');
                updateSchemaInput();
            });

            $(document).on('click', '.edit-field', function() {
                editingIndex = $(this).data('index');
                const field = fields[editingIndex];
                $('#field-name').val(field.name || '');
                $('#field-label').val(field.label);
                $('#field-type').val(field.type);
                $('#field-required').prop('checked', field.required);
                if (field.options && field.options.length > 0) {
                    $('#field-options').val(field.options.join('\n'));
                    $('#options-group').show();
                } else {
                    $('#field-options').val('');
                    $('#options-group').hide();
                }
                if (field.show_if) {
                    $('#show-if-field').val(field.show_if.field);
                    $('#show-if-value').val(field.show_if.value);
                } else {
                    $('#show-if-field').val('');
                    $('#show-if-value').val('');
                }
                $('#field-modal').modal('show');
            });

            $(document).on('click', '.delete-field', function() {
                const index = $(this).data('index');
                fields.splice(index, 1);
                renderFields();
                updateSchemaInput();
            });
            $('#approval-form').submit(function(e) {
                updateSchemaInput();
            });

            $('#field-label').on('keyup change', function() {
                const label = $(this).val();
                const name = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
                $('#field-name').val(name);
            });

            $('#preview-btn').click(function() {
                updateSchemaInput();
                const schema = $('#schema-input').val();
                const previewUrl = '/setting/timeoff/preview?schema=' + encodeURIComponent(schema);
                window.open(previewUrl, '_blank');
            });
        });


        function updateSchemaInput() {
            $('#schema-input').val(JSON.stringify(fields));
        }

        function resetModal() {
            $('#field-name').val('');
            $('#field-label').val('');
            $('#field-type').val('text');
            $('#field-required').prop('checked', false);
            $('#field-options').val('');
            $('#options-group').hide();
            $('#show-if-field').val('');
            $('#show-if-value').val('');
        }

        function renderFields() {
            $('#fields-container').empty();
            fields.forEach((field, index) => {
                const conditionalText = field.show_if ?
                    `<br><small class="text-info">Show if ${field.show_if.field} = ${field.show_if.value}</small>` :
                    '';
                const fieldHtml = `
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${field.label}</strong> (${field.name}) - ${field.type} ${field.required ? '<span class="text-danger">*</span>' : ''}
                            ${field.options && field.options.length > 0 ? '<br><small>Options: ' + field.options.join(', ') + '</small>' : ''}
                            ${conditionalText}
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary edit-field" data-index="${index}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-field" data-index="${index}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
                $('#fields-container').append(fieldHtml);
            });
        }
    </script>
@endsection

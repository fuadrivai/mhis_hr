@extends('layouts.main-layout')

@section('content-class')
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-12">
                    <form id="approval-form" method="POST" action="/setting/approval-request-type">
                        @csrf
                        <input type="hidden" name="id" id="id">

                        <!-- Code Field -->
                        <div class="form-group row">
                            <label for="code" class="col-sm-3 col-form-label">Code <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="code" name="code" required>
                                <small class="form-text text-muted">Unique code for the approval request type.</small>
                            </div>
                        </div>

                        <!-- Name Field -->
                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-form-label">Name <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="name" name="name" required>
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
                                        <!-- Dynamically added fields will appear here -->
                                    </div>
                                </div>
                                <input type="hidden" name="schema" id="schema-input">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
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
        $(document).ready(function() {
            let fields = [];
            let editingIndex = -1;

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
                const label = $('#field-label').val();
                const type = $('#field-type').val();
                const required = $('#field-required').is(':checked');
                const options = $('#field-options').val().split('\n').filter(opt => opt.trim() !== '');

                if (!label) {
                    alert('Label is required');
                    return;
                }

                const field = {
                    label,
                    type,
                    required,
                    options: type === 'radio' || type === 'select' || type === 'checkbox' ? options : []
                };

                if (editingIndex >= 0) {
                    fields[editingIndex] = field;
                } else {
                    fields.push(field);
                }

                renderFields();
                $('#field-modal').modal('hide');
                updateSchemaInput();
            });

            function resetModal() {
                $('#field-label').val('');
                $('#field-type').val('text');
                $('#field-required').prop('checked', false);
                $('#field-options').val('');
                $('#options-group').hide();
            }

            function renderFields() {
                $('#fields-container').empty();
                fields.forEach((field, index) => {
                    const fieldHtml = `
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${field.label}</strong> (${field.type}) ${field.required ? '<span class="text-danger">*</span>' : ''}
                            ${field.options.length > 0 ? '<br><small>Options: ' + field.options.join(', ') + '</small>' : ''}
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

            $(document).on('click', '.edit-field', function() {
                editingIndex = $(this).data('index');
                const field = fields[editingIndex];
                $('#field-label').val(field.label);
                $('#field-type').val(field.type);
                $('#field-required').prop('checked', field.required);
                if (field.options.length > 0) {
                    $('#field-options').val(field.options.join('\n'));
                    $('#options-group').show();
                }
                $('#field-modal').modal('show');
            });

            $(document).on('click', '.delete-field', function() {
                const index = $(this).data('index');
                fields.splice(index, 1);
                renderFields();
                updateSchemaInput();
            });

            function updateSchemaInput() {
                $('#schema-input').val(JSON.stringify(fields));
            }

            $('#approval-form').submit(function(e) {
                updateSchemaInput();
            });
        });
    </script>
@endsection

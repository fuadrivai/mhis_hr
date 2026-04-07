@extends('layouts.main-layout')

@section('content-class')
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <strong>Preview Mode:</strong> This is how the form will appear to users when they fill it out.
                    </div>

                    <form id="preview-form">
                        @csrf
                        <div id="dynamic-fields">
                            @foreach ($fields as $field)
                                <div class="form-group row field-container" data-field-name="{{ $field['name'] }}"
                                    {{ isset($field['show_if']) ? 'style="display: none;" data-show-if=\'' . json_encode($field['show_if']) . '\'' : '' }}>
                                    <label for="field-{{ $field['name'] }}" class="col-sm-3 col-form-label">
                                        {{ $field['label'] }}
                                        @if (isset($field['required']) && $field['required'])
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <div class="col-sm-9">
                                        @switch($field['type'])
                                            @case('text')
                                                <input type="text" class="form-control" id="field-{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                            @break

                                            @case('textarea')
                                                <textarea class="form-control" id="field-{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3"
                                                    {{ isset($field['required']) && $field['required'] ? 'required' : '' }}></textarea>
                                            @break

                                            @case('number')
                                                <input type="number" class="form-control" id="field-{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                            @break

                                            @case('date')
                                                <input type="date" class="form-control" id="field-{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                            @break

                                            @case('time')
                                                <input type="time" class="form-control" id="field-{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                            @break

                                            @case('checkbox')
                                                @if (isset($field['options']) && is_array($field['options']))
                                                    @foreach ($field['options'] as $option)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="field-{{ $field['name'] }}-{{ $loop->index }}"
                                                                name="{{ $field['name'] }}[]" value="{{ $option }}">
                                                            <label class="form-check-label"
                                                                for="field-{{ $field['name'] }}-{{ $loop->index }}">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @break

                                            @case('radio')
                                                @if (isset($field['options']) && is_array($field['options']))
                                                    @foreach ($field['options'] as $option)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                id="field-{{ $field['name'] }}-{{ $loop->index }}"
                                                                name="{{ $field['name'] }}" value="{{ $option }}"
                                                                {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                                            <label class="form-check-label"
                                                                for="field-{{ $field['name'] }}-{{ $loop->index }}">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @break

                                            @case('select')
                                                <select class="form-control" id="field-{{ $field['name'] }}"
                                                    name="{{ $field['name'] }}"
                                                    {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                                    <option value="">Select {{ $field['label'] }}</option>
                                                    @if (isset($field['options']) && is_array($field['options']))
                                                        @foreach ($field['options'] as $option)
                                                            <option value="{{ $option }}">{{ $option }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @break
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="fa fa-arrow-left"></i> Back to Form Builder
                                </button>
                                <button type="submit" class="btn btn-success" disabled>
                                    <i class="fa fa-save"></i> Submit (Preview Only)
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(document).ready(function() {
            // Handle conditional field visibility
            function updateConditionalFields() {
                console.log('Updating conditional fields...');
                $('.field-container').each(function() {
                    const $field = $(this);
                    const showIfData = $field.attr('data-show-if');

                    if (showIfData) {
                        try {
                            const showIf = JSON.parse(showIfData);
                            console.log('Field:', $field.data('field-name'), 'showIf:', showIf);

                            const targetField = $('[name="' + showIf.field + '"]');
                            let targetValue = '';

                            if (targetField.length > 0) {
                                if (targetField.is('input[type="radio"]')) {
                                    targetValue = $('input[name="' + showIf.field + '"]:checked').val() ||
                                        '';
                                } else if (targetField.is('input[type="checkbox"]')) {
                                    // For checkboxes, check if any are checked
                                    const checkedValues = $('input[name="' + showIf.field + '"]:checked')
                                        .map(function() {
                                            return $(this).val();
                                        }).get();
                                    targetValue = checkedValues.length > 0 ? checkedValues.join(',') : '';
                                } else {
                                    targetValue = targetField.val() || '';
                                }
                            }

                            console.log('Target field:', showIf.field, 'Target value:', targetValue,
                                'Expected:', showIf.value);
                            const shouldShow = targetValue === showIf.value;

                            if (shouldShow) {
                                console.log('Showing field:', $field.data('field-name'));
                                $field.show();
                            } else {
                                console.log('Hiding field:', $field.data('field-name'));
                                $field.hide();
                                // Clear the field value when hidden
                                $field.find('input, textarea, select').val('');
                                $field.find('input[type="checkbox"], input[type="radio"]').prop('checked',
                                    false);
                            }
                        } catch (e) {
                            console.error('Error parsing showIf data:', showIfData, e);
                        }
                    }
                });
            }

            // Update conditional fields on input change
            $(document).on('change', 'input, select, textarea', function() {
                updateConditionalFields();
            });

            // Initial check for conditional fields
            updateConditionalFields();

            // Prevent form submission in preview mode
            $('#preview-form').on('submit', function(e) {
                e.preventDefault();
                alert('This is a preview. Form submission is disabled.');
            });
        });
    </script>
@endsection

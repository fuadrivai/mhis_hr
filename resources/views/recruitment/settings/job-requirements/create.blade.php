@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>{{ isset($jobRequirement) ? 'Edit Job Requirement' : 'Add Job Requirement' }}</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                    action="{{ isset($jobRequirement) ? route('recruitment.settings.job-requirements.update', $jobRequirement) : route('recruitment.settings.job-requirements.store') }}">
                    @csrf
                    @if (isset($jobRequirement))
                        @method('PUT')
                    @endif
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input id="title" name="title" class="form-control" maxlength="255" required
                            value="{{ old('title', $jobRequirement->title ?? '') }}" placeholder="e.g. Primary Teacher, Staff, Secondary Teacher">
                    </div>
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description', $jobRequirement->description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Available Positions / Open Roles <small class="text-muted">(Optional - e.g. Finance Staff, IT Staff, or specific subjects like English, Mathematics)</small></label>
                        <div id="positionFields">
                            @php
                                $existingPositions = old('positions', isset($jobRequirement) && $jobRequirement->positions->isNotEmpty() ? $jobRequirement->positions->pluck('name')->all() : ['']);
                            @endphp
                            @foreach ($existingPositions as $positionName)
                                <div class="input-group position-field" style="margin-bottom: 8px;">
                                    <input name="positions[]" class="form-control" maxlength="255"
                                        value="{{ $positionName }}" placeholder="Enter an available position or role (e.g. Finance Staff, English)">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-danger remove-position"
                                            aria-label="Remove position">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="addPosition" class="btn btn-default btn-sm">
                            <i class="fa fa-plus"></i> Add Position / Role
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Requirements Criteria *</label>
                        <p class="text-muted" style="margin-bottom: 10px; font-size: 12px;">
                            Configure individual criteria for AI CV matching. Specify whether each requirement is <strong>Mandatory</strong> (missing it caps candidate score at 60) and its <strong>Weight</strong> (importance from 1 to 5).
                        </p>
                        <div id="requirementFields">
                            @php
                                $categories = [
                                    'education' => 'Education',
                                    'experience' => 'Experience',
                                    'skill' => 'Skill',
                                    'certification' => 'Certification',
                                    'language' => 'Language',
                                    'location' => 'Location',
                                    'other' => 'Other',
                                ];

                                $rawItems = old('requirements');
                                if (!is_array($rawItems)) {
                                    if (isset($jobRequirement) && $jobRequirement->details->isNotEmpty()) {
                                        $items = [];
                                        foreach ($jobRequirement->details as $d) {
                                            $items[] = [
                                                'text' => $d->requirement,
                                                'category' => $d->category ?? 'other',
                                                'weight' => $d->weight ?? 3,
                                                'is_mandatory' => $d->is_mandatory ? 1 : 0,
                                            ];
                                        }
                                    } else {
                                        $items = [
                                            ['text' => '', 'category' => 'education', 'weight' => 3, 'is_mandatory' => 0]
                                        ];
                                    }
                                } else {
                                    $items = [];
                                    foreach ($rawItems as $k => $v) {
                                        if (is_array($v)) {
                                            $items[] = [
                                                'text' => $v['text'] ?? ($v['requirement'] ?? ''),
                                                'category' => $v['category'] ?? 'other',
                                                'weight' => $v['weight'] ?? 3,
                                                'is_mandatory' => !empty($v['is_mandatory']) ? 1 : 0,
                                            ];
                                        } else {
                                            $items[] = [
                                                'text' => (string) $v,
                                                'category' => 'other',
                                                'weight' => 3,
                                                'is_mandatory' => 0,
                                            ];
                                        }
                                    }
                                }
                            @endphp

                            @foreach ($items as $idx => $item)
                                <div class="well well-sm requirement-row" style="margin-bottom: 10px; background: #fdfdfd; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
                                    <div class="row">
                                        <div class="col-md-6 form-group" style="margin-bottom: 5px;">
                                            <label style="font-size: 11px; text-transform: uppercase; color: #555;">Requirement Statement *</label>
                                            <input name="requirements[{{ $idx }}][text]" class="form-control input-sm req-text-input" maxlength="1000" required
                                                value="{{ $item['text'] }}" placeholder="e.g. Min. Bachelor degree in Education / English">
                                        </div>
                                        <div class="col-md-2 form-group" style="margin-bottom: 5px;">
                                            <label style="font-size: 11px; text-transform: uppercase; color: #555;">Category</label>
                                            <select name="requirements[{{ $idx }}][category]" class="form-control input-sm req-category-select">
                                                @foreach ($categories as $catKey => $catLabel)
                                                    <option value="{{ $catKey }}" {{ ($item['category'] ?? '') === $catKey ? 'selected' : '' }}>
                                                        {{ $catLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group" style="margin-bottom: 5px;">
                                            <label style="font-size: 11px; text-transform: uppercase; color: #555;">Weight</label>
                                            <select name="requirements[{{ $idx }}][weight]" class="form-control input-sm req-weight-select">
                                                <option value="1" {{ ($item['weight'] ?? 3) == 1 ? 'selected' : '' }}>1 (Low)</option>
                                                <option value="2" {{ ($item['weight'] ?? 3) == 2 ? 'selected' : '' }}>2 (Minor)</option>
                                                <option value="3" {{ ($item['weight'] ?? 3) == 3 ? 'selected' : '' }}>3 (Standard)</option>
                                                <option value="4" {{ ($item['weight'] ?? 3) == 4 ? 'selected' : '' }}>4 (Important)</option>
                                                <option value="5" {{ ($item['weight'] ?? 3) == 5 ? 'selected' : '' }}>5 (Critical)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group" style="margin-bottom: 5px;">
                                            <label style="font-size: 11px; text-transform: uppercase; color: #555;">Mandatory?</label>
                                            <div style="padding-top: 5px;">
                                                <input type="hidden" name="requirements[{{ $idx }}][is_mandatory]" value="0">
                                                <label style="font-weight: normal; cursor: pointer; margin-bottom: 0;">
                                                    <input type="checkbox" name="requirements[{{ $idx }}][is_mandatory]" value="1" class="req-mandatory-checkbox" {{ !empty($item['is_mandatory']) ? 'checked' : '' }}>
                                                    <span class="text-danger" style="font-weight: 600; font-size: 12px;">Must-Have</span>
                                                </label>
                                                <button type="button" class="btn btn-danger btn-xs pull-right remove-requirement" title="Remove requirement" style="margin-top: 1px;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="addRequirement" class="btn btn-default btn-sm">
                            <i class="fa fa-plus"></i> Add Requirement Criterion
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="footer">Footer</label>
                        <textarea id="footer" name="footer" class="form-control" rows="2">{{ old('footer', $jobRequirement->footer ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i>
                        {{ isset($jobRequirement) ? 'Update Job Requirement' : 'Save Job Requirement' }}
                    </button>
                    <a href="{{ route('recruitment.settings.job-requirements') }}" class="btn btn-default">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(function() {
            var reqIndex = {{ count($items) }};

            $('#addPosition').on('click', function() {
                $('#positionFields').append(
                    '<div class="input-group position-field" style="margin-bottom: 8px;">' +
                    '<input name="positions[]" class="form-control" maxlength="255" placeholder="Enter an available position or role (e.g. Finance Staff, English)">' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-danger remove-position" aria-label="Remove position"><i class="fa fa-trash"></i></button></span>' +
                    '</div>'
                );
            });

            $(document).on('click', '.remove-position', function() {
                if ($('.position-field').length > 1) {
                    $(this).closest('.position-field').remove();
                } else {
                    $(this).closest('.position-field').find('input').val('');
                }
            });

            $('#addRequirement').on('click', function() {
                var idx = reqIndex++;
                var html = `
                    <div class="well well-sm requirement-row" style="margin-bottom: 10px; background: #fdfdfd; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
                        <div class="row">
                            <div class="col-md-6 form-group" style="margin-bottom: 5px;">
                                <label style="font-size: 11px; text-transform: uppercase; color: #555;">Requirement Statement *</label>
                                <input name="requirements[${idx}][text]" class="form-control input-sm req-text-input" maxlength="1000" required placeholder="e.g. Min. 2 years experience teaching Cambridge curriculum">
                            </div>
                            <div class="col-md-2 form-group" style="margin-bottom: 5px;">
                                <label style="font-size: 11px; text-transform: uppercase; color: #555;">Category</label>
                                <select name="requirements[${idx}][category]" class="form-control input-sm req-category-select">
                                    <option value="education">Education</option>
                                    <option value="experience">Experience</option>
                                    <option value="skill">Skill</option>
                                    <option value="certification">Certification</option>
                                    <option value="language">Language</option>
                                    <option value="location">Location</option>
                                    <option value="other" selected>Other</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group" style="margin-bottom: 5px;">
                                <label style="font-size: 11px; text-transform: uppercase; color: #555;">Weight</label>
                                <select name="requirements[${idx}][weight]" class="form-control input-sm req-weight-select">
                                    <option value="1">1 (Low)</option>
                                    <option value="2">2 (Minor)</option>
                                    <option value="3" selected>3 (Standard)</option>
                                    <option value="4">4 (Important)</option>
                                    <option value="5">5 (Critical)</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group" style="margin-bottom: 5px;">
                                <label style="font-size: 11px; text-transform: uppercase; color: #555;">Mandatory?</label>
                                <div style="padding-top: 5px;">
                                    <input type="hidden" name="requirements[${idx}][is_mandatory]" value="0">
                                    <label style="font-weight: normal; cursor: pointer; margin-bottom: 0;">
                                        <input type="checkbox" name="requirements[${idx}][is_mandatory]" value="1" class="req-mandatory-checkbox">
                                        <span class="text-danger" style="font-weight: 600; font-size: 12px;">Must-Have</span>
                                    </label>
                                    <button type="button" class="btn btn-danger btn-xs pull-right remove-requirement" title="Remove requirement" style="margin-top: 1px;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#requirementFields').append(html);
            });

            $(document).on('click', '.remove-requirement', function() {
                if ($('.requirement-row').length > 1) {
                    $(this).closest('.requirement-row').remove();
                } else {
                    $(this).closest('.requirement-row').find('input.req-text-input').val('');
                }
            });
        });
    </script>
@endsection

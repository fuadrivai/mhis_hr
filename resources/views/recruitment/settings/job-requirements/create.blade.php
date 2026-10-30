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
                            value="{{ old('title', $jobRequirement->title ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description', $jobRequirement->description ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Requirements *</label>
                        <div id="requirementFields">
                            @foreach (old('requirements', isset($jobRequirement) ? $jobRequirement->details->pluck('requirement')->all() : ['']) as $requirement)
                                <div class="input-group requirement-field" style="margin-bottom: 8px;">
                                    <input name="requirements[]" class="form-control" maxlength="1000" required
                                        value="{{ $requirement }}" placeholder="Enter a job requirement">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-danger remove-requirement"
                                            aria-label="Remove requirement">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="addRequirement" class="btn btn-default btn-sm">
                            <i class="fa fa-plus"></i> Add Requirement
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
            $('#addRequirement').on('click', function() {
                $('#requirementFields').append(
                    '<div class="input-group requirement-field" style="margin-bottom: 8px;">' +
                    '<input name="requirements[]" class="form-control" maxlength="1000" required placeholder="Enter a job requirement">' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-danger remove-requirement" aria-label="Remove requirement"><i class="fa fa-trash"></i></button></span>' +
                    '</div>'
                );
            });

            $(document).on('click', '.remove-requirement', function() {
                if ($('.requirement-field').length > 1) {
                    $(this).closest('.requirement-field').remove();
                }
            });
        });
    </script>
@endsection

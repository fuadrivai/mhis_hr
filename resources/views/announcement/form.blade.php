@php
    $selectedBranches = collect(old('branches', []))->map(fn($value) => (string) $value)->all();
    $selectedOrganizations = collect(old('organizations', []))->map(fn($value) => (string) $value)->all();
    $selectedJobLevels = collect(old('job_levels', []))->map(fn($value) => (string) $value)->all();
    $selectedPositions = collect(old('positions', []))->map(fn($value) => (string) $value)->all();
    $allEmployees = old('all_employees', '1');
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Announcement Information</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title"
                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                    placeholder="Enter announcement title">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id"
                    class="form-select select2 @error('category_id') is-invalid @enderror" style="width: 100%">
                    <option value="">-- Select Category --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="publish_at" class="form-label">Publish At <span class="text-danger">*</span></label>
                <input type="text" name="publish_at" id="publish_at"
                    class="form-control datetimepicker @error('publish_at') is-invalid @enderror"
                    value="{{ old('publish_at', now()->format('Y-m-d H:i')) }}" placeholder="YYYY-MM-DD HH:mm"
                    autocomplete="off">
                @error('publish_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="link" class="form-label">Link</label>
                <input type="url" name="link" id="link"
                    class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}"
                    placeholder="https://example.com">
                @error('link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                <textarea name="content" id="content" rows="10" class="form-control @error('content') is-invalid @enderror"
                    placeholder="Write the announcement content here...">{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-md-row gap-4">
                    <div class="form-check mr-3">
                        <input type="hidden" name="send_email" value="0">
                        <input class="form-check-input" type="checkbox" value="1" id="send_email" name="send_email"
                            @checked(old('send_email', false))>
                        <label class="form-check-label" for="send_email">Send Email</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="send_push_notification" value="0">
                        <input class="form-check-input" type="checkbox" value="1" id="send_push_notification"
                            name="send_push_notification" @checked(old('send_push_notification', true))>
                        <label class="form-check-label" for="send_push_notification">Send Push Notification</label>
                    </div>
                </div>
                @error('send_email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('send_push_notification')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Audience</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="form-check">
                    <input class="form-check-input audience-option" type="radio" name="all_employees"
                        id="all_employees_yes" value="1" @checked($allEmployees == '1' || $allEmployees === 1 || $allEmployees === true)>
                    <label class="form-check-label" for="all_employees_yes">All Employees</label>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check">
                    <input class="form-check-input audience-option" type="radio" name="all_employees"
                        id="all_employees_no" value="0" @checked($allEmployees == '0' || $allEmployees === 0 || $allEmployees === false)>
                    <label class="form-check-label" for="all_employees_no">Custom Audience</label>
                </div>
            </div>
            @error('all_employees')
                <div class="col-12">
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                </div>
            @enderror
            @error('custom_audience')
                <div class="col-12">
                    <div class="alert alert-danger mb-0">{{ $message }}</div>
                </div>
            @enderror
        </div>

        <div id="customAudienceCard"
            class="custom-audience-wrapper {{ $allEmployees == '1' || $allEmployees === 1 || $allEmployees === true ? 'is-hidden' : '' }}">
            @include('announcement.partials.audience', [
                'selectedBranches' => $selectedBranches,
                'selectedOrganizations' => $selectedOrganizations,
                'selectedJobLevels' => $selectedJobLevels,
                'selectedPositions' => $selectedPositions,
            ])
        </div>
    </div>
</div>

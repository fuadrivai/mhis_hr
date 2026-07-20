@php
    $announcement = $announcement ?? null;
    $selectedBranches = collect(old('branches', $announcement ? $announcement->branches->pluck('id')->all() : []))
        ->map(fn($value) => (string) $value)
        ->all();
    $selectedOrganizations = collect(
        old('organizations', $announcement ? $announcement->organizations->pluck('id')->all() : []),
    )
        ->map(fn($value) => (string) $value)
        ->all();
    $selectedJobLevels = collect(old('job_levels', $announcement ? $announcement->jobLevels->pluck('id')->all() : []))
        ->map(fn($value) => (string) $value)
        ->all();
    $selectedPositions = collect(old('positions', $announcement ? $announcement->positions->pluck('id')->all() : []))
        ->map(fn($value) => (string) $value)
        ->all();
    $allEmployees = (string) old('all_employees', $announcement && !$announcement->all_employees ? '0' : '1');
    $selectedCategoryId = (string) old('category_id', $announcement->category_id ?? '');
    $sendEmail = (bool) old('send_email', $announcement->send_email ?? false);
    $sendPushNotification = (bool) old('send_push_notification', $announcement->send_push_notification ?? true);
    $attachmentPreview =
        $announcement && $announcement->attachment ? asset('storage/' . $announcement->attachment) : null;
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
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $announcement->title ?? '') }}" placeholder="Enter announcement title">
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
                        <option value="{{ $category->id }}"
                            {{ $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="attachment" class="form-label">Attachment</label>
                <input type="file" name="attachment" id="attachment"
                    class="form-control @error('attachment') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div id="attachmentPreviewWrapper" class="mt-3 {{ $attachmentPreview ? '' : 'd-none' }}">
                    <img id="attachmentPreview" src="{{ $attachmentPreview ?? '' }}" alt="Attachment preview"
                        class="img-fluid rounded border" style="max-height: 220px; object-fit: cover;">
                </div>
            </div>

            <div class="col-12">
                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                <textarea name="content" id="content" rows="10" class="form-control @error('content') is-invalid @enderror"
                    placeholder="Write the announcement content here...">{{ old('content', $announcement->content ?? '') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-md-row gap-4">
                    <div class="form-check mr-3">
                        <input type="hidden" name="send_email" value="0">
                        <input class="form-check-input" type="checkbox" value="1" id="send_email" name="send_email"
                            {{ $sendEmail ? 'checked' : '' }}>
                        <label class="form-check-label" for="send_email">Send Email</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="send_push_notification" value="0">
                        <input class="form-check-input" type="checkbox" value="1" id="send_push_notification"
                            name="send_push_notification" {{ $sendPushNotification ? 'checked' : '' }}>
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
                        id="all_employees_yes" value="1" {{ $allEmployees === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="all_employees_yes">All Employees</label>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check">
                    <input class="form-check-input audience-option" type="radio" name="all_employees"
                        id="all_employees_no" value="0" {{ $allEmployees === '0' ? 'checked' : '' }}>
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

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <label for="branches" class="form-label">Branches</label>
        <select name="branches[]" id="branches"
            class="form-select select2-multiple @error('branches') is-invalid @enderror" multiple="multiple"
            style="width: 100%">
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}">
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branches')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-lg-6">
        <label for="organizations" class="form-label">Organizations</label>
        <select name="organizations[]" id="organizations"
            class="form-select select2-multiple @error('organizations') is-invalid @enderror" multiple="multiple"
            style="width: 100%">
            @foreach ($organizations as $organization)
                <option value="{{ $organization->id }}">
                    {{ $organization->name }}
                </option>
            @endforeach
        </select>
        @error('organizations')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-lg-6">
        <label for="job_levels" class="form-label">Job Levels</label>
        <select name="job_levels[]" id="job_levels"
            class="form-select select2-multiple @error('job_levels') is-invalid @enderror" multiple="multiple"
            style="width: 100%">
            @foreach ($jobLevels as $jobLevel)
                <option value="{{ $jobLevel->id }}">
                    {{ $jobLevel->name }}
                </option>
            @endforeach
        </select>
        @error('job_levels')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-lg-6">
        <label for="positions" class="form-label">Positions</label>
        <select name="positions[]" id="positions"
            class="form-select select2-multiple @error('positions') is-invalid @enderror" multiple="multiple"
            style="width: 100%">
            @foreach ($positions as $position)
                <option value="{{ $position->id }}">
                    {{ $position->name }}
                </option>
            @endforeach
        </select>
        @error('positions')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

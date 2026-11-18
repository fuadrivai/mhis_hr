@extends('layouts.main-layout')

@section('content-child')
    <div class="x_panel">
        <div class="x_title">
            <h2>{{ $title }}</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST"
                action="{{ isset($group) ? route('setting.hourly-time-off.update', $group->id) : route('setting.hourly-time-off.store') }}">
                @csrf
                @if (isset($group))
                    @method('PUT')
                @endif
                <input type="hidden" name="is_active" value="0">
                <div class="form-group row">
                    <label for="name" class="col-sm-3 col-form-label">Name</label>
                    <div class="col-sm-9">
                        <input class="form-control" id="name" name="name" maxlength="255" required
                            value="{{ old('name', $group->name ?? 'Hourly Permission') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="code" class="col-sm-3 col-form-label">Code</label>
                    <div class="col-sm-9">
                        <input class="form-control" id="code" name="code" maxlength="255" required
                            value="{{ old('code', $group->code ?? 'HOURLY_PERMISSION') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="period_type" class="col-sm-3 col-form-label">Period</label>
                    <div class="col-sm-9">
                        <select class="form-control" id="period_type" name="period_type" required>
                            @foreach (['monthly' => 'Monthly', 'yearly' => 'Yearly', 'unlimited' => 'Unlimited'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('period_type', $group->period_type ?? 'monthly') === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row" id="maximum-hours-row">
                    <label for="maximum_hours" class="col-sm-3 col-form-label">Maximum Hours</label>
                    <div class="col-sm-9">
                        <input class="form-control" id="maximum_hours" name="maximum_hours" type="number" min="0.01"
                            step="0.01" value="{{ old('maximum_hours', $group->maximum_hours ?? '6.00') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Status</label>
                    <div class="col-sm-9">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', isset($group) ? $group->is_active : true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Time Off Types</label>
                    <div class="col-sm-9">
                        @php
                            $selectedTimeoffIds = old(
                                'timeoff_ids',
                                isset($group) ? $group->timeoffs->pluck('id')->all() : [],
                            );
                        @endphp
                        @forelse ($timeoffs as $timeoff)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="timeoff-{{ $timeoff->id }}"
                                    name="timeoff_ids[]" value="{{ $timeoff->id }}"
                                    {{ in_array($timeoff->id, $selectedTimeoffIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="timeoff-{{ $timeoff->id }}">{{ $timeoff->name }}
                                    <small class="text-muted">({{ $timeoff->code }})</small></label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No active Time Off types are available.</p>
                        @endforelse
                        <small class="form-text text-muted">A Time Off type can belong to one balance group.</small>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                        <a href="{{ route('setting.hourly-time-off.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(function() {
            function toggleMaximumHours() {
                const unlimited = $('#period_type').val() === 'unlimited';
                $('#maximum-hours-row').toggle(!unlimited);
                $('#maximum_hours').prop('required', !unlimited);
            }
            $('#period_type').on('change', toggleMaximumHours);
            toggleMaximumHours();
        });
    </script>
@endsection

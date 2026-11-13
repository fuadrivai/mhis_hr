@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title"><h2>Create Visitor Invitation</h2><div class="clearfix"></div></div>
            <div class="x_content">
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('visitor.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Visitor full name *</label>
                            <input name="full_name" class="form-control" value="{{ old('full_name') }}" required maxlength="255">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Phone</label>
                            <input name="phone" class="form-control" value="{{ old('phone') }}" maxlength="40">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Company / Organization</label>
                            <input name="company" class="form-control" value="{{ old('company') }}" maxlength="255">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Visitor category *</label>
                            <select name="visitor_category_id" class="form-control" required>
                                <option value="">Select category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('visitor_category_id') == $category->id)>{{ $category->name }}{{ $category->requires_approval ? ' (approval required)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Visit purpose *</label>
                            <select name="visitor_purpose_id" class="form-control" required>
                                <option value="">Select purpose</option>
                                @foreach ($purposes as $purpose)
                                    <option value="{{ $purpose->id }}" @selected(old('visitor_purpose_id') == $purpose->id)>{{ $purpose->name }}{{ $purpose->requires_approval ? ' (approval required)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Host employee *</label>
                            <select name="host_employee_id" class="form-control select2" style="width: 100%" required>
                                @foreach ($hosts as $host)
                                    <option value="{{ $host->id }}" @selected(old('host_employee_id', $defaultHostId) == $host->id)>{{ $host->user?->name ?? 'Employee #' . $host->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Branch (meeting location) *</label>
                            <select name="branch_id" class="form-control" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id', $defaultBranchId) == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Visit date *</label>
                            <input type="text" id="visit-date-picker" class="form-control date-picker" data-date-format="dd MM yyyy" data-date-language="en" data-date-start-date="{{ now()->format('d F Y') }}" value="{{ \Carbon\Carbon::parse(old('visit_date', now()->toDateString()))->format('d F Y') }}" autocomplete="off" readonly required>
                            <input type="hidden" id="visit-date-value" name="visit_date" value="{{ old('visit_date', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Expected arrival time *</label>
                            <input type="text" id="arrival-time-picker" name="expected_arrival_time" class="form-control time-picker" value="{{ old('expected_arrival_time') }}" autocomplete="off" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Expected departure date (optional)</label>
                            <input type="text" id="departure-date-picker" name="expected_departure_date" class="form-control date-picker" data-date-format="yyyy-mm-dd" data-date-language="en" value="{{ old('expected_departure_date') }}" autocomplete="off" readonly>
                            <small class="text-muted">Only if different from visit date.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Expected departure time *</label>
                            <input type="text" id="departure-time-picker" name="expected_departure_time" class="form-control time-picker" value="{{ old('expected_departure_time') }}" autocomplete="off" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Visitor photo (optional)</label>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Visit notes</label>
                            <textarea name="notes" class="form-control" rows="3" maxlength="3000">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <p class="text-muted">An approval request will be sent to the host when the selected category or purpose requires approval.</p>
                    <button class="btn btn-success" type="submit"><i class="fa fa-send"></i> Submit Invitation</button>
                    <a class="btn btn-default" href="{{ route('visitor.index') }}">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title"><h2>Create Guest Registration Links</h2><div class="clearfix"></div></div>
            <div class="x_content">
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('visitor.invitation-links.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Batch / event name *</label>
                            <input name="name" class="form-control" value="{{ old('name') }}" maxlength="255" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Host employee *</label>
                            <select name="host_employee_id" class="form-control select2" style="width:100%" required>
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
                        <div class="col-md-6 form-group">
                            <label>Visitor category *</label>
                            <select name="visitor_category_id" class="form-control" required>
                                <option value="">Select category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('visitor_category_id') == $category->id)>{{ $category->name }}{{ $category->requires_approval ? ' (approval required)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Visit purpose *</label>
                            <select name="visitor_purpose_id" class="form-control" required>
                                <option value="">Select purpose</option>
                                @foreach ($purposes as $purpose)
                                    <option value="{{ $purpose->id }}" @selected(old('visitor_purpose_id') == $purpose->id)>{{ $purpose->name }}{{ $purpose->requires_approval ? ' (approval required)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Visit date *</label>
                            <input type="date" id="batch-visit-date" name="visit_date" class="form-control" value="{{ old('visit_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Arrival time *</label>
                            <input type="time" name="expected_arrival_time" class="form-control" value="{{ old('expected_arrival_time') }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Departure date (optional)</label>
                            <input type="date" id="batch-departure-date" name="expected_departure_date" class="form-control" value="{{ old('expected_departure_date') }}" min="{{ old('visit_date', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Departure time *</label>
                            <input type="time" name="expected_departure_time" class="form-control" value="{{ old('expected_departure_time') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" maxlength="3000">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Link expiry date (optional)</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}" min="{{ now()->toDateString() }}" max="{{ old('visit_date', now()->toDateString()) }}">
                            <small class="text-muted">Defaults to the visit date.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Additional links to share yourself</label>
                            <input type="number" name="share_link_count" class="form-control" value="{{ old('share_link_count', 0) }}" min="0" max="100" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email recipients (one email per line, optional)</label>
                            <textarea name="recipient_emails_input" class="form-control" rows="3" maxlength="20000" placeholder="guest1@example.com&#10;guest2@example.com">{{ old('recipient_emails_input') }}</textarea>
                            <small class="text-muted">Each address gets a separate link by email. A batch can contain up to 100 links total.</small>
                        </div>
                    </div>
                    <p class="text-muted">Guests can only enter their name, phone, email, and company. Each link can be used once.</p>
                    <button class="btn btn-success" type="submit"><i class="fa fa-link"></i> Create Links</button>
                    <a class="btn btn-default" href="{{ route('visitor.invitation-links.index') }}">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(function () {
            $('#batch-visit-date').on('change', function () {
                var visitDate = $(this).val();
                var $departureDate = $('#batch-departure-date');
                $departureDate.attr('min', visitDate);
                $('input[name="expires_at"]').attr('max', visitDate);
                if ($departureDate.val() && $departureDate.val() < visitDate) {
                    $departureDate.val('');
                }
            });
            $('#batch-visit-date').trigger('change');
        });
    </script>
@endsection

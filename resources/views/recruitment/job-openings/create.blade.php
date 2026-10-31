@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <ul style="margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>Create Job Opening <small>Set up a vacancy to start accepting candidates</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.job-openings.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Openings
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="{{ route('recruitment.job-openings.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="job_requirement_id">
                                Job Requirement Template <small class="text-muted">(Optional — selecting one will auto-fill title & position below)</small>
                            </label>
                            <select name="job_requirement_id" id="job_requirement_id" class="form-control select2">
                                <option value="">-- None (Create without template) --</option>
                                @foreach ($jobRequirements as $req)
                                    <option value="{{ $req->id }}"
                                        data-title="{{ $req->title }}"
                                        data-position-id="{{ $req->positions->first()?->position_id ?? '' }}"
                                        @selected((string) old('job_requirement_id') === (string) $req->id)>
                                        {{ $req->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="title">Opening Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" required class="form-control" placeholder="e.g. Primary School English Teacher">
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="position_id">Master Position</label>
                            <select name="position_id" id="position_id" class="form-control select2">
                                <option value="">-- Select Master Position --</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->id }}" @selected((string) old('position_id') === (string) $pos->id)>
                                        {{ $pos->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="division">Division / Department</label>
                            <input type="text" id="division" name="division" value="{{ old('division') }}" class="form-control" placeholder="e.g. Academic, Operations, Finance">
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="branch_id">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" required class="form-control select2">
                                <option value="">-- Select Branch --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="headcount">Headcount Quota <span class="text-danger">*</span></label>
                            <input type="number" id="headcount" name="headcount" value="{{ old('headcount', 1) }}" min="1" required class="form-control">
                            <span class="help-block" style="margin-bottom: 0;">When joined candidates reach this quota, the opening automatically marks as Filled.</span>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="hiring_manager_employee_id">Hiring Manager</label>
                            <select name="hiring_manager_employee_id" id="hiring_manager_employee_id" class="form-control select2">
                                <option value="">-- Select Hiring Manager --</option>
                                @foreach ($hiringManagers as $emp)
                                    <option value="{{ $emp->id }}" @selected((string) old('hiring_manager_employee_id') === (string) $emp->id)>
                                        {{ $emp->personal?->fullname ?: ($emp->user?->name ?: 'Employee #' . $emp->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="opened_at">Opening Date <span class="text-danger">*</span></label>
                            <input type="date" id="opened_at" name="opened_at" value="{{ old('opened_at', date('Y-m-d')) }}" required class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="closes_at">Closing Date <small class="text-muted">(Optional)</small></label>
                            <input type="date" id="closes_at" name="closes_at" value="{{ old('closes_at') }}" class="form-control">
                            <span class="help-block" style="margin-bottom: 0;">Leave blank to stay open indefinitely.</span>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="status">Initial Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" required class="form-control">
                                <option value="open" @selected(old('status', 'open') === 'open')>Open (Accepting applicants immediately)</option>
                                <option value="draft" @selected(old('status') === 'draft')>Draft (Hidden from public & candidates)</option>
                            </select>
                        </div>
                    </div>

                    <div class="ln_solid" style="margin-top: 15px;"></div>
                    <div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Job Opening</button>
                        <a href="{{ route('recruitment.job-openings.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({ width: '100%' });
            }

            $('#job_requirement_id').on('change', function() {
                var selectedOption = $(this).find(':selected');
                var reqTitle = selectedOption.data('title');
                var positionId = selectedOption.data('position-id');

                if (reqTitle && !$('#title').val()) {
                    $('#title').val(reqTitle);
                }
                if (positionId && !$('#position_id').val()) {
                    $('#position_id').val(positionId).trigger('change');
                }
            });
        });
    </script>
@endsection

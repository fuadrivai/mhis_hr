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
                <h2>Edit Job Opening <small>{{ $opening->title }}</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.job-openings.show', $opening) }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> View Opening
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="{{ route('recruitment.job-openings.update', $opening) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="job_requirement_id">Job Requirement Template</label>
                            <select name="job_requirement_id" id="job_requirement_id" class="form-control select2">
                                <option value="">-- None (No template linked) --</option>
                                @foreach ($jobRequirements as $req)
                                    <option value="{{ $req->id }}" @selected((string) old('job_requirement_id', $opening->job_requirement_id) === (string) $req->id)>
                                        {{ $req->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="title">Opening Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title', $opening->title) }}" required class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="position_id">Master Position</label>
                            <select name="position_id" id="position_id" class="form-control select2">
                                <option value="">-- Select Master Position --</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->id }}" @selected((string) old('position_id', $opening->position_id) === (string) $pos->id)>
                                        {{ $pos->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="division">Division / Department</label>
                            <input type="text" id="division" name="division" value="{{ old('division', $opening->division) }}" class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="branch_id">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" required class="form-control select2">
                                <option value="">-- Select Branch --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $opening->branch_id) === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="headcount">Headcount Quota <span class="text-danger">*</span></label>
                            <input type="number" id="headcount" name="headcount" value="{{ old('headcount', $opening->headcount) }}" min="1" required class="form-control">
                            <span class="help-block" style="margin-bottom: 0;">Currently filled: <strong>{{ $opening->filled_count }}</strong> joined candidate(s).</span>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="hiring_manager_employee_id">Hiring Manager</label>
                            <select name="hiring_manager_employee_id" id="hiring_manager_employee_id" class="form-control select2">
                                <option value="">-- Select Hiring Manager --</option>
                                @foreach ($hiringManagers as $emp)
                                    <option value="{{ $emp->id }}" @selected((string) old('hiring_manager_employee_id', $opening->hiring_manager_employee_id) === (string) $emp->id)>
                                        {{ $emp->personal?->fullname ?: ($emp->user?->name ?: 'Employee #' . $emp->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="opened_at">Opening Date <span class="text-danger">*</span></label>
                            <input type="date" id="opened_at" name="opened_at" value="{{ old('opened_at', $opening->opened_at ? $opening->opened_at->format('Y-m-d') : '') }}" required class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="closes_at">Closing Date <small class="text-muted">(Optional)</small></label>
                            <input type="date" id="closes_at" name="closes_at" value="{{ old('closes_at', $opening->closes_at ? $opening->closes_at->format('Y-m-d') : '') }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" required class="form-control">
                                <option value="open" @selected(old('status', $opening->status) === 'open')>Open</option>
                                <option value="draft" @selected(old('status', $opening->status) === 'draft')>Draft</option>
                                <option value="filled" @selected(old('status', $opening->status) === 'filled')>Filled</option>
                                <option value="closed" @selected(old('status', $opening->status) === 'closed')>Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="ln_solid" style="margin-top: 15px;"></div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Job Opening</button>
                        <a href="{{ route('recruitment.job-openings.show', $opening) }}" class="btn btn-default">Cancel</a>
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
        });
    </script>
@endsection

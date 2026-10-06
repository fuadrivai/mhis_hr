<div class="form-section">
    <form action="{{ route('assessment-setting.assignment.store') }}" method="POST" class="row">
        @csrf
        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
            <select name="employee_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Employee --</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown User' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
            <select name="subject_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Subject --</option>
                @foreach ($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->subjectCategory->name ?? '' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
            <select name="school_class_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Class --</option>
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-link"></i> Assign Subject</button>
        </div>
    </form>
</div>
<table class="table table-striped table-bordered datatable" style="width: 100%">
    <thead>
        <tr>
            <th>Employee</th>
            <th>Subject</th>
            <th>Class</th>
            <th style="width: 15%;">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employeeSubjects as $es)
            <tr>
                <td><strong>{{ $es->employee->user->name ?? 'Unknown User' }}</strong></td>
                <td>
                    {{ $es->subject->name ?? '' }}
                    <span class="label label-default">{{ $es->subject->subjectCategory->name ?? '' }}</span>
                </td>
                <td><span class="badge bg-blue">{{ $es->schoolClass->name ?? '' }}</span></td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm replace-employee-button"
                        data-toggle="modal" data-target="#replaceEmployeeModal"
                        data-replace-url="{{ route('assessment-setting.assignment.employee.update', $es->id) }}">
                        <i class="fa fa-exchange"></i> Replace
                    </button>
                    <form action="{{ route('assessment-setting.assignment.destroy', $es->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this assignment?')"><i class="fa fa-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="modal fade" id="replaceEmployeeModal" tabindex="-1" role="dialog"
    aria-labelledby="replaceEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="replaceEmployeeForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="replaceEmployeeModalLabel">Replace Employee</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Select an active employee for this assignment.</p>
                    <div class="table-responsive">
                        <table id="replaceEmployeeTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Level</th>
                                    <th>Position</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->user->name ?? 'Unknown User' }}</td>
                                        <td>{{ $employee->employment->branch->name ?? ($employee->employment->branch_name ?? '-') }}</td>
                                        <td>{{ $employee->employment->organization->name ?? ($employee->employment->organization_name ?? '-') }}</td>
                                        <td>{{ $employee->employment->job_level->name ?? ($employee->employment->job_level_name ?? '-') }}</td>
                                        <td>{{ $employee->employment->job_position->name ?? ($employee->employment->job_position_name ?? '-') }}</td>
                                        <td>
                                            <button type="submit" name="employee_id" value="{{ $employee->id }}"
                                                class="btn btn-success btn-sm">
                                                <i class="fa fa-check"></i> Choose
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

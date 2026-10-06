<div class="form-section">
    <form action="{{ route('assessment-setting.approver.store') }}" method="POST" class="row">
        @csrf
        <div class="col-md-2 col-sm-2 col-xs-12 form-group">
            <select name="subject_category_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Category --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-2 col-xs-12 form-group">
            <select name="employee_id" class="form-control select2" style="width: 100%;" required>
                <option value="">-- Select Approver (Employee) --</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown User' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
            <select name="subject_ids[]" class="form-control select2" style="width: 100%;"
                multiple="multiple" data-placeholder="-- Select Subjects (Optional) --">
                @foreach ($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-2 col-xs-12 form-group">
            <select name="school_class_ids[]" class="form-control select2" style="width: 100%;"
                multiple="multiple" data-placeholder="-- Select Classrooms (Optional) --">
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-2 col-xs-12 form-group">
            <input type="number" name="level" class="form-control" placeholder="Level (1, 2...)" min="1" required>
        </div>
        <div class="col-md-1 col-sm-1 col-xs-12 form-group">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i></button>
        </div>
    </form>
</div>
<table class="table table-striped table-bordered datatable" style="width: 100%">
    <thead>
        <tr>
            <th>Category</th>
            <th>Approver</th>
            <th>Subjects</th>
            <th>Classrooms</th>
            <th>Level</th>
            <th style="width: 15%;">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($approvers as $a)
            <tr>
                <td><span class="label label-info">{{ $a->subjectCategory->name ?? '' }}</span></td>
                <td><strong>{{ $a->employee->user->name ?? 'Unknown User' }}</strong></td>
                <td>
                    @foreach ($a->subjects as $s)
                        <span class="badge bg-purple">{{ $s->name }}</span>
                    @endforeach
                </td>
                <td>
                    @foreach ($a->schoolClasses as $c)
                        <span class="badge bg-blue">{{ $c->name }}</span>
                    @endforeach
                </td>
                <td><span class="badge bg-green">Level {{ $a->level }}</span></td>
                <td>
                    <form action="{{ route('assessment-setting.approver.destroy', $a->id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-warning btn-sm btn-edit-approver"
                            data-id="{{ $a->id }}" data-category="{{ $a->subject_category_id }}"
                            data-employee="{{ $a->employee_id }}" data-level="{{ $a->level }}"
                            data-classes="{{ json_encode($a->schoolClasses->pluck('id')) }}"
                            data-subjects="{{ json_encode($a->subjects->pluck('id')) }}">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this approver?')"><i class="fa fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="modal fade" id="editApproverModal" tabindex="-1" role="dialog" aria-labelledby="editApproverModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editApproverForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="editApproverModalLabel">Edit Approver</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="subject_category_id" id="edit_category_id" class="form-control select2"
                            style="width: 100%;" required>
                            <option value="">-- Select Category --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Approver (Employee)</label>
                        <select name="employee_id" id="edit_employee_id" class="form-control select2"
                            style="width: 100%;" required>
                            <option value="">-- Select Approver (Employee) --</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown User' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subjects (Optional)</label>
                        <select name="subject_ids[]" id="edit_subject_ids" class="form-control select2"
                            style="width: 100%;" multiple="multiple" data-placeholder="-- Select Subjects --">
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Classrooms (Optional)</label>
                        <select name="school_class_ids[]" id="edit_school_class_ids" class="form-control select2"
                            style="width: 100%;" multiple="multiple" data-placeholder="-- Select Classrooms --">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="number" name="level" id="edit_level" class="form-control"
                            placeholder="Level (1, 2...)" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

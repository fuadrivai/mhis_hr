@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-tab-content {
            padding-top: 20px;
        }

        .form-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-cogs"></i> Assessment Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="" role="tabpanel" data-example-id="togglable-tabs">
                    <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_approvers" id="approvers-tab" role="tab" data-toggle="tab"
                                aria-expanded="true"><i class="fa fa-users"></i> Approvers</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_monitors" id="monitors-tab" role="tab" data-toggle="tab"
                                aria-expanded="false"><i class="fa fa-eye"></i> Monitors</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_assignments" id="assignments-tab" role="tab" data-toggle="tab"
                                aria-expanded="false"><i class="fa fa-check-square-o"></i> Employee Assignments</a>
                        </li>
                    </ul>
                    <div id="myTabContent" class="tab-content custom-tab-content">
                        <!-- Approvers -->
                        <div role="tabpanel" class="tab-pane fade active in" id="tab_approvers"
                            aria-labelledby="approvers-tab">
                            <div class="form-section">
                                <form action="{{ route('assessment-setting.approver.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select name="subject_category_id" class="form-control select2" style="width: 100%;"
                                            required>
                                            <option value="">-- Select Category --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="employee_id" class="form-control select2" style="width: 100%;"
                                            required>
                                            <option value="">-- Select Approver (Employee) --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown User' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="school_class_ids[]" class="form-control select2" style="width: 100%;" multiple="multiple" data-placeholder="-- Select Classrooms (Optional) --">
                                            @foreach ($classes as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <input type="number" name="level" class="form-control"
                                            placeholder="Level (1, 2...)" min="1" required>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Add</button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped table-bordered datatable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Approver</th>
                                        <th>Classrooms</th>
                                        <th>Level</th>
                                        <th style="width: 15%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($approvers as $a)
                                        <tr>
                                            <td><span class="label label-info">{{ $a->subjectCategory->name ?? '' }}</span>
                                            </td>
                                            <td><strong>{{ $a->employee->user->name ?? 'Unknown User' }}</strong></td>
                                            <td>
                                                @foreach($a->schoolClasses as $c)
                                                    <span class="badge bg-blue">{{ $c->name }}</span>
                                                @endforeach
                                            </td>
                                            <td><span class="badge bg-green">Level {{ $a->level }}</span></td>
                                            <td>
                                                <form action="{{ route('assessment-setting.approver.destroy', $a->id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-warning btn-sm btn-edit-approver" 
                                                        data-id="{{ $a->id }}" 
                                                        data-category="{{ $a->subject_category_id }}" 
                                                        data-employee="{{ $a->employee_id }}" 
                                                        data-level="{{ $a->level }}" 
                                                        data-classes="{{ json_encode($a->schoolClasses->pluck('id')) }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Delete this approver?')"><i
                                                            class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Monitors -->
                        <div role="tabpanel" class="tab-pane fade" id="tab_monitors" aria-labelledby="monitors-tab">
                            <div class="form-section">
                                <form action="{{ route('assessment-setting.monitor.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-4 col-sm-4 col-xs-12 form-group">
                                        <select name="subject_category_id" class="form-control select2"
                                            style="width: 100%;" required>
                                            <option value="">-- Select Category --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 col-sm-5 col-xs-12 form-group">
                                        <select name="employee_id" class="form-control select2" style="width: 100%"
                                            required>
                                            <option value="">-- Select Monitor (Employee) --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">
                                                    {{ $emp->user->name ?? 'Unknown User' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i
                                                class="fa fa-plus"></i> Add Monitor</button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped table-bordered datatable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Monitor</th>
                                        <th style="width: 15%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monitors as $m)
                                        <tr>
                                            <td><span
                                                    class="label label-info">{{ $m->subjectCategory->name ?? '' }}</span>
                                            </td>
                                            <td><strong>{{ $m->employee->user->name ?? 'Unknown User' }}</strong></td>
                                            <td>
                                                <form action="{{ route('assessment-setting.monitor.destroy', $m->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Delete this monitor?')"><i
                                                            class="fa fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Employee Assignments -->
                        <div role="tabpanel" class="tab-pane fade" id="tab_assignments" aria-labelledby="assignments-tab">
                            <div class="form-section">
                                <form action="{{ route('assessment-setting.assignment.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="employee_id" class="form-control select2" style="width: 100%;"
                                            required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">
                                                    {{ $emp->user->name ?? 'Unknown User' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="subject_id" class="form-control select2" style="width: 100%;"
                                            required>
                                            <option value="">-- Select Subject --</option>
                                            @foreach ($subjects as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }}
                                                    ({{ $s->subjectCategory->name ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="school_class_id" class="form-control select2" style="width: 100%;"
                                            required>
                                            <option value="">-- Select Class --</option>
                                            @foreach ($classes as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i
                                                class="fa fa-link"></i> Assign Subject</button>
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
                                            <td>{{ $es->subject->name ?? '' }} <span
                                                    class="label label-default">{{ $es->subject->subjectCategory->name ?? '' }}</span>
                                            </td>
                                            <td><span class="badge bg-blue">{{ $es->schoolClass->name ?? '' }}</span></td>
                                            <td>
                                                <form
                                                    action="{{ route('assessment-setting.assignment.destroy', $es->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Delete this assignment?')"><i
                                                            class="fa fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Approver Modal -->
    <div class="modal fade" id="editApproverModal" tabindex="-1" role="dialog" aria-labelledby="editApproverModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editApproverForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="editApproverModalLabel">Edit Approver</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="subject_category_id" id="edit_category_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Approver (Employee)</label>
                            <select name="employee_id" id="edit_employee_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select Approver (Employee) --</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown User' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Classrooms (Optional)</label>
                            <select name="school_class_ids[]" id="edit_school_class_ids" class="form-control select2" style="width: 100%;" multiple="multiple" data-placeholder="-- Select Classrooms --">
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Level</label>
                            <input type="number" name="level" id="edit_level" class="form-control" placeholder="Level (1, 2...)" min="1" required>
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
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".datatable").DataTable({
                "language": {
                    "emptyTable": "No data available in this section"
                }
            });

            // Remember active tab on reload
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                localStorage.setItem('activeTab', $(e.target).attr('href'));
            });

            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#myTab a[href="' + activeTab + '"]').tab('show');
            } else {
                // Force Classes as default if no tab is saved
                $('#myTab a[href="#tab_classes"]').tab('show');
            }

            $('.btn-edit-approver').click(function() {
                var id = $(this).data('id');
                var category = $(this).data('category');
                var employee = $(this).data('employee');
                var level = $(this).data('level');
                var classes = $(this).data('classes');

                $('#editApproverForm').attr('action', '/setting/assessment/approver/' + id);
                $('#edit_category_id').val(category).trigger('change');
                $('#edit_employee_id').val(employee).trigger('change');
                $('#edit_level').val(level);
                $('#edit_school_class_ids').val(classes).trigger('change');

                $('#editApproverModal').modal('show');
            });
        });
    </script>
@endsection

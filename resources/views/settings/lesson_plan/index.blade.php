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
                <h2><i class="fa fa-cogs"></i> Lesson Plan Settings</h2>
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
                            <a href="#tab_classes" id="classes-tab" role="tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fa fa-building-o"></i> Classes</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_categories" id="categories-tab" role="tab" data-toggle="tab"
                                aria-expanded="false"><i class="fa fa-tags"></i> Subject Categories</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_subjects" id="subjects-tab" role="tab" data-toggle="tab"
                                aria-expanded="false"><i class="fa fa-book"></i> Subjects</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_approvers" id="approvers-tab" role="tab" data-toggle="tab"
                                aria-expanded="false"><i class="fa fa-users"></i> Approvers</a>
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
                        <!-- Classes -->
                        <div role="tabpanel" class="tab-pane fade active in" id="tab_classes" aria-labelledby="classes-tab">
                            <div class="form-section">
                                <form action="{{ route('lesson-plan-setting.class.store') }}" method="POST" class="row">
                                    @csrf
                                    <div class="col-md-9 col-sm-9 col-xs-12 form-group">
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Enter Class Name (e.g. 10A)" required>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i>
                                            Add Class</button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped table-bordered datatable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th style="width: 80%;">Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($classes as $c)
                                        <tr>
                                            <td><strong>{{ $c->name }}</strong></td>
                                            <td>
                                                <form action="{{ route('lesson-plan-setting.class.destroy', $c->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this class?')"><i
                                                            class="fa fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Categories -->
                        <div role="tabpanel" class="tab-pane fade" id="tab_categories" aria-labelledby="categories-tab">
                            <div class="form-section">
                                <form action="{{ route('lesson-plan-setting.category.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-9 col-sm-9 col-xs-12 form-group">
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Enter Category Name (e.g. National)" required>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i
                                                class="fa fa-plus"></i> Add Category</button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped table-bordered datatable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th style="width: 80%;">Category Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $c)
                                        <tr>
                                            <td><strong>{{ $c->name }}</strong></td>
                                            <td>
                                                <form action="{{ route('lesson-plan-setting.category.destroy', $c->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this category?')"><i
                                                            class="fa fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Subjects -->
                        <div role="tabpanel" class="tab-pane fade" id="tab_subjects" aria-labelledby="subjects-tab">
                            <div class="form-section">
                                <form action="{{ route('lesson-plan-setting.subject.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-4 col-sm-4 col-xs-12 form-group">
                                        <select name="subject_category_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 col-sm-5 col-xs-12 form-group">
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Subject Name (e.g. Math)" required>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i
                                                class="fa fa-plus"></i> Add Subject</button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped table-bordered datatable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Subject</th>
                                        <th style="width: 15%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subjects as $s)
                                        <tr>
                                            <td><span
                                                    class="label label-info">{{ $s->subjectCategory->name ?? '' }}</span>
                                            </td>
                                            <td><strong>{{ $s->name }}</strong></td>
                                            <td>
                                                <form action="{{ route('lesson-plan-setting.subject.destroy', $s->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Delete this subject?')"><i
                                                            class="fa fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Approvers -->
                        <div role="tabpanel" class="tab-pane fade" id="tab_approvers" aria-labelledby="approvers-tab">
                            <div class="form-section">
                                <form action="{{ route('lesson-plan-setting.approver.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select id="approver_category_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select name="subject_id" id="approver_subject_id" class="form-control" required>
                                            <option value="">-- Select Subject --</option>
                                            @foreach ($subjects as $sub)
                                                <option value="{{ $sub->id }}"
                                                    data-category="{{ $sub->subject_category_id }}">{{ $sub->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select name="school_class_id" class="form-control" required>
                                            <option value="">-- Select Class --</option>
                                            @foreach ($classes as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select name="employee_id" class="form-control select2" style="width: 100%"
                                            required>
                                            <option value="">-- Select Approver (Employee) --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">
                                                    {{ $emp->user->name ?? 'Unknown User' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <input type="number" name="level" class="form-control"
                                            placeholder="Level (1, 2...)" min="1" required>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <button type="submit" class="btn btn-primary btn-block"><i
                                                class="fa fa-plus"></i> Add Approver</button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped table-bordered datatable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Approver</th>
                                        <th>Level</th>
                                        <th style="width: 15%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($approvers as $a)
                                        <tr>
                                            <td><span class="label label-info">{{ $a->subject->name ?? '' }}</span></td>
                                            <td><span class="badge bg-blue">{{ $a->schoolClass->name ?? '' }}</span></td>
                                            <td><strong>{{ $a->employee->user->name ?? 'Unknown User' }}</strong></td>
                                            <td><span class="badge bg-green">Level {{ $a->level }}</span></td>
                                            <td>
                                                <form action="{{ route('lesson-plan-setting.approver.destroy', $a->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Delete this approver?')"><i
                                                            class="fa fa-trash"></i> Delete</button>
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
                                <form action="{{ route('lesson-plan-setting.monitor.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-4 col-sm-4 col-xs-12 form-group">
                                        <select name="subject_category_id" class="form-control" required>
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
                                                <form action="{{ route('lesson-plan-setting.monitor.destroy', $m->id) }}"
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
                        <div role="tabpanel" class="tab-pane fade" id="tab_assignments"
                            aria-labelledby="assignments-tab">
                            <div class="form-section">
                                <form action="{{ route('lesson-plan-setting.assignment.store') }}" method="POST"
                                    class="row">
                                    @csrf
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select name="employee_id" class="form-control select2" style="width: 100%"
                                            required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">
                                                    {{ $emp->user->name ?? 'Unknown User' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
                                        <select id="assignment_category_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="subject_id" id="assignment_subject_id" class="form-control"
                                            required>
                                            <option value="">-- Select Subject --</option>
                                            @foreach ($subjects as $s)
                                                <option value="{{ $s->id }}"
                                                    data-category="{{ $s->subject_category_id }}">{{ $s->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                                        <select name="school_class_id" class="form-control" required>
                                            <option value="">-- Select Class --</option>
                                            @foreach ($classes as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-2 col-xs-12 form-group">
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
                                                    action="{{ route('lesson-plan-setting.assignment.destroy', $es->id) }}"
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

            function setupSubjectFilter(categorySelectId, subjectSelectId) {
                var $categorySelect = $('#' + categorySelectId);
                var $subjectSelect = $('#' + subjectSelectId);

                // Store all original options
                var $subjectOptions = $subjectSelect.find('option[value!=""]').clone();

                $categorySelect.on('change', function() {
                    var selectedCategory = $(this).val();

                    // Clear current options except the placeholder
                    $subjectSelect.find('option[value!=""]').remove();

                    // Re-add options that match the category
                    $subjectOptions.each(function() {
                        var catId = $(this).data('category');
                        if (selectedCategory === '' || catId == selectedCategory) {
                            $subjectSelect.append($(this).clone());
                        }
                    });
                });

                // Trigger change immediately to set initial state
                $categorySelect.trigger('change');
            }

            setupSubjectFilter('approver_category_id', 'approver_subject_id');
            setupSubjectFilter('assignment_category_id', 'assignment_subject_id');
        });
    </script>
@endsection

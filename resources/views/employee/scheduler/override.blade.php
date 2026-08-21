@extends('layouts.main-layout')

@section('content-class')
    <style>
        .override-page .card {
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .override-page .selected-empty {
            color: #6b7280;
            text-align: center;
            padding: 18px;
        }

        .override-page .employee-list {
            max-height: 420px;
            overflow-y: auto;
        }

        .override-page .employee-table-wrap {
            max-height: 420px;
            overflow: auto;
        }

        .override-page .employee-table-wrap thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 override-page">
        <div class="x_panel">
            <div class="x_title">
                <h2>Assign Shift Override <small>One-day assignment</small></h2>
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
                <form id="overrideForm" method="POST" action="{{ route('scheduler.override.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="shift_id">Master Shift</label>
                                <select name="shift_id" id="shift_id" class="form-control" required>
                                    <option value="">-- Select Shift --</option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}">
                                            {{ $shift->name }} ({{ $shift->schedule_in }} - {{ $shift->schedule_out }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="override_date">Date</label>
                                <input type="text" id="override_date" class="form-control" value="{{ $selectedDate }}"
                                    autocomplete="off" required>
                                <input type="hidden" name="date" id="override_date_value" value="{{ $selectedDate }}">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-3">
                            <button type="button" class="btn btn-info btn-block" id="showEmployeesBtn">
                                <i class="fa fa-users"></i> Show Active Employees
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Selected Employees</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="selectedEmployeesTable">
                                <thead>
                                    <tr>
                                        <th style="width: 60px">#</th>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th style="width: 110px">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <a href="{{ route('scheduler.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveOverrideBtn">
                            <i class="fa fa-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="activeEmployeesModal" tabindex="-1" role="dialog"
        aria-labelledby="activeEmployeesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activeEmployeesModalLabel">Select Active Employees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="employeeSearch">Search</label>
                            <input type="search" class="form-control" id="employeeSearch"
                                placeholder="Name or employee ID">
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="branchFilter">Branch</label>
                            <select class="form-control employee-filter" id="branchFilter">
                                <option value="">All</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ strtolower($branch->name) }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="organizationFilter">Organization</label>
                            <select class="form-control employee-filter" id="organizationFilter">
                                <option value="">All</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ strtolower($organization->name) }}">{{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="levelFilter">Level</label>
                            <select class="form-control employee-filter" id="levelFilter">
                                <option value="">All</option>
                                @foreach ($levels as $level)
                                    <option value="{{ strtolower($level->name) }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="positionFilter">Job Position</label>
                            <select class="form-control employee-filter" id="positionFilter">
                                <option value="">All</option>
                                @foreach ($positions as $position)
                                    <option value="{{ strtolower($position->name) }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="selectAllEmployees">
                        <label class="custom-control-label" for="selectAllEmployees">Select All Visible Employees</label>
                    </div>

                    <div class="employee-table-wrap">
                        <table class="table table-bordered table-striped table-sm mb-0" id="activeEmployeeTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px">Select</th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Level</th>
                                    <th>Job Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    @php
                                        $employment = $employee->employment;
                                        $branchName = optional(optional($employment)->branch)->name;
                                        $organizationName = optional(optional($employment)->organization)->name;
                                        $levelName = optional(optional($employment)->job_level)->name;
                                        $positionName = optional(optional($employment)->job_position)->name;
                                    @endphp
                                    <tr class="employee-option"
                                        data-search="{{ strtolower(($employee->personal->fullname ?? '') . ' ' . (optional($employment)->employee_id ?? '')) }}"
                                        data-branch="{{ strtolower($branchName ?? '') }}"
                                        data-organization="{{ strtolower($organizationName ?? '') }}"
                                        data-level="{{ strtolower($levelName ?? '') }}"
                                        data-position="{{ strtolower($positionName ?? '') }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="employee-checkbox"
                                                id="employee_{{ $employee->id }}" value="{{ $employee->id }}"
                                                data-name="{{ $employee->personal->fullname ?? 'Unknown Employee' }}"
                                                data-code="{{ optional($employment)->employee_id ?? '' }}">
                                        </td>
                                        <td>{{ optional($employment)->employee_id ?? '-' }}</td>
                                        <td>{{ $employee->personal->fullname ?? 'Unknown Employee' }}</td>
                                        <td>{{ $branchName ?? '-' }}</td>
                                        <td>{{ $organizationName ?? '-' }}</td>
                                        <td>{{ $levelName ?? '-' }}</td>
                                        <td>{{ $positionName ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmEmployeesBtn">Select</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(document).ready(function() {
            const selectedEmployees = new Map();
            const initialEmployeeIds = @json(array_values($selectedEmployees));

            $('#override_date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom left'
            });

            $('#override_date').on('changeDate', function(event) {
                $('#override_date_value').val(moment(event.date).format('YYYY-MM-DD'));
            });

            $('.employee-checkbox').each(function() {
                if (initialEmployeeIds.indexOf(Number($(this).val())) !== -1) {
                    $(this).prop('checked', true);
                }
            });

            $('#showEmployeesBtn').on('click', function() {
                $('.employee-checkbox').each(function() {
                    $(this).prop('checked', selectedEmployees.has(Number($(this).val())));
                });
                $('#activeEmployeesModal').modal('show');
            });

            function filterEmployees() {
                const query = $('#employeeSearch').val().toLowerCase().trim();
                const branch = $('#branchFilter').val();
                const organization = $('#organizationFilter').val();
                const level = $('#levelFilter').val();
                const position = $('#positionFilter').val();

                $('.employee-option').each(function() {
                    const row = $(this);
                    const matchesSearch = row.data('search').indexOf(query) !== -1;
                    const matchesBranch = !branch || row.data('branch') === branch;
                    const matchesOrganization = !organization || row.data('organization') === organization;
                    const matchesLevel = !level || row.data('level') === level;
                    const matchesPosition = !position || row.data('position') === position;

                    row.toggle(matchesSearch && matchesBranch && matchesOrganization && matchesLevel &&
                        matchesPosition);
                });
            }

            $('#employeeSearch, .employee-filter').on('input change', filterEmployees);

            $('#selectAllEmployees').on('change', function() {
                $('.employee-option:visible .employee-checkbox').prop('checked', this.checked);
            });

            $('#confirmEmployeesBtn').on('click', function() {
                $('.employee-option:visible .employee-checkbox:checked').each(function() {
                    selectedEmployees.set(Number($(this).val()), {
                        id: Number($(this).val()),
                        name: $(this).data('name'),
                        code: $(this).data('code')
                    });
                });

                $('.employee-option:visible .employee-checkbox:not(:checked)').each(function() {
                    selectedEmployees.delete(Number($(this).val()));
                });

                renderSelectedEmployees();
                $('#activeEmployeesModal').modal('hide');
            });

            function renderSelectedEmployees() {
                const $body = $('#selectedEmployeesTable tbody').empty();
                let rowNumber = 0;

                selectedEmployees.forEach(function(employee) {
                    rowNumber++;
                    $body.append(`
                        <tr>
                            <td>${rowNumber}</td>
                            <td>${employee.code || '-'}</td>
                            <td>${employee.name}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-employee" data-id="${employee.id}">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </td>
                            <input type="hidden" name="employee_ids[]" value="${employee.id}">
                        </tr>
                    `);
                });

                if (!rowNumber) {
                    $body.append('<tr><td colspan="4" class="selected-empty">No employees selected.</td></tr>');
                }
            }

            $('#selectedEmployeesTable').on('click', '.remove-employee', function() {
                selectedEmployees.delete(Number($(this).data('id')));
                $(`#employee_${$(this).data('id')}`).prop('checked', false);
                renderSelectedEmployees();
            });

            $('#overrideForm').on('submit', function(event) {
                if (!selectedEmployees.size) {
                    event.preventDefault();
                    sweetAlert('Error', 'Please select at least one active employee.', 'error');
                    return;
                }

                $('#saveOverrideBtn').prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Saving...');
            });

            initialEmployeeIds.forEach(function(id) {
                const checkbox = $(`#employee_${id}`);
                if (checkbox.length) {
                    selectedEmployees.set(id, {
                        id: id,
                        name: checkbox.data('name'),
                        code: checkbox.data('code')
                    });
                }
            });
            renderSelectedEmployees();
        });
    </script>
@endsection

@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="" role="tabpanel" data-example-id="togglable-tabs">
            <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="index-tab" data-toggle="tab" href="#index-content" role="tab"
                        aria-controls="index-content" aria-selected="true">
                        Goverment
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="company-tab" data-toggle="tab" href="#company-content" role="tab"
                        aria-controls="company-content" aria-selected="false">
                        Company
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="school-tab" data-toggle="tab" href="#school-content" role="tab"
                        aria-controls="school-content" aria-selected="false">
                        School
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="schedulerTabContent">
                <div role="tabpanel" class="tab-pane fade show active" id="index-content" aria-labelledby="index-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table id="government-holidays" class="table table-striped table-bordered table-sm"
                                        style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($governments as $index => $holiday)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $holiday->date->format('d F Y') }}</td>
                                                    <td>{{ $holiday->name }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $holiday->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                            {{ $holiday->is_active ? 'Active' : 'Not Active' }}
                                                        </span>
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

                <div role="tabpanel" class="tab-pane fade" id="company-content" aria-labelledby="company-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="scheduler-calendar-card">
                                <div class="scheduler-loading d-none" id="schedulerLoading" aria-live="polite">
                                    <span><i class="fa fa-spinner fa-spin"></i>Loading schedule...</span>
                                </div>

                                <div class="scheduler-calendar-scroll">
                                    <table id="company-holidays" class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th>Branch</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($companies as $index => $holiday)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $holiday->date->format('d F Y') }}</td>
                                                    <td>{{ $holiday->name }}</td>
                                                    <td>{{ optional($holiday->branch)->name ?? 'All Branches' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $holiday->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                            {{ $holiday->is_active ? 'Active' : 'Not Active' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary holiday-edit-button"
                                                            data-update-url="{{ route('holiday.update', $holiday->id) }}"
                                                            data-type="COMPANY"
                                                            data-date="{{ $holiday->date->format('Y-m-d') }}"
                                                            data-name="{{ $holiday->name }}"
                                                            data-description="{{ $holiday->description }}"
                                                            data-branch-id="{{ $holiday->branch_id }}">Edit</button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger holiday-delete-button"
                                                            data-delete-url="{{ route('holiday.destroy', $holiday->id) }}">Delete</button>
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
                <div role="tabpanel" class="tab-pane fade" id="school-content" aria-labelledby="school-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="scheduler-calendar-scroll">
                                <table id="school-holidays" class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Branch</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schools as $index => $holiday)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $holiday->date->format('d F Y') }}</td>
                                                <td>{{ $holiday->name }}</td>
                                                <td>{{ $holiday->category }}</td>
                                                <td>{{ optional($holiday->branch)->name ?? 'All Branches' }}</td>
                                                <td>
                                                    <span
                                                        class="badge {{ $holiday->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                        {{ $holiday->is_active ? 'Active' : 'Not Active' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary holiday-edit-button"
                                                        data-update-url="{{ route('holiday.update', $holiday->id) }}"
                                                        data-type="SCHOOL"
                                                        data-date="{{ $holiday->date->format('Y-m-d') }}"
                                                        data-name="{{ $holiday->name }}"
                                                        data-description="{{ $holiday->description }}"
                                                        data-category="{{ $holiday->category }}"
                                                        data-branch-id="{{ $holiday->branch_id }}">Edit</button>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger holiday-delete-button"
                                                        data-delete-url="{{ route('holiday.destroy', $holiday->id) }}">Delete</button>
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
    </div>

    <div class="modal fade" id="holiday-modal" tabindex="-1" role="dialog" aria-labelledby="holiday-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="holiday-form" action="{{ route('holiday.store') }}" method="POST">
                @csrf
                <input type="hidden" id="holiday-method" value="POST">
                <input type="hidden" name="type" id="holiday-type">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="holiday-modal-title">Add Company Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="holiday-form-errors" class="alert alert-danger d-none" role="alert"></div>
                        <div class="form-group" id="holiday-name-group">
                            <label for="holiday-name">Name</label>
                            <input type="text" class="form-control" id="holiday-name" name="name"
                                maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="holiday-start-date">Start Date</label>
                            <input type="text" class="form-control" readonly id="holiday-start-date"
                                name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="holiday-end-date">End Date</label>
                            <input type="text" class="form-control" readonly id="holiday-end-date" name="end_date"
                                required>
                        </div>
                        <div class="form-group" id="holiday-description-group">
                            <label for="holiday-description">Description</label>
                            <textarea class="form-control" id="holiday-description" name="description" rows="3" maxlength="255"></textarea>
                        </div>
                        <div class="form-group" id="holiday-category-group">
                            <label for="holiday-category">Category</label>
                            <select class="form-control" id="holiday-category" name="category">
                                <option value="">Choose category</option>
                                <option value="REGULAR">Regular</option>
                                <option value="NEW_ACADEMIC_YEAR">New Academic Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="holiday-branch">Branch</label>
                            <select class="form-control" id="holiday-branch" name="branch_id">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="holiday-save-button">Save Holiday</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#holiday-start-date, #holiday-end-date').datepicker({
                format: 'yyyy-mm-dd',
                orientation: 'top auto',
                autoclose: true,
                todayHighlight: true,
                language: 'id',
                clearBtn: true
            });

            function openHolidayModal(type) {
                var isSchoolHoliday = type === 'SCHOOL';
                $('#holiday-form')[0].reset();
                $('#holiday-form').attr('action', "{{ route('holiday.store') }}");
                $('#holiday-method').val('POST');
                $('#holiday-save-button').text('Save Holiday');
                $('#holiday-form-errors').addClass('d-none').empty();
                $('#holiday-type').val(type);
                $('#holiday-name-group').show();
                $('#holiday-name').prop('required', true);
                $('#holiday-description').prop('required', !isSchoolHoliday);
                $('#holiday-category-group').toggle(isSchoolHoliday);
                $('#holiday-category').prop('required', isSchoolHoliday);
                $('#holiday-modal-title').text(isSchoolHoliday ? 'Add School Holiday' : 'Add Company Holiday');
                $('#holiday-modal').modal('show');
            }

            $('#government-holidays').DataTable({
                paging: false,
                searching: false,
                length: false,
            });
            $('#company-holidays').DataTable({
                paging: false,
                searching: false,
                length: false,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Company Holiday <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-holiday'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        openHolidayModal('COMPANY');
                    }
                }],
            });
            $('#school-holidays').DataTable({
                paging: false,
                searching: false,
                length: false,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add School Holiday <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-school-holiday'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        openHolidayModal('SCHOOL');
                    }
                }],
            });

            $(document).on('click', '.holiday-edit-button', function() {
                var button = $(this);
                var type = button.data('type');
                var isSchoolHoliday = type === 'SCHOOL';

                openHolidayModal(type);
                $('#holiday-form').attr('action', button.data('update-url'));
                $('#holiday-method').val('PUT');
                $('#holiday-modal-title').text(isSchoolHoliday ? 'Edit School Holiday' :
                    'Edit Company Holiday');
                $('#holiday-save-button').text('Update Holiday');
                $('#holiday-name').val(button.data('name'));
                $('#holiday-description').val(button.data('description'));
                $('#holiday-category').val(button.data('category') || '');
                $('#holiday-branch').val(button.data('branch-id') || '');
                $('#holiday-start-date').datepicker('update', button.data('date'));
                $('#holiday-end-date').datepicker('update', button.data('date'));
            });

            $(document).on('click', '.holiday-delete-button', function() {
                var button = $(this);
                if (!window.confirm('Delete this holiday?')) {
                    return;
                }

                button.prop('disabled', true);
                $.ajax({
                    url: button.data('delete-url'),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        sweetAlert('Success', response.message, 'success');
                        window.setTimeout(function() {
                            window.location.reload();
                        }, 1200);
                    },
                    error: function() {
                        button.prop('disabled', false);
                        sweetAlert('Error', 'Unable to delete the holiday. Please try again.',
                            'error');
                    }
                });
            });

            $('#holiday-form').on('submit', function(event) {
                event.preventDefault();

                var form = this;
                var saveButton = $('#holiday-save-button');
                var errorBox = $('#holiday-form-errors');

                if (saveButton.prop('disabled')) {
                    return;
                }

                errorBox.addClass('d-none').empty();

                var startDate = moment($('#holiday-start-date').val(), 'YYYY-MM-DD', true);
                var endDate = moment($('#holiday-end-date').val(), 'YYYY-MM-DD', true);
                if (!startDate.isValid() || !endDate.isValid()) {
                    errorBox.text('Please select a valid start and end date.').removeClass('d-none');
                    return;
                }
                if (endDate.isBefore(startDate, 'day')) {
                    errorBox.text('End date must be on or after start date.').removeClass('d-none');
                    return;
                }

                var formData = $(form).serializeArray();
                $.each(formData, function(index, field) {
                    if (field.name === 'start_date') {
                        field.value = startDate.format('YYYY-MM-DD');
                    } else if (field.name === 'end_date') {
                        field.value = endDate.format('YYYY-MM-DD');
                    }
                });

                saveButton.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: $(form).attr('action'),
                    method: $('#holiday-method').val(),
                    data: $.param(formData),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#holiday-modal').one('hidden.bs.modal', function() {
                            sweetAlert('Success', response.message, 'success');
                            window.setTimeout(function() {
                                window.location.reload();
                            }, 1200);
                        }).modal('hide');
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON || {};
                        var errors = response.errors || {};
                        var messages = [];

                        $.each(errors, function(field, fieldMessages) {
                            $.each(fieldMessages, function(index, message) {
                                messages.push(message);
                            });
                        });

                        if (messages.length) {
                            var list = $('<ul class="mb-0"></ul>');
                            $.each(messages, function(index, message) {
                                list.append($('<li></li>').text(message));
                            });
                            errorBox.empty().append(list).removeClass('d-none');
                        } else {
                            sweetAlert('Error', 'Unable to save the holiday. Please try again.',
                                'error');
                        }
                    },
                    complete: function() {
                        saveButton.prop('disabled', false).text('Save Holiday');
                    }
                });
            });
        });
    </script>
@endsection

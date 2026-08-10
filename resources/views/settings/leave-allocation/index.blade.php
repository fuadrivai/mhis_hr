@extends('layouts.main-layout')
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Form Filter</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="search">Employee</label>
                            <input type="text" name="search" id="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Name or email">
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="branch">Branch</label>
                            <select name="branch" id="branch" class="form-control">
                                <option value="all">All branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(request('branch') == $branch->id)>{{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="organization">Organization</label>
                            <select name="organization" id="organization" class="form-control">
                                <option value="all">All organizations</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}" @selected(request('organization') == $organization->id)>
                                        {{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="level">Level</label>
                            <select name="level" id="level" class="form-control">
                                <option value="all">All levels</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" @selected(request('level') == $level->id)>{{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="position">Position</label>
                            <select name="position" id="position" class="form-control">
                                <option value="all">All positions</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected(request('position') == $position->id)>{{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 form-group">
                            <label for="order">Balance order</label>
                            <select name="order" id="order" class="form-control">
                                <option value="remaining_asc" @selected(request('order', 'remaining_asc') === 'remaining_asc')>Low to high</option>
                                <option value="remaining_desc" @selected(request('order') === 'remaining_desc')>High to low</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 form-group">
                            <label for="per_page">Rows per page</label>
                            <select name="per_page" id="per_page" class="form-control">
                                @foreach ([5, 10, 20, 50, 100] as $perPage)
                                    <option value="{{ $perPage }}" @selected((int) request('per_page', 10) === $perPage)>{{ $perPage }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-4 form-group" style="padding-top: 25px;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ url()->current() }}" class="btn btn-default">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Level</th>
                                    <th>Position</th>
                                    <th>Remaining Balance</th>
                                    <th>#</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($leaveAllocations as $index => $leaveAllocation)
                                    <tr>
                                        <td class="text-right">{{ $leaveAllocations->firstItem() + $index }}</td>
                                        <td>{{ $leaveAllocation->employee->personal->fullname ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->organization->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->job_level->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->job_position->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $leaveAllocation->remaining }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-sm btn-allocation-modal"
                                                data-url="{{ url('setting/leave/allocation/' . $leaveAllocation->id) }}"
                                                data-employee="{{ $leaveAllocation->employee->personal->fullname ?? 'Employee' }}"
                                                data-mode="history">History</button>
                                            <button type="button" class="btn btn-primary btn-sm btn-allocation-modal"
                                                data-id="{{ $leaveAllocation->id }}"
                                                data-url="{{ url('setting/leave/allocation/' . $leaveAllocation->id) }}"
                                                data-employee="{{ $leaveAllocation->employee->personal->fullname ?? 'Employee' }}"
                                                data-mode="edit">Edit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No leave allocations found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="text-right">
                            {{ $leaveAllocations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="leave-allocation-modal" tabindex="-1" role="dialog"
        aria-labelledby="leave-allocation-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leave-allocation-modal-title">Leave Allocation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="leave-allocation-loading" class="text-center">Loading...</div>
                    <div id="leave-allocation-content" class="d-none">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Total:</strong> <span id="allocation-total"></span></div>
                            <div class="col-sm-4"><strong>Used:</strong> <span id="allocation-used"></span></div>
                            <div class="col-sm-4"><strong>Remaining:</strong> <span id="allocation-remaining"></span>
                            </div>
                        </div>
                        <form id="leave-allocation-form" class="d-none">
                            <div class="form-group">
                                <input type="hidden" id="allocation-id" name="allocation_id">
                                <label for="allocation-remaining-input">Remaining balance</label>
                                <input type="number" min="0" step="1" class="form-control"
                                    id="allocation-remaining-input" name="remaining" required>
                                <small class="text-danger d-none" id="allocation-error"></small>
                            </div>
                            <button type="submit" class="btn btn-primary">Save balance</button>
                        </form>
                        <hr>
                        <h5>Leave History</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Days</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody id="leave-allocation-history"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(document).ready(function() {
            let allocationUrl = null;

            $('.btn-allocation-modal').on('click', function() {
                const button = $(this);
                const isEditMode = button.data('mode') === 'edit';
                allocationUrl = button.data('url');
                $('#allocation-id').val(button.data('id'));

                $('#leave-allocation-modal-title').text(isEditMode ? 'Adjust Leave Balance' :
                    'Leave History');
                $('#leave-allocation-loading').removeClass('d-none');
                $('#leave-allocation-content, #leave-allocation-form, #allocation-error').addClass(
                    'd-none');
                $('#leave-allocation-modal').modal('show');

                $.get(allocationUrl)
                    .done(function(allocation) {
                        $('#allocation-total').text(allocation.total);
                        $('#allocation-used').text(allocation.used);
                        $('#allocation-remaining').text(allocation.remaining);
                        $('#allocation-remaining-input').val(allocation.remaining);

                        const rows = allocation.histories.length ? allocation.histories.map(function(
                                history) {
                                const date = new Date(history.created_at).toLocaleString();
                                const days = history.days > 0 ? '+' + history.days : history.days;
                                return '<tr><td>' + date + '</td><td>' + history.type +
                                    '</td><td>' + days +
                                    '</td><td>' + (history.remark || '-') + '</td></tr>';
                            }).join('') :
                            '<tr><td colspan="4" class="text-center">No leave history found.</td></tr>';

                        $('#leave-allocation-history').html(rows);
                        $('#leave-allocation-loading').addClass('d-none');
                        $('#leave-allocation-content').removeClass('d-none');
                        $('#leave-allocation-form').toggleClass('d-none', !isEditMode);
                    })
                    .fail(function() {
                        $('#leave-allocation-loading').text('Unable to load leave allocation details.');
                    });
            });

            $('#leave-allocation-form').on('submit', function(event) {
                event.preventDefault();
                const submitButton = $(this).find('[type="submit"]');

                submitButton.prop('disabled', true);
                $('#allocation-error').addClass('d-none');

                $.ajax({
                    url: allocationUrl,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        remaining: $('#allocation-remaining-input').val(),
                        id: $('#allocation-id').val()
                    }
                }).done(function() {
                    window.location.reload();
                }).fail(function(response) {
                    const errors = response.responseJSON && response.responseJSON.errors;
                    const message = errors && errors.remaining ? errors.remaining[0] :
                        'Unable to update the remaining balance.';
                    $('#allocation-error').text(message).removeClass('d-none');
                }).always(function() {
                    submitButton.prop('disabled', false);
                });
            });
        });
    </script>
@endsection

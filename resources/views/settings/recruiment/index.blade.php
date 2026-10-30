@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .monitor-form-section {
            padding: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-eye"></i> Recruitment Monitoring <small>Manage users with full Recruitment
                        access</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="alert alert-info" role="status">
                    Assigned monitors can open the Recruitment workspace and manage candidates and applications.
                    Only administrators can add or remove monitor access.
                </div>

                <div class="monitor-form-section">
                    <form action="{{ route('settings.recruitment-monitors.store') }}" method="POST" class="row">
                        @csrf
                        <div class="col-md-9 col-sm-9 col-xs-12 form-group">
                            <select id="employee_id" name="employee_id" class="form-control select2" style="width: 100%;"
                                required aria-label="Select monitor employee">
                                <option value="">-- Select employee --</option>
                                @foreach ($availableEmployees as $employee)
                                    <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                                        {{ $employee->user->name }}
                                        @if ($employee->user?->email)
                                            ({{ $employee->user->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if ($availableEmployees->isEmpty())
                                <p class="help-block">No active employees are available to assign.</p>
                            @endif
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12 form-group">
                            <button type="submit" class="btn btn-primary btn-block"
                                {{ $availableEmployees->isEmpty() ? 'disabled' : '' }}>
                                <i class="fa fa-plus"></i> Add Monitor
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table id="recruitment-monitors-table" class="table table-striped table-bordered" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Monitor</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monitors as $monitor)
                                <tr>
                                    <td><strong>{{ $monitor->employee->user->name ?? 'Unknown User' }}</strong></td>
                                    <td>
                                        <form action="{{ route('settings.recruitment-monitors.destroy', $monitor) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this monitor?')">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
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
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(function() {
            $('#recruitment-monitors-table').DataTable({
                pageLength: 25,
                ordering: true,
                responsive: true,
                language: {
                    emptyTable: 'No Recruitment monitors have been assigned.'
                }
            });

            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
@endsection

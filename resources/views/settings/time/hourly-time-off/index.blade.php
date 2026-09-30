@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_title">
            <h2>Hourly Time Off Balance Groups</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="row mb-3">
                <div class="col-12 text-right">
                    <a href="{{ route('setting.hourly-time-off.create') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Add Balance Group
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="hourly-balance-groups" class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Maximum</th>
                            <th>Period</th>
                            <th>Time Off Types</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->code }}</td>
                                <td>{{ $group->maximum_hours === null ? 'Unlimited' : number_format((float) $group->maximum_hours, 2) . ' hours' }}
                                </td>
                                <td>{{ ucfirst($group->period_type) }}</td>
                                <td>{{ $group->timeoffs_count }}</td>
                                <td>
                                    <span class="badge {{ $group->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $group->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('setting.hourly-time-off.show', $group->id) }}"
                                        class="btn btn-sm btn-info" title="View balances">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('setting.hourly-time-off.edit', $group->id) }}"
                                        class="btn btn-sm btn-primary" title="Edit group">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(function() {
            $('#hourly-balance-groups').DataTable({
                ordering: false,
                pageLength: 25,
                language: {
                    search: '',
                    searchPlaceholder: 'Search groups'
                }
            });
        });
    </script>
@endsection

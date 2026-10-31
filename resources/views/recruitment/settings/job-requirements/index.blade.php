@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <div class="x_panel">
            <div class="x_title">
                <h2>Job Requirements</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.settings.job-requirements.create') }}"
                            class="btn btn-success btn-sm text-white">
                            <i class="fa fa-plus"></i> Add Job Requirement
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content table-responsive">
                <table id="job-requirements-table" class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Title & Available Roles</th>
                            <th class="text-center">Status</th>
                            <th class="text-center job-requirement-actions-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jobRequirements as $jobRequirement)
                            <tr>
                                <td>
                                    <strong>{{ $jobRequirement->title }}</strong>
                                    @if ($jobRequirement->positions->isNotEmpty())
                                        <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px;">
                                            @foreach ($jobRequirement->positions as $pos)
                                                <span class="label label-info" style="font-size: 11px; padding: 3px 6px; display: inline-block;">{{ $pos->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST"
                                        action="{{ route('recruitment.settings.job-requirements.status', $jobRequirement) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active"
                                            value="{{ $jobRequirement->is_active ? 0 : 1 }}">
                                        <label class="job-requirement-toggle">
                                            <input type="checkbox" aria-label="Toggle {{ $jobRequirement->title }} status"
                                                {{ $jobRequirement->is_active ? 'checked' : '' }}
                                                onchange="this.form.submit()">
                                            <span class="job-requirement-toggle-slider"></span>
                                        </label>
                                        <span class="job-requirement-status">
                                            {{ $jobRequirement->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </form>
                                </td>
                                <td class="text-center job-requirement-actions-column">
                                    <div class="job-requirement-actions">
                                        <a href="{{ route('recruitment.settings.job-requirements.edit', $jobRequirement) }}"
                                            class="btn btn-primary btn-sm" aria-label="Edit {{ $jobRequirement->title }}"
                                            title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('recruitment.settings.job-requirements.destroy', $jobRequirement) }}"
                                            class="job-requirement-delete-form"
                                            onsubmit="return confirm('Delete this job requirement?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                aria-label="Delete {{ $jobRequirement->title }}" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
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
    <style>
        .job-requirement-actions-column {
            width: 120px;
            white-space: nowrap;
        }

        .job-requirement-actions {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .job-requirement-delete-form {
            display: inline-flex !important;
            margin: 0;
        }

        .job-requirement-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 30px;
            margin: 0;
        }

        .job-requirement-toggle {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 22px;
            margin: 0 6px 0 0;
            vertical-align: middle;
        }

        .job-requirement-toggle input {
            width: 0;
            height: 0;
            opacity: 0;
        }

        .job-requirement-toggle-slider {
            position: absolute;
            inset: 0;
            cursor: pointer;
            background: #ccc;
            border-radius: 22px;
            transition: .2s;
        }

        .job-requirement-toggle-slider:before {
            position: absolute;
            bottom: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            content: "";
            background: #fff;
            border-radius: 50%;
            transition: .2s;
        }

        .job-requirement-toggle input:checked+.job-requirement-toggle-slider {
            background: #26b99a;
        }

        .job-requirement-toggle input:checked+.job-requirement-toggle-slider:before {
            transform: translateX(20px);
        }

        .job-requirement-status {
            vertical-align: middle;
        }
    </style>
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(function() {
            $('#job-requirements-table').DataTable({
                pageLength: 25,
                responsive: true,
                ordering: true,
                language: {
                    search: '',
                    searchPlaceholder: 'Search job requirements'
                }
            });
        });
    </script>
@endsection

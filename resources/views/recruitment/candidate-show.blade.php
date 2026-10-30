@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .application-pipeline-form { min-width: 190px; }
        .application-pipeline-form .form-control { margin-bottom: 5px; }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Candidate Profile <small>{{ $candidate->candidate_code ?: 'Candidate' }}</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.candidates.applications.create', $candidate) }}"
                            class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Add Application
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recruitment.candidates.edit', $candidate) }}"
                            class="btn btn-default btn-sm">
                            <i class="fa fa-pencil"></i> Edit Candidate
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recruitment.candidates') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Candidate Database
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
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
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <strong>Full Name</strong>
                        <p>{{ $candidate->full_name }}</p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <strong>Email</strong>
                        <p><a href="mailto:{{ $candidate->email }}">{{ $candidate->email }}</a></p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <strong>Phone Number</strong>
                        <p><a href="tel:{{ $candidate->phone }}">{{ $candidate->phone }}</a></p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <strong>Current City</strong>
                        <p>{{ $candidate->current_city ?: '--' }}</p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <strong>Candidate Code</strong>
                        <p>{{ $candidate->candidate_code ?: '--' }}</p>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <strong>Total Applications</strong>
                        <p>{{ $candidate->applications->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_title">
                <h2>Application History</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content table-responsive">
                <table id="candidate-applications-table" class="table table-striped table-bordered table-sm"
                    style="width: 100%">
                    <thead>
                        <tr>
                            <th>Position Applied</th>
                            <th>Branch</th>
                            <th>Source</th>
                            <th>Registration Method</th>
                            <th>Status</th>
                            <th>Current Stage</th>
                            <th>HR PIC</th>
                            <th>Last Activity</th>
                            <th>Applied At</th>
                            <th>Consent Recorded</th>
                            <th>CV / Resume</th>
                            <th>Update Pipeline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidate->applications as $application)
                            <tr>
                                <td>{{ $application->position_applied ?: ($application->position->name ?? '--') }}</td>
                                <td>{{ $application->branch->name ?? '--' }}</td>
                                <td>{{ $application->source }}</td>
                                <td>{{ str_replace('_', ' ', ucfirst($application->registration_method)) }}</td>
                                <td>{{ $filterData['statuses'][$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}</td>
                                <td>{{ $filterData['stages'][$application->stage] ?? ucfirst(str_replace('_', ' ', $application->stage)) }}</td>
                                <td>{{ $application->hrPic->name ?? '--' }}</td>
                                <td>{{ $application->last_activity_at?->format('d M Y H:i') ?: '--' }}</td>
                                <td>{{ $application->applied_at?->format('d M Y H:i') ?: '--' }}</td>
                                <td>{{ $application->consent_at?->format('d M Y H:i') ?: '--' }}</td>
                                <td>
                                    @if ($application->resume_path)
                                        <a
                                            href="{{ route('recruitment.candidates.resume', [$candidate, $application]) }}">
                                            <i class="fa fa-download"></i> Download CV
                                        </a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST"
                                        action="{{ route('recruitment.candidates.applications.pipeline', [$candidate, $application]) }}"
                                        class="application-pipeline-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="stage" class="form-control input-sm" aria-label="Application stage">
                                            @foreach ($filterData['stages'] as $value => $label)
                                                <option value="{{ $value }}" @selected($application->stage === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select name="hr_pic_id" class="form-control input-sm" aria-label="HR PIC">
                                            <option value="">Unassigned</option>
                                            @foreach ($filterData['assignableHrPics'] as $pic)
                                                <option value="{{ $pic->id }}" @selected((string) $application->hr_pic_id === (string) $pic->id)>
                                                    {{ $pic->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <i class="fa fa-save"></i> Update
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
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(function() {
            $('#candidate-applications-table').DataTable({
                pageLength: 25,
                ordering: true,
                responsive: true,
                pagingType: 'simple',
                language: {
                    emptyTable: 'This candidate has no application history.'
                }
            });
        });
    </script>
@endsection

@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if ($section === 'dashboard')
            <div class="x_panel">
                <div class="x_title">
                    <h2>Applicant Tracking System <small>Recruitment workspace</small></h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <p>
                        Manage candidates from application intake through selection. The MHIS HUB ATS starts with
                        a candidate database, application management, and a recruitment pipeline.
                    </p>
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="well">
                                <h4><i class="fa fa-users"></i> Candidates</h4>
                                <p>Candidate profiles, contact information, documents, and application history.</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="well">
                                <h4><i class="fa fa-file-text-o"></i> Applications</h4>
                                <p>Candidate applications for job openings and their selection status.</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="well">
                                <h4><i class="fa fa-sitemap"></i> Recruitment Pipeline</h4>
                                <p>Track candidates centrally by selection stage.</p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info" role="status">
                        The ATS navigation is ready. Candidate and application management will be implemented
                        incrementally; the remaining menu items are currently placeholders.
                    </div>
                </div>
            </div>
        @else
            <div class="x_panel">
                <div class="x_title">
                    <h2>{{ $title }}</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="alert alert-info" role="status">
                        {{ $title }} is part of the Recruitment ATS structure and will be available in a
                        subsequent implementation phase.
                    </div>
                    <a href="{{ route('recruitment.index', ['section' => 'dashboard']) }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Recruitment Dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

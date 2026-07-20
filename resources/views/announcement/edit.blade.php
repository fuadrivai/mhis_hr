@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <style>
        .announcement-help {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .custom-audience-wrapper.is-hidden {
            display: none;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div>Please review the highlighted fields and try again.</div>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="announcementForm" action="/announcement/{{ $announcement->id }}" method="POST"
            enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            @include('announcement.form', ['announcement' => $announcement])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/announcement" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Announcement</button>
            </div>
        </form>
    </div>
@endsection

@section('content-script')
    @include('announcement.partials.scripts')
@endsection

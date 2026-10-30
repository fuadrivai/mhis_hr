@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12 col-xs-12">
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
                <h2>Edit Candidate <small>{{ $candidate->candidate_code }}</small></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('recruitment.candidates.show', $candidate) }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Profile
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="{{ route('recruitment.candidates.update', $candidate) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="full_name">Candidate Name *</label>
                            <input id="full_name" name="full_name" class="form-control" required maxlength="255"
                                value="{{ old('full_name', $candidate->full_name) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="email">Email *</label>
                            <input id="email" name="email" type="email" class="form-control" required maxlength="255"
                                value="{{ old('email', $candidate->email) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="phone">Phone Number *</label>
                            <input id="phone" name="phone" type="tel" class="form-control" required maxlength="50"
                                value="{{ old('phone', $candidate->phone) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="current_city">Current City</label>
                            <input id="current_city" name="current_city" class="form-control" maxlength="255"
                                value="{{ old('current_city', $candidate->current_city) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

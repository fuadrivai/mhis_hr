@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>View KPI Template</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-group">
                    <label>Template Name</label>
                    <input type="text" class="form-control" value="{{ $kpi_template->name }}" readonly disabled>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" disabled {{ $kpi_template->is_public ? 'checked' : '' }}> Make this template Public (Visible to other users)
                    </label>
                </div>

                <hr>
                <h4>Managerial Skills</h4>
                <div id="managerial-container">
                    @php $mIndex = 0; @endphp
                    @foreach($kpi_template->targets->where('type', 'managerial') as $mTarget)
                        <div class="card bg-light p-3 mb-2" id="mTarget-{{ $mIndex }}">
                            <div class="row">
                                <div class="col-md-9">
                                    <input type="text" class="form-control" value="{{ $mTarget->name }}" readonly disabled>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" value="{{ $mTarget->weight }}" readonly disabled>
                                </div>
                            </div>
                            <div class="mt-2 ml-4">
                                <h6>Sub Targets:</h6>
                                <div id="mSubContainer-{{ $mIndex }}">
                                    @foreach($mTarget->subTargets as $sub)
                                        @php $subIndex = uniqid(); @endphp
                                        <div class="row mb-1" id="mSub-{{ $subIndex }}">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm" value="{{ $sub->name }}" readonly disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control form-control-sm" value="{{ $sub->target_score }}" readonly disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control form-control-sm" value="{{ $sub->weight }}" readonly disabled>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @php $mIndex++; @endphp
                    @endforeach
                </div>

                <hr>
                <h4>To Achieved List (TAL)</h4>
                <div id="tal-container">
                    @php $tIndex = 0; @endphp
                    @foreach($kpi_template->targets->where('type', 'tal') as $tTarget)
                        <div class="row mb-2" id="tTarget-{{ $tIndex }}">
                            <div class="col-md-6">
                                <input type="text" class="form-control" value="{{ $tTarget->name }}" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" value="{{ $tTarget->target_score }}" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" value="{{ $tTarget->weight }}" readonly disabled>
                            </div>
                        </div>
                        @php $tIndex++; @endphp
                    @endforeach
                </div>

                <hr>
                <a href="{{ route('kpi-template.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection

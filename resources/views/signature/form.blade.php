@extends('layouts.main-layout')
@section('content-class')
@endsection

@section('content-child')
    <div class="col-md-12">
        @if (session()->has('message'))
            <div class="alert alert-{{ session('color') }} alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                @if (session()->has('title'))
                    <h6><i class="icon fas fa-ban"></i> {{ session('title') }} </h6>
                @else
                    <h6><i class="icon fas fa-check"></i> Success !</h6>
                @endif
                {{ session('message') }}
            </div>
        @endif
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <form id="location-form" autocomplete="OFF">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="name">Document Title</label>
                                            <input required type="text" id="name" value=""
                                                class="form-control" name="name" />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="name">Sign Date</label>
                                            <input required type="text" id="name" value=""
                                                class="form-control date-picker" name="name" />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="longitude">Document Link</label>
                                            <input type="text" value="" id="latitude" class="form-control"
                                                name="latitude" />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <Textarea id="description" rows="4" class="form-control" name="description"></Textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 text-right">
                                        <button type="submit" onclick="save()"
                                            class="btn btn-primary btn-block">Save</button>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div id="qrcode-container">
                                        <div id="qrcode"></div>
                                        <img id="icon" class="img" width="50" src="/images/only-logo.png" />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('content-script')
    <script></script>
@endsection

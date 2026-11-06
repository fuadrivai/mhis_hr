@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="row">
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title"><h2>Visitor Categories</h2><div class="clearfix"></div></div>
                    <div class="x_content">
                        <form method="POST" action="{{ route('visitor.settings.categories.store') }}" class="form-inline" style="margin-bottom:15px">
                            @csrf
                            <input name="name" class="form-control" placeholder="New category" required maxlength="255">
                            <label><input type="checkbox" name="requires_approval" value="1"> Approval required</label>
                            <label><input type="checkbox" name="requires_additional_info" value="1"> Extra information required</label>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </form>
                        @foreach ($categories as $category)
                            <form method="POST" action="{{ route('visitor.settings.categories.update', $category) }}" class="form-horizontal" style="border-top:1px solid #ddd;padding:12px 0">
                                @csrf @method('PUT')
                                <div class="form-group">
                                    <div class="col-sm-5"><input name="name" class="form-control" value="{{ $category->name }}" required maxlength="255"></div>
                                    <div class="col-sm-7">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="hidden" name="requires_approval" value="0">
                                        <input type="hidden" name="requires_additional_info" value="0">
                                        <label><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label>
                                        <label><input type="checkbox" name="requires_approval" value="1" @checked($category->requires_approval)> Approval</label>
                                        <label><input type="checkbox" name="requires_additional_info" value="1" @checked($category->requires_additional_info)> Extra info</label>
                                        <button class="btn btn-xs btn-success" type="submit">Save</button>
                                        <small class="text-muted">{{ $category->visits_count }} visits</small>
                                    </div>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="x_panel">
                    <div class="x_title"><h2>Visit Purposes</h2><div class="clearfix"></div></div>
                    <div class="x_content">
                        <form method="POST" action="{{ route('visitor.settings.purposes.store') }}" class="form-inline" style="margin-bottom:15px">
                            @csrf
                            <input name="name" class="form-control" placeholder="New purpose" required maxlength="255">
                            <label><input type="checkbox" name="requires_approval" value="1"> Approval required</label>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </form>
                        @foreach ($purposes as $purpose)
                            <form method="POST" action="{{ route('visitor.settings.purposes.update', $purpose) }}" class="form-horizontal" style="border-top:1px solid #ddd;padding:12px 0">
                                @csrf @method('PUT')
                                <div class="form-group">
                                    <div class="col-sm-5"><input name="name" class="form-control" value="{{ $purpose->name }}" required maxlength="255"></div>
                                    <div class="col-sm-7">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="hidden" name="requires_approval" value="0">
                                        <label><input type="checkbox" name="is_active" value="1" @checked($purpose->is_active)> Active</label>
                                        <label><input type="checkbox" name="requires_approval" value="1" @checked($purpose->requires_approval)> Approval</label>
                                        <button class="btn btn-xs btn-success" type="submit">Save</button>
                                        <small class="text-muted">{{ $purpose->visits_count }} visits</small>
                                    </div>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

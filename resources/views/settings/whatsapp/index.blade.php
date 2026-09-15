@extends('layouts.main-layout')
@section('content-child')
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Settings</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form action="{{ route('whatsapp.setting.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">API Key <span class="required">*</span></label>
                    <div class="col-md-6 col-sm-6">
                        <input type="text" name="api_key" required="required" class="form-control" value="{{ $setting->api_key ?? '' }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Sender Number <span class="required">*</span></label>
                    <div class="col-md-6 col-sm-6">
                        <input type="text" name="number" required="required" class="form-control" value="{{ $setting->number ?? '' }}">
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group row">
                    <div class="col-md-6 col-sm-6 offset-md-3">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Tags</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form action="{{ route('whatsapp.tag.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Tag Name <span class="required">*</span></label>
                    <div class="col-md-3 col-sm-3">
                        <input type="text" name="name" required="required" class="form-control">
                    </div>
                    <label class="col-form-label col-md-1 col-sm-1 label-align">Color</label>
                    <div class="col-md-2 col-sm-2">
                        <input type="color" name="color_code" required="required" class="form-control" value="#26b99a" style="height: 38px;">
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <button type="submit" class="btn btn-primary">Add Tag</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Color</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tags as $tag)
                        <tr>
                            <td>{{ $tag->name }}</td>
                            <td><span class="badge" style="background-color: {{ $tag->color_code }}; color: white; padding: 5px 10px;">{{ $tag->color_code }}</span></td>
                            <td>
                                <form action="{{ route('whatsapp.tag.destroy', $tag->id) }}" method="POST" onsubmit="return confirm('Remove tag?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($tags->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center">No tags found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Monitors</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form action="{{ route('whatsapp.monitor.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Select Employee <span class="required">*</span></label>
                    <div class="col-md-6 col-sm-6">
                        <select name="employee_id" class="form-control select2" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->user->name ?? 'No Name' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <button type="submit" class="btn btn-primary">Add Monitor</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monitors as $monitor)
                        <tr>
                            <td>{{ $monitor->employee->user->name ?? 'Unknown' }}</td>
                            <td>
                                <form action="{{ route('whatsapp.monitor.destroy', $monitor->id) }}" method="POST" onsubmit="return confirm('Remove monitor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($monitors->isEmpty())
                        <tr>
                            <td colspan="2" class="text-center">No monitors found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Chatters</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form action="{{ route('whatsapp.chatter.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Select Employee <span class="required">*</span></label>
                    <div class="col-md-6 col-sm-6">
                        <select name="employee_id" class="form-control select2" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->user->name ?? 'No Name' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Assigned Tags</label>
                    <div class="col-md-6 col-sm-6">
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_all_tags" value="1" checked id="isAllTagsCheck"> All Tags</label>
                        </div>
                        <select name="tags[]" class="form-control select2" multiple id="tagsSelect" disabled style="width: 100%;">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row mt-2">
                    <div class="col-md-6 col-sm-6 offset-md-3">
                        <button type="submit" class="btn btn-primary">Add/Update Chatter</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Assigned Tags</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chatters as $chatter)
                        <tr>
                            <td>{{ $chatter->employee->user->name ?? 'Unknown' }}</td>
                            <td>
                                @if($chatter->is_all_tags)
                                    <span class="badge badge-info" style="background-color: #17a2b8; color: white;">All Tags</span>
                                @else
                                    @foreach($chatter->tags as $t)
                                        <span class="badge" style="background-color: {{ $t->color_code }}; color: white; padding: 5px;">{{ $t->name }}</span>
                                    @endforeach
                                    @if($chatter->tags->isEmpty())
                                        <span class="text-muted">No Tags</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('whatsapp.chatter.destroy', $chatter->id) }}" method="POST" onsubmit="return confirm('Remove chatter?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($chatters->isEmpty())
                        <tr>
                            <td colspan="2" class="text-center">No chatters found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('content-script')
<script>
$(document).ready(function() {
    $('#isAllTagsCheck').change(function() {
        if($(this).is(':checked')) {
            $('#tagsSelect').prop('disabled', true);
        } else {
            $('#tagsSelect').prop('disabled', false);
        }
    });
});
</script>
@endsection

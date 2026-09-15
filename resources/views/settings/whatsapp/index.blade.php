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
                    <div class="col-md-3 col-sm-3">
                        <button type="submit" class="btn btn-primary">Add Chatter</button>
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
                    @foreach($chatters as $chatter)
                        <tr>
                            <td>{{ $chatter->employee->user->name ?? 'Unknown' }}</td>
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

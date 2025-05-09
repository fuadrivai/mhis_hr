@extends('layouts.main-layout')
@section('content-class')
@endsection

@section('content-child')
<div class="x_panel">
    <div class="x_title">
        <h2>Form Shift</h2>
        <div class="clearfix"></div>
    </div>
    <div class="x_content">
      <form autocomplete="OFF" action={{isset($data)?"/shift/".$data['id']:"/shift"}} method="POST">
        @csrf
        @if (isset($data['id']))
            <input class="d-none" name="id" id="id" type="text" value="{{ $data['id'] }}">
            @method('PUT')
        @endif
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="">Shift name</label>
              <input type="text" name="name" value="{{ old('name',$data['name']??'') }}" required class="form-control">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="">Code</label>
              <input type="text" name="code" value="{{ old('code',$data['code']??'') }}" class="form-control">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="">Shift Label</label>
              <select name="shift_label" class="form-control" id="shift-label">
                <option value="wfo">WFO</option>
                <option value="wfh">WFH</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="">Schedule in</label>
              <input type="text" value="{{ old('schedule_in',$data['schedule_in']??'') }}" name="schedule_in" required class="form-control has-feedback-left time-picker" id="schedule-in" placeholder="00:00">
              <span style="top: 25px" class="fa fa-clock-o form-control-feedback left" aria-hidden="true"></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="">Schedule in</label>
              <input type="text" value="{{ old('schedule_out',$data['schedule_out']??'') }}" name="schedule_out" required class="form-control has-feedback-left time-picker" id="schedule-out" placeholder="00:00">
              <span style="top: 25px" class="fa fa-clock-o form-control-feedback left" aria-hidden="true"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="">Break start</label>
              <input type="text" value="{{ old('break_start',$data['break_start']??'') }}" id="break-start" name="break_start" class="form-control has-feedback-left time-picker" placeholder="00:00">
              <span style="top: 25px" class="fa fa-clock-o form-control-feedback left" aria-hidden="true"></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="">Break end</label>
              <input type="text" value="{{ old('break_end',$data['break_end']??'') }}" id="break-end" name="break_end" class="form-control has-feedback-left time-picker" placeholder="00:00">
              <span style="top: 25px" class="fa fa-clock-o form-control-feedback left" aria-hidden="true"></span>
            </div>
          </div>
        </div>
        <div class="row justify-content-center pt-3">
          <div class="col-md-2 text-center">
            <button type="submit" class="btn btn-block btn-info"><i class="fa fa-save-o"> Save</i></button>
          </div>
        </div>
      </form>
    </div>
</div>
@endsection

@section('content-script')
@endsection
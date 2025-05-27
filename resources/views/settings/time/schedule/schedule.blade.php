@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_title">
            <h2>Form Schedule</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            @if (isset($data['id']))
                <input class="d-none" name="id" id="id" type="text" value="{{ $data['id'] }}">
                @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Shift name</label>
                        <input type="text" name="name" value="{{ old('name', $data['name'] ?? '') }}" required
                            class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Effective Date</label>
                        <input type="text" value="{{ old('effective_date', $data['effective_date'] ?? '') }}"
                            name="effective_date" required class="form-control has-feedback-left date-picker"
                            id="schedule-in" placeholder="dd MMMM yyyy">
                        <span style="top: 25px" class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Description</label>
                        <textarea name="description" id="description" class="form-control" cols="30" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row pb-2 pt-2">
                <div class="col-md-6">
                    <h4>Set shift pattern</h4>
                    <small>Create pattern & assign shift for this schedule</small>
                </div>
            </div>
            <div class="row">
                <table id="tbl-datatable" class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Shift</th>
                            <th>Working Hour</th>
                            <th>Break Hour</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="row pb-2 pt-2">
                <div class="col-md-6 text-left">
                    <button class="btn btn-secondary btn-sm" type="button" id="btn-add-shift">
                        <i class="fa fa-plus-circle"></i> Add shift
                    </button>
                </div>
            </div>
            <div class="row justify-content-end pt-3">
                <div class="col-md-2 text-right">
                    <button type="submit" class="btn btn-block btn-info"><i class="fa fa-save"> Save</i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/moment/min/moment.min.js"></script>
    <script>
        let scheduleDetails = [];
        $(document).ready(function() {
            // getShift();
            tbldata = $("#tbl-datatable").DataTable({
                paging: false,
                searching: false,
                length: false,
                info: false,
                data: scheduleDetails,
            });

            $('#btn-add-shift').on('click', function() {

            })
        })

        function getShift() {
            ajax(null, `{{ URL::to('shift/get') }}`, "GET",
                function(json) {
                    console.log(json)
                })
        }
    </script>
@endsection

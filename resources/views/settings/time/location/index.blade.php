@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/live-attendance-setting.css') }}">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel live-attendance-setting">
            <div class="x_content live-attendance-setting__content">
                <div class="live-attendance-setting__icon" aria-hidden="true">
                    <i class="fa fa-shield"></i>
                </div>
                <div>
                    <h4 class="live-attendance-setting__heading">Face Recognition</h4>
                    <span
                        class="live-attendance-setting__status {{ $liveAttendanceSetting->need_face_recognition ? 'live-attendance-setting__status--active' : 'live-attendance-setting__status--inactive' }}">
                        {{ $liveAttendanceSetting->need_face_recognition ? 'Required' : 'Not required' }}
                    </span>
                </div>
                <form method="POST" action="{{ url('setting/live-attendance/face-recognition') }}">
                    @csrf
                    @method('PUT')
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="need_face_recognition" value="0">
                        <input type="checkbox" class="custom-control-input" id="need-face-recognition"
                            name="need_face_recognition" value="1"
                            {{ $liveAttendanceSetting->need_face_recognition ? 'checked' : '' }}>
                        <label class="custom-control-label" for="need-face-recognition">
                            Require for attendance
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Save
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Cutoff Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table id="tbl-cutoff" class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Cutoff Day</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cutoffs as $cutoff)
                            <tr>
                                <td>{{ $cutoff->name }}</td>
                                <td class="text-center">{{ $cutoff->cutoff_day }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $cutoff->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $cutoff->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info text-white btn-edit-cutoff"
                                        data-id="{{ $cutoff->id }}" data-name="{{ $cutoff->name }}"
                                        data-cutoff-day="{{ $cutoff->cutoff_day }}"
                                        data-active="{{ $cutoff->is_active ? '1' : '0' }}">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Location Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table id="tbl-location" class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Location Setting Name</th>
                            <th>GPS Location</th>
                            <th>Assign To</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($locations as $loc)
                            <tr>
                                <td>{{ $loc->name }}</td>
                                <td>{{ $loc->detailsCount() }}</td>
                                <td>{{ $loc->employeesCount() }}</td>
                                <td><a href="/setting/location/{{ $loc->id }}/edit"
                                        class="btn btn-sm btn-info text-white"><i class="fa fa-pencil"></i> Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-cutoff" tabindex="-1" role="dialog" aria-labelledby="modal-cutoff-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="form-cutoff">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-cutoff-title">Edit Cutoff</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="cutoff-name">Name</label>
                            <input type="text" class="form-control" id="cutoff-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="cutoff-day">Cutoff Day</label>
                            <input type="number" class="form-control" id="cutoff-day" name="cutoff_day" min="1"
                                max="31" required>
                        </div>
                        <div class="form-group mb-0">
                            <label for="cutoff-active">Status</label>
                            <select class="form-control" id="cutoff-active" name="is_active" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-edit-cutoff').on('click', function() {
                const cutoff = $(this);
                $('#form-cutoff').attr('action', '{{ url('setting/location/cutoff') }}/' + cutoff.data(
                    'id'));
                $('#cutoff-name').val(cutoff.data('name'));
                $('#cutoff-day').val(cutoff.data('cutoff-day'));
                $('#cutoff-active').val(cutoff.data('active'));
                $('#modal-cutoff').modal('show');
            });

            $("#tbl-cutoff").DataTable({
                ordering: false,
                searching: false,
                lengthChange: false,
                paging: false,
            })
            $("#tbl-location").DataTable({
                ordering: false,
                searching: false,
                lengthChange: false,
                paging: false,
                dom: '<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip',
                buttons: [{
                    text: 'New Location  <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-assign'
                    },
                    className: 'btn btn-success ml-2 btn-sm font-weight-bold',
                    action: function() {
                        window.location.href = `/setting/location/create`
                    }
                }]
            })
        })
    </script>
@endsection

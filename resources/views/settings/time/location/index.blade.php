@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        .live-attendance-setting {
            border-top: 3px solid #26b99a;
        }

        .live-attendance-setting__content {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
            min-height: 92px;
            padding: 20px 8px;
        }

        .live-attendance-setting__icon {
            display: flex;
            flex: 0 0 48px;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 6px;
            background: #e8f6f2;
            color: #1f947b;
            font-size: 22px;
        }

        .live-attendance-setting__heading {
            margin: 0 0 4px;
            color: #2a3f54;
            font-size: 16px;
            font-weight: 600;
        }

        .live-attendance-setting__status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .live-attendance-setting__status--active {
            background: #e8f6f2;
            color: #167661;
        }

        .live-attendance-setting__status--inactive {
            background: #f1f3f5;
            color: #65727f;
        }

        .live-attendance-setting form {
            display: flex;
            align-items: center;
            gap: 20px;
            min-width: 0;
        }

        .live-attendance-setting .custom-control {
            min-height: 30px;
            padding-left: 58px;
        }

        .live-attendance-setting .custom-control-label {
            color: #33495e;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            line-height: 30px;
        }

        .live-attendance-setting .custom-control-label::before {
            top: 2px;
            left: -58px;
            width: 48px;
            height: 26px;
            border: 0;
            border-radius: 13px;
            background: #cbd3da;
            box-shadow: none;
        }

        .live-attendance-setting .custom-control-label::after {
            top: 5px;
            left: -55px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            transition: transform .2s ease;
        }

        .live-attendance-setting .custom-control-input:checked~.custom-control-label::before {
            background-color: #26b99a;
            box-shadow: none;
        }

        .live-attendance-setting .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(22px);
        }

        .live-attendance-setting .btn {
            min-width: 112px;
            border-radius: 4px;
            font-weight: 600;
        }

        @media (max-width: 767px) {
            .live-attendance-setting__content {
                align-items: flex-start;
            }

            .live-attendance-setting form {
                grid-column: 1 / -1;
                justify-content: space-between;
            }
        }

        @media (max-width: 480px) {
            .live-attendance-setting__content {
                gap: 12px;
            }

            .live-attendance-setting form {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .live-attendance-setting .btn {
                width: 100%;
            }
        }
    </style>
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
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#tbl-location").DataTable({
                ordering: false,
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

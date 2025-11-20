@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12  ">
        <div class="x_panel">
            <div class="x_title">
                <h2>Insert Filter</h2>
                <div class="col-4">

                </div>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <div class="form-group">
                            <input type="text" id="filter-date" class="form-control date-picker">
                        </div>
                    </li>
                    <li>
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content" style="display: none">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Branch</label>
                            <select name="branch" id="branch" class="form-control select2" style="width: 100%">
                                <option value="all">All branch</option>
                                @foreach ($branches as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Organization</label>
                            <select name="organization" id="organization" class="form-control select2" style="width: 100%">
                                <option value="all">All organization</option>
                                @foreach ($organizations as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Position</label>
                            <select name="position" id="position" class="form-control select2" style="width: 100%">
                                <option value="all">All position</option>
                                @foreach ($positions as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Level</label>
                            <select name="level" id="level" class="form-control select2" style="width: 100%">
                                <option value="all">All level</option>
                                @foreach ($levels as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <table id="tbl-location" class="table table-striped table-bordered table-sm" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Schedule In</th>
                            <th>Schedule Out</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filter-date').val(moment().format('DD MMMM YYYY'))
            tblAttendance = $("#tbl-location").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/time/attendance",
                    type: "GET",
                    data: function(d) {
                        d.date = $('#filter-date').val();
                        d.branch = $('#branch').val();
                        d.organization = $('#organization').val();
                        d.position = $('#position').val();
                        d.level = $('#level').val();
                    }
                },
                columns: [{
                        data: "fullname",
                        defaultContent: "--",
                    },
                    {
                        data: "date",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `${moment(data).format('ddd, DD MMM YYYY')}`
                        }
                    },
                    {
                        data: "shift_name",
                        defaultContent: "--",
                    },
                    {
                        data: "schedule_in",
                        defaultContent: "--",
                    },
                    {
                        data: "schedule_out",
                        defaultContent: "--",
                    },
                    {
                        data: "check_in",
                        defaultContent: "--",
                    },
                    {
                        data: "check_out",
                        defaultContent: "--",
                    },
                    {
                        data: "status",
                        defaultContent: "--",
                    },
                    {
                        data: "id",
                        defaultContent: "--",
                        mRender: function() {
                            return `
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Edit</a>
                                    <a class="dropdown-item" href="#">Vie history log</a>
                                    <a class="dropdown-item" href="#">Lock attendance</a>
                                    <a class="dropdown-item" href="#">Delete</a>
                                </div>
                            </div>`
                        }
                    },
                ]
            })

            $('#filter-date, #branch, #organization, #position, #level').on('change', function() {
                tblAttendance.ajax.reload();
            });
        })
    </script>
@endsection

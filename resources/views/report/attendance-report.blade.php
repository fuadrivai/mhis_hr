@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12  ">
        <div class="x_panel">
            <div class="x_content">
                <form id="form-filter" autocomplete="off">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="month">Month</label>
                                <input required type="text" id="filter-month" readonly class="form-control month-picker">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <select required name="branch" id="branch" class="form-control select2"
                                    style="width: 100%">
                                    <option disabled selected>-- Select Branch --</option>
                                    @foreach ($branches as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Organization</label>
                                <select name="organization" id="organization" class="form-control select2"
                                    style="width: 100%">
                                    <option value="all">All organization</option>
                                    @foreach ($organizations as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4" style="padding-top: 27px">
                            <button type="submit" class="btn btn-primary"> <i class="fa fa-search"></i> Search
                                Report</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div style="overflow-x: auto; white-space: nowrap;">
                    <table id="tbl-location" class="table table-stripe table-sm" style="width: 100%">
                        <thead>
                            <tr class="text-center" style="vertical-align: middle">
                                <th rowspan="2">Name</th>
                                <th rowspan="2">Organization</th>
                                <th rowspan="2">Position</th>
                                <th rowspan="2">Date</th>
                                <th colspan="2">Schedule</th>
                                <th colspan="2">Clock</th>
                                <th rowspan="2">Status</th>
                                <th rowspan="2">#</th>
                            </tr>
                            <tr class="text-center">
                                <th>In</th>
                                <th>Out</th>
                                <th>In</th>
                                <th>Out</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filter-month').val(moment().format('MMMM YYYY'))
            // tblAttendance = $("#tbl-location").DataTable({
            //     processing: true,
            //     serverSide: true,
            //     searching: false,
            //     lengthChange: false,
            //     ajax: {
            //         url: "/time/attendance",
            //         type: "GET",
            //         data: function(d) {
            //             d.searchName = $('#search').val();
            //             d.date = $('#filter-date').val();
            //             d.branch = $('#branch').val();
            //             d.organization = $('#organization').val();
            //             d.position = $('#position').val();
            //             d.level = $('#level').val();
            //         }
            //     },
            //     columns: [{
            //             data: "fullname",
            //             defaultContent: "--",
            //             mRender: function(data, type, full) {
            //                 return `${data}<br><small>${full.employee_id} - ${full.employee.employment.branch.name}</small>`
            //             }
            //         },
            //         {
            //             data: "fullname",
            //             defaultContent: "--",
            //             mRender: function(data, type, full) {
            //                 return `${full.employee.employment.organization.name}`
            //             }
            //         },
            //         {
            //             data: "fullname",
            //             defaultContent: "--",
            //             mRender: function(data, type, full) {
            //                 return `${full.employee.employment.job_position.name}`
            //             }
            //         },
            //         {
            //             data: "date",
            //             defaultContent: "--",
            //             mRender: function(data, type, full) {
            //                 return `${moment(data).format('ddd, DD MMM YYYY')}`
            //             }
            //         },
            //         {
            //             data: "schedule_in",
            //             defaultContent: "--",
            //         },
            //         {
            //             data: "schedule_out",
            //             defaultContent: "--",
            //         },
            //         {
            //             data: "check_in",
            //             defaultContent: "--",
            //         },
            //         {
            //             data: "check_out",
            //             defaultContent: "--",
            //         },
            //         {
            //             data: "status",
            //             defaultContent: "--",
            //         },
            //         {
            //             data: "id",
            //             defaultContent: "--",
            //             mRender: function() {
            //                 return `
        //                 <div class="btn-group">
        //                     <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle"
        //                         data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        //                         Actions
        //                     </button>
        //                     <div class="dropdown-menu">
        //                         <a class="dropdown-item" href="#">Edit</a>
        //                         <a class="dropdown-item" href="#">Vie history log</a>
        //                         <a class="dropdown-item" href="#">Lock attendance</a>
        //                     </div>
        //                 </div>`
            //             }
            //         },
            //     ]
            // })

            // $('#filter-month, #branch, #organization, #position, #level').on('change', function() {
            //     tblAttendance.ajax.reload();
            // });
        })
    </script>
@endsection

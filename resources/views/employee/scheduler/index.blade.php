@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        td img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 8px;
        }

        td .employee-name {
            vertical-align: middle;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>Employee name</th>
                                    <th>Employee ID</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Schedule</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <body>
                                @foreach ($data as $item)
                                    <tr>
                                        <td><input type="checkbox" class="row-check" value="{{ $item->id }}"></td>
                                        <td><img class="img" src="/images/user.png" alt="profile">
                                            <span class="employee-name">{{ $item->personal->fullname }}</span>
                                        </td>
                                        <td>{{ $item->employment->employee_id }}</td>
                                        <td>{{ $item->employment->branch->name }}</td>
                                        <td>{{ $item->employment->organization->name }}</td>
                                        <td><a class="btn-schedule" href="#"
                                                data-schedule="{{ optional($item->activeSchedule)->schedule_id }}"
                                                data-toggle="modal"
                                                data-target="#modal-shift">{{ optional($item->activeSchedule)->schedule_name }}</a>
                                        </td>
                                        <td>
                                            <button type="button" data-id="{{ $item->id }}"
                                                class="btn btn-info btn-sm btn-assign">
                                                Assign</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </body>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-data" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-data" autocomplete="OFF" method="POST">
                    @csrf
                    <div id="form-method">
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Assign Schedule</h5>
                        <button type="button" aria-hidden="true" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="employee_id" class="form-control d-none" name="employee_id" />
                        <div class="form-group">
                            <label for="name">Work Schedule</label>
                            <select name="schedule_id" id="schedule_id" class="form-control" required>
                                <option value="" disabled selected>--Select Schedule--</option>
                                @foreach ($schedules as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Effective Date</label>
                            <input required type="text" id="effective_date" class="form-control date-picker"
                                name="effective_date" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-shift" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="form-shift">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Shift</h5>
                        <button type="button" aria-hidden="true" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="tbl-shift" class="table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="text-center">Shift name</th>
                                    <th class="text-center">Schedule in</th>
                                    <th class="text-center">Schedule out</th>
                                    <th class="text-center">Break start</th>
                                    <th class="text-center">Break end</th>
                                </tr>
                            </thead>

                            <body></body>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary"> Close</button>
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
        let checkedList = new Set();
        let scheduleDetails = [];
        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                lengthMenu: [
                    [10, 25, 50, 100, 500],
                    [10, 25, 50, 100, 500]
                ],
                pageLength: 25,
                ordering: false,
                dom: '<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip',
                buttons: [{
                    text: '<span id="selected-info" class="ml-2 text-white"></span> - Assign Schedule  <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-assign'
                    },
                    className: 'btn btn-success ml-2 btn-sm font-weight-bold d-none',
                    action: function() {
                        $("#employee_id").val(Array.from(checkedList).join(','));
                        $('#modal-data').modal('show');
                    }
                }]
            });
            $('#checkAll').click(function() {
                let checked = this.checked;
                $('#tbl-datatable .row-check').prop('checked', checked);
                $('#tbl-datatable .row-check').each(function() {
                    let id = $(this).val();
                    if (checked) {
                        checkedList.add(id);
                    } else {
                        checkedList.delete(id);
                    }
                });
                updateInfo();
            });

            $('#tbl-datatable').on('change', '.row-check', function() {
                let checked = this.checked;
                let id = $(this).val();
                if (checked) {
                    checkedList.add(id);
                } else {
                    checkedList.delete(id);
                }
                updateInfo();
            });

            $("#tbl-datatable").on('click', '.btn-assign', function() {
                let id = $(this).data('id');
                $("#employee_id").val(id);
                $("#modal-data").modal('show');
            });

            $("#form-data").submit(function(e) {
                e.preventDefault();
                let employeeSchedule = {
                    employee_id: $("#employee_id").val(),
                    schedule_id: $("#schedule_id").val(),
                    schedule_name: $("#schedule_id option:selected").text(),
                    effective_start_date: moment($("#effective_date").val(), "DD MMMM YYYY").format(
                        "YYYY-MM-DD"),
                };
                let url = "{{ route('scheduler.store') }}";
                ajax(employeeSchedule, url, "POST",
                    function(json) {
                        $("#modal-data").modal('hide');
                        sweetAlert("Success", 'Schedule assigned successfully.');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    },
                    function(xhr) {
                        let res = xhr.responseJSON;
                        $("#modal-data").modal('hide');
                        if (res && res.message) {
                            sweetAlert("Error", res.message, "error");
                        } else {
                            sweetAlert("Error", "Something went wrong", "error");
                        }
                    });

            });
            tblShift = $("#tbl-shift").DataTable({
                paging: false,
                searching: false,
                length: false,
                info: false,
                data: scheduleDetails,
                columns: [{
                        data: "shift_name",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.schedule_in",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.schedule_out",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.break_start",
                        defaultContent: "-",
                    },
                    {
                        data: "shift.break_end",
                        defaultContent: "-",
                    },
                ],
                order: [
                    [0, "desc"]
                ],
                columnDefs: [{
                    targets: "_all",
                    className: "text-center align-middle"
                }]
            });

            $("#tbl-datatable").on('click', '.btn-schedule', function() {
                let id = $(this).data('schedule');
                getSchedule(id);
            });
        })

        function getSchedule(id) {
            let url = `{{ URL::to('setting/schedule/') }}/${id}`;
            ajax({}, url, "GET", function(json) {
                scheduleDetails = json.details;
                reloadJsonDataTable(tblShift, scheduleDetails);
            });
        }

        function updateInfo() {
            let count = checkedList.size;
            let btnAssign = $('.x_panel').find("#btn-assign");
            btnAssign.toggleClass('d-none', count === 0);
            btnAssign.find('#selected-info').text(count > 0 ? `${count} Employee Selected` : '');
        }
    </script>
@endsection

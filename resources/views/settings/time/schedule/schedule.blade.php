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
            <form id="form-schedule" action="{{ !isset($data) ? '/setting/schedule' : "/setting/schedule/$data->id" }}"
                autocomplete="off" method="POST">
                @csrf
                @if (isset($data->id))
                    <input class="d-none" name="id" id="id" type="text" value="{{ $data->id }}">
                    @method('PUT')
                @endif
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Shift name</label>
                                    <input type="text" id="name" name="name"
                                        value="{{ old('name', $data->name ?? '') }}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Effective Date</label>
                                    <input type="text" value="{{ old('effective_date', $data->effective_date ?? '') }}"
                                        name="effective-date" required class="form-control has-feedback-left date-picker"
                                        id="effective-date">
                                    <span style="top: 25px" class="fa fa-calendar form-control-feedback left"
                                        aria-hidden="true"></span>
                                </div>
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input {{ $data?->ignore_national_holiday ?? false ? 'checked' : '' }}
                                                id="national-holiday" name="national-holiday" type="checkbox"
                                                value="">
                                            Ignore national hiloday
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input {{ $data?->ignore_company_holiday ?? false ? 'checked' : '' }}
                                                id="company-holiday" name="company-holiday" type="checkbox" value="">
                                            Ignore company hiloday
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input {{ $data?->ignore_special_holiday ?? false ? 'checked' : '' }}
                                                id="special-holiday" name="special-holiday" type="checkbox" value="">
                                            Ignore special hiloday
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Description</label>
                                    <textarea value="{{ old('description', $data->description ?? '') }}" name="description" id="description"
                                        class="form-control" cols="30" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="col-12 d-none">
                                <div class="form-group">
                                    <textarea name="json-data" id="json-data" class="form-control" cols="30" rows="4"></textarea>
                                </div>
                            </div>
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
                    <div class="col-12">
                        <div class="table-responsive card-box">
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
                    </div>
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
            </form>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/moment/min/moment.min.js"></script>
    <script>
        let schedule = {
            details: []
        }
        let shifts = {!! $shifts !!}
        let appData = @json(isset($data) ? $data : null);
        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                paging: false,
                searching: false,
                length: false,
                info: false,
                data: schedule.details,
                columns: [{
                        data: "day",
                        defaultContent: "-",
                        className: "text-center"
                    },
                    {
                        data: "shift",
                        defaultContent: "-",
                        mRender: function(data, type, full) {
                            let select =
                                `<select class="form-control select-shift" data-id="${data?.id??''}">`
                            select +=
                                `<option value="">-- select shift --</option>`
                            shifts.forEach(option => {
                                select +=
                                    `<option value="${option.id}" ${(option.id==data?.id??'')?'selected':''}>${option.name}</option>`
                            });
                            select += '</select>';
                            return select;
                        }
                    },
                    {
                        data: "shift",
                        defaultContent: "-",
                        className: "text-center",
                        mRender: function(data, type, full) {
                            if ((Object.keys(data).length != 0)) {
                                return `<label class="font-weight-bold">${data.schedule_in} - ${data.schedule_out}</label> (${diffTime(data.schedule_in, data.schedule_out)})`
                            } else {
                                return ""
                            }
                        }
                    },
                    {
                        data: "shift",
                        defaultContent: "-",
                        className: "text-center",
                        mRender: function(data, type, full) {
                            if ((Object.keys(data).length != 0)) {
                                return `<label class="font-weight-bold">${data?.break_start??""} - ${data?.break_end??""}</label> (${diffTime(data.break_start, data.break_end)})`
                            } else {
                                return ""
                            }
                        }
                    },
                    {
                        data: "id",
                        defaultContent: "-",
                        className: "text-center",
                        mRender: function(data, type, full) {
                            return `<button class="btn btn-sm btn-danger delete-schedule"><i class="fa fa-trash"></i></button>`
                        }
                    },
                ]
            });

            if (appData) {
                schedule = appData;
                reloadJsonDataTable(tbldata, schedule.details);
            }

            $('#btn-add-shift').on('click', function() {
                let detail = {
                    day: null,
                    number: 0,
                    shift: {}
                }
                if (schedule.details.length == 0) {
                    detail.number = 1;
                } else {
                    const maxValue = Math.max.apply(null, schedule.details.map(item => item.number));
                    detail.number = maxValue + 1;
                }
                detail.day = `Day ${detail.number}`
                schedule.details.push(detail);
                reloadJsonDataTable(tbldata, schedule.details);
            })

            $('#tbl-datatable').on('change', '.select-shift', function() {
                let data = tbldata.row($(this).parents('tr')).data();
                let shiftId = $(this).val();
                let filterShift = shifts.filter(val => val.id == shiftId);
                data.shift = filterShift[0];
                reloadJsonDataTable(tbldata, schedule.details);
            })

            $('#tbl-datatable').on('click', '.delete-schedule', function() {
                let data = tbldata.row($(this).parents('tr')).index();
                schedule.details.splice(data, 1);
                for (let i = 0; i < schedule.details.length; i++) {
                    const e = schedule.details[i];
                    e.number = i + 1;
                    e.day = `Day ${e.number}`;
                }
                reloadJsonDataTable(tbldata, schedule.details);
            })

            $('#form-schedule').on('submit', function(e) {
                if (schedule.details.length === 0) {
                    e.preventDefault();
                    toastr.error("Please add at least one schedule before submitting")
                }
                let effectiveDate = moment($('#effective-date').val(), "DD MMMM YYYY").format("YYYY-MM-DD")
                schedule.name = $('#name').val();
                schedule.effectiveDate = effectiveDate;
                schedule.description = $('#description').val();
                schedule.ignoreNationalHoliday = $('#national-holiday').is(':checked');
                schedule.ignoreSpeciallHoliday = $('#special-holiday').is(':checked');
                schedule.ignoreCompanylHoliday = $('#company-holiday').is(':checked');

                $('#json-data').val(JSON.stringify(schedule));
            })
        })
    </script>
@endsection

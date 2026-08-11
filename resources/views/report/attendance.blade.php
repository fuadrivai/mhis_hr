@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date">Insert Periode</label>
                            <input type="text" readonly class="form-control month-picker" id="month" name="month"
                                value="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button onclick="onClickfilter()" type="button" class="btn btn-success btn-block mt-4"
                            id="btn-filter"><i class="fa fa-filter"></i>Filter</button>
                    </div>
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
        $(document).ready(function() {

        })

        async function onClickfilter() {
            let month = $('#month').val();
            if (month == '') {
                sweetAlert("Error", "Please select month first", "error")
            }

            let fulldate = moment(month, 'MMMM YYYY').format('YYYY-MM-DD');

            blockUI();
            let response = await ajaxPromise({
                url: "/report/attendance/filter",
                method: "GET",
                data: {
                    month: fulldate,
                }
            });
            return response;
        }
    </script>
@endsection

@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="" role="tabpanel" data-example-id="togglable-tabs">
            <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-toggle="pill" href="#tab_content1" role="tab"
                        aria-controls="custom-tabs-five-normal" aria-selected="true">
                        Schedule
                    </a>

                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="/setting/shift" role="tab" aria-controls="custom-tabs-five-normal"
                        aria-selected="false">
                        Shift
                    </a>
                </li>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div role="tabpanel" class="tab-pane active " id="tab_content1" aria-labelledby="home-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <a href="/setting/schedule/create" class="btn btn-success btn-sm text-white btn-add"><i
                                            class="fa fa-plus"></i> Add Schedule</a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive card-box">
                                        <table id="tbl-schedule" class="table table-striped table-bordered table-sm"
                                            style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>Schedule name</th>
                                                    <th>Effective date</th>
                                                    <th>Shift pattern</th>
                                                    <th>Description</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <body>
                                                @foreach ($data as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $item['name'] }}</td>
                                                        <td>{{ $item['effective_date'] }}</td>
                                                        <td>1</td>
                                                        <td>{{ $item['description'] }}</td>
                                                        <td>
                                                            <button data-id ="{{ $item['id'] }}"
                                                                data-desc="{{ $item['description'] }}"
                                                                data-name="{{ $item['name'] }}"
                                                                data-code="{{ $item['code'] }}" data-toggle="modal"
                                                                data-target="#modal-data" type="button"
                                                                class="btn btn-success btn-sm text-white btn-data">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
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
            tblSchedule = $("#tbl-schedule").DataTable();
        })
    </script>
@endsection

@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        .dataTable td,
        .dataTable th {
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="" role="tabpanel" data-example-id="togglable-tabs">
            <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a data-tab="all" class="nav-link" href="/setting/schedule" role="tab"
                        aria-controls="custom-tabs-five-normal" aria-selected="true">
                        Schedule
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a data-tab="active" class="nav-link active" data-toggle="pill" href="#tab_content2" role="tab"
                        aria-controls="custom-tabs-five-normal" aria-selected="false">
                        Shift
                    </a>
                </li>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="tab_content2" aria-labelledby="home-tab">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <a href="/setting/shift/create" class="btn btn-success btn-sm text-white btn-add"><i
                                            class="fa fa-plus"></i> Add Shift</a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive card-box">
                                        <table id="tbl-shift" class="table table-striped table-bordered table-sm"
                                            style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>Shift name</th>
                                                    <th>Code</th>
                                                    <th class="text-center">Working hour</th>
                                                    <th class="text-center">Break hour</th>
                                                    <th class="text-center">Overnight</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>

                                            <body>
                                                @foreach ($data as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $item->name }}</td>
                                                        <td>{{ $item->code }}</td>
                                                        <td class="text-center">{{ $item->schedule_in }} -
                                                            {{ $item->schedule_out }}
                                                            {{-- <br> {!! \App\Helpers\diffTime($item->schedule_in, $item->schedule_out) !!} --}}
                                                            <br> {{ $item->schedule_duration() }}
                                                        </td>
                                                        <td class="text-center">{{ $item->break_start }} -
                                                            {{ $item->break_end }}
                                                            <br> {{ $item->break_duration() }}
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($item->is_overnight)
                                                                <span class="badge badge-info">Yes</span>
                                                            @else
                                                                <span></span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="/setting/shift/{{ $item->id }}/edit"
                                                                class="btn btn-success btn-sm">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
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
            tblShift = $("#tbl-shift").DataTable();
        })
    </script>
@endsection

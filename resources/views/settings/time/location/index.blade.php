@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
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

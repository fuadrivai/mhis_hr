@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Location Table</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <a href="/location/create" class="btn btn-success btn-sm text-white"><i class="fa fa-plus"></i> Add Location</a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <table id="tbl-location" class="table table-striped table-bordered table-sm" style="width: 100%">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Branch</th>
                        <th>Employee</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($locations as $loc)
                        <tr>
                            <td>{{$loc->name}}</td>
                            <td>{{$loc->branch->name}}</td>
                            <td>{{$loc->total}}</td>
                            <td><a href="/location/{{$loc->id}}/edit" class="btn btn-sm btn-info text-white"><i class="fa fa-pencil"></i> Edit</a></td>
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
<script>
    $(document).ready(function(){
        $("#tbl-location").DataTable()
    })
</script>
@endsection
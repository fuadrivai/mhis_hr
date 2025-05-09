@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <button data-toggle="modal" data-target="#modal-data" type="button" class="btn btn-success btn-sm text-white btn-add"><i class="fa fa-plus"></i> Add Job Level</button>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="row">
                <div class="col-sm-12">
                    <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>Organization</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <body>
                            @foreach ($data as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$item['name']}}</td>
                                    <td>
                                        <button data-id ="{{$item['id']}}" data-name="{{$item['name']}}" data-toggle="modal" data-target="#modal-data" type="button" class="btn btn-success btn-sm text-white btn-data">
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

@include('settings.modal-general')
@endsection
@section('content-script')
<script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="/plugins/moment/min/moment.min.js"></script>


<script>
    $(document).ready(function(){
        tbldata=  $("#tbl-datatable").DataTable();

        $("#tbl-datatable").on('click','.btn-data',function(){
            let id = $(this).attr('data-id');
            let name = $(this).attr('data-name');
            $('#id').val(id);
            $('#name').val(name);
            $('#form-method').append(`@method('put')`);
            $('#form-data').attr('action',`/organization/${id}`)
        });

        $('.btn-add').on('click',function(){
            $('#form-method').append(`@method('post')`);
            $('#form-data').attr('action',"/organization")
        });
    })
</script>
@endsection
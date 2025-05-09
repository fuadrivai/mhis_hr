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
                                <th>Code</th>
                                <th>Branch</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <body>
                            @foreach ($data as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$item['code']}}</td>
                                    <td>{{$item['name']}}</td>
                                    <td>{{$item['description']}}</td>
                                    <td>
                                        <button data-id ="{{$item['id']}}" data-desc="{{$item['description']}}" data-name="{{$item['name']}}" data-code="{{$item['code']}}" data-toggle="modal" data-target="#modal-data" type="button" class="btn btn-success btn-sm text-white btn-data">
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


<div class="modal fade" id="modal-data" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-dataLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-data"  autocomplete="OFF" method="POST">
                @csrf
                <div id="form-method">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-dataLabel">Form Branch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="form-password" class="form-group">
                        <label for="name">Code</label>
                        <input type="text" id="id" class="form-control d-none" name="id" />
                        <input type="text" id="code" class="form-control" name="code" required/>
                    </div>
                    <div id="form-password" class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" class="form-control" name="name" required/>
                    </div>
                    <div id="form-password" class="form-group">
                        <label for="name">Address</label>
                        <input type="text" id="description" class="form-control" name="description"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
            let code = $(this).attr('data-code');
            let desc = $(this).attr('data-desc');
            $('#id').val(id);
            $('#name').val(name);
            $('#code').val(code);
            $('#description').val(desc);
            $('#form-method').append(`@method('put')`);
            $('#form-data').attr('action',`/branch/${id}`)
        });

        $('.btn-add').on('click',function(){
            $('#form-method').append(`@method('post')`);
            $('#form-data').attr('action',"/branch")
        });
    })
</script>
@endsection
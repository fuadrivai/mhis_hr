@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>User Table</h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <button data-toggle="modal" data-target="#modalUser" type="button" class="btn btn-success btn-sm text-white btn-add-user"><i class="fa fa-plus"></i> Add User</button>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="row">
                <div class="col-sm-12">
                    <table id="tbl-user" class="table table-striped table-bordered table-sm" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Last Login</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-user" autocomplete="OFF"  method="post">
                @csrf
                <div id="form-method">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUserLabel">Form User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="id" class="form-control d-none" name="id" />
                        <input type="text" id="name" class="form-control" name="name" required />
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="text" id="email" class="form-control" name="email" required />
                    </div>
                    <div id="form-password" class="form-group">
                        <label for="email">Password *</label>
                        <input type="text" id="password"  value="mutiaraharapan" class="form-control" name="password" />
                        <small class="text-info f-bold">You can change password *</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReset" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-change-password" action="/user/reset" autocomplete="OFF" method="POST">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUserLabel">Form User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="form-password" class="form-group">
                        <label for="password-reset">Password *</label>
                        <input type="text" id="id-reset" class="form-control d-none" name="id-reset" />
                        <input type="text" id="password-reset" class="form-control" name="password-reset" />
                        <small class="text-info f-bold">You can change password *</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
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
        tblUser=  $("#tbl-user").DataTable({
            processing:true,
            serverSide:true,
            ajax:{
                url:"{{ route('user.index') }}",
                type:"GET",
            },
            columns:[
                {
                    data:"name",
                    defaultContent:"--"
                },
                {
                    data:"email",
                    defaultContent:"--"
                },
                {
                    data:"updated_at",
                    defaultContent:"--",
                    mRender:function(data,type,full){
                        return moment(data).format("DD MMMM YYYY HH:mm:ss")
                    }
                },
                {
                    data: 'id',
                    mRender: function(data, type, full) {
                        return `<a data-toggle="modal" data-target="#modalUser" title="Edit" class="btn btn-sm btn-info text-white btn-edit-user"><i class="fa fa-pencil"></i> Edit</a>
                        <a data-toggle="modal" data-target="#modalReset"  title="Edit" class="btn btn-sm btn-danger text-white btn-reset-password"><i class="fa fa-refresh"></i> Reset Password</a>`
                    }
                }
            ],  
            columnDefs: [
                { 
                    className: "text-center",
                    targets: [2,3]
                },
            ],
            order:[[2,'desc']]
        });

        $("#tbl-user").on('click','.btn-edit-user',function(){
            let data = tblUser.row($(this).parents('tr')).data();
            $('#id').val(data.id);
            $('#name').val(data.name);
            $('#email').val(data.email);
            $('#form-password').addClass('d-none')
            $('#form-method').append(`
                @method('put')
            `)
            $('#form-user').attr('action',`/user/${data.id}`)
        })
        $("#tbl-user").on('click','.btn-reset-password',function(){
            let data = tblUser.row($(this).parents('tr')).data();
            $('#id-reset').val(data.id);
        })

        $(".btn-add-user").on('click',function(){
            $('#form-password').removeClass('d-none');
            $('#form-user').attr('action','/user')
            $('#form-method').append(`
                @method('post')
            `)
        })

        $('')
    })
</script>
@endsection
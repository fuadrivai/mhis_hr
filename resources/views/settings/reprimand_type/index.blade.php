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
                        <button data-toggle="modal" data-target="#modal-data" type="button"
                            class="btn btn-success btn-sm text-white btn-add"><i class="fa fa-plus"></i> Add Reprimand Type</button>
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
                                    <th>Reprimand Type Name</th>
                                    <th>Level</th>
                                    <th>Deduction Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reprimandTypes as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->level }}</td>
                                        <td>{{ $item->deduction_score }}</td>
                                        <td>
                                            <button data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-level="{{ $item->level }}" data-score="{{ $item->deduction_score }}"
                                                data-toggle="modal" data-target="#modal-data" type="button"
                                                class="btn btn-success btn-sm text-white btn-data">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <form action="{{ route('reprimand-type.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm text-white"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal-data" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modal-dataLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-data" autocomplete="OFF" method="POST">
                    @csrf
                    <div id="form-method"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Reprimand Type Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Reprimand Type Name</label>
                            <input type="text" id="id" class="form-control d-none" name="id" />
                            <input type="text" id="name" class="form-control" name="name" required/>
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <input type="number" id="level" class="form-control" name="level" value="1" min="1" required/>
                        </div>
                        <div class="form-group">
                            <label for="deduction_score">Deduction Score</label>
                            <input type="number" id="deduction_score" class="form-control" name="deduction_score" value="0" required/>
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

    <script>
        $(document).ready(function() {
            $("#tbl-datatable").DataTable();

            $("#tbl-datatable").on('click', '.btn-data', function() {
                let id = $(this).attr('data-id');
                let name = $(this).attr('data-name');
                let level = $(this).attr('data-level');
                let score = $(this).attr('data-score');
                $('#id').val(id);
                $('#name').val(name);
                $('#level').val(level);
                $('#deduction_score').val(score);
                $('#form-method').empty().append(`@method('put')`);
                $('#form-data').attr('action', `/setting/reprimand-type/${id}`);
            });

            $('.btn-add').on('click', function() {
                $('#id').val('');
                $('#name').val('');
                $('#level').val('1');
                $('#deduction_score').val('0');
                $('#form-method').empty();
                $('#form-data').attr('action', "/setting/reprimand-type");
            });
        })
    </script>
@endsection

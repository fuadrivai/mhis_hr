@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>KPI Templates</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a href="{{ route('kpi-template.create') }}" class="btn btn-success btn-sm text-white btn-add"><i class="fa fa-plus"></i> Add KPI Template</a>
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
                                    <th>Template Name</th>
                                    <th>Targets Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->targets->count() }}</td>
                                        <td>
                                            @if(auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1) || $item->created_by == auth()->id())
                                                <a href="{{ route('kpi-template.edit', $item->id) }}" class="btn btn-success btn-sm text-white" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <form action="{{ route('kpi-template.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm text-white" title="Delete"><i class="fa fa-trash"></i></button>
                                                </form>
                                            @endif

                                            <a href="{{ route('kpi-template.show', $item->id) }}" class="btn btn-info btn-sm text-white" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <form action="{{ route('kpi-template.copy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Copy this template?');">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm text-white" title="Copy"><i class="fa fa-copy"></i></button>
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
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $("#tbl-datatable").DataTable();
        });
    </script>
@endsection

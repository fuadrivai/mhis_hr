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
                    <div class="col-sm-12">
                        <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Level</th>
                                    <th>Position</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($approvals as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $item->organization->name ?? 'N/A' }}</td>
                                        <td>{{ $item->level->name ?? 'N/A' }}</td>
                                        <td>{{ $item->position->name ?? 'N/A' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary"
                                                onclick="window.location.href='/setting/approval/{{ $item->id }}/edit'"><i
                                                    class="fa fa-eye"></i> Edit</button>
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
    <script src="/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function() {
            tbldata = $("#tbl-datatable").DataTable({
                pageLength: 25,
                ordering: false,
                responsive: true,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Rule <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-rule'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        window.location.href = "/setting/approval/create";
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
            });
        })
    </script>
@endsection

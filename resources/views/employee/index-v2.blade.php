@extends('layouts.main-layout')
@section('content-class')
    <link rel="stylesheet" href="{{ asset('css/employee.css') }}">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel employee-filter-panel">
            <div class="x_title employee-filter-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="employee-section-title">Form Filter</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="#filterFormCollapse" class="text-dark employee-filter-toggle" data-toggle="collapse"
                            role="button" aria-expanded="true" aria-controls="filterFormCollapse"
                            id="filterCollapseToggle">
                            <i class="fa fa-chevron-up" id="filterCollapseIcon"></i>
                            <span id="filterCollapseLabel">Hide Filter</span>
                        </a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content collapse show employee-filter-body" id="filterFormCollapse">
                <div class="row employee-filter-grid">
                    <div class="col-md-4">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Insert Name or Email</label>
                            <input type="text" name="search" id="search" class="form-control"
                                placeholder="Full name or email">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Organization</label>
                            <select name="organization" id="organization" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($organizations as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Position</label>
                            <select name="position" id="position" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($positions as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Level</label>
                            <select name="level" id="level" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($levels as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Status</label>
                            <select name="status" id="status" class="select2 form-control">
                                <option value="all">All</option>
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group employee-field">
                            <label class="employee-field-label" for="">Branch</label>
                            <select name="branch" id="branch" class="select2 form-control">
                                <option value="all">All</option>
                                @foreach ($branches as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel employee-toolbar-panel">
            <div class="row employee-toolbar-row">
                <div class="col-md-6">
                    <div class="employee-toolbar-left">
                        <span class="employee-length-label">Length :</span>
                        <select id="dummy" name="dummy" class="form-control perpage employee-length-select"
                            style="height: 37px;width:80px">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <i class="employee-toolbar-summary" id="employee-page-summary"><b>Show {{ $page['from'] }} to
                                {{ $page['to'] }}
                                of total
                                {{ $page['total'] }}</b></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-right employee-toolbar-actions-wrap">
                        <div class="btn-group employee-toolbar-actions" role="group">
                            <a data-toggle="modal" data-target="#importExcel" href="/employee/create"
                                class="btn btn-info text-white btn-add-employee"><i class="fa fa-upload"></i>
                                Import</a>
                            <a href="/employee/create" class="btn btn-success text-white btn-add-employee"><i
                                    class="fa fa-plus"></i> Add User</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div id="employee-list" class="row">
            @include('employee._list')
        </div>
    </div>


    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="/employee/import" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                    </div>
                    <div class="modal-body">

                        {{ csrf_field() }}

                        <label>Choose File</label>
                        <div class="form-group">
                            <input type="file" name="file" required="required">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('content-script')
    <script>
        let typingTimer;
        $(document).ready(function() {
            $('#filterFormCollapse').collapse('hide');
            $('#filterFormCollapse').on('shown.bs.collapse', function() {
                $('#filterCollapseIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $('#filterCollapseLabel').text('Hide Filter');
                $('#filterCollapseToggle').attr('aria-expanded', 'true');
            });

            $('#filterFormCollapse').on('hidden.bs.collapse', function() {
                $('#filterCollapseIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('#filterCollapseLabel').text('Show Filter');
                $('#filterCollapseToggle').attr('aria-expanded', 'false');
            });

            $('#search').on('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    loadEmployee()
                }, 400);
            });

            $('.perpage, #level, #branch, #status, #organization, #position').on('change keyup', function() {
                loadEmployee();
            });

            $(document).on('click', '#employee-list .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                loadEmployee(url);
            });
        });

        function loadEmployee(url = "/employee") {
            blockUI();
            const data = {
                search: $('#search').val(),
                organization: $('#organization').val(),
                position: $('#position').val(),
                level: $('#level').val(),
                status: $('#status').val(),
                branch: $('#branch').val(),
                perpage: $('.perpage').val(),
            };

            $.ajax({
                url: url,
                data: data,
                type: "GET",
                success: function(html) {
                    const response = $('<div>').html(html);
                    const summaryHtml = response.find('.employee-page-summary-source').html();

                    $('#employee-list').html(html);

                    if (summaryHtml) {
                        $('#employee-page-summary').html(summaryHtml);
                    }
                }
            });
        }
    </script>
@endsection

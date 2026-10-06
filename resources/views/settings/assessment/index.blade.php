@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-tab-content {
            padding-top: 20px;
        }

        .form-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-cogs"></i> Assessment Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div role="tabpanel">
                    <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_approvers" id="approvers-tab" role="tab" data-toggle="tab"
                                data-assessment-tab="approvers" aria-expanded="true"><i class="fa fa-users"></i>
                                Approvers</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_monitors" id="monitors-tab" role="tab" data-toggle="tab"
                                data-assessment-tab="monitors" aria-expanded="false"><i class="fa fa-eye"></i> Monitors</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_assignments" id="assignments-tab" role="tab" data-toggle="tab"
                                data-assessment-tab="assignments" aria-expanded="false"><i class="fa fa-check-square-o"></i>
                                Employee Assignments</a>
                        </li>
                    </ul>
                    <div id="myTabContent" class="tab-content custom-tab-content">
                        <div role="tabpanel" class="tab-pane fade active in" id="tab_approvers"
                            aria-labelledby="approvers-tab">
                            <p><i class="fa fa-spinner fa-spin"></i> Loading approvers...</p>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="tab_monitors" aria-labelledby="monitors-tab"></div>
                        <div role="tabpanel" class="tab-pane fade" id="tab_assignments" aria-labelledby="assignments-tab">
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
    <script>
        $(document).ready(function() {
            var tabUrlTemplate = @json(route('assessment-setting.tab', ['tab' => '__assessment_tab__']));

            function loadAssessmentTab(tab) {
                var $pane = $('#tab_' + tab);
                $pane.html('<p><i class="fa fa-spinner fa-spin"></i> Loading...</p>');

                $.get(tabUrlTemplate.replace('__assessment_tab__', encodeURIComponent(tab)))
                    .done(function(html) {
                        $pane.html(html);
                        $pane.find('.datatable').DataTable({
                            language: {
                                emptyTable: 'No data available in this section'
                            }
                        });
                        $pane.find('.select2').select2({
                            width: '100%'
                        });

                        var $replaceTable = $pane.find('#replaceEmployeeTable');
                        if ($replaceTable.length) {
                            $replaceTable.DataTable({
                                language: {
                                    emptyTable: 'No active employees available'
                                },
                                pageLength: 10,
                                lengthMenu: [
                                    [10, 25, 50, -1],
                                    [10, 25, 50, 'All']
                                ]
                            });
                        }
                    })
                    .fail(function(xhr) {
                        $pane.html('<div class="alert alert-danger">Unable to load this section (HTTP ' +
                            xhr.status + '). Please try again.</div>');
                    });
            }

            $('a[data-assessment-tab]').on('click', function() {
                loadAssessmentTab($(this).data('assessment-tab'));
            });
            $('#approvers-tab').tab('show');
            loadAssessmentTab('approvers');

            $(document).on('click', '.btn-edit-approver', function() {
                var id = $(this).data('id');
                var category = $(this).data('category');
                var employee = $(this).data('employee');
                var level = $(this).data('level');
                var classes = $(this).data('classes');
                var subjects = $(this).data('subjects');

                $('#editApproverForm').attr('action', '/setting/assessment/approver/' + id);
                $('#edit_category_id').val(category).trigger('change');
                $('#edit_employee_id').val(employee).trigger('change');
                $('#edit_level').val(level);
                $('#edit_school_class_ids').val(classes).trigger('change');
                $('#edit_subject_ids').val(subjects).trigger('change');

                $('#editApproverModal').modal('show');
            });

            $(document).on('show.bs.modal', '#replaceEmployeeModal', function(event) {
                var replaceButton = $(event.relatedTarget);
                $('#replaceEmployeeForm').attr('action', replaceButton.data('replace-url'));
            });

            $(document).on('shown.bs.modal', '#replaceEmployeeModal', function() {
                $('#replaceEmployeeTable').DataTable().columns.adjust();
            });
        });
    </script>
@endsection

@extends('layouts.main-layout')

@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/side-drawer-modal-bootstrap/bootstrap-side-modals.css" rel="stylesheet">
@endsection
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Approval Rule Name *</label>
                                    <input required type="text" id="name" class="form-control" name="name"
                                        value="{{ $rule->name ?? '' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="branch">Branch *</label>
                                    <select name="branch" id="branch" class="form-control">
                                        <option value="">-- Select Branch --</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ isset($rule) && old('branch', $rule->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="organization">Organization *</label>
                                    <select name="organization" id="organization" class="form-control">
                                        <option value="">-- Select Organization --</option>
                                        @foreach ($organizations as $organization)
                                            <option value="{{ $organization->id }}"
                                                {{ isset($rule) && old('organization', $rule->organization_id ?? '') == $organization->id ? 'selected' : '' }}>
                                                {{ $organization->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="level">Level *</label>
                                    <select name="level" id="level" class="form-control">
                                        <option value="">-- Select Level --</option>
                                        @foreach ($jobLevels as $jobLevel)
                                            <option value="{{ $jobLevel->id }}"
                                                {{ isset($rule) && old('level', $rule->job_level_id ?? '') == $jobLevel->id ? 'selected' : '' }}>
                                                {{ $jobLevel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="position">Position *</label>
                                    <select name="position" id="position" class="form-control">
                                        <option value="">-- Select Position --</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ isset($rule) && old('position', $rule->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
                <div class="page-title">
                    <div class="title_left">
                        <h5>Approval Steps</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button type="button" class="btn btn-sm btn-secondary" id="btn-add-rule"><i class="fa fa-plus"></i> Add
                    Steps</button>
            </div>
        </div>
    </div>
    <div class="col-md-12" id="rules-container">

    </div>
    <div class="col-md-12 text-center">
        <button onclick="submitWorkflow()" type="button" class="btn btn-success"><i class="fa fa-save"></i> Submit Approval
            Rule</button>
    </div>

    <div class="modal modal-right fade" id="right-modal-user" tabindex="-1" role="dialog"
        aria-labelledby="right_modal_lg">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table id="tbl-employee" class="table table-striped table-bordered table-sm"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="checkAll" class="checkAll"></th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Level</th>
                                        <th>Organization</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-fixed">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-submit-employee">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/loadingoverlay/loadingoverlay.min.js"></script>
    <script src="/plugins/jquery-ui/jquery-ui.js"></script>
    <script>
        let workflow = {
            name: null,
            branch: null,
            organization: null,
            level: null,
            position: null,
            steps: []
        };
        let selectedStep = null;
        let selectedEmployess = [];
        $(document).ready(function() {
            renderSteps();
            $("#rules-container").sortable({
                placeholder: "ui-state-highlight",

                start: function(event, ui) {
                    ui.item.data('oldIndex', ui.item.index());
                },

                update: function(event, ui) {

                    const oldIndex = ui.item.data('oldIndex');
                    const newIndex = ui.item.index();

                    if (oldIndex === newIndex) return;

                    const moved = workflow.steps.splice(oldIndex, 1)[0];
                    workflow.steps.splice(newIndex, 0, moved);

                    workflow.steps.forEach((step, i) => {
                        step.index = i + 1;
                        step.name = "Step " + (i + 1);
                    });

                    renderSteps();
                }
            });

            $('#btn-add-rule').on('click', function() {
                let stepNumber = workflow.steps.length + 1;
                let step = {
                    index: stepNumber,
                    name: `Step ${stepNumber}`,
                    approvers: [],
                    approval_mode: 'any'
                };
                workflow.steps.push(step);
                renderSteps();
            });

            $("#rules-container").on('change', '.step-logic', function() {
                let index = $(this).data('index');
                workflow.steps[index - 1].approval_mode = $(this).val();
            })
            $("#rules-container").on('click', '.remove-step', function() {
                let index = $(this).data('index');
                workflow.steps.splice(index - 1, 1);
                workflow.steps.forEach((step, i) => {
                    step.index = i + 1;
                    step.name = "Step " + (i + 1);
                });

                selectedEmployess = selectedEmployess.filter(emp => emp.index != index);
                renderSteps();
            })
            $("#rules-container").on('click', '.add-approvers', function() {
                let index = $(this).data('index');
                selectedStep = workflow.steps[index - 1];
                $("#right-modal-user").modal('show');
                renderSteps();
            })

            $("#rules-container").on('click', '.remove-approver', function() {
                let empId = $(this).data('id');
                let stepIndex = $(this).data('step');
                workflow.steps[stepIndex - 1].approvers = workflow.steps[stepIndex - 1].approvers.filter(
                    approver => approver.employeeId !== empId);
                selectedEmployess = selectedEmployess.filter(emp => emp.employee.id !== empId);
                renderSteps();
            })

            $('#tbl-employee').on('change', 'td input[type="checkbox"]', function() {
                let employee = tblUser.row($(this).parents('tr')).data();
                let val = $(this).prop('checked');
                if (val == true) {
                    const isExist = selectedEmployess.some(emp => emp.employee.id == employee.id);
                    if (isExist) return;
                    let setpEmployee = {
                        index: selectedStep.index,
                        employee: employee
                    }
                    selectedEmployess.push(setpEmployee)
                } else {
                    selectedEmployess = selectedEmployess.filter(emp => emp.employee.id !== employee.id);
                }
            })

            $('#right-modal-user').on('show.bs.modal', function(e) {
                tblUser = $("#tbl-employee").DataTable({
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    ajax: {
                        url: "{{ URL::to('setting/location/employee/filter') }}",
                        type: "GET",
                    },
                    columns: [{
                            data: "id",
                            defaultContent: "--",
                            mRender: function(data, type, full) {
                                return `<input type="checkbox" class="input-check" data-id="${data}">`
                            }
                        },
                        {
                            data: "personal.fullname",
                            defaultContent: "--",
                            mRender: function(data, type, full) {
                                return `<strong>${data}</strong><br>${full.personal.email}`
                            }
                        },
                        {
                            data: "employment.branch_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.job_level_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.organization_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.job_position_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.employment_status",
                            defaultContent: "--"
                        },
                    ],
                    drawCallback: function(settings) {
                        var api = this.api();
                        var node = api.rows().nodes()
                        for (var i = 0; i < node.length; i++) {
                            let empId = $(node[i]).find('input').attr('data-id')
                            let isExist = workflow.steps[selectedStep.index - 1].approvers.some(
                                item => item.employeeId == empId)
                            let outOfSelected = workflow.steps.filter(step => step.index !=
                                selectedStep.index).some(step => step.approvers.some(
                                approver => approver.employeeId ==
                                empId))
                            if (isExist) {
                                $(node[i]).find('input').prop('checked', true)
                            }
                            if (outOfSelected) {
                                $(node[i]).find('input').prop('checked', true)
                                $(node[i]).find('input').prop('disabled', true)
                            }

                        }
                    },
                });
            })
            $('#right-modal-user').on('hidden.bs.modal', function(e) {
                selectedStep = null;
                $("#tbl-employee").DataTable().destroy();
            })

            $('#btn-submit-employee').on('click', function() {
                workflow.steps[selectedStep.index - 1].approvers = [];
                selectedEmployess.filter(emp => emp.index == selectedStep.index).forEach(emp => {
                    workflow.steps[selectedStep.index - 1].approvers.push({
                        employeeId: emp.employee.employment.id,
                        employeeName: emp.employee.personal.fullname
                    });
                })

                $("#right-modal-user").modal('hide')
                renderSteps();
            })
        })

        async function submitWorkflow() {
            try {
                workflow.name = $("#name").val();
                workflow.branch = $("#branch").val();
                workflow.organization = $("#organization").val();
                workflow.level = $("#level").val();
                workflow.position = $("#position").val();
                //validation
                if (!workflow.name || !workflow.branch || !workflow.organization || !workflow.level || !workflow
                    .position) {
                    sweetAlert("Error", "Please fill all required fields", "error");
                    return;
                }
                if (workflow.steps.length == 0) {
                    sweetAlert("Error", "Please add at least one approval step", "error");
                    return;
                }
                for (let step of workflow.steps) {
                    if (step.approvers.length == 0) {
                        sweetAlert("Error", `Please add at least one approver for ${step.name}`, "error");
                        return;
                    }
                }

                blockUI();
                let res = await ajaxPromise({
                    url: "/setting/approval",
                    method: "POST",
                    data: workflow
                })
                setTimeout(() => {
                    window.location.href = "/setting/approval";
                }, 1000);
                sweetAlert("Success", "Approval rule has been created", "success");
            } catch (error) {
                sweetAlert("Error", "Something went wrong. Please try again later.", "error");
                return;
            }
        }

        function renderSteps() {
            let container = $("#rules-container");
            container.empty();
            if (workflow.steps.length == 0) {
                let emptyHtml = `
                    <div class="x_panel">
                        <div class="x_title">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h5><i class="fa fa-list"></i> No Approval Steps Added</h5>
                                </div>
                            </div>
                        </div>
                        <div class="x_content">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <p class="text-muted">Click "Add Steps" to create your first approval step.</p>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                `;
                container.append(emptyHtml);
                return;
            }
            workflow.steps.forEach((step, index) => {
                let idx = index + 1;
                let stepHtml = `
                    <div class="x_panel" style="cursor: move;" data-index="${idx}">
                        <div class="x_title">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="fa fa-list"></i> ${step.name}</h5>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-sm btn-primary add-approvers" data-index="${idx}"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger remove-step" data-index="${idx}"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content" id="step-${idx}-approvers">
                `;
                step.approvers.forEach(approver => {
                    stepHtml += `<div class="badge badge-secondary mr-2" id="approver-${approver.employeeId}">
                                <div class="m-1"><label style="font-size: 14px;">${approver.employeeName} <i class="fa fa-times ml-1 remove-approver" data-step="${idx}" data-id="${approver.employeeId}" style="cursor: pointer;"></i></div></label>
                                <div class="clearfix"></div>
                            </div>`;
                });

                stepHtml += `<div class="mt-2">
                                <label class="font-weight-bold">Approval Logic : </label> <br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input step-logic" data-index="${idx}" type="radio" id="step-logic-any-${idx}" name="step-logic-${idx}" value="any" ${step.approval_mode === 'any' ? 'checked' : ''}>
                                    <label class="form-check-label" for="step-logic-any-${idx}">Can be approved by any of the approvers</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input step-logic" data-index="${idx}" type="radio" id="step-logic-all-${idx}" name="step-logic-${idx}" value="all" ${step.approval_mode === 'all' ? 'checked' : ''}>
                                    <label class="form-check-label" for="step-logic-all-${idx}">Must be approved by all approvers</label>
                                </div>
                            </div>`;
                stepHtml += `</div></div>`;
                container.append(stepHtml);
            });
        }
    </script>
@endsection

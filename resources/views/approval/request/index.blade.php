@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline-container {
            max-height: 500px;
            overflow-y: auto;
        }

        .approver-item {
            transition: all 0.2s ease;
        }

        .approver-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .timeline-item.active .timeline-marker {
            background-color: #007bff !important;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #007bff;
        }
    </style>
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_content">
            <div class="row">
                <div class="col-md-12">
                    <table id="tbl-datatable" class="table table-striped table-bordered table-sm" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Requester</th>
                                <th>Time Off Type</th>
                                <th>Status</th>
                                <th>Step</th>
                                <th>Created At</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Timeline Modal -->
    <div class="modal fade" id="timelineModal" tabindex="-1" role="dialog" aria-labelledby="timelineModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="timelineModalLabel">
                        <i class="fa fa-history"></i> Request Timeline
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/time/request/datatable",
                    type: "GET",
                },
                pageLength: 25,
                ordering: false,
                responsive: true,
                pagingType: 'simple',
                dom: `<"row"<"col-sm-6 d-flex align-items-center"lB><"col-sm-6"f>>tip`,
                buttons: [{
                    text: 'Add Request <i class="fa fa-plus-circle"></i>',
                    attr: {
                        id: 'btn-approval-request'
                    },
                    className: 'btn btn-success font-weight-bold mx-1',
                    action: function() {
                        window.location.href = "/time/request/create";
                    }
                }],
                language: {
                    info: "Page _PAGE_ of _PAGES_",
                    lengthMenu: "_MENU_ ",
                    search: "",
                    searchPlaceholder: "Search.."
                },
                columns: [{
                        data: "requester.personal.fullname",
                        defaultContent: "--",
                    },
                    {
                        data: "type",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return data && data.name ? data.name : '--';
                        }
                    },
                    {
                        data: "status",
                        defaultContent: "--",
                        className: "text-center",
                        mRender: function(data) {
                            const badge = data === 'approved' ? 'success' : data === 'rejected' ?
                                'danger' : data === 'cancelled' ? 'secondary' : 'warning';
                            return `<span class="badge badge-${badge}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : '--'}</span>`;
                        }
                    },
                    {
                        data: "current_step",
                        className: "text-center",
                        defaultContent: "--",
                    },
                    {
                        data: "created_at",
                        defaultContent: "--",
                        className: "text-center",
                        mRender: function(data) {
                            return moment(data).format('DD MMM YYYY HH:mm');
                        }
                    },
                    {
                        data: "id",
                        className: "text-center",
                        mRender: function(data) {
                            return `
                            <button type="button" class="btn btn-sm btn-secondary btn-timeline" data-id="${data}" title="View timeline">
                                <i class="fa fa-history"></i>
                            </button>
                            <a href="/time/request/${data}/edit" class="btn btn-sm btn-primary" title="Edit Request">
                                <i class="fa fa-edit"></i>
                            </a>
                            `;
                        }
                    },
                ],
            });
        });

        $(document).on('click', '.btn-timeline', function() {
            const requestId = $(this).data('id');
            $('#timelineModal').modal('show');
            loadTimelineData(requestId);
        });

        function loadTimelineData(requestId) {
            // Show loading
            $('#timelineModal .modal-body').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading timeline data...</p>
                </div>
            `);

            // Load approvers and history data
            Promise.all([
                ajaxPromise('/time/request/' + requestId + '/approver'),
                ajaxPromise('/time/request/' + requestId + '/history')
            ]).then(function([approversData, historyData]) {
                renderTimelineModal(approversData, historyData);
            }).catch(function(error) {
                console.error('Error loading timeline data:', error);
                $('#timelineModal .modal-body').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        Error loading timeline data. Please try again.
                    </div>
                `);
            });
        }

        function renderTimelineModal(approvers, history) {
            const modalBody = `
                <div class="timeline-container">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="timelineTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="approvers-tab" data-toggle="tab" href="#approvers" role="tab">
                                <i class="fa fa-users"></i> Approvers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="history-tab" data-toggle="tab" href="#history" role="tab">
                                <i class="fa fa-history"></i> History
                            </a>
                        </li>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content mt-3" id="timelineTabsContent">
                        <!-- Approvers Tab -->
                        <div class="tab-pane fade show active" id="approvers" role="tabpanel">
                            <div class="approvers-list">
                                ${renderApprovers(approvers)}
                            </div>
                        </div>

                        <!-- History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <div class="history-timeline">
                                ${renderHistory(history)}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#timelineModal .modal-body').html(modalBody);
        }

        function renderApprovers(approvers) {
            if (!approvers || approvers.length === 0) {
                return `
                    <div class="text-center py-4 text-muted">
                        <i class="fa fa-users fa-2x mb-2"></i>
                        <p>No approvers assigned</p>
                    </div>
                `;
            }

            return approvers.map((approver, index) => `
                <div class="approver-item mb-3 p-3 border rounded">
                    <div class="d-flex align-items-center">
                        <div class="approver-avatar mr-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-user"></i>
                            </div>
                        </div>
                        <div class="approver-info flex-grow-1">
                            <h6 class="mb-1">${approver.approver?.personal?.fullname || 'Unknown'}</h6>
                            <small class="text-muted">
                                Step ${approver.step_order} • ${approver.status || 'Pending'}
                            </small>
                        </div>
                        <div class="approver-status">
                            ${getStatusBadge(approver.status)}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderHistory(history) {
            if (!history || history.length === 0) {
                return `
                    <div class="text-center py-4 text-muted">
                        <i class="fa fa-history fa-2x mb-2"></i>
                        <p>No history available</p>
                    </div>
                `;
            }

            return `
                <div class="timeline">
                    ${history.map((item, index) => `
                                                            <div class="timeline-item ${index === 0 ? 'active' : ''}">
                                                                <div class="timeline-marker bg-primary"></div>
                                                                <div class="timeline-content">
                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                        <div>
                                                                            <h6 class="mb-1">${item.action || 'Action performed'}</h6>
                                                                            <p class="mb-1 text-muted">${item.notes || ''}</p>
                                                                            <small class="text-muted">
                                                                                By: ${item.user?.personal?.fullname || 'System'}
                                                                            </small>
                                                                        </div>
                                                                        <small class="text-muted">
                                                                            ${moment(item.created_at).format('DD MMM YYYY HH:mm')}
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `).join('')}
                </div>
            `;
        }

        function getStatusBadge(status) {
            const statusConfig = {
                'pending': {
                    class: 'warning',
                    icon: 'fa-clock'
                },
                'approved': {
                    class: 'success',
                    icon: 'fa-check'
                },
                'rejected': {
                    class: 'danger',
                    icon: 'fa-times'
                },
                'cancelled': {
                    class: 'secondary',
                    icon: 'fa-ban'
                }
            };

            const config = statusConfig[status] || statusConfig['pending'];
            return `<span class="badge badge-${config.class}"><i class="fa ${config.icon}"></i> ${status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pending'}</span>`;
        }

        function ajaxPromise(url) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: resolve,
                    error: reject
                });
            });
        }
    </script>
@endsection

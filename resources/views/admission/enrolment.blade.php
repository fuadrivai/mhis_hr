@extends('layouts.main-layout')

@section('content-class')
    <link rel="stylesheet" href="/css/employee.css">
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <style>
        .enrolment-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(25, 42, 70, 0.06);
            margin-bottom: 20px;
            padding: 14px 24px 24px;
        }

        .enrolment-card-header {
            align-items: center;
            border-bottom: 1px solid #d5dbe2;
            display: flex;
            gap: 14px;
            padding-bottom: 8px;
        }

        .enrolment-avatar {
            background: linear-gradient(135deg, #4c54e8, #3524b9);
            border-radius: 50%;
            color: #fff;
            flex: 0 0 40px;
            font-size: 18px;
            font-weight: 700;
            height: 40px;
            line-height: 40px;
            text-align: center;
        }

        .enrolment-filter-box {
            background: #fff;
            border-radius: 12px;
            padding: 26px 24px 24px;
        }

        .enrolment-filter-toggle {
            color: #4264d7;
            display: inline-block;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .enrolment-filter-toggle:hover,
        .enrolment-filter-toggle:focus {
            color: #2f4eb5;
            text-decoration: none;
        }

        .enrolment-filter-grid {
            display: grid;
            gap: 14px 24px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .enrolment-filter-search {
            grid-column: span 2;
        }

        .enrolment-filter-field {
            margin: 0;
        }

        .enrolment-filter-field label {
            color: #526b8b;
            display: block;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .enrolment-filter-field .form-control {
            border: 1px solid #d5e0ec;
            border-radius: 4px;
            box-shadow: none;
            color: #526b8b;
            height: 38px;
        }

        .enrolment-filter-field .form-control::placeholder {
            color: #a6b8cd;
        }

        .enrolment-filter-field .form-control:focus {
            border-color: #7393dc;
            box-shadow: 0 0 0 2px rgba(74, 103, 207, .12);
        }

        .enrolment-filter-field .form-control:disabled {
            background: #e9edf2;
            color: #71839a;
        }

        .enrolment-filter-footer {
            border-top: 1px solid #d5dbe2;
            margin: 10px -12px 0;
            padding-top: 16px;
            text-align: center;
        }

        .enrolment-download {
            background: #198754;
            border: 0;
            border-radius: 3px;
            color: #fff;
            font-weight: 600;
            padding: 7px 12px;
        }

        .enrolment-download:hover,
        .enrolment-download:focus {
            background: #146c43;
            color: #fff;
        }

        .enrolment-card-heading {
            flex: 1;
            min-width: 0;
        }

        .enrolment-code {
            color: #55749b;
            font-size: 15px;
        }

        .enrolment-name {
            color: #101010;
            font-size: 15px;
            font-weight: 700;
            margin-top: 8px;
        }

        .enrolment-tag {
            border-radius: 3px;
            color: #fff;
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            margin-left: 5px;
            padding: 3px 6px;
        }

        .enrolment-tag-internal {
            background: #69737c;
        }

        .enrolment-tag-external {
            background: #df344b;
        }

        .enrolment-tag-form {
            background: #4a5fc1;
        }

        .enrolment-card-actions {
            display: flex;
            gap: 5px;
        }

        .enrolment-card-actions .btn {
            border-radius: 3px;
            color: #fff;
            height: 31px;
            padding: 5px 9px;
        }

        .enrolment-card-actions .btn-success {
            background: #198754;
        }

        .enrolment-card-actions .btn-primary {
            background: #4960bd;
        }

        .enrolment-card-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1.08fr;
            margin: 16px 0 18px;
        }

        .enrolment-detail {
            border-right: 1px solid #e2e5e8;
            min-height: 92px;
            padding: 0 24px 0 12px;
        }

        .enrolment-detail:first-child {
            padding-left: 12px;
        }

        .enrolment-detail:last-child {
            border-right: 0;
            padding-right: 0;
        }

        .enrolment-detail-title {
            color: #3919ae;
            font-size: 14px;
            margin: 0 0 5px;
        }

        .enrolment-detail-line {
            color: #101010;
            font-size: 15px;
            line-height: 23px;
        }

        .enrolment-invoice {
            color: #4663dd;
            font-weight: 700;
        }

        .enrolment-payment {
            background: #ffc107;
            border-radius: 3px;
            color: #111;
            font-weight: 700;
            padding: 3px 7px;
        }

        .enrolment-progress {
            background: #f1f4f8;
            border-left: 3px solid #7136d9;
            border-radius: 6px;
            padding: 9px 10px 4px;
        }

        .enrolment-progress-line {
            border-top: 1px solid #d5d9de;
            margin: 13px 26px -24px;
        }

        .enrolment-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .enrolment-step {
            color: #26364c;
            font-size: 10px;
            text-align: center;
            width: 25%;
        }

        .enrolment-step-icon {
            background: #c4cbd1;
            border-radius: 50%;
            color: #fff;
            display: block;
            font-size: 13px;
            height: 28px;
            line-height: 28px;
            margin: 0 auto 5px;
            position: relative;
            width: 28px;
        }

        .enrolment-step small {
            font-size: 9px;
            font-style: italic;
        }

        .enrolment-step small:empty {
            display: none;
        }

        .enrolment-pagination {
            margin-top: 8px;
        }

        @media (max-width: 767px) {
            .enrolment-filter-box {
                padding: 20px 14px;
            }

            .enrolment-filter-grid {
                grid-template-columns: 1fr;
            }

            .enrolment-card {
                padding: 14px 12px 16px;
            }

            .enrolment-card-header {
                align-items: flex-start;
            }

            .enrolment-card-actions {
                margin-left: auto;
            }

            .enrolment-card-details {
                grid-template-columns: 1fr;
            }

            .enrolment-detail,
            .enrolment-detail:first-child,
            .enrolment-detail:last-child {
                border-bottom: 1px solid #e2e5e8;
                border-right: 0;
                padding: 10px 0;
            }

            .enrolment-detail:last-child {
                border-bottom: 0;
            }

            .enrolment-code,
            .enrolment-name {
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel enrolment-filter-box">
            <a href="#enrolmentFilterCollapse" class="enrolment-filter-toggle" data-toggle="collapse" role="button"
                aria-expanded="true" aria-controls="enrolmentFilterCollapse">
                Insert Filter <i class="fa fa-caret-down"></i>
            </a>
            <div class="collapse show" id="enrolmentFilterCollapse">
                <div class="enrolment-filter-grid">
                    <div class="form-group enrolment-filter-field enrolment-filter-search">
                        <label for="enrolment-search">Search</label>
                        <input type="search" id="enrolment-search" class="form-control"
                            placeholder="code, child name, parent name, email, phone">
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-start-date">Start date</label>
                        <input type="date" id="enrolment-start-date" class="form-control">
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-end-date">End date</label>
                        <input type="date" id="enrolment-end-date" class="form-control" disabled>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-parent">Parent</label>
                        <select id="enrolment-parent" class="form-control">
                            <option value="all">All</option>
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-payment-source">Payment Source</label>
                        <select id="enrolment-payment-source" class="form-control">
                            <option value="all">All</option>
                            <option value="custom_form">Custom Payment</option>
                            <option value="web_form">Dashboard</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-place">Place</label>
                        <select id="enrolment-place" class="form-control" disabled>
                            <option value="all">All</option>
                            <option value="Exhibition">Exhibition</option>
                            <option value="School">School</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-status">Status</label>
                        <select id="enrolment-status" class="form-control">
                            <option value="all">All status</option>
                            <option value="PENDING">Pending</option>
                            <option value="PAID">Paid</option>
                            <option value="EXPIRED">Expired</option>
                            <option value="CANCEL">Cancel</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-branch">Branch</label>
                        <select id="enrolment-branch" class="form-control">
                            <option value="all">All Branches</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-level">Level</label>
                        <select id="enrolment-level" class="form-control" disabled>
                            <option value="all">All Levels</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-grade">Grade</label>
                        <select id="enrolment-grade" class="form-control" disabled>
                            <option value="all">All Grades</option>
                        </select>
                    </div>
                    <div class="form-group enrolment-filter-field">
                        <label for="enrolment-academic-year">Academic Year</label>
                        <select id="enrolment-academic-year" class="form-control">
                            <option value="all">All Academic Years</option>
                        </select>
                    </div>
                </div>
                <div class="enrolment-filter-footer">
                    <button type="button" class="enrolment-download" id="enrolment-download">
                        <i class="fa fa-download"></i> Download excel
                    </button>
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
                        <select id="enrolment-length" class="form-control employee-length-select"
                            style="height: 37px;width:80px">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <i class="employee-toolbar-summary" id="enrolment-summary">Loading enrolments...</i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-right employee-toolbar-actions-wrap">
                        <button type="button" class="btn btn-light btn-sm" id="enrolment-reset">
                            <i class="fa fa-refresh"></i> Reset filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div id="enrolment-list"></div>
        <div class="enrolment-pagination"></div>
        <div class="d-none">
            <table id="tbl-enrolment">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Parent</th>
                        <th>Email</th>
                        <th>Child</th>
                        <th>Phone</th>
                        <th>Invoice</th>
                        <th>Payment Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            const enrolmentTable = $('#tbl-enrolment').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                lengthChange: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ url('/admission/enrolment') }}",
                    type: 'GET',
                    data: function(data) {
                        data.search.value = $('#enrolment-search').val();
                        data.level = $('#enrolment-level').val();
                        data.branch = $('#enrolment-branch').val();
                        data.grade = $('#enrolment-grade').val();
                        data.status = $('#enrolment-status').val();
                        data.start_date = $('#enrolment-start-date').val();
                        data.end_date = $('#enrolment-end-date').val();
                        data.parent = $('#enrolment-parent').val();
                        data.payment_source = $('#enrolment-payment-source').val();
                        data.place = $('#enrolment-place').val();
                        data.academic_year = $('#enrolment-academic-year').val();
                    }
                },
                columns: [{
                    data: 'code',
                    defaultContent: '--',
                }, {
                    data: 'parent_name',
                    defaultContent: '--',
                }, {
                    data: 'email',
                    defaultContent: '--',
                }, {
                    data: 'child_name',
                    defaultContent: '--',
                }, {
                    data: 'phone_number',
                    defaultContent: '--',
                }, {
                    data: 'invoice_id',
                    defaultContent: '--',
                }, {
                    data: 'payment_status',
                    defaultContent: '--',
                }, {
                    data: 'created_at',
                    defaultContent: '--',
                }],
                drawCallback: function() {
                    const info = this.api().page.info();
                    const rows = this.api().rows({
                        page: 'current'
                    }).data().toArray();
                    $('#enrolment-list').html(rows.map(renderEnrolmentCard).join(''));
                    $('.enrolment-pagination').html($('.dataTables_paginate').detach());
                    $('#enrolment-summary').text(
                        `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal}`);
                }
            });

            function getLookupItems(response) {
                if (Array.isArray(response)) {
                    return response;
                }

                return response.data || response.items || response.results || [];
            }

            function loadLookup(url, selector, placeholder) {
                const select = $(selector);
                select.prop('disabled', true).html(`<option value="all">Loading...</option>`);

                return $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json'
                }).done(function(response) {
                    const options = [`<option value="all">${escapeHtml(placeholder)}</option>`];

                    getLookupItems(response).forEach(function(item) {
                        const value = item.id ?? item.value ?? item.code;
                        const label = item.name ?? item.title ?? item.label ?? item.academic_year ??
                            item.year ?? value;

                        if (value !== undefined && value !== null) {
                            options.push(
                                `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`
                            );
                        }
                    });

                    select.html(options.join('')).prop('disabled', false);
                }).fail(function() {
                    select.html(`<option value="all">${escapeHtml(placeholder)}</option>`).prop('disabled',
                        false);
                });
            }

            function resetDependentSelect(selector, placeholder) {
                $(selector).html(`<option value="all">${escapeHtml(placeholder)}</option>`).prop('disabled', true);
            }

            loadLookup("{{ route('admission.academic-year') }}", '#enrolment-academic-year', 'All Academic Years');
            loadLookup("{{ route('admission.branch') }}", '#enrolment-branch', 'All Branches');

            $('#enrolment-branch').on('change', function() {
                const branchId = $(this).val();
                resetDependentSelect('#enrolment-level', 'All Levels');
                resetDependentSelect('#enrolment-grade', 'All Grades');

                if (branchId !== 'all') {
                    loadLookup("{{ url('/admission/level/branch') }}/" + encodeURIComponent(branchId),
                        '#enrolment-level', 'All Levels');
                }
            });

            $('#enrolment-level').on('change', function() {
                const levelId = $(this).val();
                resetDependentSelect('#enrolment-grade', 'All Grades');

                if (levelId !== 'all') {
                    loadLookup("{{ url('/admission/grade/level') }}/" + encodeURIComponent(levelId),
                        '#enrolment-grade', 'All Grades');
                }
            });

            function escapeHtml(value) {
                return $('<div>').text(value == null || value === '' ? '--' : value).html();
            }

            function renderEnrolmentCard(enrolment) {
                const name = enrolment.child_name || enrolment.student_name || enrolment.name || '--';
                const parent = enrolment.parent_name || enrolment.parent || '--';
                const status = String(enrolment.payment_status || 'PENDING').toUpperCase();
                const type = String(enrolment.type || enrolment.admission_type || '').toLowerCase();
                const initials = name.split(' ').map(part => part.charAt(0)).slice(0, 2).join('').toUpperCase();

                return `<article class="enrolment-card">
                    <div class="enrolment-card-header">
                        <div class="enrolment-avatar">${escapeHtml(initials || '--')}</div>
                        <div class="enrolment-card-heading">
                            <div class="enrolment-code">Kode : ${escapeHtml(enrolment.code)}
                                <span class="enrolment-tag ${type === 'external' ? 'enrolment-tag-external' : 'enrolment-tag-internal'}">${escapeHtml(type || 'Internal')}</span>
                                <span class="enrolment-tag enrolment-tag-form">Custom Form</span>
                                <button type="button" class="btn btn-secondary btn-sm ml-1"><i class="fa fa-pencil"></i></button>
                            </div>
                            <div class="enrolment-name">${escapeHtml(name)}</div>
                        </div>
                        <div class="enrolment-card-actions">
                            <button type="button" class="btn btn-success" title="Restore"><i class="fa fa-history"></i></button>
                            <button type="button" class="btn btn-primary" title="View"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="enrolment-card-details">
                        <div class="enrolment-detail">
                            <h6 class="enrolment-detail-title">INFORMASI AKADEMIK</h6>
                            <div class="enrolment-detail-line">Tahun Ajaran: ${escapeHtml(enrolment.academic_year || enrolment.school_year || '--')}</div>
                            <div class="enrolment-detail-line">Cabang: ${escapeHtml(enrolment.branch || enrolment.branch_name || '--')}</div>
                            <div class="enrolment-detail-line">Level: ${escapeHtml(enrolment.level || enrolment.level_name || enrolment.grade || '--')}</div>
                        </div>
                        <div class="enrolment-detail">
                            <h6 class="enrolment-detail-title">ORANG TUA (${escapeHtml(enrolment.parent_relation || '')})</h6>
                            <div class="enrolment-detail-line">${escapeHtml(parent)}</div>
                            <div class="enrolment-detail-line">${escapeHtml(enrolment.email)}</div>
                            <div class="enrolment-detail-line">${escapeHtml(enrolment.phone_number || enrolment.phone)}</div>
                        </div>
                        <div class="enrolment-detail">
                            <h6 class="enrolment-detail-title enrolment-invoice">${escapeHtml(enrolment.invoice_id || '--')}</h6>
                            <div class="enrolment-detail-line">Tgl Enrol: ${escapeHtml(enrolment.created_at)}</div>
                            <div class="enrolment-detail-line">Status: <span class="enrolment-payment">${escapeHtml(status)}</span></div>
                            <div class="enrolment-detail-line">${escapeHtml(enrolment.payment_message || (status === 'PENDING' ? 'Belum melakukan pembayaran' : '--'))}</div>
                        </div>
                    </div>
                    <div class="enrolment-progress">
                        <div class="enrolment-progress-line"></div>
                        <div class="enrolment-steps">
                            <div class="enrolment-step"><span class="enrolment-step-icon"><i class="fa fa-graduation-cap"></i></span>Visit <small>${escapeHtml(enrolment.visit_status || 'Not Scheduled')}</small></div>
                            <div class="enrolment-step"><span class="enrolment-step-icon"><i class="fa fa-user"></i></span>Enrolment</div>
                            <div class="enrolment-step"><span class="enrolment-step-icon"><i class="fa fa-folder"></i></span>Documents</div>
                            <div class="enrolment-step"><span class="enrolment-step-icon"><i class="fa fa-check-square"></i></span>Agreement</div>
                        </div>
                    </div>
                </article>`;
            }

            let filterTimer;
            $('#enrolment-search').on('input', function() {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(() => enrolmentTable.ajax.reload(null, true), 400);
            });
            $('#enrolment-start-date, #enrolment-end-date, #enrolment-parent, #enrolment-payment-source, #enrolment-place, #enrolment-status, #enrolment-branch, #enrolment-level, #enrolment-grade, #enrolment-academic-year')
                .on('change', () => enrolmentTable.ajax.reload());
            $('#enrolment-length').on('change', function() {
                enrolmentTable.page.len(parseInt(this.value, 10)).draw();
            });
            $('#enrolment-reset').on('click', function() {
                $('#enrolment-search, #enrolment-start-date, #enrolment-end-date').val('');
                $('#enrolment-parent, #enrolment-payment-source, #enrolment-place, #enrolment-branch, #enrolment-level, #enrolment-grade, #enrolment-academic-year')
                    .val('all');
                $('#enrolment-status').val('all');
                resetDependentSelect('#enrolment-level', 'All Levels');
                resetDependentSelect('#enrolment-grade', 'All Grades');
                enrolmentTable.ajax.reload();
            });
            $('#enrolmentFilterCollapse').on('shown.bs.collapse', function() {
                $('#enrolmentFilterIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $('#enrolmentFilterLabel').text('Hide Filter');
            }).on('hidden.bs.collapse', function() {
                $('#enrolmentFilterIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('#enrolmentFilterLabel').text('Show Filter');
            });
        });
    </script>
@endsection

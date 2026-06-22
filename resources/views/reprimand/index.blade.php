@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/select2/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <button data-toggle="modal" data-target="#modal-data" type="button"
                            class="btn btn-success btn-sm text-white btn-add"><i class="fa fa-plus"></i> Create Reprimand</button>
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
                                    <th>Employee</th>
                                    <th>Reprimand Type</th>
                                    <th>Effective Date</th>
                                    <th>End Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reprimands as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->employee->personal->fullname ?? 'N/A' }}</td>
                                        <td>{{ $item->reprimandType->name ?? 'N/A' }} ({{ $item->reprimandType->deduction_score ?? 0 }})</td>
                                        <td>{{ \Carbon\Carbon::parse($item->effective_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</td>
                                        <td>
                                            <button data-id="{{ $item->id }}" 
                                                data-employee="{{ $item->employee_id }}" 
                                                data-type="{{ $item->reprimand_type_id }}" 
                                                data-effective="{{ $item->effective_date }}" 
                                                data-end="{{ $item->end_date }}" 
                                                data-notes="{{ $item->notes }}" 
                                                data-attachment="{{ $item->attachment_link }}"
                                                data-watchers="{{ json_encode($item->watchers->pluck('id')) }}"
                                                data-toggle="modal" data-target="#modal-data" type="button"
                                                class="btn btn-success btn-sm text-white btn-data">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <form action="{{ route('reprimand.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this reprimand?');">
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="form-data" autocomplete="OFF" method="POST">
                    @csrf
                    <div id="form-method"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-dataLabel">Create reprimand</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="employee_id">Assign to <span class="text-danger">*</span></label>
                            <select id="employee_id" class="form-control select2" name="employee_id" required style="width: 100%">
                                <option value="">Select employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->personal->fullname ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="reprimand_type_id">Reprimand type <span class="text-danger">*</span></label>
                            <select id="reprimand_type_id" class="form-control" name="reprimand_type_id" required>
                                <option value="">Select type</option>
                                @foreach($reprimandTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} (Deduction: {{ $type->deduction_score }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="effective_date">Effective date <span class="text-danger">*</span></label>
                                <input type="date" id="effective_date" class="form-control" name="effective_date" required/>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="end_date">End date <span class="text-danger">*</span></label>
                                <input type="date" id="end_date" class="form-control" name="end_date" required/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" class="form-control" name="notes" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="watchers">Watchers</label>
                            <select id="watchers" class="form-control select2" name="watchers[]" multiple="multiple" style="width: 100%">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->personal->fullname ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">You can select another employee or manager to receive updates about reprimands on email and push notifications.</small>
                        </div>

                        <div class="form-group">
                            <label for="attachment_link">Attachment</label>
                            <input type="url" id="attachment_link" class="form-control" name="attachment_link" placeholder="Enter file link URL (e.g., Google Drive link)" />
                            <small class="form-text text-muted">Please provide a valid URL link to the document.</small>
                        </div>

                        <div class="form-group">
                            <label for="document_template_id">Document template</label>
                            <select id="document_template_id" class="form-control" name="document_template_id">
                                <option value="">Optional</option>
                            </select>
                            <small class="form-text text-muted">You also now can attached from document template, setup your template.</small>
                        </div>
                        <input type="hidden" name="id" id="id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/select2/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $("#tbl-datatable").DataTable();
            $('.select2').select2({
                dropdownParent: $('#modal-data')
            });

            $("#tbl-datatable").on('click', '.btn-data', function() {
                let id = $(this).attr('data-id');
                let employee = $(this).attr('data-employee');
                let type = $(this).attr('data-type');
                let effective = $(this).attr('data-effective');
                let end = $(this).attr('data-end');
                let notes = $(this).attr('data-notes');
                let attachment = $(this).attr('data-attachment');
                let watchers = JSON.parse($(this).attr('data-watchers') || '[]');

                $('#id').val(id);
                $('#employee_id').val(employee).trigger('change');
                $('#reprimand_type_id').val(type);
                $('#effective_date').val(effective);
                $('#end_date').val(end);
                $('#notes').val(notes);
                $('#attachment_link').val(attachment);
                $('#watchers').val(watchers).trigger('change');

                $('#modal-dataLabel').text('Edit reprimand');
                $('#form-data button[type="submit"]').text('Update');

                $('#form-method').empty().append(`@method('put')`);
                $('#form-data').attr('action', `/employee/reprimand/${id}`); // Adjust base URL if needed based on prefix
            });

            $('.btn-add').on('click', function() {
                $('#id').val('');
                $('#employee_id').val('').trigger('change');
                $('#reprimand_type_id').val('');
                $('#effective_date').val('');
                $('#end_date').val('');
                $('#notes').val('');
                $('#attachment_link').val('');
                $('#watchers').val([]).trigger('change');

                $('#modal-dataLabel').text('Create reprimand');
                $('#form-data button[type="submit"]').text('Create');

                $('#form-method').empty();
                $('#form-data').attr('action', "/employee/reprimand");
            });
        })
    </script>
@endsection

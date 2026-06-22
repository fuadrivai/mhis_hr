@extends('layouts.info')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-employee')
<div class="row">
    <div class="col-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Key Performance Indicators (KPI)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <ul class="nav nav-tabs bar_tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="history-tab" data-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="true">KPI History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="assign-tab" data-toggle="tab" href="#assign" role="tab" aria-controls="assign" aria-selected="false">Assign New KPI</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="history" role="tabpanel" aria-labelledby="history-tab">
                        <table class="table table-striped table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Academic Year</th>
                                    <th>Reprimand Deduction (%)</th>
                                    <th>Links</th>
                                    <th>Final Score</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data->kpis as $kpi)
                                <tr>
                                    <td>{{ $kpi->academic_year }}</td>
                                    <td>{{ $kpi->reprimand_deduction_percentage }}</td>
                                    <td>
                                        @if($kpi->managerial_file_url)
                                            <a href="{{ $kpi->managerial_file_url }}" target="_blank" class="badge badge-primary"><i class="fa fa-external-link"></i> Managerial</a>
                                        @endif
                                        @if($kpi->tal_file_url)
                                            <a href="{{ $kpi->tal_file_url }}" target="_blank" class="badge badge-info"><i class="fa fa-external-link"></i> TAL</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($kpi->final_score !== null)
                                            <span class="badge badge-success" style="font-size: 14px;">{{ $kpi->final_score }}</span>
                                        @else
                                            <span class="badge badge-secondary">Not Calculated</span>
                                        @endif
                                    </td>
                                    <td>{{ $kpi->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('employee.kpi.calculate', $kpi->id) }}" class="btn btn-info btn-sm"><i class="fa fa-calculator"></i> Calculate</a>
                                        <a href="{{ route('employee.kpi.edit', $kpi->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                        <form action="{{ route('employee.kpi.destroy', $kpi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this KPI?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No KPI history found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="assign" role="tabpanel" aria-labelledby="assign-tab">
                        <form action="{{ route('employee.kpi.store', $data->id) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="form-group">
                                <label>Academic Year</label>
                                @if($activeYear ?? false)
                                    <input type="text" class="form-control" value="{{ $activeYear->name }}" readonly>
                                    <small class="form-text text-muted">The Academic Year is automatically assigned to the current active year configured in Settings.</small>
                                @else
                                    <input type="text" class="form-control text-danger" value="No Active Academic Year" readonly>
                                    <small class="form-text text-danger">Please configure an Active Academic Year in Settings > Academic Year before assigning KPIs.</small>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Use Template (Optional)</label>
                                <select id="template-selector" class="form-control">
                                    <option value="">-- Custom (No Template) --</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr>
                            <h4>Managerial Skills</h4>
                            <div id="managerial-container"></div>
                            <button type="button" class="btn btn-info btn-sm mt-2" onclick="addManagerialTarget()"><i class="fa fa-plus"></i> Add Managerial Target</button>

                            <hr>
                            <h4>To Achieved List (TAL)</h4>
                            <div id="tal-container"></div>
                            <button type="button" class="btn btn-info btn-sm mt-2" onclick="addTalTarget()"><i class="fa fa-plus"></i> Add TAL Target</button>

                            <hr>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Save & Dispatch to API</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content-employee-script')
<script>
    let mIndex = 0;
    let tIndex = 0;
    let globalSubIndex = Date.now();
    const templatesData = @json($templates);

    $('#template-selector').on('change', function() {
        const tplId = $(this).val();
        $('#managerial-container').empty();
        $('#tal-container').empty();
        mIndex = 0;
        tIndex = 0;

        if(!tplId) return;

        const tpl = templatesData.find(t => t.id == tplId);
        if(tpl) {
            tpl.targets.forEach(target => {
                if(target.type === 'managerial') {
                    const currentIndex = mIndex;
                    addManagerialTarget(target.name, target.target_score, target.weight);
                    if(target.sub_targets && target.sub_targets.length > 0) {
                        target.sub_targets.forEach(sub => {
                            addManagerialSubTarget(currentIndex, sub.name, sub.target_score, sub.weight);
                        });
                    }
                } else if (target.type === 'tal') {
                    addTalTarget(target.name, target.target_score, target.weight);
                }
            });
        }
    });

    function addManagerialTarget(name = '', targetScore = '', weight = '') {
        const html = `
            <div class="card bg-light p-3 mb-2" id="mTarget-${mIndex}">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" name="managerial_targets[${mIndex}][name]" class="form-control" placeholder="Target Name" value="${name}" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="managerial_targets[${mIndex}][target_score]" class="form-control" placeholder="Target Number" value="${targetScore ?? ''}" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="managerial_targets[${mIndex}][weight]" class="form-control" placeholder="Weight (%)" value="${weight ?? ''}">
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="button" class="btn btn-danger btn-sm" onclick="$('#mTarget-${mIndex}').remove()"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
                <div class="mt-2 ml-4">
                    <h6>Sub Targets:</h6>
                    <div id="mSubContainer-${mIndex}"></div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addManagerialSubTarget(${mIndex})"><i class="fa fa-plus"></i> Add Sub Target</button>
                    <button type="button" class="btn btn-info btn-sm" onclick="openBulkAddModal(${mIndex})"><i class="fa fa-paste"></i> Bulk Add Sub Targets</button>
                </div>
            </div>
        `;
        $('#managerial-container').append(html);
        mIndex++;
    }

    function addManagerialSubTarget(targetIndex, name = '', targetScore = '', weight = '') {
        const subIndex = globalSubIndex++;
        const html = `
            <div class="row mb-1" id="mSub-${subIndex}">
                <div class="col-md-5">
                    <input type="text" name="managerial_targets[${targetIndex}][sub_targets][${subIndex}][name]" class="form-control form-control-sm" placeholder="Sub Target Name" value="${name}" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="managerial_targets[${targetIndex}][sub_targets][${subIndex}][target_score]" class="form-control form-control-sm" placeholder="Target Number" value="${targetScore ?? ''}" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="managerial_targets[${targetIndex}][sub_targets][${subIndex}][weight]" class="form-control form-control-sm" placeholder="Weight (%)" value="${weight ?? ''}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="$('#mSub-${subIndex}').remove()"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
        $(`#mSubContainer-${targetIndex}`).append(html);
    }

    function addTalTarget(name = '', targetScore = '', weight = '') {
        const html = `
            <div class="row mb-2" id="tTarget-${tIndex}">
                <div class="col-md-5">
                    <input type="text" name="tal_targets[${tIndex}][name]" class="form-control" placeholder="TAL Target Name" value="${name}" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="tal_targets[${tIndex}][target_score]" class="form-control" placeholder="Target Number" value="${targetScore ?? ''}" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="tal_targets[${tIndex}][weight]" class="form-control" placeholder="Weight (%)" value="${weight ?? ''}" required>
                </div>
                <div class="col-md-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm" onclick="$('#tTarget-${tIndex}').remove()"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#tal-container').append(html);
        tIndex++;
    }

    function openBulkAddModal(mIndex) {
        $('#bulkAddTargetIndex').val(mIndex);
        $('#bulkAddTextarea').val('');
        $('#bulkAddTargetScore').val('');
        $('#bulkAddModal').modal('show');
    }

    function processBulkAdd() {
        const mIndex = $('#bulkAddTargetIndex').val();
        const text = $('#bulkAddTextarea').val();
        const targetScore = $('#bulkAddTargetScore').val();
        const lines = text.split(/\r?\n/);
        lines.forEach(line => {
            const name = line.trim();
            if (name) {
                addManagerialSubTarget(mIndex, name, targetScore);
            }
        });
        $('#bulkAddModal').modal('hide');
    }
</script>

<!-- Bulk Add Modal -->
<div class="modal fade" id="bulkAddModal" tabindex="-1" role="dialog" aria-labelledby="bulkAddModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bulkAddModalLabel">Bulk Add Sub Targets</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label>Target Score (Applies to all pasted items)</label>
            <input type="number" step="0.01" id="bulkAddTargetScore" class="form-control" placeholder="Target Score">
        </div>
        <div class="form-group">
            <label>Paste Sub Targets (one per line)</label>
            <textarea id="bulkAddTextarea" class="form-control" rows="10" placeholder="Sub Target 1&#10;Sub Target 2&#10;..."></textarea>
        </div>
        <input type="hidden" id="bulkAddTargetIndex">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="processBulkAdd()">Add Sub Targets</button>
      </div>
    </div>
  </div>
</div>
@endsection

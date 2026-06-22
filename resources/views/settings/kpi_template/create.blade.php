@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Add KPI Template</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="{{ route('kpi-template.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Template Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_public" value="1"> Make this template Public (Visible to other users)
                        </label>
                    </div>

                    <hr>
                    <h4>Managerial Skills</h4>
                    <div id="managerial-container">
                        <!-- Managerial Targets go here -->
                    </div>
                    <button type="button" class="btn btn-info btn-sm mt-2" onclick="addManagerialTarget()"><i class="fa fa-plus"></i> Add Managerial Target</button>

                    <hr>
                    <h4>To Achieved List (TAL)</h4>
                    <div id="tal-container">
                        <!-- TAL Targets go here -->
                    </div>
                    <button type="button" class="btn btn-info btn-sm mt-2" onclick="addTalTarget()"><i class="fa fa-plus"></i> Add TAL Target</button>

                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Template</button>
                    <a href="{{ route('kpi-template.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
<script>
    let mIndex = 0;
    let tIndex = 0;
    let globalSubIndex = Date.now();

    function addManagerialTarget() {
        const html = `
            <div class="card bg-light p-3 mb-2" id="mTarget-${mIndex}">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" name="managerial_targets[${mIndex}][name]" class="form-control" placeholder="Target Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="managerial_targets[${mIndex}][target_score]" class="form-control" placeholder="Target Number" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="managerial_targets[${mIndex}][weight]" class="form-control" placeholder="Weight (%)">
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
        const subIndex = globalSubIndex++; // unique ID
        const html = `
            <div class="row mb-1" id="mSub-${subIndex}">
                <div class="col-md-5">
                    <input type="text" name="managerial_targets[${targetIndex}][sub_targets][${subIndex}][name]" class="form-control form-control-sm" placeholder="Sub Target Name" value="${name}" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="managerial_targets[${targetIndex}][sub_targets][${subIndex}][target_score]" class="form-control form-control-sm" placeholder="Target Number" value="${targetScore}" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="managerial_targets[${targetIndex}][sub_targets][${subIndex}][weight]" class="form-control form-control-sm" placeholder="Weight (%)" value="${weight}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="$('#mSub-${subIndex}').remove()"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
        $(`#mSubContainer-${targetIndex}`).append(html);
    }

    function addTalTarget() {
        const html = `
            <div class="row mb-2" id="tTarget-${tIndex}">
                <div class="col-md-5">
                    <input type="text" name="tal_targets[${tIndex}][name]" class="form-control" placeholder="TAL Target Name" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="tal_targets[${tIndex}][target_score]" class="form-control" placeholder="Target Number" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="tal_targets[${tIndex}][weight]" class="form-control" placeholder="Weight (%)" required>
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

@extends('layouts.info')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-employee')
<div class="row">
    <div class="col-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Edit Key Performance Indicators (KPI)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="mb-3">
                    <a href="{{ route('employee.kpi.index', $data->id) }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to KPI History</a>
                </div>
                
                <form action="{{ route('employee.kpi.update', $kpi->id) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Academic Year</label>
                        <input type="text" class="form-control" value="{{ $kpi->academic_year }}" readonly>
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
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Update & Dispatch to API</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content-employee-script')
<script>
    let mIndex = 0;
    let tIndex = 0;
    const kpiData = @json($kpi);

    $(document).ready(function() {
        if(kpiData && kpiData.targets) {
            kpiData.targets.forEach(target => {
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
                </div>
            </div>
        `;
        $('#managerial-container').append(html);
        mIndex++;
    }

    function addManagerialSubTarget(targetIndex, name = '', targetScore = '', weight = '') {
        const subIndex = Date.now() + Math.floor(Math.random() * 1000);
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
</script>
@endsection

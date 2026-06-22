@extends('layouts.info')
@section('content-class')
@endsection
@section('content-employee')
<div class="row">
    <div class="col-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>KPI Calculation Breakdown <small>{{ $kpi->academic_year }}</small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <h4>Managerial Skills (60% Weight)</h4>
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Target Name</th>
                                    <th>Target</th>
                                    <th>Actual</th>
                                    <th>Weight (%)</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($managerialBreakdown as $m)
                                <tr>
                                    <td><strong>{{ $m['name'] }}</strong></td>
                                    <td>{{ $m['target'] }}</td>
                                    <td>{{ number_format($m['actual'], 2) }}</td>
                                    <td>{{ $m['weight'] }}%</td>
                                    <td><strong>{{ number_format($m['points'], 2) }}</strong></td>
                                </tr>
                                @if(isset($m['sub_targets']) && count($m['sub_targets']) > 0)
                                    @foreach($m['sub_targets'] as $sub)
                                    <tr class="table-sm" style="font-size: 0.85rem; background-color: #f8f9fa;">
                                        <td class="pl-4">↳ {{ $sub['name'] }}</td>
                                        <td>{{ $sub['target'] }}</td>
                                        <td>{{ number_format($sub['actual'], 2) }}</td>
                                        <td>{{ $sub['weight'] }}%</td>
                                        <td>{{ number_format($sub['points'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total Managerial Points</th>
                                    <th>{{ number_format($managerialScore, 2) }}</th>
                                </tr>
                                <tr class="table-info">
                                    <th colspan="4" class="text-right">Weighted Score (x 60%)</th>
                                    <th>{{ number_format($managerialWeighted, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h4>TAL Targets (40% Weight) <small class="text-muted">All-or-Nothing Rule</small></h4>
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Target Name</th>
                                    <th>Target</th>
                                    <th>Actual</th>
                                    <th>Weight (%)</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($talBreakdown as $t)
                                <tr>
                                    <td>{{ $t['name'] }}</td>
                                    <td>{{ $t['target'] }}</td>
                                    <td>{{ $t['actual'] }}</td>
                                    <td>{{ $t['weight'] }}%</td>
                                    <td>
                                        @if($t['points'] > 0)
                                            <span class="text-success">{{ number_format($t['points'], 2) }}</span>
                                        @else
                                            <span class="text-danger">0.00</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total TAL Points</th>
                                    <th>{{ number_format($talScore, 2) }}</th>
                                </tr>
                                <tr class="table-info">
                                    <th colspan="4" class="text-right">Weighted Score (x 40%)</th>
                                    <th>{{ number_format($talWeighted, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 offset-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="text-center mb-4">Final Score Calculation</h3>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th>Managerial Weighted:</th>
                                        <td class="text-right">{{ number_format($managerialWeighted, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>TAL Weighted:</th>
                                        <td class="text-right">+ {{ number_format($talWeighted, 2) }}</td>
                                    </tr>
                                    <tr class="text-danger border-bottom border-danger">
                                        <th>Reprimand Deduction:</th>
                                        <td class="text-right">- {{ number_format($deduction, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th><h3>Final Total Score:</h3></th>
                                        <td class="text-right"><h3>{{ number_format($finalScore, 2) }}</h3></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('employee.kpi.index', $data->id) }}" class="btn btn-secondary">Cancel</a>
                            <form action="{{ route('employee.kpi.save-score', $kpi->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="final_score" value="{{ $finalScore }}">
                                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Final Score to Profile</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
@section('content-employee-script')
@endsection

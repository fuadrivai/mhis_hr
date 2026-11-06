@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title"><h2>Visitor Reports</h2><div class="clearfix"></div></div>
            <div class="x_content">
                <form method="GET" class="form-inline" style="margin-bottom:15px">
                    <label>From <input type="date" name="from" class="form-control" value="{{ request('from') }}"></label>
                    <label>To <input type="date" name="to" class="form-control" value="{{ request('to') }}"></label>
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a class="btn btn-success" href="{{ route('visitor.reports.export', $queryParams) }}"><i class="fa fa-download"></i> Export CSV</a>
                </form>
                <div class="row">
                    <div class="col-md-4"><div class="x_panel"><h4>Total visits</h4><h2>{{ $totalCount }}</h2></div></div>
                    <div class="col-md-4"><div class="x_panel"><h4>Currently inside</h4><h2>{{ $insideCount }}</h2></div></div>
                    <div class="col-md-4"><div class="x_panel"><h4>Completed</h4><h2>{{ $completedCount }}</h2></div></div>
                </div>
                @include('visitor.partials.visit-table', ['visits' => $visits])
                {{ $visits->links() }}
            </div>
        </div>
    </div>
@endsection

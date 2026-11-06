@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title"><h2>Visitor History</h2><div class="clearfix"></div></div>
            <div class="x_content">
                <form method="GET" class="form-inline" style="margin-bottom:15px">
                    <label>From <input type="date" name="from" class="form-control" value="{{ request('from') }}"></label>
                    <label>To <input type="date" name="to" class="form-control" value="{{ request('to') }}"></label>
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a class="btn btn-default" href="{{ route('visitor.index') }}">All visits</a>
                </form>
                @include('visitor.partials.visit-table', ['visits' => $visits])
                {{ $visits->links() }}
            </div>
        </div>
    </div>
@endsection

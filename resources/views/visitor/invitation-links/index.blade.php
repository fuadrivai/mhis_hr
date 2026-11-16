@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Guest Registration Batches</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="btn btn-success btn-sm" href="{{ route('visitor.invitation-links.create') }}"><i class="fa fa-plus"></i> Create Links</a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content table-responsive">
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                <table class="table table-striped table-bordered">
                    <thead><tr><th>Batch</th><th>Visit date</th><th>Host / location</th><th>Purpose / category</th><th>Links</th><th>Expiry</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td>{{ $batch->name }}</td>
                                <td>{{ $batch->visit_date->format('d M Y') }}</td>
                                <td>{{ $batch->host->user?->name ?? '—' }}<br><small>{{ $batch->branch->name }}</small></td>
                                <td>{{ $batch->purpose->name }} / {{ $batch->category->name }}</td>
                                <td>{{ $batch->submitted_links_count }} submitted / {{ $batch->links_count }} total</td>
                                <td>{{ $batch->expires_at->format('d M Y H:i') }}</td>
                                <td><a class="btn btn-xs btn-primary" href="{{ route('visitor.invitation-links.show', $batch->id) }}">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No guest registration batches yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $batches->links() }}
            </div>
        </div>
    </div>
@endsection

@extends('layouts.main-layout')
@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Form Filter</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="search">Employee</label>
                            <input type="text" name="search" id="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Name or email">
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="branch">Branch</label>
                            <select name="branch" id="branch" class="form-control">
                                <option value="all">All branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(request('branch') == $branch->id)>{{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="organization">Organization</label>
                            <select name="organization" id="organization" class="form-control">
                                <option value="all">All organizations</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}" @selected(request('organization') == $organization->id)>
                                        {{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="level">Level</label>
                            <select name="level" id="level" class="form-control">
                                <option value="all">All levels</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" @selected(request('level') == $level->id)>{{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="position">Position</label>
                            <select name="position" id="position" class="form-control">
                                <option value="all">All positions</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected(request('position') == $position->id)>{{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 form-group">
                            <label for="order">Balance order</label>
                            <select name="order" id="order" class="form-control">
                                <option value="remaining_asc" @selected(request('order', 'remaining_asc') === 'remaining_asc')>Low to high</option>
                                <option value="remaining_desc" @selected(request('order') === 'remaining_desc')>High to low</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 form-group">
                            <label for="per_page">Rows per page</label>
                            <select name="per_page" id="per_page" class="form-control">
                                @foreach ([5, 10, 20, 50, 100] as $perPage)
                                    <option value="{{ $perPage }}" @selected((int) request('per_page', 10) === $perPage)>{{ $perPage }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-4 form-group" style="padding-top: 25px;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ url()->current() }}" class="btn btn-default">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-striped table-bordered table-sm" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Organization</th>
                                    <th>Level</th>
                                    <th>Position</th>
                                    <th>Remaining Balance</th>
                                    <th>#</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($leaveAllocations as $index => $leaveAllocation)
                                    <tr>
                                        <td class="text-right">{{ $leaveAllocations->firstItem() + $index }}</td>
                                        <td>{{ $leaveAllocation->employee->personal->fullname ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->organization->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->job_level->name ?? 'N/A' }}</td>
                                        <td>{{ $leaveAllocation->employee->employment->job_position->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $leaveAllocation->remaining }}</td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No leave allocations found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="text-right">
                            {{ $leaveAllocations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('settings.modal-general')
@endsection

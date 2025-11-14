@extends('layouts.main-layout')
@section('content-class')
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h5>Form Filter</h5>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="" method="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Insert Name or Email</label>
                                <input value="{{ $search }}" type="text" name="search" id="search"
                                    class="form-control" placeholder="Full name or email">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Organization</label>
                                <select name="organization" id="organization" class="select2 form-control">
                                    <option value="all">All</option>
                                    @foreach ($organizations as $org)
                                        @if ($query['organization'] == $org['id'])
                                            <option selected value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                        @else
                                            <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Position</label>
                                <select name="position" id="position" class="select2 form-control">
                                    <option value="all">All</option>
                                    @foreach ($positions as $org)
                                        @if ($query['position'] == $org['id'])
                                            <option selected value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                        @else
                                            <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Level</label>
                                <select name="level" id="level" class="select2 form-control">
                                    <option value="all">All</option>
                                    @foreach ($levels as $org)
                                        @if ($query['level'] == $org['id'])
                                            <option selected value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                        @else
                                            <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Status</label>
                                <select name="status" id="status" class="select2 form-control">
                                    <option {{ $query['status'] == 'all' ? 'selected' : '' }} value="all">All</option>
                                    <option {{ $query['status'] == 'permanent' ? 'selected' : '' }} value="permanent">
                                        Permanent
                                    </option>
                                    <option {{ $query['status'] == 'contract' ? 'selected' : '' }} value="contract">
                                        Contract
                                    </option>
                                    <option {{ $query['status'] == 'freelance' ? 'selected' : '' }} value="freelance">
                                        Freelance
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Branch</label>
                                <select name="branch" id="branch" class="select2 form-control">
                                    <option value="all">All</option>
                                    @foreach ($branches as $org)
                                        @if ($query['branch'] == $org->id)
                                            <option selected value="{{ $org->id }}">{{ $org->name }}</option>
                                        @else
                                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2" style="padding-top: 29px !important">
                            <button style="submit" class="btn btn-primary">Filter <i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="row">
                <div class="col-md-6">
                    <div class="row pl-2 pr-2 d-flex">
                        <span class="pt-2">Length : </span>
                        <select id="dummy" name="dummy" class="form-control perpage" style="height: 37px;width:80px">
                            <option {{ $query['perpage'] == 5 ? 'selected' : '' }} value="5">5</option>
                            <option {{ $query['perpage'] == 10 ? 'selected' : '' }} value="10">10</option>
                            <option {{ $query['perpage'] == 20 ? 'selected' : '' }} value="20">20</option>
                            <option {{ $query['perpage'] == 50 ? 'selected' : '' }} value="50">50</option>
                            <option {{ $query['perpage'] == 100 ? 'selected' : '' }} value="100">100</option>
                        </select>
                        <i class="pl-2 pt-2"><b>Show {{ $page['from'] }} to {{ $page['to'] }} of total
                                {{ $page['total'] }}</b></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <div class="btn-group" role="group">
                                <a data-toggle="modal" data-target="#importExcel" href="/employee/create"
                                    class="btn btn-info text-white btn-add-employee"><i class="fa fa-upload"></i> Import</a>
                                <a href="/employee/create" class="btn btn-success text-white btn-add-employee"><i
                                        class="fa fa-plus"></i> Add User</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (count($employees) == 0)
        <div class="card">
            <div class="row">
                <div class="col-12 text-center">
                    Tidak Ada Product
                </div>
            </div>
        </div>
    @endif

    @foreach ($employees as $emp)
        <div class="col-md-12 col-sm-12">
            <div class="x_panel">
                <div class="x_content">
                    <div class="row">
                        <div class="col col-xs-12">
                            @if (!isset($emp->personal->avatar) || $emp->personal->avatar == '')
                                <img width="80" src="{{ asset('images/user.png') }}"
                                    class="rounded float-left img-thumbnail" alt="{{ $emp->personal->fullname ?? '--' }}">
                            @else
                                <img width="80" src="{{ asset('storage/' . $pr->image) }}"
                                    class="rounded float-left img-thumbnail" alt="{{ $emp->personal->fullname ?? '--' }}">
                            @endif
                        </div>
                        <div class="col-3 col-xs-12 text-left">
                            <a href='/profile/personal/{{ $emp->id }}'>
                                <label class="m-0 p-0 font-weight-bold"
                                    for="">{{ Str::upper($emp->personal->fullname ?? '--') }}</label>
                            </a>

                            <p class="m-0 p-0 font-weight-bold" for=""><i class="fa fa-envelope text-info"></i>
                                {{ $emp->personal->email }}</p>
                            <p class="m-0 p-0 font-weight-bold" for=""><i class="fa fa-phone text-info"></i>
                                {{ $emp->personal->mobile_phone }}</p>
                            <p class="m-0 p-0">NIK : <label class="m-0 p-0 font-weight-bold"
                                    for="">{{ $emp->employment->employee_id ?? '' }}</label></p>
                            <p class="m-0 p-0">DOB : <label class="m-0 p-0 font-weight-bold" for="">
                                    {{ $emp->personal->birth_date == '' ? '--' : \Carbon\Carbon::parse($emp->personal->birth_date)->format('d F Y') }}
                                </label></p>
                        </div>
                        <div class="col-2 col-xs-12 text-center">
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0 p-0 font-weight-bold">Branch</p>
                                    <p class="m-0 p-0">
                                        <label for="">{{ $emp->employment->branch->name }}</label>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0 p-0 font-weight-bold">Organization</p>
                                    <p class="m-0 p-0">
                                        <label for="">{{ $emp->employment->organization_name }}</label>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0 p-0 font-weight-bold">Job Position</p>
                                    <p class="m-0 p-0">
                                        <label for="">{{ $emp->employment->job_position_name }}</label>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0 p-0 font-weight-bold">Job Level</p>
                                    <p class="m-0 p-0">
                                        <label for="">{{ $emp->employment->job_level_name }}</label>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0 p-0 font-weight-bold">Join Date</p>
                                    <p class="m-0 p-0">
                                        <label
                                            for="">{{ $emp->employment->join_date == '' ? '--' : \Carbon\Carbon::parse($emp->employment->join_date)->format('d F Y') }}</label>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0 p-0 font-weight-bold">End Date</p>
                                    <p class="m-0 p-0">
                                        <label
                                            for="">{{ $emp->employment->end_date != '' ? \Carbon\Carbon::parse($emp->employment->end_date)->format('d F Y') : '--' }}</label>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col text-center">
                            <p class="m-0 p-0 font-weight-bold">Status</p>
                            <p class="m-0 p-0">
                                @switch($emp->employment->employment_status)
                                    @case('permanent')
                                        <span class="badge badge-primary">{{ $emp->employment->employment_status }}</span>
                                    @break

                                    @case('contract')
                                        <span class="badge badge-danger">{{ $emp->employment->employment_status }}</span>
                                    @break

                                    @case('freelance')
                                        <span class="badge badge-warning">{{ $emp->employment->employment_status }}</span>
                                    @break

                                    @default
                                        <span
                                            class="badge badge-secondary">{{ $emp->employment->employment_status ?? '--' }}</span>
                                @endswitch
                            </p>
                        </div>
                        <div class="col text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href='/profile/personal/{{ $emp->id }}'>Info</a>
                                    <a class="dropdown-item" href="#">Transfer</a>
                                    <a class="dropdown-item" href="#">Resign</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
                <div class="row pl-2 pr-2 d-flex">
                    <span class="pt-1">Length : </span>
                    <select id="dummy" name="dummy" class="form-control perpage" style="height: 37px;width:80px">
                        <option {{ $query['perpage'] == 5 ? 'selected' : '' }} value="5">5</option>
                        <option {{ $query['perpage'] == 10 ? 'selected' : '' }} value="10">10</option>
                        <option {{ $query['perpage'] == 20 ? 'selected' : '' }} value="20">20</option>
                        <option {{ $query['perpage'] == 50 ? 'selected' : '' }} value="50">50</option>
                        <option {{ $query['perpage'] == 100 ? 'selected' : '' }} value="100">100</option>
                    </select>
                    <i class="pt-1"><b> Show {{ $page['from'] }} to {{ $page['to'] }} of total
                            {{ $page['total'] }}</b></i>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row pl-2 pr-2 d-flex justify-content-end">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="/employee/import" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                    </div>
                    <div class="modal-body">

                        {{ csrf_field() }}

                        <label>Choose File</label>
                        <div class="form-group">
                            <input type="file" name="file" required="required">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('content-script')
    <script>
        $(document).ready(function() {
            $('.perpage').on('change', function() {
                $('#perpage').val($(this).val())
                let query = getQueryString();
                query.perpage = $(this).val();
                navigate(query);
            })
        });
    </script>
@endsection

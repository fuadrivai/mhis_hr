@extends('layouts.info')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
@endsection

@section('content-employee')
    <div class="row">
        <div class="col-12">
            <div class="x_panel">
                <div class="x_content">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button onclick="showForm()" type="button" class="btn btn-info btn-sm btn-edit">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                            <form action="/profile/employment" autocomplete="off" method="POST">
                                @csrf
                                @method('PUT')
                                @if (isset($data['employment']['id']))
                                    <input class="d-none" name="id" id="id" type="text"
                                        value="{{ $data['employment']['id'] }}">
                                @endif
                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        <h4 style="color: black">Employment data</h4>
                                        <br>
                                        <small>Your data information related to company.</small>
                                    </div>
                                    <div class="col-md-8 col-12">
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-user text-info"></i> Company name</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p>{{ $data['employment']['company']['name'] ?? '--' }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-phone text-info"></i> Employee id</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p>{{ $data['employment']['employee_id'] ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-mobile-phone text-info"></i> Organization name</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">
                                                    {{ $data['employment']['organization']['name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="organization" class="form-control select2"
                                                        id="organization" style="width: 100%">
                                                        @foreach ($organizations as $item)
                                                            <option
                                                                {{ $data['employment']['organization']['id'] == $item['id'] ? 'selected' : '' }}
                                                                value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-briefcase text-info"></i> Job position</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">
                                                    {{ $data['employment']['job_position']['name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="position" class="form-control select2" id="position"
                                                        style="width: 100%">
                                                        @foreach ($positions as $item)
                                                            <option
                                                                {{ $data['employment']['job_position']['id'] == $item['id'] ? 'selected' : '' }}
                                                                value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-sitemap text-info"></i> Job level</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['employment']['job_level']['name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="level" class="form-control select2" id="level"
                                                        style="width: 100%">
                                                        @foreach ($levels as $item)
                                                            <option
                                                                {{ $data['employment']['job_level']['id'] == $item['id'] ? 'selected' : '' }}
                                                                value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-hourglass-half text-info"></i> Employment status</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['employment']['employment_status'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="employment-status" class="form-control select2"
                                                        id="employment-status" style="width: 100%">
                                                        <option
                                                            {{ $data['employment']['employment_status'] == 'permanent' ? 'selected' : '' }}
                                                            value="permanent">Permanent</option>
                                                        <option
                                                            {{ $data['employment']['employment_status'] == 'contract' ? 'selected' : '' }}
                                                            value="contract">Contract</option>
                                                        <option
                                                            {{ $data['employment']['employment_status'] == 'probation' ? 'selected' : '' }}
                                                            value="probation">Probation</option>
                                                        <option
                                                            {{ $data['employment']['employment_status'] == 'freelance' ? 'selected' : '' }}
                                                            value="freelance">Freelance</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-hourglass-half text-info"></i> Branch</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['employment']['branch']['name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="branch" class="form-control select2" id="branch"
                                                        style="width: 100%">
                                                        @foreach ($branches as $item)
                                                            <option
                                                                {{ $data['employment']['branch']['id'] == $item['id'] ? 'selected' : '' }}
                                                                value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-calendar text-info"></i> Join Date</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">
                                                    {{ $data['employment']->joinDate() ?? '-' }} - <span
                                                        class="badge badge-secondary">
                                                        {{ $data['employment']->age() ?? '-' }}</span>
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control date-picker"
                                                        name="join_date" id="join-date"
                                                        value="{{ $data['employment']->joinDate() }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-calendar text-info"></i> End employment status date</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">
                                                    {{ $data['employment']->endDate() ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control date-picker"
                                                        name="end_date" id="end-date"
                                                        value="{{ $data['employment']->endDate() }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-hourglass-half text-info"></i> Approval line</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">
                                                    {{ $data['employment']['approval_line_name'] ?? '-' }}
                                                </p>
                                                <div class="input-group flex-nowrap data-form d-none">

                                                    <input type="text" class="form-control" name="approval_line"
                                                        id="approval-line" placeholder="approval line"
                                                        aria-label="approval line" aria-describedby="addon-wrapping">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="button-addon2"><i class="fa fa-edit"> Change
                                                                data</i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row data-form d-none">
                                            <div class="col-12 text-right">
                                                <button type="button" onclick="closeForm()"
                                                    class="btn btn-secondary btn-sm"><i class="fa fa-times"></i> Cancel
                                                </button>
                                                <button type="submit" class="btn btn-success btn-sm"><i
                                                        class="fa fa-save"></i> Submit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('content-employee-script')
    <script src="/plugins/iCheck/icheck.min.js"></script>
    <script>
        $(document).ready(function() {
            tblMember = $("#tbl-member").DataTable();
        })

        function showForm() {
            $('.data-form').removeClass('d-none');
            $('.data-text').addClass('d-none');
        }

        function closeForm() {
            $('.data-form').addClass('d-none');
            $('.data-text').removeClass('d-none');
        }
    </script>
@endsection

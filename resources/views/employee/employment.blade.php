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
                            <form action="" autocomplete="off" method="POST">
                                @csrf
                                @if (isset($data['employment']['id']))
                                    <input class="d-none" name="id" id="id" type="text"
                                        value="{{ $data['employment']['id'] }}">
                                    @method('PUT')
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
                                                <p class="data-text">{{ $data['employment']['organization_name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="organization" class="form-control select2"
                                                        id="organization" style="width: 100%">
                                                        @foreach ($organizations as $item)
                                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-envelope text-info"></i> Job position</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['employment']['job_position_name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="position" class="form-control select2" id="position"
                                                        style="width: 100%">
                                                        @foreach ($positions as $item)
                                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-envelope text-info"></i> Job level</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['employment']['job_level_name'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="level" class="form-control select2" id="level"
                                                        style="width: 100%">
                                                        @foreach ($levels as $item)
                                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-envelope text-info"></i> Employment status</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['employment']['employment_status'] ?? '-' }}
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <select name="level" class="form-control select2" id="level"
                                                        style="width: 100%">
                                                        @foreach ($levels as $item)
                                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
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

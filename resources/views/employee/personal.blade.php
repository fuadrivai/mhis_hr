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
                    <ul class="nav nav-tabs bar_tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                aria-controls="home" aria-selected="true"><i class="fa fa-user text-primary"></i>
                                Basic Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                aria-controls="profile" aria-selected="false"><i class="fa fa-users text-success"></i>
                                Family
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                                aria-controls="contact" aria-selected="false"><i class="fa fa-phone text-warning"></i>
                                Emergency Contact
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button onclick="showForm()" type="button" class="btn btn-info btn-sm btn-edit">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                            <form action="/profile/personal" autocomplete="off" method="POST">
                                @csrf
                                @if (isset($data['personal']['id']))
                                    <input class="d-none" name="id" id="id" type="text"
                                        value="{{ $data['personal']['id'] }}">
                                    @method('PUT')
                                @endif
                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        <h4 style="color: black">Personal Data</h4>
                                        <br>
                                        <small>Your email address is your identity on MHIS Hub is used to log in.</small>
                                    </div>
                                    <div class="col-md-8 col-12">
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-user text-info"></i> Full Name</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->fullname }}</p>
                                                <div class="row data-form d-none">
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <input type="text" placeholder="First Name"
                                                                class="form-control" name="first_name" id="first-name"
                                                                required value="{{ $data['personal']->first_name }}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <input type="text" placeholder="Last Name"
                                                                class="form-control" name="last_name" id="last-name"
                                                                value="{{ $data['personal']->last_name }}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-phone text-info"></i> Phone</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->phone ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control" name="phone" id="phone"
                                                        value="{{ $data['personal']->phone }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-mobile-phone text-info"></i> Mobile Phone</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']['mobile_phone'] ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input required type="text" class="form-control"
                                                        name="mobile_phone" id="mobile-phone"
                                                        value="{{ $data['personal']->mobile_phone }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-envelope text-info"></i> Email</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']['email'] ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input required type="text" class="form-control" name="email"
                                                        id="email" value="{{ $data['personal']->email }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-globe text-info"></i> Place of Birth</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']['birth_place'] ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control" name="birth_place"
                                                        id="birth-place" required
                                                        value="{{ $data['personal']->birth_place }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-calendar text-info"></i> Birthdate</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">
                                                    {{ $data['personal']->birthDate() ?? '-' }} - <span
                                                        class="badge badge-secondary">
                                                        {{ $data['personal']->age() ?? '-' }}</span>
                                                </p>
                                                <div class="form-group data-form d-none">
                                                    <input required type="text" class="form-control date-picker"
                                                        name="birth_date" id="birth-date"
                                                        value="{{ $data['personal']->birthDate() }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-{{ $data['personal']->gender() }} text-info"></i>
                                                    gender</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ ucfirst($data['personal']->gender()) }}</p>
                                                <div class="form-group data-form d-none">
                                                    <p>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gendre"
                                                            id="gender1" value="male"
                                                            {{ $data['personal']->gender() == 'male' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="gender1">
                                                            Male
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gendre"
                                                            id="gender2" value="female"
                                                            {{ $data['personal']->gender() == 'female' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="gender2">
                                                            Female
                                                        </label>
                                                    </div>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-pagelines text-info"></i> Marital Status</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->maritalStatus() }}</p>
                                                <div class="form-group data-form d-none">
                                                    <select required name="marital_status" class="form-control select2"
                                                        id="marital-status" style="width: 100%">
                                                        <option
                                                            {{ $data['personal']->marital_status == '1' ? 'selected' : '' }}
                                                            value="1">Single</option>
                                                        <option
                                                            {{ $data['personal']->marital_status == '2' ? 'selected' : '' }}
                                                            value="2">Merried</option>
                                                        <option
                                                            {{ $data['personal']->marital_status == '3' ? 'selected' : '' }}
                                                            value="3">Widow</option>
                                                        <option
                                                            {{ $data['personal']->marital_status == '4' ? 'selected' : '' }}
                                                            value="4">Widower</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-calendar text-info"></i> Blood Type</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->blood_type ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <select name="blood_type" class="form-control select2"
                                                        id="blood-type" style="width: 100%">
                                                        <option value="">-- Select blood type --</option>
                                                        <option
                                                            {{ $data['personal']->blood_type == 'A' ? 'selected' : '' }}
                                                            value="A">A</option>
                                                        <option
                                                            {{ $data['personal']->blood_type == 'B' ? 'selected' : '' }}
                                                            value="B">B</option>
                                                        <option
                                                            {{ $data['personal']->blood_type == 'AB' ? 'selected' : '' }}
                                                            value="AB">AB</option>
                                                        <option
                                                            {{ $data['personal']->blood_type == 'O' ? 'selected' : '' }}
                                                            value="O">O</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-moon-o text-info"></i> Religion</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->religion->name ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <select required name="religion_id" class="form-control select2"
                                                        id="religion_id" style="width: 100%">
                                                        @foreach ($religions as $item)
                                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        <h4 style="color: black">Identity & Address</h4>
                                    </div>
                                    <div class="col-md-8 col-12">
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-credit-card text-info"></i> NIK (NPWP 16 digit)</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->identity_number ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control" name="identity_number"
                                                        id="identity-number"
                                                        value="{{ $data['personal']->identity_number }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-book text-info"></i> Passport number</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->passport_number ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control" name="passport_number"
                                                        id="passport-number"
                                                        value="{{ $data['personal']->passport_number }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-calendar text-info"></i> Passport expiration date</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->expiredIdentity() }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control date-picker"
                                                        name="expired_date_identity_id" id="expired-date-identity-id"
                                                        value="{{ $data['personal']->expiredIdentity() }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-file-code-o text-info"></i> Postal code</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->postal_code ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <input type="text" class="form-control" name="postal_code"
                                                        id="postal-code" value="{{ $data['personal']->postal_code }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-map-marker text-info"></i> Citizen ID address</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->address ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <textarea name="address" id="address" class="form-control" rows="3">{{ $data['personal']->address }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-12">
                                                <p><i class="fa fa-map-marker text-info"></i> Residential address</p>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <p class="data-text">{{ $data['personal']->current_address ?? '-' }}</p>
                                                <div class="form-group data-form d-none">
                                                    <textarea name="current_address" id="current-address" class="form-control" rows="3">{{ $data['personal']->current_address }}</textarea>
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
                        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button data-toggle="modal" data-target="#modal-family"
                                        class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add Family</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive card-box">
                                        <table id="tbl-member" class="table table-striped table-bordered table-sm"
                                            style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Name</th>
                                                    <th>Relationship</th>
                                                    <th>Birth Date</th>
                                                    <th>Id Number</th>
                                                    <th>Phone</th>
                                                    <th>Gender</th>
                                                    <th>Religion</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @for ($i = 0; $i < count($data['personal']['families'] ?? []); $i++)
                                                    <?php $family = ($data['personal']['families'] ?? [])[$i]; ?>
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $family->fullname ?? '-' }}</td>
                                                        <td>{{ $family->relationship->name }}</td>
                                                        <td>{{ $family->birthDate() }}</td>
                                                        <td>{{ $family->id_number }}</td>
                                                        <td>{{ $family->mobile_phone }}</td>
                                                        <td>{{ $family->strGendre() }}</td>
                                                        <td>{{ $family->religion->name }}</td>
                                                        <td><button data-id="{{ $family->id }}"
                                                                class="btn btn-sm btn-danger" id="btn-delete"><i
                                                                    class="fa fa-trash"></i></button></td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button data-toggle="modal" data-target="#modal-econ"
                                        class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add Emergency
                                        Contact</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive card-box">
                                        <table id="tbl-econ" class="table table-striped table-bordered table-sm"
                                            style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Relationship</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @for ($i = 0; $i < count($data['personal']['emergencies'] ?? []); $i++)
                                                    <?php $econ = ($data['personal']['emergencies'] ?? [])[$i]; ?>
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $econ->name ?? '-' }}</td>
                                                        <td>{{ $econ->mobile_phone }}</td>
                                                        <td>{{ $econ->relationship->name }}</td>
                                                        <td><button data-id="{{ $econ->id }}"
                                                                class="btn btn-sm btn-danger" id="btn-delete-econ"><i
                                                                    class="fa fa-trash"></i></button></td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-family" tabindex="-1" role="dialog" aria-labelledby="documentUploadLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="documentUploadLabel">
                        <i class="fa fa-users mr-2"></i>Family Form
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="family-form">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="family-id">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="fullname">Full name*</label>
                                    <input required type="text" class="form-control" id="fullname"
                                        placeholder="Enter name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="relation">Relationship*</label>
                                    <select required id="relation" class="form-control select2" style="width: 100%">
                                        <option value="" disabled selected>--choose relation--</option>
                                        @foreach ($relations as $relation)
                                            <option value="{{ $relation->id }}">{{ $relation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile_number">Phone*</label>
                                    <input required type="text" class="form-control"
                                        id="mobile_number"placeholder="Phone number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_number">ID number</label>
                                    <input type="text" class="form-control" id="id_number"placeholder="Id number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="relation">Gender*</label>
                                    <select required id="gendre" class="form-control select2" style="width: 100%">
                                        <option value="1">Male</option>
                                        <option value="2">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="relation">Religion</label>
                                    <select id="relation" class="form-control select2" style="width: 100%">
                                        <option value="" disabled selected>--choose religion--</option>
                                        @foreach ($religions as $religion)
                                            <option value="{{ $religion->id }}">{{ $religion->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="family-birthdate">Birthdate*</label>
                                    <input type="text" required class="form-control date-picker"
                                        id="family-birthdate"placeholder="Id number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="marital">Marital status</label>
                                    <select id="marital" class="form-control select2" style="width: 100%">
                                        <option value="">--select status--</option>
                                        <option value="1">Single</option>
                                        <option value="2">Merried</option>
                                        <option value="3">Widow</option>
                                        <option value="4">Widower</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="family-jon">Job</label>
                                    <input type="text" class="form-control" id="family-job" name="job"
                                        placeholder="Job">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="family-address">Address</label>
                                    <input type="text" class="form-control" id="family-address" name="family-address"
                                        placeholder="Address">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="checkbox">
                                    <p>
                                        <input id="add-emergency" name="add-emergency" type="checkbox" value="">
                                        Add to emergency contact
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitDocumentBtn">
                            <i class="fa fa-save mr-2"></i>Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-econ" tabindex="-1" role="dialog" aria-labelledby="econ-model"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="econ-model">
                        <i class="fa fa-users mr-2"></i>Emergency Contact Form
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="econ-form">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="econ-id">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="econ-fullname">Full name*</label>
                                    <input required type="text" class="form-control" id="econ-fullname"
                                        placeholder="Enter name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="econ-relation">econ-Relationship*</label>
                                    <select required id="econ-relation" class="form-control select2" style="width: 100%">
                                        <option value="" disabled selected>--choose relation--</option>
                                        @foreach ($relations as $relation)
                                            <option value="{{ $relation->id }}">{{ $relation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="econ-mobile_number">Phone*</label>
                                    <input required type="text" class="form-control"
                                        id="econ-mobile_number"placeholder="Phone number">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save mr-2"></i>Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('content-employee-script')
    <script src="/plugins/iCheck/icheck.min.js"></script>
    <script>
        $(document).ready(function() {
            tblMember = $("#tbl-member, #tbl-econ").DataTable({
                searching: false,
                paging: false,
                info: false,
                lengthChange: false,
                ordering: false
            });

            $('#family-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const payload = {
                    fullname: $('#fullname').val(),
                    personal_id: $('#id').val(),
                    relation_ship_id: $('#relation').val(),
                    religion_id: $('#relation').val(),
                    mobile_number: $('#mobile_number').val(),
                    address: $('#family-address').val(),
                    id_number: $('#id_number').val(),
                    gendre: $('#gendre').val(),
                    marital_status: $('#marital').val(),
                    birth_date: moment($('#family-birthdate').val(), "DD MMMM YYYY").format(
                        "YYYY-MM-DD"),
                    job: $('#family-job').val(),
                };
                $('#modal-family').modal('hide')
                blockUI();

                ajax(payload, '/profile/family', "POST", function(json) {
                    sweetAlert("Success", "Data successfully recorded", "success");
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            })

            $('#btn-delete').on('click', function() {
                if (!confirm("Are you sure you want to delete this data?")) {
                    e.preventDefault();
                }
                let familyId = $(this).data('id');
                blockUI();
                ajax(null, `/profile/family/${familyId}`, "DELETE", function(json) {
                    sweetAlert("Success", "Data removed successfully", "success");
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            })
            $('#btn-delete-econ').on('click', function() {
                if (!confirm("Are you sure you want to delete this data?")) {
                    e.preventDefault();
                }
                let econId = $(this).data('id');
                blockUI();
                ajax(null, `/profile/emergency/${econId}`, "DELETE", function(json) {
                    sweetAlert("Success", "Data removed successfully", "success");
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            })

            $('#econ-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const payload = {
                    fullname: $('#econ-fullname').val(),
                    personal_id: $('#id').val(),
                    relation_ship_id: $('#econ-relation').val(),
                    mobile_number: $('#econ-mobile_number').val(),
                };
                $('#modal-econ').modal('hide')
                blockUI();

                ajax(payload, '/profile/emergency', "POST", function(json) {
                    sweetAlert("Success", "Data successfully recorded", "success");
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            })
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

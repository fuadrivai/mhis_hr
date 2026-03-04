@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/jquery-smartwizard-master/dist/css/smart_wizard_all.css" rel="stylesheet">
    <link href="/plugins/iCheck/skins/flat/green.css" rel="stylesheet">
    <style>
        #fileInfo {
            animation: slideIn 0.3s ease;
        }

        #filePreview img {
            max-height: 150px;
            display: block;
            margin-bottom: 0.5rem;
        }

        #filePreview embed {
            width: 100%;
            height: 200px;
        }

        .pdf-preview {
            width: 100%;
            height: 200px;
            /* tinggi konsisten */
            overflow: hidden;
            border-radius: 8px;
        }

        .pdf-preview embed {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
@endsection

@section('content-child')
    <div class="x_panel">
        <div class="x_title">
            <h2>Employee <small>Form</small></h2>
            {{-- <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
            </ul> --}}
            <div class="clearfix"></div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="x_content">
            <div id="smartwizard" dir="rtl-">
                <ul class="nav nav-progress">
                    <li class="nav-item">
                        <a class="nav-link" href="#step-1">
                            <div class="num">1</div>
                            Personal Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#step-2">
                            <span class="num">2</span>
                            Employment Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#step-3">
                            <span class="num">3</span>
                            Payroll Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#step-4">
                            <span class="num">4</span>
                            Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="#step-5">
                            <span class="num">5</span>
                            Confirm Employee
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
                        <form id="form-1" class="row row-cols-1 ms-5 me-5 needs-validation" autocomplete="off"
                            novalidate>
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-sm-12">
                                    <div class="row pb-3">
                                        <div class="col-md-12">
                                            <h3>Personal data</h3>
                                            <small>Fill all employee personal basic information data</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">First name*</label>
                                                <input id="first-name" name="first-name" required type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Last name</label>
                                                <input id="last-name" name="last-name" type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Email*</label>
                                                <input id="email" name="email" required type="email"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Phone</label>
                                                <input id="phone" name="phone" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Mobile Phone*</label>
                                                <input id="mobile-phone" name="mobile-phone" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Place of birth</label>
                                                <input id="birth-place" name="birth-place" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group has-feedback">
                                                <label for="">Date of birth</label>
                                                <input type="text" class="form-control has-feedback-left date-picker"
                                                    id="birth-date">
                                                <span style="top: 25px" class="fa fa-calendar form-control-feedback left"
                                                    aria-hidden="true"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Gender</label>
                                                <p>
                                                    <label for="genderM">Male : </label>
                                                    <input type="radio" class="flat" name="gender" id="genderM"
                                                        value="1" checked="" required /> <label
                                                        for="genderF">Female :</label>
                                                    <input type="radio" class="flat" name="gender" id="genderF"
                                                        value="2" required />
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Marital status</label>
                                                <select name="marital-status" class="form-control select2"
                                                    id="marital-status" style="width: 100%">
                                                    <option value="1">Single</option>
                                                    <option value="2">Merried</option>
                                                    <option value="3">Widow</option>
                                                    <option value="4">Widower</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Blood type</label>
                                                <select name="blood-type" class="form-control select2" id="blood-type"
                                                    style="width: 100%">
                                                    <option value="">-- Select blood type --</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="AB">AB</option>
                                                    <option value="O">O</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Religion</label>
                                                <select name="religion" class="form-control select2" id="religion"
                                                    style="width: 100%">
                                                    @foreach ($religions as $item)
                                                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row pb-3">
                                        <div class="col-md-12">
                                            <h3>Identity & address</h3>
                                            <small>Employee identity address information</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Identity Type</label>
                                                <select name="identity-type" class="form-control select2"
                                                    id="identity-type" style="width: 100%">
                                                    <option value="ktp">KTP</option>
                                                    <option value="sim">SIM</option>
                                                    <option value="passport">Passport</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">NIK (NPWP 16 digit)*</label>
                                                <input id="nik" name="nik" required type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Passport number</label>
                                                <input id="passport-number" name="passport-number" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group has-feedback">
                                                <label for="">Passport expiry date</label>
                                                <input type="text" class="form-control date-picker has-feedback-left"
                                                    id="passport-expired-date" name="passport-expired-date">
                                                <span style="top: 25px" class="fa fa-calendar form-control-feedback left"
                                                    aria-hidden="true"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Postal code</label>
                                                <input id="postal-code" name="postal-code" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Address</label>
                                                <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="check-redentian" name="check-redentian"
                                                            type="checkbox" value=""> Use as residential address
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Residential address</label>
                                                <textarea class="form-control" name="current-address" id="current-address" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                        <form id="form-2" class="row row-cols-1 ms-5 me-5 needs-validation" autocomplete="off"
                            novalidate>
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-sm-12">
                                    <div class="row pb-3">
                                        <div class="col-md-12">
                                            <h3>Employment data</h3>
                                            <small>Fill all employee data information related to company</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Employee ID*</label>
                                                <input id="employee-id" name="employee-id" required type="text"
                                                    class="form-control" placeholder="Employee ID">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Barcode</label>
                                                <input id="barcode" name="barcode" type="text" class="form-control"
                                                    placeholder="Barcode">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Branch</label>
                                                <select name="branch" id="branch" required
                                                    class="form-control select2" style="width: 100%">
                                                    @foreach ($branches as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Organization</label>
                                                <select name="organization" id="organization" required
                                                    class="form-control select2" style="width: 100%">
                                                    @foreach ($organizations as $item)
                                                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Job position</label>
                                                <select name="position" id="position" required
                                                    class="form-control select2" style="width: 100%">
                                                    @foreach ($positions as $item)
                                                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Job level</label>
                                                <select name="level" id="level" required
                                                    class="form-control select2" style="width: 100%">
                                                    @foreach ($levels as $item)
                                                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Employment status</label>
                                                <select name="employee-status" id="employee-status" required
                                                    class="form-control select2" style="width: 100%">
                                                    <option value="permanent">Permanent</option>
                                                    <option value="contract">Contract</option>
                                                    <option value="freelance">Freelance</option>
                                                    <option value="probation">Probation</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Schedule</label>
                                                <select name="schedule" id="schedule" class="form-control select2"
                                                    style="width: 100%">
                                                    @foreach ($schedules as $item)
                                                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Approval Line</label>
                                                <select name="approval" id="approval" class="form-control select2"
                                                    style="width: 100%">
                                                    @foreach ($employees as $item)
                                                        <option value="{{ $item['personal']['id'] }}">
                                                            {{ $item['personal']['fullname'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group has-feedback">
                                                <label for="">Join Date*</label>
                                                <input type="text" required
                                                    class="form-control date-picker has-feedback-left" id="join-date"
                                                    placeholder="Join Date">
                                                <span style="top: 25px" class="fa fa-calendar form-control-feedback left"
                                                    aria-hidden="true"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group has-feedback">
                                                <label for="">End status date</label>
                                                <input type="text" class="form-control date-picker has-feedback-left"
                                                    id="end-date" placeholder="End status date">
                                                <span style="top: 25px" class="fa fa-calendar form-control-feedback left"
                                                    aria-hidden="true"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group has-feedback">
                                                <label for="">Sign date</label>
                                                <input type="text" class="form-control date-picker has-feedback-left"
                                                    id="sign-date">
                                                <span style="top: 25px" class="fa fa-calendar form-control-feedback left"
                                                    aria-hidden="true"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                        <form id="form-3" class="row row-cols-1 ms-5 me-5 needs-validation" autocomplete="off"
                            novalidate>
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-sm-12">
                                    <div class="row pb-3">
                                        <div class="col-md-12">
                                            <h3>Bank account</h3>
                                            <small>The employee's bank account is used for payroll</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Bank Name</label>
                                                <select name="bank" id="bank" class="form-control select2"
                                                    style="width: 100%">
                                                    @foreach ($banks as $item)
                                                        <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Account number</label>
                                                <input id="account-number" name="account-number" type="text"
                                                    class="form-control" placeholder="Account Number">
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Account holder name</label>
                                                <input id="account-holder" name="account-holder" type="text"
                                                    class="form-control" placeholder="Account holder name">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">NPWP 15 digit (old)</label>
                                                <input type="text" class="form-control" id="npwp"
                                                    name="npwp">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">PTKP Status</label>
                                                <select name="ptkp-status" id="ptkp-status" class="form-control select2"
                                                    style="width: 100%">
                                                    <option value="1">TK/0</option>
                                                    <option value="2">TK/1</option>
                                                    <option value="3">TK/2</option>
                                                    <option value="4">TK/3</option>
                                                    <option value="5">K/0</option>
                                                    <option value="6">K/1</option>
                                                    <option value="7">K/2</option>
                                                    <option value="8">K/3</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                                <label for="">Employment tax status</label>
                                                <select name="employment-tax-status" id="employment-tax-status"
                                                    class="form-control select2" style="width: 100%">
                                                    <option value="0">Pegawai Tetap</option>
                                                    <option value="1">Pegawai Tidak Tetap</option>
                                                    <option value="2">Bukan Pegawai yang Bersifat Berkesinambunga
                                                    </option>
                                                    <option value="3">Bukan Pegawai yang tidak Bersifat
                                                        Berkesinambungan</option>
                                                    <option value="4">Ekspatriat</option>
                                                    <option value="5">Ekspatriat Dalam Negeri</option>
                                                    <option value="6">Tenaga Ahli yang Bersifat Berkesinambungan
                                                    </option>
                                                    <option value="7">Tenaga Ahli yang Tidak Bersifat Berkesinambungan
                                                    </option>
                                                    <option value="8">Dewan Komisaris</option>
                                                    <option value="9">Tenaga Ahli yang Bersifat Berkesinambungan >1 PK
                                                    </option>
                                                    <option value="10">Tenaga Kerja Lepas</option>
                                                    <option value="11">Bukan Pegawai yang Bersifat Berkesinambungan >1
                                                        PK</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">BPJS ketenagakerjaan number</label>
                                                <input type="text" class="form-control" name="bpjs-ketenagakerjaan"
                                                    id="bpjs-ketenagakerjaan">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="">BPJS Kesehatan number</label>
                                                <input type="text" class="form-control" name="bpjs-kesehatan"
                                                    id="bpjs-kesehatan">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                        <form id="form-4" class="ms-5 me-5 needs-validation" autocomplete="off" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>Upload Documents</h3>
                                    <small>The employee's documents are required for verification and payroll
                                        processing</small>
                                </div>
                                <div class="col-md-6 justify-content-end d-flex">
                                    <button data-toggle="modal" data-target="#documentUploadModal" type="button"
                                        class="btn btn-primary" id="addDocBtn">
                                        <i class="fa fa-download mr-1"></i>Add Document
                                    </button>
                                </div>
                            </div>
                            <hr>
                            <div class="row" id="document-list">
                                <div class="col-md-12">
                                    <div class="list-group">
                                        <p class="text-muted text-center">No documents uploaded yet</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="step-5" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                        <form id="form-5" class="row row-cols-1 ms-5 me-5 needs-validation justify-content-center"
                            autocomplete="off" novalidate>
                            <div class="col-md-8 col-sm-12">
                                <div class="row pt-5 justify-content-center">
                                    <div class="col-12 text-center">
                                        <i class="fa fa-envelope text-primary"
                                            style="font-size: clamp(5rem,15vw,15vw)"></i>
                                    </div>
                                    <div class="col-12 text-center">
                                        <p class="font-weight-bold">Invite employee to MHIS HUB</p>
                                        <p>Before submitting, you need to invite the employee to be able to access the
                                            MHIS Hub account.</p>
                                    </div>
                                    <div class="col-8 text-center">
                                        <div class="alert alert-dark" role="alert">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <p>
                                                        <input id="create-account" checked name="create-account"
                                                            type="checkbox" value=""> Invite to access MHIS Hub
                                                    </p>
                                                </div>
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


    <!-- Modal for Document Upload -->
    <div class="modal fade" id="documentUploadModal" tabindex="-1" role="dialog"
        aria-labelledby="documentUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="documentUploadLabel">
                        <i class="fa fa-upload mr-2"></i>Upload Document
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="documentForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="docCategorySelect">Document Category</label>
                                    <select id="docCategorySelect" class="form-control select2" style="width: 100%">
                                        @foreach ($categories as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                        @endforeach
                                        <option value="custom">Custom</option>
                                    </select>
                                    <input type="text" id="docCategoryCustom" class="form-control mt-2"
                                        placeholder="Enter custom document name" style="display: none;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="documentNumber">Document Number</label>
                                    <input type="text" class="form-control" id="documentNumber"
                                        placeholder="Enter document number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="issuedDate">Issued Date</label>
                                    <input type="text" class="form-control date-picker" id="issuedDate"
                                        placeholder="Select issued date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiryDate">Expiry Date <small id="expiryDateLabel">(if
                                            applicable)</small></label>
                                    <input type="text" class="form-control date-picker" id="expiryDate"
                                        placeholder="Select expiry date">
                                </div>
                            </div>
                        </div>
                        <!-- File Upload Area -->
                        <div class="form-group">
                            <label><strong>Upload File</strong></label>
                            <input type="file" id="documentFile" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        </div>
                        <!-- preview area -->
                        <div id="fileInfo" class="mt-2" style="display:none;">
                            <div id="filePreview"></div>
                            <small id="fileName" class="text-muted"></small>
                        </div>
                        <div class="form-group">
                            <label for="docNotes">Notes <small>(optional)</small></label>
                            <textarea class="form-control" id="docNotes" rows="3" placeholder="Add any additional notes..."></textarea>
                        </div>

                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitDocumentBtn">
                            <i class="fa fa-upload mr-2"></i>Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script src="/plugins/jquery-smartwizard-master/dist/js/jquery.smartWizard.js"></script>
    <script src="/plugins/iCheck/icheck.min.js"></script>
    {{-- <script src="/plugins/switchery/dist/switchery.min.js"></script> --}}

    <script>
        let uploadedDocuments = [];
        let _file = null;
        $(document).ready(function() {
            $('#smartwizard').smartWizard({
                selected: 0,
                theme: 'arrows',
                transition: {
                    animation: 'none'
                },
                toolbar: {
                    showNextButton: true,
                    showPreviousButton: true,
                    position: 'bottom',
                    extraHtml: `<button class="btn btn-success" id="btnFinish" disabled onclick="onSubmit()">Submit</button>`
                },
                anchor: {
                    enableNavigation: true,
                    enableNavigationAlways: false,
                    enableDoneState: true,
                    markPreviousStepsAsDone: true,
                    unDoneOnBackNavigation: true,
                    enableDoneStateNavigation: true
                }
            });
            $("#smartwizard").on("leaveStep", function(e, anchorObject, currentStepIdx, nextStepIdx,
                stepDirection) {
                // Validate only on forward movement  
                if (stepDirection == 'forward') {
                    let form = document.getElementById('form-' + (currentStepIdx + 1));
                    if (form) {
                        if (!form.checkValidity()) {
                            form.classList.add('was-validated');
                            $('#smartwizard').smartWizard("setState", [currentStepIdx], 'error');
                            $("#smartwizard").smartWizard('fixHeight');
                            return false;
                        }
                        $('#smartwizard').smartWizard("unsetState", [currentStepIdx], 'error');
                    }
                }
            });

            // Step show event
            $("#smartwizard").on("showStep", function(e, anchorObject, stepIndex, stepDirection, stepPosition) {
                $("#prev-btn").removeClass('disabled').prop('disabled', false);
                $("#next-btn").removeClass('disabled').prop('disabled', false);
                if (stepPosition === 'first') {
                    $("#prev-btn").addClass('disabled').prop('disabled', true);
                } else if (stepPosition === 'last') {
                    $("#next-btn").addClass('disabled').prop('disabled', true);
                } else {
                    $("#prev-btn").removeClass('disabled').prop('disabled', false);
                    $("#next-btn").removeClass('disabled').prop('disabled', false);
                }
                // Get step info from Smart Wizard
                let stepInfo = $('#smartwizard').smartWizard("getStepInfo");
                $("#sw-current-step").text(stepInfo.currentStep + 1);
                $("#sw-total-step").text(stepInfo.totalSteps);
                $("#btnFinish").prop('disabled', stepPosition == 'last' ? false : true);
                // Focus first name
                if (stepIndex == 1) {
                    setTimeout(() => {
                        $('#first-name').focus();
                    }, 0);
                }
            });

            $("#state_selector").on("change", function() {
                $('#smartwizard').smartWizard("setState",
                    [$('#step_to_style').val()], $(this).val(), !$('#is_reset').prop("checked"));
                return true;
            });

            $("#style_selector").on("change", function() {
                $('#smartwizard').smartWizard("setStyle",
                    [$('#step_to_style').val()], $(this).val(), !$('#is_reset').prop("checked"));
                return true;
            });

            $("#check-redentian").on('change', function() {
                let val = $(this).prop('checked');
                let address = $('#address').val();
                $('#current-address').val(val ? address : '');
            })

            setupDragAndDrop();

            $('#documentFile').on('change', handleFileSelect);

            $('#submitDocumentBtn').on('click', function(e) {
                e.preventDefault();
                if (!_file) {
                    sweetAlert("Warning", "Please select a file to upload.", "warning");
                    return;
                }
                const documentData = {
                    category: {
                        id: $('#docCategorySelect').val(),
                        name: $('#docCategorySelect option:selected').text().trim()
                    },
                    documentNumber: $('#documentNumber').val(),
                    issuedDate: $('#issuedDate').val(),
                    expiryDate: $('#expiryDate').val(),
                    notes: $('#docNotes').val(),
                    file: _file
                };
                uploadedDocuments.push(documentData);
                $('#documentForm')[0].reset();
                $('#fileInfo').hide();
                _file = null;
                $('#documentUploadModal').modal('hide');
                renderDocumentList();
            });

        })

        function renderDocumentList() {
            const list = $('#document-list');
            list.empty();
            if (uploadedDocuments.length === 0) {
                list.html(`
                    <div class="col-md-12">
                        <div class="list-group">
                            <p class="text-muted text-center">No documents uploaded yet</p>
                        </div>
                    </div>
                `);
                $('.tab-content').css('min-height', '170px');
                return;
            }
            uploadedDocuments.forEach((doc, index) => {
                const previewUrl = URL.createObjectURL(doc.file);
                let previewContent = '';
                if (doc.file.type.startsWith('image/')) {
                    previewContent =
                        `<img src="${previewUrl}" class="card-img-top img-thumbnail" style="object-fit: cover;" />`;
                } else if (doc.file.type === 'application/pdf') {
                    previewContent =
                        `<div class="pdf-preview"><embed src="${previewUrl}" type="application/pdf" /> </div>`;
                } else {
                    previewContent = `<p>${doc.file.name}</p>`;
                }

                const item = $(`
                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 text-center">
                                        ${previewContent}
                                       <label>${doc.category.name}</label>
                                       <button class="btn btn-block btn-danger btn-sm" onclick="removeDocument(${index})">
                                           <i class="fa fa-trash"></i> Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`);
                list.append(item);
            });
            const multiplier = Math.ceil(uploadedDocuments.length / 4);
            $('.tab-content').css('min-height', `${multiplier * 500}px`);
        }

        function removeDocument(index) {
            uploadedDocuments.splice(index, 1);
            renderDocumentList();
        }

        function onSubmit() {
            const getVal = id => $(`#${id}`).val() || "";
            const getDate = id => {
                const val = getVal(id);
                return val ? moment(val, "DD MMMM YYYY").format("YYYY-MM-DD") : null;
            };
            const getText = id => $(`#${id} option:selected`).text().trim();

            const firstName = getVal('first-name');
            const lastName = getVal('last-name');
            const fullName = lastName ? `${firstName} ${lastName}` : firstName;

            let empStatus = getVal('employee-status');

            const personal = {
                firstName,
                lastName,
                fullName,
                barcode: getVal('barcode'),
                email: getVal('email'),
                address: getVal('address'),
                currentAddress: getVal('current-address'),
                birthPlace: getVal('birth-place'),
                birthDate: getDate('birth-date'),
                phone: getVal('phone'),
                avatar: "",
                mobilePhone: getVal('mobile-phone'),
                gendre: $('input[name="gender"]').val(),
                maritalStatus: getVal('marital-status'),
                bloodType: getVal('blood-type'),
                religionId: getVal('religion'),
                identityType: getVal('identity-type'),
                identityNumber: getVal('nik'),
                expiredDateIdentityId: getDate('passport-expired-date'),
                postalCode: getVal('postal-code'),
                passportNumber: getVal('passport-number')
            };

            const employment = {
                employeeId: getVal('employee-id'),
                organizationId: getVal('organization'),
                organizationName: getText('organization'),
                jobPositionId: getVal('position'),
                jobPositionName: getText('position'),
                approvalLine: getVal('approval'),
                approvalLineName: getText('approval'),
                jobLevelId: getVal('level'),
                jobLevelName: getText('level'),
                branchId: getVal('branch'),
                branchName: getText('branch'),
                employmentStatus: empStatus,
                joinDate: getDate('join-date'),
                endDate: empStatus === "permanent" ? null : getDate('end-date'),
                signDate: getDate('sign-date')
            };

            const payrollInfo = {
                bankId: getVal('bank'),
                bankName: getText('bank'),
                accountHolder: getVal('account-holder'),
                accountNumber: getVal('account-number'),
                npwp: getVal('npwp'),
                ptkpStatus: getVal('ptkp-status'),
                bpjsKetenagakerjaan: getVal('bpjs-ketenagakerjaan'),
                bpjsKesehatan: getVal('bpjs-kesehatan'),
                employmentTaxStatus: getVal('employment-tax-status')
            };

            const formData = new FormData();
            formData.append("personal", JSON.stringify(personal));
            formData.append("employment", JSON.stringify(employment));
            formData.append("payrollInfo", JSON.stringify(payrollInfo));
            formData.append("schedule", getVal('schedule'));
            formData.append("approvalLine", getVal('approval'));
            formData.append("inviteAccount", $('#create-account').prop('checked'));

            uploadedDocuments.forEach((doc, index) => {
                formData.append(`documents[${index}][category_id]`, doc.category.id);
                formData.append(`documents[${index}][category_name]`, doc.category.name);
                formData.append(`documents[${index}][document_number]`, doc.documentNumber);
                formData.append(`documents[${index}][issued_date]`, doc.issuedDate ? moment(doc.issuedDate,
                    "DD MMMM YYYY").format("YYYY-MM-DD") : "");
                formData.append(`documents[${index}][expiry_date]`, doc.expiryDate ? moment(doc.expiryDate,
                    "DD MMMM YYYY").format("YYYY-MM-DD") : "");
                formData.append(`documents[${index}][notes]`, doc.notes);
                formData.append(`documents[${index}][file]`, doc.file);
            });

            $.ajax({
                url: `/employee`,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function(res) {
                    sweetAlert("Success", "Employee has been saved successfully.", "success");
                    // setTimeout(() => window.location.href = "/employee/create", 1000);
                },
                error: function(err) {
                    sweetAlert("Failed", err.responseJSON.message, "error");
                },
            });
        }

        function setupDragAndDrop() {
            const dropZone = $('#dropZone');

            dropZone.on('dragover', function(e) {
                e.preventDefault();
                $(this).css('border-color', '#28a745').css('background-color', '#f1f1f1');
            });

            dropZone.on('dragleave', function() {
                $(this).css('border-color', '#007bff').css('background-color', '');
            });

            dropZone.on('drop', function(e) {
                e.preventDefault();
                $(this).css('border-color', '#007bff').css('background-color', '');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#documentFile')[0].files = files;
                    handleFileSelect();
                }
            });

            dropZone.on('click', function(e) {
                if (e.target === this) {
                    $('#documentFile').click();
                }
            });

            $('#documentFile').on('click', function(e) {
                e.stopPropagation();
            });
        }

        function handleFileSelect() {
            const file = $('#documentFile')[0].files[0];
            const fileInfo = $('#fileInfo');
            const fileName = $('#fileName');

            if (file) {
                const maxSize = 5 * 1024 * 1024
                if (file.size > maxSize) {
                    sweetAlert("Warning", "File size exceeds 5MB limit", "warning");
                    $('#documentFile').val('');
                    fileInfo.hide();
                    return;
                }

                const preview = $('#filePreview');
                preview.empty();

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html(`<img src="${e.target.result}" alt="preview" />`);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html(`<embed src="${e.target.result}" type="application/pdf" />`);
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.html(`<p>${file.name}</p>`);
                }

                fileName.text(`${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
                fileInfo.show();
                _file = file;
            } else {
                fileInfo.hide();
                _file = null;
            }
        }
    </script>
@endsection

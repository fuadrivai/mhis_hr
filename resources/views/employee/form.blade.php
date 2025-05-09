@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/nprogress/nprogress.css" rel="stylesheet">
    <link href="/plugins/iCheck/skins/flat/green.css" rel="stylesheet">
    <link href="/plugins/switchery/dist/switchery.min.css" rel="stylesheet">
    
@endsection

@section('content-child')
<div class="x_panel">
    <div class="x_title">
        <h2>Employee <small>Form</small></h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
        </ul>
        <div class="clearfix"></div>
    </div>
    <div class="x_content">
      <div id="wizard" class="form_wizard wizard_horizontal">
        <ul class="wizard_steps">
            <li>
              <a href="#step-1">
                  <span class="step_no">1</span>
                  <span class="step_descr">Personal Data</span>
              </a>
            </li>
          <li>
            <a href="#step-2">
              <span class="step_no">2</span>
              <span class="step_descr">Employment Data</span>
            </a>
          </li>
          <li>
            <a href="#step-3">
              <span class="step_no">3</span>
              <span class="step_descr">Payroll Data</span>
            </a>
          </li>
          <li>
            <a href="#step-4">
              <span class="step_no">4</span>
              <span class="step_descr">Invite Employee</span>
            </a>
          </li>
        </ul>
        <div id="step-1">
          <form class="form-horizontal form-label-left">
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
                      <input id="first_name" name="first_name" required type="text" class="form-control" placeholder="First Name">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Last name</label>
                      <input id="last_name" name="last_name" type="text" class="form-control" placeholder="Last Name">
                    </div>
                  </div>
                  <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                      <label for="">Email*</label>
                      <input id="email" name="email" required type="email" class="form-control" placeholder="example@email.com">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Place of birth*</label>
                      <input id="birth_place" name="birth_place" required type="text" class="form-control" placeholder="Birth Place">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group has-feedback">
                      <label for="">Date of birth*</label>
											<input type="text" required class="form-control has-feedback-left date-picker" id="inputSuccess2" placeholder="DOB">
											<span style="top: 25px" class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Gender</label>
										<p>
											<label for="genderM">Male : </label> 
											<input type="radio" class="flat" name="gender" id="genderM" value="1" checked="" required /> <label for="genderF">Female :</label>
											<input type="radio" class="flat" name="gender" id="genderF" value="2" required />
										</p>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Marital status</label>
                      <select name="marital_status" class="form-control select2" id="marital_status">
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
                      <select name="" class="form-control select2" id="">
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
                      <select name="" class="form-control select2" id="">
                        @foreach ($religions as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
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
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">NIK (NPWP 16 digit)*</label>
                      <input id="nik" name="nik" required type="text" class="form-control" placeholder="0000 0000 0000 0000">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Passport number</label>
                      <input id="last_name" name="last_name" type="text" class="form-control" placeholder="Last Name">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group has-feedback">
                      <label for="">Passport expiry date</label>
											<input type="text" class="form-control date-picker has-feedback-left" id="inputSuccess2" placeholder="First Name">
											<span style="top: 25px" class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Postal code</label>
                      <input id="postal_code" name="postal_code"  type="text" class="form-control">
                    </div>
                  </div>
                  <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                      <label for="">Citizen ID address</label>
                      <textarea class="form-control" name="" id="" rows="3"></textarea>
                    </div>
                  </div>
                  <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                      <div class="checkbox">
                        <label>
                          <input type="checkbox" value=""> Use as residential address
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12 col-sm-12">
                    <div class="form-group">
                      <label for="">Citizen ID address</label>
                      <textarea class="form-control" name="" id="" rows="3"></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div id="step-2">
          <form action="">
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
                      <input id="employee_id" name="employee_id" required type="text" class="form-control" placeholder="Employee ID">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Barcode</label>
                      <input id="barcode" name="barcode" type="text" class="form-control" placeholder="Barcode">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Branch*</label>
                      <select name="branch" id="branch" required class="form-control select2">
                        @foreach ($branches as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Organization*</label>
                      <select name="organization" id="organization" required class="form-control select2">
                        @foreach ($organizations as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Job position*</label>
                      <select name="position" id="position" required class="form-control select2">
                        @foreach ($positions as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Job level*</label>
                      <select name="level" id="level" required class="form-control select2">
                        @foreach ($levels as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                      <label for="">Employment status*</label>
                      <select name="level" id="level" required class="form-control select2">
                        <option value="permanent">Permanent</option>
                        <option value="contract">Contract</option>
                        <option value="freelance">Freelance</option>
                        <option value="probation">Probation</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group has-feedback">
                      <label for="">Join Date*</label>
											<input type="text" readonly required class="form-control date-picker has-feedback-left" id="inputSuccess2" placeholder="Join Date">
											<span style="top: 25px" class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group has-feedback">
                      <label for="">End status date</label>
											<input type="text" class="form-control date-picker has-feedback-left" id="inputSuccess2" placeholder="End status date">
											<span style="top: 25px" class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group has-feedback">
                      <label for="">Sign date</label>
											<input type="text" class="form-control date-picker has-feedback-left" id="inputSuccess2" placeholder="End status date">
											<span style="top: 25px" class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Schedule</label>
                      <select name="level" id="level" class="form-control select2">
                        <option value="permanent">Permanent</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Approval</label>
                      <select name="level" id="level" class="form-control select2">
                        <option value="permanent">Permanent</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div id="step-3">
          <form action="">
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
                      <select name="bank" id="bank" class="form-control select2">
                        <option value="permanent">Permanent</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                      <label for="">Account number</label>
                      <input id="account_number" name="account_number" type="text" class="form-control" placeholder="Account Number">
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                      <label for="">Account holder name</label>
                      <input id="account_holder" name="account_holder" type="text" class="form-control" placeholder="Account holder name">
                    </div>
                  </div>
                  <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                      <label for="">NPWP 15 digit (old)</label>
                      <input type="text" class="form-control" name="npwp">
                    </div>
                  </div>
                  <div class="col-md-2 col-sm-12">
                    <div class="form-group">
                      <label for="">PTKP Status</label>
                      <select name="organization" id="organization" class="form-control select2">
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
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">Employment tax status</label>
                      <select name="organization" id="organization" class="form-control select2">
                        <option value="0">Pegawai Tetap</option>
                        <option value="1">Pegawai Tidak Tetap</option>
                        <option value="2">Bukan Pegawai yang Bersifat Berkesinambunga</option>
                        <option value="3">Bukan Pegawai yang tidak Bersifat Berkesinambungan</option>
                        <option value="4">Ekspatriat</option>
                        <option value="5">Ekspatriat Dalam Negeri</option>
                        <option value="6">Tenaga Ahli yang Bersifat Berkesinambungan</option>
                        <option value="7">Tenaga Ahli yang Tidak Bersifat Berkesinambungan</option>
                        <option value="8">Dewan Komisaris</option>
                        <option value="9">Tenaga Ahli yang Bersifat Berkesinambungan >1 PK</option>
                        <option value="10">Tenaga Kerja Lepas</option>
                        <option value="11">Bukan Pegawai yang Bersifat Berkesinambungan >1 PK</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">BPJS ketenagakerjaan number</label>
                      <input type="text" class="form-control" name="bpjs_ketenagakerjaan" id="bpjs_ketenagakerjaan">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                      <label for="">BPJS Kesehatan number</label>
                      <input type="text" class="form-control" name="bpjs_kesehatan" id="bpjs_kesehatan">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div id="step-4">
          
        </div>
      </div>
    </div>
</div>
@endsection

@section('content-script')
    <script src="/plugins/jQuery-Smart-Wizard/js/jquery.smartWizard.js"></script>
    <script src="/plugins/iCheck/icheck.min.js"></script>
    <script src="/plugins/switchery/dist/switchery.min.js"></script>
    

    <script>
      $(document).ready(function () {
        $('.stepContainer').css('height',"1050px")
        // $('.stepContainer').removeClass('stepContainer')
      })
    </script>
@endsection
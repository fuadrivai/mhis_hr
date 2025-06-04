@extends('layouts.info')

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
                            <div class="row pt-3">
                                <div class="col-md-4 col-12">
                                    <h4 style="color: black">Personal Data</h4>
                                    <br>
                                    <small>Your email address is your identity on MHIS Hub is used to log in.</small>
                                </div>
                                <div class="col-md-8 col-12">
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-user text-info"></i> Full Name</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data->personal->fullname }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-phone text-info"></i> Phone</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data->personal->phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-mobile-phone text-info"></i> Mobile Phone</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->mobile_phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-envelope text-info"></i> Email</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-globe text-info"></i> Place of Birth</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->birth_place ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-calendar text-info"></i> Birthdate</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->birth_date ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i
                                                    class="fa  fa-{{ $data?->personal?->gendre == 1 ? 'male' : 'female' }} text-info"></i>
                                                gender</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->gendre == 1 ? 'Male' : 'Female' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-pagelines text-info"></i> Marital Status</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            @switch($data?->personal?->marital_status)
                                                @case(1)
                                                    <p>Single</p>
                                                @break

                                                @case(2)
                                                    <p>Merried</p>
                                                @break

                                                @case(3)
                                                    <p>Widow</p>
                                                @break

                                                @default
                                                    <p>Widower</p>
                                            @endswitch

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-calendar text-info"></i> Blood Type</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->blood_type ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <p><i class="fa fa-calendar text-info"></i> Religion</p>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <p>{{ $data?->personal?->religion->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <h4 style="color: black">Identity & Address</h4>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                            Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four
                            loko farm-to-table craft beer twee. Qui photo
                            booth letterpress, commodo enim craft beer mlkshk aliquip
                        </div>
                        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            xxFood truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                            Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four
                            loko farm-to-table craft beer twee. Qui photo
                            booth letterpress, commodo enim craft beer mlkshk
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

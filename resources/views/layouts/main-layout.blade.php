<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MHIS || HRIS</title>
    <!-- Bootstrap -->
    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="/plugins/nprogress/nprogress.css" rel="stylesheet">
    <link href="/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet">
    <link href="/plugins/bootstrap-datepicker/css/jquery.timepicker.css" rel="stylesheet">
    {{-- <link href="/plugins/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"> --}}
    <link href="/plugins/select2/dist/css/select2.min.css" rel="stylesheet">
    @yield('content-class')
    <!-- Custom Theme Style -->
    <link href="/build/css/custom.min.css" rel="stylesheet">
</head>

<body class="nav-md">
    <?php $image = session('avatar'); ?>
    <?php $empId = session('empId'); ?>
    <div class="container body">
        <div class="main_container">
            <div class="col-md-3 left_col">
                <div class="left_col scroll-view">
                    <div class="navbar nav_title" style="border: 0;">
                        <a href="" class="site_title"><img class="img-thumbnail rounded"
                                src="/images/logo-mh.png" width="35" alt=""> <span>Mutiara
                                Harapan</span></a>
                    </div>
                    <div class="clearfix"></div>
                    <!-- menu profile quick info -->
                    <div class="profile clearfix">
                        <div class="profile_pic" style = "padding-right: 15px; position: relative; width: 80px;">
                            <img id="profileImagePreviewTrigger"
                                src="{{ $image ? asset('storage/' . $image) : asset('images/user.png') }}"
                                alt="Profile image" class="img-circle profile_img"
                                style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; cursor: pointer;">
                        </div>
                        <div class="profile_info" style = "padding-left: 15px;">
                            <span>Welcome,</span>
                            <a href="/profile/personal/{{ $empId }}">
                                <h2> {{ auth()->user()->name }}</h2>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <!-- /menu profile quick info -->
                    <br />
                    <!-- sidebar menu -->
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                            <ul class="nav side-menu">
                                <li><a href="/"><i class="fa fa-dashboard"></i> Home </a></li>
                                {{-- <li><a href="/pin-location"><i class="fa fa-map"></i> Pin Location </a></li> --}}

                                <?php
                                $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
                                $isRole3 = auth()->user()->roles->contains('id', 3);
                                ?>

                                @if ($isAdmin)
                                    <li class={{ Request::is('employee*') ? 'active' : '' }}><a><i
                                                class="fa fa-users"></i>
                                            Employee Directory <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu"
                                            style="display: {{ Request::is('employee*') ? 'block' : 'none' }}">
                                            <li class={{ Request::is('employee*') ? 'current-page' : '' }}><a
                                                    href="/employee">Employee</a></li>
                                            <li class={{ Request::is('employee/reprimand*') ? 'current-page' : '' }}>
                                                <a href="/employee/reprimand">Reprimand</a>
                                            </li>
                                            <li class={{ Request::is('employee*') ? 'current-page' : '' }}><a
                                                    href="/scheduler">Scheduler</a></li>
                                        </ul>
                                    </li>
                                @endif

                                @if ($isAdmin)
                                    <li class={{ Request::is('time*') ? 'active' : '' }}><a><i
                                                class="fa fa-clock-o"></i>
                                            Time <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu"
                                            style="display: {{ Request::is('time*') ? 'block' : 'none' }}">
                                            <li class={{ Request::is('time*') ? 'current-page' : '' }}><a
                                                    href="/time/attendance">Attendance</a></li>
                                            <li class={{ Request::is('time*') ? 'current-page' : '' }}><a
                                                    href="#">Overtime</a></li>
                                            <li class={{ Request::is('time*') ? 'current-page' : '' }}><a
                                                    href="/time/request">Time Off</a></li>
                                        </ul>
                                    </li>
                                    </li>
                                @endif

                                <li class={{ Request::is('lesson-plan*') ? 'active' : '' }}><a><i
                                            class="fa fa-book"></i>
                                        Lesson Plan <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu"
                                        style="display: {{ Request::is('lesson-plan*') ? 'block' : 'none' }}">
                                        <li class={{ Request::is('lesson-plan/my*') ? 'current-page' : '' }}><a
                                                href="{{ route('employee.lesson-plan.index') }}">My Lesson Plans</a>
                                        </li>
                                        <li class={{ Request::is('lesson-plan/approvals*') ? 'current-page' : '' }}><a
                                                href="{{ route('lesson-plan.approvals.index') }}">Approvals</a></li>
                                        <?php $isMonitor = \App\Models\SubjectCategoryMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists(); ?>
                                        @if ($isMonitor)
                                            <li
                                                class={{ Request::is('lesson-plan/monitoring*') ? 'current-page' : '' }}>
                                                <a href="{{ route('employee.lesson-plan.monitoring.index') }}">Monitoring
                                                    Lesson Plan</a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>

                                <li class={{ Request::is('assessment*') ? 'active' : '' }}><a><i
                                            class="fa fa-list-alt"></i>
                                        Assessment <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu"
                                        style="display: {{ Request::is('assessment*') ? 'block' : 'none' }}">
                                        <li class={{ Request::is('assessment/my*') ? 'current-page' : '' }}><a
                                                href="{{ route('employee.assessment.index') }}">My Assessments</a></li>
                                        <li class={{ Request::is('assessment/approvals*') ? 'current-page' : '' }}><a
                                                href="{{ route('assessment.approvals.index') }}">Approvals</a></li>
                                    </ul>
                                </li>

                                @if ($isAdmin || $isRole3)
                                    <li class={{ Request::is('setting*') ? 'active' : '' }}><a><i
                                                class="fa fa-gears"></i>
                                            Settings <span class="fa fa-chevron-down"></span></a>
                                        <ul style="display: {{ Request::is('setting*') ? 'block' : 'none' }}"
                                            class="nav child_menu">
                                            <li><a>Company<span class="fa fa-chevron-down"></span></a>
                                                <ul class="nav child_menu">
                                                    @if ($isAdmin)
                                                        <li><a href="/setting/branch">Branch</a></li>
                                                        <li><a href="/setting/organization">Organization</a></li>
                                                        <li><a href="/setting/position">Job Position</a></li>
                                                        <li><a href="/setting/level">Job Level</a></li>
                                                        <li><a href="/setting/religion">Religion</a></li>
                                                        <li><a href="/setting/reprimand-type">Reprimand Type</a></li>
                                                        <li><a href="/setting/academic-year">Academic Year</a></li>
                                                    @endif
                                                    <li><a href="/setting/kpi-template">KPI Template</a></li>
                                                    @if ($isAdmin)
                                                        <li><a href="/setting/lesson-plan">Lesson Plan Settings</a>
                                                        </li>
                                                        <li><a href="/setting/lesson-plan-target">Lesson Plan
                                                                Targets</a></li>
                                                        <li><a href="/setting/assessment">Assessment Settings</a></li>
                                                        <li><a href="/setting/assessment-target">Assessment Targets</a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </li>
                                            @if ($isAdmin)
                                                <li class={{ Request::is('setting*') ? 'active' : '' }}><a>Time<span
                                                            class="fa fa-chevron-down"></span></a>
                                                    <?php
                                                    $isBlock = Request::is('setting/schedule*') || Request::is('setting/shift*') || Request::is('setting/timeoff*') || Request::is('setting/holiday*') || Request::is('setting/location*');
                                                    ?>
                                                    <ul style="display: {{ $isBlock ? 'block' : 'none' }}"
                                                        class="nav child_menu">
                                                        <li><a href="/setting/schedule">Schedule</a></li>
                                                        <li><a href="/setting/timeoff">Time off</a></li>
                                                        <li><a href="/setting/holiday">Holiday</a></li>
                                                        <li><a href="/setting/location">Live Attendance</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="/setting/approval">Approval</a></li>
                                                <li><a href="/setting/bank">Bank</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif

                                @if ($isAdmin)
                                    <li><a href="/user"><i class="fa fa-user"></i> Management User </a></li>
                                @endif
                                <li><a href="/internal-document/create" target="_blank"><i class="fa fa-folder"></i>
                                        Form Document </a></li>
                                {{-- <li><a href="/signature"><i class="fa fa-qrcode"></i> E Signature </a></li> --}}
                            </ul>
                        </div>

                    </div>
                    <!-- /sidebar menu -->
                </div>
            </div>
            <!-- top navigation -->
            <div class="top_nav">
                <div class="nav_menu">
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>
                    <nav class="nav navbar-nav">
                        <ul class=" navbar-right">
                            <li class="nav-item dropdown open" style="padding-left: 15px;">
                                <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true"
                                    id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ $image ? asset('storage/' . $image) : asset('images/user.png') }}"
                                        alt="">{{ auth()->user()->name }}

                                </a>
                                <div class="dropdown-menu dropdown-usermenu pull-right"
                                    aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="/profile/personal/{{ $empId }}">
                                        Profile</a>
                                    {{-- <a class="dropdown-item" href="javascript:;">
                                        <span class="badge bg-red pull-right">50%</span>
                                        <span>Settings</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">Help</a> --}}
                                    <form action="/logout" method="post">
                                        @csrf
                                        <button class="dropdown-item" type="submit"><i
                                                class="fa fa-sign-out pull-right"></i> Log Out</button>
                                    </form>

                                </div>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <!-- /top navigation -->

            <!-- page content -->
            <div class="right_col" role="main">
                <div class="page-title">
                    <div class="title_left">
                        <h3>{{ $title }}</h3>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="row">
                    {{-- Main Contain  --}}
                    @yield('content-child')
                </div>
            </div>
            <!-- /page content -->

            <!-- footer content -->
            <footer>
                <div class="pull-right">
                    Copyright © 2024 <a href="https://mutiaraharapan.sch.id/">Mutiara Harapan Islamic School</a>. All
                    rights reserved.
                </div>
                <div class="clearfix"></div>
            </footer>
            <!-- /footer content -->
        </div>
    </div>

    <!-- jQuery -->
    <script src="/plugins/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="/plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/plugins/moment/moment.js"></script>
    <script src="/plugins/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <!-- FastClick -->
    <script src="/plugins/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="/plugins/nprogress/nprogress.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="/build/js/custom.min.js"></script>
    {{-- <script src="/plugins/jquery-validate/jquery.validate.min.js"></script> --}}
    <script src="/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="/plugins/bootstrap-datepicker/js/jquery.timepicker.min.js"></script>
    <script src="/plugins/select2/dist/js/select2.full.min.js"></script>
    <script src="/plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="/plugins/jquery.blockUI/jquery.blockUI.js"></script>
    <script src="/js/script.js?v=1.1.6"></script>

    <div class="modal fade" id="profileImagePreviewModal" tabindex="-1" role="dialog"
        aria-labelledby="profileImagePreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileImagePreviewLabel">Profile Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="profileImagePreviewFull"
                        src="{{ $image ? asset('storage/' . $image) : asset('images/user.png') }}"
                        alt="Profile image preview" style="max-width: 100%; max-height: 70vh; border-radius: 10px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="saveProfileImage" disabled>
                        <i class="fa fa-save"></i> Save Image
                    </button>
                    <button type="button" class="btn btn-primary" id="openImageSourceModal">
                        <i class="fa fa-pencil"></i> Edit Picture
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageSourceModal" tabindex="-1" role="dialog" aria-labelledby="imageSourceLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageSourceLabel">Choose Image Source</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <button type="button" class="btn btn-info btn-block" id="chooseFromCamera">
                        <i class="fa fa-camera"></i> Camera
                    </button>
                    <button type="button" class="btn btn-default btn-block" id="chooseFromComputer"
                        style="margin-top: 8px;">
                        <i class="fa fa-folder-open"></i> Computer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cameraCaptureModal" tabindex="-1" role="dialog"
        aria-labelledby="cameraCaptureLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraCaptureLabel">Take a Picture</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <video id="cameraVideo" autoplay playsinline
                        style="width: 100%; max-height: 60vh; border-radius: 10px; background: #000;"></video>
                    <canvas id="cameraCanvas" style="display: none;"></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="captureCameraPhoto">
                        <i class="fa fa-camera"></i> Capture
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <input type="file" id="avatarInputCamera" accept="image/*" capture="user" style="display: none;">
    <input type="file" id="avatarInputComputer" accept="image/*" style="display: none;">

    <script>
        var cameraStream = null;
        var selectedAvatarBase64 = null;
        var avatarUploadEndpoint = '/employee/face/register';

        function setPreviewImage(result, imageBase64) {
            selectedAvatarBase64 = imageBase64 || null;
            $('#profileImagePreviewFull').attr('src', result);
            $('#profileImagePreviewTrigger').attr('src', result);
            $('.profile-image-preview-trigger').each(function() {
                if (this.tagName && this.tagName.toLowerCase() === 'img') {
                    $(this).attr('src', result);
                }
                $(this).attr('data-preview-src', result);
            });
            $('#saveProfileImage').prop('disabled', !selectedAvatarBase64);
        }

        function stopCameraStream() {
            if (!cameraStream) {
                return;
            }
            cameraStream.getTracks().forEach(function(track) {
                track.stop();
            });
            cameraStream = null;
            $('#cameraVideo').get(0).srcObject = null;
        }

        $(document).on('click', '#profileImagePreviewTrigger, .profile-image-preview-trigger', function() {
            var previewSrc = $(this).data('preview-src') || $(this).attr('src');
            if (previewSrc) {
                $('#profileImagePreviewFull').attr('src', previewSrc);
            }
            $('#profileImagePreviewModal').modal('show');
        });

        $(document).on('click', '#openImageSourceModal', function() {
            $('#imageSourceModal').modal('show');
        });

        $(document).on('click', '#chooseFromCamera', function() {
            $('#imageSourceModal').modal('hide');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                $('#avatarInputCamera').trigger('click');
                return;
            }

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user'
                },
                audio: false
            }).then(function(stream) {
                cameraStream = stream;
                var video = $('#cameraVideo').get(0);
                video.srcObject = stream;
                $('#cameraCaptureModal').modal('show');
            }).catch(function() {
                $('#avatarInputCamera').trigger('click');
            });
        });

        $(document).on('click', '#captureCameraPhoto', function() {
            var video = $('#cameraVideo').get(0);
            if (!video || !video.videoWidth || !video.videoHeight) {
                return;
            }

            var canvas = $('#cameraCanvas').get(0);
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                if (!blob) {
                    return;
                }
                var result = canvas.toDataURL('image/jpeg', 0.9);
                setPreviewImage(result, result);
                $('#cameraCaptureModal').modal('hide');
            }, 'image/jpeg', 0.9);
        });

        $('#cameraCaptureModal').on('hidden.bs.modal', function() {
            stopCameraStream();
        });

        $(document).on('click', '#chooseFromComputer', function() {
            $('#imageSourceModal').modal('hide');
            $('#avatarInputComputer').trigger('click');
        });

        $(document).on('change', '#avatarInputCamera, #avatarInputComputer', function(event) {
            var file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var result = e.target.result;
                setPreviewImage(result, result);
            };
            reader.readAsDataURL(file);
        });

        $(document).on('click', '#saveProfileImage', function() {
            if (!selectedAvatarBase64) {
                return;
            }

            var $btn = $('#saveProfileImage');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            let payload = {
                employee_id: '{{ $empId }}',
                image: selectedAvatarBase64,
                photo: selectedAvatarBase64
            };
            ajax(payload, avatarUploadEndpoint, "POST", function(json) {
                sweetAlert("Success", "Data successfully recorded", "success");
                selectedAvatarBase64 = null;
                $('#saveProfileImage').prop('disabled', true);
                $('#profileImagePreviewModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            });
        });
    </script>
    @yield('content-script')
</body>

</html>

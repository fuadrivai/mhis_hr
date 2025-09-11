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
                        <div class="profile_pic">
                            <img src="/images/img.jpg" alt="..." class="img-circle profile_img">
                        </div>
                        <div class="profile_info">
                            <span>Welcome,</span>
                            <h2>{{ auth()->user()->name }}</h2>
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
                                <li><a href="/location"><i class="fa fa-map"></i> Pin Location </a></li>
                                <li class={{ Request::is('employee*') ? 'active' : '' }}><a><i class="fa fa-users"></i>
                                        Employee Directory <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu"
                                        style="display: {{ Request::is('employee*') ? 'block' : 'none' }}">
                                        <li class={{ Request::is('employee*') ? 'current-page' : '' }}><a
                                                href="/employee">Employee</a></li>
                                        <li class={{ Request::is('employee*') ? 'current-page' : '' }}><a
                                                href="/scheduler">Scheduler</a></li>
                                    </ul>
                                </li>
                                <li class={{ Request::is('setting*') ? 'active' : '' }}><a><i class="fa fa-gears"></i>
                                        Settings <span class="fa fa-chevron-down"></span></a>
                                    <ul style="display: {{ Request::is('setting*') ? 'block' : 'none' }}"
                                        class="nav child_menu">
                                        <li><a>Company<span class="fa fa-chevron-down"></span></a>
                                            <ul class="nav child_menu">
                                                <li><a href="/setting/company">Company Info</a></li>
                                                <li><a href="/setting/branch">Branch</a></li>
                                                <li><a href="/setting/organization">Organization</a></li>
                                                <li><a href="/setting/position">Job Position</a></li>
                                                <li><a href="/setting/level">Job Level</a></li>
                                                <li><a href="/setting/religion">Religion</a></li>
                                            </ul>
                                        </li>
                                        <li class={{ Request::is('setting*') ? 'active' : '' }}><a>Time<span
                                                    class="fa fa-chevron-down"></span></a>
                                            <?php
                                            $isBlock = Request::is('setting/schedule*') || Request::is('setting/shift*');
                                            ?>
                                            <ul style="display: {{ $isBlock ? 'block' : 'none' }}"
                                                class="nav child_menu">
                                                <li><a href="/setting/schedule">Schedule</a></li>
                                                <li><a href="/setting/timeoff">Time off</a></li>
                                                <li><a href="/setting/holiday">Holiday</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="/setting/bank">Bank</a></li>
                                    </ul>
                                </li>
                                <li><a href="/user"><i class="fa fa-user"></i> Management User </a></li>
                                <li><a href="/signature"><i class="fa fa-qrcode"></i> E Signature </a></li>
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
                                    <img src="/images/img.jpg" alt="">{{ auth()->user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-usermenu pull-right"
                                    aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="javascript:;"> Profile</a>
                                    <a class="dropdown-item" href="javascript:;">
                                        <span class="badge bg-red pull-right">50%</span>
                                        <span>Settings</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:;">Help</a>
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
                <div class="">
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
    <script src="/js/script.js?v=1.1.3"></script>
    @yield('content-script')
</body>

</html>

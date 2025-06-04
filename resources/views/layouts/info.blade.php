@extends('layouts.main-layout')

@section('content-class')
    <style>
        .menu-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .collapsed .toggle-icon {
            transform: rotate(0deg);
        }

        .expanded .toggle-icon {
            transform: rotate(180deg);
        }

        .li {
            background-color: transparent;
            border: none;
        }

        .active-li {
            background-color: aquamarine;
            border: none;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-12 col-md-2 text-center">
        <div class="clearfix">
            <div class="">
                <img src="/images/img.jpg" alt="{{ $data->personal->fullname }}" class="img-circle profile_img">
            </div>
        </div>
        <div class="">
            <h2>{{ $data->personal->fullname }}</h2>
            <h6>{{ $data->employment->job_position_name }}</h6>
            @if ($data->employment->status == 1)
                <h6><span class="badge badge-success">Active Employee</span></h6>
            @else
                <h6><span class="badge badge-danger">Inactive Employee</span></h6>
            @endif

        </div>
        <hr>
        <ul class="list-group">
            <?php $bool = Request::is('profile/personal*') || Request::is('profile/employment*') || Request::is('profile/education*') || Request::is('profile/portofolio*'); ?>
            <li class="list-group-item menu-toggle collapsed li" data-toggle="collapse" data-target="#menu1"
                aria-expanded="false">
                <span>General</span>
                <i class="fa fa-chevron-down toggle-icon"></i>
            </li>

            <ul class="collapse list-group list-group-flush text-left {{ $bool ? 'show' : '' }}" id="menu1">
                <li class="list-group-item li"
                    style="{{ Request::is('profile/personal*') ? 'background-color:aquamarine' : '' }}"><a
                        href="/profile/personal/{{ $data->id }}">Personal</a></li>
                <li class="list-group-item li"
                    style="{{ Request::is('profile/employment*') ? 'background-color:aquamarine' : '' }}"><a
                        href="/profile/employment/{{ $data->id }}">Employment</a></li>
                <li class="list-group-item li"
                    style="{{ Request::is('profile/education*') ? 'background-color:aquamarine' : '' }}"><a
                        href="/profile/education/{{ $data->id }}">Education & Experience</a></li>
                <li class="list-group-item li"
                    style="{{ Request::is('profile/portofolio*') ? 'background-color:aquamarine' : '' }}"><a
                        href="/profile/portofolio/{{ $data->id }}">Additional Info</a></li>
            </ul>

            <li class="list-group-item menu-toggle collapsed li" data-toggle="collapse" data-target="#menu2"
                aria-expanded="false">
                <span>Payroll</span>
                <i class="fa fa-chevron-down toggle-icon li"></i>
            </li>
            <ul class="collapse list-group list-group-flush text-left" id="menu2">
                <li class="list-group-item li"><a href="">Payroll Info</a></li>
            </ul>
            <li class="list-group-item menu-toggle collapsed li" data-toggle="collapse" data-target="#menu3"
                aria-expanded="false">
                <span>Time Management</span>
                <i class="fa fa-chevron-down toggle-icon"></i>
            </li>
            <ul class="collapse list-group list-group-flush text-left" id="menu3">
                <li class="list-group-item li"><a href="">Attendance</a></li>
                <li class="list-group-item li"><a href="">Time Off</a></li>
            </ul>
        </ul>
    </div>
    <div class="col-2 col-md-10">
        @yield('content-employee')
    </div>
@endsection


@section('content-script')
    <script>
        $(document).ready(function() {
            $('#menu_toggle').click()
            $('.menu-toggle').on('click', function() {
                const targetId = $(this).data('target');
                const isExpanded = $(targetId).hasClass('show');
                $('.menu-toggle').removeClass('expanded').addClass('collapsed');
                if (!isExpanded) {
                    $(this).removeClass('collapsed').addClass('expanded');
                }
            });

            $('.collapse').on('show.bs.collapse', function() {
                $('[data-target="#' + this.id + '"]').removeClass('collapsed').addClass('expanded');
            }).on('hide.bs.collapse', function() {
                $('[data-target="#' + this.id + '"]').removeClass('expanded').addClass('collapsed');
            });
        });
    </script>
@endsection

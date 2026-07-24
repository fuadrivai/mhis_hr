@forelse ($employees as $emp)
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel employee-card">
            <div class="x_content">
                <div class="row">
                    <div class="col-md-5 col-sm-12 col-xs-12">
                        <div class="employee-profile">
                            <div class="employee-profile-checkbox">
                                <input type="checkbox" class="employee-select-item" value="{{ $emp->id }}"
                                    aria-label="Select {{ $emp->personal->fullname ?? 'employee' }}">
                            </div>
                            @if (!isset($emp->personal->avatar) || $emp->personal->avatar == '')
                                <img src="{{ asset('images/user.png') }}" class="employee-avatar"
                                    alt="{{ $emp->personal->fullname ?? '--' }}">
                            @else
                                <img src="{{ asset('storage/' . $emp->personal->avatar) }}" class="employee-avatar"
                                    alt="{{ $emp->personal->fullname ?? '--' }}">
                            @endif

                            <div>
                                <a href='/profile/personal/{{ $emp->id }}'>
                                    <h4 class="employee-name">{{ Str::upper($emp->personal->fullname ?? '--') }}
                                    </h4>
                                </a>
                                <p class="employee-subline"><i
                                        class="fa fa-envelope"></i>{{ $emp->personal->email ?? '--' }}</p>
                                <p class="employee-subline"><i
                                        class="fa fa-phone"></i>{{ $emp->personal->mobile_phone ?? '--' }}</p>
                                <p class="employee-subline">NIK : {{ $emp->employment->employee_id ?? '--' }}</p>
                                <p class="employee-subline">DOB :
                                    {{ empty($emp->personal->birth_date) ? '--' : \Carbon\Carbon::parse($emp->personal->birth_date)->format('d F Y') }}
                                </p>
                                @if (empty($emp->user))
                                    <p><span class="badge badge-danger" style="font-size: 10px;">No access MHIS
                                            HUB</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <div class="employee-meta-box">
                            <p class="employee-meta-title">Branch</p>
                            <p class="employee-meta-value">{{ $emp->employment->branch->name ?? '--' }}</p>
                            <p class="employee-meta-title">Organization</p>
                            <p class="employee-meta-value">{{ $emp->employment->organization->name ?? '--' }}</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <div class="employee-meta-box">
                            <p class="employee-meta-title">Job Position</p>
                            <p class="employee-meta-value">{{ $emp->employment->job_position->name ?? '--' }}</p>
                            <p class="employee-meta-title">Job Level</p>
                            <p class="employee-meta-value">{{ $emp->employment->job_level->name ?? '--' }}</p>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 col-xs-12">
                        <div class="employee-meta-box">
                            <p class="employee-meta-title">Join Date</p>
                            <p class="employee-meta-value">
                                {{ $emp->employment->join_date == '' ? '--' : \Carbon\Carbon::parse($emp->employment->join_date)->format('d F Y') }}
                            </p>
                            <p class="employee-meta-title">End Date</p>
                            <p class="employee-meta-value">
                                {{ $emp->employment->end_date != '' ? \Carbon\Carbon::parse($emp->employment->end_date)->format('d F Y') : '--' }}
                            </p>
                        </div>
                    </div>
                    {{-- <div class="col-md-1 col-sm-6 col-xs-12">
                            
                        </div> --}}
                    <div class="col-md-1 col-sm-6 col-xs-12">
                        <p class="employee-meta-title">Status</p>
                        <div class="employee-status">
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
                        </div>
                        <div class="employee-actions">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href='/profile/personal/{{ $emp->id }}'>Info</a>
                                    @if (empty($emp->user))
                                        <form action="/user/invite/{{ $emp->id }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                Invite to MHIS HUB
                                            </button>
                                        </form>
                                    @endif
                                    <a class="dropdown-item" href="#">Transfer</a>
                                    <a class="dropdown-item" href="#">Resign</a>
                                    <a class="dropdown-item single-deactivate" data-id="{{ $emp->id }}"
                                        href="#">Deactivate</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="employee-empty text-center">
                Tidak Ada Product
            </div>
        </div>
    @endforelse
    <div class="col-md-12">
        <div class="row employee-list-footer">
            <div class="col-md-6">
                <p class="employee-list-summary employee-page-summary-source"><b> Show {{ $page['from'] }} to
                        {{ $page['to'] }}
                        of total
                        {{ $page['total'] }}</b></p>
            </div>
            <div class="col-md-6">
                <div class="text-right">
                    {{ $employees->onEachSide(0)->links() }}
                </div>
            </div>
        </div>
    </div>

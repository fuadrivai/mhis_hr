@extends('layouts.main-layout')

@section('content-child')
    <style>
        #walk-in-registration {
            overflow: hidden;
            border: 1px solid #e3e8ed;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(39, 57, 74, .07);
        }

        #walk-in-registration .x_title {
            padding: 18px 22px;
            border-bottom: 1px solid #e8edf1;
            background: linear-gradient(110deg, #fff 60%, #f5f8fa);
        }

        #walk-in-registration .x_title h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #34495e;
            font-size: 19px;
            font-weight: 600;
        }

        #walk-in-registration .x_title h2 .fa {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 9px;
            background: #eaf3f8;
            color: #28749a;
        }

        #walk-in-registration .x_content {
            padding: 22px;
        }

        #walk-in-registration .walk-in-intro {
            margin: 0 0 18px;
            color: #73879c;
        }

        #walk-in-registration .walk-in-fields {
            padding: 18px 16px 4px;
            border: 1px solid #e8edf1;
            border-radius: 8px;
            background: #fbfcfd;
        }

        #walk-in-registration .form-group {
            margin-bottom: 17px;
        }

        #walk-in-registration .form-group label {
            margin-bottom: 6px;
            color: #434a54;
            font-weight: 600;
        }

        #walk-in-registration .form-control {
            min-height: 38px;
            border-color: #dce3e8;
            border-radius: 5px;
            box-shadow: none;
        }

        #walk-in-registration .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, .1);
        }

        #walk-in-registration .walk-in-optional {
            margin-left: 4px;
            color: #96a1ab;
            font-size: 11px;
            font-weight: 400;
        }

        #walk-in-registration .walk-in-group-toggle {
            margin: 18px 0;
        }

        #walk-in-registration .walk-in-group-toggle label {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: #434a54;
            cursor: pointer;
            font-weight: 600;
        }

        #walk-in-registration .walk-in-group-toggle input {
            margin: 2px 0 0;
        }

        #walk-in-registration .walk-in-group-section {
            margin: 15px 0;
            padding: 16px;
            border: 1px solid #e8edf1;
            border-radius: 8px;
            background: #f8fafb;
        }

        #walk-in-registration .walk-in-group-heading {
            margin-bottom: 12px;
            color: #34495e;
        }

        #walk-in-registration .walk-in-group-heading p {
            margin: 4px 0 0;
            color: #73879c;
            font-size: 12px;
        }

        #walk-in-registration .walk-in-group-member {
            margin: 10px 0;
            padding: 13px;
            border: 1px solid #e3e8ed;
            border-radius: 7px;
            background: #fff;
        }

        #walk-in-registration .walk-in-group-member-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #28749a;
        }

        #walk-in-registration .walk-in-group-member .form-group {
            margin-bottom: 12px;
        }

        #walk-in-registration .walk-in-approval-note {
            display: flex;
            gap: 10px;
            margin: 15px 0 18px;
            padding: 12px 14px;
            border: 1px solid #d6e8f1;
            border-radius: 7px;
            background: #f3f9fc;
            color: #526b7a;
        }

        #walk-in-registration .walk-in-approval-note .fa {
            margin-top: 2px;
            color: #28749a;
        }

        #walk-in-registration .walk-in-actions {
            display: flex;
            justify-content: flex-end;
        }

        #walk-in-registration .walk-in-actions .btn {
            min-height: 40px;
            padding: 8px 18px;
            border-radius: 5px;
        }

        @media (max-width: 767px) {
            #walk-in-registration .x_title,
            #walk-in-registration .x_content {
                padding: 15px;
            }

            #walk-in-registration .walk-in-fields {
                padding: 14px 12px 1px;
            }

            #walk-in-registration .walk-in-actions .btn {
                width: 100%;
            }
        }
    </style>
    <div class="col-md-12">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('visitor_qr_token'))
            <div class="alert alert-info">
                <strong>Walk-in registered: {{ session('visitor_visit_code') }}</strong>
                @if (session('visitor_group_count'))
                    <p>This request also includes {{ session('visitor_group_count') }} companion registration(s). Each person needs host approval.</p>
                @endif
                <p>One-time QR/scanner token. Save it securely; it will not be shown again.</p>
                <img src="{{ session('visitor_qr_image') }}" alt="Visitor QR code" style="width:160px;height:160px;background:#fff;padding:6px">
                <code id="new-visitor-token">{{ session('visitor_qr_token') }}</code>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="x_panel">
            <div class="x_title"><h2><i class="fa fa-id-badge"></i> Unified Visitor Desk</h2><div class="clearfix"></div></div>
            <div class="x_content">
                <p>Scan a visit token or search by visit code, name, phone, or badge. Scanning only displays the visit; it never changes its status.</p>
                <form id="visitor-search-form" class="form-inline" action="{{ route('visitor.security-desk.search') }}" method="POST" autocomplete="off">
                    <div class="form-group" style="width:70%;max-width:760px">
                        <input id="visitor-search" name="query" class="form-control input-lg" style="width:100%" placeholder="Scan QR / enter visit code, visitor name, phone, or badge" autofocus required>
                    </div>
                    <button class="btn btn-primary btn-lg" type="submit"><i class="fa fa-search"></i> Search</button>
                </form>
                <div id="visitor-search-error" class="alert alert-warning" style="display:none;margin-top:12px"></div>
                <div id="visitor-candidates" class="list-group" style="display:none;margin-top:12px"></div>
                <div id="visitor-panel" class="x_panel" style="display:none;margin-top:20px">
                    <div class="x_title"><h2 id="visit-heading"></h2><div class="clearfix"></div></div>
                    <div class="x_content">
                        <div class="row">
                            <div class="col-md-3"><strong>Visitor</strong><p id="visit-visitor"></p></div>
                            <div class="col-md-3"><strong>Company</strong><p id="visit-company"></p></div>
                            <div class="col-md-3"><strong>Category / Purpose</strong><p id="visit-category-purpose"></p></div>
                            <div class="col-md-3"><strong>Status</strong><p id="visit-status"></p></div>
                            <div class="col-md-3"><strong>Host / Branch</strong><p id="visit-host-branch"></p></div>
                            <div class="col-md-3"><strong>Visit date</strong><p id="visit-date"></p></div>
                            <div class="col-md-3"><strong>Expected arrival – departure</strong><p id="visit-time"></p></div>
                            <div class="col-md-3"><strong>Check-in / Badge</strong><p id="visit-checkin"></p></div>
                            <div class="col-md-12"><strong>Visit notes</strong><p id="visit-notes"></p></div>
                        </div>
                        <div id="check-in-section" style="display:none">
                            <hr><h4>Confirm Check-in</h4>
                            <form id="check-in-form" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3 form-group"><label>Badge number</label><input name="badge_number" class="form-control" maxlength="40"></div>
                                    <div class="col-md-3 form-group"><label>Visitor photo (optional)</label><input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
                                    <div class="col-md-6 form-group"><label>Security notes</label><input name="notes" class="form-control" maxlength="2000"></div>
                                </div>
                                <label><input type="checkbox" name="identity_verified" value="1" required> I verified this visitor's identity and authorization.</label><br>
                                <button class="btn btn-success" type="submit"><i class="fa fa-sign-in"></i> Confirm Check-in</button>
                            </form>
                        </div>
                        <div id="check-out-section" style="display:none">
                            <hr><h4>Confirm Check-out</h4>
                            <form id="check-out-form" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 form-group"><label>Badge return</label>
                                        <select name="badge_return_status" class="form-control">
                                            <option value="returned">Badge returned</option>
                                            <option value="lost">Badge lost</option>
                                            <option value="not_applicable">Not applicable</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8 form-group"><label>Check-out notes (optional)</label><input name="notes" class="form-control" maxlength="2000"></div>
                                </div>
                                <label><input type="checkbox" name="confirmed" value="1" required> I confirm this visitor has left the school premises.</label><br>
                                <button class="btn btn-warning" type="submit"><i class="fa fa-sign-out"></i> Confirm Check-out</button>
                            </form>
                            @if ($canManageVisits)
                                <hr><h4>Manual Check-out Correction</h4>
                                <form id="correct-check-out-form" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input name="reason" class="form-control" placeholder="Required correction reason" maxlength="2000" required>
                                        <span class="input-group-btn"><button class="btn btn-danger" type="submit">Record Correction</button></span>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:20px">
                    <div class="col-md-6">
                        <div class="x_panel">
                            <div class="x_title"><h2>Currently Inside</h2><div class="clearfix"></div></div>
                            <div class="x_content table-responsive">
                                <table class="table table-striped">
                                    <thead><tr><th>Visitor</th><th>Host</th><th>Badge</th><th></th></tr></thead>
                                    <tbody>
                                        @forelse ($visitsInside as $visit)
                                            <tr>
                                                <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->branch->name }}</small></td>
                                                <td>{{ $visit->host->user?->name ?? '—' }}</td>
                                                <td>{{ $visit->badge_number ?? '—' }}</td>
                                                <td><button class="btn btn-xs btn-default quick-search" data-code="{{ $visit->visit_code }}">Open</button></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">No visitors currently inside.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="x_panel">
                            <div class="x_title"><h2>Expected Today</h2><div class="clearfix"></div></div>
                            <div class="x_content table-responsive">
                                <table class="table table-striped">
                                    <thead><tr><th>Visitor</th><th>Host</th><th>Arrival</th><th></th></tr></thead>
                                    <tbody>
                                        @forelse ($expectedVisits as $visit)
                                            <tr>
                                                <td>{{ $visit->visitor->full_name }}<br><small>{{ $visit->branch->name }}</small></td>
                                                <td>{{ $visit->host->user?->name ?? '—' }}</td>
                                                <td>{{ $visit->expected_arrival_at?->format('H:i') ?? '—' }}</td>
                                                <td><button class="btn btn-xs btn-default quick-search" data-code="{{ $visit->visit_code }}">Open</button></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">No expected visitors today.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="walk-in-registration" class="x_panel">
                    <div class="x_title">
                        <h2><i class="fa fa-user-plus"></i> Register Walk-in</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <p class="walk-in-intro">Register an unscheduled visitor. The selected host must approve the walk-in before the visitor can be checked in.</p>
                        <form method="POST" action="{{ route('visitor.security-desk.walk-in') }}">
                            @csrf
                            <div class="walk-in-fields">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-full-name">Visitor name <span class="text-danger">*</span></label>
                                        <input id="walk-in-full-name" name="full_name" class="form-control"
                                            value="{{ old('full_name') }}" required maxlength="255" autocomplete="name">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-company">Company / organization</label>
                                        <input id="walk-in-company" name="company" class="form-control"
                                            value="{{ old('company') }}" maxlength="255" autocomplete="organization">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-phone">Phone</label>
                                        <input id="walk-in-phone" name="phone" class="form-control"
                                            value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-email">Email</label>
                                        <input id="walk-in-email" type="email" name="email" class="form-control"
                                            value="{{ old('email') }}" maxlength="255" autocomplete="email">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-category">Category <span class="walk-in-optional">Optional</span></label>
                                        <select id="walk-in-category" name="visitor_category_id" class="form-control select2" style="width:100%">
                                            <option value="">Select category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('visitor_category_id') == $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-purpose">Purpose <span class="walk-in-optional">Optional</span></label>
                                        <select id="walk-in-purpose" name="visitor_purpose_id" class="form-control select2" style="width:100%">
                                            <option value="">Select purpose</option>
                                            @foreach ($purposes as $purpose)
                                                <option value="{{ $purpose->id }}" @selected(old('visitor_purpose_id') == $purpose->id)>{{ $purpose->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-host">Host employee <span class="text-danger">*</span></label>
                                        <select id="walk-in-host" name="host_employee_id" class="form-control select2" style="width:100%" required>
                                            <option value="">Select host</option>
                                            @foreach ($hosts as $host)
                                                <option value="{{ $host->id }}" @selected(old('host_employee_id') == $host->id)>{{ $host->user?->name ?? 'Employee #' . $host->id }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-branch">Branch / meeting location <span class="text-danger">*</span></label>
                                        <select id="walk-in-branch" name="branch_id" class="form-control select2" style="width:100%" required>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}" @selected(old('branch_id', $branches->first()?->id) == $branch->id)>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="walk-in-departure">Expected departure <span class="walk-in-optional">Optional</span></label>
                                        <input id="walk-in-departure" type="datetime-local" name="expected_departure_at"
                                            class="form-control" value="{{ old('expected_departure_at') }}">
                                        <small class="text-muted">Leave blank if the departure time is not known.</small>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <label for="walk-in-notes">Visit notes</label>
                                        <textarea id="walk-in-notes" name="notes" class="form-control" rows="3" maxlength="3000">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="walk-in-group-toggle">
                                <label for="walk-in-bring-group">
                                    <input id="walk-in-bring-group" name="bring_group" type="checkbox" value="1" @checked(old('bring_group'))>
                                    <span>This visitor is bringing friends or colleagues</span>
                                </label>
                            </div>
                            <section id="walk-in-group-section" class="walk-in-group-section" @unless(old('bring_group')) hidden @endunless>
                                <div class="walk-in-group-heading">
                                    <div>
                                        <strong>Friends or colleagues</strong>
                                        <p>Add up to 20 people. Name is required; phone and email are optional.</p>
                                    </div>
                                </div>
                                <div id="walk-in-group-members">
                                    @foreach (old('group_members', []) as $index => $member)
                                        <article class="walk-in-group-member">
                                            <div class="walk-in-group-member-heading">
                                                <strong>Guest <span class="walk-in-group-member-number">{{ $index + 1 }}</span></strong>
                                                <button class="btn btn-default btn-xs remove-walk-in-group-member" type="button">Remove</button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 form-group">
                                                    <label for="walk-in-group-{{ $index }}-full_name">Full name <span class="text-danger">*</span></label>
                                                    <input id="walk-in-group-{{ $index }}-full_name" data-member-field="full_name"
                                                        name="group_members[{{ $index }}][full_name]" value="{{ $member['full_name'] ?? '' }}"
                                                        class="form-control" maxlength="255" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="walk-in-group-{{ $index }}-phone">Phone</label>
                                                    <input id="walk-in-group-{{ $index }}-phone" data-member-field="phone"
                                                        name="group_members[{{ $index }}][phone]" value="{{ $member['phone'] ?? '' }}"
                                                        class="form-control" maxlength="40" type="tel">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="walk-in-group-{{ $index }}-email">Email</label>
                                                    <input id="walk-in-group-{{ $index }}-email" data-member-field="email"
                                                        name="group_members[{{ $index }}][email]" value="{{ $member['email'] ?? '' }}"
                                                        class="form-control" maxlength="255" type="email">
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                                <button id="add-walk-in-group-member" class="btn btn-default btn-sm" type="button">
                                    <i class="fa fa-plus"></i> Add another person
                                </button>
                            </section>
                            <div class="walk-in-approval-note">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <span>The invitation will be sent to the selected host for approval. The visitor can be checked in after approval.</span>
                            </div>
                            <div class="walk-in-actions">
                                <button class="btn btn-primary" type="submit"><i class="fa fa-paper-plane"></i> Submit for host approval</button>
                            </div>
                        </form>
                    </div>
                </div>
                <template id="walk-in-group-member-template">
                    <article class="walk-in-group-member">
                        <div class="walk-in-group-member-heading">
                            <strong>Guest <span class="walk-in-group-member-number"></span></strong>
                            <button class="btn btn-default btn-xs remove-walk-in-group-member" type="button">Remove</button>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Full name <span class="text-danger">*</span></label>
                                <input data-member-field="full_name" class="form-control" maxlength="255" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Phone</label>
                                <input data-member-field="phone" class="form-control" maxlength="40" type="tel">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input data-member-field="email" class="form-control" maxlength="255" type="email">
                            </div>
                        </div>
                    </article>
                </template>
            </div>
        </div>
    </div>
@endsection

@section('content-script')
    <script>
        $(function () {
            var searchUrl = @json(route('visitor.security-desk.search'));
            var checkInUrl = @json(route('visitor.check-in', ['visit' => '__VISIT__']));
            var checkOutUrl = @json(route('visitor.check-out', ['visit' => '__VISIT__']));
            var correctionUrl = @json(route('visitor.correct-check-out', ['visit' => '__VISIT__']));
            var candidates = [];
            var $walkInGroupToggle = $('#walk-in-bring-group');
            var $walkInGroupSection = $('#walk-in-group-section');
            var $walkInGroupMembers = $('#walk-in-group-members');
            var walkInGroupTemplate = document.getElementById('walk-in-group-member-template');

            function renumberWalkInGroupMembers() {
                $walkInGroupMembers.find('.walk-in-group-member').each(function (index) {
                    var $row = $(this);
                    $row.find('.walk-in-group-member-number').text(index + 1);
                    $row.find('[data-member-field]').each(function () {
                        var $input = $(this);
                        var field = $input.data('member-field');
                        var id = 'walk-in-group-' + index + '-' + field;
                        $input.attr({
                            id: id,
                            name: 'group_members[' + index + '][' + field + ']'
                        });
                        $input.closest('.form-group').find('label').attr('for', id);
                    });
                });
                $('#add-walk-in-group-member').prop('disabled', $walkInGroupMembers.find('.walk-in-group-member').length >= 20);
            }

            function addWalkInGroupMember() {
                if ($walkInGroupMembers.find('.walk-in-group-member').length >= 20) {
                    return;
                }
                $walkInGroupMembers.append(walkInGroupTemplate.content.firstElementChild.cloneNode(true));
                renumberWalkInGroupMembers();
            }

            function updateWalkInGroupSection() {
                var enabled = $walkInGroupToggle.is(':checked');
                $walkInGroupSection.prop('hidden', !enabled);
                $walkInGroupSection.find('input').prop('disabled', !enabled);
                if (enabled && !$walkInGroupMembers.find('.walk-in-group-member').length) {
                    addWalkInGroupMember();
                }
            }

            function showVisit(visit) {
                $('#visitor-panel').show();
                $('#visit-heading').text(visit.visit_code);
                $('#visit-visitor').text(visit.visitor + (visit.phone ? ' · ' + visit.phone : ''));
                $('#visit-company').text(visit.company || '—');
                $('#visit-category-purpose').text(visit.category + ' · ' + visit.purpose);
                $('#visit-status').text(visit.status.replaceAll('_', ' '));
                $('#visit-host-branch').text(visit.host + ' · ' + visit.branch);
                $('#visit-date').text(visit.date);
                $('#visit-time').text((visit.arrival || '—') + ' – ' + (visit.departure || '—'));
                $('#visit-checkin').text((visit.check_in_at || 'Not checked in') + ' · Badge ' + (visit.badge_number || '—'));
                $('#visit-notes').text(visit.notes || '—');
                $('#check-in-section, #check-out-section').hide();
                if (visit.can_check_in) {
                    $('#check-in-form').attr('action', checkInUrl.replace('__VISIT__', visit.id));
                    $('#check-in-section').show();
                } else if (visit.can_check_out) {
                    $('#check-out-form').attr('action', checkOutUrl.replace('__VISIT__', visit.id));
                    $('#correct-check-out-form').attr('action', correctionUrl.replace('__VISIT__', visit.id));
                    $('#check-out-section').show();
                }
            }

            function searchVisit(term) {
                $('#visitor-search-error').hide();
                $('#visitor-candidates').hide().empty();
                $.ajax({
                    url: searchUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        query: term
                    }
                })
                    .done(function (response) {
                        candidates = response.visits || [];
                        if (candidates.length === 1) {
                            showVisit(candidates[0]);
                            return;
                        }
                        $('#visitor-panel').hide();
                        candidates.forEach(function (visit, index) {
                            var button = $('<button type="button" class="list-group-item list-group-item-action"></button>');
                            button.text(visit.visitor + ' · ' + visit.date + ' · ' + visit.branch + ' · ' + visit.host + ' · ' + visit.status);
                            button.attr('data-index', index);
                            $('#visitor-candidates').append(button);
                        });
                        $('#visitor-candidates').show();
                    })
                    .fail(function (xhr) {
                        $('#visitor-panel').hide();
                        $('#visitor-search-error').text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to search visits.').show();
                    });
            }

            $('#visitor-search-form').on('submit', function (event) {
                event.preventDefault();
                var term = $('#visitor-search').val().trim();
                if (term) searchVisit(term);
            });
            $('.quick-search').on('click', function () {
                var code = $(this).data('code');
                $('#visitor-search').val(code);
                searchVisit(code);
            });
            $('#visitor-candidates').on('click', 'button', function () {
                showVisit(candidates[Number($(this).attr('data-index'))]);
            });

            $walkInGroupToggle.on('change', updateWalkInGroupSection);
            $('#add-walk-in-group-member').on('click', addWalkInGroupMember);
            $walkInGroupMembers.on('click', '.remove-walk-in-group-member', function () {
                $(this).closest('.walk-in-group-member').remove();
                if (!$walkInGroupMembers.find('.walk-in-group-member').length) {
                    $walkInGroupToggle.prop('checked', false);
                    updateWalkInGroupSection();
                }
                renumberWalkInGroupMembers();
            });
            renumberWalkInGroupMembers();
            updateWalkInGroupSection();
        });
    </script>
@endsection

@extends('layouts.main-layout')

@section('content-child')
    <div class="col-md-12">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('visitor_qr_token'))
            <div class="alert alert-info">
                <strong>Walk-in registered: {{ session('visitor_visit_code') }}</strong>
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
                            <div class="col-md-12"><strong>Additional visitor information</strong><p id="visit-notes"></p></div>
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

                <div class="x_panel">
                    <div class="x_title"><h2>Register Walk-in</h2><div class="clearfix"></div></div>
                    <div class="x_content">
                        <form method="POST" action="{{ route('visitor.security-desk.walk-in') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Visitor name *</label><input name="full_name" class="form-control" required maxlength="255"></div>
                                <div class="col-md-4 form-group"><label>Phone</label><input name="phone" class="form-control" maxlength="40"></div>
                                <div class="col-md-4 form-group"><label>Email</label><input type="email" name="email" class="form-control" maxlength="255"></div>
                                <div class="col-md-4 form-group"><label>Company</label><input name="company" class="form-control" maxlength="255"></div>
                                <div class="col-md-4 form-group"><label>Category *</label><select name="visitor_category_id" class="form-control" required><option value="">Select</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                                <div class="col-md-4 form-group"><label>Purpose *</label><select name="visitor_purpose_id" class="form-control" required><option value="">Select</option>@foreach ($purposes as $purpose)<option value="{{ $purpose->id }}">{{ $purpose->name }}</option>@endforeach</select></div>
                                <div class="col-md-4 form-group"><label>Host employee *</label><select name="host_employee_id" class="form-control" required><option value="">Select</option>@foreach ($hosts as $host)<option value="{{ $host->id }}">{{ $host->user?->name ?? 'Employee #' . $host->id }}</option>@endforeach</select></div>
                                <div class="col-md-4 form-group"><label>Branch (meeting location) *</label><select name="branch_id" class="form-control" required>@foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
                                <div class="col-md-4 form-group"><label>Expected departure *</label><input type="datetime-local" name="expected_departure_at" class="form-control" value="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required></div>
                                <div class="col-md-6 form-group"><label>Visitor photo</label><input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
                                <div class="col-md-6 form-group"><label>Additional visitor information</label><input name="visitor_notes" class="form-control" maxlength="2000"></div>
                                <div class="col-md-12 form-group"><label>Security notes</label><textarea name="notes" class="form-control" rows="2" maxlength="3000"></textarea></div>
                            </div>
                            <p class="text-muted">Registration and check-in remain separate confirmations; verify the visitor before checking in.</p>
                            <button class="btn btn-primary" type="submit"><i class="fa fa-user-plus"></i> Register Walk-in</button>
                        </form>
                    </div>
                </div>
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
                $('#visit-notes').text(visit.visitor_notes || '—');
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
        });
    </script>
@endsection

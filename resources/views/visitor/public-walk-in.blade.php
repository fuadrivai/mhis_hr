<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#800000">
    <link href="{{ asset('plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <title>Walk-in Visitor Registration | MHIS HUB</title>
    <style>
        :root {
            color-scheme: light;
            --maroon: #800000;
            --maroon-dark: #5f0000;
            --gold: #d4af37;
            --ink: #292524;
            --muted: #716b67;
            --line: #eee8e2;
            --page: #f7f4f0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: Arial, "Helvetica Neue", sans-serif;
            line-height: 1.55;
        }

        .top-accent { height: 6px; background: linear-gradient(90deg, var(--maroon), var(--gold), var(--maroon)); }
        .page-shell { width: min(100% - 28px, 760px); margin: 34px auto; }
        .brand { display: flex; justify-content: center; margin-bottom: 20px; padding: 10px 20px; border-radius: 12px; background: var(--maroon); }
        .brand-logo { display: block; width: min(100%, 300px); height: auto; object-fit: contain; }
        .card { overflow: hidden; border: 1px solid #e8e1da; border-radius: 16px; background: #fff; box-shadow: 0 18px 50px rgba(55, 37, 24, .08); }
        .card-heading { padding: 28px 34px 22px; border-bottom: 1px solid var(--line); }
        .eyebrow { margin: 0 0 7px; color: #947a36; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--maroon); font-size: clamp(24px, 5vw, 32px); line-height: 1.2; }
        .intro { margin: 9px 0 0; color: var(--muted); }
        .card-body { padding: 26px 34px 32px; }
        .alert { margin-bottom: 18px; padding: 12px 15px; border: 1px solid #f1c5c5; border-radius: 8px; background: #fff5f5; color: #8f1d1d; }
        .alert ul { margin: 0; padding-left: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px 16px; }
        .form-group { min-width: 0; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; color: #45403d; font-size: 13px; font-weight: 700; }
        .required { color: var(--maroon); }
        input, select, textarea {
            display: block;
            width: 100%;
            min-height: 45px;
            padding: 10px 12px;
            border: 1px solid #dcd5cf;
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font: inherit;
        }
        textarea { min-height: 92px; resize: vertical; }
        input:focus, select:focus, textarea:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128, 0, 0, .1); }
        .field-help { display: block; margin-top: 5px; color: var(--muted); font-size: 11px; }
        .optional { color: var(--muted); font-size: 11px; font-weight: 400; }
        .group-toggle { margin: 20px 0; }
        .group-toggle label { display: flex; align-items: flex-start; gap: 10px; margin: 0; cursor: pointer; }
        .group-toggle input { width: 18px; min-height: 18px; flex: 0 0 18px; margin: 1px 0 0; accent-color: var(--maroon); }
        .group-toggle span { font-weight: 600; }
        .group-section { margin: 18px 0; padding: 18px; border: 1px solid var(--line); border-radius: 10px; background: #fcfaf8; }
        .group-section-heading { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .group-section-heading h2 { margin: 0; color: var(--maroon); font-size: 16px; }
        .group-section-heading p { margin: 4px 0 0; color: var(--muted); font-size: 12px; }
        .group-member { margin-top: 12px; padding: 14px; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
        .group-member-heading { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; color: var(--maroon); font-size: 13px; font-weight: 700; }
        .text-button { padding: 7px 10px; border: 1px solid #dcd5cf; border-radius: 7px; background: #fff; color: var(--ink); cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; }
        .text-button:hover { border-color: var(--maroon); color: var(--maroon); }
        .group-member-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .group-member-fields .form-group:first-child { grid-column: 1 / -1; }
        .group-member-fields input { min-height: 42px; }
        .group-add-button { margin-top: 12px; }
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: 45px; border-color: #dcd5cf; border-radius: 8px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 43px; padding-left: 12px; color: var(--ink); }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 43px; }
        .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128, 0, 0, .1); }
        .approval-note { display: flex; align-items: flex-start; gap: 10px; margin: 20px 0; padding: 13px 15px; border: 1px solid #dceaf0; border-radius: 8px; background: #f5fafc; color: #536a7b; font-size: 13px; }
        .approval-note svg { flex: 0 0 18px; margin-top: 1px; color: #1f6f8b; }
        .submit-button { display: inline-flex; min-height: 48px; align-items: center; justify-content: center; padding: 0 24px; border: 0; border-radius: 8px; background: var(--maroon); color: #fff; cursor: pointer; font: inherit; font-weight: 700; transition: background .15s, transform .15s; }
        .submit-button:hover { transform: translateY(-1px); background: var(--maroon-dark); }
        .footer { margin: 20px 0 0; color: var(--muted); font-size: 12px; text-align: center; }

        @media (max-width: 560px) {
            .page-shell { width: min(100% - 20px, 760px); margin: 20px auto; }
            .card-heading { padding: 23px 20px 19px; }
            .card-body { padding: 21px 20px 24px; }
            .form-grid { grid-template-columns: 1fr; gap: 14px; }
            .form-group.full { grid-column: auto; }
            .group-section { padding: 14px; }
            .group-member-fields { grid-template-columns: 1fr; }
            .group-member-fields .form-group:first-child { grid-column: auto; }
            .submit-button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="top-accent"></div>
    <main class="page-shell">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/logo mh menyamping putih-01-01.png') }}" alt="Mutiara Harapan Islamic School">
        </div>

        <section class="card" aria-labelledby="registration-title">
            <header class="card-heading">
                <p class="eyebrow">Visitor Management</p>
                <h1 id="registration-title">Walk-in visitor registration</h1>
                <p class="intro">Complete this form to register your visit. Your selected host will review and approve the request.</p>
            </header>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert" role="alert">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('visitor.public-walk-in.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name">Full name <span class="required">*</span></label>
                            <input id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="255" autocomplete="name" required>
                        </div>
                        <div class="form-group">
                            <label for="company">Company / organization</label>
                            <input id="company" name="company" value="{{ old('company') }}" maxlength="255" autocomplete="organization">
                        </div>
                        <div class="form-group">
                            <label for="email">Email address <span class="required">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required>
                            <small class="field-help">Your visit code and QR will be emailed after host approval.</small>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone number <span class="required">*</span></label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" required>
                        </div>
                        <div class="form-group">
                            <label for="branch_id">Branch / meeting location <span class="required">*</span></label>
                            <select id="branch_id" name="branch_id" required>
                                <option value="">Select branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="host_employee_id">Host employee <span class="required">*</span></label>
                            <select id="host_employee_id" name="host_employee_id" class="select2" required>
                                <option value="">Select host</option>
                                @foreach ($hosts as $host)
                                    <option value="{{ $host->id }}" @selected(old('host_employee_id') == $host->id)>
                                        {{ $host->personal?->fullname ?? $host->user?->name ?? 'Employee #' . $host->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="visitor_category_id">Visitor category <span class="optional">Optional</span></label>
                            <select id="visitor_category_id" name="visitor_category_id" class="select2">
                                <option value="">Select category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('visitor_category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="visitor_purpose_id">Visit purpose <span class="optional">Optional</span></label>
                            <select id="visitor_purpose_id" name="visitor_purpose_id" class="select2">
                                <option value="">Select purpose</option>
                                @foreach ($purposes as $purpose)
                                    <option value="{{ $purpose->id }}" @selected(old('visitor_purpose_id') == $purpose->id)>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="expected_departure_at">Expected departure <span class="optional">Optional</span></label>
                            <input id="expected_departure_at" type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at') }}">
                            <small class="field-help">If known, enter a departure time later today.</small>
                        </div>
                        <div class="form-group full">
                            <label for="notes">Visit notes</label>
                            <textarea id="notes" name="notes" maxlength="3000">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="group-toggle">
                        <label for="bring_group">
                            <input id="bring_group" name="bring_group" type="checkbox" value="1" @checked(old('bring_group'))>
                            <span>I am bringing friends or colleagues</span>
                        </label>
                    </div>
                    <section id="group-members-section" class="group-section" @unless(old('bring_group')) hidden @endunless>
                        <div class="group-section-heading">
                            <div>
                                <h2>Friends or colleagues</h2>
                                <p>Add up to 20 people. Name is required; phone and email are optional.</p>
                            </div>
                        </div>
                        <div id="group-members-container">
                            @foreach (old('group_members', []) as $index => $member)
                                <article class="group-member">
                                    <div class="group-member-heading">
                                        <span>Guest <span class="group-member-number">{{ $index + 1 }}</span></span>
                                        <button class="text-button remove-group-member" type="button">Remove</button>
                                    </div>
                                    <div class="group-member-fields">
                                        <div class="form-group">
                                            <label for="group-member-{{ $index }}-full_name">Full name <span class="required">*</span></label>
                                            <input id="group-member-{{ $index }}-full_name" data-member-field="full_name"
                                                name="group_members[{{ $index }}][full_name]" value="{{ $member['full_name'] ?? '' }}"
                                                maxlength="255" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="group-member-{{ $index }}-phone">Phone number</label>
                                            <input id="group-member-{{ $index }}-phone" data-member-field="phone"
                                                name="group_members[{{ $index }}][phone]" value="{{ $member['phone'] ?? '' }}"
                                                maxlength="40" type="tel">
                                        </div>
                                        <div class="form-group">
                                            <label for="group-member-{{ $index }}-email">Email address</label>
                                            <input id="group-member-{{ $index }}-email" data-member-field="email"
                                                name="group_members[{{ $index }}][email]" value="{{ $member['email'] ?? '' }}"
                                                maxlength="255" type="email">
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <button id="add-group-member" class="text-button group-add-button" type="button">+ Add another person</button>
                    </section>

                    <div class="approval-note">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 11v5m0-8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Your registration will be sent to the selected host for approval. We will email your visit code and QR after approval.</span>
                    </div>

                    <button class="submit-button" type="submit">Submit for host approval</button>
                </form>
            </div>
        </section>
        <p class="footer">Mutiara Harapan Islamic School · Secure visitor registration</p>
    </main>
    <template id="group-member-template">
        <article class="group-member">
            <div class="group-member-heading">
                <span>Guest <span class="group-member-number"></span></span>
                <button class="text-button remove-group-member" type="button">Remove</button>
            </div>
            <div class="group-member-fields">
                <div class="form-group">
                    <label>Full name <span class="required">*</span></label>
                    <input data-member-field="full_name" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label>Phone number</label>
                    <input data-member-field="phone" maxlength="40" type="tel">
                </div>
                <div class="form-group">
                    <label>Email address</label>
                    <input data-member-field="email" maxlength="255" type="email">
                </div>
            </div>
        </article>
    </template>
    <script src="{{ asset('plugins/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2').select2({ width: '100%' });

            var $toggle = $('#bring_group');
            var $section = $('#group-members-section');
            var $members = $('#group-members-container');
            var template = document.getElementById('group-member-template');

            function renumberMembers() {
                $members.find('.group-member').each(function(index) {
                    var $row = $(this);
                    $row.find('.group-member-number').text(index + 1);
                    $row.find('[data-member-field]').each(function() {
                        var $input = $(this);
                        var field = $input.data('member-field');
                        var id = 'group-member-' + index + '-' + field;
                        $input.attr({ id: id, name: 'group_members[' + index + '][' + field + ']' });
                        $input.closest('.form-group').find('label').attr('for', id);
                    });
                });
                $('#add-group-member').prop('disabled', $members.find('.group-member').length >= 20);
            }

            function updateGroupSection() {
                var enabled = $toggle.is(':checked');
                $section.prop('hidden', !enabled);
                $section.find('input').prop('disabled', !enabled);
                if (enabled && !$members.find('.group-member').length) {
                    addMember();
                }
            }

            function addMember() {
                if ($members.find('.group-member').length >= 20) {
                    return;
                }
                $members.append(template.content.firstElementChild.cloneNode(true));
                renumberMembers();
            }

            $toggle.on('change', updateGroupSection);
            $('#add-group-member').on('click', addMember);
            $members.on('click', '.remove-group-member', function() {
                $(this).closest('.group-member').remove();
                if (!$members.find('.group-member').length) {
                    $toggle.prop('checked', false);
                    updateGroupSection();
                }
                renumberMembers();
            });

            renumberMembers();
            updateGroupSection();
        });
    </script>
</body>
</html>

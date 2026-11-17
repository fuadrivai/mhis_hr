<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#800000">
    <title>Complete Visitor Registration | MHIS HUB</title>
    <style>
        :root {
            color-scheme: light;
            --maroon: #800000;
            --maroon-dark: #5f0000;
            --gold: #d4af37;
            --ink: #292524;
            --muted: #716b67;
            --line: #eee8e2;
            --surface: #fff;
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
        .page-shell { width: min(100% - 32px, 760px); margin: 44px auto; }
        .brand { display: flex; justify-content: center; margin: 0 0 22px; padding: 10px 20px; border-radius: 12px; background: var(--maroon); }
        .brand-logo { display: block; width: min(100%, 320px); height: auto; object-fit: contain; }
        .card { overflow: hidden; border: 1px solid #e8e1da; border-radius: 16px; background: var(--surface); box-shadow: 0 18px 50px rgba(55, 37, 24, .08); }
        .card-heading { padding: 30px 36px 24px; border-bottom: 1px solid var(--line); }
        .eyebrow { margin: 0 0 8px; color: #947a36; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--maroon); font-size: clamp(24px, 5vw, 32px); line-height: 1.2; }
        .intro { margin: 10px 0 0; color: var(--muted); }
        .card-body { padding: 28px 36px 34px; }
        .event-card { margin-bottom: 28px; padding: 20px; border: 1px solid var(--line); border-left: 4px solid var(--gold); border-radius: 10px; background: #fcfaf8; }
        .event-title { margin: 0 0 15px; color: var(--maroon); font-size: 18px; font-weight: 700; }
        .event-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 22px; }
        .event-item { min-width: 0; }
        .event-label { display: block; margin-bottom: 3px; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .event-value { color: var(--ink); font-size: 14px; font-weight: 600; overflow-wrap: anywhere; }
        .section-title { margin: 0 0 4px; font-size: 18px; }
        .section-help { margin: 0 0 20px; color: var(--muted); font-size: 14px; }
        .alert { margin-bottom: 18px; padding: 12px 15px; border: 1px solid #f1c5c5; border-radius: 8px; background: #fff5f5; color: #8f1d1d; }
        .alert ul { margin: 0; padding-left: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 16px; }
        .form-group { min-width: 0; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 7px; color: #45403d; font-size: 13px; font-weight: 700; }
        .required { color: var(--maroon); }
        .group-toggle { margin: 20px 0; }
        .group-toggle label { display: flex; align-items: flex-start; gap: 10px; margin: 0; cursor: pointer; }
        .group-toggle input { width: 18px; min-height: 18px; flex: 0 0 18px; margin: 1px 0 0; accent-color: var(--maroon); }
        .group-toggle span { font-weight: 600; }
        .group-section { margin: 18px 0; padding: 18px; border: 1px solid var(--line); border-radius: 10px; background: #fcfaf8; }
        .group-section-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .group-section-heading h3 { margin: 0; color: var(--maroon); font-size: 16px; }
        .group-section-heading p { margin: 4px 0 0; color: var(--muted); font-size: 12px; }
        .group-member { margin-top: 12px; padding: 14px; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
        .group-member-heading { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
        .group-member-heading strong { color: var(--maroon); font-size: 13px; }
        .text-button { padding: 7px 10px; border: 1px solid #dcd5cf; border-radius: 7px; background: #fff; color: var(--ink); cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; }
        .text-button:hover { border-color: var(--maroon); color: var(--maroon); }
        .group-member-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .group-member-fields .form-group:first-child { grid-column: 1 / -1; }
        .group-member-fields input { min-height: 42px; }
        .group-add-button { margin-top: 12px; }
        input {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            border: 1px solid #dcd5cf;
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font: inherit;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128, 0, 0, .1); }
        .contact-note { margin: 18px 0; padding: 12px 14px; border-radius: 8px; background: #f8f5ef; color: #655b4c; font-size: 13px; }
        .submit-button {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            padding: 0 24px;
            border: 0;
            border-radius: 8px;
            background: var(--maroon);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            transition: background .15s, transform .15s;
        }
        .submit-button:hover { transform: translateY(-1px); background: var(--maroon-dark); }
        .confirmation-backdrop { position: fixed; inset: 0; z-index: 20; display: flex; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; background: rgba(32, 24, 21, .58); }
        .confirmation-dialog { width: min(100%, 560px); max-height: min(90vh, 760px); overflow-y: auto; border-radius: 14px; background: #fff; box-shadow: 0 24px 70px rgba(0, 0, 0, .24); }
        .confirmation-heading { padding: 22px 24px; border-bottom: 1px solid var(--line); }
        .confirmation-heading h2 { margin: 0; color: var(--maroon); font-size: 21px; }
        .confirmation-heading p { margin: 6px 0 0; color: var(--muted); font-size: 13px; }
        .confirmation-content { padding: 20px 24px; }
        .review-section + .review-section { margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--line); }
        .review-section h3 { margin: 0 0 10px; color: var(--maroon); font-size: 14px; }
        .review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 16px; }
        .review-item { min-width: 0; }
        .review-item span { display: block; color: var(--muted); font-size: 11px; }
        .review-item strong { display: block; margin-top: 2px; overflow-wrap: anywhere; font-size: 13px; }
        .review-member { margin-top: 8px; padding: 10px 12px; border-radius: 7px; background: #fcfaf8; font-size: 13px; }
        .review-member strong { display: block; color: var(--ink); }
        .review-member span { display: block; margin-top: 3px; color: var(--muted); font-size: 12px; }
        .confirmation-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid var(--line); }
        .confirmation-actions .submit-button { min-height: 44px; }
        .confirmation-actions .submit-button:disabled { cursor: wait; opacity: .8; transform: none; }
        .submit-loading { display: inline-flex; align-items: center; gap: 9px; }
        .submit-spinner { width: 17px; height: 17px; border: 2px solid rgba(255, 255, 255, .45); border-top-color: #fff; border-radius: 50%; animation: submit-spin .7s linear infinite; }
        @keyframes submit-spin { to { transform: rotate(360deg); } }
        .secondary-button { min-height: 44px; padding: 0 16px; border: 1px solid #dcd5cf; border-radius: 8px; background: #fff; color: var(--ink); cursor: pointer; font: inherit; font-weight: 700; }
        .secondary-button:hover { border-color: var(--maroon); color: var(--maroon); }
        [hidden] { display: none !important; }
        body.modal-open { overflow: hidden; }
        .footer { margin: 22px 0 0; color: var(--muted); font-size: 12px; text-align: center; }

        @media (max-width: 560px) {
            .page-shell { width: min(100% - 20px, 760px); margin: 22px auto; }
            .card-heading { padding: 24px 20px 20px; }
            .card-body { padding: 22px 20px 26px; }
            .event-card { padding: 16px; }
            .event-grid, .form-grid { grid-template-columns: 1fr; gap: 14px; }
            .form-group.full { grid-column: auto; }
            .submit-button { width: 100%; }
            .group-section { padding: 14px; }
            .group-member-fields, .review-grid { grid-template-columns: 1fr; }
            .group-member-fields .form-group:first-child { grid-column: auto; }
            .confirmation-backdrop { align-items: flex-end; padding: 10px; }
            .confirmation-dialog { max-height: 88vh; border-radius: 14px 14px 8px 8px; }
            .confirmation-heading, .confirmation-content { padding: 18px; }
            .confirmation-actions { flex-direction: column-reverse; padding: 14px 18px; }
            .confirmation-actions button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="top-accent"></div>
    <main class="page-shell">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/logo mh menyamping putih-01-01.png') }}" alt="Mutiara Harapan Islamic School">
        </div>

        <section class="card">
            <header class="card-heading">
                <p class="eyebrow">Visitor Management</p>
                <h1>Complete your registration</h1>
                <p class="intro">Your host has prepared this invitation. Confirm your contact details to complete your visitor registration.</p>
            </header>

            <div class="card-body">
                <section class="event-card" aria-label="Invitation details">
                    <h2 class="event-title">{{ $batch->name }}</h2>
                    <div class="event-grid">
                        <div class="event-item">
                            <span class="event-label">Visit date</span>
                            <span class="event-value">{{ $batch->visit_date->format('l, d M Y') }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Time</span>
                            <span class="event-value">{{ $batch->expected_arrival_at->format('H:i') }} - {{ $batch->expected_departure_at->format('H:i') }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Purpose</span>
                            <span class="event-value">{{ $batch->purpose->name }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Meeting location</span>
                            <span class="event-value">{{ $batch->branch->name }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Host</span>
                            <span class="event-value">{{ $batch->host->user?->name ?? 'School contact' }}</span>
                        </div>
                        <div class="event-item">
                            <span class="event-label">Registration closes</span>
                            <span class="event-value">{{ $batch->expires_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </section>

                @if ($errors->any())
                    <div class="alert" role="alert">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <h2 class="section-title">Your contact information</h2>
                <p class="section-help">This link can only be used once. Please provide at least a phone number or email address.</p>
                <form id="public-registration-form" method="POST" action="{{ route('visitor.public-registration.submit', ['token' => $token]) }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="full_name">Full name <span class="required">*</span></label>
                            <input id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="255" autocomplete="name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone number</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email">
                        </div>
                        <div class="form-group full">
                            <label for="company">Company / organization</label>
                            <input id="company" name="company" value="{{ old('company') }}" maxlength="255" autocomplete="organization">
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
                                <h3>Friends or colleagues</h3>
                                <p>Add up to 20 people. Each person needs a name; phone and email are optional.</p>
                            </div>
                        </div>
                        <div id="group-members-container">
                            @foreach (old('group_members', []) as $index => $member)
                                <article class="group-member">
                                    <div class="group-member-heading">
                                        <strong>Guest <span class="group-member-number">{{ $index + 1 }}</span></strong>
                                        <button class="text-button remove-group-member" type="button">Remove</button>
                                    </div>
                                    <div class="group-member-fields">
                                        <div class="form-group">
                                            <label for="group-member-{{ $index }}-full_name">Full name <span class="required">*</span></label>
                                            <input id="group-member-{{ $index }}-full_name" data-member-field="full_name"
                                                name="group_members[{{ $index }}][full_name]"
                                                value="{{ $member['full_name'] ?? '' }}" maxlength="255" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="group-member-{{ $index }}-phone">Phone number</label>
                                            <input id="group-member-{{ $index }}-phone" data-member-field="phone"
                                                name="group_members[{{ $index }}][phone]"
                                                value="{{ $member['phone'] ?? '' }}" maxlength="40" type="tel">
                                        </div>
                                        <div class="form-group">
                                            <label for="group-member-{{ $index }}-email">Email address</label>
                                            <input id="group-member-{{ $index }}-email" data-member-field="email"
                                                name="group_members[{{ $index }}][email]"
                                                value="{{ $member['email'] ?? '' }}" maxlength="255" type="email">
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <button id="add-group-member" class="text-button group-add-button" type="button">+ Add another person</button>
                    </section>
                    <div class="contact-note">Your personal information will be shared with the host for this visit.</div>
                    <button class="submit-button" type="submit">Submit registration</button>
                </form>

                <template id="group-member-template">
                    <article class="group-member">
                        <div class="group-member-heading">
                            <strong>Guest <span class="group-member-number"></span></strong>
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

                <div id="registration-confirmation" class="confirmation-backdrop" hidden>
                    <section class="confirmation-dialog" role="dialog" aria-modal="true"
                        aria-labelledby="confirmation-title" aria-describedby="confirmation-description">
                        <header class="confirmation-heading">
                            <h2 id="confirmation-title">Confirm registration</h2>
                            <p id="confirmation-description">Review the information before submitting. Choose Edit to make changes.</p>
                        </header>
                        <div id="registration-review" class="confirmation-content"></div>
                        <div class="confirmation-actions">
                            <button id="edit-registration" class="secondary-button" type="button">Edit information</button>
                            <button id="confirm-registration" class="submit-button" type="button">
                                <span class="confirm-label">Confirm and submit</span>
                                <span class="submit-loading" hidden aria-live="polite">
                                    <span class="submit-spinner" aria-hidden="true"></span>
                                    Submitting...
                                </span>
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </section>
        <p class="footer">Mutiara Harapan Islamic School · Secure visitor registration</p>
    </main>
    <script>
        (function() {
            var form = document.getElementById('public-registration-form');
            var groupToggle = document.getElementById('bring_group');
            var groupSection = document.getElementById('group-members-section');
            var groupContainer = document.getElementById('group-members-container');
            var groupTemplate = document.getElementById('group-member-template');
            var confirmation = document.getElementById('registration-confirmation');
            var review = document.getElementById('registration-review');
            var confirmed = false;
            var isSubmitting = false;

            function renumberGroupMembers() {
                Array.prototype.forEach.call(groupContainer.querySelectorAll('.group-member'), function(row, index) {
                    row.querySelector('.group-member-number').textContent = String(index + 1);
                    Array.prototype.forEach.call(row.querySelectorAll('[data-member-field]'), function(input) {
                        var field = input.getAttribute('data-member-field');
                        var id = 'group-member-' + index + '-' + field;
                        input.id = id;
                        input.name = 'group_members[' + index + '][' + field + ']';
                        input.closest('.form-group').querySelector('label').htmlFor = id;
                    });
                });
                var addButton = document.getElementById('add-group-member');
                addButton.disabled = groupContainer.querySelectorAll('.group-member').length >= 20;
            }

            function addGroupMember() {
                if (groupContainer.querySelectorAll('.group-member').length >= 20) {
                    return;
                }

                groupContainer.appendChild(groupTemplate.content.firstElementChild.cloneNode(true));
                renumberGroupMembers();
            }

            function updateGroupSection() {
                var enabled = groupToggle.checked;
                groupSection.hidden = !enabled;
                Array.prototype.forEach.call(groupSection.querySelectorAll('input'), function(input) {
                    input.disabled = !enabled;
                });
                if (enabled && groupContainer.querySelectorAll('.group-member').length === 0) {
                    addGroupMember();
                }
            }

            function addReviewItem(grid, label, value) {
                if (!value) {
                    return;
                }
                var item = document.createElement('div');
                item.className = 'review-item';
                var itemLabel = document.createElement('span');
                itemLabel.textContent = label;
                var itemValue = document.createElement('strong');
                itemValue.textContent = value;
                item.appendChild(itemLabel);
                item.appendChild(itemValue);
                grid.appendChild(item);
            }

            function renderReview() {
                review.textContent = '';
                var primarySection = document.createElement('section');
                primarySection.className = 'review-section';
                var primaryTitle = document.createElement('h3');
                primaryTitle.textContent = 'Your information';
                var primaryGrid = document.createElement('div');
                primaryGrid.className = 'review-grid';
                primarySection.appendChild(primaryTitle);
                primarySection.appendChild(primaryGrid);
                addReviewItem(primaryGrid, 'Name', form.elements.namedItem('full_name').value.trim());
                addReviewItem(primaryGrid, 'Phone', form.elements.namedItem('phone').value.trim());
                addReviewItem(primaryGrid, 'Email', form.elements.namedItem('email').value.trim());
                addReviewItem(primaryGrid, 'Company / organization', form.elements.namedItem('company').value.trim());
                review.appendChild(primarySection);

                if (!groupToggle.checked) {
                    return;
                }

                var groupSectionReview = document.createElement('section');
                groupSectionReview.className = 'review-section';
                var groupTitle = document.createElement('h3');
                groupTitle.textContent = 'Friends or colleagues';
                groupSectionReview.appendChild(groupTitle);
                Array.prototype.forEach.call(groupContainer.querySelectorAll('.group-member'), function(row, index) {
                    var member = document.createElement('div');
                    member.className = 'review-member';
                    var name = document.createElement('strong');
                    name.textContent = (index + 1) + '. ' + row.querySelector('[data-member-field="full_name"]').value.trim();
                    var contact = document.createElement('span');
                    var phone = row.querySelector('[data-member-field="phone"]').value.trim();
                    var email = row.querySelector('[data-member-field="email"]').value.trim();
                    contact.textContent = [phone, email].filter(Boolean).join(' · ') || 'No contact details provided';
                    member.appendChild(name);
                    member.appendChild(contact);
                    groupSectionReview.appendChild(member);
                });
                review.appendChild(groupSectionReview);
            }

            function closeConfirmation() {
                confirmation.hidden = true;
                document.body.classList.remove('modal-open');
            }

            groupToggle.addEventListener('change', updateGroupSection);
            document.getElementById('add-group-member').addEventListener('click', addGroupMember);
            groupContainer.addEventListener('click', function(event) {
                var button = event.target.closest('.remove-group-member');
                if (button) {
                    button.closest('.group-member').remove();
                    if (groupContainer.querySelectorAll('.group-member').length === 0) {
                        groupToggle.checked = false;
                        updateGroupSection();
                    }
                    renumberGroupMembers();
                }
            });

            form.addEventListener('submit', function(event) {
                if (confirmed) {
                    if (isSubmitting) {
                        event.preventDefault();
                        return;
                    }
                    isSubmitting = true;
                    var confirmButton = document.getElementById('confirm-registration');
                    confirmButton.disabled = true;
                    confirmButton.setAttribute('aria-busy', 'true');
                    confirmButton.querySelector('.confirm-label').hidden = true;
                    confirmButton.querySelector('.submit-loading').hidden = false;
                    document.getElementById('edit-registration').disabled = true;
                    return;
                }
                event.preventDefault();
                renderReview();
                confirmation.hidden = false;
                document.body.classList.add('modal-open');
                document.getElementById('edit-registration').focus();
            });

            document.getElementById('edit-registration').addEventListener('click', closeConfirmation);
            document.getElementById('confirm-registration').addEventListener('click', function() {
                if (isSubmitting) {
                    return;
                }
                confirmed = true;
                form.requestSubmit();
            });
            confirmation.addEventListener('click', function(event) {
                if (event.target === confirmation) {
                    closeConfirmation();
                }
            });
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !confirmation.hidden) {
                    closeConfirmation();
                }
            });

            renumberGroupMembers();
            updateGroupSection();
        })();
    </script>
</body>
</html>

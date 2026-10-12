@extends('layouts.main-layout')

@section('content-class')
<style>
    .chat-container { display: flex; height: 70vh; border: 1px solid #ddd; border-radius: 5px; }
    .contact-list-container { width: 30%; border-right: 1px solid #ddd; display: flex; flex-direction: column; background: #fff; }
    .contact-filter { padding: 10px; border-bottom: 1px solid #ddd; }
    .contact-list { flex: 1; overflow-y: auto; }
    .contact-item { padding: 12px 15px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; align-items: center; position: relative; }
    .contact-item:hover, .contact-item.active { background: #f4f4f4; }
    .contact-item img { width: 40px; height: 40px; border-radius: 50%; margin-right: 12px; }
    .contact-info { flex: 1; min-width: 0; }
    .contact-name { font-weight: bold; font-size: 14px; display: flex; align-items: center; justify-content: space-between; }
    .contact-number { font-size: 12px; color: #777; margin-bottom: 2px; }
    .last-msg-preview { font-size: 11px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .badge-account-bintaro { background-color: #28a745; color: #fff; }
    .badge-account-bangka { background-color: #007bff; color: #fff; }
    .badge-account-semarang { background-color: #dc3545; color: #fff; }
    
    .chat-area { width: 70%; display: flex; flex-direction: column; background: #f9f9f9; }
    .chat-header { padding: 15px; background: #fff; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; font-weight: bold; }
    .chat-messages { flex: 1; padding: 15px; overflow-y: auto; }
    .message { max-width: 70%; margin-bottom: 15px; padding: 10px 15px; border-radius: 15px; font-size: 14px; }
    .message.from-me { background: #dcf8c6; margin-left: auto; border-bottom-right-radius: 0; }
    .message.from-them { background: #fff; margin-right: auto; border-bottom-left-radius: 0; border: 1px solid #eee; }
    .message-time { font-size: 10px; color: #999; text-align: right; margin-top: 5px; }
    
    .chat-input { padding: 15px; background: #fff; border-top: 1px solid #ddd; display: flex; align-items: flex-end; }
    .chat-input textarea { flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; resize: none; font-family: inherit; min-height: 44px; max-height: 120px; line-height: 20px; overflow-y: auto; }
    .chat-input button { margin-left: 10px; padding: 10px 20px; background: #26b99a; color: white; border: none; border-radius: 20px; cursor: pointer; height: 44px; }
    .chat-input button:hover { background: #1f9a80; }
</style>
@endsection

@section('content-child')
<div class="col-md-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>WhatsApp Chat</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="chat-container">
                <div class="contact-list-container">
                    <div class="contact-filter">
                        <select id="accountFilter" class="form-control mb-2">
                            <option value="">All Sender Numbers</option>
                            @if(isset($accounts))
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->number }}">{{ $acc->name }} (+{{ $acc->number }})</option>
                                @endforeach
                            @endif
                        </select>
                        <select id="tagFilter" class="form-control select2 mb-2" multiple data-placeholder="Filter by Tags">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group">
                            <input type="text" id="contactSearchInput" class="form-control form-control-sm" placeholder="Search contacts...">
                            <div class="input-group-append">
                                <button id="refreshContactsBtn" class="btn btn-sm btn-outline-secondary" type="button" title="Refresh contacts">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="contact-list" id="contactList">
                        <div style="padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i> Loading contacts...</div>
                    </div>
                </div>
                <div class="chat-area" id="chatArea" style="display: none;">
                    <div class="chat-header">
                        <div id="chatHeaderTitle">Contact Name</div>
                        <div>
                            <button class="btn btn-sm btn-info" id="assignTagBtn"><i class="fa fa-tags"></i> Tags</button>
                        </div>
                    </div>
                    <div class="chat-messages" id="chatMessages"></div>
                    <div id="mediaPreviewContainer" style="display:none; padding: 10px 15px; background: #fff; border-top: 1px solid #ddd; border-bottom: 1px solid #eee;">
                        <span id="mediaPreviewName" style="font-size: 12px; font-weight: bold; background: #e0e0e0; padding: 5px 10px; border-radius: 10px;"></span>
                        <button id="clearMediaBtn" style="background: none; border: none; color: red; font-size: 14px; cursor: pointer; margin-left: 5px;"><i class="fa fa-times-circle"></i></button>
                    </div>
                    <div class="chat-input">
                        <input type="file" id="mediaInput" style="display:none" accept=".jpg,.jpeg,.png,.mp4,.mp3,.pdf">
                        <button id="attachBtn" style="background: transparent; color: #777; border: none; padding: 10px; font-size: 20px; margin-right: 10px; height: 44px;" title="Attach file"><i class="fa fa-paperclip"></i></button>
                        <textarea id="messageInput" placeholder="Type a message..." rows="1"></textarea>
                        <button id="sendMessageBtn"><i class="fa fa-paper-plane"></i> Send</button>
                    </div>
                </div>
                <div class="chat-area" id="noChatArea" style="justify-content: center; align-items: center; font-size: 18px; color: #999;">
                    Select a contact to start chatting
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Tag Modal -->
<div class="modal fade" id="assignTagModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assign Tags</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="assignTagForm">
                    <input type="hidden" id="tagContactNumber" name="number">
                    <div class="form-group">
                        <label>Select Tags</label>
                        <select name="tags[]" id="contactTagsSelect" class="form-control select2" multiple style="width: 100%;">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveTagsBtn">Save Tags</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('content-script')
<script>
    let currentContact = null;
    let currentAccount = null;
    let allContacts = [];
    let accountsData = [];
    let limitPerAccount = 15; // Default 15 chat for every whatsapp account

    function getAccountBadgeClass(accountName) {
        if (!accountName) return 'badge-secondary';
        let name = accountName.toLowerCase();
        if (name.includes('bintaro')) return 'badge-account-bintaro';
        if (name.includes('bangka')) return 'badge-account-bangka';
        if (name.includes('semarang')) return 'badge-account-semarang';
        return 'badge-info';
    }

    function renderContacts() {
        let filterTags = $('#tagFilter').val() || [];
        let filterAccount = $('#accountFilter').val();
        let searchQuery = ($('#contactSearchInput').val() || '').trim().toLowerCase();

        // Step 1: Base filter (Tags and Search Query)
        let filteredPool = allContacts.filter(c => {
            // Tag filter
            if (filterTags.length > 0) {
                if (!c.tags || c.tags.length === 0) return false;
                if (!c.tags.some(t => filterTags.includes(t.id.toString()))) return false;
            }

            // Search filter (number or name)
            if (searchQuery) {
                let nameMatch = c.name && c.name.toLowerCase().includes(searchQuery);
                let numMatch = c.number && c.number.toString().includes(searchQuery);
                if (!nameMatch && !numMatch) return false;
            }

            return true;
        });

        // Step 2: Account limitation logic (15 chats on default for every WhatsApp account)
        let displayedContacts = [];
        let hasMore = false;
        let totalCount = 0;

        if (filterAccount) {
            // Specific WhatsApp account selected (e.g. WA Bangka)
            let accountContacts = filteredPool.filter(c => c.account_number === filterAccount);
            totalCount = accountContacts.length;
            displayedContacts = accountContacts.slice(0, limitPerAccount);
            if (accountContacts.length > limitPerAccount) {
                hasMore = true;
            }
        } else {
            // "All Sender Numbers" selected:
            // For every distinct account, take up to limitPerAccount contacts!
            let accountGroups = {};
            filteredPool.forEach(c => {
                let acc = c.account_number || 'unknown';
                if (!accountGroups[acc]) {
                    accountGroups[acc] = [];
                }
                accountGroups[acc].push(c);
            });

            // For EACH account, take top `limitPerAccount`
            Object.keys(accountGroups).forEach(accNumber => {
                let group = accountGroups[accNumber];
                totalCount += group.length;
                let slice = group.slice(0, limitPerAccount);
                displayedContacts.push(...slice);
                if (group.length > limitPerAccount) {
                    hasMore = true;
                }
            });

            // Sort merged displayed contacts by last_msg_timestamp DESC
            displayedContacts.sort((a, b) => {
                let tsA = a.last_msg_timestamp ?? 0;
                let tsB = b.last_msg_timestamp ?? 0;
                return tsB - tsA;
            });
        }

        // Step 3: Render contacts HTML
        let html = '';
        if (displayedContacts.length === 0) {
            html = '<div style="padding:20px; text-align:center; color:#999;">No contacts found</div>';
        } else {
            displayedContacts.forEach(function(contact) {
                let redDotHtml = contact.unread ? `<span style="display:inline-block; width:10px; height:10px; background:red; border-radius:50%; margin-left:5px;" title="Unread messages"></span>` : '';
                let tagsHtml = '';
                let tagIds = [];
                if (contact.tags && contact.tags.length > 0) {
                    contact.tags.forEach(t => {
                        tagsHtml += `<span style="font-size:10px; background:${t.color_code}; color:white; padding:2px 5px; border-radius:3px; margin-right:3px;">${t.name}</span>`;
                        tagIds.push(t.id);
                    });
                }
                
                let displayName = contact.name !== 'null' && contact.name ? contact.name : contact.number;
                let badgeClass = getAccountBadgeClass(contact.account_name);
                
                let lastMsgPreview = '';
                if (contact.last_message && contact.last_message.message) {
                    let previewText = contact.last_message.message.substring(0, 35);
                    if (contact.last_message.message.length > 35) previewText += '...';
                    lastMsgPreview = `<div class="last-msg-preview">${previewText}</div>`;
                }

                html += `
                    <div class="contact-item" data-number="${contact.number}" data-account="${contact.account_number}" data-name="${displayName}" data-tags="${tagIds.join(',')}">
                        <img src="${contact.picture}" alt="DP" onerror="this.src='/images/user.png'">
                        <div class="contact-info">
                            <div class="contact-name">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;" title="${displayName}">${displayName}</span>
                                ${redDotHtml}
                            </div>
                            <div class="contact-number">+${contact.number} <span class="badge ${badgeClass}" style="font-size:9px; float:right;">Via: ${contact.account_name || contact.account_number}</span></div>
                            ${lastMsgPreview}
                            <div>${tagsHtml}</div>
                        </div>
                    </div>
                `;
            });

            // Load More button
            if (hasMore) {
                let loadMoreText = filterAccount 
                    ? `Load More (Showing ${displayedContacts.length} of ${totalCount})` 
                    : `Load More (+15 per account · Showing ${displayedContacts.length} of ${totalCount})`;
                html += `
                    <div style="padding: 12px; text-align: center; border-top: 1px solid #eee; background: #fafafa;">
                        <button id="loadMoreBtn" class="btn btn-sm btn-outline-primary btn-block" style="border-radius: 15px; font-size: 12px; font-weight: 600;">
                            <i class="fa fa-arrow-down"></i> ${loadMoreText}
                        </button>
                    </div>
                `;
            } else {
                html += `
                    <div style="padding: 10px; text-align: center; color: #aaa; font-size: 11px; border-top: 1px solid #eee;">
                        Showing all ${displayedContacts.length} chats
                    </div>
                `;
            }
        }

        $('#contactList').html(html);

        if (currentContact && currentAccount) {
            $(`.contact-item[data-number="${currentContact}"][data-account="${currentAccount}"]`).addClass('active');
        }
    }

    let isFetchingContacts = false;
    let isFetchingMessages = false;
    let lastMessageSignature = '';

    function isScrolledNearBottom() {
        let el = document.getElementById('chatMessages');
        if (!el) return true;
        return el.scrollHeight - el.scrollTop - el.clientHeight < 150;
    }

    function fetchContacts(forceRefresh = false, isSilent = false) {
        if (isFetchingContacts) return;
        isFetchingContacts = true;

        if (allContacts.length === 0 && !isSilent) {
            $('#contactList').html('<div style="padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i> Loading contacts...</div>');
        }
        
        let refreshBtn = $('#refreshContactsBtn');
        if (!isSilent) {
            refreshBtn.find('i').addClass('fa-spin');
        }
        
        $.get('/whatsapp/api/contacts', { refresh: forceRefresh ? 1 : 0 }, function(res) {
            isFetchingContacts = false;
            refreshBtn.find('i').removeClass('fa-spin');
            if (res.status && res.data) {
                let scrollPos = $('#contactList').scrollTop();
                allContacts = res.data;
                accountsData = res.accounts || [];
                
                // If Account Filter options were not pre-rendered, populate them
                if ($('#accountFilter option').length <= 1) {
                    let accHtml = '<option value="">All Sender Numbers</option>';
                    let seenAccounts = [];
                    allContacts.forEach(c => {
                        if (c.account_number && !seenAccounts.includes(c.account_number)) {
                            seenAccounts.push(c.account_number);
                            accHtml += `<option value="${c.account_number}">${c.account_name || c.account_number} (+${c.account_number})</option>`;
                        }
                    });
                    let curAccVal = $('#accountFilter').val();
                    $('#accountFilter').html(accHtml);
                    if (curAccVal) $('#accountFilter').val(curAccVal);
                }

                renderContacts();
                if (isSilent && scrollPos > 0) {
                    $('#contactList').scrollTop(scrollPos);
                }
            } else if (!isSilent) {
                $('#contactList').html('<div style="padding:20px; color:red;">' + (res.message || 'Failed to load contacts') + '</div>');
            }
        }).fail(function() {
            isFetchingContacts = false;
            refreshBtn.find('i').removeClass('fa-spin');
            if (!isSilent) {
                $('#contactList').html('<div style="padding:20px; color:red;">Server error while loading contacts</div>');
            }
        });
    }

    function fetchMessages(number, name, tags, account, isSilent = false) {
        if (isFetchingMessages && isSilent) return;
        
        currentContact = number;
        currentAccount = account;
        $('#noChatArea').hide();
        $('#chatArea').show();
        $('#chatHeaderTitle').text(name + ' (via +' + account + ')');
        
        // Prepare tags modal
        $('#tagContactNumber').val(number);
        let tagArray = tags ? tags.split(',') : [];
        $('#contactTagsSelect').val(tagArray).trigger('change');
        
        if (!isSilent) {
            $('#chatMessages').html('<div style="text-align:center;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
            lastMessageSignature = '';
        }
        
        // Mark as read in backend
        $.post('/whatsapp/api/mark-read', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            number: number
        });
        
        isFetchingMessages = true;
        $.get('/whatsapp/api/messages?contact_number=' + number + '&account_number=' + account, function(res) {
            isFetchingMessages = false;
            if (res.status && res.data) {
                // Ensure chat hasn't switched to another contact in the meantime
                if (currentContact !== number || currentAccount !== account) return;

                let messages = res.data;
                let currentSignature = messages.length + '_' + (messages.length > 0 ? (messages[messages.length - 1].id || messages[messages.length - 1].timestamp || '') : '');

                if (!isSilent || currentSignature !== lastMessageSignature) {
                    let nearBottom = isScrolledNearBottom();
                    let html = '';
                    messages.forEach(function(msg) {
                        let isMe = msg.from_me === "true" || msg.from_me === true;
                        let escapedMsg = (msg.message || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
                        let employeeHtml = '';
                        if (isMe && msg.employee_name) {
                            employeeHtml = `<div style="font-size: 10px; color: #555; text-align: right; margin-top: 2px;"><i class="fa fa-user"></i> ${msg.employee_name}</div>`;
                        }
                        html += `
                            <div class="message ${isMe ? 'from-me' : 'from-them'}">
                                <div>${escapedMsg.replace(/\n/g, '<br>')}</div>
                                <div class="message-time">${msg.waktu || ''} ${msg.tanggal || ''}</div>
                                ${employeeHtml}
                            </div>
                        `;
                    });
                    $('#chatMessages').html(html);
                    lastMessageSignature = currentSignature;
                    
                    if (!isSilent || nearBottom) {
                        scrollToBottom();
                    }
                }
                
                // Clear the unread dot locally
                let contactObj = allContacts.find(c => c.number == number && c.account_number == account);
                if (contactObj && contactObj.unread) {
                    contactObj.unread = false;
                    renderContacts();
                }
            } else if (!isSilent) {
                $('#chatMessages').html('<div style="text-align:center;">No messages</div>');
            }
        }).fail(function() {
            isFetchingMessages = false;
            if (!isSilent) {
                $('#chatMessages').html('<div style="text-align:center; color:red;">Server error while loading messages</div>');
            }
        });
    }

    function scrollToBottom() {
        let el = document.getElementById('chatMessages');
        if (el) el.scrollTop = el.scrollHeight;
    }

    function sendMessage() {
        if (!currentContact) return;
        let text = $('#messageInput').val().trim();
        let fileInput = document.getElementById('mediaInput');
        let file = fileInput.files[0];
        
        if (!text && !file) return;
        
        let btn = $('#sendMessageBtn');
        btn.prop('disabled', true).text('Sending...');
        
        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('number', currentContact);
        formData.append('account_number', currentAccount);
        if (text) formData.append('message', text);
        if (file) formData.append('media', file);
        
        $.ajax({
            url: '/whatsapp/api/send',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send');
                if (res.status) {
                    $('#messageInput').val('').css('height', '44px');
                    $('#mediaInput').val('');
                    $('#mediaPreviewContainer').hide();
                    
                    fetchMessages(currentContact, $('#chatHeaderTitle').text().split(' (')[0], $('#contactTagsSelect').val() ? $('#contactTagsSelect').val().join(',') : '', currentAccount);
                    
                    // Update local timestamp / unread
                    let contactObj = allContacts.find(c => c.number == currentContact && c.account_number == currentAccount);
                    if (contactObj) {
                        contactObj.unread = false;
                        renderContacts();
                    }
                } else {
                    alert('Failed to send message: ' + (res.message || res.msg || 'Unknown error'));
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send');
                alert('Server error while sending');
            }
        });
    }

    $(document).ready(function() {
        $('.select2').select2();
        fetchContacts();
        
        $('#tagFilter').change(function() {
            renderContacts();
        });
        
        $('#accountFilter').change(function() {
            limitPerAccount = 15; // Reset limit back to 15 on account change
            renderContacts();
        });

        $('#contactSearchInput').on('input', function() {
            renderContacts();
        });

        $('#refreshContactsBtn').click(function() {
            fetchContacts(true);
        });

        $(document).on('click', '#loadMoreBtn', function() {
            limitPerAccount += 15;
            renderContacts();
        });
        
        $(document).on('click', '.contact-item', function() {
            $('.contact-item').removeClass('active');
            $(this).addClass('active');
            fetchMessages($(this).data('number'), $(this).data('name'), $(this).data('tags').toString(), $(this).data('account'));
        });
        
        $('#sendMessageBtn').click(sendMessage);
        
        $('#attachBtn').click(function() {
            $('#mediaInput').click();
        });
        
        $('#mediaInput').change(function() {
            let file = this.files[0];
            if (file) {
                $('#mediaPreviewName').text(file.name);
                $('#mediaPreviewContainer').show();
            } else {
                $('#mediaPreviewContainer').hide();
            }
        });
        
        $('#clearMediaBtn').click(function() {
            $('#mediaInput').val('');
            $('#mediaPreviewContainer').hide();
        });

        $('#messageInput').keydown(function(e) {
            if (e.which == 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        $('#messageInput').on('input', function() {
            this.style.height = '44px';
            if (this.scrollHeight > 44) {
                this.style.height = (this.scrollHeight) + 'px';
            }
        });
        
        $('#assignTagBtn').click(function() {
            $('#assignTagModal').modal('show');
        });
        
        $('#saveTagsBtn').click(function() {
            let btn = $(this);
            btn.prop('disabled', true).text('Saving...');
            
            $.post('/whatsapp/api/contacts/tags', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                number: $('#tagContactNumber').val(),
                tags: $('#contactTagsSelect').val()
            }, function(res) {
                btn.prop('disabled', false).text('Save Tags');
                if (res.status) {
                    $('#assignTagModal').modal('hide');
                    fetchContacts(true); // Reload contacts to get updated tags
                } else {
                    alert('Failed to assign tags: ' + res.message);
                }
            }).fail(function() {
                btn.prop('disabled', false).text('Save Tags');
                alert('Server error while saving tags');
            });
        });

        // 1. Silent live-poll for open chat conversation every 5 seconds
        setInterval(function() {
            if (document.visibilityState === 'visible' && currentContact && currentAccount && !isFetchingMessages) {
                let currentName = $('#chatHeaderTitle').text().split(' (')[0];
                let currentTags = $('#contactTagsSelect').val() ? $('#contactTagsSelect').val().join(',') : '';
                fetchMessages(currentContact, currentName, currentTags, currentAccount, true);
            }
        }, 5000);

        // 2. Silent background refresh for contact list every 30 seconds
        setInterval(function() {
            if (document.visibilityState === 'visible' && !isFetchingContacts) {
                fetchContacts(true, true);
            }
        }, 30000);
    });
</script>
@endsection

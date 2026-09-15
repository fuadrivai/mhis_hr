@extends('layouts.main-layout')

@section('content-class')
<style>
    .chat-container { display: flex; height: 70vh; border: 1px solid #ddd; border-radius: 5px; }
    .contact-list-container { width: 30%; border-right: 1px solid #ddd; display: flex; flex-direction: column; background: #fff; }
    .contact-filter { padding: 10px; border-bottom: 1px solid #ddd; }
    .contact-list { flex: 1; overflow-y: auto; }
    .contact-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; align-items: center; position: relative; }
    .contact-item:hover, .contact-item.active { background: #f4f4f4; }
    .contact-item img { width: 40px; height: 40px; border-radius: 50%; margin-right: 15px; }
    .contact-info { flex: 1; }
    .contact-name { font-weight: bold; font-size: 14px; display: flex; align-items: center; justify-content: space-between; }
    .contact-number { font-size: 12px; color: #777; margin-bottom: 3px; }
    
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
                        <select id="tagFilter" class="form-control">
                            <option value="">All Tags</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="contact-list" id="contactList">
                        <div style="padding:20px; text-align:center;">Loading contacts...</div>
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
    let allContacts = [];

    function renderContacts() {
        let filterTag = $('#tagFilter').val();
        let html = '';
        
        let filteredContacts = allContacts.filter(c => {
            if (!filterTag) return true;
            if (!c.tags) return false;
            return c.tags.some(t => t.id == filterTag);
        });
        
        filteredContacts.forEach(function(contact) {
            if (contact.check_unread) {
                contact.check_unread = false;
                $.get('/whatsapp/api/check-unread', { number: contact.number, ts: contact.last_msg_timestamp }, function(res) {
                    if (res.status) {
                        contact.unread = res.unread;
                        renderContacts();
                    }
                });
            }

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
            
            html += `
                <div class="contact-item" data-number="${contact.number}" data-name="${displayName}" data-tags="${tagIds.join(',')}">
                    <img src="${contact.picture}" alt="DP" onerror="this.src='/images/user.png'">
                    <div class="contact-info">
                        <div class="contact-name">
                            <span>${displayName}</span>
                            ${redDotHtml}
                        </div>
                        <div class="contact-number">+${contact.number}</div>
                        <div>${tagsHtml}</div>
                    </div>
                </div>
            `;
        });
        
        if (filteredContacts.length === 0) {
            html = '<div style="padding:20px; text-align:center; color:#999;">No contacts found</div>';
        }
        
        $('#contactList').html(html);
        
        if (currentContact) {
            $(`.contact-item[data-number="${currentContact}"]`).addClass('active');
        }
    }

    function fetchContacts() {
        $.get('/whatsapp/api/contacts', function(res) {
            if (res.status && res.data) {
                allContacts = res.data;
                renderContacts();
            } else {
                $('#contactList').html('<div style="padding:20px; color:red;">' + (res.message || 'Failed to load contacts') + '</div>');
            }
        }).fail(function() {
            $('#contactList').html('<div style="padding:20px; color:red;">Server error while loading contacts</div>');
        });
    }

    function fetchMessages(number, name, tags) {
        currentContact = number;
        $('#noChatArea').hide();
        $('#chatArea').show();
        $('#chatHeaderTitle').text(name);
        
        // Prepare tags modal
        $('#tagContactNumber').val(number);
        let tagArray = tags ? tags.split(',') : [];
        $('#contactTagsSelect').val(tagArray).trigger('change');
        
        $('#chatMessages').html('<div style="text-align:center;">Loading...</div>');
        
        // Mark as read in backend
        $.post('/whatsapp/api/mark-read', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            number: number
        });
        
        $.get('/whatsapp/api/messages?contact_number=' + number, function(res) {
            if (res.status && res.data) {
                let html = '';
                res.data.forEach(function(msg) {
                    let isMe = msg.from_me === "true";
                    let escapedMsg = msg.message.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
                    let employeeHtml = '';
                    if (isMe && msg.employee_name) {
                        employeeHtml = `<div style="font-size: 10px; color: #555; text-align: right; margin-top: 2px;"><i class="fa fa-user"></i> ${msg.employee_name}</div>`;
                    }
                    html += `
                        <div class="message ${isMe ? 'from-me' : 'from-them'}">
                            <div>${escapedMsg.replace(/\n/g, '<br>')}</div>
                            <div class="message-time">${msg.waktu} ${msg.tanggal}</div>
                            ${employeeHtml}
                        </div>
                    `;
                });
                $('#chatMessages').html(html);
                scrollToBottom();
                
                // Clear the unread dot locally since we've opened the chat
                let contactObj = allContacts.find(c => c.number == number);
                if (contactObj && contactObj.unread) {
                    contactObj.unread = false;
                    renderContacts();
                }
            } else {
                $('#chatMessages').html('<div style="text-align:center;">No messages</div>');
            }
        }).fail(function() {
            $('#chatMessages').html('<div style="text-align:center; color:red;">Server error while loading messages</div>');
        });
    }

    function scrollToBottom() {
        let el = document.getElementById('chatMessages');
        el.scrollTop = el.scrollHeight;
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
                    
                    fetchMessages(currentContact, $('#chatHeaderTitle').text(), $('#contactTagsSelect').val() ? $('#contactTagsSelect').val().join(',') : '');
                    
                    // Update local timestamp to clear red dot for future fetch if any
                    let contactObj = allContacts.find(c => c.number == currentContact);
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
        fetchContacts();
        
        $('#tagFilter').change(function() {
            renderContacts();
        });
        
        $(document).on('click', '.contact-item', function() {
            $('.contact-item').removeClass('active');
            $(this).addClass('active');
            fetchMessages($(this).data('number'), $(this).data('name'), $(this).data('tags').toString());
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
                    fetchContacts(); // Reload contacts to get updated tags
                } else {
                    alert('Failed to assign tags: ' + res.message);
                }
            }).fail(function() {
                btn.prop('disabled', false).text('Save Tags');
                alert('Server error while saving tags');
            });
        });
    });
</script>
@endsection

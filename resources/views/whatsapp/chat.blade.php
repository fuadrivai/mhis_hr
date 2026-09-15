@extends('layouts.main-layout')

@section('content-class')
<style>
    .chat-container { display: flex; height: 70vh; border: 1px solid #ddd; border-radius: 5px; }
    .contact-list { width: 30%; border-right: 1px solid #ddd; overflow-y: auto; background: #fff; }
    .contact-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; align-items: center; }
    .contact-item:hover, .contact-item.active { background: #f4f4f4; }
    .contact-item img { width: 40px; height: 40px; border-radius: 50%; margin-right: 15px; }
    .contact-info { flex: 1; }
    .contact-name { font-weight: bold; font-size: 14px; }
    .contact-number { font-size: 12px; color: #777; }
    
    .chat-area { width: 70%; display: flex; flex-direction: column; background: #f9f9f9; }
    .chat-header { padding: 15px; background: #fff; border-bottom: 1px solid #ddd; font-weight: bold; }
    .chat-messages { flex: 1; padding: 15px; overflow-y: auto; }
    .message { max-width: 70%; margin-bottom: 15px; padding: 10px 15px; border-radius: 15px; font-size: 14px; }
    .message.from-me { background: #dcf8c6; margin-left: auto; border-bottom-right-radius: 0; }
    .message.from-them { background: #fff; margin-right: auto; border-bottom-left-radius: 0; border: 1px solid #eee; }
    .message-time { font-size: 10px; color: #999; text-align: right; margin-top: 5px; }
    
    .chat-input { padding: 15px; background: #fff; border-top: 1px solid #ddd; display: flex; }
    .chat-input input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; outline: none; }
    .chat-input button { margin-left: 10px; padding: 10px 20px; background: #26b99a; color: white; border: none; border-radius: 20px; cursor: pointer; }
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
                <div class="contact-list" id="contactList">
                    <div style="padding:20px; text-align:center;">Loading contacts...</div>
                </div>
                <div class="chat-area" id="chatArea" style="display: none;">
                    <div class="chat-header" id="chatHeader">Contact Name</div>
                    <div class="chat-messages" id="chatMessages"></div>
                    <div class="chat-input">
                        <input type="text" id="messageInput" placeholder="Type a message...">
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
@endsection

@section('content-script')
<script>
    let currentContact = null;

    function fetchContacts() {
        $.get('/whatsapp/api/contacts', function(res) {
            if (res.status && res.data) {
                let html = '';
                res.data.forEach(function(contact) {
                    html += `
                        <div class="contact-item" data-number="${contact.number}" data-name="${contact.name !== 'null' ? contact.name : contact.number}">
                            <img src="${contact.picture}" alt="DP" onerror="this.src='/images/user.png'">
                            <div class="contact-info">
                                <div class="contact-name">${contact.name !== 'null' ? contact.name : contact.number}</div>
                                <div class="contact-number">+${contact.number}</div>
                            </div>
                        </div>
                    `;
                });
                $('#contactList').html(html);
            } else {
                $('#contactList').html('<div style="padding:20px; color:red;">' + (res.message || 'Failed to load contacts') + '</div>');
            }
        }).fail(function() {
            $('#contactList').html('<div style="padding:20px; color:red;">Server error while loading contacts</div>');
        });
    }

    function fetchMessages(number, name) {
        currentContact = number;
        $('#noChatArea').hide();
        $('#chatArea').show();
        $('#chatHeader').text(name);
        $('#chatMessages').html('<div style="text-align:center;">Loading...</div>');
        
        $.get('/whatsapp/api/messages?contact_number=' + number, function(res) {
            if (res.status && res.data) {
                let html = '';
                // The API seems to return oldest first or newest first, let's just loop
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
        if (!text) return;
        
        let btn = $('#sendMessageBtn');
        btn.prop('disabled', true).text('Sending...');
        
        $.post('/whatsapp/api/send', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            number: currentContact,
            message: text
        }, function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send');
            if (res.status) {
                $('#messageInput').val('');
                fetchMessages(currentContact, $('#chatHeader').text());
            } else {
                alert('Failed to send message: ' + res.message);
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send');
            alert('Server error while sending');
        });
    }

    $(document).ready(function() {
        fetchContacts();
        
        $(document).on('click', '.contact-item', function() {
            $('.contact-item').removeClass('active');
            $(this).addClass('active');
            fetchMessages($(this).data('number'), $(this).data('name'));
        });
        
        $('#sendMessageBtn').click(sendMessage);
        $('#messageInput').keypress(function(e) {
            if (e.which == 13) sendMessage();
        });
    });
</script>
@endsection

@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">

    <!-- Left Column: Send Message Form & Device Controls -->
    <div class="col-xl-7 col-lg-7">
        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar--sm bg--success-transparent text--success rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="lab la-android fs-4"></i>
                    </span>
                    <div>
                        <h6 class="card-title mb-0 fw-bold">Send Message from Android Device</h6>
                        <small class="text-muted">Direct WhatsApp dispatch from your paired mobile device</small>
                    </div>
                </div>
                <a href="{{ route('admin.device.sender.logs') }}" class="btn btn-outline--primary btn-sm">
                    <i class="las la-history me-1"></i> Message Logs
                </a>
            </div>

            <div class="card-body p-4">
                <form id="deviceSenderForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Select Android Device / Account -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between">
                            <span><i class="lab la-whatsapp text--success me-1"></i> Sender Device / WhatsApp Account <span class="text--danger">*</span></span>
                            <a href="{{ route('admin.account.listing.create') }}" class="text--primary text-xs text-decoration-none">
                                <i class="las la-plus-circle me-1"></i>Connect New Phone
                            </a>
                        </label>
                        <select name="session_id" id="session_id" class="form-select form-select-sm" required>
                            @forelse($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}" {{ $loop->first ? 'selected' : '' }}>
                                    📱 {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Active Session' }}) - {{ $acc->status == 1 ? '🟢 Connected' : '🔴 Offline' }}
                                </option>
                            @empty
                                <option value="" disabled selected>⚠️ No Connected Android WhatsApp Account Found</option>
                            @endforelse
                        </select>
                        @if($connectedAccounts->isEmpty())
                            <div class="alert alert-warning py-2 px-3 mt-2 small mb-0">
                                <i class="las la-exclamation-triangle me-1"></i> No connected WhatsApp device. <a href="{{ route('admin.account.listing.create') }}" class="fw-bold">Scan QR Code to connect your Android phone</a>.
                            </div>
                        @endif
                    </div>

                    <!-- Recipients Input & Picker -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold small text-dark mb-0">
                                <i class="las la-user-friends text--primary me-1"></i> Recipient(s) <span class="text--danger">*</span>
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-outline--info py-0 px-2" data-bs-toggle="modal" data-bs-target="#contactPickerModal">
                                    <i class="las la-address-book me-1"></i>Pick Contact
                                </button>
                                <button type="button" class="btn btn-xs btn-outline--secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#groupPickerModal">
                                    <i class="las la-users me-1"></i>Pick Group
                                </button>
                            </div>
                        </div>
                        <textarea name="receiver" id="receiver" class="form-control form-control-sm" rows="2" placeholder="e.g. 923001234567, 923219876543, or 120363xxx@g.us (Separate multiple recipients with commas or newlines)" required>{{ $primaryAccount && $primaryAccount->phone_number ? $primaryAccount->phone_number : '' }}</textarea>
                        <small class="text-muted text-xs">
                            <i class="las la-info-circle me-1"></i> Enter phone number with country code without + or spaces. Supports single or multiple comma-separated numbers.
                        </small>
                    </div>

                    <!-- Message Template Quick Picker -->
                    @if($templates->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between">
                            <span><i class="las la-bookmark text--warning me-1"></i> Quick Message Template</span>
                        </label>
                        <select id="template_picker" class="form-select form-select-sm">
                            <option value="">-- Select a saved template to autofill --</option>
                            @foreach($templates as $tmpl)
                                <option value="{{ $tmpl->message }}">{{ $tmpl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Message Body & Dynamic Tags -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold small text-dark mb-0">
                                <i class="las la-comment-alt text--info me-1"></i> Message Body
                            </label>
                            <span class="badge bg-light text-muted text-xs" id="charCounter">0 chars | 0 words</span>
                        </div>

                        <!-- Quick Tag & Emoji Bar -->
                        <div class="bg-light p-2 rounded border mb-2 d-flex flex-wrap align-items-center justify-content-between gap-1">
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline--secondary insert-tag" data-tag="{name}">+ {name}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary insert-tag" data-tag="{phone}">+ {phone}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary insert-tag" data-tag="{date}">+ {date}</button>
                                <button type="button" class="btn btn-xs btn-outline--secondary insert-tag" data-tag="{time}">+ {time}</button>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="🔥">🔥</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="✅">✅</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="🚀">🚀</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="💰">💰</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="✨">✨</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="📞">📞</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="🎬">🎬</button>
                                <button type="button" class="btn btn-xs btn-light insert-emoji" data-emoji="🤖">🤖</button>
                            </div>
                        </div>

                        <textarea name="message" id="messageText" class="form-control" rows="5" placeholder="Type your message here... Support Urdu, English, Emojis, and line breaks.">🔥 Hello {name}!

Thank you for contacting us. Your request is being processed. 

✅ Instant Delivery
📞 WhatsApp Support: +923216793596

Have a great day! ✨</textarea>
                    </div>

                    <!-- Media Attachment Options (Accordion / Toggle) -->
                    <div class="border rounded p-3 mb-4 bg-light">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold small text-dark mb-0">
                                <i class="las la-paperclip text--primary me-1"></i> Attach Media File (Optional)
                            </label>
                            <span class="text-muted text-xs">Images, Videos, PDFs, Audio</span>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-muted">Upload File from Computer</label>
                                <input type="file" name="file" id="fileUpload" class="form-control form-control-sm" accept="image/*,video/*,audio/*,.pdf,.docx,.xlsx,.zip">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-muted">Or Enter Media URL</label>
                                <input type="url" name="media_url" id="mediaUrl" class="form-control form-control-sm" placeholder="https://example.com/image.png">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-muted">Media Type</label>
                                <select name="media_type" id="mediaType" class="form-select form-select-sm">
                                    <option value="text">Auto-detect from file/URL</option>
                                    <option value="image">Image (JPG, PNG, WebP)</option>
                                    <option value="video">Video (MP4)</option>
                                    <option value="document">Document (PDF, Word, Excel)</option>
                                    <option value="audio">Audio / Voice Note</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-muted">Document Name (Optional)</label>
                                <input type="text" name="filename" id="filename" class="form-control form-control-sm" placeholder="e.g. Invoice_2026.pdf">
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Actions -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                        <button type="button" id="btnClearForm" class="btn btn-outline--secondary btn-sm px-3">
                            <i class="las la-undo me-1"></i> Reset
                        </button>
                        <button type="submit" id="btnSendMessage" class="btn btn--success px-4 fw-bold">
                            <i class="las la-paper-plane me-1"></i> Send Message Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Smartphone Live Preview & Activity Summary -->
    <div class="col-xl-5 col-lg-5">
        
        <!-- Smartphone Mockup Preview -->
        <div class="card border shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between">
                <span class="fw-bold small text-dark"><i class="las la-mobile text--primary me-1"></i> Live Android Preview</span>
                <span class="badge bg-success text-xs">WhatsApp View</span>
            </div>

            <div class="card-body p-3 bg-light d-flex justify-content-center">
                
                <!-- Mockup Phone Container -->
                <div class="phone-mockup border shadow" style="width: 100%; max-width: 340px; background: #e5ddd5; border-radius: 28px; overflow: hidden; border: 8px solid #2d3748;">
                    
                    <!-- Phone Top Status Bar -->
                    <div class="bg-dark text-white px-3 py-1 d-flex align-items-center justify-content-between text-xs" style="font-size: 10px;">
                        <span>12:00</span>
                        <div class="d-flex align-items-center gap-1">
                            <i class="las la-wifi"></i>
                            <i class="las la-battery-full"></i>
                        </div>
                    </div>

                    <!-- WhatsApp Header -->
                    <div class="p-2 d-flex align-items-center justify-content-between text-white" style="background-color: #075e54;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="las la-arrow-left text-xs"></i>
                            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                                <i class="las la-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-white text-xs fw-bold" id="previewSenderName">Customer</h6>
                                <small class="text-white text-opacity-75" style="font-size: 9px;">online</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <i class="las la-phone"></i>
                            <i class="las la-ellipsis-v"></i>
                        </div>
                    </div>

                    <!-- WhatsApp Chat Area -->
                    <div class="p-3" style="min-height: 280px; max-height: 380px; overflow-y: auto; background-image: radial-gradient(#d4c8be 1px, transparent 1px); background-size: 16px 16px;">
                        
                        <!-- Incoming dummy bubble -->
                        <div class="d-flex mb-2">
                            <div class="p-2 rounded bg-white shadow-sm text-dark" style="max-width: 80%; border-radius: 8px 8px 8px 0; font-size: 11px;">
                                <span>Hello, can you share the details?</span>
                                <div class="text-end text-muted" style="font-size: 8px;">11:58 AM</div>
                            </div>
                        </div>

                        <!-- Outgoing Live Message Bubble -->
                        <div class="d-flex justify-content-end mb-2">
                            <div class="p-2 shadow-sm text-dark" style="background-color: #dcf8c6; max-width: 85%; border-radius: 8px 8px 0 8px; font-size: 11px;">
                                
                                <!-- Media Image/Video Preview -->
                                <div id="previewMediaBox" class="mb-1 d-none">
                                    <img id="previewMediaImg" src="" class="img-fluid rounded border mb-1" style="max-height: 140px; width: 100%; object-fit: cover;">
                                    <div id="previewDocBox" class="d-none bg-white p-2 rounded border d-flex align-items-center gap-2">
                                        <i class="las la-file-pdf text--danger fs-4"></i>
                                        <span id="previewDocName" class="text-truncate fw-bold" style="font-size: 10px;">document.pdf</span>
                                    </div>
                                </div>

                                <!-- Message Text -->
                                <div id="previewMessageText" style="white-space: pre-wrap;">Type a message to see the live preview...</div>

                                <!-- Timestamp & Double Blue Tick -->
                                <div class="text-end text-muted d-flex align-items-center justify-content-end gap-1 mt-1" style="font-size: 8px;">
                                    <span id="previewTime">{{ date('h:i A') }}</span>
                                    <i class="las la-check-double text--info" style="font-size: 10px;"></i>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- WhatsApp Input Bottom Bar Mockup -->
                    <div class="p-2 bg-white d-flex align-items-center gap-2 border-top">
                        <i class="las la-smile text-muted"></i>
                        <div class="flex-grow-1 bg-light rounded-pill px-2 py-1 text-muted text-xs" style="font-size: 10px;">Message</div>
                        <i class="las la-paperclip text-muted"></i>
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background: #128c7e; font-size: 10px;">
                            <i class="las la-microphone"></i>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Recent Dispatches Summary Card -->
        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between">
                <span class="fw-bold small text-dark"><i class="las la-history text--info me-1"></i> Recent Sent Activity</span>
                <a href="{{ route('admin.device.sender.logs') }}" class="text--primary text-xs">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($recentMessages as $msg)
                        <li class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                            <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                <span class="badge {{ $msg->status == 'sent' || $msg->status == 'delivered' ? 'bg-success' : 'bg-danger' }} rounded-circle p-1"></span>
                                <div>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 180px;">
                                        {{ $msg->receiver_name ?: $msg->receiver }}
                                    </div>
                                    <small class="text-muted text-xs text-truncate d-block" style="max-width: 180px;">
                                        {{ Str::limit($msg->message, 30) }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark text-xs">{!! $msg->media_badge !!}</span>
                                <small class="text-muted d-block text-xs">{{ $msg->created_at->diffForHumans() }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">
                            No messages sent yet. Compose your first message above!
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Contact Picker -->
<div class="modal fade" id="contactPickerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="las la-address-book text--primary me-1"></i> Select Contacts</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <input type="text" id="contactSearchInput" class="form-control form-control-sm mb-3" placeholder="Search contacts by name or number...">
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAllContacts"></th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="contactTableBody">
                            @php
                                $allContacts = \App\Models\Contact::where('type', 'contact')->take(50)->get();
                            @endphp
                            @forelse($allContacts as $c)
                                <tr>
                                    <td><input type="checkbox" class="contact-checkbox" value="{{ $c->phone_number }}" data-name="{{ $c->name }}"></td>
                                    <td class="fw-bold">{{ $c->name ?: 'Contact' }}</td>
                                    <td class="font-monospace text-muted">{{ $c->phone_number }}</td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline--primary btn-pick-single" data-phone="{{ $c->phone_number }}">Select</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No contacts found. You can import or sync from WhatsApp.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" id="btnApplySelectedContacts" class="btn btn--primary btn-sm">Add Selected Contacts</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Group Picker -->
<div class="modal fade" id="groupPickerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="las la-users text--info me-1"></i> Select WhatsApp Group</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="list-group" style="max-height: 320px; overflow-y: auto;">
                    @forelse($groups as $g)
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between btn-pick-group" data-jid="{{ $g->group_id }}" data-name="{{ $g->group_name }}">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark text-xs"><i class="lab la-whatsapp text--success me-1"></i> {{ $g->group_name }}</h6>
                                <small class="text-muted font-monospace text-xs">{{ $g->group_id }}</small>
                            </div>
                            <span class="badge bg-primary text-xs">Select</span>
                        </button>
                    @empty
                        <div class="p-3 text-center text-muted">No groups found in database. Sync groups from Manage Contacts.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Sending Progress Modal -->
<div class="modal fade" id="sendingProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold text-white"><i class="las la-paper-plane text--success me-1"></i> Sending from Android Device</h6>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="spinner-border text--success mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="sendingProgressTitle">Dispatching Messages...</h5>
                <p class="text-muted small mb-3" id="sendingProgressSubtitle">Connecting to your WhatsApp device session and transmitting payload.</p>
                
                <div class="progress mb-3" style="height: 10px;">
                    <div id="sendingProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg--success" role="progressbar" style="width: 100%"></div>
                </div>

                <div id="sendingResultBox" class="border rounded p-3 text-start bg-light d-none small font-monospace" style="max-height: 180px; overflow-y: auto;">
                </div>
            </div>
            <div class="modal-footer py-2 d-none" id="sendingModalFooter">
                <a href="{{ route('admin.device.sender.logs') }}" class="btn btn-outline--primary btn-sm">View Logs</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function($){
    "use strict";

    // Update Live Mockup Preview
    function updatePreview() {
        const text = $('#messageText').val() || 'Type a message to see the live preview...';
        $('#previewMessageText').text(text);

        const receiver = $('#receiver').val().trim();
        if(receiver) {
            const first = receiver.split(',')[0].trim();
            $('#previewSenderName').text(first);
        } else {
            $('#previewSenderName').text('Customer');
        }

        const mediaUrl = $('#mediaUrl').val().trim();
        const mediaType = $('#mediaType').val();

        if (mediaUrl) {
            $('#previewMediaBox').removeClass('d-none');
            if (mediaType === 'image' || mediaUrl.match(/\.(jpg|jpeg|png|webp|gif)/i)) {
                $('#previewMediaImg').attr('src', mediaUrl).removeClass('d-none');
                $('#previewDocBox').addClass('d-none');
            } else {
                $('#previewMediaImg').addClass('d-none');
                $('#previewDocBox').removeClass('d-none');
                $('#previewDocName').text($('#filename').val() || mediaUrl.split('/').pop());
            }
        } else {
            $('#previewMediaBox').addClass('d-none');
        }

        // Count chars & words
        const chars = text.length;
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        $('#charCounter').text(`${chars} chars | ${words} words`);
    }

    $('#messageText, #receiver, #mediaUrl, #mediaType, #filename').on('input change', updatePreview);
    updatePreview();

    // Insert tag buttons
    $('.insert-tag').on('click', function(){
        const tag = $(this).data('tag');
        const textarea = document.getElementById('messageText');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
        updatePreview();
    });

    // Insert emoji buttons
    $('.insert-emoji').on('click', function(){
        const emoji = $(this).data('emoji');
        const textarea = document.getElementById('messageText');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + emoji + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
        textarea.focus();
        updatePreview();
    });

    // Template picker
    $('#template_picker').on('change', function(){
        const val = $(this).val();
        if(val) {
            $('#messageText').val(val);
            updatePreview();
        }
    });

    // Reset Form
    $('#btnClearForm').on('click', function(){
        $('#deviceSenderForm')[0].reset();
        updatePreview();
    });

    // Contact Picker Single Select
    $(document).on('click', '.btn-pick-single', function(){
        const phone = $(this).data('phone');
        $('#receiver').val(phone);
        $('#contactPickerModal').modal('hide');
        updatePreview();
    });

    // Contact Picker Multi Select
    $('#selectAllContacts').on('change', function(){
        $('.contact-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#btnApplySelectedContacts').on('click', function(){
        const selected = [];
        $('.contact-checkbox:checked').each(function(){
            selected.push($(this).val());
        });
        if(selected.length > 0) {
            $('#receiver').val(selected.join(', '));
            $('#contactPickerModal').modal('hide');
            updatePreview();
        } else {
            notify('error', 'Please select at least one contact');
        }
    });

    // Group Picker
    $('.btn-pick-group').on('click', function(){
        const jid = $(this).data('jid');
        $('#receiver').val(jid);
        $('#groupPickerModal').modal('hide');
        updatePreview();
    });

    // Contact Search Filter in Modal
    $('#contactSearchInput').on('keyup', function(){
        const val = $(this).val().toLowerCase();
        $('#contactTableBody tr').filter(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    // Submit Message Dispatch
    $('#deviceSenderForm').on('submit', function(e){
        e.preventDefault();

        const receiver = $('#receiver').val().trim();
        const message = $('#messageText').val().trim();
        const file = $('#fileUpload')[0].files[0];
        const mediaUrl = $('#mediaUrl').val().trim();

        if(!receiver){
            notify('error', 'Please enter at least one recipient phone number');
            return;
        }

        if(!message && !file && !mediaUrl){
            notify('error', 'Please enter a message text or attach a media file');
            return;
        }

        const formData = new FormData(this);
        const modal = new bootstrap.Modal(document.getElementById('sendingProgressModal'));
        modal.show();

        $('#sendingProgressTitle').text('Transmitting Message...');
        $('#sendingProgressSubtitle').text('Sending from your connected Android WhatsApp device...');
        $('#sendingResultBox').addClass('d-none').html('');
        $('#sendingModalFooter').addClass('d-none');
        $('.spinner-border').removeClass('d-none');

        $.ajax({
            url: "{{ route('admin.device.sender.send') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                $('.spinner-border').addClass('d-none');
                $('#sendingProgressTitle').html('<i class="las la-check-circle text-success me-1"></i> ' + (res.message || 'Dispatched Successfully!'));
                $('#sendingProgressSubtitle').text(`Sent: ${res.sent_count} | Failed: ${res.failed_count}`);
                
                let resultHtml = '';
                if(res.results && res.results.length > 0){
                    res.results.forEach(function(r){
                        const isOk = r.status === 'sent';
                        resultHtml += `<div class="mb-1 ${isOk ? 'text-success' : 'text-danger'}">
                            ${isOk ? '✅' : '❌'} <strong>${r.receiver}</strong>: ${isOk ? 'Delivered successfully' : (r.error || 'Failed')}
                        </div>`;
                    });
                    $('#sendingResultBox').removeClass('d-none').html(resultHtml);
                }

                $('#sendingModalFooter').removeClass('d-none');
                notify('success', res.message || 'Message sent successfully!');
            },
            error: function(xhr){
                $('.spinner-border').addClass('d-none');
                let err = 'Failed to send message';
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    err = parsed.message || parsed.error || err;
                } catch(e){}

                $('#sendingProgressTitle').html('<i class="las la-times-circle text-danger me-1"></i> Delivery Error');
                $('#sendingProgressSubtitle').text(err);
                $('#sendingModalFooter').removeClass('d-none');
                notify('error', err);
            }
        });
    });

})(jQuery);
</script>
@endpush

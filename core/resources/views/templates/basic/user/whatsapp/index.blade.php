@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My WhatsApp Accounts</h4>
                <p class="text-muted mb-0">Manage connected WhatsApp devices, extract participating groups & contacts, and send messages.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('user.contacts.lists') }}" class="btn btn-outline-secondary">
                    <i class="las la-list me-1"></i> View Contact Lists
                </a>
                <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base">
                    <i class="las la-plus-circle me-1"></i> Connect New Account
                </a>
            </div>
        </div>

        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Account Name</th>
                                <th>Phone Number</th>
                                <th>Session ID</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $acc)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar--sm {{ $acc->status == 1 ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 {{ $acc->status == 1 ? 'text-success' : 'text-secondary' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="lab la-whatsapp fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $acc->account_name }}</div>
                                                @if($acc->profile_name)
                                                    <small class="text-muted d-block"><i class="las la-user me-1"></i>{{ $acc->profile_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($acc->phone_number)
                                            <span class="fw-bold font-monospace text-primary">+{{ $acc->phone_number }}</span>
                                        @else
                                            <span class="text-muted small">Unlinked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small px-2 py-1 rounded bg-light border">{{ $acc->session_id }}</code>
                                    </td>
                                    <td>
                                        @if($acc->status == 1)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fw-semibold">
                                                <i class="las la-check-circle me-1"></i> Connected & Online
                                            </span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 fw-semibold">
                                                <i class="las la-hourglass-half me-1"></i> Pending Connection
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            @if($acc->status == 1)
                                                <!-- Extract Groups -->
                                                <button type="button" class="btn btn-outline-success btn-sm btnExtractGroups px-2 py-1" data-session="{{ $acc->session_id }}" data-name="{{ $acc->account_name }}" title="Extract WhatsApp Groups">
                                                    <i class="las la-users me-1"></i> Extract Groups
                                                </button>

                                                <!-- Extract Contacts -->
                                                <button type="button" class="btn btn-outline-primary btn-sm btnExtractContacts px-2 py-1" data-session="{{ $acc->session_id }}" data-name="{{ $acc->account_name }}" title="Extract WhatsApp Contacts">
                                                    <i class="las la-address-book me-1"></i> Extract Contacts
                                                </button>

                                                <!-- Test Message -->
                                                <button type="button" class="btn btn-outline-info btn-sm btnTestMessage px-2 py-1" data-session="{{ $acc->session_id }}" title="Send Test Message">
                                                    <i class="las la-paper-plane"></i>
                                                </button>
                                            @endif

                                            <form action="{{ route('user.whatsapp.delete', $acc->id) }}" method="POST" onsubmit="return confirm('Disconnect and remove this WhatsApp account?')" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1" title="Disconnect & Delete">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="avatar avatar--xl bg-light text-muted rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                            <i class="lab la-whatsapp fs-1"></i>
                                        </div>
                                        <h5 class="fw-bold mb-1">No WhatsApp Accounts Linked Yet</h5>
                                        <p class="text-muted small mb-3">Connect your first WhatsApp device using QR code or 8-digit Pairing Code.</p>
                                        <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm px-4">
                                            <i class="las la-link me-1"></i> Connect WhatsApp Account
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($accounts->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($accounts) }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Extract Groups -->
<div class="modal fade" id="extractGroupsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-users me-1"></i> Extract WhatsApp Groups</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="extractGroupsLoading" class="text-center py-5">
                    <div class="spinner-border text-success mb-3" role="status"></div>
                    <h6 class="fw-bold">Extracting Participating Groups...</h6>
                    <p class="text-muted small">Fetching real-time group chats and participant counts from WhatsApp.</p>
                </div>

                <div id="extractGroupsContent" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold mb-0" id="groupsCountTitle">Participating WhatsApp Groups</h6>
                            <small class="text-muted">Select a group to extract its members directly into a Contact List.</small>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3 mb-3" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Group Name</th>
                                    <th>Members</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="groupsTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Save Group Members to List -->
<div class="modal fade" id="saveGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-file-import me-1"></i> Save Group to Contact List</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="saveGroupForm">
                @csrf
                <input type="hidden" name="session_id" id="saveGroupSessionId">
                <input type="hidden" name="group_jid" id="saveGroupJid">
                <input type="hidden" name="group_name" id="saveGroupNameHidden">
                <input type="hidden" name="members_json" id="saveGroupMembersJson">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Contact List Name <span class="text-danger">*</span></label>
                        <input type="text" name="list_name" id="saveGroupNameInput" class="form-control" required>
                        <small class="text-muted">A new Contact List will be created containing all members of this group.</small>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center">
                        <i class="las la-info-circle fs-5 me-2"></i>
                        <span id="saveGroupMemberCountInfo">Extracting members...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base btn-sm" id="btnSubmitSaveGroup">
                        <i class="las la-save me-1"></i> Save Contact List
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Extract Contacts -->
<div class="modal fade" id="extractContactsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-address-book me-1"></i> Extract All WhatsApp Contacts</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="extractContactsForm">
                @csrf
                <input type="hidden" name="session_id" id="extractContactsSessionId">
                
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Extract genuine saved WhatsApp contacts and conversation contacts from this account directly into your Audience.
                    </p>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Assign to Existing List (Optional)</label>
                        <select name="contact_list_id" class="form-select">
                            <option value="">-- No Specific List (Global Audience) --</option>
                            @foreach($contactLists as $lst)
                                <option value="{{ $lst->id }}">{{ $lst->name }} ({{ $lst->contacts_count ?? 0 }} contacts)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Or Create a New List Name</label>
                        <input type="text" name="new_list_name" class="form-control" placeholder="e.g. Phone Contacts Sync">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base btn-sm" id="btnSubmitExtractContacts">
                        <i class="las la-download me-1"></i> Start Extraction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Test Message -->
<div class="modal fade" id="testMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-paper-plane me-1"></i> Send Test WhatsApp Message</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="testMessageForm">
                @csrf
                <input type="hidden" name="session_id" id="testSessionId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Recipient WhatsApp Number (with country code)</label>
                        <input type="text" name="recipient" class="form-control" placeholder="e.g. 923216793596" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Test Message Text</label>
                        <textarea name="message" class="form-control" rows="3" required>Hello! This is a test message from my WhatsApp Bot.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base btn-sm" id="btnSubmitTest"><i class="las la-paper-plane me-1"></i> Send Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        // Test Message
        $('.btnTestMessage').on('click', function () {
            var session = $(this).data('session');
            $('#testSessionId').val(session);
            $('#testMessageModal').modal('show');
        });

        $('#testMessageForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#btnSubmitTest');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Sending...');

            $.ajax({
                url: "{{ route('user.whatsapp.test.message') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $btn.prop('disabled', false).html(originalText);
                    if (res.success) {
                        notify('success', res.message);
                        $('#testMessageModal').modal('hide');
                    } else {
                        notify('error', res.message || 'Failed to send message.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error sending message.';
                    notify('error', msg);
                }
            });
        });

        // Extract Groups
        var currentExtractSession = null;
        var fetchedGroups = [];

        $('.btnExtractGroups').on('click', function () {
            currentExtractSession = $(this).data('session');
            var accountName = $(this).data('name');
            
            $('#extractGroupsLoading').removeClass('d-none');
            $('#extractGroupsContent').addClass('d-none');
            $('#extractGroupsModal').modal('show');

            $.ajax({
                url: "{{ url('user/whatsapp/extract-groups') }}/" + currentExtractSession,
                type: "GET",
                success: function (res) {
                    $('#extractGroupsLoading').addClass('d-none');
                    $('#extractGroupsContent').removeClass('d-none');

                    if (res.success && res.groups && res.groups.length > 0) {
                        fetchedGroups = res.groups;
                        $('#groupsCountTitle').text('Found ' + res.groups.length + ' WhatsApp Groups (' + accountName + ')');
                        var html = '';
                        res.groups.forEach(function (g, idx) {
                            html += '<tr>';
                            html += '<td><div class="fw-bold"><i class="las la-users text-success me-1"></i> ' + (g.subject || 'Unnamed Group') + '</div><small class="text-muted">' + (g.id || '') + '</small></td>';
                            html += '<td><span class="badge bg-light text-dark border">' + (g.participantsCount || (g.participants ? g.participants.length : 0)) + ' Members</span></td>';
                            html += '<td class="text-end"><button type="button" class="btn btn-outline-primary btn-sm btnSaveGroupToList" data-index="' + idx + '"><i class="las la-file-import me-1"></i> Save to List</button></td>';
                            html += '</tr>';
                        });
                        $('#groupsTableBody').html(html);
                    } else {
                        $('#groupsTableBody').html('<tr><td colspan="3" class="text-center py-4 text-muted">No participating groups found for this WhatsApp account.</td></tr>');
                    }
                },
                error: function (xhr) {
                    $('#extractGroupsLoading').addClass('d-none');
                    $('#extractGroupsContent').removeClass('d-none');
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Failed to extract groups.';
                    $('#groupsTableBody').html('<tr><td colspan="3" class="text-center py-4 text-danger"><i class="las la-exclamation-circle me-1"></i> ' + msg + '</td></tr>');
                }
            });
        });

        // Click Save Group to List
        $(document).on('click', '.btnSaveGroupToList', function () {
            var idx = $(this).data('index');
            var group = fetchedGroups[idx];
            if (!group) return;

            $('#saveGroupSessionId').val(currentExtractSession);
            $('#saveGroupJid').val(group.id);
            $('#saveGroupNameHidden').val(group.subject || 'WhatsApp Group');
            $('#saveGroupNameInput').val((group.subject || 'Group') + ' - List');
            $('#saveGroupMembersJson').val(JSON.stringify(group.participants || []));
            $('#saveGroupMemberCountInfo').text('Ready to extract ' + (group.participants ? group.participants.length : 0) + ' members into a new list.');

            $('#extractGroupsModal').modal('hide');
            $('#saveGroupModal').modal('show');
        });

        $('#saveGroupForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#btnSubmitSaveGroup');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Saving List...');

            $.ajax({
                url: "{{ route('user.whatsapp.save.extracted.group') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $btn.prop('disabled', false).html(originalText);
                    if (res.success) {
                        notify('success', res.message);
                        $('#saveGroupModal').modal('hide');
                    } else {
                        notify('error', res.error || 'Failed to save group list.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Error saving list.';
                    notify('error', msg);
                }
            });
        });

        // Extract Contacts
        $('.btnExtractContacts').on('click', function () {
            var session = $(this).data('session');
            $('#extractContactsSessionId').val(session);
            $('#extractContactsModal').modal('show');
        });

        $('#extractContactsForm').on('submit', function (e) {
            e.preventDefault();
            var session = $('#extractContactsSessionId').val();
            var $btn = $('#btnSubmitExtractContacts');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Extracting Contacts...');

            $.ajax({
                url: "{{ url('user/whatsapp/extract-contacts') }}/" + session,
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $btn.prop('disabled', false).html(originalText);
                    if (res.success) {
                        notify('success', res.message);
                        $('#extractContactsModal').modal('hide');
                    } else {
                        notify('error', res.error || 'Failed to extract contacts.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Error extracting contacts.';
                    notify('error', msg);
                }
            });
        });

    })(jQuery);
</script>
@endpush

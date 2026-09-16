@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My WhatsApp Accounts</h4>
                <p class="text-muted mb-0">Manage connected WhatsApp devices, scan QR codes, or connect via Pairing Code.</p>
            </div>
            <div>
                <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base">
                    <i class="las la-plus-circle me-1"></i> Connect New Account
                </a>
            </div>
        </div>

        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account Name</th>
                                <th>Phone Number</th>
                                <th>Session ID</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $acc)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar--sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                <i class="lab la-whatsapp fs-4"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark fs-6 d-block">{{ $acc->account_name }}</span>
                                                @if($acc->profile_name && $acc->profile_name != $acc->account_name)
                                                    <small class="text-muted d-block" style="font-size: 12px;">{{ $acc->profile_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($acc->phone_number)
                                            <span class="fw-bold text-dark fs-6"><i class="lab la-whatsapp text-success me-1"></i>+{{ $acc->phone_number }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-muted border">Unlinked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="px-2 py-1 bg-light border rounded text-primary fw-bold font-monospace" style="font-size: 11.5px;">{{ $acc->session_id }}</code>
                                    </td>
                                    <td>
                                        @if($acc->status == 1)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fw-bold"><i class="las la-check-circle me-1"></i> Connected & Online</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 fw-bold"><i class="las la-hourglass-half me-1"></i> Pending Connection</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($acc->status == 1)
                                                {{-- Extract Groups Icon --}}
                                                <button type="button" class="btn btn-outline-info btn-sm rounded-circle d-inline-flex align-items-center justify-content-center btnExtractGroups" data-session="{{ $acc->session_id }}" data-name="{{ $acc->account_name }}" title="Extract WhatsApp Groups" style="width: 36px; height: 36px; padding: 0;">
                                                    <i class="las la-users fs-5"></i>
                                                </button>
                                                {{-- Send / Create Message Icon --}}
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center btnTestMessage" data-session="{{ $acc->session_id }}" title="Send WhatsApp Message" style="width: 36px; height: 36px; padding: 0;">
                                                    <i class="las la-paper-plane fs-5"></i>
                                                </button>
                                            @endif
                                            {{-- Delete / Remove Account Icon --}}
                                            <form action="{{ route('user.whatsapp.delete', $acc->id) }}" method="POST" onsubmit="return confirm('Disconnect and remove this WhatsApp account?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" title="Disconnect & Remove Account" style="width: 36px; height: 36px; padding: 0;">
                                                    <i class="las la-trash-alt fs-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="lab la-whatsapp text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted fw-bold">No WhatsApp accounts linked yet</h6>
                                        <p class="text-muted small">Connect your first WhatsApp device using QR code scan or 8-digit Pairing Code.</p>
                                        <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm px-3">Connect Account</a>
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

<!-- Modal: Send / Test Message -->
<div class="modal fade" id="testMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-paper-plane me-1"></i> Send WhatsApp Message</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="testMessageForm">
                @csrf
                <input type="hidden" name="session_id" id="testSessionId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Recipient WhatsApp Number (with country code) <span class="text-danger">*</span></label>
                        <input type="text" name="recipient" class="form-control" placeholder="e.g. 923216793596 (no dashes or spaces)" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Message Content <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Type your message here..." required>Hello! This is a test message from my WhatsApp Bot.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base btn-sm px-3" id="btnSubmitTest"><i class="las la-paper-plane me-1"></i> Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Extract WhatsApp Groups -->
<div class="modal fade" id="extractGroupsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title text-white fw-bold d-flex align-items-center">
                    <i class="las la-users text-info fs-5 me-2"></i>
                    <span>Extracted WhatsApp Groups (<span id="modalAccountName"></span>)</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="groupsLoadingState" class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p class="text-muted mb-0">Fetching community groups from WhatsApp socket...</p>
                </div>

                <div id="groupsContentState" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small fw-bold"><span id="totalGroupsCount" class="text-primary fw-bold">0</span> Groups Found</span>
                            <button type="button" class="btn btn-sm btn--base" id="btnOpenSaveAllGroupsModal">
                                <i class="las la-folder-plus me-1"></i> Save All to Group List
                            </button>
                        </div>
                        <input type="text" class="form-control form-control-sm w-auto" id="filterGroupsInput" placeholder="Filter groups...">
                    </div>
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover table-bordered mb-0" id="extractedGroupsTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Group Name</th>
                                    <th>Group JID</th>
                                    <th class="text-center">Members</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="extractedGroupsBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="groupsEmptyState" class="d-none text-center py-4">
                    <i class="las la-comments text-muted fs-1 d-block mb-2"></i>
                    <h6 class="text-muted fw-bold">No WhatsApp Groups Found</h6>
                    <p class="text-muted small mb-0">This WhatsApp account is not currently a member of any community groups.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Prompt for List Name when saving All Extracted Groups -->
<div id="saveAllGroupsModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title text-white fw-bold d-flex align-items-center">
                    <i class="las la-folder-plus text-primary fs-5 me-2"></i>
                    <span>Save Groups to Contact List</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSaveAllGroups">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-3 small d-flex align-items-center">
                        <i class="las la-info-circle fs-4 me-2"></i>
                        <span>All extracted groups will be saved into this named Contact List.</span>
                    </div>

                    <div class="mb-0">
                        <label class="fw-bold mb-1">Enter Contact List Name <span class="text-danger">*</span></label>
                        <input type="text" id="target_groups_list_name" class="form-control" placeholder="e.g. My Groups, Marketing Groups" value="My WhatsApp Groups" required>
                        <small class="text-muted d-block mt-1">All extracted groups will become part of this specific contact list.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base btn-sm px-4 fw-bold" id="btnSubmitSaveAllGroups">
                        <i class="las la-save me-1"></i> Save into Contact List
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Prompt for List Name when extracting members of a single Group -->
<div id="extractSingleGroupModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title text-white fw-bold d-flex align-items-center">
                    <i class="las la-user-plus text-success fs-5 me-2"></i>
                    <span>Extract Group Members to Contact List</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formExtractSingleGroup">
                @csrf
                <input type="hidden" id="single_grp_id">
                <input type="hidden" id="single_grp_subject">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-3 small d-flex align-items-center">
                        <i class="las la-info-circle fs-4 me-2"></i>
                        <span>All members will be extracted with their real WhatsApp names and saved into a dedicated Contact List.</span>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-1">Source WhatsApp Group</label>
                        <input type="text" id="single_grp_display" class="form-control bg-light fw-bold" readonly>
                    </div>

                    <div class="mb-0">
                        <label class="fw-bold mb-1">Contact List Name <span class="text-danger">*</span></label>
                        <input type="text" id="single_grp_list_name" class="form-control" required>
                        <small class="text-muted d-block mt-1">By default named after the group. You can customize the name as desired.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold" id="btnSubmitSingleExtract">
                        <i class="las la-file-import me-1"></i> Extract & Save List
                    </button>
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

        let extractedGroupsList = [];

        // Send Test Message Modal
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
                    var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Error sending message.';
                    notify('error', msg);
                }
            });
        });

        // Extract Groups Modal Handler
        $('.btnExtractGroups').on('click', function () {
            var sessionId = $(this).data('session');
            var accountName = $(this).data('name') || 'WhatsApp Account';

            $('#modalAccountName').text(accountName);
            $('#groupsLoadingState').removeClass('d-none');
            $('#groupsContentState').addClass('d-none');
            $('#groupsEmptyState').addClass('d-none');
            $('#extractedGroupsBody').empty();
            $('#extractGroupsModal').modal('show');

            $.ajax({
                url: "{{ url('user/whatsapp/extract-groups') }}/" + sessionId,
                type: "GET",
                success: function (res) {
                    $('#groupsLoadingState').addClass('d-none');
                    if (res && res.success && res.groups && res.groups.length > 0) {
                        extractedGroupsList = res.groups;
                        $('#totalGroupsCount').text(res.groups.length);
                        var rows = '';
                        res.groups.forEach(function (g, index) {
                            var memberCount = g.participantsCount || (g.participants ? g.participants.length : '-');
                            rows += '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td><strong class="text-dark">' + (g.subject || 'Unnamed Group') + '</strong></td>' +
                                '<td><code class="small text-muted">' + g.id + '</code></td>' +
                                '<td class="text-center"><span class="badge bg-info bg-opacity-10 text-info px-2 py-1">' + memberCount + ' Members</span></td>' +
                                '<td class="text-center">' +
                                    '<div class="d-flex gap-1 justify-content-center flex-wrap">' +
                                        '<button type="button" class="btn btn-sm btn-outline-success btnOpenSingleExtract py-1 px-2" data-group-id="' + g.id + '" data-group-name="' + encodeURIComponent(g.subject || 'WhatsApp Group') + '" data-index="' + index + '" title="Extract & Save Group Members">' +
                                            '<i class="las la-user-plus me-1"></i> Extract Members' +
                                        '</button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-secondary btn-copy-jid py-1 px-2" data-jid="' + g.id + '" title="Copy Group JID">' +
                                            '<i class="las la-copy"></i>' +
                                        '</button>' +
                                    '</div>' +
                                '</td>' +
                            '</tr>';
                        });
                        $('#extractedGroupsBody').html(rows);
                        $('#groupsContentState').removeClass('d-none');
                    } else {
                        $('#groupsEmptyState').removeClass('d-none');
                    }
                },
                error: function (xhr) {
                    $('#groupsLoadingState').addClass('d-none');
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Failed to extract WhatsApp groups.';
                    notify('error', msg);
                    $('#extractGroupsModal').modal('hide');
                }
            });
        });

        // 1. Open Save All Groups Modal
        $('#btnOpenSaveAllGroupsModal').on('click', function () {
            $('#saveAllGroupsModal').modal('show');
        });

        $('#formSaveAllGroups').on('submit', function (e) {
            e.preventDefault();
            var listName = $('#target_groups_list_name').val().trim();

            if (!listName) {
                notify('error', 'Please enter a name for the Contact List');
                return;
            }

            if (!extractedGroupsList || extractedGroupsList.length === 0) {
                notify('error', 'No groups available to save.');
                return;
            }

            var simplified = extractedGroupsList.map(function (g) {
                return {
                    id: g.id,
                    subject: g.subject || g.name || 'WhatsApp Group',
                    participantsCount: g.participantsCount || (g.participants ? g.participants.length : 0)
                };
            });

            var $btn = $('#btnSubmitSaveAllGroups');
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Saving...');

            $.ajax({
                url: "{{ route('user.contacts.import.groups.list') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    list_name: listName,
                    groups: JSON.stringify(simplified)
                },
                success: function (res) {
                    $btn.prop('disabled', false).html(origHtml);
                    $('#saveAllGroupsModal').modal('hide');
                    if (res.success) {
                        notify('success', res.message);
                        setTimeout(function () {
                            window.location.href = "{{ route('user.contacts.lists') }}";
                        }, 1200);
                    } else {
                        notify('error', res.error || 'Failed to save groups to list');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    var errMsg = 'Failed to save groups.';
                    if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                        errMsg = xhr.responseJSON.message || xhr.responseJSON.error;
                    }
                    notify('error', errMsg);
                }
            });
        });

        // 2. Open Single Group Extract Modal
        $(document).on('click', '.btnOpenSingleExtract', function () {
            var groupId = $(this).data('group-id');
            var groupName = decodeURIComponent($(this).data('group-name'));
            var index = $(this).data('index');

            $('#single_grp_id').val(groupId);
            $('#single_grp_subject').val(groupName);
            $('#single_grp_display').val(groupName);
            $('#single_grp_list_name').val(groupName + ' Members');
            $('#single_grp_list_name').data('index', index);

            $('#extractSingleGroupModal').modal('show');
        });

        $('#formExtractSingleGroup').on('submit', function (e) {
            e.preventDefault();

            var groupId = $('#single_grp_id').val();
            var groupName = $('#single_grp_subject').val();
            var listName = $('#single_grp_list_name').val().trim();
            var index = $('#single_grp_list_name').data('index');

            if (!listName) {
                notify('error', 'Please enter a name for the Contact List');
                return;
            }

            var matchedGroup = extractedGroupsList[index] || extractedGroupsList.find(function (g) { return g.id === groupId; });
            if (!matchedGroup || !matchedGroup.participants || matchedGroup.participants.length === 0) {
                notify('error', 'No participants found in this group to extract.');
                return;
            }

            var $btn = $('#btnSubmitSingleExtract');
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Extracting...');

            $.ajax({
                url: "{{ route('user.contacts.extract.group.members.list') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    list_name: listName,
                    source_group_id: groupId,
                    source_group_name: groupName,
                    participants: JSON.stringify(matchedGroup.participants)
                },
                success: function (res) {
                    $btn.prop('disabled', false).html(origHtml);
                    $('#extractSingleGroupModal').modal('hide');

                    if (res.success) {
                        notify('success', res.message);
                        setTimeout(function () {
                            window.location.href = "{{ route('user.contacts.lists') }}";
                        }, 1200);
                    } else {
                        notify('error', res.error || 'Failed to extract group members');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    var errMsg = 'Failed to extract group members.';
                    if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                        errMsg = xhr.responseJSON.message || xhr.responseJSON.error;
                    }
                    notify('error', errMsg);
                }
            });
        });

        // Filter groups in modal
        $('#filterGroupsInput').on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $("#extractedGroupsBody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Copy Group JID
        $(document).on('click', '.btn-copy-jid', function () {
            var jid = $(this).data('jid');
            navigator.clipboard.writeText(jid);
            notify('success', 'Group JID copied to clipboard!');
        });

    })(jQuery);
</script>
@endpush

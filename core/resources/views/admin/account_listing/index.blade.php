@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="lab la-whatsapp me-2 fs-4 text--success"></i> @lang('Connected WhatsApp Accounts')
                </h5>
                <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn--primary">
                    <i class="las la-plus me-1"></i>@lang('Add New WhatsApp Account')
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--lg table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>@lang('Account Name')</th>
                                <th>@lang('Phone Number')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Connected At')</th>
                                <th class="text-end pe-4">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>
                                        <div class="user">
                                            <div class="thumb me-2">
                                                <i class="lab la-whatsapp text--success" style="font-size: 26px;"></i>
                                            </div>
                                            <span class="name fw-bold">{{ __($account->account_name) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($account->phone_number)
                                            <span class="badge badge--info fs-6">+{{ $account->phone_number }}</span>
                                        @else
                                            <span class="text-muted">@lang('Not Connected')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php echo $account->statusBadge; @endphp
                                    </td>
                                    <td>
                                        {{ $account->last_connected_at ? showDateTime($account->last_connected_at) : 'N/A' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline--primary btnExtractGroups"
                                                data-session_id="{{ $account->session_id }}"
                                                data-name="{{ $account->account_name }}"
                                                data-bs-toggle="tooltip"
                                                title="@lang('Extract WhatsApp Groups & Create Lists')">
                                                <i class="las la-users-cog fs-6"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline--success btnTestMessage"
                                                data-session_id="{{ $account->session_id }}"
                                                data-name="{{ $account->account_name }}"
                                                data-phone="{{ $account->phone_number }}"
                                                data-bs-toggle="tooltip"
                                                title="@lang('Send Direct Test Message')">
                                                <i class="las la-paper-plane fs-6"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                                data-action="{{ route('admin.account.listing.delete', $account->id) }}"
                                                data-question="@lang('Are you sure you want to remove and disconnect this WhatsApp account?')"
                                                data-bs-toggle="tooltip"
                                                title="@lang('Delete / Disconnect Account')">
                                                <i class="las la-trash fs-6"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-4" colspan="100%">
                                        <div class="empty-state">
                                            <i class="lab la-whatsapp text--muted mb-3" style="font-size: 48px;"></i>
                                            <p class="text-muted mb-2">@lang('No WhatsApp accounts added yet.')</p>
                                            <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn--primary">
                                                <i class="las la-plus me-1"></i>@lang('Add First WhatsApp Account')
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($accounts->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($accounts) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Test Direct Message Modal -->
<div id="testMessageModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--dark text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="lab la-whatsapp text--success me-2 fs-4"></i> @lang('Send WhatsApp Message')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="modalTestMessageForm">
                @csrf
                <input type="hidden" name="session_id" id="modal_session_id">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Sending From')</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="lab la-whatsapp text--success fs-5"></i></span>
                            <input type="text" id="modal_sender_display" class="form-control border-start-0" style="background-color: #f8fafc !important; color: #1e293b !important; font-weight: 600;" readonly>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Recipient WhatsApp Number or Group ID') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver" id="modal_receiver" class="form-control" placeholder="@lang('e.g. 923001234567 or 120363...@g.us')" required>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Message') <span class="text--danger">*</span></label>
                        <textarea name="message" id="modal_message" rows="3" class="form-control" placeholder="@lang('Type your test message...')" required>Hello! This is a test message from Auto WhatsApp.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success btn-sm px-3 fw-bold" id="btnModalSendMessage">
                        <i class="las la-paper-plane me-1"></i> @lang('Send Message')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Group Extract Modal -->
<div id="groupExtractModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white d-flex align-items-center justify-content-between">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-users-cog me-2 fs-4"></i> @lang('Extracted WhatsApp Groups & Actions')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <div id="groupLoading" class="text-center py-4">
                    <div class="spinner-border text--primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="text-muted">@lang('Extracting groups from WhatsApp...')</h6>
                </div>

                <div id="groupContent" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded border flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0">
                                @lang('Total Groups Found'): <span class="badge badge--success fs-6" id="totalGroupsCount">0</span>
                            </h6>
                        </div>
                        <div>
                            <!-- Save All Extracted Groups to a Named List (No. 1 in User Request) -->
                            <button type="button" class="btn btn-sm btn--success fw-bold" id="btnOpenSaveAllGroupsModal">
                                <i class="las la-folder-plus me-1"></i> @lang('Save All Groups to Contact List')
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>@lang('Group Name')</th>
                                    <th>@lang('Group JID / ID')</th>
                                    <th>@lang('Members')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody id="groupTableBody">
                                <!-- Rendered via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="groupEmpty" class="text-center py-4 d-none">
                    <i class="las la-info-circle text--warning mb-2" style="font-size: 42px;"></i>
                    <p class="text-muted mb-0">@lang('No participating groups found for this WhatsApp account.')</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--dark btn-sm px-4" data-bs-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Prompt for List Name when saving All Extracted Groups (No. 1 in User Request) -->
<div id="saveAllGroupsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-folder-plus me-2 fs-4"></i> @lang('Save Groups to Contact List')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="formSaveAllGroups">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-3 small d-flex align-items-center">
                        <i class="las la-info-circle fs-4 me-2"></i>
                        <span>@lang('All extracted groups will be saved into this named Contact List.')</span>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Enter Contact List Name') <span class="text--danger">*</span></label>
                        <input type="text" id="target_groups_list_name" class="form-control" placeholder="@lang('e.g. My Groups, Marketing Groups')" value="My Groups" required>
                        <small class="text-muted d-block mt-1">@lang('All extracted groups will become part of this specific list.')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold" id="btnSubmitSaveAllGroups">
                        <i class="las la-save me-1"></i> @lang('Save into Contact List')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Prompt for List Name when extracting members of a single Group (No. 2 in User Request) -->
<div id="extractSingleGroupModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--success text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-user-plus me-2 fs-4"></i> @lang('Extract Group Members to Contact List')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="formExtractSingleGroup">
                @csrf
                <input type="hidden" id="single_grp_id">
                <input type="hidden" id="single_grp_subject">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-3 small d-flex align-items-center">
                        <i class="las la-info-circle fs-4 me-2"></i>
                        <span>@lang('All members will be extracted with their real WhatsApp names and saved into a dedicated Contact List.')</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Source WhatsApp Group')</label>
                        <input type="text" id="single_grp_display" class="form-control bg-light font-weight-bold" readonly>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Contact List Name') <span class="text--danger">*</span></label>
                        <input type="text" id="single_grp_list_name" class="form-control" required>
                        <small class="text-muted d-block mt-1">@lang('By default named with that group. You can change it as desired.')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success btn-sm px-4 fw-bold" id="btnSubmitSingleExtract">
                        <i class="las la-file-import me-1"></i> @lang('Extract & Save List')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.contacts.lists.index') }}" class="btn btn-sm btn-outline--dark me-2">
        <i class="las la-list me-1"></i>@lang('Contact Lists')
    </a>
    <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New WhatsApp Account')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    let currentExtractSessionId = null;
    let extractedGroupsList = [];

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Test Message Modal
    $('.btnTestMessage').on('click', function(){
        const sessionId = $(this).data('session_id');
        const name = $(this).data('name');
        const phone = $(this).data('phone');

        $('#modal_session_id').val(sessionId);
        $('#modal_sender_display').val(name + (phone ? ' (+' + phone + ')' : ''));
        $('#testMessageModal').modal('show');
    });

    $('#modalTestMessageForm').on('submit', function(e){
        e.preventDefault();

        const sessionId = $('#modal_session_id').val();
        const receiver = $('#modal_receiver').val().trim();
        const message = $('#modal_message').val().trim();

        if(!receiver){
            notify('error', 'Please enter recipient WhatsApp number or Group JID');
            return;
        }

        $('#btnModalSendMessage').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');

        $.ajax({
            url: "{{ route('admin.account.listing.test.message') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                session_id: sessionId,
                receiver: receiver,
                message: message
            },
            success: function(res){
                $('#btnModalSendMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Message');
                if(res.status === 'success'){
                    $('#testMessageModal').modal('hide');
                    notify('success', res.message || 'Message sent successfully!');
                } else {
                    notify('error', res.error || 'Failed to send message.');
                }
            },
            error: function(xhr){
                $('#btnModalSendMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Message');
                let errMsg = 'Failed to send message.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

    // Group Extractor
    $('.btnExtractGroups').on('click', function(){
        const sessionId = $(this).data('session_id');
        currentExtractSessionId = sessionId;

        $('#groupLoading').removeClass('d-none');
        $('#groupContent').addClass('d-none');
        $('#groupEmpty').addClass('d-none');
        $('#groupTableBody').empty();
        $('#groupExtractModal').modal('show');

        $.ajax({
            url: "{{ url('admin/account-listing/extract-groups') }}/" + sessionId,
            type: "GET",
            success: function(res){
                $('#groupLoading').addClass('d-none');
                if(res.success && res.groups && res.groups.length > 0){
                    extractedGroupsList = res.groups;
                    $('#totalGroupsCount').text(res.groups.length);
                    $('#groupContent').removeClass('d-none');

                    res.groups.forEach((g, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <strong class="d-block text-dark">${g.subject}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace border px-2 py-1" style="font-size: 11px;">${g.id}</span>
                                </td>
                                <td>
                                    <span class="badge badge--info fs-6">${g.participantsCount} Members</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <!-- Extract Members of this group to New List (No. 2) -->
                                        <button type="button" class="btn btn-sm btn-outline--success btnOpenSingleExtract" data-group-id="${g.id}" data-group-name="${encodeURIComponent(g.subject)}" data-index="${index}" title="Extract members of this group into a new Contact List">
                                            <i class="las la-user-plus me-1"></i> Extract Members
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline--secondary btnCopyGroupNumbers" data-numbers='${JSON.stringify((g.participants || []).map(p => p.phone).filter(Boolean))}' title="Copy Member Phone Numbers">
                                            <i class="las la-copy"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#groupTableBody').append(row);
                    });
                } else {
                    $('#groupEmpty').removeClass('d-none');
                }
            },
            error: function(xhr){
                $('#groupLoading').addClass('d-none');
                $('#groupEmpty').removeClass('d-none');
                let errMsg = 'Failed to extract WhatsApp groups.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

    // 1. Open Save All Groups Modal (No. 1)
    $('#btnOpenSaveAllGroupsModal').on('click', function(){
        $('#saveAllGroupsModal').modal('show');
    });

    $('#formSaveAllGroups').on('submit', function(e){
        e.preventDefault();
        const listName = $('#target_groups_list_name').val().trim();

        if(!listName){
            notify('error', 'Please enter a name for the Contact List');
            return;
        }

        if(!extractedGroupsList || extractedGroupsList.length === 0){
            notify('error', 'No groups available to save.');
            return;
        }

        const simplified = extractedGroupsList.map(g => ({
            id: g.id,
            subject: g.subject || g.name || 'WhatsApp Group',
            participantsCount: g.participantsCount || (g.participants ? g.participants.length : 0)
        }));

        $('#btnSubmitSaveAllGroups').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: "{{ route('admin.contacts.import.groups.list') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                list_name: listName,
                groups: JSON.stringify(simplified)
            },
            success: function(res){
                $('#btnSubmitSaveAllGroups').prop('disabled', false).html('<i class="las la-save me-1"></i> Save into Contact List');
                $('#saveAllGroupsModal').modal('hide');
                if(res.success){
                    notify('success', res.message);
                    if(res.list_id){
                        setTimeout(() => {
                            window.location.href = "{{ url('admin/contacts/lists') }}/" + res.list_id;
                        }, 1000);
                    }
                } else {
                    notify('error', res.error || 'Failed to save groups to list');
                }
            },
            error: function(xhr){
                $('#btnSubmitSaveAllGroups').prop('disabled', false).html('<i class="las la-save me-1"></i> Save into Contact List');
                let errMsg = 'Failed to save groups.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

    // 2. Open Single Group Extract Modal (No. 2)
    $(document).on('click', '.btnOpenSingleExtract', function(){
        const groupId = $(this).data('group-id');
        const groupName = decodeURIComponent($(this).data('group-name'));
        const index = $(this).data('index');

        $('#single_grp_id').val(groupId);
        $('#single_grp_subject').val(groupName);
        $('#single_grp_display').val(groupName);
        $('#single_grp_list_name').val(groupName + ' Members');
        $('#single_grp_list_name').data('index', index);

        $('#extractSingleGroupModal').modal('show');
    });

    $('#formExtractSingleGroup').on('submit', function(e){
        e.preventDefault();

        const groupId = $('#single_grp_id').val();
        const groupName = $('#single_grp_subject').val();
        const listName = $('#single_grp_list_name').val().trim();
        const index = $('#single_grp_list_name').data('index');

        if(!listName){
            notify('error', 'Please enter a name for the Contact List');
            return;
        }

        const matchedGroup = extractedGroupsList[index] || extractedGroupsList.find(g => g.id === groupId);
        if(!matchedGroup || !matchedGroup.participants || matchedGroup.participants.length === 0){
            notify('error', 'No participants found in this group to extract.');
            return;
        }

        $('#btnSubmitSingleExtract').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Extracting...');

        $.ajax({
            url: "{{ route('admin.contacts.extract.group.members.list') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                list_name: listName,
                source_group_id: groupId,
                source_group_name: groupName,
                participants: JSON.stringify(matchedGroup.participants)
            },
            success: function(res){
                $('#btnSubmitSingleExtract').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');
                $('#extractSingleGroupModal').modal('hide');

                if(res.success){
                    notify('success', res.message);
                    if(res.list_id){
                        setTimeout(() => {
                            window.location.href = "{{ url('admin/contacts/lists') }}/" + res.list_id;
                        }, 1000);
                    }
                } else {
                    notify('error', res.error || 'Failed to extract group members');
                }
            },
            error: function(xhr){
                $('#btnSubmitSingleExtract').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');
                let errMsg = 'Failed to extract group members.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

    // Copy Group Numbers
    $(document).on('click', '.btnCopyGroupNumbers', function(){
        const numbers = $(this).data('numbers');
        if(numbers && numbers.length > 0){
            const text = numbers.join(', ');
            navigator.clipboard.writeText(text).then(function(){
                notify('success', `Copied ${numbers.length} phone numbers to clipboard!`);
            });
        } else {
            notify('warning', 'No phone numbers available to copy.');
        }
    });

})(jQuery);
</script>
@endpush

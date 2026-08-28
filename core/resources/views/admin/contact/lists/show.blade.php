@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="card-title text-white mb-0 d-flex align-items-center me-2">
                        <i class="las la-folder-open me-2 fs-4 text--primary"></i> {{ __($list->name) }}
                    </h5>
                    @if($list->type === 'groups')
                        <span class="badge badge--warning"><i class="las la-users me-1"></i> @lang('WhatsApp Groups List')</span>
                    @elseif($list->type === 'contacts')
                        <span class="badge badge--primary"><i class="las la-user me-1"></i> @lang('Direct Contacts List')</span>
                    @else
                        <span class="badge badge--dark"><i class="las la-layer-group me-1"></i> @lang('Mixed List')</span>
                    @endif
                    <span class="badge badge--info">{{ $contacts->total() }} @lang('Total Items')</span>
                </div>

                <form action="{{ route('admin.contacts.lists.show', $list->id) }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="@lang('Search name, phone, group...')" value="{{ request('search') }}">
                        <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive--lg table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Name / Subject')</th>
                                <th>@lang('Phone / Group JID')</th>
                                <th>@lang('Group / Source')</th>
                                <th>@lang('Added At')</th>
                                <th class="text-end pe-4">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($contact->type === 'group')
                                            <span class="badge badge--warning"><i class="las la-users me-1"></i> @lang('Group')</span>
                                        @else
                                            <span class="badge badge--primary"><i class="las la-user me-1"></i> @lang('Contact')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="user">
                                            <div class="thumb me-2">
                                                @if($contact->type === 'group')
                                                    <i class="lab la-whatsapp text--success fs-4"></i>
                                                @else
                                                    <i class="las la-user-circle text--primary fs-4"></i>
                                                @endif
                                            </div>
                                            <span class="name fw-bold">{{ __($contact->name) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($contact->type === 'group')
                                            <span class="badge bg-light text-dark font-monospace border px-2 py-1" style="font-size: 11px;">
                                                {{ $contact->group_id ?: $contact->phone_number }}
                                            </span>
                                        @else
                                            <span class="badge badge--info fs-6">+{{ $contact->phone_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($contact->group_name)
                                            <span class="badge badge--dark px-2 py-1">{{ __($contact->group_name) }}</span>
                                        @else
                                            <span class="text-muted small">@lang('Direct')</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ showDateTime($contact->created_at) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1 flex-wrap">
                                            @if($contact->type === 'group' || $contact->group_id)
                                                <!-- Extract Members to New Contact List Button (No. 2 in user request) -->
                                                <button type="button" class="btn btn-sm btn-outline--success btnExtractMembersFromGroup" 
                                                    data-group-id="{{ $contact->group_id ?: $contact->phone_number }}"
                                                    data-group-name="{{ $contact->name ?: $contact->group_name }}"
                                                    title="@lang('Extract members of this group into a new named Contact List')">
                                                    <i class="las la-user-plus me-1"></i> @lang('Extract Members')
                                                </button>

                                                <button type="button" class="btn btn-sm btn-outline--info btnOpenGroupMessage" 
                                                    data-group-name="{{ $contact->group_name ?: $contact->name }}" 
                                                    data-group-id="{{ $contact->group_id ?: $contact->phone_number }}"
                                                    title="@lang('Send message to this group')">
                                                    <i class="lab la-whatsapp"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline--info btnDirectMessage" 
                                                    data-name="{{ $contact->name }}"
                                                    data-phone="{{ $contact->phone_number }}"
                                                    title="@lang('Send message to this contact')">
                                                    <i class="lab la-whatsapp"></i>
                                                </button>
                                            @endif

                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                                data-action="{{ route('admin.contacts.delete', $contact->id) }}"
                                                data-question="@lang('Are you sure you want to remove this contact from the list?')">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-address-book text--muted mb-3" style="font-size: 52px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No items in this contact list yet.')</h6>
                                            <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                                                <button type="button" class="btn btn-sm btn--primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                                    <i class="las la-plus me-1"></i>@lang('Add Contact')
                                                </button>
                                                <a href="{{ route('admin.contacts.import.csv.view') }}?list_id={{ $list->id }}" class="btn btn-sm btn-outline--dark">
                                                    <i class="las la-file-csv me-1"></i>@lang('Import CSV')
                                                </a>
                                                <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--success">
                                                    <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($contacts->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($contacts) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Extract Group Members to a New Contact List (No. 2) -->
<div id="extractMembersModal" class="modal fade" tabindex="-1" role="dialog">
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
            <form id="formExtractGroupMembers">
                @csrf
                <input type="hidden" id="ext_source_group_id" name="source_group_id">
                <input type="hidden" id="ext_source_group_name" name="source_group_name">
                <div class="modal-body p-4">
                    
                    <div class="alert alert-info border-0 mb-3 small d-flex align-items-center">
                        <i class="las la-info-circle fs-4 me-2"></i>
                        <span>@lang('All members of this WhatsApp group will be extracted with their real WhatsApp names and saved into a new Contact List.')</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Selected WhatsApp Account') <span class="text--danger">*</span></label>
                        <select id="ext_session_id" class="form-control form-select" required>
                            @forelse($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}">
                                    {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                </option>
                            @empty
                                <option value="" disabled selected>@lang('No active WhatsApp account connected')</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Target WhatsApp Group')</label>
                        <input type="text" id="ext_display_group_name" class="form-control bg-light font-weight-bold" readonly>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('New Contact List Name') <span class="text--danger">*</span></label>
                        <input type="text" name="list_name" id="ext_target_list_name" class="form-control" placeholder="@lang('e.g. Qamify Members')" required>
                        <small class="text-muted d-block mt-1"><i class="las la-pencil-alt me-1"></i>@lang('Defaults to group name. You can change it as desired.')</small>
                    </div>

                    <div id="extLoadingState" class="text-center py-3 d-none">
                        <div class="spinner-border text--primary mb-2" role="status"></div>
                        <p class="text-muted small mb-0">@lang('Extracting members from WhatsApp...')</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success btn-sm px-4 fw-bold" id="btnSubmitExtractMembers">
                        <i class="las la-file-import me-1"></i> @lang('Extract & Save List')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Single Contact to this List -->
<div id="addContactModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-user-plus me-2 fs-4"></i> @lang('Add Contact to') {{ $list->name }}
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.contacts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="contact_list_id" value="{{ $list->id }}">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Contact Name') <span class="text--danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="@lang('e.g. John Doe')" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('WhatsApp Phone Number') <span class="text--danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" placeholder="@lang('e.g. 923001234567')" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Group / Tag Name (Optional)')</label>
                        <input type="text" name="group_name" class="form-control" placeholder="@lang('e.g. Leads, Client')" value="{{ $list->name }}">
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Email Address (Optional)')</label>
                        <input type="email" name="email" class="form-control" placeholder="@lang('e.g. user@domain.com')">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary btn-sm px-4 fw-bold">
                        <i class="las la-save me-1"></i> @lang('Save Contact')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Send Group Message -->
<div id="contactGroupMessageModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--dark text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="lab la-whatsapp text--success me-2 fs-4"></i> @lang('Send Message to WhatsApp Group')
                </h5>
                <button type="button" class="close text-white bg-transparent border-0 fs-4" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form id="formContactGroupMessage">
                @csrf
                <input type="hidden" name="isGroup" value="1">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Send From Connected WhatsApp') <span class="text--danger">*</span></label>
                        <select name="session_id" id="contact_grp_session_id" class="form-control form-select" required>
                            @forelse($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}">
                                    {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Connected' }})
                                </option>
                            @empty
                                <option value="" disabled selected>@lang('No active WhatsApp account connected')</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Target WhatsApp Group')</label>
                        <input type="text" id="contact_grp_display" class="form-control bg-light font-weight-bold" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Group JID / ID') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver" id="contact_grp_id" class="form-control font-monospace" placeholder="@lang('e.g. 120363...@g.us')" required>
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold mb-1">@lang('Message Text') <span class="text--danger">*</span></label>
                        <textarea name="message" id="contact_grp_text" rows="4" class="form-control" placeholder="@lang('Type your message to the group here...')" required>Hello everyone! This is a message sent from Auto WhatsApp.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn-sm px-3" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success btn-sm px-3 fw-bold" id="btnSendContactGroupMsg">
                        <i class="las la-paper-plane me-1"></i> @lang('Send to Group')
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
        <i class="las la-arrow-left me-1"></i> @lang('All Lists')
    </a>
    <button type="button" class="btn btn-sm btn--primary me-2" data-bs-toggle="modal" data-bs-target="#addContactModal">
        <i class="las la-plus me-1"></i> @lang('Add Contact')
    </button>
    <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn--warning text-dark fw-bold">
        <i class="las la-bullhorn me-1"></i> @lang('Create Campaign')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    // Open Extract Members Modal
    $('.btnExtractMembersFromGroup').on('click', function(){
        const groupId = $(this).data('group-id');
        const groupName = $(this).data('group-name');

        $('#ext_source_group_id').val(groupId);
        $('#ext_source_group_name').val(groupName);
        $('#ext_display_group_name').val(groupName);
        $('#ext_target_list_name').val(groupName + ' Members');

        $('#extractMembersModal').modal('show');
    });

    // Handle Extract Members Submit
    $('#formExtractGroupMembers').on('submit', function(e){
        e.preventDefault();

        const sessionId = $('#ext_session_id').val();
        const groupId = $('#ext_source_group_id').val();
        const groupName = $('#ext_source_group_name').val();
        const targetListName = $('#ext_target_list_name').val().trim();

        if(!sessionId){
            notify('error', 'Please select a connected WhatsApp account');
            return;
        }

        if(!targetListName){
            notify('error', 'Please provide a name for the new Contact List');
            return;
        }

        $('#extLoadingState').removeClass('d-none');
        $('#btnSubmitExtractMembers').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Extracting...');

        // 1. Fetch group members from Baileys
        $.ajax({
            url: "{{ url('admin/account-listing/extract-groups') }}/" + sessionId,
            type: "GET",
            success: function(res){
                if(res.success && res.groups){
                    const matchedGroup = res.groups.find(g => g.id === groupId) || res.groups.find(g => g.subject === groupName);

                    if(matchedGroup && matchedGroup.participants && matchedGroup.participants.length > 0){
                        // 2. Save participants into target named Contact List
                        $.ajax({
                            url: "{{ route('admin.contacts.extract.group.members.list') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                list_name: targetListName,
                                source_group_id: groupId,
                                source_group_name: groupName,
                                participants: matchedGroup.participants
                            },
                            success: function(saveRes){
                                $('#extractMembersModal').modal('hide');
                                $('#extLoadingState').addClass('d-none');
                                $('#btnSubmitExtractMembers').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');

                                if(saveRes.success){
                                    notify('success', saveRes.message);
                                    if(saveRes.list_id){
                                        setTimeout(() => {
                                            window.location.href = "{{ url('admin/contacts/lists') }}/" + saveRes.list_id;
                                        }, 1000);
                                    }
                                } else {
                                    notify('error', saveRes.error || 'Failed to save extracted list.');
                                }
                            },
                            error: function(xhr){
                                $('#extLoadingState').addClass('d-none');
                                $('#btnSubmitExtractMembers').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');
                                let errMsg = 'Failed to save contacts to list.';
                                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                                notify('error', errMsg);
                            }
                        });
                    } else {
                        $('#extLoadingState').addClass('d-none');
                        $('#btnSubmitExtractMembers').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');
                        notify('warning', 'No participants found in this group.');
                    }
                } else {
                    $('#extLoadingState').addClass('d-none');
                    $('#btnSubmitExtractMembers').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');
                    notify('error', 'Could not retrieve group data from WhatsApp.');
                }
            },
            error: function(){
                $('#extLoadingState').addClass('d-none');
                $('#btnSubmitExtractMembers').prop('disabled', false).html('<i class="las la-file-import me-1"></i> Extract & Save List');
                notify('error', 'Error connecting to WhatsApp session.');
            }
        });
    });

    // Message Group Modal
    $('.btnOpenGroupMessage').on('click', function(){
        const groupName = $(this).data('group-name') || '';
        const groupId = $(this).data('group-id') || '';

        $('#contact_grp_display').val(groupName);
        $('#contact_grp_id').val(groupId);
        $('#contactGroupMessageModal').modal('show');
    });

    $('#formContactGroupMessage').on('submit', function(e){
        e.preventDefault();

        const sessionId = $('#contact_grp_session_id').val();
        const receiver = $('#contact_grp_id').val().trim();
        const message = $('#contact_grp_text').val().trim();

        if(!sessionId){
            notify('error', 'Please select an active WhatsApp account');
            return;
        }

        $('#btnSendContactGroupMsg').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');

        $.ajax({
            url: "{{ route('admin.account.listing.test.message') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                session_id: sessionId,
                receiver: receiver,
                message: message,
                isGroup: 1
            },
            success: function(res){
                $('#btnSendContactGroupMsg').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send to Group');
                if(res.status === 'success'){
                    $('#contactGroupMessageModal').modal('hide');
                    notify('success', 'Message posted to WhatsApp group successfully!');
                } else {
                    notify('error', res.error || 'Failed to send message to group.');
                }
            },
            error: function(xhr){
                $('#btnSendContactGroupMsg').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send to Group');
                let errMsg = 'Failed to send message to group.';
                if(xhr.responseJSON && xhr.responseJSON.error) errMsg = xhr.responseJSON.error;
                notify('error', errMsg);
            }
        });
    });

})(jQuery);
</script>
@endpush

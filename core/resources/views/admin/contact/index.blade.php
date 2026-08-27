@extends('admin.layouts.app')

@section('panel')

<!-- Groups Overview Cards / Badges -->
@if($groups->count() > 0)
<div class="row mb-4 gy-3">
    <div class="col-12">
        <div class="card b-radius--10 shadow-sm border-0">
            <div class="card-header bg--primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="card-title text-white mb-0 d-flex align-items-center">
                    <i class="las la-layer-group me-2 fs-4"></i> @lang('WhatsApp Groups & Tags')
                </h5>
                <span class="badge bg-white text--primary fs-6">{{ $groups->count() }} @lang('Groups Found')</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    @foreach($groups as $grp)
                        <div class="col-xxl-3 col-xl-4 col-md-6">
                            <div class="p-3 rounded border bg-light h-100 d-flex flex-column justify-content-between {{ ($selectedGroup == $grp->group_name || $selectedGroup == $grp->group_id) ? 'border-primary shadow-sm bg-white' : '' }}">
                                <div>
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center me-2">
                                            <i class="lab la-whatsapp text--success fs-4 me-2"></i>
                                            <strong class="text-dark d-block text-truncate" style="max-width: 170px;" title="{{ $grp->group_name ?: 'Unnamed Group' }}">
                                                {{ $grp->group_name ?: trans('Unnamed Group') }}
                                            </strong>
                                        </div>
                                        <span class="badge badge--info">{{ $grp->total_count }} @lang('Contacts')</span>
                                    </div>
                                    
                                    <div class="small text-muted mb-3">
                                        <span class="d-block fw-bold text-secondary">@lang('Group ID'):</span>
                                        <span class="font-monospace text-dark text-break" style="font-size: 11px;">
                                            {{ $grp->group_id ?: trans('N/A') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline--info py-1 px-2 btnOpenGroupMessage" 
                                        data-group-name="{{ $grp->group_name }}" 
                                        data-group-id="{{ $grp->group_id }}"
                                        title="@lang('Send message to this group')">
                                        <i class="lab la-whatsapp me-1"></i>@lang('Message')
                                    </button>

                                    <a href="{{ route('admin.contacts.index', ['group' => $grp->group_name, 'group_id' => $grp->group_id]) }}" class="btn btn-sm btn-outline--primary py-1 px-2" title="@lang('Filter this group members')">
                                        <i class="las la-filter me-1"></i>@lang('Members')
                                    </a>

                                    <button type="button" class="btn btn-sm btn-outline--danger py-1 px-2 confirmationBtn"
                                        data-action="{{ route('admin.contacts.delete.group') }}?group_id={{ urlencode($grp->group_id) }}&group_name={{ urlencode($grp->group_name) }}"
                                        data-question="@lang('Are you sure you want to delete all contacts in this group?')"
                                        title="@lang('Delete Group')">
                                        <i class="las la-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Contact List Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
            <div class="card-header bg--dark text-white d-flex flex-wrap align-items-center justify-content-between py-3 gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title text-white mb-0 d-flex align-items-center">
                        <i class="las la-address-book me-2 fs-4 text--primary"></i> @lang('Contact List')
                    </h5>
                    @if($selectedGroup)
                        <span class="badge badge--warning">@lang('Filtered Group'): {{ $selectedGroup }}</span>
                        <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light py-0 px-2">
                            <i class="las la-times"></i> @lang('Clear Filter')
                        </a>
                    @endif
                </div>

                <form action="{{ route('admin.contacts.index') }}" method="GET" class="d-flex gap-2">
                    @if($selectedGroup)
                        <input type="hidden" name="group" value="{{ request('group') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                    @endif
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
                                <th>@lang('Name')</th>
                                <th>@lang('Phone Number')</th>
                                <th>@lang('Group Name / Tag')</th>
                                <th>@lang('Group ID / JID')</th>
                                <th>@lang('Email')</th>
                                <th>@lang('Added At')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr>
                                    <td>
                                        <div class="user">
                                            <div class="thumb me-2">
                                                <i class="las la-user-circle text--primary fs-4"></i>
                                            </div>
                                            <span class="name fw-bold">{{ __($contact->name) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge--info fs-6">+{{ $contact->phone_number }}</span>
                                    </td>
                                    <td>
                                        @if($contact->group_name)
                                            <span class="badge badge--dark px-2 py-1">{{ __($contact->group_name) }}</span>
                                        @else
                                            <span class="text-muted">@lang('None')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($contact->group_id)
                                            <span class="badge bg-light text-dark font-monospace border px-2 py-1" style="font-size: 11px;">
                                                {{ $contact->group_id }}
                                            </span>
                                        @else
                                            <span class="text-muted small">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $contact->email ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        {{ showDateTime($contact->created_at) }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                            data-action="{{ route('admin.contacts.delete', $contact->id) }}"
                                            data-question="@lang('Are you sure you want to remove this contact?')">
                                            <i class="las la-trash me-1"></i>@lang('Delete')
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center py-5" colspan="100%">
                                        <div class="empty-state">
                                            <i class="las la-address-book text--muted mb-3" style="font-size: 52px;"></i>
                                            <h6 class="text-muted mb-2">@lang('No contacts found.')</h6>
                                            <div class="d-flex justify-content-center gap-2 mt-3">
                                                <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn--success">
                                                    <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
                                                </a>
                                                <a href="{{ route('admin.contacts.create') }}" class="btn btn-sm btn--primary">
                                                    <i class="las la-plus me-1"></i>@lang('Add Contact')
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

<!-- Send Group Message Modal -->
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
                        <label class="fw-bold mb-1">@lang('Target WhatsApp Group Name')</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="las la-users text--primary fs-5"></i></span>
                            <input type="text" id="contact_grp_display" class="form-control border-start-0" style="background-color: #f8fafc !important; color: #1e293b !important; font-weight: 600;" readonly>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Group JID / ID') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver" id="contact_grp_id" class="form-control font-monospace" placeholder="@lang('e.g. 120363...@g.us')" required>
                        <small class="text-muted d-block mt-1"><i class="las la-info-circle me-1"></i>@lang('WhatsApp Group JID (ends with @g.us)')</small>
                    </div>

                    <div class="form-group mb-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="fw-bold mb-0">@lang('Message Text') <span class="text--danger">*</span></label>
                            <div class="dropdown">
                                <button class="btn btn-xs btn-outline--secondary dropdown-toggle btnLoadTemplates" type="button" data-bs-toggle="dropdown">
                                    <i class="las la-envelope-open-text me-1"></i> @lang('Insert Template')
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end templateDropdownMenu" style="max-height: 220px; overflow-y: auto;">
                                    <li><span class="dropdown-item text-muted small">@lang('Loading...')</span></li>
                                </ul>
                            </div>
                        </div>
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
    <a href="{{ route('admin.contacts.sync') }}" class="btn btn-sm btn-outline--success me-2">
        <i class="lab la-whatsapp me-1"></i>@lang('Sync from WhatsApp')
    </a>
    <a href="{{ route('admin.contacts.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New Contact')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    $('.btnOpenGroupMessage').on('click', function(){
        const groupName = $(this).data('group-name') || '';
        let groupId = $(this).data('group-id') || '';

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

        if(!receiver){
            notify('error', 'Please provide the WhatsApp Group JID');
            return;
        }

        if(!message){
            notify('error', 'Please enter a message to send');
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
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    // Load templates into dropdown
    $(document).on('click', '.btnLoadTemplates', function(){
        const dropdownMenu = $(this).closest('.dropdown').find('.templateDropdownMenu');
        $.ajax({
            url: "{{ route('admin.templates.list.json') }}",
            type: "GET",
            success: function(res){
                dropdownMenu.empty();
                if(res.success && res.templates && res.templates.length > 0){
                    res.templates.forEach(tpl => {
                        const item = `
                            <li>
                                <a class="dropdown-item py-2 border-bottom btnInsertTemplate" href="javascript:void(0)" data-msg="${encodeURIComponent(tpl.message)}">
                                    <strong class="d-block text-dark">${tpl.title}</strong>
                                    <small class="text-muted d-block text-truncate" style="max-width: 260px;">${tpl.message}</small>
                                </a>
                            </li>
                        `;
                        dropdownMenu.append(item);
                    });
                } else {
                    dropdownMenu.append('<li><span class="dropdown-item text-muted small">No templates saved yet.</span></li>');
                }
            }
        });
    });

    $(document).on('click', '.btnInsertTemplate', function(e){
        e.preventDefault();
        const msg = decodeURIComponent($(this).data('msg'));
        $('#contact_grp_text').val(msg);
    });

})(jQuery);
</script>
@endpush

@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 shadow-sm">
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
                                                title="@lang('Extract WhatsApp Groups & Contacts')">
                                                <i class="las la-users-cog fs-6"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline--success btnTestMessage"
                                                data-session_id="{{ $account->session_id }}"
                                                data-name="{{ $account->account_name }}"
                                                data-phone="{{ $account->phone_number }}"
                                                data-bs-toggle="tooltip"
                                                title="@lang('Send Test WhatsApp Message')">
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

<!-- Test Message Modal -->
<div id="testMessageModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg--dark text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="lab la-whatsapp text--success me-2 fs-4"></i> @lang('Send Test WhatsApp Message')
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
                            <input type="text" id="modal_sender_display" class="form-control border-start-0" style="background-color: #f8fafc !important; color: #1e293b !important; font-weight: 600; opacity: 1;" readonly>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">@lang('Recipient WhatsApp Number') <span class="text--danger">*</span></label>
                        <input type="text" name="receiver" id="modal_receiver" class="form-control" placeholder="@lang('e.g. 923001234567 (with country code)')" required>
                        <small class="text-muted d-block mt-1">
                            <i class="las la-globe me-1"></i>@lang('Include country code without + or special characters')
                        </small>
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary text-white">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="las la-users-cog me-2 fs-4"></i> @lang('Extracted WhatsApp Groups')
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
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">
                            @lang('Total Groups Found'): <span class="badge badge--success fs-6" id="totalGroupsCount">0</span>
                        </h6>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>@lang('Group Name')</th>
                                    <th>@lang('Total Members')</th>
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

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.account.listing.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus me-1"></i>@lang('Add New WhatsApp Account')
    </a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

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
            notify('error', 'Please enter recipient WhatsApp number');
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
                    notify('success', res.message || 'Test message sent successfully!');
                } else {
                    notify('error', res.error || 'Failed to send message.');
                }
            },
            error: function(xhr){
                $('#btnModalSendMessage').prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Message');
                let errMsg = 'Failed to send message.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    // Group Extractor
    $('.btnExtractGroups').on('click', function(){
        const sessionId = $(this).data('session_id');
        const name = $(this).data('name');

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
                    $('#totalGroupsCount').text(res.groups.length);
                    $('#groupContent').removeClass('d-none');

                    res.groups.forEach((g, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <strong class="d-block text-dark">${g.subject}</strong>
                                    <small class="text-muted">${g.id}</small>
                                </td>
                                <td>
                                    <span class="badge badge--info fs-6">${g.participantsCount} Members</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline--primary btnImportGroupContacts" data-group-name="${encodeURIComponent(g.subject)}" data-group-id="${g.id}" data-participants='${JSON.stringify(g.participants)}' title="Import group members directly into Contact List">
                                            <i class="las la-file-import me-1"></i> Import Contacts
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline--success btnCopyGroupNumbers" data-numbers='${JSON.stringify(g.participants.map(p => p.phone).filter(Boolean))}' title="Copy Member Phone Numbers">
                                            <i class="las la-copy me-1"></i> Copy
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
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    $(document).on('click', '.btnImportGroupContacts', function(){
        const btn = $(this);
        const groupName = decodeURIComponent(btn.data('group-name'));
        const groupId = btn.data('group-id');
        const participants = btn.data('participants');

        if(!participants || participants.length === 0){
            notify('warning', 'No participants found in this group.');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Importing...');

        $.ajax({
            url: "{{ route('admin.contacts.import.group') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                group_name: groupName,
                group_id: groupId,
                participants: participants
            },
            success: function(res){
                btn.prop('disabled', false).html('<i class="las la-check text--success me-1"></i> Imported');
                if(res.success){
                    notify('success', res.message);
                } else {
                    notify('error', res.error || 'Failed to import contacts');
                }
            },
            error: function(xhr){
                btn.prop('disabled', false).html('<i class="las la-file-import me-1"></i> Import Contacts');
                let errMsg = 'Failed to import group contacts.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    errMsg = xhr.responseJSON.error;
                }
                notify('error', errMsg);
            }
        });
    });

    $(document).on('click', '.btnCopyGroupNumbers', function(){
        const numbers = $(this).data('numbers');
        if(numbers && numbers.length > 0){
            const text = numbers.join(', ');
            navigator.clipboard.writeText(text).then(function(){
                notify('success', `Copied ${numbers.length} member phone numbers to clipboard!`);
            });
        } else {
            notify('warning', 'No phone numbers available to copy.');
        }
    });

})(jQuery);
</script>
@endpush

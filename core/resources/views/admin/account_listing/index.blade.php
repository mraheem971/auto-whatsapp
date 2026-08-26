@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--lg table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>@lang('Account Name')</th>
                                <th>@lang('Phone Number')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Connected At')</th>
                                <th>@lang('Action')</th>
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
                                    <td>
                                        <div class="button--group">
                                            <button type="button" class="btn btn-sm btn-outline--success btnTestMessage"
                                                data-session_id="{{ $account->session_id }}"
                                                data-name="{{ $account->account_name }}"
                                                data-phone="{{ $account->phone_number }}"
                                                title="@lang('Send Test WhatsApp Message')">
                                                <i class="las la-paper-plane me-1"></i>@lang('Test Message')
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" 
                                                data-action="{{ route('admin.account.listing.delete', $account->id) }}"
                                                data-question="@lang('Are you sure you want to remove and disconnect this WhatsApp account?')">
                                                <i class="las la-trash me-1"></i>@lang('Delete')
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
                <div class="modal-body">
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
                    <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success btn-sm" id="btnModalSendMessage">
                        <i class="las la-paper-plane me-1"></i> @lang('Send Message')
                    </button>
                </div>
            </form>
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

})(jQuery);
</script>
@endpush

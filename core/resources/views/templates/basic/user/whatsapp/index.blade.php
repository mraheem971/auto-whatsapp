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
                                        <div class="fw-bold text-dark">{{ $acc->account_name }}</div>
                                    </td>
                                    <td>
                                        @if($acc->phone_number)
                                            <span class="fw-bold text-dark">+{{ $acc->phone_number }}</span>
                                        @else
                                            <span class="text-muted">Unlinked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small">{{ $acc->session_id }}</code>
                                    </td>
                                    <td>
                                        @if($acc->status == 1)
                                            <span class="badge bg-success"><i class="las la-check-circle me-1"></i> Connected & Online</span>
                                        @else
                                            <span class="badge bg-warning"><i class="las la-hourglass-half me-1"></i> Pending Connection</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @if($acc->status == 1)
                                                <button type="button" class="btn btn-outline-info btn-sm btnTestMessage" data-session="{{ $acc->session_id }}" title="Send Test Message">
                                                    <i class="las la-paper-plane"></i> Test Message
                                                </button>
                                            @endif
                                            <form action="{{ route('user.whatsapp.delete', $acc->id) }}" method="POST" onsubmit="return confirm('Disconnect and remove this WhatsApp account?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Disconnect">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="lab la-whatsapp text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No WhatsApp accounts linked yet</h6>
                                        <p class="text-muted small">Connect your first WhatsApp device using QR code or Pairing Code.</p>
                                        <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm">Connect Account</a>
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

    })(jQuery);
</script>
@endpush

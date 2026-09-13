@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Direct WhatsApp Messaging & Logs</h4>
                <p class="text-muted mb-0">Send 1-to-1 personalized WhatsApp messages and inspect real-time message delivery logs.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn--base" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                    <i class="las la-paper-plane me-1"></i> Send New Message
                </button>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="row gy-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card custom--card p-3 p-md-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Total Sent</span>
                        <div class="avatar avatar--sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="las la-check-double fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ number_format($totalSent) }}</h3>
                    <small class="text-success"><i class="las la-check-circle me-1"></i>Delivered Messages</small>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card custom--card p-3 p-md-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Failed / Errors</span>
                        <div class="avatar avatar--sm bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="las la-times-circle fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ number_format($totalFailed) }}</h3>
                    <small class="text-danger"><i class="las la-exclamation-triangle me-1"></i>Failed Deliveries</small>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card custom--card p-3 p-md-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Active Accounts</span>
                        <div class="avatar avatar--sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="lab la-whatsapp fs-3"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ $accounts->count() }}</h3>
                    <small class="text-primary"><i class="las la-wifi me-1"></i>Sender Devices</small>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card custom--card p-3 p-md-4 border shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Saved Templates</span>
                        <div class="avatar avatar--sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="las la-envelope-open-text fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ $templates->count() }}</h3>
                    <small class="text-info"><i class="las la-file-alt me-1"></i>Reusable Snippets</small>
                </div>
            </div>
        </div>

        <!-- Filter & Message Logs Table -->
        <div class="card custom--card border shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h5 class="card-title mb-0 fw-bold"><i class="las la-history text--base me-1"></i> Message Logs & History</h5>
                <form action="" method="GET" class="d-flex align-items-center gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search phone or text..." value="{{ request('search') }}" style="width: 200px;">
                    <select name="status" class="form-select form-select-sm" style="width: 120px;">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn--base"><i class="las la-search"></i></button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('user.messages.index') }}" class="btn btn-sm btn-outline-secondary"><i class="las la-undo"></i></a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Recipient</th>
                                <th>Sender Account</th>
                                <th>Message Content</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Dispatched At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $msg)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">+{{ $msg->receiver }}</div>
                                        <small class="text-muted">{{ $msg->receiver_name ?? 'Individual' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $msg->device_name }}</div>
                                        <small class="text-muted">+{{ $msg->sender_phone }}</small>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-truncate" style="max-width: 260px;">
                                            {{ $msg->message }}
                                        </div>
                                        @if($msg->error_message && $msg->status == 'failed')
                                            <small class="text-danger d-block mt-1">{{ $msg->error_message }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $msg->media_badge !!}
                                    </td>
                                    <td>
                                        @if($msg->status == 'sent' || $msg->status == 'delivered')
                                            <span class="badge bg-success"><i class="las la-check-double me-1"></i> Sent</span>
                                        @else
                                            <span class="badge bg-danger"><i class="las la-times-circle me-1"></i> Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ showDateTime($msg->created_at, 'M d, Y h:i A') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="las la-comments text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No message logs recorded yet</h6>
                                        <p class="text-muted small">Send your first direct WhatsApp message using the button above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($messages->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($messages) }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Send Message -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-paper-plane me-1"></i> Send WhatsApp Message</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.messages.send') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Sender Account <span class="text-danger">*</span></label>
                        <select name="session_id" class="form-select" required>
                            <option value="">Select Connected Account</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->session_id }}">{{ $acc->account_name }} (+{{ $acc->phone_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Recipient Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="las la-phone"></i></span>
                            <input type="text" name="receiver" class="form-control" placeholder="e.g. 923001234567" required>
                        </div>
                        <small class="text-muted fs-8">Enter digits with country code, no + or spaces.</small>
                    </div>

                    @if($templates->count() > 0)
                        <div class="mb-3">
                            <label class="fw-bold small mb-1">Insert From Template</label>
                            <select class="form-select form-select-sm" id="templateSelector">
                                <option value="">-- Choose Template --</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->content }}">{{ $tmpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Message Text <span class="text-danger">*</span></label>
                        <textarea name="message" id="messageBox" rows="4" class="form-control" placeholder="Type your WhatsApp message..." required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="fw-bold small mb-1">Media URL (Optional)</label>
                            <input type="url" name="media_url" class="form-control" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="col-4">
                            <label class="fw-bold small mb-1">Media Type</label>
                            <select name="media_type" class="form-select">
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="document">Document</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--base"><i class="las la-paper-plane me-1"></i> Dispatch Message</button>
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
        $('#templateSelector').on('change', function () {
            var content = $(this).val();
            if (content) {
                $('#messageBox').val(content);
            }
        });
    })(jQuery);
</script>
@endpush
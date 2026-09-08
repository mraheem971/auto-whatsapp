@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar--sm bg--primary-transparent text--primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="las la-history fs-4"></i>
                    </span>
                    <div>
                        <h6 class="card-title mb-0 fw-bold">Device Sent Message History</h6>
                        <small class="text-muted">Complete log of all messages dispatched from your Android WhatsApp devices</small>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('admin.device.sender.index') }}" class="btn btn--primary btn-sm">
                        <i class="las la-paper-plane me-1"></i> Compose New Message
                    </a>
                    @if($messages->isNotEmpty())
                    <button type="button" class="btn btn-outline--danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                        <i class="las la-trash me-1"></i> Clear All Logs
                    </button>
                    @endif
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card-body bg-light border-bottom p-3">
                <form action="" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search number, name, or text..." value="{{ request()->search }}">
                    </div>
                    <div class="col-md-3">
                        <select name="session_id" class="form-select form-select-sm">
                            <option value="">All Devices / Accounts</option>
                            @foreach($connectedAccounts as $acc)
                                <option value="{{ $acc->session_id }}" {{ request()->session_id == $acc->session_id ? 'selected' : '' }}>
                                    📱 {{ $acc->account_name }} ({{ $acc->phone_number ? '+' . $acc->phone_number : 'Active' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="sent" {{ request()->status == 'sent' ? 'selected' : '' }}>Sent / Delivered</option>
                            <option value="pending" {{ request()->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request()->status == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="media_type" class="form-select form-select-sm">
                            <option value="">All Media Types</option>
                            <option value="text" {{ request()->media_type == 'text' ? 'selected' : '' }}>Text Only</option>
                            <option value="image" {{ request()->media_type == 'image' ? 'selected' : '' }}>Image</option>
                            <option value="video" {{ request()->media_type == 'video' ? 'selected' : '' }}>Video</option>
                            <option value="document" {{ request()->media_type == 'document' ? 'selected' : '' }}>Document / PDF</option>
                            <option value="audio" {{ request()->media_type == 'audio' ? 'selected' : '' }}>Audio</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn--primary btn-sm flex-grow-1"><i class="las la-filter me-1"></i> Filter</button>
                        <a href="{{ route('admin.device.sender.logs') }}" class="btn btn-outline--secondary btn-sm"><i class="las la-redo"></i></a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Device / Sender</th>
                                <th>Recipient</th>
                                <th>Message / Media</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $msg)
                                <tr>
                                    <td>{{ $messages->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $msg->device_name ?: 'Android Device' }}</div>
                                        <small class="text-muted font-monospace">{{ $msg->sender_phone ? '+' . $msg->sender_phone : 'Session' }}</small>
                                    </td>
                                    <td>
                                        @if($msg->is_group)
                                            <span class="badge bg-secondary text-xs"><i class="las la-users me-1"></i> Group</span>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 160px;">{{ $msg->group_name ?: $msg->receiver }}</div>
                                        @else
                                            <div class="fw-bold text-dark">{{ $msg->receiver_name ?: 'Contact' }}</div>
                                            <span class="font-monospace text-muted small">{{ $msg->receiver }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 220px;" title="{{ $msg->message }}">
                                            {{ $msg->message ?: 'No text content' }}
                                        </div>
                                        @if($msg->media_url)
                                            <small class="d-block mt-1">
                                                <a href="{{ $msg->media_url }}" target="_blank" class="text--primary text-xs">
                                                    <i class="las la-external-link-alt me-1"></i> View Attached {{ ucfirst($msg->media_type) }}
                                                </a>
                                            </small>
                                        @endif
                                        @if($msg->error_message)
                                            <small class="text-danger d-block text-xs mt-1 text-truncate" style="max-width: 220px;" title="{{ $msg->error_message }}">
                                                <i class="las la-exclamation-circle me-1"></i>{{ $msg->error_message }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{!! $msg->media_badge !!}</td>
                                    <td>{!! $msg->status_badge !!}</td>
                                    <td>
                                        <div>{{ showDateTime($msg->created_at, 'd M Y, h:i A') }}</div>
                                        <small class="text-muted text-xs">{{ $msg->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="{{ route('admin.device.sender.resend', $msg->id) }}" class="btn btn-xs btn--success" title="Resend Message">
                                                <i class="las la-redo"></i>
                                            </a>
                                            <button type="button" class="btn btn-xs btn--danger btn-delete-log" data-action="{{ route('admin.device.sender.delete', $msg->id) }}" title="Delete Log">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">
                                        <div class="py-4">
                                            <i class="las la-comment-slash fs-1 text-muted d-block mb-2"></i>
                                            No outgoing messages found matching your criteria.
                                        </div>
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

<!-- Modal: Clear All Logs Confirmation -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold text-danger"><i class="las la-trash me-1"></i> Clear All Logs?</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <p class="text-muted small mb-0">Are you sure you want to delete all sent message history? This action cannot be undone.</p>
            </div>
            <div class="modal-footer py-2">
                <form action="{{ route('admin.device.sender.clear') }}" method="POST">
                    @csrf
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--danger btn-sm">Yes, Clear All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Delete Single Log -->
<div class="modal fade" id="deleteSingleLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold text-danger"><i class="las la-trash me-1"></i> Delete Message Log</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <p class="text-muted small mb-0">Are you sure you want to remove this message log?</p>
            </div>
            <div class="modal-footer py-2">
                <form id="deleteSingleForm" action="" method="POST">
                    @csrf
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function($){
    "use strict";
    $('.btn-delete-log').on('click', function(){
        const action = $(this).data('action');
        $('#deleteSingleForm').attr('action', action);
        $('#deleteSingleLogModal').modal('show');
    });
})(jQuery);
</script>
@endpush

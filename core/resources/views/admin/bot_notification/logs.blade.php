@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
            <div>
                <h4 class="mb-1">Notification Logs & Error Audit</h4>
                <p class="text-muted mb-0">Complete audit trail of all sent notifications, user error escalations, and system alerts.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bot.notifications.index') }}" class="btn btn-outline--dark btn-sm">
                    <i class="las la-arrow-left me-1"></i> Dashboard
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                    <i class="las la-trash me-1"></i> Clear All Logs
                </button>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <form action="{{ route('admin.bot.notifications.logs') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4 col-sm-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="las la-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search phone, message, title, error..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <select name="event_type" class="form-select">
                        <option value="">-- All Event Types --</option>
                        <option value="user_error_escalation" {{ request('event_type') == 'user_error_escalation' ? 'selected' : '' }}>User Error Escalation</option>
                        <option value="system_error" {{ request('event_type') == 'system_error' ? 'selected' : '' }}>System Error</option>
                        <option value="session_disconnect" {{ request('event_type') == 'session_disconnect' ? 'selected' : '' }}>Session Disconnect</option>
                        <option value="scheduled_reminder" {{ request('event_type') == 'scheduled_reminder' ? 'selected' : '' }}>Scheduled Reminder</option>
                        <option value="incoming_message" {{ request('event_type') == 'incoming_message' ? 'selected' : '' }}>Incoming Message</option>
                        <option value="custom_alert" {{ request('event_type') == 'custom_alert' ? 'selected' : '' }}>Custom Alert</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <select name="status" class="form-select">
                        <option value="">-- All Statuses --</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent (Success)</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn--primary w-100"><i class="las la-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.bot.notifications.logs') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="las la-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="col-12">
        <div class="card border shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Event Type</th>
                                <th>Recipient</th>
                                <th>Title & Content</th>
                                <th>Status</th>
                                <th>Retries</th>
                                <th>Logged At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        @if($log->event_type === 'user_error_escalation')
                                            <span class="badge bg-warning text-dark"><i class="las la-user-shield me-1"></i> User Escalation</span>
                                        @elseif($log->event_type === 'system_error')
                                            <span class="badge bg-danger"><i class="las la-exclamation-triangle me-1"></i> System Error</span>
                                        @elseif($log->event_type === 'session_disconnect')
                                            <span class="badge bg-dark"><i class="las la-unlink me-1"></i> Disconnected</span>
                                        @elseif($log->event_type === 'scheduled_reminder')
                                            <span class="badge bg-info"><i class="las la-clock me-1"></i> Scheduled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $log->event_type)) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">+{{ $log->recipient }}</div>
                                        <small class="text-muted text-capitalize">{{ $log->recipient_type }}</small>
                                    </td>
                                    <td>
                                        @if($log->title)
                                            <div class="fw-bold text-dark small">{{ $log->title }}</div>
                                        @endif
                                        <div class="text-muted small text-truncate" style="max-width: 320px;">
                                            {{ $log->message }}
                                        </div>
                                        @if($log->error_details)
                                            <div class="text-danger small mt-1">
                                                <i class="las la-bug me-1"></i> <strong>Error:</strong> {{ \Illuminate\Support\Str::limit($log->error_details, 60) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'sent')
                                            <span class="badge bg-success"><i class="las la-check-circle me-1"></i> Sent</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger"><i class="las la-times-circle me-1"></i> Failed</span>
                                        @else
                                            <span class="badge bg-warning"><i class="las la-hourglass-half me-1"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $log->retry_count }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ showDateTime($log->created_at) }}</div>
                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- View Details Modal Trigger -->
                                            <button type="button" class="btn btn-outline-primary btnViewDetail" 
                                                    data-title="{{ $log->title }}" 
                                                    data-event="{{ $log->event_type }}" 
                                                    data-recipient="+{{ $log->recipient }}" 
                                                    data-status="{{ $log->status }}" 
                                                    data-message="{{ $log->message }}" 
                                                    data-error="{{ $log->error_details }}" 
                                                    data-metadata="{{ json_encode($log->metadata) }}"
                                                    data-time="{{ showDateTime($log->created_at) }}"
                                                    title="View Full Detail">
                                                <i class="las la-eye"></i>
                                            </button>

                                            <!-- Resend If Failed -->
                                            @if($log->status === 'failed')
                                                <a href="{{ route('admin.bot.notifications.resend.log', $log->id) }}" class="btn btn-outline-success" title="Resend Notification Now">
                                                    <i class="las la-redo-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="las la-inbox text-muted fs-1 d-block mb-2"></i>
                                        <h6 class="text-muted">No notification logs recorded yet</h6>
                                        <p class="text-muted small">Notifications dispatched via triggers, errors, or schedules will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ paginateLinks($logs) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: View Log Details -->
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-info-circle me-1"></i> Notification Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Event Type</label>
                        <div id="modalEvent" class="fw-bold"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Recipient</label>
                        <div id="modalRecipient" class="fw-bold"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Status</label>
                        <div id="modalStatus"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Logged Timestamp</label>
                        <div id="modalTime" class="fw-bold"></div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-bold">Title</label>
                        <div id="modalTitle" class="fw-bold"></div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-bold">Full Message Content</label>
                        <div class="bg-light p-3 rounded border font-monospace text-dark small" id="modalMessage" style="white-space: pre-wrap;"></div>
                    </div>
                    <div class="col-12 d-none" id="modalErrorContainer">
                        <label class="text-danger small fw-bold">Error Trace / Details</label>
                        <div class="bg-danger bg-opacity-10 p-3 rounded border border-danger font-monospace text-danger small" id="modalError" style="white-space: pre-wrap;"></div>
                    </div>
                    <div class="col-12 d-none" id="modalMetadataContainer">
                        <label class="text-muted small fw-bold">Metadata Payload</label>
                        <pre class="bg-light p-3 rounded border small mb-0" id="modalMetadata"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Clear Logs Confirmation -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title text-white fw-bold"><i class="las la-trash me-1"></i> Clear All Logs</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bot.notifications.clear.logs') }}" method="POST">
                @csrf
                <div class="modal-body text-center p-4">
                    <p class="mb-0">Are you sure you want to delete and truncate <strong>all notification logs</strong>? This cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Yes, Clear All</button>
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

        $('.btnViewDetail').on('click', function () {
            var title = $(this).data('title') || 'N/A';
            var event = $(this).data('event');
            var recipient = $(this).data('recipient');
            var status = $(this).data('status');
            var message = $(this).data('message');
            var error = $(this).data('error');
            var metadata = $(this).data('metadata');
            var time = $(this).data('time');

            $('#modalTitle').text(title);
            $('#modalEvent').text(event);
            $('#modalRecipient').text(recipient);
            $('#modalTime').text(time);
            $('#modalMessage').text(message);

            if (status === 'sent') {
                $('#modalStatus').html('<span class="badge bg-success">Sent</span>');
            } else if (status === 'failed') {
                $('#modalStatus').html('<span class="badge bg-danger">Failed</span>');
            } else {
                $('#modalStatus').html('<span class="badge bg-warning">Pending</span>');
            }

            if (error) {
                $('#modalError').text(error);
                $('#modalErrorContainer').removeClass('d-none');
            } else {
                $('#modalErrorContainer').addClass('d-none');
            }

            if (metadata && metadata !== '[]' && metadata !== '{}') {
                try {
                    var parsed = typeof metadata === 'string' ? JSON.parse(metadata) : metadata;
                    $('#modalMetadata').text(JSON.stringify(parsed, null, 2));
                    $('#modalMetadataContainer').removeClass('d-none');
                } catch (e) {
                    $('#modalMetadataContainer').addClass('d-none');
                }
            } else {
                $('#modalMetadataContainer').addClass('d-none');
            }

            $('#logDetailModal').modal('show');
        });

    })(jQuery);
</script>
@endpush

@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    
    <!-- Top Hero Banner -->
    <div class="col-12">
        <div class="card bg--dark text-white border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success text-white fw-bold px-3 py-1 text-uppercase">Notification Engine</span>
                        <span class="badge bg-primary text-white px-2 py-1"><i class="las la-bell me-1"></i>Active & Monitoring</span>
                    </div>
                    <h3 class="text-white fw-bold mb-1">WhatsApp Bot Notification & Escalation System</h3>
                    <p class="text-white text-opacity-75 mb-0">
                        Event-triggered alerts, error escalation to Admin WhatsApp, and scheduled recurring tasks.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn--success btn-sm px-3" id="btnTestAdminAlert">
                        <i class="las la-paper-plane me-1"></i> Send Test Alert to Admin
                    </button>
                    <a href="{{ route('admin.bot.notifications.settings') }}" class="btn btn-outline-light btn-sm px-3">
                        <i class="las la-cog me-1"></i> Preferences
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 border shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold">Admin Alert Recipient</span>
                <i class="lab la-whatsapp text--success fs-4"></i>
            </div>
            <h5 class="fw-bold text-dark mb-0">
                {{ $settings->admin_whatsapp_number ? '+' . $settings->admin_whatsapp_number : 'Not Configured' }}
            </h5>
            <small class="text-muted">Receives instant error escalations</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 border shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold">Customer Error Escalations</span>
                <i class="las la-user-shield text--warning fs-4"></i>
            </div>
            <h4 class="fw-bold text--warning mb-0">{{ $totalEscalations }}</h4>
            <small class="text-muted">Forwarded directly to admin</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 border shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold">System Errors Logged</span>
                <i class="las la-exclamation-triangle text--danger fs-4"></i>
            </div>
            <h4 class="fw-bold text--danger mb-0">{{ $totalSystemErrors }}</h4>
            <small class="text-muted">Disconnects & API errors</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card p-3 border shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold">Active Scheduled Tasks</span>
                <i class="las la-calendar-check text--primary fs-4"></i>
            </div>
            <h4 class="fw-bold text--primary mb-0">{{ $activeSchedulesCount }}</h4>
            <small class="text-muted">Recurring reminders & broadcasts</small>
        </div>
    </div>

    <!-- Left Column: Live Alert Stream -->
    <div class="col-xl-7 col-lg-7">
        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="las la-stream text--primary me-1"></i> Live Notification Alert Feed
                </h6>
                <a href="{{ route('admin.bot.notifications.logs') }}" class="btn btn-outline--primary btn-sm">
                    View Full Logs
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Event Type</th>
                                <th>Recipient</th>
                                <th>Message / Details</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                                <tr>
                                    <td>{!! $log->event_type_badge !!}</td>
                                    <td>
                                        <div class="fw-bold text-dark text-xs">{{ $log->recipient_type == 'admin' ? '🛡️ Admin' : '👤 Customer' }}</div>
                                        <span class="font-monospace text-muted small">+{{ $log->recipient }}</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate text-xs" style="max-width: 220px;" title="{{ $log->message }}">
                                            {{ $log->message }}
                                        </div>
                                        @if($log->error_details)
                                            <small class="text-danger d-block text-xs text-truncate" style="max-width: 220px;">
                                                <i class="las la-exclamation-circle me-1"></i>{{ $log->error_details }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{!! $log->status_badge !!}</td>
                                    <td>
                                        <small class="text-muted text-xs">{{ $log->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No notification alerts logged yet. System is monitoring for events.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Upcoming Scheduled Reminders & Preferences Quick View -->
    <div class="col-xl-5 col-lg-5">
        
        <!-- Upcoming Tasks Card -->
        <div class="card border shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="las la-clock text--warning me-1"></i> Upcoming Scheduled Tasks
                </h6>
                <a href="{{ route('admin.bot.notifications.schedules') }}" class="text--primary text-xs">
                    Manage Schedules
                </a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($upcomingTasks as $task)
                        <li class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                            <div>
                                <div class="fw-bold text-dark">{!! $task->event_type_badge !!} {{ $task->title }}</div>
                                <small class="text-muted text-xs">
                                    <i class="las la-calendar me-1"></i> Next run: {{ $task->next_run_at ? $task->next_run_at->format('d M, h:i A') : 'N/A' }}
                                    ({{ ucfirst($task->schedule_type) }})
                                </small>
                            </div>
                            <span class="badge bg-light text-dark font-monospace text-xs">
                                Sent: {{ $task->total_sent }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">
                            No scheduled tasks due. <a href="{{ route('admin.bot.notifications.schedules') }}" class="fw-bold">Create a reminder</a>.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Quick Preferences Overview Card -->
        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="las la-sliders-h text--info me-1"></i> Notification Event Toggles
                </h6>
                <a href="{{ route('admin.bot.notifications.settings') }}" class="text--primary text-xs">Edit Settings</a>
            </div>
            <div class="card-body p-3">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                        <span><i class="las la-user-shield text--warning me-1"></i> User Error Escalation to Admin</span>
                        <span class="badge {{ $settings->notify_on_user_error_escalation ? 'bg-success' : 'bg-secondary' }}">
                            {{ $settings->notify_on_user_error_escalation ? 'Enabled' : 'Disabled' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                        <span><i class="las la-exclamation-triangle text--danger me-1"></i> System Disconnect & Errors</span>
                        <span class="badge {{ $settings->notify_on_system_error ? 'bg-success' : 'bg-secondary' }}">
                            {{ $settings->notify_on_system_error ? 'Enabled' : 'Disabled' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                        <span><i class="las la-bell text--info me-1"></i> Scheduled Task Reminders</span>
                        <span class="badge {{ $settings->notify_on_scheduled_reminder ? 'bg-success' : 'bg-secondary' }}">
                            {{ $settings->notify_on_scheduled_reminder ? 'Enabled' : 'Disabled' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                        <span><i class="las la-reply text--primary me-1"></i> Auto-Error Assurance Reply to User</span>
                        <span class="badge {{ $settings->auto_error_reply_to_user ? 'bg-success' : 'bg-secondary' }}">
                            {{ $settings->auto_error_reply_to_user ? 'Enabled' : 'Disabled' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection

@push('script')
<script>
(function($){
    "use strict";

    $('#btnTestAdminAlert').on('click', function(){
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Dispatching Test Alert...');

        $.ajax({
            url: "{{ route('admin.bot.notifications.test.alert') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res){
                btn.prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Test Alert to Admin');
                notify('success', res.message || 'Test alert dispatched to admin WhatsApp!');
            },
            error: function(xhr){
                btn.prop('disabled', false).html('<i class="las la-paper-plane me-1"></i> Send Test Alert to Admin');
                let err = 'Failed to send alert.';
                try {
                    err = JSON.parse(xhr.responseText).message || err;
                } catch(e){}
                notify('error', err);
            }
        });
    });

})(jQuery);
</script>
@endpush

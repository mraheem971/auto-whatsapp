@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
            <div>
                <h4 class="mb-1">Notification Preferences & Admin Alert Rules</h4>
                <p class="text-muted mb-0">Configure instant WhatsApp alerts to Admin and automatic user error escalations.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bot.notifications.index') }}" class="btn btn-outline--dark btn-sm">
                    <i class="las la-arrow-left me-1"></i> Dashboard
                </a>
                <button type="button" class="btn btn--success btn-sm" id="btnTestAdminAlert">
                    <i class="las la-paper-plane me-1"></i> Send Test WhatsApp Alert
                </button>
            </div>
        </div>
    </div>

    <!-- Active WhatsApp Gateway Status Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 bg-light p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar--md bg--success text-white rounded-circle d-flex align-items-center justify-content-center">
                        <i class="lab la-whatsapp fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Active Sender Account for Notifications</h6>
                        @if($primaryAccount)
                            <span class="text-muted small">
                                Account: <strong>{{ $primaryAccount->account_name ?? 'Primary Session' }}</strong> 
                                (+{{ $primaryAccount->phone_number ?? $primaryAccount->session_id }}) &bull;
                                <span class="badge bg-success text-white">Online & Ready</span>
                            </span>
                        @else
                            <span class="text-danger small fw-bold">
                                <i class="las la-exclamation-circle me-1"></i> No active WhatsApp account connected. Notifications will be queued or fail until an account is linked.
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.account.listing.index') }}" class="btn btn-outline--primary btn-sm">
                        <i class="las la-external-link-alt me-1"></i> Manage WhatsApp Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="col-12">
        <form action="{{ route('admin.bot.notifications.update.settings') }}" method="POST">
            @csrf
            <div class="row gy-4">
                
                <!-- Left: Admin Recipient & Toggles -->
                <div class="col-lg-7">
                    <div class="card border shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold">
                                <i class="las la-user-shield text--primary me-1"></i> Admin Recipient & Event Triggers
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <!-- Admin Phone -->
                            <div class="form-group mb-4">
                                <label class="fw-bold text-dark mb-1">Admin WhatsApp Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted fw-bold">+</span>
                                    <input type="text" name="admin_whatsapp_number" class="form-control" 
                                           value="{{ old('admin_whatsapp_number', $settings->admin_whatsapp_number) }}" 
                                           placeholder="e.g. 923216793596 (country code + number, no dashes)" required>
                                </div>
                                <small class="text-muted">
                                    All urgent system errors, bot issues, and customer error escalations will be sent directly to this WhatsApp number.
                                </small>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-dark mb-3">Event Notification Triggers</h6>

                            <!-- Trigger 1: System Error -->
                            <div class="form-check form-switch mb-3 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="notify_on_system_error">
                                        <i class="las la-exclamation-triangle text--danger me-1"></i> System Errors & Microservice Failures
                                    </label>
                                    <p class="text-muted small mb-0">Dispatches immediate WhatsApp alert to admin when Baileys or internal API throws an unhandled error.</p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" name="notify_on_system_error" id="notify_on_system_error" value="1" {{ $settings->notify_on_system_error ? 'checked' : '' }}>
                            </div>

                            <!-- Trigger 2: User Error Escalation -->
                            <div class="form-check form-switch mb-3 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="notify_on_user_error_escalation">
                                        <i class="las la-user-tag text--warning me-1"></i> Customer Error Escalation (Keyword Triggered)
                                    </label>
                                    <p class="text-muted small mb-0">When a customer sends a message with error keywords (problem, issue, not working, etc.), relay their message immediately to Admin.</p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" name="notify_on_user_error_escalation" id="notify_on_user_error_escalation" value="1" {{ $settings->notify_on_user_error_escalation ? 'checked' : '' }}>
                            </div>

                            <!-- Trigger 3: Session Disconnect -->
                            <div class="form-check form-switch mb-3 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="notify_on_session_disconnect">
                                        <i class="las la-unlink text--danger me-1"></i> WhatsApp Session Disconnection Alert
                                    </label>
                                    <p class="text-muted small mb-0">Alerts admin as soon as any connected WhatsApp account gets logged out or disconnected.</p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" name="notify_on_session_disconnect" id="notify_on_session_disconnect" value="1" {{ $settings->notify_on_session_disconnect ? 'checked' : '' }}>
                            </div>

                            <!-- Trigger 4: Scheduled Reminders -->
                            <div class="form-check form-switch mb-3 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="notify_on_scheduled_reminder">
                                        <i class="las la-calendar-check text--primary me-1"></i> Task Schedule Execution Notification
                                    </label>
                                    <p class="text-muted small mb-0">Log and report status when scheduled recurring reminders or broadcasts finish execution.</p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" name="notify_on_scheduled_reminder" id="notify_on_scheduled_reminder" value="1" {{ $settings->notify_on_scheduled_reminder ? 'checked' : '' }}>
                            </div>

                            <!-- Trigger 5: All Incoming Messages -->
                            <div class="form-check form-switch mb-0 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="notify_on_new_message">
                                        <i class="las la-comment-dots text--info me-1"></i> All Incoming Messages Alert (High Volume)
                                    </label>
                                    <p class="text-muted small mb-0">Notify admin on EVERY incoming WhatsApp message. (Recommended OFF to avoid alert spam).</p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" name="notify_on_new_message" id="notify_on_new_message" value="1" {{ $settings->notify_on_new_message ? 'checked' : '' }}>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right: Escalation Keywords & User Auto-Reply -->
                <div class="col-lg-5">
                    <div class="card border shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold">
                                <i class="las la-robot text--success me-1"></i> Error Escalation & User Auto-Reply
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <!-- Error Keywords -->
                            <div class="form-group mb-4">
                                <label class="fw-bold text-dark mb-1">
                                    Error Detection Keywords (Comma Separated)
                                </label>
                                <textarea name="error_keywords" rows="3" class="form-control" placeholder="error, issue, problem, not working, kharab, masla, help, urgent, admin, complaint">{{ old('error_keywords', $settings->error_keywords) }}</textarea>
                                <small class="text-muted">
                                    Incoming messages containing any of these keywords will trigger escalation to Admin.
                                </small>
                            </div>

                            <hr class="my-4">

                            <!-- Auto-Reply Switch -->
                            <div class="form-check form-switch mb-3 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="auto_error_reply_to_user">
                                        Auto-Reply to User on Error
                                    </label>
                                    <p class="text-muted small mb-0">Instantly acknowledge customer when they report an issue.</p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" name="auto_error_reply_to_user" id="auto_error_reply_to_user" value="1" {{ $settings->auto_error_reply_to_user ? 'checked' : '' }}>
                            </div>

                            <!-- Auto Reply Template -->
                            <div class="form-group mb-4">
                                <label class="fw-bold text-dark mb-1">User Auto-Reply Message Template</label>
                                <textarea name="error_reply_message" rows="5" class="form-control" placeholder="Thank you for reaching out. We have logged your concern and forwarded it to our administrator...">{{ old('error_reply_message', $settings->error_reply_message) }}</textarea>
                                <small class="text-muted">
                                    This message will be automatically sent back to the customer.
                                </small>
                            </div>

                            <!-- Cron URL info -->
                            <div class="alert alert-info py-2 px-3 small mb-0">
                                <strong><i class="las la-clock me-1"></i> Cron Job Endpoint for Schedules:</strong><br>
                                <code>{{ url('/api/notifications/cron/run') }}</code>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn--primary px-4 py-2">
                        <i class="las la-save me-1"></i> Save Notification Preferences
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";

        $('#btnTestAdminAlert').on('click', function () {
            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Dispatching...');

            $.ajax({
                url: "{{ route('admin.bot.notifications.test.alert') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    $btn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        notify('success', response.message);
                    } else {
                        notify('error', response.message || 'Failed to dispatch test alert');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error sending test alert.';
                    notify('error', msg);
                }
            });
        });

    })(jQuery);
</script>
@endpush

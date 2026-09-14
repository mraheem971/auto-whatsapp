@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="las la-cog text-primary me-1"></i> Settings Hub</h3>
                <p class="text-muted mb-0">Manage your profile, anti-ban protection, WhatsApp bot routing, security, and webhook integrations.</p>
            </div>
            <div>
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="las la-user-check text-success me-1"></i> Logged in as: <strong>{{ $user->username }}</strong>
                </span>
            </div>
        </div>

        <div class="row gy-4">
            {{-- Left Navigation Tabs --}}
            <div class="col-lg-3">
                <div class="card custom--card border shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold text-dark small text-uppercase">Navigation</h6>
                    </div>
                    <div class="list-group list-group-flush settings-nav-pills p-2">
                        <a href="#profileTab" class="list-group-item list-group-item-action rounded-2 border-0 d-flex align-items-center py-2 mb-1 @if($activeTab == 'profile') active @endif" data-bs-toggle="list">
                            <i class="las la-user-circle fs-5 me-2 text-primary"></i>
                            <span class="fw-semibold">Profile & Account</span>
                        </a>
                        <a href="#behaviorTab" class="list-group-item list-group-item-action rounded-2 border-0 d-flex align-items-center py-2 mb-1 @if($activeTab == 'behavior') active @endif" data-bs-toggle="list">
                            <i class="las la-user-shield fs-5 me-2 text-danger"></i>
                            <span class="fw-semibold">Anti-Ban Protection</span>
                        </a>
                        <a href="#botTab" class="list-group-item list-group-item-action rounded-2 border-0 d-flex align-items-center py-2 mb-1 @if($activeTab == 'bot') active @endif" data-bs-toggle="list">
                            <i class="lab la-whatsapp fs-5 me-2 text-success"></i>
                            <span class="fw-semibold">Bot & Routing</span>
                        </a>
                        <a href="#securityTab" class="list-group-item list-group-item-action rounded-2 border-0 d-flex align-items-center py-2 mb-1 @if($activeTab == 'security') active @endif" data-bs-toggle="list">
                            <i class="las la-key fs-5 me-2 text-warning"></i>
                            <span class="fw-semibold">Security & Password</span>
                        </a>
                        <a href="#webhooksTab" class="list-group-item list-group-item-action rounded-2 border-0 d-flex align-items-center py-2 mb-1 @if($activeTab == 'webhooks') active @endif" data-bs-toggle="list">
                            <i class="las la-plug fs-5 me-2 text-info"></i>
                            <span class="fw-semibold">Webhooks & API</span>
                        </a>
                    </div>
                </div>

                {{-- Status Card --}}
                <div class="card custom--card border shadow-sm rounded-3 mt-3 bg-light">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-dark small mb-2"><i class="las la-shield-alt text-success me-1"></i> WhatsApp Engine Status</h6>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Connected Lines:</span>
                            <span class="fw-bold text-dark">{{ $whatsappAccounts->where('status', 1)->count() }} / {{ $whatsappAccounts->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Anti-Ban Mode:</span>
                            <span class="badge bg-success small">Active</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Safe Daily Limit:</span>
                            <span class="fw-bold text-dark">{{ number_format($botSettings->daily_send_limit) }} msgs</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Content Area --}}
            <div class="col-lg-9">
                <div class="tab-content">
                    
                    {{-- 1. Profile Tab --}}
                    <div class="tab-pane fade @if($activeTab == 'profile') show active @endif" id="profileTab">
                        <div class="card custom--card border shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                    <i class="las la-user text-primary me-1"></i> Profile & Business Details
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('user.settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row gy-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="firstname" value="{{ old('firstname', $user->firstname) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="lastname" value="{{ old('lastname', $user->lastname) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Username</label>
                                            <input type="text" class="form-control bg-light" value="{{ $user->username }}" readonly disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Email Address</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                                                <span class="input-group-text bg-success-subtle text-success border-start-0 small"><i class="las la-check-circle me-1"></i> Verified</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Phone / Mobile Number</label>
                                            <input type="text" class="form-control" name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="+92 300 1234567">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Profile Avatar</label>
                                            <input type="file" class="form-control" name="image" accept=".png, .jpg, .jpeg">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold small">Street Address</label>
                                            <input type="text" class="form-control" name="address" value="{{ old('address', $user->address) }}" placeholder="Main Boulevard, Commercial Area">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">City</label>
                                            <input type="text" class="form-control" name="city" value="{{ old('city', $user->city) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">State / Province</label>
                                            <input type="text" class="form-control" name="state" value="{{ old('state', $user->state) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">Zip / Postal Code</label>
                                            <input type="text" class="form-control" name="zip" value="{{ old('zip', $user->zip) }}">
                                        </div>
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn--base px-4 py-2 fw-bold">
                                            <i class="las la-save me-1"></i> Save Profile Details
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Anti-Ban Behavior Tab --}}
                    <div class="tab-pane fade @if($activeTab == 'behavior') show active @endif" id="behaviorTab">
                        <div class="card custom--card border shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                    <i class="las la-user-shield text-danger me-1"></i> Human Behavior & Anti-Ban Protection
                                </h5>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                    Recommended Active
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('user.settings.behavior.update') }}" method="POST">
                                    @csrf
                                    
                                    <div class="row gy-4">
                                        {{-- Typing Simulation --}}
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 h-100 bg-light">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="typing_simulation" id="typing_sim" value="1" @checked($botSettings->typing_simulation)>
                                                    <label class="form-check-label fw-bold text-dark" for="typing_sim">
                                                        Typing Simulation
                                                    </label>
                                                </div>
                                                <small class="text-muted d-block mb-3">Simulates natural "typing..." presence indicator before sending messages.</small>
                                                
                                                <label class="form-label small fw-semibold">Typing Duration (Seconds):</label>
                                                <input type="number" class="form-control" name="typing_duration_seconds" value="{{ $botSettings->typing_duration_seconds }}" min="1" max="30">
                                            </div>
                                        </div>

                                        {{-- Audio Recording Simulation --}}
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 h-100 bg-light">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="recording_simulation" id="recording_sim" value="1" @checked($botSettings->recording_simulation)>
                                                    <label class="form-check-label fw-bold text-dark" for="recording_sim">
                                                        Audio Recording Simulation
                                                    </label>
                                                </div>
                                                <small class="text-muted d-block mb-3">Shows "recording audio..." presence indicator before sending voice note messages.</small>

                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="random_presence_update" id="random_presence" value="1" @checked($botSettings->random_presence_update)>
                                                    <label class="form-check-label fw-bold text-dark" for="random_presence">
                                                        Random Online Presence
                                                    </label>
                                                </div>
                                                <small class="text-muted d-block">Periodically updates online/offline state to mimic human app usage.</small>
                                            </div>
                                        </div>

                                        {{-- Dynamic Delay Interval --}}
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 h-100">
                                                <h6 class="fw-bold text-dark mb-1"><i class="las la-stopwatch text-primary me-1"></i> Send Delay Interval (Seconds)</h6>
                                                <small class="text-muted d-block mb-3">Adds randomized interval between outgoing bulk messages to prevent spam detection.</small>

                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">Min Delay (Sec)</label>
                                                        <input type="number" class="form-control" name="min_delay_seconds" value="{{ $botSettings->min_delay_seconds }}" min="1" max="120" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">Max Delay (Sec)</label>
                                                        <input type="number" class="form-control" name="max_delay_seconds" value="{{ $botSettings->max_delay_seconds }}" min="1" max="300" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Safe Daily Limits & Sleep Mode --}}
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 h-100">
                                                <h6 class="fw-bold text-dark mb-1"><i class="las la-moon text-warning me-1"></i> Night Sleep Mode & Safety Limits</h6>
                                                <small class="text-muted d-block mb-2">Pauses bulk campaign broadcasts during late night hours.</small>

                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="sleep_mode" id="sleep_mode_toggle" value="1" @checked($botSettings->sleep_mode)>
                                                    <label class="form-check-label fw-bold text-dark" for="sleep_mode_toggle">
                                                        Enable Night Sleep Guard
                                                    </label>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">Sleep Start</label>
                                                        <input type="time" class="form-control" name="sleep_start_time" value="{{ $botSettings->sleep_start_time ?: '22:00' }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">Sleep End</label>
                                                        <input type="time" class="form-control" name="sleep_end_time" value="{{ $botSettings->sleep_end_time ?: '08:00' }}">
                                                    </div>
                                                </div>

                                                <label class="form-label small fw-semibold">Daily Send Safety Limit:</label>
                                                <input type="number" class="form-control" name="daily_send_limit" value="{{ $botSettings->daily_send_limit }}" min="10" max="50000" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn--base px-4 py-2 fw-bold">
                                            <i class="las la-shield-alt me-1"></i> Save Anti-Ban Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Bot Routing & Default Account Tab --}}
                    <div class="tab-pane fade @if($activeTab == 'bot') show active @endif" id="botTab">
                        <div class="card custom--card border shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                    <i class="lab la-whatsapp text-success me-1"></i> WhatsApp Bot Routing & Default Line
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('user.settings.bot.update') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-dark">Default Primary WhatsApp Account</label>
                                        <select class="form-select form-select-lg" name="default_whatsapp_account_id">
                                            <option value="0">Auto Load Balance across all active accounts</option>
                                            @foreach($whatsappAccounts as $acc)
                                                <option value="{{ $acc->id }}" @selected($botSettings->default_whatsapp_account_id == $acc->id)>
                                                    {{ $acc->name }} ({{ $acc->phone_number ?: $acc->session_id }}) - {{ $acc->status == 1 ? 'Connected' : 'Disconnected' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">When sending messages via API or Quick Dispatch, this account will be used by default.</small>
                                    </div>

                                    <div class="p-3 border rounded-3 bg-light mb-4">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch" name="auto_fallback" id="auto_fallback_toggle" value="1" @checked($botSettings->auto_fallback)>
                                            <label class="form-check-label fw-bold text-dark" for="auto_fallback_toggle">
                                                Automatic Failover & Fallback
                                            </label>
                                        </div>
                                        <small class="text-muted d-block">If the primary connected WhatsApp account disconnects or gets logged out, the system will automatically route outgoing campaigns and auto-replies through your next available active WhatsApp account.</small>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn--base px-4 py-2 fw-bold">
                                            <i class="las la-save me-1"></i> Save Routing Preferences
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Security Tab --}}
                    <div class="tab-pane fade @if($activeTab == 'security') show active @endif" id="securityTab">
                        <div class="card custom--card border shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                    <i class="las la-key text-warning me-1"></i> Account Password & Security
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('user.settings.security.update') }}" method="POST">
                                    @csrf
                                    
                                    <div class="row gy-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold small">Current Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password" required autocomplete="new-password">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Confirm New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <a href="{{ route('user.twofactor') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="las la-shield-alt me-1"></i> Manage Two-Factor Authentication (2FA)
                                        </a>
                                        <button type="submit" class="btn btn--base px-4 py-2 fw-bold">
                                            <i class="las la-lock me-1"></i> Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Webhooks & API Tab --}}
                    <div class="tab-pane fade @if($activeTab == 'webhooks') show active @endif" id="webhooksTab">
                        <div class="card custom--card border shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0 fw-bold fs-6 text-dark">
                                    <i class="las la-plug text-info me-1"></i> Webhooks & API Integrations
                                </h5>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">
                                    REST API v1
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('user.settings.webhooks.update') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-dark">Inbound Webhook Callback URL</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                            <input type="url" class="form-control" name="webhook_url" value="{{ old('webhook_url', $botSettings->webhook_url) }}" placeholder="https://yourdomain.com/api/whatsapp-webhook">
                                        </div>
                                        <small class="text-muted">Real-time incoming WhatsApp messages and status receipts will be forwarded as POST JSON requests to this URL.</small>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-dark">Webhook Signing Secret</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-monospace bg-light" id="webhookSecretInput" value="{{ $botSettings->webhook_secret ?: 'whsec_' . bin2hex(random_bytes(16)) }}" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookSecret()">
                                                <i class="las la-copy"></i> Copy
                                            </button>
                                            <button class="btn btn-outline-danger" type="button" onclick="document.getElementById('regenSecretForm').submit();">
                                                <i class="las la-sync"></i> Regenerate
                                            </button>
                                        </div>
                                        <small class="text-muted">Every webhook POST request includes header <code>X-WhatsApp-Signature</code> signed using this secret.</small>
                                    </div>

                                    <div class="p-3 bg-light rounded-3 border mb-4">
                                        <h6 class="fw-bold text-dark small mb-2"><i class="las la-bolt text-warning me-1"></i> Webhook Event Triggers</h6>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                    <label class="form-check-label small fw-semibold text-dark"><code>messages.upsert</code> (Incoming Chat)</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                    <label class="form-check-label small fw-semibold text-dark"><code>messages.update</code> (Delivered / Read Receipt)</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                    <label class="form-check-label small fw-semibold text-dark"><code>connection.update</code> (Session Online/Offline)</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                    <label class="form-check-label small fw-semibold text-dark"><code>contacts.upsert</code> (New Contact Added)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn--base px-4 py-2 fw-bold">
                                            <i class="las la-save me-1"></i> Save Webhook Settings
                                        </button>
                                    </div>
                                </form>

                                <form id="regenSecretForm" action="{{ route('user.settings.webhooks.secret.regen') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('style')
<style>
    .settings-nav-pills .list-group-item.active {
        background-color: #10b981 !important;
        color: #ffffff !important;
    }
    .settings-nav-pills .list-group-item.active i {
        color: #ffffff !important;
    }
    .settings-nav-pills .list-group-item {
        color: #475569;
        font-weight: 500;
        transition: all 0.2s;
    }
    .settings-nav-pills .list-group-item:hover:not(.active) {
        background-color: #f1f5f9;
        color: #0f172a;
    }
</style>
@endpush

@push('script')
<script>
    function copyWebhookSecret() {
        var copyText = document.getElementById("webhookSecretInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        notify('success', 'Webhook signing secret copied to clipboard!');
    }
</script>
@endpush

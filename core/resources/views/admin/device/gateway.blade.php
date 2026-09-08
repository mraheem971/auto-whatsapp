@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    <!-- Top Hero Banner -->
    <div class="col-12">
        <div class="card bg--dark text-white border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg--success text-white fw-bold px-3 py-1 text-uppercase">Android Mobile Gateway</span>
                        <span class="badge bg-primary text-white px-2 py-1"><i class="las la-sync me-1"></i>Live Sync Engine</span>
                    </div>
                    <h3 class="text-white fw-bold mb-1">Android Mobile Device Gateway & API Hub</h3>
                    <p class="text-white text-opacity-75 mb-0">
                        Turn your real Android phone into a high-capacity WhatsApp & SMS sending gateway. Connect via Web Admin, Termux, Android App, or Webhooks.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.device.sender.index') }}" class="btn btn--primary btn-sm px-3">
                        <i class="las la-paper-plane me-1"></i> Go to Device Messenger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Core Steps -->
    <div class="col-md-4">
        <div class="card border shadow-sm h-100 p-3 text-center">
            <div class="avatar avatar--md bg--success-transparent text--success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="lab la-whatsapp fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">1. Pair Android WhatsApp</h5>
            <p class="text-muted small mb-3">
                Open WhatsApp on your Android phone, go to <strong>Linked Devices > Link a Device</strong> and scan the QR code.
            </p>
            <a href="{{ route('admin.account.listing.create') }}" class="btn btn-outline--success btn-sm mt-auto">
                <i class="las la-qrcode me-1"></i> Scan QR Code
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border shadow-sm h-100 p-3 text-center">
            <div class="avatar avatar--md bg--primary-transparent text--primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="las la-globe fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">2. Web Admin Dispatcher</h5>
            <p class="text-muted small mb-3">
                Compose messages with media attachments, templates, and dynamic tags directly from your computer browser.
            </p>
            <a href="{{ route('admin.device.sender.index') }}" class="btn btn-outline--primary btn-sm mt-auto">
                <i class="las la-paper-plane me-1"></i> Open Web Messenger
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border shadow-sm h-100 p-3 text-center">
            <div class="avatar avatar--md bg--info-transparent text--info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="las la-code fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">3. Automated Mobile REST API</h5>
            <p class="text-muted small mb-3">
                Integrate external CRMs, websites, or Android background background services via HTTP endpoints.
            </p>
            <a href="{{ route('admin.autoreply.docs') }}" class="btn btn-outline--info btn-sm mt-auto">
                <i class="las la-book me-1"></i> View API Docs
            </a>
        </div>
    </div>

    <!-- Active Connected Android Devices -->
    <div class="col-12">
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold"><i class="lab la-android text--success me-1"></i> Connected Android Mobile Devices</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Device / Phone</th>
                                <th>Session ID</th>
                                <th>Status</th>
                                <th>Last Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($connectedAccounts as $acc)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">📱 {{ $acc->account_name }}</div>
                                        <span class="font-monospace text-muted small">{{ $acc->phone_number ? '+' . $acc->phone_number : 'Active' }}</span>
                                    </td>
                                    <td><code class="text-primary">{{ $acc->session_id }}</code></td>
                                    <td>
                                        @if($acc->status == 1)
                                            <span class="badge bg-success"><i class="las la-check-circle me-1"></i>Online / Connected</span>
                                        @else
                                            <span class="badge bg-warning"><i class="las la-spinner fa-spin me-1"></i>Disconnected</span>
                                        @endif
                                    </td>
                                    <td>{{ showDateTime($acc->updated_at, 'd M Y, h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('admin.device.sender.index') }}" class="btn btn-xs btn--primary">
                                            <i class="las la-paper-plane me-1"></i> Send Message
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No Android WhatsApp devices currently linked. <a href="{{ route('admin.account.listing.create') }}" class="fw-bold">Link your phone now</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Android REST API Endpoints Quick Reference -->
    <div class="col-12">
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold"><i class="las la-terminal text--info me-1"></i> Android Mobile Gateway Endpoints</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Method</th>
                                <th>Endpoint URL</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>{{ $baseUrl }}/api/device/send</code></td>
                                <td>Send a message from your connected Android device via API.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>{{ $baseUrl }}/api/device/info</code></td>
                                <td>Retrieve status of all connected Android devices.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>{{ $baseUrl }}/api/device/messages/pending</code></td>
                                <td>Fetch pending message queue for an Android background client.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>{{ $baseUrl }}/api/device/messages/status</code></td>
                                <td>Report delivery / failure receipt status back to the web admin panel.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

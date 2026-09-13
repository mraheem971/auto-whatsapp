@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-section py-60">
    <div class="container">
        
        <!-- Welcome & Plan Banner -->
        <div class="card custom--card border-0 shadow-sm rounded-3 mb-4 bg--dark text-white p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success text-white fw-bold px-3 py-1 text-uppercase">WhatsApp Bot Portal</span>
                        @if($plan)
                            <span class="badge bg-primary text-white px-2 py-1"><i class="las la-crown me-1"></i>{{ $plan->name }}</span>
                        @endif
                    </div>
                    <h3 class="text-white fw-bold mb-1">Welcome back, {{ $user->fullname }}!</h3>
                    <p class="text-white text-opacity-75 mb-0">
                        Manage your connected WhatsApp accounts, keyword bots, message templates, and marketing campaigns.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm px-3">
                        <i class="lab la-whatsapp me-1"></i> Connect WhatsApp
                    </a>
                    <a href="{{ route('user.plans.index') }}" class="btn btn-outline-light btn-sm px-3">
                        <i class="las la-rocket me-1"></i> Upgrade Plan
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Cards Row 1: Core WhatsApp & Communication Stats -->
        <div class="row gy-3 mb-3">
            <!-- Active Gateways -->
            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.whatsapp.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Active Gateways</span>
                            <div class="avatar avatar--sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="lab la-whatsapp fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $activeGateways }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->account_limit ?? 1 }}</span></h3>
                        <small class="{{ $activeGateways > 0 ? 'text-success' : 'text-muted' }}">
                            <i class="las {{ $activeGateways > 0 ? 'la-check-circle' : 'la-info-circle' }} me-1"></i>
                            {{ $activeGateways > 0 ? 'Online & Active' : 'No active gateway' }}
                        </small>
                    </div>
                </a>
            </div>

            <!-- Total Contacts -->
            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.contacts.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Total Contacts</span>
                            <div class="avatar avatar--sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-address-book fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($totalContacts) }}</h3>
                        <small class="text-primary"><i class="las la-user-friends me-1"></i>Synced Audience</small>
                    </div>
                </a>
            </div>

            <!-- WhatsApp Groups -->
            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.contacts.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">WhatsApp Groups</span>
                            <div class="avatar avatar--sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-users fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($totalGroups) }}</h3>
                        <small class="text-info"><i class="las la-comments me-1"></i>Community Groups</small>
                    </div>
                </a>
            </div>

            <!-- Messages Today -->
            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.campaigns.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Messages Today</span>
                            <div class="avatar avatar--sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-paper-plane fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($messagesToday) }}</h3>
                        <small class="text-warning"><i class="las la-bolt me-1"></i>Delivered Today</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Metric Cards Row 2: Bot Automations & Broadcast Hub -->
        <div class="row gy-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.autoreply.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Keyword Auto-Replies</span>
                            <div class="avatar avatar--sm bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-robot fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $totalAutoReplies }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->autoreply_limit ?? 5 }}</span></h3>
                        <small class="text-danger"><i class="las la-magic me-1"></i>Active Bot Rules</small>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.templates.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Message Templates</span>
                            <div class="avatar avatar--sm bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-envelope-open-text fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $totalTemplates }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->template_limit ?? 5 }}</span></h3>
                        <small class="text-secondary"><i class="las la-file-alt me-1"></i>Saved Templates</small>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.campaigns.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Run Campaigns</span>
                            <div class="avatar avatar--sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-bullhorn fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $totalCampaigns }} <span class="text-muted fs-6 fw-normal">/ {{ $plan->campaign_limit ?? 2 }}</span></h3>
                        <small class="text-success"><i class="las la-broadcast-tower me-1"></i>Marketing Broadcasts</small>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6">
                <a href="{{ route('user.plans.index') }}" class="text-decoration-none">
                    <div class="card custom--card p-3 p-md-4 border shadow-sm h-100 hover-shadow transition">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">Current Plan</span>
                            <div class="avatar avatar--sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="las la-crown fs-3"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $plan->name ?? 'Free Tier' }}</h3>
                        <small class="text-warning"><i class="las la-shield-alt me-1"></i>Anti-Ban Protected</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Middle Section: Quick Actions & Connected Accounts -->
        <div class="row gy-4 mb-4">
            
            <!-- Quick Actions Grid -->
            <div class="col-lg-5">
                <div class="card custom--card border shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold"><i class="las la-tools text--base me-1"></i> Quick Action Hub</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('user.whatsapp.create') }}" class="p-3 border rounded-3 d-block text-center text-decoration-none bg-light hover-shadow transition">
                                    <i class="lab la-whatsapp fs-1 text-success mb-2 d-block"></i>
                                    <span class="fw-bold text-dark d-block">Link WhatsApp</span>
                                    <small class="text-muted">QR / Pairing Code</small>
                                </a>
                            </div>

                            <div class="col-6">
                                <a href="{{ route('user.autoreply.index') }}" class="p-3 border rounded-3 d-block text-center text-decoration-none bg-light hover-shadow transition">
                                    <i class="las la-robot fs-1 text-primary mb-2 d-block"></i>
                                    <span class="fw-bold text-dark d-block">Add Keyword Bot</span>
                                    <small class="text-muted">Instant Auto-Reply</small>
                                </a>
                            </div>

                            <div class="col-6">
                                <a href="{{ route('user.campaigns.create') }}" class="p-3 border rounded-3 d-block text-center text-decoration-none bg-light hover-shadow transition">
                                    <i class="las la-bullhorn fs-1 text-warning mb-2 d-block"></i>
                                    <span class="fw-bold text-dark d-block">Run Campaign</span>
                                    <small class="text-muted">Broadcast to List</small>
                                </a>
                            </div>

                            <div class="col-6">
                                <a href="{{ route('user.settings.behavior.index') }}" class="p-3 border rounded-3 d-block text-center text-decoration-none bg-light hover-shadow transition">
                                    <i class="las la-user-shield fs-1 text-danger mb-2 d-block"></i>
                                    <span class="fw-bold text-dark d-block">Human Behavior</span>
                                    <small class="text-muted">Anti-Ban Protection</small>
                                </a>
                            </div>
                        </div>

                        <!-- Current Plan Usage Progress -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small text-dark">Plan Usage (Accounts):</span>
                                <span class="small fw-bold text-muted">{{ $connectedAccountsCount }} of {{ $plan->account_limit ?? 1 }} Used</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $limit = $plan->account_limit ?: 1;
                                    $pct = min(100, round(($connectedAccountsCount / $limit) * 100));
                                @endphp
                                <div class="progress-bar bg--base" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Connected Accounts List -->
            <div class="col-lg-7">
                <div class="card custom--card border shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold"><i class="lab la-whatsapp text-success me-1"></i> My WhatsApp Accounts</h5>
                        <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm"><i class="las la-plus-circle me-1"></i> Add Account</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Account Name</th>
                                        <th>Phone Number</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($connectedAccounts as $acc)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $acc->account_name }}</div>
                                                <small class="text-muted">{{ $acc->session_id }}</small>
                                            </td>
                                            <td>
                                                @if($acc->phone_number)
                                                    <span class="fw-bold">+{{ $acc->phone_number }}</span>
                                                @else
                                                    <span class="text-muted">Pending Link</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($acc->status == 1)
                                                    <span class="badge bg-success"><i class="las la-check-circle me-1"></i> Connected</span>
                                                @else
                                                    <span class="badge bg-warning"><i class="las la-hourglass-half me-1"></i> Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('user.whatsapp.delete', $acc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Disconnect this WhatsApp account?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Disconnect Account">
                                                        <i class="las la-unlink"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="lab la-whatsapp text-muted fs-1 d-block mb-2"></i>
                                                <p class="text-muted mb-2">No WhatsApp account connected yet.</p>
                                                <a href="{{ route('user.whatsapp.create') }}" class="btn btn--base btn-sm">Connect WhatsApp Now</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

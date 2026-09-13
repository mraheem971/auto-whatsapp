@extends($activeTemplate . 'layouts.master')
@section('content')

<style>
    :root {
        --dash-bg: #11141a;
        --dash-card-bg: #181c24;
        --dash-card-hover: #1f242f;
        --dash-border: #262c3a;
        --dash-text-muted: #8c97ac;
        --dash-pink: #ff3366;
        --dash-green: #00d084;
        --dash-blue: #0084ff;
        --dash-yellow: #ffb800;
        --dash-teal: #00c9a7;
    }

    .dark-saas-layout {
        background-color: var(--dash-bg);
        color: #f1f5f9;
        min-height: calc(100vh - 120px);
        padding: 30px 0 60px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .saas-sidebar-nav {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 20px 12px;
    }

    .saas-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 14px 8px;
        border-radius: 12px;
        color: var(--dash-text-muted);
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .saas-nav-item i {
        font-size: 22px;
        margin-bottom: 6px;
        transition: transform 0.2s ease;
    }

    .saas-nav-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
    }

    .saas-nav-item.active {
        background: rgba(255, 51, 102, 0.12);
        color: var(--dash-pink);
    }

    .saas-nav-item.active i {
        color: var(--dash-pink);
        transform: scale(1.1);
    }

    .saas-card {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .saas-card:hover {
        border-color: #343c4e;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .saas-top-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .saas-welcome-title {
        font-size: 24px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.5px;
        margin: 0;
    }

    .saas-user-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-border);
        padding: 8px 16px;
        border-radius: 50px;
    }

    .saas-avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #3b4252;
        color: #eceff4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
    }

    /* Setup Banner Card */
    .setup-banner-card {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .setup-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 20px;
        box-shadow: 0 4px 15px rgba(255, 65, 108, 0.4);
    }

    .setup-inner-gateway {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 14px 18px;
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* 4 Metric Stats Cards */
    .stat-metric-card {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-icon-wrapper {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 18px;
    }

    .icon-pink {
        background: rgba(255, 51, 102, 0.12);
        color: var(--dash-pink);
    }

    .icon-teal {
        background: rgba(0, 201, 167, 0.12);
        color: var(--dash-teal);
    }

    .icon-blue {
        background: rgba(0, 132, 255, 0.12);
        color: var(--dash-blue);
    }

    .icon-yellow {
        background: rgba(255, 184, 0, 0.12);
        color: var(--dash-yellow);
    }

    .stat-number {
        font-size: 26px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
    }

    .stat-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--dash-text-muted);
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin: 0;
    }

    /* 3 Bottom Service Credit Cards */
    .credit-service-card {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .credit-service-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 90px;
        height: 90px;
        background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }

    .service-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .service-icon-sms {
        background: rgba(0, 132, 255, 0.15);
        color: var(--dash-blue);
    }

    .service-icon-email {
        background: rgba(255, 65, 108, 0.15);
        color: var(--dash-pink);
    }

    .service-icon-whatsapp {
        background: rgba(0, 208, 132, 0.15);
        color: var(--dash-green);
    }

    .limit-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(0, 208, 132, 0.1);
        color: var(--dash-green);
        border: 1px solid rgba(0, 208, 132, 0.2);
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 14px;
        margin-bottom: 20px;
        width: fit-content;
    }

    .btn-buy-credit {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff;
        font-weight: 600;
        font-size: 13px;
        padding: 10px;
        border-radius: 10px;
        width: 100%;
        text-align: center;
        transition: all 0.2s ease;
        text-decoration: none;
        display: block;
    }

    .btn-buy-credit:hover {
        background: #ffffff;
        color: #11141a;
        border-color: #ffffff;
    }
</style>

<div class="dark-saas-layout">
    <div class="container-fluid px-lg-4">
        
        <div class="row g-4">
            
            <!-- Left Vertical Sidebar (Match Screenshot) -->
            <div class="col-xl-1 col-lg-2 col-md-3">
                <div class="saas-sidebar-nav text-center">
                    
                    <a href="{{ route('user.home') }}" class="saas-nav-item active">
                        <i class="las la-th-large"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('user.contacts.index') }}" class="saas-nav-item">
                        <i class="las la-address-book"></i>
                        <span>Contacts</span>
                    </a>

                    <a href="{{ route('user.autoreply.index') }}" class="saas-nav-item">
                        <i class="las la-comment-dots"></i>
                        <span>Messages</span>
                    </a>

                    <a href="{{ route('user.campaigns.index') }}" class="saas-nav-item">
                        <i class="las la-bullhorn"></i>
                        <span>Campaigns</span>
                    </a>

                    <a href="{{ route('user.templates.index') }}" class="saas-nav-item">
                        <i class="las la-layer-group"></i>
                        <span>Templates</span>
                    </a>

                    <a href="{{ route('user.whatsapp.index') }}" class="saas-nav-item">
                        <i class="las la-cubes"></i>
                        <span>Gateway</span>
                    </a>

                    <a href="{{ route('user.deposit.history') }}" class="saas-nav-item">
                        <i class="las la-chart-bar"></i>
                        <span>Report</span>
                    </a>

                </div>
            </div>

            <!-- Main Dashboard Area (Match Screenshot) -->
            <div class="col-xl-11 col-lg-10 col-md-9">
                
                <!-- Top Header: Welcome Title + User Badge -->
                <div class="saas-top-header">
                    <h2 class="saas-welcome-title">Welcome Back, {{ $user->fullname ?: $user->username }}</h2>
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-sm-flex align-items-center gap-2 text-muted small">
                            <span class="badge bg-dark border border-secondary px-3 py-2 text-white">
                                <i class="las la-globe me-1"></i> Global
                            </span>
                            <span class="badge bg-dark border border-secondary px-3 py-2 text-white">
                                <i class="las la-moon text-warning me-1"></i> Dark
                            </span>
                            <span class="badge bg-dark border border-secondary px-2 py-2 text-white">
                                🇺🇸 EN
                            </span>
                        </div>

                        <div class="saas-user-badge">
                            <div class="saas-avatar-circle">
                                {{ strtoupper(substr($user->firstname ?: $user->username, 0, 1)) }}
                            </div>
                            <div class="text-start">
                                <div class="small text-muted" style="font-size: 11px; line-height: 1;">Member</div>
                                <div class="fw-bold text-white small" style="line-height: 1.2;">{{ $user->fullname ?: $user->username }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complete Your Setup Alert Banner -->
                <div class="setup-banner-card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="setup-icon-box">
                                <i class="las la-bell"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-white mb-0">Complete Your Setup</h6>
                                <p class="text-muted small mb-0">Configure Your Gateways To Start Sending Messages</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold fs-6 {{ $connectedAccountsCount > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $connectedAccountsCount > 0 ? '1/1' : '0/1' }}
                            </span>
                        </div>
                    </div>

                    <!-- Inner Gateway Row -->
                    <div class="setup-inner-gateway">
                        <div class="d-flex align-items-center gap-3">
                            <i class="lab la-whatsapp fs-3 {{ $connectedAccountsCount > 0 ? 'text-success' : 'text-muted' }}"></i>
                            <div>
                                <div class="fw-bold text-white small">WhatsApp Gateway</div>
                                @if($connectedAccountsCount > 0)
                                    <div class="text-success small fw-bold">
                                        <i class="las la-check-circle me-1"></i> Active & Connected ({{ $connectedAccounts->first()->phone_number ? '+' . $connectedAccounts->first()->phone_number : 'Ready' }})
                                    </div>
                                @else
                                    <div class="text-danger small fw-bold">
                                        Add Your Gateway To Start Sending
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div>
                            @if($connectedAccountsCount > 0)
                                <a href="{{ route('user.whatsapp.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                    Manage Gateway
                                </a>
                            @else
                                <a href="{{ route('user.whatsapp.create') }}" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                                    + Connect WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 4 Stat Metric Cards (Exact Match) -->
                <div class="row g-4 mb-4">
                    
                    <!-- Contacts -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="stat-metric-card">
                            <div class="stat-icon-wrapper icon-pink">
                                <i class="las la-address-book"></i>
                            </div>
                            <div>
                                <div class="stat-number">{{ number_format($totalContacts, 2) }}</div>
                                <p class="stat-label">Contacts</p>
                            </div>
                        </div>
                    </div>

                    <!-- Groups -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="stat-metric-card">
                            <div class="stat-icon-wrapper icon-teal">
                                <i class="las la-user-friends"></i>
                            </div>
                            <div>
                                <div class="stat-number">{{ number_format($totalGroups, 2) }}</div>
                                <p class="stat-label">Groups</p>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Today -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="stat-metric-card">
                            <div class="stat-icon-wrapper icon-blue">
                                <i class="las la-paper-plane"></i>
                            </div>
                            <div>
                                <div class="stat-number">{{ number_format($messagesToday, 2) }}</div>
                                <p class="stat-label">Messages Today</p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Gateways -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="stat-metric-card">
                            <div class="stat-icon-wrapper icon-yellow">
                                <i class="las la-cog"></i>
                            </div>
                            <div>
                                <div class="stat-number">{{ number_format($connectedAccountsCount, 2) }}</div>
                                <p class="stat-label">Active Gateways</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 3 Bottom Service Credit Cards (Exact Match) -->
                <div class="row g-4">
                    
                    <!-- SMS Credit Card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="credit-service-card">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="service-icon-box service-icon-sms">
                                        <i class="las la-comment"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold" style="font-size: 11px;">SMS Credit</div>
                                        <h5 class="fw-bold text-white mb-0">Unlimited</h5>
                                    </div>
                                </div>

                                <div class="limit-pill">
                                    <i class="las la-check"></i> Unlimited Daily Limit
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('user.plans.index') }}" class="btn-buy-credit">
                                    Buy Credit
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Email Credit Card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="credit-service-card">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="service-icon-box service-icon-email">
                                        <i class="las la-envelope"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold" style="font-size: 11px;">Email Credit</div>
                                        <h5 class="fw-bold text-white mb-0">Disabled</h5>
                                    </div>
                                </div>

                                <div class="limit-pill">
                                    <i class="las la-check"></i> Unlimited Daily Limit
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('user.plans.index') }}" class="btn-buy-credit">
                                    Buy Credit
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Credit Card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="credit-service-card">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="service-icon-box service-icon-whatsapp">
                                        <i class="lab la-whatsapp"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-bold" style="font-size: 11px;">Whatsapp Credit</div>
                                        <h5 class="fw-bold text-white mb-0">Unlimited</h5>
                                    </div>
                                </div>

                                <div class="limit-pill">
                                    <i class="las la-check"></i> Unlimited Daily Limit
                                </div>
                            </div>

                            <div>
                                @if($connectedAccountsCount > 0)
                                    <a href="{{ route('user.whatsapp.index') }}" class="btn-buy-credit" style="background: rgba(0, 208, 132, 0.15); border-color: rgba(0, 208, 132, 0.3); color: var(--dash-green);">
                                        <i class="lab la-whatsapp me-1"></i> Manage Gateway
                                    </a>
                                @else
                                    <a href="{{ route('user.whatsapp.create') }}" class="btn-buy-credit" style="background: rgba(255, 65, 108, 0.15); border-color: rgba(255, 65, 108, 0.3); color: var(--dash-pink);">
                                        + Connect WhatsApp Gateway
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

@endsection

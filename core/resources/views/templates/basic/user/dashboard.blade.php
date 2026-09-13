@extends($activeTemplate . 'layouts.master')
@section('content')

<style>
    /* Custom Dark SaaS Theme Matching Reference Template */
    .saas-theme-wrapper {
        background-color: #121318;
        color: #e2e8f0;
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .saas-sidebar {
        background-color: #181920;
        width: 105px;
        min-height: 100vh;
        border-right: 1px solid #232530;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 0;
        flex-shrink: 0;
    }
    .saas-sidebar .brand-logo-box {
        width: 60px;
        height: 60px;
        background-color: #232530;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #718096;
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 25px;
        overflow: hidden;
    }
    .saas-sidebar .brand-logo-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
    .saas-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 85px;
        padding: 12px 0;
        margin-bottom: 8px;
        border-radius: 10px;
        color: #8c93a4;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .saas-nav-item i {
        font-size: 22px;
        margin-bottom: 4px;
    }
    .saas-nav-item span {
        font-size: 11px;
        font-weight: 500;
    }
    .saas-nav-item:hover {
        color: #ffffff;
        background-color: #22242e;
    }
    .saas-nav-item.active {
        background-color: #e53e3e20;
        color: #f56565;
    }
    .saas-nav-item.active .icon-box {
        background-color: #e53e3e;
        color: #ffffff;
    }
    .saas-main {
        flex-grow: 1;
        background-color: #121318;
        padding: 25px 35px;
        overflow-x: hidden;
    }
    .saas-topbar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
        margin-bottom: 25px;
    }
    .saas-topbar-icon {
        color: #a0aec0;
        font-size: 18px;
        cursor: pointer;
        transition: color 0.2s;
    }
    .saas-topbar-icon:hover {
        color: #ffffff;
    }
    .saas-user-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-left: 15px;
        border-left: 1px solid #2d3748;
    }
    .saas-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #4a5568;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e2e8f0;
        font-size: 12px;
        font-weight: bold;
    }
    .saas-card {
        background-color: #1a1b23;
        border: 1px solid #242632;
        border-radius: 12px;
        padding: 22px;
    }
    .setup-banner {
        background-color: #1a1b23;
        border: 1px solid #242632;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .setup-bell-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f56565, #e53e3e);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 20px;
        flex-shrink: 0;
    }
    .setup-item-row {
        background-color: #13141a;
        border: 1px solid #22242f;
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: border-color 0.2s;
    }
    .setup-item-row:hover {
        border-color: #3b82f6;
    }
    .stat-badge-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 12px;
    }
    .stat-badge-red { background-color: #e53e3e20; color: #f56565; }
    .stat-badge-green { background-color: #38a16920; color: #48bb78; }
    .stat-badge-blue { background-color: #3182ce20; color: #4299e1; }
    .stat-badge-gold { background-color: #d69e2e20; color: #ecc94b; }

    .service-credit-card {
        background-color: #1a1b23;
        border: 1px solid #242632;
        border-radius: 12px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .service-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .limit-pill {
        background-color: #162a22;
        color: #38a169;
        border: 1px solid #22543d;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 15px 0 20px 0;
        width: fit-content;
    }
    .btn-buy-credit {
        background-color: transparent;
        color: #ffffff;
        border: 1px solid #4a5568;
        border-radius: 8px;
        padding: 10px 0;
        width: 100%;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        display: block;
        transition: all 0.2s;
    }
    .btn-buy-credit:hover {
        border-color: #f56565;
        color: #f56565;
        background-color: #f5656510;
    }
</style>

<div class="saas-theme-wrapper d-flex">
    
    <!-- Left Vertical Dark Sidebar -->
    <aside class="saas-sidebar d-none d-md-flex">
        <div class="brand-logo-box">
            @if(siteLogo())
                <img src="{{ siteLogo() }}" alt="Logo">
            @else
                <span>160x160</span>
            @endif
        </div>

        <a href="{{ route('user.home') }}" class="saas-nav-item active">
            <i class="las la-th-large"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('user.contacts.index') }}" class="saas-nav-item">
            <i class="las la-address-book"></i>
            <span>Contacts</span>
        </a>

        <a href="{{ route('user.autoreply.index') }}" class="saas-nav-item">
            <i class="las la-envelope"></i>
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
            <i class="las la-cube"></i>
            <span>Gateway</span>
        </a>

        <a href="{{ route('user.transactions') }}" class="saas-nav-item">
            <i class="las la-chart-bar"></i>
            <span>Report</span>
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="saas-main">
        
        <!-- Topbar: Language, Dark Mode, Flag, Member Profile -->
        <div class="saas-topbar">
            <i class="las la-globe saas-topbar-icon" title="Language"></i>
            <i class="las la-sun saas-topbar-icon" title="Theme Mode"></i>
            <span class="fs-5" title="Region">🇺🇸</span>
            
            <div class="saas-user-badge">
                <div class="saas-user-avatar">
                    {{ strtoupper(substr($user->firstname ?: $user->username, 0, 2)) }}
                </div>
                <div>
                    <div class="text-muted" style="font-size: 11px;">Member</div>
                    <div class="text-white fw-bold" style="font-size: 13px;">{{ $user->fullname ?: $user->username }}</div>
                </div>
            </div>
        </div>

        <!-- Greeting Header -->
        <h2 class="text-white fw-bold mb-4" style="font-size: 26px;">
            Welcome Back, {{ $user->fullname ?: $user->username }}
        </h2>

        <!-- "Complete Your Setup" Card -->
        <div class="setup-banner">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="setup-bell-icon">
                        <i class="las la-bell"></i>
                    </div>
                    <div>
                        <h5 class="text-white fw-bold mb-1" style="font-size: 17px;">Complete Your Setup</h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Configure Your Gateways To Start Sending Messages</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-bold" style="font-size: 14px;">{{ $activeGateways > 0 ? '1/1' : '0/1' }}</span>
                    <div class="bg-dark rounded" style="width: 70px; height: 5px;">
                        <div class="{{ $activeGateways > 0 ? 'bg-success' : 'bg-danger' }} rounded h-100" style="width: {{ $activeGateways > 0 ? '100%' : '20%' }};"></div>
                    </div>
                </div>
            </div>

            <!-- Setup Item: WhatsApp Gateway -->
            <a href="{{ route('user.whatsapp.create') }}" class="setup-item-row">
                <div class="d-flex align-items-center gap-3">
                    <i class="lab la-whatsapp fs-3 text-muted"></i>
                    <div>
                        <div class="text-white fw-bold" style="font-size: 14px;">WhatsApp Gateway</div>
                        @if($activeGateways > 0)
                            <div class="text-success small fw-bold"><i class="las la-check-circle me-1"></i> Active & Connected ({{ $activeGateways }} Account{{ $activeGateways > 1 ? 's' : '' }})</div>
                        @else
                            <div class="text-danger small fw-bold">Add Your Gateway To Start Sending</div>
                        @endif
                    </div>
                </div>
                <div>
                    <i class="las la-arrow-right text-muted"></i>
                </div>
            </a>
        </div>

        <!-- 4 Stat Cards Row -->
        <div class="row g-4 mb-4">
            
            <!-- Stat 1: CONTACTS -->
            <div class="col-xl-3 col-sm-6">
                <div class="saas-card">
                    <div class="stat-badge-icon stat-badge-red">
                        <i class="las la-address-book"></i>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 28px; line-height: 1.1;">
                        {{ number_format($totalContacts, 2) }}
                    </div>
                    <div class="text-muted fw-bold mt-1" style="font-size: 12px; letter-spacing: 0.5px;">
                        CONTACTS
                    </div>
                </div>
            </div>

            <!-- Stat 2: GROUPS -->
            <div class="col-xl-3 col-sm-6">
                <div class="saas-card">
                    <div class="stat-badge-icon stat-badge-green">
                        <i class="las la-user-friends"></i>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 28px; line-height: 1.1;">
                        {{ number_format($totalGroups, 2) }}
                    </div>
                    <div class="text-muted fw-bold mt-1" style="font-size: 12px; letter-spacing: 0.5px;">
                        GROUPS
                    </div>
                </div>
            </div>

            <!-- Stat 3: MESSAGES TODAY -->
            <div class="col-xl-3 col-sm-6">
                <div class="saas-card">
                    <div class="stat-badge-icon stat-badge-blue">
                        <i class="las la-paper-plane"></i>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 28px; line-height: 1.1;">
                        {{ number_format($messagesToday, 2) }}
                    </div>
                    <div class="text-muted fw-bold mt-1" style="font-size: 12px; letter-spacing: 0.5px;">
                        MESSAGES TODAY
                    </div>
                </div>
            </div>

            <!-- Stat 4: ACTIVE GATEWAYS -->
            <div class="col-xl-3 col-sm-6">
                <div class="saas-card">
                    <div class="stat-badge-icon stat-badge-gold">
                        <i class="las la-cog"></i>
                    </div>
                    <div class="text-white fw-bold" style="font-size: 28px; line-height: 1.1;">
                        {{ number_format($activeGateways, 2) }}
                    </div>
                    <div class="text-muted fw-bold mt-1" style="font-size: 12px; letter-spacing: 0.5px;">
                        ACTIVE GATEWAYS
                    </div>
                </div>
            </div>

        </div>

        <!-- 3 Service / Credit Cards Row -->
        <div class="row g-4">
            
            <!-- Card 1: SMS Credit -->
            <div class="col-lg-4">
                <div class="service-credit-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="service-icon-box" style="background-color: #2b4c7e; color: #63b3ed;">
                                <i class="las la-comment-dots"></i>
                            </div>
                            <div>
                                <div class="text-muted small">SMS Credit</div>
                                <div class="text-white fw-bold fs-5">Unlimited</div>
                            </div>
                        </div>

                        <div class="limit-pill">
                            <i class="las la-check"></i> Unlimited Daily Limit
                        </div>
                    </div>

                    <a href="{{ route('user.plans.index') }}" class="btn-buy-credit">
                        Buy Credit
                    </a>
                </div>
            </div>

            <!-- Card 2: Email Credit -->
            <div class="col-lg-4">
                <div class="service-credit-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="service-icon-box" style="background-color: #5c2424; color: #fc8181;">
                                <i class="las la-envelope"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Email Credit</div>
                                <div class="text-white fw-bold fs-5">Disabled</div>
                            </div>
                        </div>

                        <div class="limit-pill">
                            <i class="las la-check"></i> Unlimited Daily Limit
                        </div>
                    </div>

                    <a href="{{ route('user.plans.index') }}" class="btn-buy-credit">
                        Buy Credit
                    </a>
                </div>
            </div>

            <!-- Card 3: Whatsapp Credit -->
            <div class="col-lg-4">
                <div class="service-credit-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="service-icon-box" style="background-color: #1c4532; color: #68d391;">
                                <i class="lab la-whatsapp"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Whatsapp Credit</div>
                                <div class="text-white fw-bold fs-5">Unlimited</div>
                            </div>
                        </div>

                        <div class="limit-pill">
                            <i class="las la-check"></i> Unlimited Daily Limit
                        </div>
                    </div>

                    <a href="{{ route('user.plans.index') }}" class="btn-buy-credit">
                        Buy Credit
                    </a>
                </div>
            </div>

        </div>

    </main>

</div>

@endsection

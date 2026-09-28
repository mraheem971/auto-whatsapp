@php
    $languages       = App\Models\Language::get();
    $defaultLanguage = App\Models\Language::where('code', config('app.locale'))->first();
@endphp

<header class="user-topbar bg-white border-bottom shadow-sm px-2 px-sm-3 py-2 d-flex align-items-center justify-content-between sticky-top">
    
    <!-- Left: Sleek Animated 3-Dash Hamburger & Brand/Page Title -->
    <div class="d-flex align-items-center gap-2 gap-sm-3 min-w-0">
        <button class="topbar-hamburger-btn" id="sidebarToggleBtn" type="button" aria-label="Toggle navigation" title="Menu">
            <span class="hamburger-box">
                <span class="hamburger-line line-top"></span>
                <span class="hamburger-line line-mid"></span>
                <span class="hamburger-line line-bot"></span>
            </span>
        </button>

        <!-- Brand Icon (Mobile) & Page Title -->
        <div class="topbar-title-wrapper d-flex align-items-center gap-2 min-w-0">
            <!-- Mobile Brand Icon -->
            <a href="{{ route('user.home') }}" class="d-inline-flex d-md-none align-items-center text-decoration-none flex-shrink-0">
                <img src="{{ siteFavicon() }}" alt="{{ gs('site_name') }}" class="rounded-2 shadow-sm" style="width: 28px; height: 28px; object-fit: contain;">
            </a>
            <div class="lh-sm min-w-0">
                <h5 class="mb-0 fw-bold text-dark fs-6 text-truncate topbar-page-heading">{{ __($pageTitle ?? 'Dashboard') }}</h5>
                <span class="d-none d-md-inline-block text-muted" style="font-size: 11px;">{{ gs('site_name') }} Portal</span>
            </div>
        </div>
    </div>

    <!-- Right: Utility Controls & User Profile (Icon-only) -->
    <div class="d-flex align-items-center gap-1 gap-sm-2 flex-shrink-0">
        
        <!-- Theme Toggle (Dark / Light) -->
        <button class="topbar-icon-btn" id="themeToggleBtn" type="button" title="Switch Theme">
            <i class="las la-moon fs-5" id="themeIcon"></i>
        </button>

        <!-- Language Switcher Dropdown (Icon Only) -->
        @if($languages->count() > 1)
            <div class="dropdown">
                <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Language ({{ strtoupper($defaultLanguage->code ?? 'EN') }})">
                    <i class="las la-globe fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2" style="min-width: 160px;">
                    <li class="dropdown-header text-uppercase fs-7 fw-bold text-muted px-3 py-1">@lang('Select Language')</li>
                    @foreach($languages as $lang)
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 langSel {{ config('app.locale') == $lang->code ? 'active bg-light text-primary fw-bold' : '' }}" href="javascript:void(0)" data-code="{{ $lang->code }}">
                                <span>{{ __($lang->name) }}</span>
                                @if(config('app.locale') == $lang->code)
                                    <i class="las la-check text-primary"></i>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Quick WhatsApp Shortcut (Icon Only) -->
        <a href="{{ route('user.whatsapp.index') }}" class="topbar-icon-btn text-success" title="WhatsApp Devices & Gateway">
            <i class="lab la-whatsapp fs-4"></i>
        </a>

        <!-- User Profile Avatar (Icon Only) -->
        <div class="dropdown">
            <button class="topbar-avatar-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ auth()->user()->fullname ?? auth()->user()->username }}">
                <span class="avatar-letter">{{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}</span>
                <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle">
                    <span class="visually-hidden">Online</span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="min-width: 230px;">
                <li class="px-3 py-2 border-bottom mb-1">
                    <div class="d-flex align-items-center gap-2">
                        <div class="topbar-avatar-btn bg--base text-white" style="width: 36px; height: 36px; font-size: 14px; cursor: default;">
                            {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <span class="fw-bold d-block text-dark text-truncate" style="font-size: 13px;">{{ auth()->user()->fullname }}</span>
                            <small class="text-muted d-block text-truncate" style="font-size: 11px;">{{ auth()->user()->email }}</small>
                        </div>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2" href="{{ route('user.profile.setting') }}">
                        <i class="las la-user-cog fs-5 text-muted"></i>
                        <span>@lang('Profile Settings')</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2" href="{{ route('user.change.password') }}">
                        <i class="las la-key fs-5 text-muted"></i>
                        <span>@lang('Change Password')</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2" href="{{ route('user.twofactor') }}">
                        <i class="las la-shield-alt fs-5 text-muted"></i>
                        <span>@lang('2FA Security')</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2" href="{{ route('user.plans.index') }}">
                        <i class="las la-crown fs-5 text-warning"></i>
                        <span>@lang('My Plan & Quotas')</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2 text-danger fw-semibold" href="{{ route('user.logout') }}">
                        <i class="las la-sign-out-alt fs-5"></i>
                        <span>@lang('Logout')</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>

</header>

<style>
/* ================= Modern 3-Dash Hamburger Button ================= */
.topbar-hamburger-btn {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background-color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    flex-shrink: 0;
}

.topbar-hamburger-btn:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.topbar-hamburger-btn:active {
    transform: scale(0.94);
    background-color: #f1f5f9;
}

/* 3 Rounded Dash Lines */
.topbar-hamburger-btn .hamburger-box {
    width: 20px;
    height: 15px;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.topbar-hamburger-btn .hamburger-line {
    display: block;
    height: 2.5px;
    width: 100%;
    background-color: #0f172a;
    border-radius: 4px;
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                opacity 0.2s ease, 
                width 0.2s ease,
                background-color 0.25s ease;
    transform-origin: center;
}

/* Middle dash has sleek subtle taper */
.topbar-hamburger-btn .line-mid {
    width: 15px;
}

.topbar-hamburger-btn:hover .line-mid {
    width: 20px;
}

.topbar-hamburger-btn:hover .hamburger-line {
    background-color: #25d366;
}

/* Animated active state (when sidebar is open) */
.user-layout.mobile-sidebar-open .topbar-hamburger-btn,
.topbar-hamburger-btn.is-active {
    background-color: #f0fdf4;
    border-color: #86efac;
    box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.18);
}

.user-layout.mobile-sidebar-open .topbar-hamburger-btn .hamburger-line,
.topbar-hamburger-btn.is-active .hamburger-line {
    background-color: #16a34a;
}

.user-layout.mobile-sidebar-open .topbar-hamburger-btn .line-top,
.topbar-hamburger-btn.is-active .line-top {
    transform: translateY(6.25px) rotate(45deg);
}

.user-layout.mobile-sidebar-open .topbar-hamburger-btn .line-mid,
.topbar-hamburger-btn.is-active .line-mid {
    opacity: 0;
    transform: scaleX(0);
}

.user-layout.mobile-sidebar-open .topbar-hamburger-btn .line-bot,
.topbar-hamburger-btn.is-active .line-bot {
    transform: translateY(-6.25px) rotate(-45deg);
}

/* Topbar Heading on Mobile */
.topbar-page-heading {
    font-size: 15px !important;
    max-width: 160px;
}

@media (max-width: 575.98px) {
    .topbar-hamburger-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
    }
    .topbar-page-heading {
        font-size: 14px !important;
        max-width: 125px;
    }
    .topbar-icon-btn {
        width: 35px !important;
        height: 35px !important;
    }
    .topbar-avatar-btn {
        width: 35px !important;
        height: 35px !important;
        font-size: 13px !important;
    }
}

/* Topbar Utility Buttons */
.topbar-icon-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid #e9ecef;
    background-color: #f8f9fa;
    color: #495057;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
    flex-shrink: 0;
}
.topbar-icon-btn:hover, .topbar-icon-btn:focus {
    background-color: #e9ecef;
    color: #0d6efd;
    border-color: #dee2e6;
    outline: none;
    transform: translateY(-1px);
}
.topbar-icon-btn.text-success:hover {
    color: #198754 !important;
    background-color: #d1e7dd;
    border-color: #badbcc;
}

.topbar-avatar-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.topbar-avatar-btn:hover, .topbar-avatar-btn:focus {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    outline: none;
}

/* Dark Mode Overrides */
body.dark-mode .user-topbar,
.dark-theme .user-topbar {
    background-color: #0f172a !important;
    border-color: #1e293b !important;
}
body.dark-mode .user-topbar h5,
.dark-theme .user-topbar h5 {
    color: #f1f5f9 !important;
}
body.dark-mode .topbar-hamburger-btn,
.dark-theme .topbar-hamburger-btn {
    background-color: #1e293b;
    border-color: #334155;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
}
body.dark-mode .topbar-hamburger-btn:hover,
.dark-theme .topbar-hamburger-btn:hover {
    background-color: #243247;
    border-color: #475569;
}
body.dark-mode .topbar-hamburger-btn .hamburger-line,
.dark-theme .topbar-hamburger-btn .hamburger-line {
    background-color: #f1f5f9;
}
body.dark-mode .topbar-hamburger-btn:hover .hamburger-line,
.dark-theme .topbar-hamburger-btn:hover .hamburger-line {
    background-color: #25d366;
}
body.dark-mode .user-layout.mobile-sidebar-open .topbar-hamburger-btn,
.dark-theme .user-layout.mobile-sidebar-open .topbar-hamburger-btn {
    background-color: #064e3b;
    border-color: #059669;
}
body.dark-mode .user-layout.mobile-sidebar-open .topbar-hamburger-btn .hamburger-line,
.dark-theme .user-layout.mobile-sidebar-open .topbar-hamburger-btn .hamburger-line {
    background-color: #4ade80;
}
body.dark-mode .topbar-icon-btn,
.dark-theme .topbar-icon-btn {
    background-color: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
body.dark-mode .topbar-icon-btn:hover,
.dark-theme .topbar-icon-btn:hover {
    background-color: #334155;
    color: #38bdf8;
    border-color: #475569;
}
body.dark-mode .dropdown-menu,
.dark-theme .dropdown-menu {
    background-color: #1e293b;
    border: 1px solid #334155 !important;
}
body.dark-mode .dropdown-item,
.dark-theme .dropdown-item {
    color: #cbd5e1;
}
body.dark-mode .dropdown-item:hover,
.dark-theme .dropdown-item:hover {
    background-color: #334155;
    color: #fff;
}
body.dark-mode .dropdown-divider,
.dark-theme .dropdown-divider {
    border-color: #334155;
}
</style>

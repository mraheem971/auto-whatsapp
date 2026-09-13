@php
    $languages       = App\Models\Language::get();
    $defaultLanguage = App\Models\Language::where('code', config('app.locale'))->first();
@endphp

<header class="user-topbar bg-white border-bottom shadow-sm px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
    
    <!-- Left: Sidebar Toggle & Page Title -->
    <div class="d-flex align-items-center gap-3">
        <button class="topbar-icon-btn" id="sidebarToggleBtn" title="Toggle Sidebar">
            <i class="las la-bars fs-4"></i>
        </button>
        <div class="d-none d-md-block">
            <h5 class="mb-0 fw-bold text-dark fs-6">{{ __($pageTitle ?? 'Dashboard') }}</h5>
        </div>
    </div>

    <!-- Right: Utility Controls & User Profile (Icon-only) -->
    <div class="d-flex align-items-center gap-2">
        
        <!-- Theme Toggle (Dark / Light) -->
        <button class="topbar-icon-btn" id="themeToggleBtn" title="Switch Theme">
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
}
.topbar-avatar-btn:hover, .topbar-avatar-btn:focus {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    outline: none;
}
.dark-theme .topbar-icon-btn {
    background-color: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
.dark-theme .topbar-icon-btn:hover {
    background-color: #334155;
    color: #38bdf8;
    border-color: #475569;
}
.dark-theme .user-topbar {
    background-color: #0f172a !important;
    border-color: #1e293b !important;
}
.dark-theme .user-topbar h5 {
    color: #f1f5f9 !important;
}
.dark-theme .dropdown-menu {
    background-color: #1e293b;
    border: 1px solid #334155 !important;
}
.dark-theme .dropdown-item {
    color: #cbd5e1;
}
.dark-theme .dropdown-item:hover {
    background-color: #334155;
    color: #fff;
}
.dark-theme .dropdown-divider {
    border-color: #334155;
}
</style>

@php
    $languages       = App\Models\Language::get();
    $defaultLanguage = App\Models\Language::where('code', config('app.locale'))->first();
@endphp

<header class="user-topbar bg-white border-bottom shadow-sm px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
    
    <!-- Left: Sidebar Toggle & Quick Title -->
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" id="sidebarToggleBtn" style="width: 38px; height: 38px;" title="Toggle Sidebar">
            <i class="las la-bars fs-4"></i>
        </button>
        <div class="d-none d-md-block">
            <h5 class="mb-0 fw-bold text-dark">{{ __($pageTitle ?? 'Dashboard') }}</h5>
        </div>
    </div>

    <!-- Right: Utility Controls & User Profile -->
    <div class="d-flex align-items-center gap-2 gap-md-3">
        
        <!-- Theme Toggle (Dark / Light) -->
        <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center border" id="themeToggleBtn" style="width: 38px; height: 38px;" title="Switch Light/Dark Mode">
            <i class="las la-moon fs-4" id="themeIcon"></i>
        </button>

        <!-- Language Switcher Dropdown -->
        @if($languages->count() > 1)
            <div class="dropdown">
                <button class="btn btn-light btn-sm border d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="las la-globe fs-5 text-muted"></i>
                    <span class="d-none d-sm-inline fw-bold">{{ strtoupper($defaultLanguage->code ?? 'EN') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    @foreach($languages as $lang)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 langSel" href="javascript:void(0)" data-code="{{ $lang->code }}">
                                <i class="las la-check {{ config('app.locale') == $lang->code ? 'text-success' : 'text-transparent' }}"></i>
                                <span>{{ __($lang->name) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Quick WhatsApp Connection Status -->
        <a href="{{ route('user.whatsapp.index') }}" class="btn btn-light btn-sm border d-none d-sm-flex align-items-center gap-1 text-success fw-bold">
            <i class="lab la-whatsapp fs-5"></i>
            <span>@lang('WhatsApp')</span>
        </a>

        <!-- User Profile Dropdown Badge -->
        <div class="dropdown">
            <button class="btn btn-light border p-1 pe-2 rounded-pill d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar bg--base text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                    {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                </div>
                <div class="text-start d-none d-lg-block pe-1">
                    <span class="fw-bold d-block text-dark lh-1" style="font-size: 13px;">{{ auth()->user()->username }}</span>
                    <small class="text-success fw-bold" style="font-size: 11px;">${{ showAmount(auth()->user()->balance) }}</small>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width: 220px;">
                <li class="px-2 py-1 border-bottom mb-2">
                    <span class="fw-bold d-block text-dark">{{ auth()->user()->fullname }}</span>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </li>
                <li>
                    <a class="dropdown-item rounded d-flex align-items-center gap-2 py-2" href="{{ route('user.profile.setting') }}">
                        <i class="las la-user-cog fs-5 text-muted"></i>
                        <span>@lang('Profile Settings')</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded d-flex align-items-center gap-2 py-2" href="{{ route('user.change.password') }}">
                        <i class="las la-key fs-5 text-muted"></i>
                        <span>@lang('Change Password')</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded d-flex align-items-center gap-2 py-2" href="{{ route('user.twofactor') }}">
                        <i class="las la-shield-alt fs-5 text-muted"></i>
                        <span>@lang('2FA Security')</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded d-flex align-items-center gap-2 py-2" href="{{ route('user.plans.index') }}">
                        <i class="las la-crown fs-5 text-warning"></i>
                        <span>@lang('My Plan & Quotas')</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item rounded d-flex align-items-center gap-2 py-2 text-danger" href="{{ route('user.logout') }}">
                        <i class="las la-sign-out-alt fs-5"></i>
                        <span>@lang('Logout')</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>

</header>

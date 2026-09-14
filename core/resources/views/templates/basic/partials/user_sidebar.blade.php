<aside class="user-sidebar" id="userSidebar">
    <!-- Sidebar Header / Logo -->
    <div class="user-sidebar__header d-flex align-items-center justify-content-between p-3 border-bottom">
        <a href="{{ route('user.home') }}" class="user-sidebar__logo d-flex align-items-center text-decoration-none">
            <div class="logo-icon bg--base text-white rounded-3 d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                <i class="lab la-whatsapp fs-3"></i>
            </div>
            <div class="logo-text">
                <span class="fw-bold fs-5 text-white">{{ gs('site_name') }}</span>
                <small class="d-block text-white-50" style="font-size: 11px; margin-top: -3px;">WhatsApp Bot SaaS</small>
            </div>
        </a>
        <button class="btn btn-sm text-white d-lg-none" id="closeSidebarBtn">
            <i class="las la-times fs-4"></i>
        </button>
    </div>

    <!-- User Profile Badge Preview -->
    <div class="user-sidebar__profile p-3 border-bottom bg-black bg-opacity-25">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar bg--base text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 16px;">
                {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
            </div>
            <div class="overflow-hidden flex-grow-1">
                <h6 class="text-white mb-0 text-truncate fw-bold">{{ auth()->user()->fullname }}</h6>
                <small class="text-success d-block"><i class="las la-wallet me-1"></i>${{ showAmount(auth()->user()->balance) }}</small>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="user-sidebar__menu p-2" style="overflow-y: auto; max-height: calc(100vh - 160px);">
        <ul class="nav flex-column gap-1">
            
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.home') }}" href="{{ route('user.home') }}">
                    <i class="las la-home me-2 fs-5"></i>
                    <span>@lang('Dashboard')</span>
                </a>
            </li>

            <li class="nav-header text-uppercase text-white-50 px-3 pt-3 pb-1" style="font-size: 11px; font-weight: 700;">
                @lang('WhatsApp Engine')
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.whatsapp*') }}" href="{{ route('user.whatsapp.index') }}">
                    <i class="lab la-whatsapp me-2 fs-5 text-success"></i>
                    <span>@lang('WhatsApp Accounts')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.gateway*') }}" href="{{ route('user.gateway.index') }}">
                    <i class="las la-server me-2 fs-5 text-warning"></i>
                    <span>@lang('Gateway & API Hub')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.autoreply*') }}" href="{{ route('user.autoreply.index') }}">
                    <i class="las la-robot me-2 fs-5 text-primary"></i>
                    <span>@lang('Auto-Reply Bots')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.contacts*') }}" href="{{ route('user.contacts.index') }}">
                    <i class="las la-address-book me-2 fs-5 text-info"></i>
                    <span>@lang('Contacts & Lists')</span>
                </a>
            </li>

            <li class="nav-header text-uppercase text-white-50 px-3 pt-3 pb-1" style="font-size: 11px; font-weight: 700;">
                @lang('Broadcast & Messaging')
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.campaigns*') }}" href="{{ route('user.campaigns.index') }}">
                    <i class="las la-bullhorn me-2 fs-5 text-warning"></i>
                    <span>@lang('Run Campaigns')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.messages*') }}" href="{{ route('user.messages.index') }}">
                    <i class="las la-paper-plane me-2 fs-5 text-success"></i>
                    <span>@lang('Direct Messages & Logs')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.templates*') }}" href="{{ route('user.templates.index') }}">
                    <i class="las la-envelope-open-text me-2 fs-5 text-secondary"></i>
                    <span>@lang('Message Templates')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.settings.behavior*') }}" href="{{ route('user.settings.behavior.index') }}">
                    <i class="las la-user-shield me-2 fs-5 text-danger"></i>
                    <span>@lang('Anti-Ban Settings')</span>
                </a>
            </li>

            <li class="nav-header text-uppercase text-white-50 px-3 pt-3 pb-1" style="font-size: 11px; font-weight: 700;">
                @lang('Account & Billing')
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.plans*') }}" href="{{ route('user.plans.index') }}">
                    <i class="las la-crown me-2 fs-5 text-warning"></i>
                    <span>@lang('Subscription Plans')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.deposit.index') }}" href="{{ route('user.deposit.index') }}">
                    <i class="las la-coins me-2 fs-5 text-success"></i>
                    <span>@lang('Deposit Funds')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.transactions') }}" href="{{ route('user.transactions') }}">
                    <i class="las la-chart-bar me-2 fs-5 text-info"></i>
                    <span>@lang('Transactions & Logs')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.settings*') }}" href="{{ route('user.settings.index') }}">
                    <i class="las la-cog me-2 fs-5 text-primary"></i>
                    <span>@lang('Settings Hub')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('ticket.index') }}" href="{{ route('ticket.index') }}">
                    <i class="las la-ticket-alt me-2 fs-5 text-primary"></i>
                    <span>@lang('Support Ticket')</span>
                </a>
            </li>

            <li class="nav-item mt-3 pt-2 border-top border-secondary border-opacity-25">
                <a class="nav-link text-danger" href="{{ route('user.logout') }}">
                    <i class="las la-sign-out-alt me-2 fs-5"></i>
                    <span>@lang('Logout')</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

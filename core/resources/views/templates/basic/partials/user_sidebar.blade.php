<aside class="user-sidebar" id="userSidebar">
    <!-- Sidebar Header / Logo -->
    <div class="user-sidebar__header d-flex align-items-center justify-content-between p-3 border-bottom">
        <a href="{{ route('user.home') }}" class="user-sidebar__logo d-flex align-items-center text-decoration-none">
            <div class="logo-icon me-2 d-flex align-items-center justify-content-center">
                <img src="{{ siteFavicon() }}" alt="{{ gs('site_name') }}" class="rounded-2" style="width: 36px; height: 36px; object-fit: contain;">
            </div>
            <div class="logo-text">
                <span class="fw-bold fs-5 text-white d-block lh-1">{{ gs('site_name') }}</span>
                <small class="d-block text-white-50" style="font-size: 11px; margin-top: 3px;">{{ gs('site_name') }} SaaS</small>
            </div>
        </a>
        <button class="btn btn-sm text-white d-lg-none" id="closeSidebarBtn">
            <i class="las la-times fs-4"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="user-sidebar__menu p-2">
        <ul class="nav flex-column gap-1 pb-4">
            
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.home') }}" href="{{ route('user.home') }}">
                    <i class="las la-home me-2 fs-5"></i>
                    <span>@lang('Dashboard')</span>
                </a>
            </li>

            <!-- Section 1: WhatsApp Engine -->
            <li class="nav-header text-uppercase text-white-50 px-3 pt-3 pb-1" style="font-size: 11px; font-weight: 700;">
                @lang('WhatsApp Engine')
            </li>

            <!-- WhatsApp Accounts (With Submenu: Add, Active, Pending, All) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('user.whatsapp*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('user.whatsapp*') }}" href="javascript:void(0);">
                    <i class="lab la-whatsapp me-2 fs-5 text-success"></i>
                    <span>@lang('WhatsApp Accounts')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('user.whatsapp*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('user.whatsapp.create') }}" href="{{ route('user.whatsapp.create') }}">
                                <i class="las la-plus-circle me-2 text-success"></i>
                                <span>@lang('Add Account')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.whatsapp.index') && request('status') === 'active' ? 'active' : '' }}" href="{{ route('user.whatsapp.index', ['status' => 'active']) }}">
                                <i class="las la-check-circle me-2 text-info"></i>
                                <span>@lang('Active Accounts')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.whatsapp.index') && request('status') === 'pending' ? 'active' : '' }}" href="{{ route('user.whatsapp.index', ['status' => 'pending']) }}">
                                <i class="las la-hourglass-half me-2 text-warning"></i>
                                <span>@lang('Pending Accounts')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.whatsapp.index') && !request('status') ? 'active' : '' }}" href="{{ route('user.whatsapp.index') }}">
                                <i class="las la-list me-2"></i>
                                <span>@lang('All Accounts')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Auto-Reply Bots (With Submenu: Add, Active, Disabled, All) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('user.autoreply*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('user.autoreply*') }}" href="javascript:void(0);">
                    <i class="las la-robot me-2 fs-5 text-primary"></i>
                    <span>@lang('Auto-Reply Bots')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('user.autoreply*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link" href="{{ route('user.autoreply.index') }}#createBotModal">
                                <i class="las la-plus-circle me-2 text-primary"></i>
                                <span>@lang('Add Bot Rule')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.autoreply.index') && request('status') === '1' ? 'active' : '' }}" href="{{ route('user.autoreply.index', ['status' => 1]) }}">
                                <i class="las la-check-circle me-2 text-success"></i>
                                <span>@lang('Active Bots')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.autoreply.index') && request('status') === '0' ? 'active' : '' }}" href="{{ route('user.autoreply.index', ['status' => 0]) }}">
                                <i class="las la-pause-circle me-2 text-warning"></i>
                                <span>@lang('Disabled Bots')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.autoreply.index') && request('status') === null ? 'active' : '' }}" href="{{ route('user.autoreply.index') }}">
                                <i class="las la-list me-2"></i>
                                <span>@lang('All Bot Rules')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Contacts & Lists (With Submenu: All Contacts, Contact Lists) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('user.contacts*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('user.contacts*') }}" href="javascript:void(0);">
                    <i class="las la-address-book me-2 fs-5 text-info"></i>
                    <span>@lang('Contacts & Lists')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('user.contacts*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('user.contacts.index') }}" href="{{ route('user.contacts.index') }}">
                                <i class="las la-user-friends me-2 text-info"></i>
                                <span>@lang('All Contacts')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('user.contacts.lists') }}" href="{{ route('user.contacts.lists') }}">
                                <i class="las la-folder me-2 text-warning"></i>
                                <span>@lang('Contact Lists & Groups')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Gateway & API Hub (Single Item) -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.gateway*') }}" href="{{ route('user.gateway.index') }}">
                    <i class="las la-server me-2 fs-5 text-warning"></i>
                    <span>@lang('Gateway & API Hub')</span>
                </a>
            </li>

            <!-- Section 2: Broadcast & Messaging -->
            <li class="nav-header text-uppercase text-white-50 px-3 pt-3 pb-1" style="font-size: 11px; font-weight: 700;">
                @lang('Broadcast & Messaging')
            </li>

            <!-- Run Campaigns (With Submenu: Create, Active, Pending, All) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('user.campaigns*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('user.campaigns*') }}" href="javascript:void(0);">
                    <i class="las la-bullhorn me-2 fs-5 text-warning"></i>
                    <span>@lang('Run Campaigns')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('user.campaigns*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('user.campaigns.create') }}" href="{{ route('user.campaigns.create') }}">
                                <i class="las la-plus-circle me-2 text-warning"></i>
                                <span>@lang('Create Campaign')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.campaigns.index') && request('status') === 'active' ? 'active' : '' }}" href="{{ route('user.campaigns.index', ['status' => 'active']) }}">
                                <i class="las la-play-circle me-2 text-success"></i>
                                <span>@lang('Active Campaigns')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.campaigns.index') && request('status') === 'pending' ? 'active' : '' }}" href="{{ route('user.campaigns.index', ['status' => 'pending']) }}">
                                <i class="las la-hourglass-half me-2 text-info"></i>
                                <span>@lang('Pending Campaigns')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.campaigns.index') && !request('status') ? 'active' : '' }}" href="{{ route('user.campaigns.index') }}">
                                <i class="las la-list me-2"></i>
                                <span>@lang('All Campaigns')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Direct Messages & Logs (Single Item) -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.messages*') }}" href="{{ route('user.messages.index') }}">
                    <i class="las la-paper-plane me-2 fs-5 text-success"></i>
                    <span>@lang('Direct Messages & Logs')</span>
                </a>
            </li>

            <!-- Message Templates (With Submenu: Add, Text, Media, All) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('user.templates*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('user.templates*') }}" href="javascript:void(0);">
                    <i class="las la-envelope-open-text me-2 fs-5 text-secondary"></i>
                    <span>@lang('Message Templates')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('user.templates*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link" href="{{ route('user.templates.index') }}#createTemplateModal">
                                <i class="las la-plus-circle me-2 text-primary"></i>
                                <span>@lang('Add Template')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.templates.index') && request('type') === 'text' ? 'active' : '' }}" href="{{ route('user.templates.index', ['type' => 'text']) }}">
                                <i class="las la-file-alt me-2 text-info"></i>
                                <span>@lang('Text Templates')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.templates.index') && in_array(request('type'), ['image', 'video', 'document']) ? 'active' : '' }}" href="{{ route('user.templates.index', ['type' => 'image']) }}">
                                <i class="las la-photo-video me-2 text-warning"></i>
                                <span>@lang('Media Templates')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ request()->routeIs('user.templates.index') && !request('type') ? 'active' : '' }}" href="{{ route('user.templates.index') }}">
                                <i class="las la-list me-2"></i>
                                <span>@lang('All Templates')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Anti-Ban Settings (Single Item) -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.settings.behavior*') }}" href="{{ route('user.settings.behavior.index') }}">
                    <i class="las la-user-shield me-2 fs-5 text-danger"></i>
                    <span>@lang('Anti-Ban Settings')</span>
                </a>
            </li>

            <!-- Section 3: Account & Billing -->
            <li class="nav-header text-uppercase text-white-50 px-3 pt-3 pb-1" style="font-size: 11px; font-weight: 700;">
                @lang('Account & Billing')
            </li>

            <!-- Subscription Plans (Single Item) -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.plans*') }}" href="{{ route('user.plans.index') }}">
                    <i class="las la-crown me-2 fs-5 text-warning"></i>
                    <span>@lang('Subscription Plans')</span>
                </a>
            </li>

            <!-- Deposit Funds (With Submenu: Deposit Now, Deposit History) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('user.deposit*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('user.deposit*') }}" href="javascript:void(0);">
                    <i class="las la-coins me-2 fs-5 text-success"></i>
                    <span>@lang('Deposit Funds')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('user.deposit*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('user.deposit.index') }}" href="{{ route('user.deposit.index') }}">
                                <i class="las la-wallet me-2 text-success"></i>
                                <span>@lang('Deposit Now')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('user.deposit.history') }}" href="{{ route('user.deposit.history') }}">
                                <i class="las la-history me-2 text-info"></i>
                                <span>@lang('Deposit History')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Transactions & Logs (Single Item) -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.transactions') }}" href="{{ route('user.transactions') }}">
                    <i class="las la-chart-bar me-2 fs-5 text-info"></i>
                    <span>@lang('Transactions & Logs')</span>
                </a>
            </li>

            <!-- Settings Hub (Single Item) -->
            <li class="nav-item">
                <a class="nav-link text-white {{ menuActive('user.settings*') }}" href="{{ route('user.settings.index') }}">
                    <i class="las la-cog me-2 fs-5 text-primary"></i>
                    <span>@lang('Settings Hub')</span>
                </a>
            </li>

            <!-- Support Ticket (With Submenu: Create Ticket, All Tickets) -->
            <li class="nav-item user-sidebar-dropdown {{ menuActive('ticket*') ? 'open' : '' }}">
                <a class="nav-link text-white user-dropdown-toggle {{ menuActive('ticket*') }}" href="javascript:void(0);">
                    <i class="las la-ticket-alt me-2 fs-5 text-primary"></i>
                    <span>@lang('Support Ticket')</span>
                    <i class="las la-angle-down ms-auto dropdown-chevron"></i>
                </a>
                <div class="user-submenu" style="{{ menuActive('ticket*') ? 'display: block;' : '' }}">
                    <ul class="nav flex-column ps-2 py-1">
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('ticket.open') }}" href="{{ route('ticket.open') }}">
                                <i class="las la-plus-circle me-2 text-primary"></i>
                                <span>@lang('Create Ticket')</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="submenu-link {{ menuActive('ticket.index') }}" href="{{ route('ticket.index') }}">
                                <i class="las la-list me-2"></i>
                                <span>@lang('All Tickets')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Logout -->
            <li class="nav-item mt-3 pt-2 border-top border-secondary border-opacity-25">
                <a class="nav-link text-danger" href="{{ route('user.logout') }}">
                    <i class="las la-sign-out-alt me-2 fs-5"></i>
                    <span>@lang('Logout')</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

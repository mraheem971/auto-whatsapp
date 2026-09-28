@php
    $languages       = App\Models\Language::get();
    $defaultLanguage = App\Models\Language::where('code', config('app.locale'))->first();
    $pages           = App\Models\Page::where('tempname', $activeTemplate)->where('is_default', Status::NO)->get();
@endphp

<header class="header" id="header">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand logo" href="{{ route('home') }}"><img src="{{ siteLogo() }}" alt="logo"></a>
            @auth
            <div class="header-account-button d-lg-none d-block">
                <span class="account-icon ">
                    <i class="las la-user"></i>
                </span>
            </div>
            @endauth
            <button class="navbar-toggler header-button modern-frontend-toggle" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" type="button" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="hamburger-box">
                    <span class="hamburger-line line-top"></span>
                    <span class="hamburger-line line-mid"></span>
                    <span class="hamburger-line line-bot"></span>
                </span>
            </button>

            <div class="navbar-collapse collapse" id="navbarSupportedContent">
                <ul class="navbar-nav nav-menu align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}" aria-current="page">@lang('Home')</a>
                    </li>
                    @foreach ($pages as $page)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pages', [$page->slug]) }}" aria-current="page"> {{ __($page->name) }} </a>
                        </li>
                    @endforeach
                    <li class="nav-item {{menuActive('buy.account')}}">
                        <a class="nav-link" href="{{ route('buy.account') }}" aria-current="page"> @lang('Buy Account') </a>
                    </li>
                    <li class="nav-item {{menuActive('blogs')}}">
                        <a class="nav-link" href="{{ route('blogs') }}" aria-current="page"> @lang('Blog') </a>
                    </li>
                    <li class="nav-item {{menuActive('contact')}}">
                        <a class="nav-link" href="{{ route('contact') }}" aria-current="page"> @lang('Contact') </a>
                    </li>

                    <li class="nav-item d-block d-lg-none">
                        <div class="top-button d-flex">
                            <div class="top-button__button">
                                <a class="btn btn--base" href="{{ route('user.whatsapp.create') }}"> <span class="icon"> <i class="lab la-whatsapp"></i>
                                    </span> @lang('Connect WhatsApp') </a>
                            </div>
                           
                        </div>
                    </li>
                </ul>
                <div class="d-none d-lg-block">
                    <div class="top-button d-flex justify-content-between align-items-center flex-wrap">
                        <div class="top-button__button">
                            <a class="btn btn--base" href="{{ route('user.whatsapp.create') }}"> 
                                <span class="icon"> <i class="lab la-whatsapp"></i></span> @lang('Connect WhatsApp') 
                            </a>
                        </div>
                        <div class="top-header__login">
                            <div class="user-info">
                                @if (auth()->check())
                                    <button class="user-info__button flex-align">
                                        <span class="user-info__icon">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        @lang('My Account')
                                    </button>

                                    <ul class="user-info-dropdown">
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.home')}} user-info-dropdown__link" href="{{ route('user.home') }}">
                                                <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                                                <span class="text"> @lang('Dashboard') </span>
                                            </a>
                                        </li>
                                      
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.whatsapp*')}} user-info-dropdown__link" href="{{ route('user.whatsapp.index') }}">
                                                <span class="icon"><i class="lab la-whatsapp"></i></span>
                                                <span class="text"> @lang('WhatsApp Accounts') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.autoreply*')}} user-info-dropdown__link" href="{{ route('user.autoreply.index') }}">
                                                <span class="icon"><i class="las la-robot"></i></span>
                                                <span class="text"> @lang('Auto-Reply Bots') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.templates*')}} user-info-dropdown__link" href="{{ route('user.templates.index') }}">
                                                <span class="icon"><i class="las la-envelope-open-text"></i></span>
                                                <span class="text"> @lang('Message Templates') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.campaigns*')}} user-info-dropdown__link" href="{{ route('user.campaigns.index') }}">
                                                <span class="icon"><i class="las la-bullhorn"></i></span>
                                                <span class="text"> @lang('Run Campaigns') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.contacts*')}} user-info-dropdown__link" href="{{ route('user.contacts.index') }}">
                                                <span class="icon"><i class="las la-address-book"></i></span>
                                                <span class="text"> @lang('Contacts & Lists') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.settings.behavior*')}} user-info-dropdown__link" href="{{ route('user.settings.behavior.index') }}">
                                                <span class="icon"><i class="las la-user-shield"></i></span>
                                                <span class="text"> @lang('Anti-Ban Settings') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.plans*')}} user-info-dropdown__link" href="{{ route('user.plans.index') }}">
                                                <span class="icon"><i class="las la-crown"></i></span>
                                                <span class="text"> @lang('Subscription Plans') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.deposit.index')}} user-info-dropdown__link" href="{{ route('user.deposit.index') }}">
                                                <span class="icon"> <i class="las la-coins"></i> </span>
                                                <span class="text"> @lang('Deposit Funds') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('ticket.index')}} user-info-dropdown__link" href="{{ route('ticket.index') }}">
                                                <span class="icon"> <i class="las la-ticket-alt"></i> </span>
                                                <span class="text"> @lang('Support Ticket') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.settings*')}} user-info-dropdown__link" href="{{ route('user.settings.index') }}">
                                                <span class="icon"><i class="las la-cog"></i></span>
                                                <span class="text"> @lang('Settings Hub') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.twofactor')}} user-info-dropdown__link" href="{{ route('user.twofactor') }}">
                                                <span class="icon"> <i class="fas fa-shield-alt"></i> </span>
                                                <span class="text"> @lang('2FA Security') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="user-info-dropdown__link text-danger" href="{{ route('user.logout') }}">
                                                <span class="icon"> <i class="fas fa-sign-out-alt"></i> </span>
                                                <span class="text"> @lang('Logout') </span>
                                            </a>
                                        </li>
                                    </ul>
                                @else
                                    <button class="user-info__button icon">
                                        <a href="{{ route('user.login') }}" class="user-info__button-link"></a>
                                        <span class="user-info__icon">
                                            <i class="las la-sign-in-alt"></i>
                                        </span>@lang('Login')
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

    @auth
    <div class="user-dropdown-wrapper">
        <span class="user-dropdown-wrapper__close d-lg-none d-block"><i class="las la-times"></i></span>
        <ul class="user-info-dropdown">
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.home')}} user-info-dropdown__link" href="{{ route('user.home') }}">
                    <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="text"> @lang('Dashboard') </span>
                </a>
            </li>
      
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.account.listing.index')}} user-info-dropdown__link" href="{{ route('user.account.listing.index') }}">
                    <span class="icon"><i class="fas fa-list-ul"></i></span>
                    <span class="text"> @lang('Account Listing') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.account.listing.my.bid')}} user-info-dropdown__link" href="{{ route('user.account.listing.my.bid') }}">
                    <span class="icon"><i class="fas fa-gavel"></i></span>
                    <span class="text"> @lang('My Bids') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.account.listing.purchase')}} user-info-dropdown__link" href="{{ route('user.account.listing.purchase') }}">
                    <span class="icon"><i class="fas fa-shopping-basket"></i></span>
                    <span class="text"> @lang('Purchase Account') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.deposit.index')}} user-info-dropdown__link" href="{{ route('user.deposit.index') }}">
                    <span class="icon"> <i class="las la-coins"></i> </span>
                    <span class="text"> @lang('Deposit') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.deposit.history')}} user-info-dropdown__link" href="{{ route('user.deposit.history') }}">
                    <span class="icon"> <i class="las la-file-invoice-dollar"></i> </span>
                    <span class="text"> @lang('Deposit History') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.withdraw')}} user-info-dropdown__link" href="{{ route('user.withdraw') }}">
                    <span class="icon"> <i class="las la-hand-holding-usd"></i> </span>
                    <span class="text"> @lang('Withdraw') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.withdraw.history')}} user-info-dropdown__link" href="{{ route('user.withdraw.history') }}">
                    <span class="icon"> <i class="las la-file-invoice-dollar"></i></span>
                    <span class="text"> @lang('Withdraw History') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.transactions')}} user-info-dropdown__link" href="{{ route('user.transactions') }}">
                    <span class="icon"> <i class="far fa-file-alt"></i> </span>
                    <span class="text"> @lang('Transaction History') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('ticket.index')}} user-info-dropdown__link" href="{{ route('ticket.index') }}">
                    <span class="icon"> <i class="las la-ticket-alt"></i> </span>
                    <span class="text"> @lang('My Ticket') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.general.profile')}} user-info-dropdown__link" href="{{ route('user.general.profile') }}">
                    <span class="icon"><i class="far fa-user"></i></span>
                    <span class="text"> @lang('Account Details') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.twofactor')}} user-info-dropdown__link" href="{{ route('user.twofactor') }}">
                    <span class="icon"> <i class="fas fa-shield-alt"></i> </span>
                    <span class="text"> @lang('2FA Security') </span>
                </a>
            </li>
            <li class="user-info-dropdown__item">
                <a class="{{menuActive('user.logout')}} user-info-dropdown__link" href="{{ route('user.logout') }}">
                    <span class="icon"> <i class="fas fa-sign-out-alt"></i> </span>
                    <span class="text"> @lang('Logout') </span>
                </a>
            </li>
        </ul>
    </div>
    @endauth

<style>
/* ================= Modern Frontend 3-Dash Hamburger Toggle ================= */
.navbar-toggler.modern-frontend-toggle {
    width: 42px !important;
    height: 42px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    background: rgba(255, 255, 255, 0.12) !important;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}

.navbar-toggler.modern-frontend-toggle:hover,
.navbar-toggler.modern-frontend-toggle:focus {
    background: rgba(255, 255, 255, 0.22) !important;
    border-color: rgba(255, 255, 255, 0.45) !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18) !important;
    outline: none !important;
    transform: translateY(-1px);
}

.navbar-toggler.modern-frontend-toggle:active {
    transform: scale(0.95);
}

.navbar-toggler.modern-frontend-toggle .hamburger-box {
    width: 20px;
    height: 15px;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.navbar-toggler.modern-frontend-toggle .hamburger-line {
    display: block;
    height: 2.5px;
    width: 100%;
    background-color: #ffffff;
    border-radius: 4px;
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                opacity 0.2s ease, 
                width 0.2s ease,
                background-color 0.25s ease;
    transform-origin: center;
}

.navbar-toggler.modern-frontend-toggle .line-mid {
    width: 15px;
}

.navbar-toggler.modern-frontend-toggle:hover .line-mid {
    width: 20px;
}

/* Animated active state (when menu is collapsed/expanded) */
.navbar-toggler.modern-frontend-toggle[aria-expanded="true"] {
    background: rgba(37, 211, 102, 0.25) !important;
    border-color: rgba(37, 211, 102, 0.6) !important;
    box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.25) !important;
}

.navbar-toggler.modern-frontend-toggle[aria-expanded="true"] .hamburger-line {
    background-color: #25d366;
}

.navbar-toggler.modern-frontend-toggle[aria-expanded="true"] .line-top {
    transform: translateY(6.25px) rotate(45deg);
}

.navbar-toggler.modern-frontend-toggle[aria-expanded="true"] .line-mid {
    opacity: 0;
    transform: scaleX(0);
}

.navbar-toggler.modern-frontend-toggle[aria-expanded="true"] .line-bot {
    transform: translateY(-6.25px) rotate(-45deg);
}

/* Override old font icon content rules */
.navbar-toggler.modern-frontend-toggle[aria-expanded=true] i::before {
    display: none !important;
}

/* Mobile dropdown menu polish */
@media (max-width: 991.98px) {
    .header .navbar-collapse {
        background: #111b21 !important;
        border-radius: 16px !important;
        padding: 16px 20px !important;
        margin-top: 14px !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
    }
}
</style>
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
            <button class="navbar-toggler header-button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" type="button" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span id="hiddenNav"><i class="las la-bars"></i></span>
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
                                            <a class="{{menuActive('ticket.index')}} user-info-dropdown__link" href="{{ route('ticket.index') }}">
                                                <span class="icon"> <i class="las la-ticket-alt"></i> </span>
                                                <span class="text"> @lang('Support Ticket') </span>
                                            </a>
                                        </li>
                                        <li class="user-info-dropdown__item">
                                            <a class="{{menuActive('user.profile.setting')}} user-info-dropdown__link" href="{{ route('user.profile.setting') }}">
                                                <span class="icon"><i class="far fa-user"></i></span>
                                                <span class="text"> @lang('Profile Setting') </span>
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
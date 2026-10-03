@extends($activeTemplate . 'layouts.app')
@section('panel')

@auth
    <!-- Authenticated User Portal Layout with Fixed Collapsible Sidebar -->
    <div class="user-layout" id="userLayout">
        
        <!-- Sidebar Overlay (Mobile) -->
        <div class="user-sidebar-backdrop d-lg-none" id="userSidebarBackdrop"></div>

        <!-- Fixed Collapsible Sidebar -->
        @include($activeTemplate . 'partials.user_sidebar')

        <!-- Main Wrapper -->
        <div class="user-main-wrapper" id="userMainWrapper">
            
            <!-- Top Navigation Header with Utility Controls -->
            @include($activeTemplate . 'partials.user_topbar')

            @if(auth()->check() && !auth()->user()->hasActiveSubscription())
                <div class="px-3 pt-3 px-md-4 pt-md-3">
                    <div class="alert alert-warning border-0 shadow-sm rounded-3 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-0" style="background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%); border-left: 4px solid #f59e0b !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="las la-exclamation-triangle fs-4 text-warning"></i>
                            <div>
                                <strong class="text-dark small d-block">@lang('Trial Period / Subscription Expired')</strong>
                                <span class="text-muted small">@lang('Keyword bots and campaign broadcasts are automatically paused. Subscribe to a plan to resume automated messaging.')</span>
                            </div>
                        </div>
                        <a href="{{ route('user.plans.index') }}" class="btn btn-warning btn-sm fw-bold px-3 py-1 text-dark shadow-sm">
                            <i class="las la-bolt me-1"></i> @lang('Choose Plan')
                        </a>
                    </div>
                </div>
            @endif

            <!-- Main Dynamic Content -->
            <main class="user-content-body p-3 p-md-4">
                @yield('content')
            </main>

            <!-- Compact Footer -->
            <footer class="user-footer text-center py-3 border-top bg-white small text-muted">
                &copy; {{ date('Y') }} {{ gs('site_name') }} &bull; @lang('All Rights Reserved').
            </footer>
        </div>
    </div>
@else
    <!-- Public Guest Layout -->
    @include($activeTemplate . 'partials.header')
    
    <div class="root">
        @yield('content')
    </div>

    @include($activeTemplate . 'partials.footer')
@endauth

@endsection

@push('style')
<style>
    /* ================= Modern User Portal CSS ================= */
    :root {
        --sidebar-width: 270px;
        --sidebar-bg: #111b21;
        --sidebar-active-bg: rgba(37, 211, 102, 0.15);
        --sidebar-active-text: #25d366;
        --topbar-height: 62px;
        --body-bg-light: #f4f6f9;
        --card-bg-light: #ffffff;
        --text-color-light: #1e293b;
        --text-muted-light: #64748b;
        --border-color-light: #e2e8f0;
    }

    /* ================= Light Mode (High Contrast & Crystal Clear Text) ================= */
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #0f172a;
        background-color: #f8fafc;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        color: #0f172a !important;
        font-weight: 700;
        letter-spacing: -0.2px;
    }

    .text-dark {
        color: #0f172a !important;
        font-weight: 600;
    }

    /* Crisp, High-Contrast Labels & Form Elements */
    label, .form-label, .form-group label, .form-check-label {
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 13.5px;
        margin-bottom: 6px;
        letter-spacing: 0.1px;
    }

    .form-check-label {
        margin-bottom: 0 !important;
        cursor: pointer;
    }

    .connection-method-card {
        background-color: #ffffff;
        border: 1.5px solid #cbd5e1 !important;
        transition: all 0.2s ease-in-out;
    }

    .connection-method-card:hover {
        border-color: #25d366 !important;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.1);
    }

    .connection-method-card:has(input:checked) {
        border-color: #25d366 !important;
        background-color: rgba(37, 211, 102, 0.06) !important;
    }

    .form-control, .form-select, textarea {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 14px;
        padding: 9px 13px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .form-control::placeholder, textarea::placeholder {
        color: #64748b !important;
        opacity: 1;
        font-weight: 400;
    }

    .form-control:focus, .form-select:focus, textarea:focus {
        border-color: #25d366 !important;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2) !important;
        outline: none;
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .input-group-text {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border: 1.5px solid #cbd5e1 !important;
        font-weight: 600;
    }

    /* Enhanced, fully legible subtitles & helper text */
    .text-muted, .form-text, small.text-muted, p.text-muted {
        color: #475569 !important;
        font-size: 12.5px;
        font-weight: 500;
    }

    /* Modal System */
    .modal-content {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border: none !important;
        border-radius: 14px !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18) !important;
        overflow: hidden;
    }

    .modal-header {
        background-color: #075e54 !important;
        color: #ffffff !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
        padding: 16px 24px !important;
    }

    .modal-header .modal-title {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 17px;
    }

    .modal-body {
        padding: 24px !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .modal-footer {
        padding: 16px 24px !important;
        background-color: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
    }

    /* Cards & Tables */
    .card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        background-color: #ffffff;
    }



    .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a !important;
        font-weight: 700;
    }

    .table {
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .table tbody {
        background-color: #ffffff !important;
    }

    .table tbody tr {
        background-color: #ffffff !important;
    }

    .table thead th,
    .table-light th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #cbd5e1 !important;
    }

    .table tbody td {
        background-color: #ffffff !important;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
        font-size: 14px;
        font-weight: 500;
    }

    .badge.bg-light {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        font-weight: 600;
    }

    .bg-light {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }

    /* ================= Dark Mode Overrides ================= */
    body.dark-mode {
        --body-bg-light: #0b141a;
        --card-bg-light: #111b21;
        --text-color-light: #e9edef;
        background-color: #0b141a !important;
        color: #e9edef !important;
    }

    body.dark-mode .user-layout {
        background-color: #0b141a !important;
    }

    body.dark-mode .user-topbar,
    body.dark-mode .card,
    body.dark-mode .user-footer,
    body.dark-mode .dropdown-menu {
        background-color: #111b21 !important;
        color: #e9edef !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .card-header,
    body.dark-mode .card-footer {
        background-color: #182229 !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #e9edef !important;
    }

    body.dark-mode .text-dark,
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
    body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
        color: #e9edef !important;
    }

    body.dark-mode label,
    body.dark-mode .form-label,
    body.dark-mode .form-group label,
    body.dark-mode .form-check-label {
        color: #e9edef !important;
        font-weight: 600 !important;
    }

    body.dark-mode .connection-method-card {
        background-color: #202c33 !important;
        border: 1.5px solid #2a3942 !important;
    }

    body.dark-mode .connection-method-card:hover {
        border-color: #25d366 !important;
    }

    body.dark-mode .connection-method-card:has(input:checked) {
        border-color: #25d366 !important;
        background-color: rgba(37, 211, 102, 0.12) !important;
    }

    body.dark-mode .text-muted,
    body.dark-mode .form-text,
    body.dark-mode small.text-muted {
        color: #8696a0 !important;
    }

    /* Dark Mode Forms */
    body.dark-mode .bg-light,
    body.dark-mode .table-light,
    body.dark-mode .form-control,
    body.dark-mode .form-select,
    body.dark-mode textarea {
        background-color: #202c33 !important;
        color: #e9edef !important;
        border: 1.5px solid #2a3942 !important;
    }

    body.dark-mode .form-control::placeholder,
    body.dark-mode textarea::placeholder {
        color: #8696a0 !important;
        opacity: 1;
    }

    body.dark-mode .form-control:focus,
    body.dark-mode .form-select:focus,
    body.dark-mode textarea:focus {
        background-color: #202c33 !important;
        color: #ffffff !important;
        border-color: #25d366 !important;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.25) !important;
    }

    body.dark-mode .input-group-text {
        background-color: #182229 !important;
        color: #8696a0 !important;
        border: 1.5px solid #2a3942 !important;
    }

    body.dark-mode select option {
        background-color: #202c33 !important;
        color: #e9edef !important;
    }

    /* Dark Mode Modals */
    body.dark-mode .modal-content {
        background-color: #111b21 !important;
        color: #e9edef !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7) !important;
    }

    body.dark-mode .modal-header {
        background-color: #075e54 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }

    body.dark-mode .modal-header .modal-title {
        color: #ffffff !important;
    }

    body.dark-mode .modal-body {
        background-color: #111b21 !important;
        color: #e9edef !important;
    }

    body.dark-mode .modal-footer {
        background-color: #182229 !important;
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #e9edef !important;
    }

    /* Dark Mode Tables */
    body.dark-mode .table {
        background-color: #111b21 !important;
        color: #e9edef !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .table thead th {
        background-color: #182229 !important;
        color: #8696a0 !important;
        border-bottom: 1.5px solid rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode .table tbody {
        background-color: #111b21 !important;
    }

    body.dark-mode .table tbody tr {
        background-color: #111b21 !important;
    }

    body.dark-mode .table tbody tr:hover {
        background-color: #1a2730 !important;
    }

    body.dark-mode .table tbody td,
    body.dark-mode .table td {
        background-color: #111b21 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        color: #d1d7db !important;
    }

    body.dark-mode .bg-white {
        background-color: #111b21 !important;
        color: #e9edef !important;
    }

    body.dark-mode .border {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode .badge.bg-light {
        background-color: #202c33 !important;
        color: #e9edef !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }

    body.dark-mode .dropdown-item {
        color: #e9edef !important;
    }

    body.dark-mode .dropdown-item:hover {
        background-color: #202c33 !important;
        color: #25d366 !important;
    }

    body.dark-mode .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    body.dark-mode code,
    body.dark-mode pre {
        background-color: #202c33 !important;
        color: #25d366 !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    /* Hide Debugbar/Ignition floating badges that overlap the layout */
    #phpdebugbar, .phpdebugbar, .phpdebugbar-open-handler, [class*="phpdebugbar"], 
    .ignition-badge, #ignition-badge, [id*="ignition"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .user-layout {
        display: flex;
        min-height: 100vh;
        background-color: var(--body-bg-light);
    }

    /* Fixed Left Sidebar */
    .user-sidebar {
        width: var(--sidebar-width);
        background-color: var(--sidebar-bg);
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 1040;
        transition: all 0.3s ease-in-out;
        box-shadow: 2px 0 10px rgba(0,0,0,0.15);
    }

    .user-sidebar__header {
        flex-shrink: 0;
    }

    .user-sidebar__menu {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Sleek slim scrollbar for sidebar */
    .user-sidebar__menu::-webkit-scrollbar {
        width: 4px;
    }
    .user-sidebar__menu::-webkit-scrollbar-track {
        background: transparent;
    }
    .user-sidebar__menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 4px;
    }
    .user-sidebar__menu::-webkit-scrollbar-thumb:hover {
        background: rgba(37, 211, 102, 0.5);
    }

    .user-sidebar .nav-item {
        border: none !important;
        border-bottom: none !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    .user-sidebar .nav-item:first-child,
    .user-sidebar .nav-item:last-child,
    .user-sidebar .nav-item:nth-last-child(2) a {
        border-bottom: none !important;
    }

    .user-sidebar .nav-item .nav-link,
    .user-sidebar .nav-link {
        color: #94a3b8 !important;
        padding: 10px 14px !important;
        border-radius: 10px !important;
        border: none !important;
        border-bottom: none !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        text-align: left !important;
        text-decoration: none !important;
        margin: 2px 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .user-sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
    }

    .user-sidebar .nav-link.active,
    .user-sidebar .nav-link.menu-active {
        background-color: var(--sidebar-active-bg) !important;
        color: var(--sidebar-active-text) !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25) !important;
    }

    .user-sidebar .nav-link > i:first-child,
    .user-sidebar .nav-link > .menu-icon {
        width: 22px !important;
        min-width: 22px !important;
        height: 22px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        margin-right: 12px !important;
        margin-left: 0 !important;
        flex-shrink: 0 !important;
        text-align: center !important;
    }

    .user-sidebar .nav-link > span,
    .user-sidebar .nav-link > .menu-title {
        display: inline-block !important;
        flex: 1 1 auto !important;
        text-align: left !important;
        margin: 0 !important;
        padding: 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 13.5px !important;
        color: inherit !important;
    }

    /* Sidebar Dropdowns & Submenus */
    .user-sidebar .user-dropdown-toggle {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 100% !important;
        cursor: pointer !important;
    }

    .user-sidebar .dropdown-chevron {
        font-size: 11px !important;
        transition: transform 0.25s ease !important;
        margin-left: auto !important;
        margin-right: 0 !important;
        color: #64748b !important;
        flex-shrink: 0 !important;
    }

    .user-sidebar .user-sidebar-dropdown.open > .user-dropdown-toggle .dropdown-chevron {
        transform: rotate(180deg) !important;
        color: #25d366 !important;
    }

    .user-sidebar .user-submenu {
        display: none;
        padding-left: 6px;
        margin-top: 3px;
        margin-bottom: 5px;
        border-left: 2px solid rgba(37, 211, 102, 0.25);
        margin-left: 22px;
    }

    .user-sidebar .user-sidebar-dropdown.open > .user-submenu {
        display: block;
    }

    .user-sidebar .submenu-link {
        font-size: 13px !important;
        padding: 8px 12px !important;
        color: #94a3b8 !important;
        border-radius: 8px !important;
        border: none !important;
        border-bottom: none !important;
        font-weight: 500 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        text-align: left !important;
        transition: all 0.15s ease !important;
        text-decoration: none !important;
        margin: 2px 0 !important;
    }

    .user-sidebar .submenu-link:hover {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.06) !important;
        padding-left: 15px !important;
    }

    .user-sidebar .submenu-link.active {
        color: #25d366 !important;
        font-weight: 600 !important;
        background-color: rgba(37, 211, 102, 0.12) !important;
    }

    .user-sidebar .submenu-link i {
        font-size: 15px !important;
        width: 18px !important;
        text-align: center !important;
        margin-right: 10px !important;
        flex-shrink: 0 !important;
    }

    .user-sidebar .nav-header {
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: 0.8px !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        padding: 14px 14px 4px 14px !important;
        border: none !important;
        border-bottom: none !important;
    }

    /* Main Content Wrapper */
    .user-main-wrapper {
        flex: 1;
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease-in-out;
    }

    /* Collapsed Sidebar State */
    .user-layout.sidebar-collapsed .user-sidebar {
        margin-left: calc(-1 * var(--sidebar-width));
    }

    .user-layout.sidebar-collapsed .user-main-wrapper {
        margin-left: 0;
    }

    /* Mobile Drawer & Mobile Friendly Layout */
    .sidebar-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .sidebar-close-btn:hover, .sidebar-close-btn:active {
        background: rgba(239, 68, 68, 0.25);
        border-color: #ef4444;
        color: #ef4444;
    }

    /* Remove excessive py-60 / py-120 padding on dashboard inside user portal */
    .user-layout .py-60,
    .user-layout .py-120 {
        padding-top: 10px !important;
        padding-bottom: 25px !important;
    }

    @media (max-width: 991.98px) {
        .user-sidebar {
            width: min(85vw, 290px);
            left: 0;
            top: 0;
            bottom: 0;
            transform: translateX(-105%);
            transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.32s ease;
            box-shadow: none;
            z-index: 1050;
            border-radius: 0 16px 16px 0;
            will-change: transform;
            margin-left: 0 !important;
        }

        .user-main-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
            min-width: 0;
        }

        .user-layout.mobile-sidebar-open .user-sidebar {
            transform: translateX(0);
            box-shadow: 10px 0 35px rgba(0, 0, 0, 0.4);
        }

        .user-sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            display: block !important;
        }

        .user-layout.mobile-sidebar-open .user-sidebar-backdrop {
            opacity: 1;
            visibility: visible;
        }

        body.mobile-drawer-locked {
            overflow: hidden !important;
            touch-action: none;
        }
    }

    @media (max-width: 767.98px) {
        .user-content-body {
            padding: 10px 10px 30px 10px !important;
        }
        .user-content-body .container,
        .user-content-body .container-fluid {
            padding-left: 4px !important;
            padding-right: 4px !important;
        }
        .user-content-body .card {
            border-radius: 12px !important;
        }
        .user-content-body .card-body {
            padding: 14px !important;
        }
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        // Sidebar Toggle (Desktop & Mobile)
        $('#sidebarToggleBtn').on('click', function(e) {
            e.preventDefault();
            if ($(window).width() < 992) {
                var isOpen = $('#userLayout').hasClass('mobile-sidebar-open');
                if (isOpen) {
                    $('#userLayout').removeClass('mobile-sidebar-open');
                    $('body').removeClass('mobile-drawer-locked');
                    $('#sidebarToggleBtn').removeClass('is-active');
                } else {
                    $('#userLayout').addClass('mobile-sidebar-open');
                    $('body').addClass('mobile-drawer-locked');
                    $('#sidebarToggleBtn').addClass('is-active');
                }
            } else {
                $('#userLayout').toggleClass('sidebar-collapsed');
            }
        });

        $('#closeSidebarBtn, #userSidebarBackdrop').on('click', function() {
            $('#userLayout').removeClass('mobile-sidebar-open');
            $('body').removeClass('mobile-drawer-locked');
            $('#sidebarToggleBtn').removeClass('is-active');
        });

        // Auto close on mobile when clicking regular menu link
        $('.user-sidebar .nav-link:not(.user-dropdown-toggle), .user-sidebar .submenu-link').on('click', function() {
            if ($(window).width() < 992) {
                $('#userLayout').removeClass('mobile-sidebar-open');
                $('body').removeClass('mobile-drawer-locked');
                $('#sidebarToggleBtn').removeClass('is-active');
            }
        });

        // Sidebar Submenu Dropdowns Toggle
        $(document).on('click', '.user-dropdown-toggle', function (e) {
            e.preventDefault();
            var $parent = $(this).closest('.user-sidebar-dropdown');
            var $submenu = $parent.find('.user-submenu');
            
            if ($parent.hasClass('open')) {
                $submenu.slideUp(180, function() {
                    $parent.removeClass('open');
                });
            } else {
                $parent.addClass('open');
                $submenu.slideDown(180);
            }
        });

        // Auto open active dropdowns on page load
        $('.user-sidebar-dropdown').each(function() {
            if ($(this).find('.submenu-link.active, .nav-link.active').length > 0) {
                $(this).addClass('open');
                $(this).find('.user-submenu').show();
            }
        });

        // Theme Toggle (Dark / Light) with LocalStorage persistence
        var currentTheme = localStorage.getItem('user_portal_theme') || 'light';
        if (currentTheme === 'dark') {
            $('body').addClass('dark-mode');
            $('#themeIcon').removeClass('la-moon').addClass('la-sun');
        }

        $('#themeToggleBtn').on('click', function() {
            $('body').toggleClass('dark-mode');
            var isDark = $('body').hasClass('dark-mode');
            if (isDark) {
                $('#themeIcon').removeClass('la-moon').addClass('la-sun');
                localStorage.setItem('user_portal_theme', 'dark');
            } else {
                $('#themeIcon').removeClass('la-sun').addClass('la-moon');
                localStorage.setItem('user_portal_theme', 'light');
            }
        });

        // Form elements helper
        var inputElements = $('[type=text],[type=password],select,textarea');
        $.each(inputElements, function(index, element) {
            element = $(element);
            if (element.hasClass('exclude')) return true;
            if (!element.attr('id') && element.attr('name')) {
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'));
            }
        });

    })(jQuery);
</script>
@endpush

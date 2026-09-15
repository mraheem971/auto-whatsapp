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
    label, .form-label, .form-group label {
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 13.5px;
        margin-bottom: 6px;
        letter-spacing: 0.1px;
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
        color: #0f172a;
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
    body.dark-mode .form-group label {
        color: #e9edef !important;
        font-weight: 600 !important;
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
        color: #e9edef !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .table thead th {
        background-color: #182229 !important;
        color: #8696a0 !important;
        border-bottom: 1.5px solid rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode .table td {
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

    body.dark-mode .card.bg-light {
        background-color: #182229 !important;
        color: #e9edef !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
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
        z-index: 1040;
        transition: all 0.3s ease-in-out;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    .user-sidebar .nav-link {
        color: #aebac1 !important;
        padding: 9px 14px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .user-sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.08);
        color: #ffffff !important;
    }

    .user-sidebar .nav-link.active,
    .user-sidebar .nav-link.menu-active {
        background-color: var(--sidebar-active-bg);
        color: var(--sidebar-active-text) !important;
        font-weight: 600;
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

    /* Mobile Drawer */
    @media (max-width: 991.98px) {
        .user-sidebar {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        .user-main-wrapper {
            margin-left: 0 !important;
        }
        .user-layout.mobile-sidebar-open .user-sidebar {
            margin-left: 0;
        }
        .user-sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
            display: none;
        }
        .user-layout.mobile-sidebar-open .user-sidebar-backdrop {
            display: block;
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
                $('#userLayout').toggleClass('mobile-sidebar-open');
            } else {
                $('#userLayout').toggleClass('sidebar-collapsed');
            }
        });

        $('#closeSidebarBtn, #userSidebarBackdrop').on('click', function() {
            $('#userLayout').removeClass('mobile-sidebar-open');
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
            if (element.hasClass('exclude')) return false;
            element.closest('.form-group').find('label').attr('for', element.attr('name'));
            element.attr('id', element.attr('name'));
        });

    })(jQuery);
</script>
@endpush

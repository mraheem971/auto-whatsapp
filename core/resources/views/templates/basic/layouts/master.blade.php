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
        --text-color-light: #2c3e50;
    }

    body.dark-mode {
        --body-bg-light: #0d1418;
        --card-bg-light: #182229;
        --text-color-light: #e9edef;
        background-color: var(--body-bg-light) !important;
        color: var(--text-color-light) !important;
    }

    body.dark-mode .user-topbar,
    body.dark-mode .card,
    body.dark-mode .user-footer,
    body.dark-mode .dropdown-menu {
        background-color: var(--card-bg-light) !important;
        color: var(--text-color-light) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode .text-dark,
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
    body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
        color: #e9edef !important;
    }

    body.dark-mode .text-muted {
        color: #8696a0 !important;
    }

    body.dark-mode .bg-light,
    body.dark-mode .table-light,
    body.dark-mode .form-control,
    body.dark-mode .form-select {
        background-color: #111b21 !important;
        color: #e9edef !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    body.dark-mode .table {
        color: #e9edef !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tổng quan Dashboard') | StudyPrep Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800|instrument-sans:400,500,600,700">

    {{-- Admin Global Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    @stack('styles')
</head>
<body class="admin-body">
    @php
        $navActive = $active ?? 'dashboard';
    @endphp

    {{-- Mobile backdrop --}}
    <div id="admin-sidebar-backdrop" class="admin-mobile-backdrop" onclick="toggleAdminSidebar(false)"></div>

    {{-- Shared Admin Sidebar Component --}}
    <x-admin.sidebar :active="$navActive" />

    {{-- Main Container --}}
    <div class="admin-main-wrap">
        {{-- Topbar Header (Matches Image 2) --}}
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="admin-mobile-toggle" onclick="toggleAdminSidebar()" aria-label="Mở menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="admin-topbar-mobile-logo" title="StudyPrep Admin">
                    <img class="sp-sidebar__logo" src="{{ asset('images/logo.svg') }}" alt="StudyPrep">
                </a>
                <div class="admin-breadcrumb-top">
                    @hasSection('top_breadcrumb')
                        @yield('top_breadcrumb')
                    @else
                        <span>Hệ thống</span>
                        <span class="crumb-arrow">></span>
                        <span>Quản trị người dùng</span>
                    @endif
                </div>
            </div>

        </header>

        {{-- Content Area --}}
        <main class="admin-content-area">
            @if (session('info'))
                <div style="background-color: #eef2ff; color: #3730a3; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                    {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Responsive Sidebar Drawer Script --}}
    <script>
        function toggleAdminSidebar(forceState) {
            var sidebar = document.getElementById('admin-sidebar');
            var backdrop = document.getElementById('admin-sidebar-backdrop');
            if (!sidebar || !backdrop) return;

            var shouldOpen = typeof forceState === 'boolean' ? forceState : !sidebar.classList.contains('open');
            sidebar.classList.toggle('open', shouldOpen);
            backdrop.classList.toggle('open', shouldOpen);
            document.body.style.overflow = shouldOpen ? 'hidden' : '';
        }
    </script>
    @stack('scripts')
</body>
</html>

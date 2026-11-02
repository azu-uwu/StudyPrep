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
        $currentAdmin = auth()->user();
        $adminDisplayName = $currentAdmin->full_name ?? $currentAdmin->user_name ?? 'Nguyễn Admin';
        $adminDisplayRole = ($currentAdmin->role ?? 'admin') === 'admin' ? 'Super Admin' : ucfirst($currentAdmin->role ?? 'Admin');
    @endphp

    {{-- Mobile backdrop --}}
    <div id="admin-sidebar-backdrop" class="admin-mobile-backdrop" onclick="toggleAdminSidebar(false)"></div>

    {{-- Shared Admin Sidebar Component --}}
    <x-admin.sidebar :active="$navActive" />

    {{-- Main Container --}}
    <div class="admin-main-wrap">
        {{-- Topbar Header --}}
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="admin-mobile-toggle" onclick="toggleAdminSidebar()" aria-label="Mở menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="admin-breadcrumb-top">
                    @hasSection('top_breadcrumb')
                        @yield('top_breadcrumb')
                    @else
                        <span>Hệ thống</span>
                        <span class="crumb-arrow">&gt;</span>
                        <span class="crumb-active">@yield('title', 'Quản trị hệ thống')</span>
                    @endif
                </div>
            </div>

            <div class="admin-topbar-right">
                <div class="admin-topbar-user">
                    <div class="admin-topbar-avatar" title="{{ $adminDisplayName }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="admin-topbar-user-meta">
                        <span class="admin-topbar-user-name" title="{{ $adminDisplayName }}">{{ $adminDisplayName }}</span>
                        <span class="admin-topbar-user-role">{{ $adminDisplayRole }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="admin-topbar-logout-btn" title="Đăng xuất" aria-label="Đăng xuất">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </form>
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

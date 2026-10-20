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

    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    @stack('styles')
</head>
<body class="min-h-screen bg-admin-canvas font-sans text-slate-600 antialiased">
    @php
        $admin = auth()->user();
        $adminName = $admin->full_name ?? $admin->user_name ?? 'Admin';
        $adminRole = strcasecmp((string) ($admin->role ?? ''), 'super_admin') === 0 ? 'Super Admin' : 'Admin';
        $navActive = $active ?? 'dashboard';
    @endphp

    {{-- Overlay menu mobile --}}
    <div
        id="admin-sidebar-backdrop"
        class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden"
        data-admin-sidebar-close
    ></div>

    <aside
        id="admin-sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-[272px] -translate-x-full flex-col border-r border-violet-100/80 bg-white transition-transform duration-200 lg:translate-x-0"
    >
        <div class="flex items-center gap-3 px-6 py-6">
            <span class="flex size-10 items-center justify-center rounded-xl bg-admin-accent text-white shadow-sm">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 9.5 12 5l8 4.5-8 4.5L4 9.5Z" />
                    <path d="M7 12.5v4.2c0 .4 2.2 2.3 5 2.3s5-1.9 5-2.3v-4.2" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="font-nunito text-xl font-extrabold leading-none tracking-tight">
                    <span class="text-brand-navy">Study</span><span class="text-brand-prep">PREP</span>
                </p>
                <span class="mt-1 inline-flex rounded-md bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-admin-accent">
                    Admin Portal
                </span>
            </div>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-4 pb-6">
            <div>
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Tổng quan</p>
                <a href="{{ route('admin.dashboard') }}" class="{{ $navActive === 'dashboard' ? 'bg-violet-50 text-admin-accent' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    Dashboard
                </a>
            </div>

            <div>
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Quản lý đào tạo</p>
                <div class="space-y-1">
                    <a href="#de-thi" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h11a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6"/></svg>
                        Đề thi
                    </a>
                    <a href="#ngan-hang-cau-hoi" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        Ngân hàng câu hỏi
                    </a>
                    <a href="#tu-vung" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                        Từ vựng & Flashcards
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Quản trị hệ thống</p>
                <div class="space-y-1">
                    <a href="#nguoi-dung" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Quản lý người dùng
                    </a>
                    <a href="#phan-quyen" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Phân quyền & Vai trò
                    </a>
                    <a href="#bao-cao" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><path d="M7 14v4M12 9v9M17 5v13"/></svg>
                        Báo cáo & Thống kê
                    </a>
                    <a href="#cai-dat" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9c.3.6.9 1 1.5 1H21a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.4 1Z"/></svg>
                        Cài đặt hệ thống
                    </a>
                </div>
            </div>
        </nav>

        <div class="border-t border-violet-100 p-4">
            <div class="flex items-center gap-3 rounded-2xl bg-violet-50/80 p-3">
                <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-admin-accent text-sm font-bold text-white">
                    @if (filled($admin->avatar_url ?? null))
                        <img src="{{ $admin->avatar_url }}" alt="" class="size-full object-cover">
                    @else
                        {{ mb_strtoupper(mb_substr($adminName, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-800">{{ $adminName }}</p>
                    <p class="text-xs text-slate-500">{{ $adminRole }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-admin-accent" aria-label="Đăng xuất">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-[272px]">
        <header class="sticky top-0 z-20 border-b border-violet-100/80 bg-white/80 backdrop-blur-md">
            <div class="flex items-center gap-3 px-4 py-3 lg:px-8">
                <button
                    type="button"
                    class="inline-flex size-10 items-center justify-center rounded-xl border border-violet-100 bg-white text-slate-600 lg:hidden"
                    data-admin-sidebar-toggle
                    aria-label="Mở menu"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>

                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">Tìm kiếm nhanh</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                    <input
                        type="search"
                        placeholder="Tìm học viên, đề thi, câu hỏi..."
                        class="w-full rounded-xl border border-violet-100 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-admin-accent focus:bg-white focus:ring-2 focus:ring-admin-accent/20"
                    >
                </label>

                <button type="button" class="relative inline-flex size-10 items-center justify-center rounded-xl border border-violet-100 bg-white text-slate-500" aria-label="Thông báo hệ thống">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 1 0 6 8c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                    <span class="absolute right-2 top-2 size-2 rounded-full bg-rose-500"></span>
                </button>

                <div class="hidden items-center gap-2 sm:flex">
                    <div class="flex size-10 items-center justify-center overflow-hidden rounded-full bg-admin-accent text-sm font-bold text-white">
                        {{ mb_strtoupper(mb_substr($adminName, 0, 1)) }}
                    </div>
                    <div class="hidden md:block">
                        <p class="text-sm font-bold leading-tight text-slate-800">{{ $adminName }}</p>
                        <p class="text-xs text-slate-500">{{ $adminRole }}</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 lg:px-8 lg:py-8">
            @if (session('info'))
                <div class="mb-4 rounded-xl border border-violet-100 bg-white px-4 py-3 text-sm text-slate-600">
                    {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var backdrop = document.getElementById('admin-sidebar-backdrop');
            var toggle = document.querySelector('[data-admin-sidebar-toggle]');
            var closers = document.querySelectorAll('[data-admin-sidebar-close]');

            function setOpen(open) {
                document.body.classList.toggle('overflow-hidden', open);
                sidebar.classList.toggle('-translate-x-full', !open);
                backdrop.classList.toggle('hidden', !open);
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    setOpen(sidebar.classList.contains('-translate-x-full'));
                });
            }

            closers.forEach(function (el) {
                el.addEventListener('click', function () { setOpen(false); });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>

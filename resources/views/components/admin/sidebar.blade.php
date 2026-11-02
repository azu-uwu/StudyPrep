@props([
    'active' => 'dashboard'
])

@php
    $admin = auth()->user();
    $adminName = $admin->full_name ?? $admin->user_name ?? 'Nguyễn Admin';
    $adminRole = 'Super Admin';
@endphp

<aside id="admin-sidebar" class="admin-sidebar">
    {{-- Header Logo + ADMIN PORTAL Badge --}}
    
    <div class="admin-sidebar-main">
        <div class="admin-sidebar-header">
            <a class="sp-sidebar__brand" href="{{ route('admin.dashboard') }}" aria-label="StudyPrep - Trang chủ">
                <img class="sp-sidebar__logo" src="{{ asset('images/logo.svg') }}" alt="StudyPrep">
                <img class="sp-sidebar__mark" src="{{ asset('favicon.svg') }}" alt="" aria-hidden="true">
            </a>
            <div class="admin-portal-badge">
                <span>ADMIN</span>
                <span>PORTAL</span>
            </div>
        </div>

        {{-- Navigation Menu --}}
        <nav class="admin-sidebar-nav" aria-label="Menu quản trị">
            {{-- Nhóm 1: TỔNG QUAN --}}
            <div class="admin-nav-group">
                <div class="admin-nav-group-title">TỔNG QUAN</div>
                <ul class="admin-nav-list">
                    <li class="admin-nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Nhóm 2: QUẢN LÝ ĐÀO TẠO --}}
            <div class="admin-nav-group">
                <div class="admin-nav-group-title">QUẢN LÝ ĐÀO TẠO</div>
                <ul class="admin-nav-list">
                    <li class="admin-nav-item {{ $active === 'de-thi' ? 'active' : '' }}">
                        <a href="#de-thi">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                            <span>Đề thi</span>
                        </a>
                    </li>
                    <li class="admin-nav-item {{ $active === 'ngan-hang-cau-hoi' ? 'active' : '' }}">
                        <a href="{{ route('admin.questions.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <path d="M9 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <span>Ngân hàng câu hỏi</span>
                        </a>
                    </li>
                    <li class="admin-nav-item {{ in_array($active, ['tu-vung', 'tu-vung-flashcard', 'vocabularies']) ? 'active' : '' }}">
                        <a href="{{ route('admin.vocabularies.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="4" width="14" height="17" rx="2" transform="rotate(-6 12 12)"></rect>
                                <rect x="6" y="3" width="14" height="17" rx="2"></rect>
                            </svg>
                            <span>Từ vựng & Flashcards</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Nhóm 3: QUẢN TRỊ HỆ THỐNG --}}
            <div class="admin-nav-group">
                <div class="admin-nav-group-title">QUẢN TRỊ HỆ THỐNG</div>
                <ul class="admin-nav-list">
                    <li class="admin-nav-item {{ $active === 'nguoi-dung' ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Quản lý người dùng</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    {{-- Bottom Admin Profile Card --}}
    <div class="admin-sidebar-footer">
        <div class="admin-user-container">
            <div class="admin-user-card">
                <div class="admin-user-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="admin-user-info">
                    <div class="admin-user-name" title="{{ $adminName }}">{{ $adminName }}</div>
                    <div class="admin-user-role">{{ $adminRole }}</div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="admin-logout-btn" title="Đăng xuất" aria-label="Đăng xuất">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

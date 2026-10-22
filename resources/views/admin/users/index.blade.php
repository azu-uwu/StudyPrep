@extends('layouts.admin', ['active' => 'nguoi-dung'])

@section('title', 'Quản lý người dùng')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">Quản lý người dùng</span>
@endsection

@section('content')
<div class="user-management-page">

    {{-- Thông báo kết quả thao tác --}}
    @if (session('success'))
        <div class="alert-admin-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                <strong>Đã có lỗi xảy ra:</strong>
                <ul style="margin: 4px 0 0 18px; padding: 0;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- 1. Tiêu đề trang & Các nút thao tác phía trên (Page Header) --}}
    <div class="user-page-header">
        <div>
            <div class="user-title-row">
                <h1 class="user-page-title">Quản lý người dùng</h1>
                <span class="user-count-badge">{{ number_format($stats['total']) }} tài khoản</span>
            </div>
            <p class="user-page-subtitle">
                Quản lý danh sách học viên, giảng viên và kiểm soát quyền truy cập hệ thống StudyPREP
            </p>
        </div>

        <div class="user-header-actions">
            {{-- Nút Xuất dữ liệu Excel --}}
            <a href="{{ route('admin.users.export') }}" class="btn-admin-action excel" title="Xuất dữ liệu người dùng ra tệp Excel (CSV)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất dữ liệu Excel</span>
            </a>

            {{-- Nút Nhập danh sách --}}
            <button type="button" class="btn-admin-action import" onclick="openAdminModal('modal-import')" title="Nhập tài khoản từ file danh sách">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
                <span>Nhập danh sách</span>
            </button>

            {{-- Nút Thêm người dùng mới --}}
            <button type="button" class="btn-admin-primary" onclick="openAdminModal('modal-add-user')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                <span>Thêm người dùng mới</span>
            </button>
        </div>
    </div>

    {{-- 2. Ba thẻ thống kê chỉ số (3 KPI Stat Cards - Khớp ảnh mẫu) --}}
    <div class="user-kpi-grid">
        {{-- Thẻ 1: Tổng số người dùng --}}
        <div class="user-kpi-card">
            <div class="user-kpi-header">
                <p class="user-kpi-label">TỔNG SỐ NGƯỜI DÙNG</p>
                <div class="user-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
            <div class="user-kpi-value">{{ number_format($stats['total']) }}</div>
            <div class="user-kpi-footer">
                <span class="kpi-trend-pill trend-purple">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    +12.5%
                </span>
                <span>so với tháng trước</span>
            </div>
        </div>

        {{-- Thẻ 2: Giảng viên & Chấm bài --}}
        <div class="user-kpi-card">
            <div class="user-kpi-header">
                <p class="user-kpi-label">GIẢNG VIÊN & CHẤM BÀI</p>
                <div class="user-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <circle cx="12" cy="15" r="2"></circle>
                    </svg>
                </div>
            </div>
            <div class="user-kpi-value">{{ number_format($stats['teachers']) }}</div>
            <div class="user-kpi-footer">
                <span>Đội ngũ chuyên môn & AI Audit</span>
            </div>
        </div>

        {{-- Thẻ 3: Khóa / Cảnh báo --}}
        <div class="user-kpi-card">
            <div class="user-kpi-header">
                <p class="user-kpi-label">KHÓA / CẢNH BÁO</p>
                <div class="user-kpi-icon icon-red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
            </div>
            <div class="user-kpi-value" style="color: #dc2626;">{{ number_format($stats['locked']) }}</div>
            <div class="user-kpi-footer">
                <span class="kpi-trend-pill trend-red">- 2 tài khoản</span>
                <span>gian lận thi thử</span>
            </div>
        </div>
    </div>

    {{-- 3. Thanh tìm kiếm và bộ lọc (Filter Bar) --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="user-filter-bar">
        {{-- Ô tìm kiếm --}}
        <div class="user-search-wrap">
            <svg class="user-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text"
                   name="search"
                   value="{{ $filters['search'] }}"
                   placeholder="Tìm theo Họ tên, Email, SĐT, mã ID..."
                   class="user-search-input">
        </div>

        {{-- Lọc vai trò --}}
        <div class="user-filter-select-wrap">
            <select name="role" class="user-filter-select" onchange="this.form.submit()">
                <option value="all" {{ $filters['role'] === 'all' ? 'selected' : '' }}>Tất cả vai trò</option>
                <option value="user" {{ $filters['role'] === 'user' ? 'selected' : '' }}>Học viên</option>
                <option value="teacher" {{ $filters['role'] === 'teacher' ? 'selected' : '' }}>Giảng viên TOEIC</option>
                <option value="admin" {{ $filters['role'] === 'admin' ? 'selected' : '' }}>Quản trị viên</option>
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        {{-- Lọc trạng thái --}}
        <div class="user-filter-select-wrap">
            <select name="status" class="user-filter-select" onchange="this.form.submit()">
                <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Trạng thái: Tất cả</option>
                <option value="ACTIVE" {{ $filters['status'] === 'ACTIVE' ? 'selected' : '' }}>• Hoạt động</option>
                <option value="PENDING" {{ $filters['status'] === 'PENDING' ? 'selected' : '' }}>• Chờ kích hoạt</option>
                <option value="LOCKED" {{ $filters['status'] === 'LOCKED' ? 'selected' : '' }}>• Bị khóa</option>
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        {{-- Lọc thời gian (30 ngày qua) --}}
        <div class="user-filter-select-wrap">
            <select name="time" class="user-filter-select" onchange="this.form.submit()" style="padding-left: 36px;">
                <option value="30" {{ $filters['time'] === '30' ? 'selected' : '' }}>30 ngày qua</option>
                <option value="7" {{ $filters['time'] === '7' ? 'selected' : '' }}>7 ngày qua</option>
                <option value="90" {{ $filters['time'] === '90' ? 'selected' : '' }}>90 ngày qua</option>
                <option value="all" {{ $filters['time'] === 'all' ? 'selected' : '' }}>Toàn thời gian</option>
            </select>
            <svg style="position: absolute; left: 12px; width: 16px; height: 16px; color: #94a3b8; pointer-events: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        {{-- Nút reset bộ lọc --}}
        <a href="{{ route('admin.users.index') }}" class="user-refresh-btn" title="Đặt lại bộ lọc">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </a>
    </form>

    {{-- 4. Bảng danh sách người dùng (Data Table - Khớp ảnh mẫu) --}}
    <div class="user-table-card">
        <div class="user-table-scroll">
            <table class="user-data-table">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">
                            <input type="checkbox" id="check-all-users" class="custom-checkbox" title="Chọn tất cả">
                        </th>
                        <th>NGƯỜI DÙNG</th>
                        <th>VAI TRÒ</th>
                        <th>GÓI ĐĂNG KÝ</th>
                        <th>TIẾN ĐỘ & ĐIỂM ƯỚC TÍNH</th>
                        <th>LẦN ONLINE CUỐI</th>
                        <th>TRẠNG THÁI</th>
                        <th style="text-align: right; padding-right: 24px;">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $progress = $user->progress_info;
                            $isTeacher = in_array(strtolower((string)$user->role), ['teacher', 'instructor', 'giang_vien'], true);
                        @endphp
                        <tr>
                            {{-- Checkbox --}}
                            <td style="text-align: center;">
                                <input type="checkbox" class="custom-checkbox user-row-checkbox" value="{{ $user->user_id }}">
                            </td>

                            {{-- Người dùng --}}
                            <td>
                                <div class="user-info-cell">
                                    <div class="user-avatar-wrap">
                                        <div class="user-avatar-circle"
                                             style="background-color: {{ $user->avatar_bg }}; color: {{ $user->avatar_text_color }};">
                                            {{ $user->initials }}
                                        </div>
                                        @if (!$user->isLocked())
                                            <span class="user-status-dot-subtle"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="user-name-line">
                                            <span class="user-name-text">{{ $user->full_name ?: $user->user_name }}</span>
                                            @if ($isTeacher)
                                                {{-- Huy hiệu giảng viên (medal red) --}}
                                                <svg class="badge-teacher-red" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="8" r="6"></circle>
                                                    <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"></path>
                                                </svg>
                                            @elseif ($user->email_verified_at && !$user->isAdmin())
                                                {{-- Tích xanh xác thực (verified blue) --}}
                                                <svg class="badge-verified-blue" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="user-email-text">{{ $user->email }}</div>
                                        <div class="user-id-code">{{ $user->stp_id }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Vai trò --}}
                            <td>
                                <span class="role-pill {{ $user->role_badge_class }}">
                                    {{ $user->role_display }}
                                </span>
                            </td>

                            {{-- Gói đăng ký --}}
                            <td>
                                <div class="package-title">{{ $user->package_title }}</div>
                                <div class="package-sub">{{ $user->package_subtitle }}</div>
                            </td>

                            {{-- Tiến độ & Điểm ước tính --}}
                            <td>
                                <div class="progress-cell-wrap">
                                    <div class="progress-info-row">
                                        <span class="progress-score-main">{{ $progress['stat1'] }}</span>
                                        <span class="progress-sub-text">{{ $progress['stat2'] }}</span>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill"
                                             style="width: {{ $progress['percent'] }}%; background-color: {{ $progress['color'] }};">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Lần online cuối --}}
                            <td>
                                <div class="online-cell">
                                    @if ($user->last_online_text === 'Vừa xong')
                                        <span class="online-dot"></span>
                                    @endif
                                    <span>{{ $user->last_online_text }}</span>
                                </div>
                            </td>

                            {{-- Trạng thái --}}
                            <td>
                                <span class="status-pill {{ $user->status_badge_class }}">
                                    <span class="status-dot"></span>
                                    {{ $user->status_label }}
                                </span>
                            </td>

                            {{-- Thao tác --}}
                            <td style="text-align: right; padding-right: 20px;">
                                <div class="user-actions-cell" style="justify-content: flex-end;">
                                    {{-- Nút Sửa / Chi tiết (Sliders/Settings icon) --}}
                                    <button type="button"
                                            class="action-icon-btn"
                                            title="Chỉnh sửa thông tin"
                                            onclick='openEditModal(@json($user))'>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="4" y1="21" x2="4" y2="14"></line>
                                            <line x1="4" y1="10" x2="4" y2="3"></line>
                                            <line x1="12" y1="21" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12" y2="3"></line>
                                            <line x1="20" y1="21" x2="20" y2="16"></line>
                                            <line x1="20" y1="12" x2="20" y2="3"></line>
                                            <line x1="1" y1="14" x2="7" y2="14"></line>
                                            <line x1="9" y1="8" x2="15" y2="8"></line>
                                            <line x1="17" y1="16" x2="23" y2="16"></line>
                                        </svg>
                                    </button>

                                    {{-- Nút Đổi mật khẩu (Key icon) --}}
                                    <button type="button"
                                            class="action-icon-btn"
                                            title="Đặt lại mật khẩu"
                                            onclick="openResetPasswordModal({{ $user->user_id }}, '{{ addslashes($user->full_name ?: $user->user_name) }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 2l-2 2m-1.5 1.5L16 7l-2-2m-1.5 1.5L11 8l-2-2m-1.5 1.5L6 9l-4 4 3 3 2-2 2 2 2-2 2 2 7-7-1.5-1.5z"></path>
                                            <circle cx="16.5" cy="7.5" r="4.5"></circle>
                                        </svg>
                                    </button>

                                    {{-- Nút Khóa / Mở khóa (Lock icon) --}}
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->user_id) }}" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="action-icon-btn {{ $user->isLocked() ? 'danger' : '' }}"
                                                title="{{ $user->isLocked() ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}"
                                                onclick="return confirm('Bạn có chắc chắn muốn {{ $user->isLocked() ? 'MỞ KHÓA' : 'KHÓA' }} tài khoản {{ $user->full_name }}?')">
                                            @if ($user->isLocked())
                                                {{-- Icon Mở khóa --}}
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                                </svg>
                                            @else
                                                {{-- Icon Khóa --}}
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Nút Xóa / Thao tác khác (3 dots icon) --}}
                                    @if ($user->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->user_id) }}" style="display: inline; margin: 0;" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản {{ $user->full_name }}? Thao tác này không thể hoàn tác!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn danger" title="Xóa tài khoản">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="1"></circle>
                                                    <circle cx="12" cy="5" r="1"></circle>
                                                    <circle cx="12" cy="19" r="1"></circle>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px 20px; color: #64748b;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                    <p style="margin: 0; font-weight: 600; font-size: 15px;">Không tìm thấy tài khoản phù hợp</p>
                                    <p style="margin: 0; font-size: 13px; color: #94a3b8;">Vui lòng thử từ khóa tìm kiếm hoặc thay đổi bộ lọc</p>
                                    <a href="{{ route('admin.users.index') }}" class="btn-admin-action" style="margin-top: 10px;">Xóa bộ lọc</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 5. Thanh phân trang dưới cùng (Khớp đúng ảnh mẫu) --}}
        <div class="user-table-footer">
            <div class="user-footer-left">
                <span>
                    Hiển thị 1–{{ $users->count() }} trong tổng số {{ number_format($stats['total']) }} người dùng
                </span>

                <div class="user-per-page-select">
                    <span>Số dòng:</span>
                    <input type="number"
                           class="per-page-input"
                           value="{{ $filters['per_page'] }}"
                           min="5"
                           max="100"
                           onchange="location.href='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value">
                </div>
            </div>

            <div class="user-pagination">
                {{-- Nút về trang đầu |< --}}
                <a href="{{ $users->url(1) }}" class="page-btn {{ $users->onFirstPage() ? 'disabled' : '' }}" title="Trang đầu">
                    &verbar;&lt;
                </a>

                {{-- Nút trang trước < --}}
                <a href="{{ $users->previousPageUrl() }}" class="page-btn {{ $users->onFirstPage() ? 'disabled' : '' }}" title="Trang trước">
                    &lt;
                </a>

                {{-- Trang 1 (Active) --}}
                <a href="{{ $users->url(1) }}" class="page-btn {{ $users->currentPage() === 1 ? 'active' : '' }}">1</a>

                @if ($users->lastPage() >= 2)
                    <a href="{{ $users->url(2) }}" class="page-btn {{ $users->currentPage() === 2 ? 'active' : '' }}">2</a>
                @else
                    <span class="page-btn">2</span>
                @endif

                @if ($users->lastPage() >= 3)
                    <a href="{{ $users->url(3) }}" class="page-btn {{ $users->currentPage() === 3 ? 'active' : '' }}">3</a>
                @else
                    <span class="page-btn">3</span>
                @endif

                <span class="page-btn disabled">...</span>
                <span class="page-btn">355</span>

                {{-- Nút trang sau > --}}
                <a href="{{ $users->nextPageUrl() ?: '#' }}" class="page-btn {{ !$users->hasMorePages() ? 'disabled' : '' }}" title="Trang sau">
                    &gt;
                </a>

                {{-- Nút về trang cuối >| --}}
                <a href="{{ $users->url($users->lastPage()) }}" class="page-btn {{ !$users->hasMorePages() ? 'disabled' : '' }}" title="Trang cuối">
                    &gt;&verbar;
                </a>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: THÊM NGƯỜI DÙNG MỚI --}}
<div id="modal-add-user" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Thêm người dùng mới</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-add-user')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Họ và tên <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="full_name" class="form-control-admin" placeholder="Ví dụ: Nguyễn Văn A" required>
                </div>
                <div class="form-group-admin">
                    <label>Địa chỉ Email <span style="color: #dc2626;">*</span></label>
                    <input type="email" name="email" class="form-control-admin" placeholder="name@studyprep.edu.vn" required>
                </div>
                <div class="form-group-admin">
                    <label>Tên đăng nhập (Username)</label>
                    <input type="text" name="user_name" class="form-control-admin" placeholder="Để trống sẽ tự tạo theo Email">
                </div>
                <div class="form-group-admin">
                    <label>Mật khẩu khởi tạo <span style="color: #dc2626;">*</span></label>
                    <input type="password" name="password" class="form-control-admin" placeholder="Tối thiểu 6 ký tự" required>
                </div>
                <div class="form-group-admin">
                    <label>Vai trò hệ thống <span style="color: #dc2626;">*</span></label>
                    <select name="role" class="form-control-admin" required>
                        <option value="user">Học viên (User / Learner)</option>
                        <option value="teacher">Giảng viên TOEIC & Chấm bài</option>
                        <option value="admin">Quản trị viên (Admin)</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label>Trạng thái ban đầu <span style="color: #dc2626;">*</span></label>
                    <select name="status" class="form-control-admin" required>
                        <option value="ACTIVE">Hoạt động (Active)</option>
                        <option value="PENDING">Chờ kích hoạt email (Pending)</option>
                        <option value="LOCKED">Khóa tài khoản (Locked)</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-add-user')">Hủy bỏ</button>
                <button type="submit" class="btn-admin-primary">Lưu người dùng</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: CHỈNH SỬA THÔNG TIN NGƯỜI DÙNG --}}
<div id="modal-edit-user" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Cập nhật tài khoản</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-edit-user')">&times;</button>
        </div>
        <form id="form-edit-user" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Họ và tên <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="edit_full_name" name="full_name" class="form-control-admin" required>
                </div>
                <div class="form-group-admin">
                    <label>Địa chỉ Email <span style="color: #dc2626;">*</span></label>
                    <input type="email" id="edit_email" name="email" class="form-control-admin" required>
                </div>
                <div class="form-group-admin">
                    <label>Mật khẩu mới (Bỏ trống nếu không thay đổi)</label>
                    <input type="password" name="password" class="form-control-admin" placeholder="Nhập mật khẩu mới nếu muốn đổi">
                </div>
                <div class="form-group-admin">
                    <label>Vai trò hệ thống</label>
                    <select id="edit_role" name="role" class="form-control-admin" required>
                        <option value="user">Học viên</option>
                        <option value="teacher">Giảng viên TOEIC</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label>Trạng thái tài khoản</label>
                    <select id="edit_status" name="status" class="form-control-admin" required>
                        <option value="ACTIVE">Hoạt động (Active)</option>
                        <option value="PENDING">Chờ kích hoạt (Pending)</option>
                        <option value="LOCKED">Bị khóa (Locked)</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-edit-user')">Đóng</button>
                <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: ĐẶT LẠI MẬT KHẨU --}}
<div id="modal-reset-pwd" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Đặt lại mật khẩu</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-reset-pwd')">&times;</button>
        </div>
        <form id="form-reset-pwd" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="admin-modal-body">
                <p style="font-size: 14px; color: #475569; margin-top: 0;">
                    Đặt lại mật khẩu đăng nhập cho tài khoản: <strong id="reset-pwd-user-name"></strong>
                </p>
                <div class="form-group-admin">
                    <label>Mật khẩu mới</label>
                    <input type="text" name="new_password" class="form-control-admin" value="StudyPrep@2026">
                    <small style="color: #64748b; font-size: 12px; margin-top: 4px; display: block;">
                        Mật khẩu mặc định gợi ý là <code>StudyPrep@2026</code>. Bạn có thể sửa thành mật khẩu khác.
                    </small>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-reset-pwd')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Xác nhận đặt lại</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: NHẬP DANH SÁCH TỪ FILE CSV --}}
<div id="modal-import" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Nhập danh sách tài khoản</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-import')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="admin-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                    Hỗ trợ tệp <code>.CSV</code> có các cột: <strong>Họ tên, Email, Vai trò (user / teacher)</strong>.
                </p>
                <div class="form-group-admin">
                    <label>Chọn tệp CSV từ máy tính</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="form-control-admin" required>
                </div>
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; font-size: 12.5px; color: #64748b;">
                    Mẫu dòng dữ liệu: <code>Nguyễn Văn A, nguyen@studyprep.edu.vn, user</code>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-import')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Tải lên & Nhập</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdminModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('open');
    }

    function closeAdminModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('open');
    }

    // Đóng modal khi click ra ngoài vùng backdrop
    document.querySelectorAll('.admin-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });

    // Mở modal Edit với dữ liệu người dùng
    function openEditModal(user) {
        var form = document.getElementById('form-edit-user');
        form.action = '/admin/users/' + user.user_id;
        document.getElementById('edit_full_name').value = user.full_name || user.user_name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_role').value = user.role || 'user';
        document.getElementById('edit_status').value = user.status || 'ACTIVE';
        openAdminModal('modal-edit-user');
    }

    // Mở modal Reset Password
    function openResetPasswordModal(userId, userName) {
        var form = document.getElementById('form-reset-pwd');
        form.action = '/admin/users/' + userId + '/reset-password';
        document.getElementById('reset-pwd-user-name').innerText = userName;
        openAdminModal('modal-reset-pwd');
    }

    // Check all checkbox
    var checkAll = document.getElementById('check-all-users');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.user-row-checkbox').forEach(function(cb) {
                cb.checked = checked;
            });
        });
    }
</script>
@endpush

@extends('layouts.admin', ['active' => 'de-thi'])

@section('title', 'Quản lý đề thi TOEIC')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <span class="crumb-active">Quản lý đề thi</span>
@endsection

@section('content')
<div class="qbank-page">

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
            <strong>Đã có lỗi xảy ra:</strong>
            <ul style="margin: 6px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. Tiêu đề trang & Nút thao tác phía trên (Đồng bộ bố cục với Ngân hàng câu hỏi) --}}
    <div class="user-page-header">
        <div>
            <div class="qbank-breadcrumb-sub">
                <span>Quản lý đào tạo</span>
                <span>></span>
                <span class="crumb-active">Quản lý đề thi TOEIC</span>
            </div>
            <div class="user-title-row">
                <h1 class="user-page-title">Quản lý đề thi TOEIC</h1>
            </div>
            <p class="user-page-subtitle" style="margin: 4px 0 0 0; color: #64748b; font-size: 13.5px;">
                Xây dựng, kiểm định chỉ tiêu ETS và quản lý bộ đề thi chuẩn (Listening, Reading, Speaking &amp; Writing)
            </p>
        </div>

        <div class="user-header-actions">
            <button type="button" class="btn-admin-primary" onclick="openAdminModal('modal-add-test')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Thêm đề thi</span>
            </button>
        </div>
    </div>

    {{-- 2. Bốn thẻ thống kê chỉ số KPI --}}
    <div class="qbank-kpi-grid">
        {{-- Thẻ 1: Tổng số đề thi --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Tổng số đề thi</p>
                <div class="qbank-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ $stats['total'] }}</span>
                <span class="qbank-kpi-sub">bộ đề</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Toàn bộ đề thi trong hệ thống</span>
            </div>
        </div>

        {{-- Thẻ 2: Listening & Reading --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Listening &amp; Reading</p>
                <div class="qbank-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ $stats['lr_count'] }}</span>
                <span class="qbank-kpi-sub">đề (Full L&amp;R / Nghe / Đọc)</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Đề 200 câu hoặc tách lẻ 100 câu</span>
            </div>
        </div>

        {{-- Thẻ 3: Speaking & Writing --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Speaking &amp; Writing</p>
                <div class="qbank-kpi-icon icon-pink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ $stats['sw_count'] }}</span>
                <span class="qbank-kpi-sub">đề (Full S&amp;W / Nói / Viết)</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Speaking 11 câu - Writing 8 câu - Full 19 câu</span>
            </div>
        </div>

        {{-- Thẻ 4: Đề thi đã hoàn thiện --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Đã hoàn thiện (100%)</p>
                <div class="qbank-kpi-icon icon-red" style="background: #ecfdf5; color: #059669;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ $stats['completed'] }}</span>
                <span class="qbank-kpi-sub">đề đạt chuẩn</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Đủ chính xác số câu đã duyệt từng Part</span>
            </div>
        </div>
    </div>

    {{-- 3. Bộ lọc và tìm kiếm (Render động từ $blueprints) --}}
    <form method="GET" action="{{ route('admin.tests.index') }}" class="user-filter-bar" style="margin-bottom: 24px;">
        <div class="user-search-wrap">
            <svg class="user-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" class="user-search-input" placeholder="Tìm theo tên đề thi, mã đề (VD: TEST-001)..." value="{{ $filters['search'] }}">
        </div>

        <div class="user-filter-select-wrap">
            <select name="type" class="user-filter-select" onchange="this.form.submit()">
                <option value="ALL" {{ $filters['type'] === 'ALL' ? 'selected' : '' }}>Tất cả loại đề</option>
                @foreach ($blueprints as $bpKey => $bp)
                    <option value="{{ $bpKey }}" {{ $filters['type'] === $bpKey ? 'selected' : '' }}>
                        {{ $bp['name'] }}
                    </option>
                @endforeach
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        <div class="user-filter-select-wrap">
            <select name="status" class="user-filter-select" onchange="this.form.submit()">
                <option value="ALL" {{ $filters['status'] === 'ALL' ? 'selected' : '' }}>Tất cả trạng thái</option>
                <option value="COMPLETED" {{ $filters['status'] === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn thiện (Đủ từng Part)</option>
                <option value="IN_PROGRESS" {{ $filters['status'] === 'IN_PROGRESS' ? 'selected' : '' }}>Đang biên soạn câu hỏi</option>
                <option value="EMPTY" {{ $filters['status'] === 'EMPTY' ? 'selected' : '' }}>Chưa có câu hỏi (0 câu)</option>
                <option value="INVALID" {{ $filters['status'] === 'INVALID' ? 'selected' : '' }}>Vượt chỉ tiêu / Lệch cấu trúc</option>
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        <button type="submit" class="user-filter-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Lọc</span>
        </button>

        @if ($filters['search'] || $filters['type'] !== 'ALL' || $filters['status'] !== 'ALL')
            <a href="{{ route('admin.tests.index') }}" class="user-refresh-btn" title="Đặt lại bộ lọc" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <polyline points="23 20 23 14 17 14"></polyline>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                </svg>
            </a>
        @endif
    </form>

    {{-- 4. Bảng danh sách đề thi --}}
    <div class="user-table-card">
        <div class="user-table-scroll">
            <table class="user-data-table">
                <thead>
                    <tr>
                        <th style="width: 95px;">Mã đề</th>
                        <th>Tên đề thi</th>
                        <th style="width: 235px;">Loại đề thi</th>
                        <th style="width: 105px;">Thời gian</th>
                        <th style="width: 195px;">Tiến độ câu hỏi</th>
                        <th style="width: 175px;">Trạng thái</th>
                        <th style="width: 135px;">Ngày tạo</th>
                        <th class="admin-actions-heading" style="width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tests as $test)
                        @php
                            $rate = $test['completion_rate'];
                            $barWidth = min(100, max(0, $rate));
                            $isComplete = $test['status'] === 'COMPLETED';
                            $isInvalid = $test['status'] === 'INVALID';
                            $isEmpty = $test['status'] === 'EMPTY';
                            $progressColor = $isInvalid ? '#dc2626' : ($isComplete ? '#059669' : ($isEmpty ? '#94a3b8' : '#3b35dc'));
                            $barColor = $isInvalid ? '#ef4444' : ($isComplete ? '#10b981' : ($isEmpty ? '#cbd5e1' : '#4f46e5'));
                            $iconType = $test['icon'] ?? 'headphones';
                        @endphp
                        <tr>
                            {{-- Mã đề --}}
                            <td>
                                <span class="user-id-code" style="font-weight: 700; color: #4338ca; white-space: nowrap;">{{ str_replace(['—', '–'], '-', $test['code']) }}</span>
                            </td>

                            {{-- Tên đề thi --}}
                            <td>
                                <div>
                                    <a href="{{ route('admin.tests.show', $test['id']) }}" style="font-weight: 700; color: #0f172a; text-decoration: none; font-size: 14.5px;" onmouseover="this.style.color='#3b35dc'" onmouseout="this.style.color='#0f172a'">
                                        {{ str_replace(['—', '–'], '-', $test['title']) }}
                                    </a>
                                    @if (!empty($test['is_newly_created']))
                                        <span style="display: inline-block; margin-left: 6px; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 6px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                            Vừa tạo
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Loại đề thi (6 màu badge & icon phân biệt theo từng kỹ năng) --}}
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: {{ $test['badge_bg'] }}; color: {{ $test['badge_color'] }}; border: 1px solid {{ $test['badge_border'] }}; white-space: nowrap;">
                                    @if ($iconType === 'headphones')
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                        </svg>
                                    @elseif ($iconType === 'mic')
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                            <line x1="12" y1="19" x2="12" y2="23"></line>
                                            <line x1="8" y1="23" x2="16" y2="23"></line>
                                        </svg>
                                    @elseif ($iconType === 'book')
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                        </svg>
                                    @else
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    @endif
                                    <span>{{ $test['short_type_name'] }} ({{ $test['target_questions'] }} câu)</span>
                                </span>
                            </td>

                            {{-- Thời gian --}}
                            <td>
                                <span style="font-size: 13.5px; font-weight: 600; color: #475569; white-space: nowrap;">
                                    {{ $test['total_time'] }} phút
                                </span>
                            </td>

                            {{-- Tiến độ câu hỏi (không cắt trần tỷ lệ % thực tế) --}}
                            <td>
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 5px;">
                                        <span style="font-weight: 700; color: {{ $isInvalid ? '#b91c1c' : '#1e293b' }};">
                                            {{ $test['current_questions'] }} / {{ $test['target_questions'] }}
                                        </span>
                                        <span style="font-weight: 700; color: {{ $progressColor }};">
                                            {{ number_format($rate, 1) }}%
                                        </span>
                                    </div>
                                    <div style="width: 100%; height: 7px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $barWidth }}%; background: {{ $barColor }}; border-radius: 999px; transition: width 0.3s ease;"></div>
                                    </div>
                                    @if (!empty($test['unapproved_count']))
                                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                            +{{ $test['unapproved_count'] }} câu chưa duyệt (không tính)
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Trạng thái --}}
                            <td>
                                @if ($isInvalid)
                                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 3px;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 8px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                            Vượt chỉ tiêu / Lệch cấu trúc
                                        </span>
                                        @if (!empty($test['issue_reason']))
                                            <span style="font-size: 11px; color: #b91c1c; font-weight: 600;">
                                                {{ ucfirst($test['issue_reason']) }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif ($isComplete)
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 8px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                        Đã hoàn thiện
                                    </span>
                                @elseif ($isEmpty)
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 8px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #94a3b8;"></span>
                                        Chưa có câu hỏi
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 8px; background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                                        Đang biên soạn
                                    </span>
                                @endif
                                @if (!empty($test['duplicate_count']) && $test['duplicate_count'] > 0)
                                    <div style="margin-top: 4px; font-size: 11px; font-weight: 700; color: #b45309;">
                                        ⚠ Trùng {{ $test['duplicate_count'] }} câu
                                    </div>
                                @endif
                            </td>

                            {{-- Ngày tạo --}}
                            <td>
                                <span style="font-size: 13px; color: #64748b; white-space: nowrap;">{{ $test['created_at'] ?: '-' }}</span>
                            </td>

                            {{-- Thao tác: Đồng bộ 100% style nút/icon SVG của Ngân hàng câu hỏi --}}
                            <td class="admin-actions-cell">
                                <div class="user-actions-cell">
                                    {{-- Nút Xem chi tiết & Soạn câu hỏi (Eye icon) --}}
                                    <a href="{{ route('admin.tests.show', $test['id']) }}"
                                       class="action-icon-btn"
                                       style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
                                       title="Xem chi tiết &amp; Soạn câu hỏi"
                                       aria-label="Chi tiết đề thi {{ $test['code'] }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    {{-- Nút Sửa thông tin đề thi (Pen icon) --}}
                                    <button type="button"
                                            class="action-icon-btn"
                                            title="Sửa thông tin đề thi"
                                            aria-label="Sửa đề thi {{ $test['code'] }}"
                                            onclick='openEditTestModal(@json($test))'>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>

                                    {{-- Nút Xóa đề thi (Trash icon) --}}
                                    <button type="button"
                                            class="action-icon-btn danger"
                                            title="Xóa đề thi"
                                            aria-label="Xóa đề thi {{ $test['code'] }}"
                                            onclick='confirmDeleteTest(@json($test["id"]), @json($test["code"]), @json($test["title"]), @json($test["total_attached_questions"] ?? $test["current_questions"]))'>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px 20px; color: #64748b;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                    <span style="font-size: 15px; font-weight: 600;">Không tìm thấy đề thi nào phù hợp với bộ lọc</span>
                                    <a href="{{ route('admin.tests.index') }}" class="btn-admin-action" style="margin-top: 6px; font-size: 13px;">Đặt lại bộ lọc</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer bảng & Thanh phân trang chuẩn hóa --}}
        <div class="user-table-footer">
            <div class="user-footer-left">
                <span>
                    Hiển thị <strong>{{ $tests->count() }}</strong> / <strong>{{ $tests->total() }}</strong> đề thi
                    @if ($tests->total() > 0)
                        (Bản ghi {{ $tests->firstItem() }}-{{ $tests->lastItem() }})
                    @endif
                </span>

                <div class="user-per-page-select">
                    <span>Số dòng:</span>
                    <input type="number"
                           class="per-page-input"
                           value="{{ $filters['per_page'] ?? 10 }}"
                           min="5"
                           max="100"
                           onchange="changePerPage(this.value)">
                </div>
            </div>

            <div class="user-pagination">
                {{-- Nút về trang đầu --}}
                @if ($tests->onFirstPage())
                    <span class="page-btn disabled" title="Trang đầu" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"></polyline>
                            <polyline points="18 17 13 12 18 7"></polyline>
                        </svg>
                    </span>
                @else
                    <a href="{{ $tests->url(1) }}" class="page-btn" title="Trang đầu">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"></polyline>
                            <polyline points="18 17 13 12 18 7"></polyline>
                        </svg>
                    </a>
                @endif

                {{-- Nút trang trước --}}
                @if ($tests->onFirstPage())
                    <span class="page-btn disabled" title="Trang trước" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                @else
                    <a href="{{ $tests->previousPageUrl() }}" class="page-btn" title="Trang trước">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                @endif

                {{-- Số trang động theo Database --}}
                @php
                    $curPage = $tests->currentPage();
                    $lastPage = max(1, $tests->lastPage());
                @endphp
                @for ($p = max(1, $curPage - 2); $p <= min($lastPage, $curPage + 2); $p++)
                    @if ($p === $curPage)
                        <span class="page-btn active">{{ $p }}</span>
                    @else
                        <a href="{{ $tests->url($p) }}" class="page-btn">{{ $p }}</a>
                    @endif
                @endfor

                {{-- Nút trang sau --}}
                @if ($tests->hasMorePages())
                    <a href="{{ $tests->nextPageUrl() }}" class="page-btn" title="Trang sau">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                @else
                    <span class="page-btn disabled" title="Trang sau" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                @endif

                {{-- Nút về trang cuối --}}
                @if ($tests->hasMorePages())
                    <a href="{{ $tests->url($lastPage) }}" class="page-btn" title="Trang cuối">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"></polyline>
                            <polyline points="6 17 11 12 6 7"></polyline>
                        </svg>
                    </a>
                @else
                    <span class="page-btn disabled" title="Trang cuối" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"></polyline>
                            <polyline points="6 17 11 12 6 7"></polyline>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- Form ẩn xóa đề thi --}}
<form id="delete-test-form" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- MODAL 1: THÊM ĐỀ THI MỚI --}}
<div id="modal-add-test" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 580px;">
        <div class="admin-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 9px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <h3 class="admin-modal-title" style="margin: 0;">Thêm đề thi TOEIC mới</h3>
            </div>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-add-test')">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.tests.store') }}" id="formAddTest">
            @csrf
            <input type="hidden" name="_modal" value="add">
            <div class="admin-modal-body" style="padding: 24px;">
                <p style="font-size: 13px; color: #64748b; margin-top: 0; margin-bottom: 20px; line-height: 1.5;">
                    Quy trình quản lý: <strong>Tạo đề thi trước -&gt; Thêm câu hỏi đúng Part -&gt; Xem trước &amp; Lưu đề thi</strong>. Hệ thống tự động khóa cứng cấu trúc Part chuẩn ETS theo loại đề bạn chọn.
                </p>

                {{-- 1. Tên đề thi --}}
                <div class="form-group-admin" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        Tên đề thi <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="title" class="form-control-admin" placeholder="Ví dụ: TOEIC Reading Practice Test 01 - ETS 2026" required value="{{ old('_modal') === 'add' ? old('title') : '' }}" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; outline: none;">
                </div>

                {{-- 2. Loại đề thi (Render động từ $blueprints) --}}
                <div class="form-group-admin" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        Loại đề thi TOEIC <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="exam_type" id="modalExamTypeSelect" class="form-control-admin" required onchange="renderBlueprintPreview('modalExamTypeSelect', 'modalStructureText', 'modalTimeDisplay', 'modalQuestionsDisplay')" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; outline: none; background: #fff;">
                        @foreach ($blueprints as $bpKey => $bp)
                            <option value="{{ $bpKey }}" {{ (old('_modal') === 'add' ? old('exam_type', 'TOEIC_LR') : 'TOEIC_LR') === $bpKey ? 'selected' : '' }}>
                                {{ $bp['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Cấu trúc đề thi (Tự động render từ $blueprints) --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 18px;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        Cấu trúc kiểm định tiêu chuẩn (Blueprint ETS)
                    </div>
                    <div id="modalStructureText" style="font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.65;"></div>
                </div>

                {{-- 4 & 5. Thời gian làm bài và Số lượng câu hỏi --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group-admin">
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #64748b; margin-bottom: 5px;">
                            Thời gian làm bài
                        </label>
                        <div id="modalTimeDisplay" style="padding: 10px 14px; background: #f1f5f9; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0;"></div>
                    </div>

                    <div class="form-group-admin">
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #64748b; margin-bottom: 5px;">
                            Tổng số câu hỏi yêu cầu
                        </label>
                        <div id="modalQuestionsDisplay" style="padding: 10px 14px; background: #f1f5f9; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0;"></div>
                    </div>
                </div>
            </div>

            <div class="admin-modal-footer" style="padding: 16px 24px; border-top: 1px solid #edf0f7; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-add-test')">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-admin-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Tạo đề thi</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: CHỈNH SỬA THÔNG TIN ĐỀ THI (ĐỒNG BỘ PREVIEW BLUEPRINT VỚI MODAL THÊM) --}}
<div id="modal-edit-test" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 580px;">
        <div class="admin-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 9px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <h3 class="admin-modal-title" id="edit-test-modal-title" style="margin: 0;">Cập nhật thông tin đề thi</h3>
            </div>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-edit-test')">&times;</button>
        </div>

        <form method="POST" action="" id="formEditTest">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit">
            <input type="hidden" name="_edit_test_id" id="editTestIdInput" value="{{ old('_edit_test_id') }}">
            <input type="hidden" name="_edit_test_code" id="editTestCodeInput" value="{{ old('_edit_test_code') }}">
            <input type="hidden" name="_edit_attached_count" id="editTestAttachedCountInput" value="{{ old('_edit_attached_count', 0) }}">

            <div class="admin-modal-body" style="padding: 24px;">
                <div class="form-group-admin" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        Tên đề thi <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="title" id="editTestTitleInput" class="form-control-admin" required value="{{ old('_modal') === 'edit' ? old('title') : '' }}" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px;">
                </div>

                <div class="form-group-admin" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        Loại đề thi TOEIC <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="exam_type" id="editTestTypeSelect" class="form-control-admin" required onchange="onEditExamTypeChange()" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; background: #fff;">
                        @foreach ($blueprints as $bpKey => $bp)
                            <option value="{{ $bpKey }}" {{ (old('_modal') === 'edit' ? old('exam_type') : '') === $bpKey ? 'selected' : '' }}>
                                {{ $bp['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Cảnh báo tự động dọn câu hỏi sai kỹ năng khi đổi loại đề --}}
                <div id="editExamTypeWarning" style="display: none; margin-bottom: 16px; padding: 10px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 12.5px; color: #92400e; line-height: 1.5;">
                    <strong>Lưu ý khi đổi loại đề:</strong> Đề thi hiện có <strong id="editWarningCount">0</strong> câu hỏi đã gắn. Nếu đổi sang loại đề khác, hệ thống sẽ tự động gỡ các câu hỏi không thuộc kỹ năng/Part của loại đề mới.
                </div>

                {{-- Khung preview cấu trúc Part / Thời gian / Số câu cho Modal Sửa --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 18px;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        Cấu trúc kiểm định tiêu chuẩn (Blueprint ETS)
                    </div>
                    <div id="editModalStructureText" style="font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.65;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group-admin">
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #64748b; margin-bottom: 5px;">
                            Thời gian làm bài
                        </label>
                        <div id="editModalTimeDisplay" style="padding: 10px 14px; background: #f1f5f9; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0;"></div>
                    </div>

                    <div class="form-group-admin">
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #64748b; margin-bottom: 5px;">
                            Tổng số câu hỏi yêu cầu
                        </label>
                        <div id="editModalQuestionsDisplay" style="padding: 10px 14px; background: #f1f5f9; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0;"></div>
                    </div>
                </div>
            </div>

            <div class="admin-modal-footer" style="padding: 16px 24px; border-top: 1px solid #edf0f7; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-edit-test')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    var EXAM_BLUEPRINTS = @json($blueprints);
    var EDIT_ORIGINAL_EXAM_TYPE = '';
    var EDIT_ATTACHED_QUESTIONS_COUNT = 0;

    function escapeHtmlText(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('open');
            if (id === 'modal-add-test') {
                renderBlueprintPreview('modalExamTypeSelect', 'modalStructureText', 'modalTimeDisplay', 'modalQuestionsDisplay');
            } else if (id === 'modal-edit-test') {
                renderBlueprintPreview('editTestTypeSelect', 'editModalStructureText', 'editModalTimeDisplay', 'editModalQuestionsDisplay');
            }
        }
    }

    function closeAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('open');
        }
    }

    document.querySelectorAll('.admin-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });

    function renderBlueprintPreview(selectId, structureId, timeId, questionsId) {
        var select = document.getElementById(selectId);
        var structureText = document.getElementById(structureId);
        var timeDisplay = document.getElementById(timeId);
        var questionsDisplay = document.getElementById(questionsId);

        if (!select || !structureText || !timeDisplay || !questionsDisplay) return;

        var selectedKey = select.value;
        var bp = EXAM_BLUEPRINTS[selectedKey] || EXAM_BLUEPRINTS['TOEIC_LR'];
        if (!bp) return;

        var lines = [];
        var skills = bp.skills || {};
        Object.keys(skills).forEach(function(skillKey) {
            var skill = skills[skillKey];
            var partSummary = (skill.parts || []).map(function(part) {
                return escapeHtmlText(part.name.split(':')[0]) + ' (' + part.target_count + ' câu)';
            }).join(', ');

            lines.push(
                '<div>&bull; <strong>' + escapeHtmlText(skill.name) + ' (' + skill.total_questions + ' câu - ' + skill.time_limit + 'p):</strong> ' + partSummary + '</div>'
            );
        });

        structureText.innerHTML = lines.join('');
        timeDisplay.textContent = bp.time_note || (bp.total_time + ' phút');
        questionsDisplay.textContent = bp.total_questions + ' câu hỏi chuẩn ETS';
    }

    function onEditExamTypeChange() {
        renderBlueprintPreview('editTestTypeSelect', 'editModalStructureText', 'editModalTimeDisplay', 'editModalQuestionsDisplay');
        var select = document.getElementById('editTestTypeSelect');
        var warnBox = document.getElementById('editExamTypeWarning');
        var warnCount = document.getElementById('editWarningCount');
        if (!select || !warnBox) return;

        if (EDIT_ATTACHED_QUESTIONS_COUNT > 0 && EDIT_ORIGINAL_EXAM_TYPE && select.value !== EDIT_ORIGINAL_EXAM_TYPE) {
            if (warnCount) warnCount.textContent = String(EDIT_ATTACHED_QUESTIONS_COUNT);
            warnBox.style.display = 'block';
        } else {
            warnBox.style.display = 'none';
        }
    }

    function openEditTestModal(test) {
        var form = document.getElementById('formEditTest');
        if (!form) return;

        EDIT_ORIGINAL_EXAM_TYPE = test.exam_type || 'TOEIC_LR';
        EDIT_ATTACHED_QUESTIONS_COUNT = Number(test.total_attached_questions || test.current_questions || 0);

        form.action = '{{ url('/admin/tests') }}/' + test.id;
        document.getElementById('edit-test-modal-title').textContent = 'Cập nhật đề thi ' + test.code;
        document.getElementById('editTestIdInput').value = test.id;
        document.getElementById('editTestCodeInput').value = test.code || '';
        document.getElementById('editTestAttachedCountInput').value = EDIT_ATTACHED_QUESTIONS_COUNT;
        document.getElementById('editTestTitleInput').value = test.title || '';
        document.getElementById('editTestTypeSelect').value = EDIT_ORIGINAL_EXAM_TYPE;

        var warnBox = document.getElementById('editExamTypeWarning');
        if (warnBox) warnBox.style.display = 'none';

        openAdminModal('modal-edit-test');
    }

    function confirmDeleteTest(testId, testCode, testTitle, attachedCount) {
        var count = Number(attachedCount || 0);
        var message = 'Bạn có chắc chắn muốn xóa đề thi "' + testTitle + '" (' + testCode + ') khỏi hệ thống?';
        if (count > 0) {
            message += '\n\nLưu ý: Đề thi này hiện đang liên kết ' + count + ' câu hỏi (câu hỏi gốc trong Ngân hàng vẫn được giữ nguyên).';
        }

        if (confirm(message)) {
            var form = document.getElementById('delete-test-form');
            form.action = '{{ url('/admin/tests') }}/' + testId;
            form.submit();
        }
    }

    function changePerPage(rawValue) {
        var perPage = parseInt(rawValue, 10);
        if (isNaN(perPage) || perPage < 5) perPage = 5;
        if (perPage > 100) perPage = 100;

        var url = new URL(window.location.href);
        url.searchParams.set('per_page', String(perPage));
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderBlueprintPreview('modalExamTypeSelect', 'modalStructureText', 'modalTimeDisplay', 'modalQuestionsDisplay');

        @if (isset($errors) && $errors->any())
            var failedModal = @json(old('_modal'));
            if (failedModal === 'add') {
                openAdminModal('modal-add-test');
            } else if (failedModal === 'edit') {
                var editId = @json(old('_edit_test_id'));
                var editCode = @json(old('_edit_test_code'));
                var editAttached = Number(@json(old('_edit_attached_count', 0)));
                if (editId) {
                    var form = document.getElementById('formEditTest');
                    if (form) form.action = '{{ url('/admin/tests') }}/' + editId;
                    if (editCode) {
                        document.getElementById('edit-test-modal-title').textContent = 'Cập nhật đề thi ' + editCode;
                    }
                    EDIT_ATTACHED_QUESTIONS_COUNT = editAttached;
                    openAdminModal('modal-edit-test');
                }
            }
        @endif
    });
</script>
@endpush
@endsection

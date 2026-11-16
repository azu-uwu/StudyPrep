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
        <div class="alert-admin-success" style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-admin-error" style="display: flex; align-items: center; gap: 10px; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <strong>Đã có lỗi xảy ra:</strong>
            <ul style="margin: 6px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. Tiêu đề trang & Hai nút thao tác phía trên (Header) --}}
    <div class="user-page-header">
        <div>
            <div class="qbank-breadcrumb-sub">
                <span>Quản lý đào tạo</span>
                <span>></span>
                <span class="crumb-active">Quản lý đề thi TOEIC</span>
            </div>
            <div class="user-title-row">
                <h1 class="user-page-title">Quản lý đề thi TOEIC</h1>
                <span class="user-count-badge">{{ count($tests) }} đề thi</span>
            </div>
            <p class="user-page-subtitle">Quản lý các đề thi chuẩn hóa TOEIC Full Test (Listening & Reading và Speaking & Writing)</p>
        </div>

        <div class="user-header-actions">
            {{-- Nút 1: Thêm câu hỏi mới (Dẫn tới Ngân hàng câu hỏi chung) --}}
            <a href="{{ route('admin.questions.create') }}" class="btn-admin-action" style="color: #4338ca; border-color: #c7d2fe; background: #f5f3ff; font-weight: 700;" title="Thêm câu hỏi mới vào Ngân hàng câu hỏi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Thêm câu hỏi mới</span>
            </a>

            {{-- Nút 2: Thêm đề thi (Mở modal tạo Full Test mới) --}}
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

        {{-- Thẻ 2: Listening & Reading (200 câu) --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Listening & Reading</p>
                <div class="qbank-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ $stats['lr_count'] }}</span>
                <span class="qbank-kpi-sub">đề (200 câu / đề)</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Part 1 đến Part 7 (120 phút)</span>
            </div>
        </div>

        {{-- Thẻ 3: Speaking & Writing (19 câu) --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Speaking & Writing</p>
                <div class="qbank-kpi-icon icon-pink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ $stats['sw_count'] }}</span>
                <span class="qbank-kpi-sub">đề (19 câu / đề)</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Nhiệm vụ Nói & Viết (~80 phút)</span>
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
                <span>Đủ số lượng câu hỏi tất cả các Part</span>
            </div>
        </div>
    </div>

    {{-- 3. Bộ lọc và tìm kiếm --}}
    <form method="GET" action="{{ route('admin.tests.index') }}" class="user-filter-bar" style="margin-bottom: 24px;">
        <div class="user-search-wrap">
            <svg class="user-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" class="user-search-input" placeholder="Tìm theo tên đề thi, mã đề..." value="{{ $filters['search'] }}">
        </div>

        <div class="user-filter-select-wrap">
            <select name="type" class="user-filter-select">
                <option value="ALL" {{ $filters['type'] === 'ALL' ? 'selected' : '' }}>Tất cả loại đề</option>
                <option value="TOEIC_LR" {{ $filters['type'] === 'TOEIC_LR' ? 'selected' : '' }}>TOEIC Listening & Reading (200 câu)</option>
                <option value="TOEIC_SW" {{ $filters['type'] === 'TOEIC_SW' ? 'selected' : '' }}>TOEIC Speaking & Writing (19 câu)</option>
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        <div class="user-filter-select-wrap">
            <select name="status" class="user-filter-select">
                <option value="ALL" {{ $filters['status'] === 'ALL' ? 'selected' : '' }}>Tất cả trạng thái</option>
                <option value="COMPLETED" {{ $filters['status'] === 'COMPLETED' ? 'selected' : '' }}>Đã hoàn thiện (Đủ câu)</option>
                <option value="IN_PROGRESS" {{ $filters['status'] === 'IN_PROGRESS' ? 'selected' : '' }}>Đang biên soạn câu hỏi</option>
                <option value="EMPTY" {{ $filters['status'] === 'EMPTY' ? 'selected' : '' }}>Chưa có câu hỏi (0 câu)</option>
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
                        <th style="width: 80px;">Mã đề</th>
                        <th>Tên đề thi</th>
                        <th style="width: 220px;">Loại đề thi</th>
                        <th style="width: 130px;">Thời gian</th>
                        <th style="width: 200px;">Tiến độ câu hỏi</th>
                        <th style="width: 150px;">Trạng thái</th>
                        <th style="width: 140px;">Ngày tạo</th>
                        <th class="admin-actions-heading" style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tests as $test)
                        @php
                            $isLR = $test['exam_type'] === 'TOEIC_LR';
                            $rate = $test['completion_rate'];
                            $isComplete = $test['status'] === 'COMPLETED';
                            $isEmpty = $test['status'] === 'EMPTY';
                        @endphp
                        <tr>
                            {{-- Mã đề --}}
                            <td>
                                <span class="user-id-code" style="font-weight: 700; color: #4338ca;">{{ $test['code'] }}</span>
                            </td>

                            {{-- Tên đề thi --}}
                            <td>
                                <div>
                                    <a href="{{ route('admin.tests.show', $test['id']) }}" style="font-weight: 700; color: #0f172a; text-decoration: none; font-size: 14.5px;" onmouseover="this.style.color='#3b35dc'" onmouseout="this.style.color='#0f172a'">
                                        {{ $test['title'] }}
                                    </a>
                                    @if (!empty($test['is_mock_created']))
                                        <span style="display: inline-block; margin-left: 6px; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 6px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                            Vừa tạo
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Loại đề thi --}}
                            <td>
                                @if ($isLR)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                        </svg>
                                        Listening & Reading (200 câu)
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        Speaking & Writing (19 câu)
                                    </span>
                                @endif
                            </td>

                            {{-- Thời gian --}}
                            <td>
                                <span style="font-size: 13.5px; font-weight: 600; color: #475569;">
                                    {{ $test['total_time'] }} phút
                                </span>
                            </td>

                            {{-- Tiến độ câu hỏi --}}
                            <td>
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 5px;">
                                        <span style="font-weight: 700; color: #1e293b;">
                                            {{ $test['current_questions'] }} / {{ $test['target_questions'] }}
                                        </span>
                                        <span style="font-weight: 700; color: {{ $isComplete ? '#059669' : ($isEmpty ? '#94a3b8' : '#3b35dc') }};">
                                            {{ number_format($rate, 1) }}%
                                        </span>
                                    </div>
                                    <div style="width: 100%; height: 7px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $rate }}%; background: {{ $isComplete ? '#10b981' : ($isEmpty ? '#cbd5e1' : '#4f46e5') }}; border-radius: 999px; transition: width 0.3s ease;"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Trạng thái --}}
                            <td>
                                @if ($isComplete)
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
                            </td>

                            {{-- Ngày tạo --}}
                            <td>
                                <span style="font-size: 13px; color: #64748b;">{{ $test['created_at'] }}</span>
                            </td>

                            {{-- Thao tác --}}
                            <td class="admin-actions-cell">
                                <div class="user-actions-cell" style="justify-content: flex-end;">
                                    <a href="{{ route('admin.tests.show', $test['id']) }}" class="btn-admin-action" style="padding: 5px 12px; font-size: 12.5px; font-weight: 700; color: #3b35dc; border-color: #c7d2fe; text-decoration: none;" title="Xem chi tiết đề thi">
                                        Chi tiết
                                    </a>
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

        {{-- Footer bảng --}}
        <div class="user-table-footer">
            <div class="user-footer-left">
                <span>Hiển thị <strong>{{ count($tests) }}</strong> / <strong>{{ $stats['total'] }}</strong> đề thi</span>
            </div>
        </div>
    </div>

</div>

{{-- MODAL THÊM ĐỀ THI MỚI (Khớp thiết kế Admin hiện có) --}}
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
                <h3 class="admin-modal-title" style="margin: 0;">Thêm đề thi Full Test mới</h3>
            </div>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-add-test')">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.tests.store') }}" id="formAddTest">
            @csrf
            <div class="admin-modal-body" style="padding: 24px;">
                <p style="font-size: 13px; color: #64748b; margin-top: 0; margin-bottom: 20px; line-height: 1.5;">
                    Quy trình quản lý: <strong>Tạo đề thi trước &rarr; Thêm câu hỏi sau &rarr; Hoàn thiện đề thi</strong>. Đề thi mới tạo ban đầu sẽ có 0 câu hỏi và sẵn sàng để bạn bổ sung câu hỏi vào từng Part.
                </p>

                {{-- 1. Tên đề thi --}}
                <div class="form-group-admin" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        Tên đề thi <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="title" class="form-control-admin" placeholder="Ví dụ: TOEIC Actual Test 05 - ETS 2026" required value="{{ old('title') }}" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; outline: none;">
                </div>

                {{-- 2. Loại đề thi (Đúng 2 lựa chọn Full Test bắt buộc) --}}
                <div class="form-group-admin" style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        Loại đề thi TOEIC <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="exam_type" id="modalExamTypeSelect" class="form-control-admin" required onchange="updateModalExamStructure()" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px; outline: none; background: #fff;">
                        <option value="TOEIC_LR" {{ old('exam_type', 'TOEIC_LR') === 'TOEIC_LR' ? 'selected' : '' }}>
                            TOEIC Listening & Reading (Full Test)
                        </option>
                        <option value="TOEIC_SW" {{ old('exam_type') === 'TOEIC_SW' ? 'selected' : '' }}>
                            TOEIC Speaking & Writing (Full Test)
                        </option>
                    </select>
                </div>

                {{-- 3. Cấu trúc đề thi (Tự động hiển thị theo loại đề) --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 18px;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        Cấu trúc kiểm định tiêu chuẩn (Blueprint)
                    </div>
                    <div id="modalStructureText" style="font-size: 13.5px; font-weight: 600; color: #1e293b; line-height: 1.6;">
                        {{-- Sẽ cập nhật tự động bởi JS --}}
                    </div>
                </div>

                {{-- 4 & 5. Thời gian làm bài và Số lượng câu hỏi (Tự động hiển thị - Khóa nhập tay) --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group-admin">
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #64748b; margin-bottom: 5px;">
                            Thời gian làm bài
                        </label>
                        <div id="modalTimeDisplay" style="padding: 10px 14px; background: #f1f5f9; border-radius: 10px; font-size: 14px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0;">
                            120 phút
                        </div>
                    </div>

                    <div class="form-group-admin">
                        <label style="display: block; font-size: 12.5px; font-weight: 700; color: #64748b; margin-bottom: 5px;">
                            Tổng số câu hỏi yêu cầu
                        </label>
                        <div id="modalQuestionsDisplay" style="padding: 10px 14px; background: #f1f5f9; border-radius: 10px; font-size: 14px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0;">
                            200 câu hỏi
                        </div>
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

@push('scripts')
<script>
    // Hàm mở/đóng Modal chuẩn phong cách Admin StudyPrep
    function openAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('open');
            updateModalExamStructure();
        }
    }

    function closeAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('open');
        }
    }

    // Đóng modal khi click ra ngoài backdrop
    document.querySelectorAll('.admin-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });

    // Cập nhật cấu trúc đề thi, thời gian và số câu hỏi tương ứng trong Modal
    function updateModalExamStructure() {
        var select = document.getElementById('modalExamTypeSelect');
        var structureText = document.getElementById('modalStructureText');
        var timeDisplay = document.getElementById('modalTimeDisplay');
        var questionsDisplay = document.getElementById('modalQuestionsDisplay');

        if (!select || !structureText || !timeDisplay || !questionsDisplay) return;

        var selectedVal = select.value;

        if (selectedVal === 'TOEIC_LR') {
            structureText.innerHTML = '• <strong>Listening:</strong> Part 1 (6 câu), Part 2 (25 câu), Part 3 (39 câu), Part 4 (30 câu)<br>' +
                                      '• <strong>Reading:</strong> Part 5 (30 câu), Part 6 (16 câu), Part 7 (54 câu)';
            timeDisplay.textContent = '120 phút (Listening 45p, Reading 75p)';
            questionsDisplay.textContent = '200 câu hỏi tiêu chuẩn';
        } else {
            structureText.innerHTML = '• <strong>Speaking:</strong> 11 câu hỏi / nhiệm vụ thực tế (Khoảng 20 phút)<br>' +
                                      '• <strong>Writing:</strong> 8 câu hỏi / nhiệm vụ viết luận và phản hồi (60 phút)';
            timeDisplay.textContent = '80 phút (Speaking ~20p, Writing 60p)';
            questionsDisplay.textContent = '19 câu hỏi / nhiệm vụ';
        }
    }

    // Tự động kích hoạt khi load trang
    document.addEventListener('DOMContentLoaded', function() {
        updateModalExamStructure();
    });
</script>
@endpush
@endsection

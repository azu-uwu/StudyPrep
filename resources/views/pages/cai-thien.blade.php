<x-layouts.app active="cai-thien" title="StudyPrep - Cải thiện">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/cai-thien.css') }}">
@endpush

{{-- ===== KHUNG CHÍNH TRANG CẢI THIỆN ===== --}}
<div id="ctMainWrapper">

{{-- ===== TIÊU ĐỀ TRANG (Đồng bộ chuẩn hệ thống StudyPrep) ===== --}}
<div class="ct-page-header-row">
    <h1 class="ct-page-title">Cải thiện</h1>

    {{-- Thanh chuyển đổi nhanh chế độ mẫu (hỗ trợ kiểm tra giao diện & Empty State theo YC) --}}
    <div class="ct-demo-toggle-wrap" title="Chuyển đổi trạng thái dữ liệu mẫu để kiểm tra giao diện">
        <span class="ct-demo-toggle-label">Dữ liệu mẫu:</span>
        <button type="button" class="ct-demo-toggle-btn is-active" data-mode="full" onclick="setDemoState('full', this)">Có dữ liệu</button>
        <button type="button" class="ct-demo-toggle-btn" data-mode="empty_wrong" onclick="setDemoState('empty_wrong', this)">Trống câu sai</button>
        <button type="button" class="ct-demo-toggle-btn" data-mode="empty_weakness" onclick="setDemoState('empty_weakness', this)">Trống điểm yếu</button>
        <button type="button" class="ct-demo-toggle-btn" data-mode="all_empty" onclick="setDemoState('all_empty', this)">Chưa có dữ liệu</button>
    </div>
</div>

{{-- ===== HÀNG 2 THẺ THỐNG KÊ TỔNG HỢP ===== --}}
<div class="ct-stats-grid">
    {{-- Thẻ 1: Tổng câu sai --}}
    <div class="ct-stat-card" id="ctStatCardWrong">
        <div class="ct-stat-content">
            <p class="ct-stat-label">Tổng câu sai</p>
            <div class="ct-stat-value-wrap">
                <span class="ct-stat-number ct-stat-number--danger" id="statWrongCount">150</span>
                <span class="ct-stat-unit" id="statWrongUnit">câu</span>
            </div>
        </div>
        <div class="ct-stat-icon-wrap ct-stat-icon-wrap--amber">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 6V3.5L18.5 8H15zM8 12h8v2H8v-2zm0 4h5v2H8v-2z"/>
            </svg>
        </div>
    </div>

    {{-- Thẻ 2: Điểm yếu nhất --}}
    <div class="ct-stat-card ct-stat-card--danger" id="ctStatCardWeakness">
        <div class="ct-stat-content">
            <p class="ct-stat-label">Điểm yếu nhất</p>
            <div class="ct-stat-value-wrap">
                <span class="ct-stat-number ct-stat-number--danger" id="statWeaknessName">Part 4</span>
                <span class="ct-stat-badge" id="statWeaknessBadge">Độ thành thạo 18%</span>
            </div>
        </div>
        <div class="ct-stat-icon-wrap ct-stat-icon-wrap--red" id="statWeaknessIconWrap">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </div>
    </div>
</div>

{{-- ===== BỘ CHUYỂN ĐỔI TAB: MẶC ĐỊNH LÀ "CÂU SAI" ===== --}}
<div class="ct-segmented-switcher-wrap">
    <div class="ct-segmented-switcher" role="tablist">
        {{-- Tab 1: Câu sai (Mặc định active) --}}
        <button type="button" class="ct-switcher-tab is-active" id="tab-btn-wrong" onclick="switchImproveTab('wrong')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
            </svg>
            <span>Câu sai</span>
        </button>

        {{-- Tab 2: Điểm yếu --}}
        <button type="button" class="ct-switcher-tab ct-switcher-tab--weakness" id="tab-btn-weakness" onclick="switchImproveTab('weakness')">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            <span>Điểm yếu</span>
        </button>
    </div>
</div>

{{-- =========================================================================
     TAB CONTENT 1: CÂU SAI (TỔNG HỢP THEO PART, CÓ "XEM KẾT QUẢ" VÀ "LÀM LẠI")
     ========================================================================= --}}
<div id="view-wrong" class="ct-tab-view is-active">

    {{-- Trạng thái CÓ DỮ LIỆU: Một khung card chung chứa danh sách Part phân cách nhẹ --}}
    <div class="ct-wrong-card-group" id="ctWrongDataContainer">

        {{-- Part 1: Mô tả hình (22 câu sai khớp Ảnh 1 & Ảnh 2) --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #E0F2FE;">
                    <svg width="40" height="40" viewBox="0 0 34 34" fill="none">
                        <path d="M6 25L14 15L19 21L22 17L27 25H6Z" fill="#10B981"/>
                        <circle cx="21" cy="11" r="3" fill="#F59E0B"/>
                        <g transform="translate(13, 7) rotate(32)">
                            <rect x="0" y="0" width="4" height="14" rx="1.5" fill="#F59E0B"/>
                            <path d="M0 14L2 18L4 14Z" fill="#3B82F6"/>
                        </g>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 1: Mô tả hình</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">22</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(1)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(1, 22)">
                    Làm lại
                </x-button>
            </div>
        </div>

        {{-- Part 2: Hỏi & Đáp --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #EFF6FF;">
                    <svg width="40" height="40" viewBox="0 0 34 34" fill="none">
                        <rect x="7" y="9" width="14" height="11" rx="4" fill="#38BDF8"/>
                        <path d="M10 20L8 23V20H10Z" fill="#38BDF8"/>
                        <line x1="10" y1="13" x2="18" y2="13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="10" y1="16" x2="15" y2="16" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <rect x="15" y="14" width="13" height="11" rx="4" fill="#EF4444"/>
                        <path d="M25 25L23 27V25H25Z" fill="#EF4444"/>
                        <text x="21.5" y="22.5" font-size="8" font-weight="800" fill="white" text-anchor="middle">A</text>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 2: Hỏi &amp; Đáp</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">15</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(2)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(2, 15)">
                    Làm lại
                </x-button>
            </div>
        </div>

        {{-- Part 3: Hội thoại ngắn --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #FEF3C7;">
                    <svg width="40" height="40" viewBox="0 0 34 34" fill="none">
                        <rect x="8" y="7" width="16" height="11" rx="4" fill="#F59E0B"/>
                        <path d="M11 18L9 21V18H11Z" fill="#F59E0B"/>
                        <line x1="11" y1="11" x2="21" y2="11" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="11" y1="14" x2="17" y2="14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <rect x="13" y="15" width="15" height="11" rx="4" fill="#0D9488"/>
                        <path d="M25 26L23 28V26H25Z" fill="#0D9488"/>
                        <line x1="16" y1="19" x2="25" y2="19" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="16" y1="22" x2="22" y2="22" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 3: Hội thoại ngắn</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">32</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(3)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(3, 32)">
                    Làm lại
                </x-button>
            </div>
        </div>

        {{-- Part 4: Bài nói chuyện ngắn --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #FDF2F8;">
                    <svg width="40" height="40" viewBox="0 0 34 34" fill="none">
                        <circle cx="17" cy="11" r="3.5" fill="#FB7185"/>
                        <path d="M12 18C12 16 14 15 17 15C20 15 22 16 22 18V20H12V18Z" fill="#FDA4AF"/>
                        <path d="M13 20H21L20 28H14L13 20Z" fill="#94A3B8"/>
                        <rect x="11" y="19" width="12" height="2" rx="1" fill="#64748B"/>
                        <line x1="19" y1="16" x2="18" y2="19" stroke="#1E293B" stroke-width="1.2"/>
                        <circle cx="19" cy="15.5" r="1" fill="#1E293B"/>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 4: Bài nói chuyện ngắn</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">26</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(4)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(4, 26)">
                    Làm lại
                </x-button>
            </div>
        </div>

        {{-- Part 5: Hoàn thành câu --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #EEF2FF;">
                    <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                        <rect x="8" y="6" width="26" height="36" rx="5" fill="#FFFFFF" stroke="#1E293B" stroke-width="2.5"/>
                        <rect x="12" y="11" width="8" height="8" rx="2" fill="#38BDF8" stroke="#0284C7" stroke-width="1.2"/>
                        <path d="M14 15l2 2 3-3" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="12" y="22" width="8" height="8" rx="2" fill="#38BDF8" stroke="#0284C7" stroke-width="1.2"/>
                        <path d="M14 26l2 2 3-3" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <g transform="translate(32, 22) rotate(42)">
                            <rect x="-18" y="-4" width="22" height="8" rx="1" fill="#F59E0B" stroke="#1E293B" stroke-width="1.8"/>
                            <line x1="-18" y1="0" x2="4" y2="0" stroke="#D97706" stroke-width="1.2"/>
                            <path d="M-18 -4 L-25 0 L-18 4 Z" fill="#FDE68A" stroke="#1E293B" stroke-width="1.8"/>
                            <rect x="4" y="-4" width="5" height="8" rx="1" fill="#EF4444" stroke="#1E293B" stroke-width="1.8"/>
                        </g>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 5: Hoàn thành câu</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">22</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(5)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(5, 22)">
                    Làm lại
                </x-button>
            </div>
        </div>

        {{-- Part 6: Hoàn thành đoạn văn --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #EEF2FF;">
                    <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                        <rect x="8" y="6" width="24" height="32" rx="4" fill="#EEF2FF" stroke="#4F46E5" stroke-width="2"/>
                        <rect x="16" y="12" width="24" height="30" rx="4" fill="#FFFFFF" stroke="#4F46E5" stroke-width="2"/>
                        <line x1="22" y1="18" x2="34" y2="18" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/>
                        <line x1="22" y1="24" x2="32" y2="24" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/>
                        <line x1="22" y1="30" x2="34" y2="30" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 6: Hoàn thành đoạn văn</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">14</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(6)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(6, 14)">
                    Làm lại
                </x-button>
            </div>
        </div>

        {{-- Part 7: Đọc hiểu đoạn văn --}}
        <div class="ct-wrong-part-row">
            <div class="ct-wrong-part-left">
                <div class="ct-part-icon" style="background: #EFF6FF;">
                    <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                        <rect x="12" y="6" width="24" height="36" rx="4" fill="#3B82F6"/>
                        <rect x="12" y="6" width="5" height="36" rx="2" fill="#2563EB"/>
                        <line x1="32" y1="8" x2="32" y2="38" stroke="#93C5FD" stroke-width="1.2"/>
                        <path d="M18 36v8l3-2 3 2v-8z" fill="#BFDBFE"/>
                    </svg>
                </div>
                <div class="ct-wrong-part-info">
                    <h3 class="ct-wrong-part-title">Part 7: Đọc hiểu đoạn văn</h3>
                    <p class="ct-wrong-part-count">Số câu sai: <span class="ct-wrong-num">19</span></p>
                    <button type="button" class="ct-link-view-result" onclick="openPartResult(7)">Xem kết quả</button>
                </div>
            </div>
            <div class="ct-wrong-part-actions">
                <x-button variant="primary" size="small" type="button" onclick="goToRedoSetup(7, 19)">
                    Làm lại
                </x-button>
            </div>
        </div>

    </div>

    {{-- Trạng thái CHƯA CÓ DỮ LIỆU (EMPTY STATE) --}}
    <div class="ct-empty-state-box" id="ctWrongEmptyState" style="display: none;">
        <div class="ct-empty-state-icon">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <h3 class="ct-empty-state-title">Hiện chưa có câu sai</h3>
        <p class="ct-empty-state-desc">Hãy luyện tập hoặc làm đề thi thử để kiểm tra kiến thức và cải thiện kỹ năng của bạn.</p>
        <div class="ct-empty-state-actions">
            <x-button variant="primary" size="medium" href="{{ url('/luyen-tap') }}">
                Luyện tập
            </x-button>
            <x-button variant="neutral" size="medium" href="{{ url('/thi-thu') }}">
                Thi thử
            </x-button>
        </div>
    </div>

</div>

{{-- =========================================================================
     TAB CONTENT 2: ĐIỂM YẾU (BỐ CỤC CHUẨN XÁC THEO ẢNH 2)
     ========================================================================= --}}
<div id="view-weakness" class="ct-tab-view" style="display: none;">

    {{-- 1. Hàng bộ lọc kỹ năng: Tất cả 11 | Nghe 6 (Active mặc định như ảnh 2) | Đọc 5 --}}
    <div class="ct-filter-pills-row" id="ctWeaknessFilterRow">
        <button type="button" class="ct-filter-pill" data-filter="all" onclick="filterWeakness('all', this)">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            <span>Tất cả</span>
            <span class="ct-pill-badge" id="badgeCountAll">11</span>
        </button>

        <button type="button" class="ct-filter-pill is-active" data-filter="listen" onclick="filterWeakness('listen', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
            </svg>
            <span>Nghe</span>
            <span class="ct-pill-badge" id="badgeCountListen">6</span>
        </button>

        <button type="button" class="ct-filter-pill" data-filter="read" onclick="filterWeakness('read', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
            <span>Đọc</span>
            <span class="ct-pill-badge" id="badgeCountRead">5</span>
        </button>
    </div>

    {{-- 2. Tiêu đề phân mục: ⚡ Điểm yếu cần được cải thiện | TOP N & Tìm hiểu thêm --}}
    <div class="ct-section-meta-header" id="ctWeaknessMetaHeader">
        <h2 class="ct-section-meta-title">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            <span>Điểm yếu cần được cải thiện | <span class="ct-top-highlight" id="ctWeaknessTopLabel">TOP 6</span></span>
        </h2>
        <a href="javascript:void(0)" class="ct-learn-more-link" onclick="openLearnMoreModal()" data-sp-modal-open="ct-info-modal">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span>Tìm hiểu thêm</span>
        </a>
    </div>

    {{-- Khung chứa dữ liệu Điểm yếu --}}
    <div id="ctWeaknessDataContainer">

        {{-- 3. Điểm yếu cần ưu tiên nhất (Rank 1): Hiển thị riêng trong Card nền đỏ viền đỏ nhẹ --}}
        <div class="ct-weakness-priority-card" id="ctWeaknessPriorityWrap">
            <div class="ct-weakness-left">
                <div class="ct-rank-circle ct-rank-circle--red" id="priorityRankNum">1</div>
                <div class="ct-weakness-details">
                    <span class="ct-weakness-tag" id="priorityMetaTag">NGHE · PART 4</span>
                    <h3 class="ct-weakness-title" id="priorityTitle">Lời nói</h3>
                    <p class="ct-weakness-proficiency" id="priorityProficiency">Độ thành thạo 18%</p>
                </div>
            </div>
            <div class="ct-weakness-actions">
                <x-button variant="danger" size="small" href="{{ url('/luyen-tap/nghe?part=4') }}" id="priorityActionBtn">
                    Luyện ngay &rarr;
                </x-button>
            </div>
        </div>

        {{-- 4. Các điểm yếu còn lại (Rank 2 trở đi): Đặt chung trong một Card có đường phân cách nhẹ --}}
        <div class="ct-weakness-grouped-card" id="ctWeaknessGroupedCard">

            {{-- Rank 2: Hội thoại 2 người --}}
            <div class="ct-weakness-row" data-skill="listen" data-part="3">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">2</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE · PART 3</span>
                        <h3 class="ct-weakness-title">
                            Hội thoại 2 người
                            <span class="ct-tag-practiced">• Đã luyện 36 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 19%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/nghe?part=3') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Rank 3: Câu hỏi Yes/No --}}
            <div class="ct-weakness-row" data-skill="listen" data-part="2">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">3</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE · PART 2</span>
                        <h3 class="ct-weakness-title">
                            Câu hỏi Yes/No
                            <span class="ct-tag-practiced">• Đã luyện 8 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 29%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/nghe?part=2') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Rank 4: Câu hỏi có từ để hỏi --}}
            <div class="ct-weakness-row" data-skill="listen" data-part="2">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">4</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE · PART 2</span>
                        <h3 class="ct-weakness-title">
                            Câu hỏi có từ để hỏi
                            <span class="ct-tag-practiced">• Đã luyện 11 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 42%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/nghe?part=2') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Rank 5: Sự vật và bối cảnh trong ảnh --}}
            <div class="ct-weakness-row" data-skill="listen" data-part="1">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">5</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE · PART 1</span>
                        <h3 class="ct-weakness-title">
                            Sự vật và bối cảnh trong ảnh
                            <span class="ct-tag-predicted">• Dự đoán</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 21%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/nghe?part=1') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Rank 6: Lời nói + câu hỏi hình ảnh --}}
            <div class="ct-weakness-row" data-skill="listen" data-part="4">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">6</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE · PART 4</span>
                        <h3 class="ct-weakness-title">
                            Lời nói + câu hỏi hình ảnh
                            <span class="ct-tag-predicted">• Dự đoán</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 22%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/nghe?part=4') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Mục Đọc 1 (Part 5: Từ loại và cấu trúc) --}}
            <div class="ct-weakness-row" data-skill="read" data-part="5" style="display: none;">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">7</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">ĐỌC · PART 5</span>
                        <h3 class="ct-weakness-title">
                            Từ loại và cấu trúc ngữ pháp
                            <span class="ct-tag-practiced">• Đã luyện 20 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 25%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/doc?part=5') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Mục Đọc 2 (Part 5: Liên từ & Giới từ) --}}
            <div class="ct-weakness-row" data-skill="read" data-part="5" style="display: none;">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">8</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">ĐỌC · PART 5</span>
                        <h3 class="ct-weakness-title">
                            Liên từ &amp; Giới từ chỉ thời gian
                            <span class="ct-tag-practiced">• Đã luyện 14 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 28%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/doc?part=5') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Mục Đọc 3 (Part 6: Điền câu vào ngữ cảnh) --}}
            <div class="ct-weakness-row" data-skill="read" data-part="6" style="display: none;">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">9</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">ĐỌC · PART 6</span>
                        <h3 class="ct-weakness-title">
                            Điền câu hoàn chỉnh vào ngữ cảnh
                            <span class="ct-tag-practiced">• Đã luyện 8 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 34%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/doc?part=6') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Mục Đọc 4 (Part 7: Đọc hiểu văn bản đơn) --}}
            <div class="ct-weakness-row" data-skill="read" data-part="7" style="display: none;">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">10</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">ĐỌC · PART 7</span>
                        <h3 class="ct-weakness-title">
                            Đọc hiểu Email &amp; Thư báo nội bộ
                            <span class="ct-tag-practiced">• Đã luyện 16 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 38%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/doc?part=7') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

            {{-- Mục Đọc 5 (Part 7: Văn bản chéo đa đoạn) --}}
            <div class="ct-weakness-row" data-skill="read" data-part="7" style="display: none;">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">11</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">ĐỌC · PART 7</span>
                        <h3 class="ct-weakness-title">
                            Tìm thông tin đối chiếu văn bản kép
                            <span class="ct-tag-predicted">• Dự đoán</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 42%</p>
                    </div>
                </div>
                <div class="ct-weakness-actions">
                    <x-button variant="danger" size="small" href="{{ url('/luyen-tap/doc?part=7') }}">
                        Luyện
                    </x-button>
                </div>
            </div>

        </div>

    </div>

    {{-- Trạng thái CHƯA CÓ DỮ LIỆU ĐIỂM YẾU (EMPTY STATE) --}}
    <div class="ct-empty-state-box" id="ctWeaknessEmptyState" style="display: none;">
        <div class="ct-empty-state-icon">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </div>
        <h3 class="ct-empty-state-title">Chưa có dữ liệu phân tích điểm yếu</h3>
        <p class="ct-empty-state-desc">Hãy hoàn thành một bài thi thử để hệ thống phân tích kết quả và xác định những điểm bạn cần cải thiện.</p>
        <div class="ct-empty-state-actions">
            <x-button variant="primary" size="medium" href="{{ url('/thi-thu') }}">
                Thi thử ngay
            </x-button>
        </div>
    </div>

</div>

</div> {{-- ĐÓNG #ctMainWrapper --}}

{{-- =========================================================================
     GIAO DIỆN XEM KẾT QUẢ BÀI LUYỆN (KHỚP ẢNH 2)
     Hiển thị khi bấm "Xem kết quả" ở bất kỳ Part nào trong danh sách câu sai
     ========================================================================= --}}
<div id="ctResultView" class="ct-result-view" style="display: none;">
    <div class="ct-result-topbar">
        <button type="button" class="ct-result-close-btn" onclick="closePartResult()" aria-label="Quay lại trang Cải thiện" title="Quay lại">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="ct-result-heading">
        <div class="ct-result-eyebrow">KẾT QUẢ BÀI LUYỆN</div>
        <h1 class="ct-result-title" id="ctResultMainTitle">Part 1 — Nghe</h1>
    </div>

    {{-- Thanh Tabs lọc: Tất cả | Câu đúng | Câu sai | Chưa trả lời --}}
    <div class="ct-result-tabs" role="tablist">
        <button type="button" class="ct-result-tab-btn is-active" data-filter="all" onclick="filterResultQuestions('all', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            <span>Tất cả</span>
            <span class="ct-result-tab-badge" id="badgeResultAll">22</span>
        </button>

        <button type="button" class="ct-result-tab-btn" data-filter="correct" onclick="filterResultQuestions('correct', this)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Câu đúng</span>
            <span class="ct-result-tab-badge" id="badgeResultCorrect">0</span>
        </button>

        <button type="button" class="ct-result-tab-btn" data-filter="incorrect" onclick="filterResultQuestions('incorrect', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            <span>Câu sai</span>
            <span class="ct-result-tab-badge" id="badgeResultIncorrect">22</span>
        </button>

        <button type="button" class="ct-result-tab-btn" data-filter="unanswered" onclick="filterResultQuestions('unanswered', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>Chưa trả lời</span>
            <span class="ct-result-tab-badge" id="badgeResultUnanswered">0</span>
        </button>
    </div>

    {{-- Khung hiển thị danh sách câu hỏi của Part (Ảnh 2) --}}
    <div class="ct-result-part-card">
        <div class="ct-result-part-header" onclick="toggleResultAccordion()">
            <h3 class="ct-result-part-title" id="ctResultCardTitle">Part 1: Mô tả hình</h3>
            <div class="ct-result-header-right">
                <span class="ct-result-pct-pill">0%</span>
                <span class="ct-result-score-text" id="ctResultScoreText">0/22 Câu đúng</span>
                <button type="button" class="ct-result-accordion-btn" id="ctResultAccordionBtn" aria-label="Thu gọn hoặc mở rộng danh sách">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <div class="ct-result-part-body" id="ctResultQuestionsBody">
            {{-- Đổ dữ liệu động các câu hỏi qua JS --}}
        </div>
        <div class="ct-result-empty-filter" id="ctResultEmptyFilter">
            Không có câu hỏi nào thuộc phân loại này trong danh sách câu sai đã lưu.
        </div>
    </div>

    {{-- Nút hành động ở cuối --}}
    <div class="ct-result-bottom-actions">
        <x-button variant="success" size="large" type="button" id="ctResultRedoBtn" onclick="redoFromCurrentResult()">
            ÔN LẠI CÁC CÂU NÀY &rarr;
        </x-button>
        <x-button variant="neutral" size="large" type="button" onclick="closePartResult()">
            Quay lại Cải thiện
        </x-button>
    </div>
</div>

{{-- ===== MODAL "TÌM HIỂU THÊM" (TÁI SỬ DỤNG COMPONENT X-MODAL) ===== --}}
<x-modal id="ct-info-modal" title="Thuật toán xếp hạng Điểm yếu" maxWidth="500px">
    <p style="font-size: 14px; line-height: 1.6; color: var(--sp-text-body, #334155); margin: 0 0 16px;">
        Sau này StudyPrep sẽ sử dụng AI để tự động tổng hợp và phân tích kết quả bài thi thử của bạn nhằm xác định chính xác các điểm yếu cụ thể theo từng dạng bài và từng Part.
    </p>
    <ul style="font-size: 13.5px; color: var(--sp-text-body, #334155); line-height: 1.6; padding-left: 20px; margin: 0 0 20px;">
        <li style="margin-bottom: 6px;"><strong style="color: var(--sp-danger, #DC2626);">Thành thạo &lt; 60%:</strong> Báo động đỏ, cần ưu tiên luyện tập ngay để không bị trừ điểm đáng tiếc.</li>
        <li style="margin-bottom: 6px;"><strong style="color: var(--sp-warning, #D97706);">Thành thạo 60% - 85%:</strong> Đang tiến bộ, cần làm thêm 10-15 câu để củng cố phản xạ.</li>
        <li><strong style="color: var(--sp-success, #059669);">Thành thạo &gt; 85%:</strong> Đã vững kỹ năng.</li>
    </ul>

    <x-slot:actions>
        <x-button variant="primary" size="medium" data-sp-modal-close type="button" onclick="closeLearnMoreModal()" style="width: 100%;">
            Đã hiểu
        </x-button>
    </x-slot:actions>
</x-modal>

@push('scripts')
<script>
    /**
     * Dữ liệu Part và số câu sai mẫu
     */
    const PART_WRONG_DATA = {
        1: { name: 'Part 1: Mô tả hình', skillName: 'Nghe', skillRoute: 'nghe', count: 22, bg: '#E0F2FE' },
        2: { name: 'Part 2: Hỏi & Đáp', skillName: 'Nghe', skillRoute: 'nghe', count: 15, bg: '#EFF6FF' },
        3: { name: 'Part 3: Hội thoại ngắn', skillName: 'Nghe', skillRoute: 'nghe', count: 32, bg: '#FEF3C7' },
        4: { name: 'Part 4: Bài nói chuyện ngắn', skillName: 'Nghe', skillRoute: 'nghe', count: 26, bg: '#FDF2F8' },
        5: { name: 'Part 5: Hoàn thành câu', skillName: 'Đọc', skillRoute: 'doc', count: 22, bg: '#EEF2FF' },
        6: { name: 'Part 6: Hoàn thành đoạn văn', skillName: 'Đọc', skillRoute: 'doc', count: 14, bg: '#EEF2FF' },
        7: { name: 'Part 7: Đọc hiểu đoạn văn', skillName: 'Đọc', skillRoute: 'doc', count: 19, bg: '#EFF6FF' }
    };

    let currentResultPart = 1;

    /**
     * Điều hướng sang trang chọn số câu ôn lại (Tái sử dụng in-page setup - Ảnh 1)
     */
    function goToRedoSetup(partNum, wrongCount) {
        var info = PART_WRONG_DATA[partNum] || { count: wrongCount, skillRoute: (partNum <= 4 ? 'nghe' : 'doc') };
        var count = wrongCount || info.count;
        var baseUrl = (partNum <= 4) ? "{{ route('luyen-tap.nghe') }}" : "{{ url('/luyen-tap/doc') }}";
        window.location.href = baseUrl + '?part=' + partNum + '&mode=review&wrong=' + count;
    }

    /**
     * Mở giao diện xem kết quả câu sai của Part (Khớp Ảnh 2)
     */
    function openPartResult(partNum) {
        currentResultPart = partNum;
        var info = PART_WRONG_DATA[partNum] || { name: 'Part ' + partNum, skillName: (partNum <= 4 ? 'Nghe' : 'Đọc'), count: 22 };

        // Cập nhật Tiêu đề
        document.getElementById('ctResultMainTitle').textContent = 'Part ' + partNum + ' — ' + info.skillName;
        document.getElementById('ctResultCardTitle').textContent = info.name;

        // Cập nhật Badges số lượng
        document.getElementById('badgeResultAll').textContent = info.count;
        document.getElementById('badgeResultIncorrect').textContent = info.count;
        document.getElementById('badgeResultCorrect').textContent = '0';
        document.getElementById('badgeResultUnanswered').textContent = '0';

        // Cập nhật Điểm số & Tỉ lệ đúng
        document.getElementById('ctResultScoreText').textContent = '0/' + info.count + ' Câu đúng';
        document.getElementById('ctResultRedoBtn').innerHTML = '<span>ÔN LẠI ' + info.count + ' CÂU NÀY &rarr;</span>';

        // Reset các tab filter
        document.querySelectorAll('.ct-result-tab-btn').forEach(function(b) { b.classList.remove('is-active'); });
        var firstTab = document.querySelector('.ct-result-tab-btn[data-filter="all"]');
        if (firstTab) firstTab.classList.add('is-active');

        // Render danh sách câu hỏi
        renderResultQuestions(partNum, info.count);

        // Chuyển view: Ẩn trang chính Cải thiện, hiện View kết quả
        document.getElementById('ctMainWrapper').style.display = 'none';
        document.getElementById('ctResultView').style.display = 'block';

        // Cập nhật URL (?result_part=X)
        var newUrl = new URL(window.location);
        newUrl.searchParams.set('result_part', partNum);
        window.history.pushState({ result_part: partNum }, '', newUrl);

        // Cuộn mượt lên đầu
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /**
     * Đóng giao diện xem kết quả, quay lại trang Cải thiện
     */
    function closePartResult() {
        document.getElementById('ctResultView').style.display = 'none';
        document.getElementById('ctMainWrapper').style.display = 'block';

        var newUrl = new URL(window.location);
        newUrl.searchParams.delete('result_part');
        window.history.pushState({}, '', newUrl.pathname);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /**
     * Thu gọn hoặc mở rộng accordion danh sách câu hỏi
     */
    function toggleResultAccordion() {
        var body = document.getElementById('ctResultQuestionsBody');
        var btn = document.getElementById('ctResultAccordionBtn');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            btn.classList.remove('is-collapsed');
        } else {
            body.style.display = 'none';
            btn.classList.add('is-collapsed');
        }
    }

    /**
     * Lọc danh sách câu hỏi theo tab (Tất cả / Câu đúng / Câu sai / Chưa trả lời)
     */
    function filterResultQuestions(filter, btn) {
        document.querySelectorAll('.ct-result-tab-btn').forEach(function(b) { b.classList.remove('is-active'); });
        if (btn) btn.classList.add('is-active');

        var body = document.getElementById('ctResultQuestionsBody');
        var emptyMsg = document.getElementById('ctResultEmptyFilter');

        if (filter === 'correct' || filter === 'unanswered') {
            body.style.display = 'none';
            emptyMsg.style.display = 'block';
        } else {
            body.style.display = 'block';
            emptyMsg.style.display = 'none';
        }
    }

    /**
     * Ôn lại các câu sai của Part hiện tại từ giao diện kết quả
     */
    function redoFromCurrentResult() {
        var info = PART_WRONG_DATA[currentResultPart];
        goToRedoSetup(currentResultPart, info ? info.count : 22);
    }

    /**
     * Mở / đóng chi tiết giải thích một câu hỏi
     */
    function toggleResultQuestionDetail(index) {
        var detail = document.getElementById('ctQDetail_' + index);
        var chevron = document.getElementById('ctQChevron_' + index);
        if (!detail) return;

        if (detail.classList.contains('is-open')) {
            detail.classList.remove('is-open');
            if (chevron) chevron.classList.remove('is-open');
        } else {
            detail.classList.add('is-open');
            if (chevron) chevron.classList.add('is-open');
        }
    }

    /**
     * Sinh danh sách câu hỏi mẫu cho View Kết quả (Khớp 100% Ảnh 2)
     */
    function renderResultQuestions(partNum, totalCount) {
        var container = document.getElementById('ctResultQuestionsBody');
        if (!container) return;

        // Mẫu 10 câu đầu tiên cho Part 1 chuẩn xác theo Ảnh 2:
        // Câu 1: User A, Đúng D
        // Câu 2: User B, Đúng C
        // Câu 3: User B, Đúng A
        // Câu 4: User B, Đúng C
        // Câu 5: User B, Đúng D
        // Câu 6: User B, Đúng D
        // Câu 7: User C, Đúng B
        // Câu 8: User C, Đúng B
        // Câu 9: User C, Đúng D
        // Câu 10: User C, Đúng D
        var part1Answers = [
            { user: 'A', correct: 'D' },
            { user: 'B', correct: 'C' },
            { user: 'B', correct: 'A' },
            { user: 'B', correct: 'C' },
            { user: 'B', correct: 'D' },
            { user: 'B', correct: 'D' },
            { user: 'C', correct: 'B' },
            { user: 'C', correct: 'B' },
            { user: 'C', correct: 'D' },
            { user: 'C', correct: 'D' },
        ];

        var isPart2 = (partNum === 2);
        var optionsList = isPart2 ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];

        var html = '';
        var renderCount = Math.min(totalCount, 22);

        for (var i = 1; i <= renderCount; i++) {
            var answerPair;
            if (partNum === 1 && i <= 10) {
                answerPair = part1Answers[i - 1];
            } else {
                var u = isPart2 ? (i % 2 === 0 ? 'B' : 'A') : (i % 3 === 0 ? 'C' : (i % 2 === 0 ? 'B' : 'A'));
                var c = isPart2 ? (u === 'A' ? 'B' : (u === 'B' ? 'C' : 'A')) : (u === 'D' ? 'A' : (u === 'A' ? 'D' : (u === 'B' ? 'C' : 'B')));
                answerPair = { user: u, correct: c };
            }

            var optionCirclesHtml = '';
            optionsList.forEach(function(opt) {
                var cls = 'ct-result-opt-circle--neutral';
                if (opt === answerPair.user) {
                    cls = 'ct-result-opt-circle--user-wrong';
                } else if (opt === answerPair.correct) {
                    cls = 'ct-result-opt-circle--correct';
                }
                optionCirclesHtml += `<span class="ct-result-opt-circle ${cls}">${opt}</span>`;
            });

            // Chi tiết giải thích mẫu
            var detailContentHtml = getQuestionDetailHtml(partNum, i, answerPair, optionsList);

            html += `
                <div class="ct-result-q-item" id="ctQItem_${i}">
                    <div class="ct-result-q-row" onclick="toggleResultQuestionDetail(${i})">
                        <div class="ct-result-q-label">Câu ${i}</div>
                        <div class="ct-result-q-controls">
                            <span class="ct-result-status-icon ct-result-status-icon--incorrect" title="Câu sai">&times;</span>
                            ${optionCirclesHtml}
                            <span class="ct-result-chevron" id="ctQChevron_${i}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="ct-result-q-detail" id="ctQDetail_${i}">
                        ${detailContentHtml}
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    /**
     * Dữ liệu giải thích chi tiết mẫu mô phỏng cho từng câu hỏi
     */
    function getQuestionDetailHtml(partNum, qNum, pair, optionsList) {
        var optTexts = {
            'A': 'They are walking along the hallway in the office.',
            'B': 'A woman is giving a formal presentation to the team.',
            'C': 'Some documents are scattered across the large conference table.',
            'D': 'Several colleagues are gathered around a computer screen.'
        };

        if (partNum === 5) {
            optTexts = {
                'A': 'frequently',
                'B': 'frequency',
                'C': 'frequent',
                'D': 'frequenting'
            };
        }

        var optListHtml = '';
        optionsList.forEach(function(opt) {
            var isCorrect = (opt === pair.correct);
            var isUserWrong = (opt === pair.user);
            var itemCls = isCorrect ? 'is-correct' : (isUserWrong ? 'is-user-wrong' : '');
            var badgeHtml = isCorrect 
                ? '<span class="ct-result-opt-badge ct-result-opt-badge--correct">Đáp án đúng</span>' 
                : (isUserWrong ? '<span class="ct-result-opt-badge ct-result-opt-badge--wrong">Bạn chọn</span>' : '');

            optListHtml += `
                <div class="ct-result-opt-item ${itemCls}">
                    <strong>${opt}.</strong>
                    <span style="flex: 1;">${optTexts[opt] || 'Nội dung phương án ' + opt}</span>
                    ${badgeHtml}
                </div>
            `;
        });

        var stemText = partNum <= 4 
            ? `[Câu hỏi Listening Part ${partNum} - Câu ${qNum}] Nghe đoạn ghi âm và chọn câu trả lời chính xác nhất.`
            : `[Câu hỏi Reading Part ${partNum} - Câu ${qNum}] The board of directors agreed that the quarterly reports should be reviewed ________ before the shareholders meeting.`;

        var explanation = partNum <= 4
            ? `Phương án (${pair.correct}) mô tả hoàn toàn chuẩn xác hành động và bối cảnh chính được đề cập. Phương án bạn chọn (${pair.user}) bị sai do từ khóa đồng âm gây bẫy trong bài nghe.`
            : `Vị trí trống đứng sau động từ ở thể bị động 'be reviewed' và bổ nghĩa cho hành động xem xét, nên cần một trạng từ (adverb). Do đó chọn đáp án (${pair.correct}).`;

        var transcriptHtml = '';
        if (partNum <= 4) {
            transcriptHtml = `
                <div class="ct-result-transcript">
                    <strong>🎧 Audio Transcript:</strong><br>
                    (A) ${optTexts['A'] || ''}<br>
                    (B) ${optTexts['B'] || ''}<br>
                    (C) ${optTexts['C'] || ''}<br>
                    ${!optionsList.includes('D') ? '' : '(D) ' + (optTexts['D'] || '')}
                    <div style="margin-top: 6px; color: #64748B;">
                        <strong>Dịch nghĩa:</strong> Bức ảnh chụp các đồng nghiệp đang tập trung làm việc và thảo luận công việc cùng nhau.
                    </div>
                </div>
            `;
        }

        return `
            <div class="ct-result-q-stem">${stemText}</div>
            <div class="ct-result-opt-list">${optListHtml}</div>
            <div class="ct-result-explanation">
                <strong>💡 Giải thích chi tiết:</strong> ${explanation}
            </div>
            ${transcriptHtml}
        `;
    }

    /**
     * Chuyển đổi tab giữa "Câu sai" và "Điểm yếu"
     */
    function switchImproveTab(tab) {
        var btnWrong = document.getElementById('tab-btn-wrong');
        var btnWeakness = document.getElementById('tab-btn-weakness');
        var viewWrong = document.getElementById('view-wrong');
        var viewWeakness = document.getElementById('view-weakness');

        if (tab === 'wrong') {
            btnWrong.classList.add('is-active');
            btnWeakness.classList.remove('is-active');
            viewWrong.style.display = 'block';
            viewWeakness.style.display = 'none';
        } else {
            btnWeakness.classList.add('is-active');
            btnWrong.classList.remove('is-active');
            viewWeakness.style.display = 'block';
            viewWrong.style.display = 'none';
        }
    }

    /**
     * Lọc danh sách điểm yếu (Tất cả 11 / Nghe 6 / Đọc 5)
     */
    function filterWeakness(skill, btn) {
        document.querySelectorAll('.ct-filter-pill').forEach(function(el) { el.classList.remove('is-active'); });
        if (btn) btn.classList.add('is-active');

        var priorityWrap = document.getElementById('ctWeaknessPriorityWrap');
        var groupedCard = document.getElementById('ctWeaknessGroupedCard');
        var topLabel = document.getElementById('ctWeaknessTopLabel');
        var rows = document.querySelectorAll('.ct-weakness-row');

        if (skill === 'all') {
            priorityWrap.style.display = 'flex';
            groupedCard.style.display = 'block';
            rows.forEach(function(r) { r.style.display = 'flex'; });
            if (topLabel) topLabel.textContent = 'TOP 11';
        } else if (skill === 'listen') {
            priorityWrap.style.display = 'flex';
            groupedCard.style.display = 'block';
            rows.forEach(function(r) {
                if (r.getAttribute('data-skill') === 'listen') {
                    r.style.display = 'flex';
                } else {
                    r.style.display = 'none';
                }
            });
            if (topLabel) topLabel.textContent = 'TOP 6';
        } else if (skill === 'read') {
            priorityWrap.style.display = 'none';
            groupedCard.style.display = 'block';
            rows.forEach(function(r) {
                if (r.getAttribute('data-skill') === 'read') {
                    r.style.display = 'flex';
                } else {
                    r.style.display = 'none';
                }
            });
            if (topLabel) topLabel.textContent = 'TOP 5';
        }
    }

    /**
     * Modal tìm hiểu thêm (Tái sử dụng x-modal)
     */
    function openLearnMoreModal() {
        var modal = document.getElementById('ct-info-modal');
        if (modal) {
            if (window.spModal && typeof window.spModal.open === 'function') {
                window.spModal.open(modal);
            } else {
                modal.hidden = false;
            }
        }
    }

    function closeLearnMoreModal() {
        var modal = document.getElementById('ct-info-modal');
        if (modal) {
            if (window.spModal && typeof window.spModal.close === 'function') {
                window.spModal.close(modal);
            } else {
                modal.hidden = true;
            }
        }
    }

    /**
     * Quản lý chuyển đổi chế độ kiểm tra dữ liệu mẫu
     */
    function setDemoState(mode, btn) {
        document.querySelectorAll('.ct-demo-toggle-btn').forEach(function(b) { b.classList.remove('is-active'); });
        if (btn) btn.classList.add('is-active');

        var wrongData = document.getElementById('ctWrongDataContainer');
        var wrongEmpty = document.getElementById('ctWrongEmptyState');
        var weaknessData = document.getElementById('ctWeaknessDataContainer');
        var weaknessMeta = document.getElementById('ctWeaknessMetaHeader');
        var weaknessEmpty = document.getElementById('ctWeaknessEmptyState');

        var statWrongCount = document.getElementById('statWrongCount');
        var statWrongUnit = document.getElementById('statWrongUnit');
        var statWeaknessName = document.getElementById('statWeaknessName');
        var statWeaknessBadge = document.getElementById('statWeaknessBadge');
        var statWeaknessIconWrap = document.getElementById('statWeaknessIconWrap');

        if (mode === 'full') {
            wrongData.style.display = 'block';
            wrongEmpty.style.display = 'none';
            weaknessData.style.display = 'block';
            weaknessMeta.style.display = 'flex';
            weaknessEmpty.style.display = 'none';

            statWrongCount.textContent = '150';
            statWrongUnit.textContent = 'câu';
            statWeaknessName.textContent = 'Part 4';
            statWeaknessBadge.textContent = 'Độ thành thạo 18%';
            statWeaknessBadge.className = 'ct-stat-badge';
            statWeaknessIconWrap.className = 'ct-stat-icon-wrap ct-stat-icon-wrap--red';
        } else if (mode === 'empty_wrong') {
            wrongData.style.display = 'none';
            wrongEmpty.style.display = 'block';
            weaknessData.style.display = 'block';
            weaknessMeta.style.display = 'flex';
            weaknessEmpty.style.display = 'none';

            statWrongCount.textContent = '0';
            statWrongUnit.textContent = 'câu';
            statWeaknessName.textContent = 'Part 4';
            statWeaknessBadge.textContent = 'Độ thành thạo 18%';
            statWeaknessBadge.className = 'ct-stat-badge';
        } else if (mode === 'empty_weakness') {
            wrongData.style.display = 'block';
            wrongEmpty.style.display = 'none';
            weaknessData.style.display = 'none';
            weaknessMeta.style.display = 'none';
            weaknessEmpty.style.display = 'block';

            statWrongCount.textContent = '150';
            statWrongUnit.textContent = 'câu';
            statWeaknessName.textContent = '--';
            statWeaknessBadge.textContent = 'Chưa có đánh giá';
            statWeaknessBadge.className = 'ct-stat-badge ct-stat-badge--neutral';
        } else if (mode === 'all_empty') {
            wrongData.style.display = 'none';
            wrongEmpty.style.display = 'block';
            weaknessData.style.display = 'none';
            weaknessMeta.style.display = 'none';
            weaknessEmpty.style.display = 'block';

            statWrongCount.textContent = '0';
            statWrongUnit.textContent = 'câu';
            statWeaknessName.textContent = '--';
            statWeaknessBadge.textContent = 'Chưa có đánh giá';
            statWeaknessBadge.className = 'ct-stat-badge ct-stat-badge--neutral';
        }
    }

    // Đọc URL param khởi tạo ban đầu (?tab=...&state=...&result_part=...)
    window.addEventListener('DOMContentLoaded', function() {
        var params = new URLSearchParams(window.location.search);
        var tabParam = params.get('tab');
        if (tabParam === 'weakness') {
            switchImproveTab('weakness');
        } else {
            switchImproveTab('wrong');
        }

        var stateParam = params.get('state');
        if (stateParam) {
            var btn = document.querySelector('.ct-demo-toggle-btn[data-mode="' + stateParam + '"]');
            if (btn) setDemoState(stateParam, btn);
        }

        // Tự động mở View Kết quả nếu có param ?result_part=...
        var resultPartParam = params.get('result_part');
        if (resultPartParam) {
            var pNum = parseInt(resultPartParam, 10);
            if (!isNaN(pNum) && pNum >= 1 && pNum <= 7) {
                openPartResult(pNum);
            }
        }
    });

    window.addEventListener('popstate', function(e) {
        var params = new URLSearchParams(window.location.search);
        var resultPartParam = params.get('result_part');
        if (resultPartParam) {
            openPartResult(parseInt(resultPartParam, 10));
        } else {
            closePartResult();
        }
    });
</script>
@endpush

</x-layouts.app>

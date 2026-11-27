<x-layouts.app active="lich-su" title="StudyPrep - Lịch sử">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/lich-su.css') }}">
@endpush

{{-- ===== TIÊU ĐỀ TRANG & THANH CÔNG CỤ (Đồng bộ chuẩn hệ thống StudyPrep) ===== --}}
<div class="ls-header-toolbar">
    <h1 class="ls-page-title">Lịch sử</h1>

    <div class="ls-top-actions">
        {{-- Ô tìm kiếm --}}
        <form method="GET" action="{{ route('lich-su') }}" class="ls-search-wrap" id="lsSearchForm">
            <input type="hidden" name="range" value="{{ $range }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="part" value="{{ $selectedPart }}">
            <input type="hidden" name="sort" value="{{ $sort }}">

            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text"
                   class="ls-search-input"
                   id="lsSearchInput"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Tìm kiếm bài tập, Part..."
                   autocomplete="off">
        </form>

        {{-- Nút chọn thời gian & Dropdown --}}
        <div class="ls-date-dropdown-wrap">
            <button type="button" class="ls-date-btn" id="lsDateBtn" onclick="toggleDateDropdown(event)" aria-haspopup="true" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span id="lsDateText">{{ $rangeLabel }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div class="ls-date-dropdown" id="lsDateDropdown" style="display: none;">
                @php
                    $rangeOptions = [
                        'today' => 'Hôm nay',
                        '7days' => '7 ngày qua',
                        '30days' => '30 ngày qua',
                        '90days' => '90 ngày qua',
                        'all' => 'Tất cả thời gian',
                    ];
                @endphp
                @foreach ($rangeOptions as $rKey => $rLabel)
                    <a href="{{ request()->fullUrlWithQuery(['range' => $rKey, 'page' => 1]) }}"
                       class="ls-date-option {{ $range === $rKey ? 'is-selected' : '' }}">
                        {{ $rLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Nút xuất báo cáo --}}
        <x-button variant="primary" size="small" type="button" onclick="exportReport()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <span>Xuất báo cáo</span>
        </x-button>
    </div>
</div>

{{-- ===== 4 THẺ SỐ LIỆU TỔNG HỢP ===== --}}
<div class="ls-stats-grid">
    {{-- Thẻ 1: Hoạt động ghi nhận --}}
    <div class="ls-stat-card">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Hoạt động ghi nhận</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number">{{ $stats['activity_count'] }}</span>
                <span class="ls-stat-unit">lượt làm bài</span>
            </div>
        </div>
        <div class="ls-stat-icon-wrap ls-stat-icon-wrap--blue">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
    </div>

    {{-- Thẻ 2: Tỉ lệ đúng TB --}}
    <div class="ls-stat-card">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Tỉ lệ đúng TB</p>
            <div class="ls-stat-value-wrap">
                @if ($stats['accuracy_percent'] !== null)
                    <span class="ls-stat-number ls-stat-number--primary">{{ $stats['accuracy_percent'] }}%</span>
                    @if ($stats['accuracy_delta'] !== null)
                        <span class="ls-stat-badge {{ $stats['accuracy_delta_direction'] === 'down' ? 'ls-stat-badge--down' : '' }}">
                            {{ $stats['accuracy_delta_direction'] === 'down' ? '↓' : '↑' }} {{ ($stats['accuracy_delta'] > 0 ? '+' : '') . $stats['accuracy_delta'] }}%
                        </span>
                    @endif
                @else
                    <span class="ls-stat-number ls-stat-number--primary" style="font-size: 16px; font-weight: 700;">Chưa có dữ liệu</span>
                @endif
            </div>
        </div>
        <div class="ls-stat-icon-wrap ls-stat-icon-wrap--primary">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 14 14"></polyline>
            </svg>
        </div>
    </div>

    {{-- Thẻ 3: Thời gian học tập --}}
    <div class="ls-stat-card">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Thời gian học tập</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number">{{ $stats['study_time_text'] }}</span>
            </div>
        </div>
        <div class="ls-stat-icon-wrap ls-stat-icon-wrap--amber">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 15 15"></polyline>
            </svg>
        </div>
    </div>

    {{-- Thẻ 4: Câu sai tích lũy --}}
    <div class="ls-stat-card ls-stat-card--danger {{ ! $stats['can_review_weak_points'] ? 'ls-stat-card--no-action' : '' }}">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Câu sai tích lũy</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number ls-stat-number--danger">{{ $stats['accumulated_wrong_count'] }}</span>
                <span class="ls-stat-unit">câu</span>
            </div>
        </div>
        @if ($stats['can_review_weak_points'])
            <x-button variant="danger" size="small" type="button" onclick="reviewWeakPoints()">
                Ôn ngay
            </x-button>
        @endif
    </div>
</div>

{{-- ===== THANH LỌC THEO LOẠI BÀI & SẮP XẾP ===== --}}
<div class="ls-filter-sort-row">
    <div class="ls-filter-pills" role="tablist">
        <a href="{{ request()->fullUrlWithQuery(['type' => 'all', 'page' => 1]) }}"
           class="ls-pill-item {{ $type === 'all' ? 'is-active' : '' }}">
            <span>Tất cả</span>
            <span class="ls-pill-badge">{{ $typeCounts['all'] }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['type' => 'lesson', 'page' => 1]) }}"
           class="ls-pill-item {{ $type === 'lesson' ? 'is-active' : '' }}">
            <span>Bài học</span>
            <span class="ls-pill-badge">{{ $typeCounts['lesson'] }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['type' => 'exam', 'page' => 1]) }}"
           class="ls-pill-item {{ $type === 'exam' ? 'is-active' : '' }}">
            <span>Luyện đề</span>
            <span class="ls-pill-badge">{{ $typeCounts['exam'] }}</span>
        </a>

        <select class="ls-filter-select-pill" id="lsPartFilter" onchange="filterByPart(this.value)" aria-label="Lọc theo Part">
            <option value="all" {{ $selectedPart === 'all' ? 'selected' : '' }}>Part (Tất cả)</option>
            @foreach ($partList as $partItem)
                <option value="{{ $partItem['code'] }}" {{ $selectedPart === $partItem['code'] ? 'selected' : '' }}>
                    {{ $partItem['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 13.5px; color: #64748B; font-weight: 600;">Sắp xếp:</span>
        <select class="ls-filter-select-pill" style="border: none; padding: 4px 8px; font-weight: 700; color: #1E293B;" onchange="sortActivities(this.value)">
            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất trước</option>
            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Cũ nhất trước</option>
            <option value="score_desc" {{ $sort === 'score_desc' ? 'selected' : '' }}>Điểm cao nhất</option>
            <option value="score_asc" {{ $sort === 'score_asc' ? 'selected' : '' }}>Điểm thấp nhất</option>
        </select>
    </div>
</div>

{{-- Dòng mô tả số lượng --}}
<p class="ls-count-subtitle" id="lsCountSubtitle">Hiển thị {{ $displayedCount }} trong {{ $totalCount }} hoạt động</p>

{{-- ===== DANH SÁCH BÀI ĐÃ LÀM (TIMELINE) ===== --}}
<div id="lsActivitiesContainer">

    @if ($totalCount === 0)
        @if ($hasAnyHistory)
            {{-- Trạng thái không tìm thấy kết quả do bộ lọc / tìm kiếm --}}
            <div class="ls-empty-state-box">
                <div class="ls-empty-state-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h3 class="ls-empty-state-title">Không tìm thấy bài làm phù hợp</h3>
                <p class="ls-empty-state-desc">Không có kết quả nào khớp với điều kiện tìm kiếm hoặc bộ lọc hiện tại của bạn.</p>
                <div class="ls-empty-state-actions">
                    <x-button variant="neutral" size="medium" href="{{ route('lich-su') }}">
                        Xóa bộ lọc
                    </x-button>
                </div>
            </div>
        @else
            {{-- Trạng thái CHƯA CÓ LỊCH SỬ LÀM BÀI (Empty State đồng bộ chuẩn trang Cải thiện) --}}
            <div class="ls-empty-state-box">
                <div class="ls-empty-state-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h3 class="ls-empty-state-title">Chưa có lịch sử làm bài</h3>
                <p class="ls-empty-state-desc">Hãy luyện tập hoặc làm đề thi thử để bắt đầu theo dõi quá trình học tập của bạn.</p>
                <div class="ls-empty-state-actions">
                    <x-button variant="primary" size="medium" href="{{ url('/luyen-tap') }}">
                        Luyện tập
                    </x-button>
                    <x-button variant="neutral" size="medium" href="{{ url('/thi-thu') }}">
                        Thi thử
                    </x-button>
                </div>
            </div>
        @endif
    @else
        {{-- Danh sách các nhóm ngày --}}
        @foreach ($groupedHistory as $groupKey => $group)
            <div class="ls-timeline-group" data-group="{{ $groupKey }}">
                <div class="ls-group-header">
                    <svg class="ls-group-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span class="ls-group-title">{{ $group['label'] }}</span>
                    <div class="ls-group-line"></div>
                </div>

                @foreach ($group['items'] as $item)
                    <div class="ls-activity-card"
                         data-attempt-id="{{ $item['attempt_id'] }}"
                         data-part="{{ strtolower($item['part_code'] ?? 'part5') }}"
                         data-type="{{ $item['type'] }}"
                         data-title="{{ $item['title'] }}">
                        <div class="ls-activity-left">
                            <div class="ls-activity-icon {{ $item['icon_class'] }}">
                                @if (in_array(strtolower($item['part_code'] ?? ''), ['part1', 'part2'], true))
                                    {{-- Icon Tai nghe / Hình ảnh --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                    </svg>
                                @elseif (strtolower($item['part_code'] ?? '') === 'part3')
                                    {{-- Icon Bong bóng hội thoại --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                @elseif (strtolower($item['part_code'] ?? '') === 'part4')
                                    {{-- Icon Bài nói chuyện ngắn --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="7" r="4"></circle>
                                        <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"></path>
                                    </svg>
                                @elseif (strtolower($item['part_code'] ?? '') === 'part6')
                                    {{-- Icon Hoàn thành đoạn văn --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                @elseif (strtolower($item['part_code'] ?? '') === 'part7')
                                    {{-- Icon Đọc hiểu văn bản --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    </svg>
                                @elseif ($item['is_exam'])
                                    {{-- Icon Đề thi thử --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    </svg>
                                @else
                                    {{-- Icon Part 5 (Hoàn thành câu / Mặc định) --}}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 11l3 3L22 4"></path>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                    </svg>
                                @endif
                            </div>

                            <div class="ls-activity-details">
                                <div class="ls-activity-title-row">
                                    <h3 class="ls-activity-title">{{ $item['title'] }}</h3>
                                    @if (! empty($item['mode_label']))
                                        <span class="ls-badge-mode">{{ $item['mode_label'] }}</span>
                                    @endif
                                    @if ($item['incorrect_count'] > 0)
                                        <span class="ls-badge-wrong">{{ $item['incorrect_count'] }} câu sai</span>
                                    @endif
                                </div>
                                <p class="ls-activity-submeta">{{ $item['submeta_text'] }}</p>
                                <p class="ls-activity-timestamp-row">
                                    <span>{{ $item['time_formatted'] }}</span>
                                    <span class="{{ $item['delta']['class'] }}">{{ $item['delta']['text'] }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="ls-activity-right">
                            <div class="ls-rate-box">
                                <span class="ls-rate-label">{{ $item['score_label'] }}</span>
                                <span class="ls-rate-value">{{ $item['score_text'] }}</span>
                            </div>
                            <div class="ls-action-buttons">
                                <x-button variant="neutral" size="small" href="{{ $item['result_url'] }}">
                                    Xem kết quả
                                </x-button>
                                <x-button variant="primary" size="small" href="{{ $item['retry_url'] }}">
                                    Ôn lại
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

</div>

{{-- ===== PHÂN TRANG (PAGINATION) ===== --}}
@if ($paginator->hasPages())
    <div class="ls-pagination">
        {{-- Nút Trước --}}
        @if ($paginator->onFirstPage())
            <span class="ls-page-btn is-disabled">&lsaquo; Trước</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="ls-page-btn">&lsaquo; Trước</a>
        @endif

        {{-- Các nút số trang --}}
        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $pageNumber => $pageUrl)
            @if ($pageNumber == $paginator->currentPage())
                <span class="ls-page-btn is-active">{{ $pageNumber }}</span>
            @else
                <a href="{{ $pageUrl }}" class="ls-page-btn">{{ $pageNumber }}</a>
            @endif
        @endforeach

        {{-- Nút Sau --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="ls-page-btn">Sau &rsaquo;</a>
        @else
            <span class="ls-page-btn is-disabled">Sau &rsaquo;</span>
        @endif
    </div>
@endif

@push('scripts')
<script>
    // Xử lý bật/tắt dropdown chọn thời gian
    function toggleDateDropdown(e) {
        e.stopPropagation();
        const dd = document.getElementById('lsDateDropdown');
        const isOpen = dd.style.display !== 'none';
        dd.style.display = isOpen ? 'none' : 'block';
        document.getElementById('lsDateBtn').setAttribute('aria-expanded', !isOpen);
    }

    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('lsDateDropdown');
        if (dd && !dd.contains(e.target) && e.target.id !== 'lsDateBtn') {
            dd.style.display = 'none';
            document.getElementById('lsDateBtn').setAttribute('aria-expanded', 'false');
        }
    });

    // Lọc theo Part
    function filterByPart(partValue) {
        const url = new URL(window.location.href);
        url.searchParams.set('part', partValue);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Sắp xếp
    function sortActivities(sortValue) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortValue);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Dieu huong sang trang Cai thien tab Cau sai
    function reviewWeakPoints() {
        window.location.href = '{{ url('/cai-thien?tab=cau-sai') }}';
    }

    // Thông báo chức năng Xuất báo cáo đang phát triển
    function exportReport() {
        alert('📊 Chức năng Xuất báo cáo đang được hoàn thiện và sẽ sẵn sàng trong bản cập nhật tới.');
    }
</script>
@endpush

</x-layouts.app>

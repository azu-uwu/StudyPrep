<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện Đọc Câu (Read Aloud)">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/read-aloud.css') }}">
@endpush

{{-- Khung bố cục 2 cột: Thẻ nội dung chính bên trái & Widget tải ứng dụng bên phải --}}
<div class="ra-page-layout">

    {{-- Card Container bám sát 100% hình ảnh tham chiếu (Ảnh 1) --}}
    <div class="ra-card-container">

        {{-- 1. Header: Nút quay lại & Tiêu đề "Read Aloud" với icon sách mở --}}
        <div class="ra-header">
            <a href="{{ url('/luyen-tap/noi') }}" class="ra-back-btn" title="Quay lại luyện kỹ năng Nói" aria-label="Quay lại">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <div class="ra-header-title-wrap">
                <svg class="ra-header-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="#0076FE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" stroke="#0076FE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 7h8M8 11h5" stroke="#0076FE" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <h1 class="ra-header-title">Read Aloud</h1>
            </div>
        </div>

        {{-- 2. Thanh Tabs: Đọc Câu (Active) vs Đọc Đoạn --}}
        <nav class="ra-tabs-nav" aria-label="Hình thức luyện tập">
            <a href="{{ url('/luyen-tap/doc-cau') }}" class="ra-tab-link is-active" aria-current="page">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <span>Đọc Câu</span>
            </a>
            <a href="{{ url('/luyen-tap/doc-doan') }}" class="ra-tab-link">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Đọc Đoạn</span>
            </a>
        </nav>

        {{-- 3. Category Pills: Tất cả, 200 Basic Words, TOEIC Speaking --}}
        <div class="ra-category-pills" role="tablist">
            <button type="button" class="ra-pill-btn is-active" data-filter="all" onclick="selectCategory('all', this)">Tất cả</button>
            <button type="button" class="ra-pill-btn" data-filter="basic" onclick="selectCategory('basic', this)">200 Basic Words</button>
            <button type="button" class="ra-pill-btn" data-filter="toeic" onclick="selectCategory('toeic', this)">TOEIC Speaking</button>
        </div>

        {{-- 4. Filter & Counter bar: Số bài học, Độ khó, Tiến độ, Bookmark --}}
        <div class="ra-filter-bar">
            <div class="ra-count-info">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span id="raSentenceCount">208 Bài học</span>
            </div>
            <div class="ra-filter-actions">
                <select class="ra-filter-select" id="raDifficultyFilter" onchange="applyFilters()" aria-label="Lọc theo độ khó">
                    <option value="all">Độ khó</option>
                    <option value="easy">Dễ</option>
                    <option value="medium">Trung bình</option>
                    <option value="hard">Khó</option>
                </select>
                <select class="ra-filter-select" id="raProgressFilter" onchange="applyFilters()" aria-label="Lọc theo tiến độ">
                    <option value="all">Tiến độ</option>
                    <option value="completed">Đã hoàn thành</option>
                    <option value="uncompleted">Chưa học</option>
                </select>
                <select class="ra-filter-select" id="raBookmarkFilter" onchange="applyFilters()" aria-label="Lọc theo bookmark">
                    <option value="all">Bookmark</option>
                    <option value="bookmarked">Đã lưu</option>
                    <option value="unbookmarked">Chưa lưu</option>
                </select>
            </div>
        </div>

        {{-- 5. Danh sách bài đọc câu (Khớp chuẩn cấu trúc Ảnh 1) --}}
        @php
            $sentences = [
                [
                    'id' => 1,
                    'title' => '1. If you have a good job, think smart.',
                    'full_text' => 'If you have a good job, think smart.',
                    'diff' => 'easy',
                    'diff_label' => 'Dễ',
                    'category' => 'basic'
                ],
                [
                    'id' => 2,
                    'title' => '2. Our affordable loans can start repairing your credit right away.',
                    'full_text' => 'Our affordable loans can start repairing your credit score and rebuilding your financial stability immediately.',
                    'diff' => 'medium',
                    'diff_label' => 'Trung bình',
                    'category' => 'toeic'
                ],
                [
                    'id' => 3,
                    'title' => '3. Say goodbye to cash advances on your redit card and taking out a bank loan with their extremely hi...',
                    'full_text' => 'Say goodbye to cash advances on your credit card and taking out a bank loan with their extremely high interest rates.',
                    'diff' => 'hard',
                    'diff_label' => 'Khó',
                    'category' => 'toeic'
                ],
                [
                    'id' => 4,
                    'title' => '4. If you own a car that is less than three years old, you qualify instantly.',
                    'full_text' => 'If you own a car that is less than three years old, you qualify instantly.',
                    'diff' => 'medium',
                    'diff_label' => 'Trung bình',
                    'category' => 'toeic'
                ],
                [
                    'id' => 5,
                    'title' => '5. Visit our website at www.wise-financial.com.',
                    'full_text' => 'Visit our website at www.wise-financial.com.',
                    'diff' => 'easy',
                    'diff_label' => 'Dễ',
                    'category' => 'basic'
                ],
                [
                    'id' => 6,
                    'title' => '6. Making your own bread at home is easy with the new Double Duty Dough Mixer by Berring.',
                    'full_text' => 'Making your own bread at home is easy with the new Double Duty Dough Mixer by Berring.',
                    'diff' => 'easy',
                    'diff_label' => 'Dễ',
                    'category' => 'basic'
                ],
                [
                    'id' => 7,
                    'title' => '7. Unlike other bread machines that can be difficult to clean and store, the Double Duty Dough M...',
                    'full_text' => 'Unlike other bread machines that can be difficult to clean and store, the Double Duty Dough Mixer is compact and dishwasher safe.',
                    'diff' => 'medium',
                    'diff_label' => 'Trung bình',
                    'category' => 'toeic'
                ],
                [
                    'id' => 8,
                    'title' => '8. This stainless steel appliance will mix dough for you in a fraction of the time it takes to knead ...',
                    'full_text' => 'This stainless steel appliance will mix dough for you in a fraction of the time it takes to knead by hand.',
                    'diff' => 'medium',
                    'diff_label' => 'Trung bình',
                    'category' => 'toeic'
                ],
                [
                    'id' => 9,
                    'title' => '9. The automated delay feature at the beginning of the mix cycle gives your ingredients time to reach ...',
                    'full_text' => 'The automated delay feature at the beginning of the mix cycle gives your ingredients time to reach room temperature.',
                    'diff' => 'hard',
                    'diff_label' => 'Khó',
                    'category' => 'toeic'
                ],
                [
                    'id' => 10,
                    'title' => '10. We guarantee that the accompanying Berring Best Breads recipe book will be a family favou...',
                    'full_text' => 'We guarantee that the accompanying Berring Best Breads recipe book will be a family favourite.',
                    'diff' => 'medium',
                    'diff_label' => 'Trung bình',
                    'category' => 'toeic'
                ],
                [
                    'id' => 11,
                    'title' => '11. This is Director Gabriel Henderson and I would like to welcome everyone to the department’...',
                    'full_text' => 'This is Director Gabriel Henderson and I would like to welcome everyone to the department’s annual meeting.',
                    'diff' => 'medium',
                    'diff_label' => 'Trung bình',
                    'category' => 'toeic'
                ],
                [
                    'id' => 12,
                    'title' => '12. We have prepared barbecued beef, drinks and ice cream as well as a lot of fun activities.',
                    'full_text' => 'We have prepared barbecued beef, drinks and ice cream as well as a lot of fun activities.',
                    'diff' => 'easy',
                    'diff_label' => 'Dễ',
                    'category' => 'basic'
                ],
                [
                    'id' => 13,
                    'title' => '13. Your children can play on the playground equipped with swings, slides, and games.',
                    'full_text' => 'Your children can play on the playground equipped with swings, slides, and games.',
                    'diff' => 'easy',
                    'diff_label' => 'Dễ',
                    'category' => 'basic'
                ],
            ];
        @endphp

        <div class="ra-item-list" id="raSentenceList">
            @forelse($sentences as $item)
                <div class="ra-item-row"
                     data-id="{{ $item['id'] }}"
                     data-diff="{{ $item['diff'] }}"
                     data-category="{{ $item['category'] }}"
                     onclick="goToPractice({{ $item['id'] }})"
                     role="button"
                     tabindex="0">
                    <div class="ra-item-left">
                        <div class="ra-circle-icon" id="circle-check-{{ $item['id'] }}" title="Trạng thái hoàn thành"></div>
                        <p class="ra-item-title" title="{{ $item['full_text'] }}">{{ $item['title'] }}</p>
                        <span class="ra-diff-badge ra-diff-badge--{{ $item['diff'] }}">{{ $item['diff_label'] }}</span>
                    </div>
                    <div class="ra-item-right">
                        <span class="ra-score-tag" id="practice-count-{{ $item['id'] }}">#0</span>
                        <button type="button" class="ra-bookmark-btn" id="bookmark-btn-{{ $item['id'] }}" onclick="toggleBookmarkRow(event, {{ $item['id'] }})" title="Lưu bài học" aria-label="Lưu bài học">
                            <svg class="ra-bookmark-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </button>
                        <svg class="ra-chevron-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            @empty
                {{-- Trạng thái rỗng khi backend trả về danh sách trống --}}
                <div class="ra-empty-state">
                    <svg class="ra-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        <line x1="9" y1="9" x2="15" y2="9"/>
                        <line x1="9" y1="13" x2="13" y2="13"/>
                    </svg>
                    <h3 class="ra-empty-title">Chưa có bài học nào</h3>
                    <p class="ra-empty-desc">Dữ liệu bài tập Đọc Câu đang chờ quản trị viên xuất bản từ Admin. Vui lòng quay lại sau!</p>
                </div>
            @endforelse
        </div>

        {{-- Trạng thái rỗng khi lọc không khớp kết quả --}}
        <div class="ra-empty-state" id="raFilterEmpty" style="display: none;">
            <svg class="ra-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3 class="ra-empty-title">Không tìm thấy bài học phù hợp</h3>
            <p class="ra-empty-desc">Không có bài đọc câu nào khớp với bộ lọc bạn đã chọn. Vui lòng thử đổi tiêu chí lọc.</p>
        </div>

    </div>

    {{-- Widget bên phải: "Học trên Điện thoại di động" (Ảnh 1 & Ảnh 3) --}}
    <aside class="ra-sidebar-promo">
        <h2 class="ra-promo-title">Học trên Điện<br>thoại di động</h2>
        <div class="ra-promo-mockup-wrap">
            <svg viewBox="0 0 140 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Vỏ điện thoại -->
                <rect x="25" y="10" width="90" height="160" rx="18" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="3"/>
                <!-- Màn hình -->
                <rect x="31" y="20" width="78" height="140" rx="12" fill="#FFFFFF"/>
                <!-- Notch -->
                <rect x="52" y="14" width="36" height="4" rx="2" fill="#94A3B8"/>
                <!-- Icon app StudyPrep trên màn hình -->
                <rect x="48" y="55" width="44" height="44" rx="12" fill="#0076FE"/>
                <!-- Logo mũ cử nhân trắng -->
                <path d="M70 65l14 7-14 7-14-7 14-7z" fill="#FFFFFF"/>
                <path d="M62 76v7c0 3 3.6 5 8 5s8-2 8-5v-7" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>
                <!-- Nút bấm demo trong app -->
                <rect x="42" y="115" width="56" height="12" rx="6" fill="#E2E8F0"/>
                <rect x="50" y="132" width="40" height="8" rx="4" fill="#F1F5F9"/>
            </svg>
        </div>
        <div class="ra-promo-actions">
            <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer" class="ra-promo-btn" title="Tải app từ App Store">
                <svg viewBox="0 0 24 24">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.84c.62-.75 1.04-1.8 0.92-2.84-.89.04-1.98.6-2.62 1.34-.56.64-.99 1.69-.87 2.7.99.08 2.02-.51 2.57-1.2z"/>
                </svg>
                <div class="ra-promo-btn-content">
                    <span class="ra-promo-btn-sub">Tải app từ</span>
                    <span class="ra-promo-btn-main">AppStore</span>
                </div>
            </a>
            <a href="https://play.google.com" target="_blank" rel="noopener noreferrer" class="ra-promo-btn" title="Tải app từ Google Play">
                <svg viewBox="0 0 24 24">
                    <path d="M3.609 1.814L13.793 12 3.61 22.186c-.37-.34-.61-.83-.61-1.38V3.194c0-.55.24-1.04.609-1.38zm11.3 9.07l2.454-2.454-9.986-5.74 7.532 8.194zm0 2.232l-7.532 8.194 9.986-5.74-2.454-2.454zm1.116-1.116l3.328 1.912c.795.457.795 1.207 0 1.664l-3.328 1.912-2.316-2.316 2.316-2.172z"/>
                </svg>
                <div class="ra-promo-btn-content">
                    <span class="ra-promo-btn-sub">Tải app từ</span>
                    <span class="ra-promo-btn-main">Google Play</span>
                </div>
            </a>
        </div>
    </aside>

</div>

@push('scripts')
<script>
    const STORAGE_BOOKMARKS_KEY = 'studyprep_sentence_bookmarks';
    const STORAGE_COMPLETED_KEY = 'studyprep_sentence_completed';
    const STORAGE_COUNTS_KEY = 'studyprep_sentence_practice_counts';

    let currentCategory = 'all';

    function getStoredBookmarks() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_BOOKMARKS_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function getStoredCompleted() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_COMPLETED_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function getStoredPracticeCounts() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_COUNTS_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function initPage() {
        const bookmarks = getStoredBookmarks();
        const completed = getStoredCompleted();
        const counts = getStoredPracticeCounts();

        // Cập nhật trạng thái từng dòng
        document.querySelectorAll('.ra-item-row').forEach(row => {
            const id = parseInt(row.getAttribute('data-id'), 10);

            // Bookmark icon
            const btn = document.getElementById('bookmark-btn-' + id);
            if (btn && bookmarks.includes(id)) {
                btn.classList.add('is-bookmarked');
            }

            // Completed circle check
            const circle = document.getElementById('circle-check-' + id);
            if (circle && completed.includes(id)) {
                circle.style.backgroundColor = '#0076FE';
                circle.style.borderColor = '#0076FE';
                circle.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" style="width:12px;height:12px;display:block;margin:2px auto;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }

            // Practice count
            const countEl = document.getElementById('practice-count-' + id);
            if (countEl) {
                const c = counts[id] || 0;
                countEl.textContent = '#' + c;
            }
        });

        applyFilters();
    }

    function toggleBookmarkRow(e, id) {
        e.stopPropagation();
        let bookmarks = getStoredBookmarks();
        const btn = document.getElementById('bookmark-btn-' + id);

        if (bookmarks.includes(id)) {
            bookmarks = bookmarks.filter(bId => bId !== id);
            if (btn) btn.classList.remove('is-bookmarked');
        } else {
            bookmarks.push(id);
            if (btn) btn.classList.add('is-bookmarked');
        }

        localStorage.setItem(STORAGE_BOOKMARKS_KEY, JSON.stringify(bookmarks));
        applyFilters();
    }

    function selectCategory(category, el) {
        currentCategory = category;
        document.querySelectorAll('.ra-category-pills .ra-pill-btn').forEach(btn => btn.classList.remove('is-active'));
        if (el) el.classList.add('is-active');
        applyFilters();
    }

    function applyFilters() {
        const diffFilter = document.getElementById('raDifficultyFilter').value;
        const progressFilter = document.getElementById('raProgressFilter').value;
        const bookmarkFilter = document.getElementById('raBookmarkFilter').value;

        const bookmarks = getStoredBookmarks();
        const completed = getStoredCompleted();

        let visibleCount = 0;
        const rows = document.querySelectorAll('.ra-item-row');

        rows.forEach(row => {
            const id = parseInt(row.getAttribute('data-id'), 10);
            const rowDiff = row.getAttribute('data-diff');
            const rowCategory = row.getAttribute('data-category');

            let matchCategory = (currentCategory === 'all' || rowCategory === currentCategory);
            let matchDiff = (diffFilter === 'all' || rowDiff === diffFilter);

            let isCompleted = completed.includes(id);
            let matchProgress = true;
            if (progressFilter === 'completed') matchProgress = isCompleted;
            if (progressFilter === 'uncompleted') matchProgress = !isCompleted;

            let isBookmarked = bookmarks.includes(id);
            let matchBookmark = true;
            if (bookmarkFilter === 'bookmarked') matchBookmark = isBookmarked;
            if (bookmarkFilter === 'unbookmarked') matchBookmark = !isBookmarked;

            if (matchCategory && matchDiff && matchProgress && matchBookmark) {
                row.style.display = 'flex';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countSpan = document.getElementById('raSentenceCount');
        if (countSpan) {
            countSpan.textContent = visibleCount + ' Bài học';
        }

        const filterEmpty = document.getElementById('raFilterEmpty');
        if (filterEmpty) {
            filterEmpty.style.display = (visibleCount === 0 && rows.length > 0) ? 'flex' : 'none';
        }
    }

    function goToPractice(id) {
        window.location.href = "{{ url('/luyen-tap/noi/phat-am') }}?type=sentence&id=" + id;
    }

    document.addEventListener('DOMContentLoaded', initPage);
</script>
@endpush

</x-layouts.app>

<x-layouts.app active="tu-vung" :title="'StudyPrep - ' . ($topic['name'] ?? 'Danh sách từ vựng')">

@push('styles')
    {{-- Kế thừa CSS chuẩn từ Luyện tập và Danh sách từ vựng --}}
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-tap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung-danh-sach.css') }}">
@endpush

<div class="tv-list-container">

    {{-- ===== 1. HEADER TRANG: QUAY LẠI & TIÊU ĐỀ ===== --}}
    <div class="tv-list-header">
        <a href="{{ route('tu-vung') }}" class="tv-list-back-btn" title="Quay lại Từ vựng" aria-label="Quay lại Từ vựng">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
        <h1 class="tv-list-title">Danh sách từ vựng</h1>
    </div>

    @if($topic)
        {{-- ===== 2. CARD THÔNG TIN CHỦ ĐỀ ===== --}}
        <div class="tv-topic-info-card">
            {{-- Thumbnail / Cover của chủ đề --}}
            <div class="tv-topic-thumb-wrap">
                @if(!empty($topic['thumbnail']))
                    <img src="{{ asset($topic['thumbnail']) }}"
                         alt="{{ $topic['name'] }}"
                         class="tv-topic-thumb-img"
                         loading="lazy">
                @else
                    {{-- Render Cover SVG chuẩn đẹp giống ảnh tham khảo cho Test 1 --}}
                    <div class="tv-topic-badge-icon">
                        <span class="tv-topic-badge-tag">{{ $topic['thumbnail_label'] ?? 'TEST 1 RC' }}</span>
                        {{-- Icon mũ tốt nghiệp & cuốn sách màu xanh --}}
                        <svg width="42" height="34" viewBox="0 0 44 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Cuốn sách nền dưới -->
                            <rect x="6" y="24" width="32" height="6" rx="2" fill="#0284C7" />
                            <rect x="8" y="20" width="28" height="4" rx="1.5" fill="#38BDF8" />
                            <!-- Mũ cử nhân -->
                            <path d="M22 6L40 14L22 22L4 14L22 6Z" fill="#0076FE" />
                            <path d="M12 18V25C12 28 22 30 22 30C22 30 32 28 32 25V18" stroke="#0053BF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M40 14V22" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" />
                            <circle cx="40" cy="23" r="1.5" fill="#F59E0B" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Thông tin tên chủ đề, số lượng từ và các mức CEFR --}}
            <div class="tv-topic-info-body">
                <h2 class="tv-topic-name">{{ $topic['name'] }}</h2>
                <p class="tv-topic-vocab-count">{{ $topic['total_vocabularies'] ?? count($vocabularies) }} từ vựng</p>

                {{-- Badges CEFR Level: A1, A2, B1, B2, C1, C2 --}}
                @if(!empty($topic['cefr_levels']))
                    @php
                        $cefrColors = [
                            'A1' => '#94A3B8', // Xám
                            'A2' => '#38BDF8', // Xanh da trời
                            'B1' => '#10B981', // Xanh lá
                            'B2' => '#EF4444', // Đỏ
                            'C1' => '#F59E0B', // Cam
                            'C2' => '#EAB308', // Vàng
                        ];
                    @endphp
                    <div class="tv-topic-cefr-row">
                        @foreach($topic['cefr_levels'] as $level)
                            <span class="tv-cefr-pill" style="background-color: {{ $cefrColors[$level] ?? '#64748B' }};">
                                {{ $level }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== 3. KHU VỰC TIẾN ĐỘ ===== --}}
        <section class="tv-progress-section" aria-labelledby="tvProgressHeading">
            <h3 class="tv-section-label" id="tvProgressHeading">Tiến độ</h3>

            <div class="tv-progress-card">
                <div class="tv-progress-rows">
                    {{-- Hàng 1: Hoàn thành --}}
                    @php
                        $completedPct = $topic['progress']['completed_pct'] ?? 0;
                        $masteryScore = $topic['progress']['mastery_score'] ?? 0.0;
                        $accuracyPct = $topic['progress']['accuracy_pct'] ?? 0;
                    @endphp
                    <div class="tv-progress-row">
                        <span class="tv-prow-label">Hoàn thành</span>
                        <div class="tv-prow-bar-wrap" role="progressbar" aria-valuenow="{{ $completedPct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="tv-prow-bar-fill" style="width: {{ $completedPct }}%;"></div>
                        </div>
                        <span class="tv-prow-value">{{ $completedPct }}%</span>
                    </div>

                    {{-- Hàng 2: Độ thành thạo (Cột tín hiệu + điểm số) --}}
                    <div class="tv-progress-row">
                        <span class="tv-prow-label">Độ thành thạo</span>
                        <div class="tv-mastery-indicator" title="Độ thành thạo: {{ number_format($masteryScore, 1) }}">
                            <div class="tv-mastery-bar tv-mastery-bar--1 {{ $masteryScore >= 1.0 ? 'is-active' : '' }}"></div>
                            <div class="tv-mastery-bar tv-mastery-bar--2 {{ $masteryScore >= 2.0 ? 'is-active' : '' }}"></div>
                            <div class="tv-mastery-bar tv-mastery-bar--3 {{ $masteryScore >= 3.0 ? 'is-active' : '' }}"></div>
                            <div class="tv-mastery-bar tv-mastery-bar--4 {{ $masteryScore >= 4.0 ? 'is-active' : '' }}"></div>
                            <div class="tv-mastery-bar tv-mastery-bar--5 {{ $masteryScore >= 5.0 ? 'is-active' : '' }}"></div>
                            <span class="tv-mastery-score">{{ number_format($masteryScore, 1) }}</span>
                        </div>
                    </div>

                    {{-- Hàng 3: Tỉ lệ đúng --}}
                    <div class="tv-progress-row">
                        <span class="tv-prow-label">Tỉ lệ đúng</span>
                        <div class="tv-accuracy-wrap">
                            <span class="tv-prow-value">{{ $accuracyPct }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Nút "HỌC TỪ MỚI" sử dụng Button Component có sẵn --}}
                <div class="tv-progress-cta-wrap">
                    <x-button variant="primary" size="medium" id="btnLearnNew" class="tv-btn-learn" href="{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}">
                        → HỌC TỪ MỚI
                    </x-button>
                </div>
            </div>
        </section>

        {{-- ===== 4. BỘ LỌC TỪ VỰNG (TẤT CẢ | NẮM VỮNG | DỄ QUÊN | BỎ QUA) ===== --}}
        <div class="tv-filter-nav-wrap">
            <div class="tv-filter-tabs" role="tablist">
                {{-- Tab 1: Tất cả --}}
                <button type="button"
                        class="tv-filter-tab-btn is-active"
                        role="tab"
                        aria-selected="true"
                        data-filter="all"
                        onclick="setFilterTab('all', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    <span>Tất cả</span>
                    <span class="tv-tab-count-badge" id="count-all">{{ $filterCounts['all'] ?? count($vocabularies) }}</span>
                </button>

                {{-- Tab 2: Nắm vững --}}
                <button type="button"
                        class="tv-filter-tab-btn"
                        role="tab"
                        aria-selected="false"
                        data-filter="mastered"
                        onclick="setFilterTab('mastered', this)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Nắm vững</span>
                    <span class="tv-tab-count-badge" id="count-mastered">{{ $filterCounts['mastered'] ?? 0 }}</span>
                </button>

                {{-- Tab 3: Dễ quên --}}
                <button type="button"
                        class="tv-filter-tab-btn"
                        role="tab"
                        aria-selected="false"
                        data-filter="review"
                        onclick="setFilterTab('review', this)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>Dễ quên</span>
                    <span class="tv-tab-count-badge" id="count-review">{{ $filterCounts['review'] ?? 0 }}</span>
                </button>

                {{-- Tab 4: Bỏ qua --}}
                <button type="button"
                        class="tv-filter-tab-btn"
                        role="tab"
                        aria-selected="false"
                        data-filter="skipped"
                        onclick="setFilterTab('skipped', this)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Bỏ qua</span>
                    <span class="tv-tab-count-badge" id="count-skipped">{{ $filterCounts['skipped'] ?? 0 }}</span>
                </button>
            </div>
        </div>

        {{-- ===== 5. THANH SẮP XẾP & VIEW MODE ===== --}}
        <div class="tv-toolbar-row">
            {{-- Dropdown sắp xếp --}}
            <div class="tv-sort-wrap">
                <button type="button" class="tv-sort-btn" onclick="toggleSortDropdown(event)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 15l5 5 5-5M7 9l5-5 5 5" />
                    </svg>
                    <span id="currentSortLabel">Mặc định</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>

                <div class="tv-sort-dropdown" id="sortDropdown">
                    <button type="button" class="tv-sort-item is-selected" onclick="applySort('default', 'Mặc định')">Mặc định</button>
                    <button type="button" class="tv-sort-item" onclick="applySort('az', 'Từ A → Z')">Từ A → Z</button>
                    <button type="button" class="tv-sort-item" onclick="applySort('za', 'Từ Z → A')">Từ Z → A</button>
                    <button type="button" class="tv-sort-item" onclick="applySort('level', 'Theo độ khó CEFR')">Theo độ khó CEFR</button>
                </div>
            </div>

            {{-- Chế độ xem (List icon) --}}
            <button type="button" class="tv-view-mode-btn" title="Chế độ xem danh sách" aria-label="Xem danh sách">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- ===== 6. DANH SÁCH VOCABULARY CARDS ===== --}}
        @if(count($vocabularies) > 0)
            <div class="tv-vocab-list" id="tvVocabList">
                @foreach($vocabularies as $vocab)
                    <x-vocabulary-card :vocab="$vocab" />
                @endforeach
            </div>

            {{-- Trạng thái rỗng khi filter không tìm thấy kết quả --}}
            <div class="tv-empty-filter-state" id="emptyFilterState" style="display: none;">
                <div class="tv-empty-filter-icon">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h4 class="tv-empty-filter-title">Chưa có từ vựng nào trong mục này</h4>
                <p class="tv-empty-filter-desc">Hãy luyện tập thêm để bổ sung từ vào danh sách này.</p>
                <x-button variant="neutral" size="small" onclick="resetToAllFilter()">
                    Xem tất cả từ vựng
                </x-button>
            </div>
        @else
            {{-- Trạng thái chủ đề chưa có từ vựng --}}
            <div class="tv-empty-topic-state">
                <div class="tv-empty-filter-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <h4 class="tv-empty-filter-title">Chủ đề này chưa có từ vựng.</h4>
                <p class="tv-empty-filter-desc">Nội dung từ vựng đang được biên soạn và sẽ sớm có mặt.</p>
                <x-button variant="primary" size="medium" href="{{ route('tu-vung') }}">
                    Quay lại danh mục Từ vựng
                </x-button>
            </div>
        @endif

    @else
        {{-- Fallback an toàn: Không tìm thấy chủ đề --}}
        <div class="tv-empty-topic-state">
            <div class="tv-empty-filter-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h4 class="tv-empty-filter-title">Không tìm thấy chủ đề này</h4>
            <p class="tv-empty-filter-desc">Chủ đề bạn yêu cầu không tồn tại hoặc đã được cập nhật đường dẫn mới.</p>
            <x-button variant="primary" size="medium" href="{{ route('tu-vung') }}">
                Quay lại danh sách chủ đề
            </x-button>
        </div>
    @endif

</div>

{{-- Toast thông báo tương tác --}}
<div class="tv-toast-popup" id="tvToastPopup" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
    <span id="tvToastMessage">Thông báo</span>
</div>

@push('scripts')
<script>
    // 1. Quản lý trạng thái tab lọc (Tất cả / Nắm vững / Dễ quên / Bỏ qua)
    let currentFilter = 'all';

    function setFilterTab(filter, tabElement) {
        currentFilter = filter;

        // Cập nhật giao diện tab active
        document.querySelectorAll('.tv-filter-tab-btn').forEach(btn => {
            btn.classList.remove('is-active');
            btn.setAttribute('aria-selected', 'false');
        });

        if (tabElement) {
            tabElement.classList.add('is-active');
            tabElement.setAttribute('aria-selected', 'true');
        }

        // Lọc danh sách thẻ từ vựng
        const cards = document.querySelectorAll('.tv-vocab-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status') || 'new';

            if (filter === 'all') {
                card.style.display = 'block';
                visibleCount++;
            } else if (filter === cardStatus) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Xử lý hiển thị empty state khi tab không có từ vựng
        const emptyState = document.getElementById('emptyFilterState');
        const vocabList = document.getElementById('tvVocabList');
        if (emptyState && vocabList) {
            if (visibleCount === 0 && cards.length > 0) {
                emptyState.style.display = 'flex';
                vocabList.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                vocabList.style.display = 'flex';
            }
        }
    }

    function resetToAllFilter() {
        const allTab = document.querySelector('[data-filter="all"]');
        setFilterTab('all', allTab);
    }

    // 2. Phát âm từ vựng / câu ví dụ sử dụng Web Speech API chuẩn
    function speakWord(text, btnElement) {
        if (!text) return;

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel(); // Dừng câu trước đó nếu đang đọc

            const utter = new SpeechSynthesisUtterance(text);
            utter.lang = 'en-US';
            utter.rate = 0.9; // Tốc độ chuẩn, rõ ràng

            if (btnElement) {
                btnElement.classList.add('is-playing');
                utter.onend = () => btnElement.classList.remove('is-playing');
                utter.onerror = () => btnElement.classList.remove('is-playing');
            }

            window.speechSynthesis.speak(utter);
        } else {
            showToast('Trình duyệt chưa hỗ trợ phát âm trực tiếp.');
        }
    }

    // 3. Đóng mở bản dịch câu ví dụ (Icon quả cầu)
    function toggleTranslation(vocabId) {
        const transBox = document.getElementById('example-vi-' + vocabId);
        if (transBox) {
            transBox.classList.toggle('show');
        }
    }

    // 4. Menu tùy chọn 3 chấm trên từng thẻ
    function toggleCardMenu(vocabId) {
        const menu = document.getElementById('vocabMenu-' + vocabId);
        if (!menu) return;

        const isCurrentlyOpen = menu.classList.contains('show');
        // Đóng các menu khác trước
        document.querySelectorAll('.tv-vocab-dropdown').forEach(m => m.classList.remove('show'));

        if (!isCurrentlyOpen) {
            menu.classList.add('show');
        }
    }

    // Đánh dấu trạng thái học từ vựng (Nắm vững / Dễ quên / Bỏ qua)
    function markVocabStatus(vocabId, newStatus) {
        const card = document.getElementById('vocab-card-' + vocabId);
        if (card) {
            card.setAttribute('data-status', newStatus);
            // Đóng menu
            const menu = document.getElementById('vocabMenu-' + vocabId);
            if (menu) menu.classList.remove('show');

            // Cập nhật lại số lượng trên các tab
            recalculateTabCounts();

            // Nếu đang ở tab filter khác, chạy lại filter
            if (currentFilter !== 'all') {
                const activeTab = document.querySelector('.tv-filter-tab-btn.is-active');
                setFilterTab(currentFilter, activeTab);
            }

            const statusNames = {
                'mastered': 'Đã chuyển vào mục: Nắm vững',
                'review': 'Đã chuyển vào mục: Dễ quên',
                'skipped': 'Đã chuyển vào mục: Bỏ qua'
            };
            showToast(statusNames[newStatus] || 'Đã cập nhật trạng thái');
        }
    }

    function recalculateTabCounts() {
        const cards = document.querySelectorAll('.tv-vocab-card');
        let counts = { all: cards.length, mastered: 0, review: 0, skipped: 0 };

        cards.forEach(card => {
            const status = card.getAttribute('data-status') || 'new';
            if (counts[status] !== undefined) {
                counts[status]++;
            }
        });

        const cAll = document.getElementById('count-all');
        const cMastered = document.getElementById('count-mastered');
        const cReview = document.getElementById('count-review');
        const cSkipped = document.getElementById('count-skipped');

        if (cAll) cAll.textContent = counts.all;
        if (cMastered) cMastered.textContent = counts.mastered;
        if (cReview) cReview.textContent = counts.review;
        if (cSkipped) cSkipped.textContent = counts.skipped;
    }

    // 5. Quản lý Sắp xếp danh sách (Sort Dropdown)
    function toggleSortDropdown(event) {
        event.stopPropagation();
        const dd = document.getElementById('sortDropdown');
        if (dd) dd.classList.toggle('show');
    }

    function applySort(sortKey, sortLabel) {
        const labelElem = document.getElementById('currentSortLabel');
        if (labelElem) labelElem.textContent = sortLabel;

        const dd = document.getElementById('sortDropdown');
        if (dd) {
            dd.classList.remove('show');
            dd.querySelectorAll('.tv-sort-item').forEach(item => {
                item.classList.toggle('is-selected', item.textContent.trim() === sortLabel);
            });
        }

        const list = document.getElementById('tvVocabList');
        if (!list) return;

        const cards = Array.from(list.querySelectorAll('.tv-vocab-card'));

        const cefrOrder = { 'A1': 1, 'A2': 2, 'B1': 3, 'B2': 4, 'C1': 5, 'C2': 6 };

        if (sortKey === 'az') {
            cards.sort((a, b) => a.dataset.word.localeCompare(b.dataset.word));
        } else if (sortKey === 'za') {
            cards.sort((a, b) => b.dataset.word.localeCompare(a.dataset.word));
        } else if (sortKey === 'level') {
            cards.sort((a, b) => (cefrOrder[a.dataset.cefr] || 0) - (cefrOrder[b.dataset.cefr] || 0));
        } else {
            // default ID order
            cards.sort((a, b) => {
                const idA = parseInt(a.id.replace('vocab-card-', ''), 10) || 0;
                const idB = parseInt(b.id.replace('vocab-card-', ''), 10) || 0;
                return idA - idB;
            });
        }

        cards.forEach(card => list.appendChild(card));
        showToast('Đã sắp xếp: ' + sortLabel);
    }

    // 6. Xử lý click button "HỌC TỪ MỚI"
    function handleStartLearn() {
        window.location.href = "{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}";
    }

    // 7. Toast helper hiển thị thông báo nhẹ nhàng
    let toastTimeout;
    function showToast(message) {
        const toast = document.getElementById('tvToastPopup');
        const msgElem = document.getElementById('tvToastMessage');
        if (!toast || !msgElem) return;

        msgElem.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 2800);
    }

    // Đóng dropdown khi click ra ngoài màn hình
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.tv-sort-wrap')) {
            const dd = document.getElementById('sortDropdown');
            if (dd) dd.classList.remove('show');
        }
        if (!e.target.closest('.tv-vocab-actions')) {
            document.querySelectorAll('.tv-vocab-dropdown').forEach(m => m.classList.remove('show'));
        }
    });
</script>
@endpush

</x-layouts.app>

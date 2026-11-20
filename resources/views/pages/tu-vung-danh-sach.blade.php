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
        @php
            $currentTopicId = $topic['topic_id'] ?? $topicId ?? null;
            $isPersonalTopic = false;
            if (auth()->check() && !empty($currentTopicId)) {
                $topicModel = \App\Models\Topic::find((int) $currentTopicId);
                $isPersonalTopic = ($topicModel !== null && $topicModel->user_id !== null && (int) $topicModel->user_id === (int) auth()->id());
            }
        @endphp

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

            {{-- Menu tùy chọn chủ đề cá nhân (chỉ hiển thị khi thuộc về người dùng đang đăng nhập) --}}
            @if($isPersonalTopic)
                <div class="tv-topic-actions">
                    <button type="button"
                            class="tv-topic-menu-btn"
                            id="btnTopicMenu"
                            title="Tùy chọn chủ đề"
                            aria-label="Tùy chọn chủ đề"
                            onclick="toggleTopicMenu(event)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="5" r="2.2" />
                            <circle cx="12" cy="12" r="2.2" />
                            <circle cx="12" cy="19" r="2.2" />
                        </svg>
                    </button>

                    {{-- Dropdown tùy chọn: 1 là sửa, 2 là xóa --}}
                    <div class="tv-topic-dropdown" id="topicDropdown">
                        <button type="button"
                                class="tv-topic-dropdown-item"
                                data-sp-modal-open="modalEditTopic"
                                onclick="closeTopicMenu()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Sửa chủ đề</span>
                        </button>
                        <button type="button"
                                class="tv-topic-dropdown-item tv-topic-dropdown-item--danger"
                                data-sp-modal-open="modalDeleteTopic"
                                onclick="closeTopicMenu()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                            <span>Xóa chủ đề</span>
                        </button>
                    </div>
                </div>
            @endif
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

                {{-- Nút hành động: "HỌC TỪ MỚI" & "THÊM TỪ VỰNG" (chỉ hiển thị cho chủ đề cá nhân do chính mình tạo) --}}
                <div class="tv-progress-cta-wrap">
                    @if($isPersonalTopic)
                        <x-button variant="neutral" size="medium" id="btnAddVocabulary" class="tv-btn-add-vocab" data-sp-modal-open="modalCreateVocabulary">
                            + THÊM TỪ VỰNG
                        </x-button>
                    @endif
                    <x-button variant="primary" size="medium" id="btnLearnNew" class="tv-btn-learn" href="{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}" data-require-auth>
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
                    <x-vocabulary-card :vocab="$vocab" :is-personal-topic="$isPersonalTopic" />
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

@if($topic && $isPersonalTopic)
    {{-- ===== MODAL THÊM TỪ VỰNG MỚI (BE-008.2.4 Frontend) ===== --}}
    <x-modal id="modalCreateVocabulary" title="Thêm từ vựng mới" maxWidth="640px">
        {{-- Biểu tượng đóng modal ở góc trên bên phải --}}
        <button type="button" class="tv-modal-close-icon" data-sp-modal-close aria-label="Đóng modal" id="btnCloseVocabModal">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <form id="formCreateVocabulary" onsubmit="return false;" novalidate>
            {{-- Hàng 1: Từ tiếng Anh (bắt buộc) & Phiên âm (không bắt buộc) --}}
            <div class="tv-form-grid-2col">
                <x-input
                    id="vocabWordInput"
                    name="word"
                    label="Từ tiếng Anh *"
                    placeholder="Ví dụ: collaborate"
                    maxlength="150"
                    :show-count="true"
                    size="md"
                    autocomplete="off"
                />

                <x-input
                    id="vocabPronunciationInput"
                    name="pronunciation"
                    label="Phiên âm"
                    placeholder="Ví dụ: /kəˈlæb.ə.reɪt/"
                    maxlength="150"
                    size="md"
                    autocomplete="off"
                />
            </div>

            {{-- Hàng 2: Nghĩa tiếng Việt (bắt buộc) & Loại từ (không bắt buộc) --}}
            <div class="tv-form-grid-2col">
                <x-input
                    id="vocabMeaningViInput"
                    name="meaning_vi"
                    label="Nghĩa tiếng Việt *"
                    placeholder="Ví dụ: hợp tác, cộng tác"
                    size="md"
                    autocomplete="off"
                />

                <div class="sp-field" id="wrap-vocabPosInput">
                    <label class="sp-label" for="vocabPosInput">
                        <span>Loại từ</span>
                    </label>
                    <div class="sp-control sp-control--md tv-select-control">
                        <select id="vocabPosInput" name="part_of_speech" class="sp-input tv-select-input">
                            <option value="">-- Chọn loại từ (không bắt buộc) --</option>
                            <option value="Noun">Danh từ (Noun)</option>
                            <option value="Verb">Động từ (Verb)</option>
                            <option value="Adjective">Tính từ (Adjective)</option>
                            <option value="Adverb">Trạng từ (Adverb)</option>
                            <option value="Preposition">Giới từ (Preposition)</option>
                            <option value="Conjunction">Liên từ (Conjunction)</option>
                            <option value="Pronoun">Đại từ (Pronoun)</option>
                            <option value="Phrase">Cụm từ (Phrase)</option>
                            <option value="Interjection">Thán từ (Interjection)</option>
                        </select>
                        <span class="tv-select-chevron" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </div>
                    <span id="vocabPosInput-desc" class="sp-caption" hidden></span>
                </div>
            </div>

            {{-- Hàng 3: URL hình ảnh (không bắt buộc) --}}
            <x-input
                id="vocabImageUrlInput"
                name="image_url"
                label="URL hình ảnh"
                placeholder="https://example.com/hinh-anh.jpg"
                maxlength="500"
                size="md"
                autocomplete="off"
            />

            {{-- Hàng 4: Ví dụ tiếng Anh (không bắt buộc) --}}
            <x-input
                type="textarea"
                id="vocabExampleEnInput"
                name="example_en"
                label="Ví dụ tiếng Anh"
                placeholder="Ví dụ: They decided to collaborate on the research project."
                rows="2"
                size="md"
            />

            {{-- Hàng 5: Nghĩa của ví dụ (không bắt buộc) --}}
            <x-input
                type="textarea"
                id="vocabExampleViInput"
                name="example_vi"
                label="Nghĩa của ví dụ"
                placeholder="Ví dụ: Họ quyết định hợp tác trong dự án nghiên cứu."
                rows="2"
                size="md"
            />

            {{-- Thông báo phản hồi trạng thái dữ liệu Frontend --}}
            <div id="createVocabNotice" class="tv-modal-notice" style="display: none;" role="status">
                <div class="tv-modal-notice-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="tv-modal-notice-text">
                    <div class="tv-modal-notice-title" id="createVocabNoticeTitle">Dữ liệu hợp lệ</div>
                    <div class="tv-modal-notice-desc" id="createVocabNoticeDesc">Chức năng đang được hoàn thiện, sẵn sàng kết nối Backend (BE-008.2.4).</div>
                </div>
            </div>
        </form>

        <x-slot:actions>
            <x-button variant="neutral" size="medium" data-sp-modal-close id="btnCancelCreateVocab">
                Hủy
            </x-button>
            <x-button variant="primary" size="medium" id="btnSubmitCreateVocab">
                + Thêm từ vựng
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- ===== MODAL XÁC NHẬN XÓA TỪ VỰNG CÁ NHÂN (BE-008.2.5) ===== --}}
    <x-modal id="modalConfirmDeleteSingleVocab" title="Xóa từ vựng" maxWidth="480px">
        <p style="margin: 0 0 16px; font-size: 14.5px; line-height: 1.5; color: #334155;">
            Bạn có chắc chắn muốn xóa từ <strong id="deleteSingleVocabWordName" style="color: #0F172A;"></strong> không? Tiến độ học liên quan cũng sẽ bị xóa. Hành động này không thể hoàn tác.
        </p>

        <x-slot:actions>
            <x-button variant="neutral" size="medium" data-sp-modal-close id="btnCancelDeleteSingleVocab" onclick="closeDeleteSingleVocabModal()">
                Hủy
            </x-button>
            <x-button variant="danger" size="medium" id="btnConfirmDeleteSingleVocab" onclick="executeDeleteSingleVocab()">
                Xóa từ vựng
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- ===== MODAL CHỈNH SỬA CHỦ ĐỀ CÁ NHÂN ===== --}}
    <x-modal id="modalEditTopic" title="Chỉnh sửa chủ đề cá nhân" maxWidth="560px">
        <div class="tv-edit-topic-group">
            <label for="editTopicNameInput" class="tv-edit-topic-label">
                Tên chủ đề <span class="tv-text-danger">*</span>
            </label>
            <input type="text"
                   id="editTopicNameInput"
                   class="tv-edit-topic-input"
                   value="{{ $topic['name'] }}"
                   placeholder="Nhập tên chủ đề..."
                   maxlength="100">
            <div id="editTopicNameError" style="display: none; color: #DC2626; font-size: 12.5px; margin-top: 5px; font-weight: 600;"></div>
        </div>

        <div class="tv-edit-topic-group" style="margin-bottom: 0;">
            <div class="tv-edit-vocab-header">
                <div>
                    <label class="tv-edit-topic-label" style="margin-bottom: 2px;">
                        Danh sách từ vựng trong chủ đề
                    </label>
                    <p class="tv-edit-topic-hint">Tích chọn các từ bạn muốn xóa khỏi chủ đề này:</p>
                </div>
                @if(count($vocabularies) > 0)
                    <div class="tv-edit-vocab-quick-actions" id="editVocabQuickActions">
                        <button type="button" class="tv-btn-select-action" onclick="toggleSelectAllVocabs(true)">Chọn tất cả</button>
                        <span class="tv-quick-sep">•</span>
                        <button type="button" class="tv-btn-select-action" onclick="toggleSelectAllVocabs(false)">Bỏ chọn</button>
                    </div>
                @endif
            </div>

            @if(count($vocabularies) > 0)
                <div class="tv-edit-vocab-list" id="editVocabList">
                    @foreach($vocabularies as $vocab)
                        @php
                            $vId = $vocab['vocabulary_id'] ?? $loop->index;
                            $vWord = $vocab['word'] ?? '';
                            $vPos = $vocab['part_of_speech'] ?? '';
                            $vMeaning = $vocab['meaning_vi'] ?? '';
                        @endphp
                        <label class="tv-edit-vocab-item" for="select-vocab-{{ $vId }}" id="edit-vocab-item-{{ $vId }}">
                            <input type="checkbox"
                                   class="tv-edit-vocab-checkbox"
                                   id="select-vocab-{{ $vId }}"
                                   value="{{ $vId }}"
                                   onchange="handleVocabCheckChange(this, '{{ $vId }}')">
                            <div class="tv-edit-vocab-info">
                                <div class="tv-edit-vocab-word-row">
                                    <span class="tv-edit-vocab-word">{{ $vWord }}</span>
                                    @if(!empty($vPos))
                                        <span class="tv-edit-vocab-pos">{{ $vPos }}</span>
                                    @endif
                                </div>
                                <div class="tv-edit-vocab-meaning">{{ $vMeaning }}</div>
                            </div>
                            <span class="tv-edit-vocab-tag-delete">Sẽ xóa</span>
                        </label>
                    @endforeach
                </div>
                <div class="tv-edit-vocab-footer-summary" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                    <span id="selectedVocabCountText">Đã chọn: <strong>0</strong> từ để xóa</span>
                    <button type="button"
                            id="btnTriggerDeleteVocabs"
                            class="tv-btn-action-delete"
                            style="display: none; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; transition: all 0.15s ease;"
                            onclick="openConfirmDeleteVocabsModal()">
                        Xóa từ đã chọn
                    </button>
                </div>
            @else
                <div class="tv-edit-vocab-empty" id="editVocabEmptyState">
                    <p>Chủ đề này hiện chưa có từ vựng nào.</p>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button variant="neutral" data-sp-modal-close>
                Hủy bỏ
            </x-button>
            <x-button variant="primary" id="btnSaveEditTopic" onclick="handleSaveEditTopic()">
                Lưu thay đổi
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- ===== MODAL XÁC NHẬN XÓA TỪ VỰNG HÀNG LOẠT (BE-008.2.2) ===== --}}
    <x-modal id="modalConfirmDeleteVocabs" title="Xác nhận xóa từ vựng" maxWidth="460px">
        <p style="margin: 0 0 14px; font-size: 15px; color: #334155; line-height: 1.5;">
            Bạn có chắc chắn muốn xóa <strong id="confirmDeleteVocabCount">0</strong> từ vựng đã chọn khỏi chủ đề này không?
        </p>
        <div style="font-size: 13.5px; color: #991B1B; background: #FEF2F2; padding: 12px 14px; border-radius: 12px; border: 1px solid #FEE2E2; line-height: 1.5;">
            <strong>Lưu ý:</strong> Thao tác này sẽ xóa vĩnh viễn các từ vựng được chọn và toàn bộ tiến độ học liên quan trong chủ đề.
        </div>

        <x-slot:actions>
            <x-button variant="neutral" type="button" onclick="closeConfirmDeleteVocabsModal()">
                Hủy
            </x-button>
            <x-button variant="danger" type="button" id="btnExecuteDeleteVocabs" onclick="executeDeleteSelectedVocabs()">
                Xóa từ đã chọn
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- ===== MODAL XÁC NHẬN XÓA CHỦ ĐỀ CÁ NHÂN (BE-008.2.3) ===== --}}
    <x-modal id="modalDeleteTopic" title="Xóa chủ đề?" maxWidth="460px">
        <p style="margin: 0; font-size: 15px; color: #334155; line-height: 1.6;">
            Bạn có chắc chắn muốn xóa chủ đề <strong>"{{ $topic['name'] }}"</strong> không? Toàn bộ từ vựng trong chủ đề và tiến độ học liên quan sẽ bị xóa vĩnh viễn. Hành động này không thể hoàn tác.
        </p>

        <x-slot:actions>
            <x-button variant="neutral" data-sp-modal-close>
                Hủy
            </x-button>
            <x-button variant="danger" id="btnConfirmDeleteTopic" onclick="executeDeleteTopic()">
                Xóa chủ đề
            </x-button>
        </x-slot:actions>
    </x-modal>
@endif

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

    // 8. Tùy chọn chủ đề cá nhân (Menu 3 chấm & Modal)
    function toggleTopicMenu(e) {
        if (e) e.stopPropagation();
        const dd = document.getElementById('topicDropdown');
        if (dd) {
            dd.classList.toggle('show');
        }
    }

    function closeTopicMenu() {
        const dd = document.getElementById('topicDropdown');
        if (dd) {
            dd.classList.remove('show');
        }
    }

    function handleVocabCheckChange(checkbox, vocabId) {
        const item = document.getElementById('edit-vocab-item-' + vocabId);
        if (item) {
            if (checkbox.checked) {
                item.classList.add('is-selected');
            } else {
                item.classList.remove('is-selected');
            }
        }
        updateSelectedVocabCount();
    }

    function toggleSelectAllVocabs(select) {
        const checkboxes = document.querySelectorAll('.tv-edit-vocab-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = select;
            const item = cb.closest('.tv-edit-vocab-item');
            if (item) {
                if (select) {
                    item.classList.add('is-selected');
                } else {
                    item.classList.remove('is-selected');
                }
            }
        });
        updateSelectedVocabCount();
    }

    function updateSelectedVocabCount() {
        const count = document.querySelectorAll('.tv-edit-vocab-checkbox:checked').length;
        const countText = document.getElementById('selectedVocabCountText');
        if (countText) {
            countText.innerHTML = 'Đã chọn: <strong>' + count + '</strong> từ để xóa';
        }
        const btnDelete = document.getElementById('btnTriggerDeleteVocabs');
        if (btnDelete) {
            btnDelete.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    function clearEditTopicNameError() {
        const nameInput = document.getElementById('editTopicNameInput');
        const errEl = document.getElementById('editTopicNameError');
        if (nameInput) {
            nameInput.style.borderColor = '';
        }
        if (errEl) {
            errEl.textContent = '';
            errEl.style.display = 'none';
        }
    }

    function showEditTopicNameError(msg) {
        const nameInput = document.getElementById('editTopicNameInput');
        const errEl = document.getElementById('editTopicNameError');
        if (nameInput) {
            nameInput.style.borderColor = '#DC2626';
            nameInput.focus();
        }
        if (errEl) {
            errEl.textContent = msg;
            errEl.style.display = 'block';
        }
    }

    // A. Thao tác Đổi tên chủ đề cá nhân (BE-008.2.2)
    // Tuyệt đối không xóa bất kỳ từ vựng nào khi thực hiện thao tác này!
    function handleSaveEditTopic() {
        const nameInput = document.getElementById('editTopicNameInput');
        const newName = nameInput ? nameInput.value.trim() : '';

        clearEditTopicNameError();

        if (!newName) {
            showEditTopicNameError('Vui lòng nhập tên chủ đề.');
            return;
        }

        if (newName.length > 100) {
            showEditTopicNameError('Tên chủ đề không được vượt quá 100 ký tự.');
            return;
        }

        const saveBtn = document.getElementById('btnSaveEditTopic');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Đang lưu...';
        }

        const updateUrl = "{{ route('tu-vung.topics.update', $currentTopicId ?? 0) }}";

        fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: newName })
        })
        .then(async function (res) {
            const data = await res.json().catch(function () { return {}; });
            return { status: res.status, ok: res.ok, data: data };
        })
        .then(function (result) {
            if (!result.ok) {
                throw result;
            }

            // Cập nhật tên chủ đề trên giao diện trang danh sách
            const topicNameEl = document.querySelector('.tv-topic-name');
            if (topicNameEl && result.data.topic && result.data.topic.name) {
                topicNameEl.textContent = result.data.topic.name;
            }
            document.title = 'StudyPrep - ' + (result.data.topic ? result.data.topic.name : newName);

            // Đóng modal chỉnh sửa
            const modal = document.getElementById('modalEditTopic');
            if (modal) {
                modal.hidden = true;
                if (!document.querySelector('.sp-modal:not([hidden])')) {
                    document.body.style.overflow = '';
                }
            }

            showToast('Cập nhật chủ đề thành công!');
        })
        .catch(function (err) {
            if (err.status === 422 && err.data && err.data.errors && err.data.errors.name) {
                showEditTopicNameError(err.data.errors.name[0]);
            } else if (err.status === 403) {
                showToast(err.data && err.data.message ? err.data.message : 'Bạn không có quyền chỉnh sửa chủ đề này.');
            } else if (err.status === 401) {
                showToast('Vui lòng đăng nhập lại để thực hiện.');
            } else {
                showEditTopicNameError('Đã có lỗi xảy ra. Vui lòng thử lại.');
            }
        })
        .finally(function () {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Lưu thay đổi';
            }
        });
    }

    // B. Thao tác Xóa từ vựng hàng loạt (BE-008.2.2)
    // Tách biệt hoàn toàn với thao tác đổi tên
    function openConfirmDeleteVocabsModal() {
        const checked = document.querySelectorAll('.tv-edit-vocab-checkbox:checked');
        if (!checked.length) {
            showToast('Vui lòng chọn ít nhất một từ vựng để xóa.');
            return;
        }

        const countSpan = document.getElementById('confirmDeleteVocabCount');
        if (countSpan) {
            countSpan.textContent = checked.length;
        }

        const confirmModal = document.getElementById('modalConfirmDeleteVocabs');
        if (confirmModal) {
            confirmModal.hidden = false;
            document.body.style.overflow = 'hidden';
        }
    }

    function closeConfirmDeleteVocabsModal() {
        const confirmModal = document.getElementById('modalConfirmDeleteVocabs');
        if (confirmModal) {
            confirmModal.hidden = true;
            // Nếu modalEditTopic vẫn đang mở thì giữ overflow: hidden, ngược lại mở khóa
            const stillOpen = document.querySelectorAll('.sp-modal:not([hidden])');
            if (!stillOpen.length) {
                document.body.style.overflow = '';
            }
        }
    }

    function executeDeleteSelectedVocabs() {
        const checked = document.querySelectorAll('.tv-edit-vocab-checkbox:checked');
        const selectedIds = Array.from(checked).map(function (cb) {
            return parseInt(cb.value, 10);
        });

        if (!selectedIds.length) {
            closeConfirmDeleteVocabsModal();
            return;
        }

        const executeBtn = document.getElementById('btnExecuteDeleteVocabs');
        if (executeBtn) {
            executeBtn.disabled = true;
            executeBtn.textContent = 'Đang xóa...';
        }

        const bulkDeleteUrl = "{{ route('tu-vung.topics.vocabularies.bulk-delete', $currentTopicId ?? 0) }}";

        fetch(bulkDeleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ vocabulary_ids: selectedIds })
        })
        .then(async function (res) {
            const data = await res.json().catch(function () { return {}; });
            return { status: res.status, ok: res.ok, data: data };
        })
        .then(function (result) {
            if (!result.ok) {
                throw result;
            }

            // 1. Đóng modal xác nhận
            closeConfirmDeleteVocabsModal();

            // 2. Gỡ bỏ các từ đã xóa khỏi danh sách trong Modal
            selectedIds.forEach(function (id) {
                const modalItem = document.getElementById('edit-vocab-item-' + id);
                if (modalItem) {
                    modalItem.remove();
                }

                // 3. Gỡ bỏ các từ đã xóa khỏi danh sách thẻ từ vựng trên trang
                const card = document.getElementById('vocab-card-' + id);
                if (card) {
                    card.remove();
                }
            });

            // 4. Cập nhật số lượng hiển thị trong modal và trên trang
            updateSelectedVocabCount();

            const remainingCount = typeof result.data.remaining_count !== 'undefined'
                ? result.data.remaining_count
                : document.querySelectorAll('.tv-vocab-card').length;

            const countEl = document.querySelector('.tv-topic-vocab-count');
            if (countEl) {
                countEl.textContent = remainingCount + ' từ vựng';
            }

            const tabCountAll = document.getElementById('count-all');
            if (tabCountAll) {
                tabCountAll.textContent = remainingCount;
            }

            // 5. Nếu trong modal không còn từ nào, hiển thị empty state
            const modalVocabItems = document.querySelectorAll('.tv-edit-vocab-item');
            if (!modalVocabItems.length) {
                const listContainer = document.getElementById('editVocabList');
                if (listContainer) {
                    listContainer.innerHTML = '<div class="tv-edit-vocab-empty" id="editVocabEmptyState"><p>Chủ đề này hiện chưa có từ vựng nào.</p></div>';
                }
                const quickActions = document.getElementById('editVocabQuickActions');
                if (quickActions) {
                    quickActions.style.display = 'none';
                }
            }

            // 6. Nếu trên trang không còn từ nào, hiển thị empty topic state
            const remainingCards = document.querySelectorAll('.tv-vocab-card');
            if (!remainingCards.length) {
                const listWrap = document.getElementById('tvVocabList');
                if (listWrap) {
                    listWrap.outerHTML = `
                        <div class="tv-empty-topic-state">
                            <div class="tv-empty-filter-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                            </div>
                            <h4 class="tv-empty-filter-title">Chủ đề này chưa có từ vựng.</h4>
                            <p class="tv-empty-filter-desc">Nội dung từ vựng đang được biên soạn và sẽ sớm có mặt.</p>
                            <a href="{{ route('tu-vung') }}" class="sp-btn sp-btn--primary sp-btn--medium">Quay lại danh mục Từ vựng</a>
                        </div>
                    `;
                }
            }

            showToast('Đã xóa các từ vựng được chọn!');
        })
        .catch(function (err) {
            const msg = err.data && err.data.message ? err.data.message : 'Có lỗi xảy ra khi xóa từ vựng.';
            showToast(msg);
        })
        .finally(function () {
            if (executeBtn) {
                executeBtn.disabled = false;
                executeBtn.textContent = 'Xóa từ đã chọn';
            }
        });
    }

    // C. Thao tác Xóa toàn bộ chủ đề cá nhân (BE-008.2.3)
    function executeDeleteTopic() {
        const deleteBtn = document.getElementById('btnConfirmDeleteTopic');
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Đang xóa...';
        }

        const deleteUrl = "{{ route('tu-vung.topics.destroy', $currentTopicId ?? 0) }}";

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(async function (res) {
            const data = await res.json().catch(function () { return {}; });
            return { status: res.status, ok: res.ok, data: data };
        })
        .then(function (result) {
            if (!result.ok) {
                throw result;
            }

            // Đóng modal xác nhận xóa
            const modal = document.getElementById('modalDeleteTopic');
            if (modal) {
                modal.hidden = true;
                if (!document.querySelector('.sp-modal:not([hidden])')) {
                    document.body.style.overflow = '';
                }
            }

            // Hiển thị Toast thông báo thành công
            showToast('Đã xóa chủ đề thành công!');

            // Điều hướng về trang Từ vựng
            const redirectUrl = (result.data && result.data.redirect_url) ? result.data.redirect_url : "{{ route('tu-vung') }}";
            setTimeout(function () {
                window.location.href = redirectUrl;
            }, 650);
        })
        .catch(function (err) {
            if (err.status === 401) {
                showToast('Vui lòng đăng nhập lại để thực hiện.');
                if (typeof window.spOpenAuthModal === 'function') {
                    window.spOpenAuthModal();
                }
            } else if (err.status === 403) {
                showToast(err.data && err.data.message ? err.data.message : 'Bạn không có quyền xóa chủ đề này.');
            } else if (err.status === 404) {
                showToast('Chủ đề không tồn tại hoặc đã bị xóa.');
            } else {
                showToast(err.data && err.data.message ? err.data.message : 'Có lỗi xảy ra khi xóa chủ đề. Vui lòng thử lại.');
            }
        })
        .finally(function () {
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Xóa chủ đề';
            }
        });
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
        if (!e.target.closest('.tv-topic-actions')) {
            const topicDd = document.getElementById('topicDropdown');
            if (topicDd) topicDd.classList.remove('show');
        }
    });

    // ===== 9. QUẢN LÝ DỮ LIỆU & MODAL TỪ VỰNG CÁ NHÂN (BE-008.2.4 & BE-008.2.5) =====
    const vocabMap = {};
    @foreach($vocabularies as $v)
        vocabMap[{{ $v['vocabulary_id'] }}] = @json($v);
    @endforeach

    function getVocabData(vocabId) {
        if (vocabMap[vocabId]) return vocabMap[vocabId];
        const card = document.getElementById('vocab-card-' + vocabId);
        if (card && card.dataset.vocab) {
            try { return JSON.parse(card.dataset.vocab); } catch (_) {}
        }
        return null;
    }

    let vocabModalOpener = null;
    let currentVocabModalMode = 'create'; // 'create' | 'edit'
    let editingVocabId = null;

    function setVocabModalMode(mode, vocab = null) {
        currentVocabModalMode = mode;
        const modal = document.getElementById('modalCreateVocabulary');
        if (!modal) return;
        const titleEl = modal.querySelector('.sp-modal__title') || modal.querySelector('h2') || modal.querySelector('.sp-modal-title');
        const submitBtn = document.getElementById('btnSubmitCreateVocab');

        if (mode === 'edit') {
            editingVocabId = vocab ? vocab.vocabulary_id : null;
            if (titleEl) titleEl.textContent = 'Sửa từ vựng';
            if (submitBtn) submitBtn.innerHTML = 'Lưu thay đổi';
        } else {
            editingVocabId = null;
            if (titleEl) titleEl.textContent = 'Thêm từ vựng mới';
            if (submitBtn) submitBtn.innerHTML = '+ Thêm từ vựng';
        }
    }

    function openCreateVocabModal() {
        const modal = document.getElementById('modalCreateVocabulary');
        if (!modal) return;
        vocabModalOpener = document.getElementById('btnAddVocabulary') || document.activeElement;
        setVocabModalMode('create');
        resetCreateVocabForm(true);
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(function () {
            const wordInput = document.getElementById('vocabWordInput');
            if (wordInput) wordInput.focus();
        }, 120);
    }

    function openEditVocabModal(vocabId) {
        document.querySelectorAll('.tv-vocab-dropdown').forEach(m => m.classList.remove('show'));

        const vocab = getVocabData(vocabId);
        if (!vocab) {
            showToast('Không tìm thấy thông tin từ vựng.');
            return;
        }

        const modal = document.getElementById('modalCreateVocabulary');
        if (!modal) return;

        vocabModalOpener = document.activeElement;
        setVocabModalMode('edit', vocab);
        resetCreateVocabForm(false);

        const wordInput = document.getElementById('vocabWordInput');
        const pronInput = document.getElementById('vocabPronunciationInput');
        const meaningInput = document.getElementById('vocabMeaningViInput');
        const posSelect = document.getElementById('vocabPosInput');
        const imageUrlInput = document.getElementById('vocabImageUrlInput');
        const exEnInput = document.getElementById('vocabExampleEnInput');
        const exViInput = document.getElementById('vocabExampleViInput');

        if (wordInput) wordInput.value = vocab.word || '';
        if (pronInput) pronInput.value = vocab.pronunciation || '';
        if (meaningInput) meaningInput.value = vocab.meaning_vi || '';
        if (posSelect) posSelect.value = vocab.part_of_speech || '';
        if (imageUrlInput) imageUrlInput.value = vocab.image_url || '';
        if (exEnInput) exEnInput.value = vocab.example_en || '';
        if (exViInput) exViInput.value = vocab.example_vi || '';

        const wordCount = document.getElementById('vocabWordInput-count');
        if (wordCount) {
            wordCount.textContent = (vocab.word || '').length + ' / 150 ký tự';
        }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(function () {
            if (wordInput) wordInput.focus();
        }, 120);
    }

    function closeCreateVocabModal() {
        const modal = document.getElementById('modalCreateVocabulary');
        if (!modal) return;
        modal.hidden = true;
        if (!document.querySelector('.sp-modal:not([hidden])')) {
            document.body.style.overflow = '';
        }
        setVocabModalMode('create');
        resetCreateVocabForm(true);
        if (vocabModalOpener && typeof vocabModalOpener.focus === 'function') {
            vocabModalOpener.focus();
        }
    }

    function resetCreateVocabForm(clearValues = true) {
        const form = document.getElementById('formCreateVocabulary');
        if (form && clearValues) {
            form.reset();
        }
        ['vocabWordInput', 'vocabPronunciationInput', 'vocabMeaningViInput', 'vocabPosInput', 'vocabImageUrlInput', 'vocabExampleEnInput', 'vocabExampleViInput'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) clearVocabFieldError(el);
        });

        const notice = document.getElementById('createVocabNotice');
        if (notice) {
            notice.style.display = 'none';
            notice.className = 'tv-modal-notice';
        }

        if (clearValues) {
            const wordCount = document.getElementById('vocabWordInput-count');
            if (wordCount) {
                wordCount.textContent = '0 / 150 ký tự';
            }
        }
    }

    function setVocabFieldError(field, message) {
        if (!field) return;
        const control = field.closest('.sp-control');
        const label = field.closest('.sp-field') ? field.closest('.sp-field').querySelector('.sp-label') : null;
        const desc = document.getElementById(field.id + '-desc');

        if (control) control.classList.add('has-error');
        if (label) label.classList.add('sp-label--error');
        if (desc) {
            desc.textContent = '! ' + message;
            desc.className = 'sp-caption sp-caption--error';
            desc.hidden = false;
        }
    }

    function clearVocabFieldError(field) {
        if (!field) return;
        const control = field.closest('.sp-control');
        const label = field.closest('.sp-field') ? field.closest('.sp-field').querySelector('.sp-label') : null;
        const desc = document.getElementById(field.id + '-desc');

        if (control) control.classList.remove('has-error');
        if (label) label.classList.remove('sp-label--error');
        if (desc) {
            desc.textContent = '';
            desc.className = 'sp-caption';
            desc.hidden = true;
        }
    }

    function isValidHttpUrl(str) {
        try {
            const parsed = new URL(str);
            return parsed.protocol === 'http:' || parsed.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }

    function handleCreateVocabSubmit() {
        const wordInput = document.getElementById('vocabWordInput');
        const pronInput = document.getElementById('vocabPronunciationInput');
        const meaningInput = document.getElementById('vocabMeaningViInput');
        const posSelect = document.getElementById('vocabPosInput');
        const imageUrlInput = document.getElementById('vocabImageUrlInput');
        const notice = document.getElementById('createVocabNotice');

        let isValid = true;
        let firstErrorField = null;

        // 1. word: bắt buộc, không được chỉ chứa khoảng trắng, tối đa 150 ký tự
        const wordVal = wordInput ? wordInput.value.trim() : '';
        if (!wordVal) {
            setVocabFieldError(wordInput, 'Vui lòng nhập từ tiếng Anh.');
            isValid = false;
            if (!firstErrorField) firstErrorField = wordInput;
        } else if (wordVal.length > 150) {
            setVocabFieldError(wordInput, 'Từ tiếng Anh không được vượt quá 150 ký tự (hiện tại ' + wordVal.length + ' ký tự).');
            isValid = false;
            if (!firstErrorField) firstErrorField = wordInput;
        } else {
            clearVocabFieldError(wordInput);
        }

        // 2. meaning_vi: bắt buộc, không được chỉ chứa khoảng trắng
        const meaningVal = meaningInput ? meaningInput.value.trim() : '';
        if (!meaningVal) {
            setVocabFieldError(meaningInput, 'Vui lòng nhập nghĩa tiếng Việt.');
            isValid = false;
            if (!firstErrorField) firstErrorField = meaningInput;
        } else {
            clearVocabFieldError(meaningInput);
        }

        // 3. pronunciation: không bắt buộc, tối đa 150 ký tự
        const pronVal = pronInput ? pronInput.value.trim() : '';
        if (pronVal.length > 150) {
            setVocabFieldError(pronInput, 'Phiên âm không được vượt quá 150 ký tự.');
            isValid = false;
            if (!firstErrorField) firstErrorField = pronInput;
        } else if (pronInput) {
            clearVocabFieldError(pronInput);
        }

        // 4. part_of_speech: không bắt buộc, tối đa 30 ký tự
        const posVal = posSelect ? posSelect.value.trim() : '';
        if (posVal.length > 30) {
            setVocabFieldError(posSelect, 'Loại từ không được vượt quá 30 ký tự.');
            isValid = false;
            if (!firstErrorField) firstErrorField = posSelect;
        } else if (posSelect) {
            clearVocabFieldError(posSelect);
        }

        // 5. image_url: không bắt buộc, tối đa 500 ký tự và phải là URL hợp lệ nếu có
        const imageUrlVal = imageUrlInput ? imageUrlInput.value.trim() : '';
        if (imageUrlVal.length > 500) {
            setVocabFieldError(imageUrlInput, 'URL hình ảnh không được vượt quá 500 ký tự.');
            isValid = false;
            if (!firstErrorField) firstErrorField = imageUrlInput;
        } else if (imageUrlVal.length > 0 && !isValidHttpUrl(imageUrlVal)) {
            setVocabFieldError(imageUrlInput, 'URL hình ảnh không hợp lệ (phải bắt đầu bằng http:// hoặc https://).');
            isValid = false;
            if (!firstErrorField) firstErrorField = imageUrlInput;
        } else if (imageUrlInput) {
            clearVocabFieldError(imageUrlInput);
        }

        // Nếu có lỗi: giữ nguyên dữ liệu đã nhập, không đóng modal, focus trường lỗi đầu tiên
        if (!isValid) {
            if (notice) notice.style.display = 'none';
            if (firstErrorField) firstErrorField.focus();
            return;
        }

        const isEditing = currentVocabModalMode === 'edit' && editingVocabId;
        const submitBtn = document.getElementById('btnSubmitCreateVocab');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : (isEditing ? 'Lưu thay đổi' : '+ Thêm từ vựng');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang lưu...';
        }

        const exEnInput = document.getElementById('vocabExampleEnInput');
        const exViInput = document.getElementById('vocabExampleViInput');

        const payload = {
            word: wordVal,
            meaning_vi: meaningVal,
            part_of_speech: posVal || null,
            pronunciation: pronVal || null,
            example_en: exEnInput && exEnInput.value.trim() ? exEnInput.value.trim() : null,
            example_vi: exViInput && exViInput.value.trim() ? exViInput.value.trim() : null,
            image_url: imageUrlVal || null
        };

        const targetUrl = isEditing
            ? "/tu-vung/chu-de/{{ $currentTopicId ?? 0 }}/tu-vung/" + editingVocabId
            : "{{ route('tu-vung.topics.vocabularies.store', $currentTopicId ?? 0) }}";
        const httpMethod = isEditing ? 'PATCH' : 'POST';

        fetch(targetUrl, {
            method: httpMethod,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(async function (res) {
            const data = await res.json().catch(function () { return {}; });
            return { status: res.status, ok: res.ok, data: data };
        })
        .then(function (result) {
            if (result.status === 201 || (isEditing && result.status === 200)) {
                closeCreateVocabModal();
                resetCreateVocabForm(true);

                const successMsg = result.data.message || (isEditing ? 'Cập nhật từ vựng thành công!' : 'Thêm từ vựng thành công!');
                showToast(successMsg);

                if (isEditing) {
                    updateVocabularyCard(result.data.vocabulary);
                } else {
                    appendVocabularyCard(result.data.vocabulary, result.data.total_vocabularies);
                }
                return;
            }

            if (result.status === 422) {
                const errors = result.data.errors || {};
                let firstBackendErr = null;

                if (errors.word) {
                    setVocabFieldError(wordInput, errors.word[0]);
                    if (!firstBackendErr) firstBackendErr = wordInput;
                }
                if (errors.meaning_vi) {
                    setVocabFieldError(meaningInput, errors.meaning_vi[0]);
                    if (!firstBackendErr) firstBackendErr = meaningInput;
                }
                if (errors.part_of_speech) {
                    setVocabFieldError(posSelect, errors.part_of_speech[0]);
                    if (!firstBackendErr) firstBackendErr = posSelect;
                }
                if (errors.pronunciation) {
                    setVocabFieldError(pronInput, errors.pronunciation[0]);
                    if (!firstBackendErr) firstBackendErr = pronInput;
                }
                if (errors.image_url) {
                    setVocabFieldError(imageUrlInput, errors.image_url[0]);
                    if (!firstBackendErr) firstBackendErr = imageUrlInput;
                }
                if (errors.example_en) {
                    setVocabFieldError(exEnInput, errors.example_en[0]);
                    if (!firstBackendErr) firstBackendErr = exEnInput;
                }
                if (errors.example_vi) {
                    setVocabFieldError(exViInput, errors.example_vi[0]);
                    if (!firstBackendErr) firstBackendErr = exViInput;
                }

                if (firstBackendErr) {
                    firstBackendErr.focus();
                } else if (result.data.message) {
                    showToast(result.data.message);
                }
                return;
            }

            const errorMsg = result.data && result.data.message
                ? result.data.message
                : (result.status === 403 ? 'Bạn không có quyền thực hiện thao tác này.' : 'Đã có lỗi xảy ra.');
            showToast(errorMsg);
        })
        .catch(function () {
            showToast('Không thể kết nối đến máy chủ. Vui lòng thử lại sau.');
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }

    // ===== 10. XÓA TỪNG TỪ VỰNG CÁ NHÂN (BE-008.2.5 Frontend) =====
    let deletingVocabId = null;

    function openDeleteVocabModal(vocabId) {
        document.querySelectorAll('.tv-vocab-dropdown').forEach(m => m.classList.remove('show'));

        const vocab = getVocabData(vocabId);
        const wordName = vocab && vocab.word ? vocab.word : '';

        deletingVocabId = vocabId;
        const wordNameEl = document.getElementById('deleteSingleVocabWordName');
        if (wordNameEl) {
            wordNameEl.textContent = wordName ? '"' + wordName + '"' : 'này';
        }

        const modal = document.getElementById('modalConfirmDeleteSingleVocab');
        if (modal) {
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDeleteSingleVocabModal() {
        const modal = document.getElementById('modalConfirmDeleteSingleVocab');
        if (modal) {
            modal.hidden = true;
            if (!document.querySelector('.sp-modal:not([hidden])')) {
                document.body.style.overflow = '';
            }
        }
        deletingVocabId = null;
    }

    function executeDeleteSingleVocab() {
        if (!deletingVocabId) return;

        const btn = document.getElementById('btnConfirmDeleteSingleVocab');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Đang xóa...';
        }

        const deleteUrl = "/tu-vung/chu-de/{{ $currentTopicId ?? 0 }}/tu-vung/" + deletingVocabId;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(async function (res) {
            const data = await res.json().catch(function () { return {}; });
            return { status: res.status, ok: res.ok, data: data };
        })
        .then(function (result) {
            if (!result.ok) {
                throw result;
            }

            const removedId = deletingVocabId;
            closeDeleteSingleVocabModal();

            // 1. Xóa thẻ trên giao diện
            const card = document.getElementById('vocab-card-' + removedId);
            if (card) {
                card.remove();
            }

            // 2. Xóa khỏi modal Sửa chủ đề nếu có
            const editItem = document.getElementById('edit-vocab-item-' + removedId);
            if (editItem) {
                editItem.remove();
                if (typeof updateSelectedVocabCount === 'function') {
                    updateSelectedVocabCount();
                }
            }

            // 3. Xóa khỏi bộ nhớ vocabMap
            delete vocabMap[removedId];

            // 4. Cập nhật số lượng
            const remaining = typeof result.data.remaining_count !== 'undefined'
                ? result.data.remaining_count
                : document.querySelectorAll('.tv-vocab-card').length;

            const countEl = document.querySelector('.tv-topic-vocab-count');
            if (countEl) {
                countEl.textContent = remaining + ' từ vựng';
            }

            const tabCountAll = document.getElementById('count-all');
            if (tabCountAll) {
                tabCountAll.textContent = remaining;
            }

            recalculateTabCounts();

            // 5. Nếu hết từ, hiển thị empty topic state
            if (remaining === 0) {
                const listWrap = document.getElementById('tvVocabList');
                if (listWrap) {
                    listWrap.outerHTML = `
                        <div class="tv-empty-topic-state">
                            <div class="tv-empty-filter-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                            </div>
                            <h4 class="tv-empty-filter-title">Chủ đề này chưa có từ vựng.</h4>
                            <p class="tv-empty-filter-desc">Nội dung từ vựng đang được biên soạn và sẽ sớm có mặt.</p>
                            <a href="{{ route('tu-vung') }}" class="sp-btn sp-btn--primary sp-btn--medium">Quay lại danh mục Từ vựng</a>
                        </div>
                    `;
                }
            }

            showToast('Đã xóa từ vựng thành công!');
        })
        .catch(function (err) {
            const msg = err.data && err.data.message ? err.data.message : 'Có lỗi xảy ra khi xóa từ vựng.';
            showToast(msg);
        })
        .finally(function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Xóa từ vựng';
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJsString(str) {
        if (!str) return '';
        return String(str)
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '\\"')
            .replace(/\n/g, '\\n')
            .replace(/\r/g, '');
    }

    function highlightWordInExample(word, example) {
        if (!word || !example) return escapeHtml(example || '');
        const escapedEx = escapeHtml(example);
        const escapedWord = escapeHtml(word);
        const regex = new RegExp('(\\b' + escapedWord.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '[a-zA-Z]*\\b)', 'gi');
        return escapedEx.replace(regex, '<span class="tv-vocab-highlight">$1</span>');
    }

    function createVocabCardHtml(vocab) {
        const isPersonal = Boolean({{ $isPersonalTopic ? 'true' : 'false' }});
        const encodedVocab = escapeHtml(JSON.stringify(vocab));
        return `
            <article class="tv-vocab-card"
                     data-status="${vocab.status || 'new'}"
                     data-word="${escapeHtml((vocab.word || '').toLowerCase())}"
                     data-cefr="${escapeHtml(vocab.cefr_level || 'B1')}"
                     data-vocab="${encodedVocab}"
                     id="vocab-card-${vocab.vocabulary_id}">
                <div class="tv-vocab-header">
                    <h3 class="tv-vocab-word">${escapeHtml(vocab.word)}</h3>
                    <div class="tv-vocab-actions">
                        <button type="button"
                                class="tv-vocab-menu-btn"
                                aria-label="Tùy chọn từ vựng ${escapeHtml(vocab.word)}"
                                title="Tùy chọn"
                                onclick="toggleCardMenu('${vocab.vocabulary_id}')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2.2" />
                                <circle cx="12" cy="12" r="2.2" />
                                <circle cx="12" cy="19" r="2.2" />
                            </svg>
                        </button>
                        <div class="tv-vocab-dropdown" id="vocabMenu-${vocab.vocabulary_id}">
                            ${isPersonal ? `
                            <button type="button" class="tv-vocab-dropdown-item" onclick="openEditVocabModal('${vocab.vocabulary_id}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Sửa</span>
                            </button>
                            <button type="button" class="tv-vocab-dropdown-item tv-vocab-dropdown-item--danger" onclick="openDeleteVocabModal('${vocab.vocabulary_id}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                                <span>Xóa</span>
                            </button>
                            <hr class="tv-vocab-dropdown-divider" style="margin: 4px 0; border: none; border-top: 1px solid #EEF1F7;">
                            ` : ''}
                            <button type="button" class="tv-vocab-dropdown-item" onclick="markVocabStatus('${vocab.vocabulary_id}', 'mastered')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Đánh dấu Đã nắm vững
                            </button>
                            <button type="button" class="tv-vocab-dropdown-item" onclick="markVocabStatus('${vocab.vocabulary_id}', 'review')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                Đánh dấu Dễ quên
                            </button>
                            <button type="button" class="tv-vocab-dropdown-item" onclick="markVocabStatus('${vocab.vocabulary_id}', 'skipped')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Bỏ qua từ này
                            </button>
                        </div>
                    </div>
                </div>
                <div class="tv-vocab-meta">
                    <span class="tv-cefr-badge" style="background-color: #10B981;">
                        ${escapeHtml(vocab.cefr_level || 'B1')}
                    </span>
                    <span class="tv-vocab-pos">${escapeHtml(vocab.part_of_speech || 'Từ vựng')}</span>
                    <button type="button"
                            class="tv-audio-btn tv-audio-btn--small"
                            aria-label="Nghe phát âm từ ${escapeHtml(vocab.word)}"
                            title="Nghe phát âm"
                            onclick="speakWord('${escapeJsString(vocab.word)}', this)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                        </svg>
                    </button>
                    ${vocab.pronunciation ? `<span class="tv-vocab-phonetic">${escapeHtml(vocab.pronunciation)}</span>` : ''}
                </div>
                <div class="tv-vocab-meaning">
                    <span class="tv-star-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#EF4444">
                            <circle cx="12" cy="12" r="11" fill="#EF4444" />
                            <path d="M12 6.5l1.6 3.8 4.1.4-3.1 2.7.9 4.1-3.5-2.1-3.5 2.1.9-4.1-3.1-2.7 4.1-.4L12 6.5z" fill="#FFFFFF" />
                        </svg>
                    </span>
                    <span class="tv-meaning-text">${escapeHtml(vocab.meaning_vi)}</span>
                </div>
                ${vocab.example_en ? `
                <div class="tv-vocab-example-box">
                    <div class="tv-example-header">
                        <button type="button"
                                class="tv-audio-btn tv-audio-btn--example"
                                aria-label="Nghe câu ví dụ"
                                title="Nghe câu ví dụ"
                                onclick="speakWord('${escapeJsString(vocab.example_en)}', this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                        <div class="tv-example-content">
                            <p class="tv-example-en">${highlightWordInExample(vocab.word, vocab.example_en)}</p>
                        </div>
                        ${vocab.example_vi ? `
                        <button type="button"
                                class="tv-translate-btn"
                                aria-label="Xem dịch câu ví dụ"
                                title="Dịch câu ví dụ"
                                onclick="toggleTranslation('${vocab.vocabulary_id}')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                        </button>
                        ` : ''}
                    </div>
                    ${vocab.example_vi ? `
                    <div class="tv-example-vi" id="example-vi-${vocab.vocabulary_id}">
                        <span>${escapeHtml(vocab.example_vi)}</span>
                    </div>
                    ` : ''}
                </div>
                ` : ''}
                ${vocab.image_url ? `
                <div class="tv-vocab-illustration">
                    <img src="${escapeHtml(vocab.image_url)}" alt="${escapeHtml(vocab.word)}" loading="lazy">
                </div>
                ` : ''}
            </article>
        `;
    }

    function updateVocabularyCard(vocab) {
        if (!vocab) return;
        vocabMap[vocab.vocabulary_id] = vocab;

        const oldCard = document.getElementById('vocab-card-' + vocab.vocabulary_id);
        if (oldCard) {
            const temp = document.createElement('div');
            temp.innerHTML = createVocabCardHtml(vocab);
            if (temp.firstElementChild) {
                oldCard.parentNode.replaceChild(temp.firstElementChild, oldCard);
            }
        }

        // Cập nhật trong modal Sửa chủ đề nếu có
        const editItem = document.getElementById('edit-vocab-item-' + vocab.vocabulary_id);
        if (editItem) {
            const wordEl = editItem.querySelector('.tv-edit-vocab-word');
            const posEl = editItem.querySelector('.tv-edit-vocab-pos');
            const meaningEl = editItem.querySelector('.tv-edit-vocab-meaning');
            if (wordEl) wordEl.textContent = vocab.word || '';
            if (posEl) {
                if (vocab.part_of_speech) {
                    posEl.textContent = vocab.part_of_speech;
                    posEl.style.display = '';
                } else {
                    posEl.style.display = 'none';
                }
            }
            if (meaningEl) meaningEl.textContent = vocab.meaning_vi || '';
        }
    }

    function appendVocabularyCard(vocab, totalVocabularies) {
        if (!vocab) return;
        vocabMap[vocab.vocabulary_id] = vocab;

        let listContainer = document.getElementById('tvVocabList');

        // Nếu lúc đầu chủ đề rỗng (chưa có tvVocabList mà chỉ có .tv-empty-topic-state)
        if (!listContainer) {
            const emptyTopicState = document.querySelector('.tv-empty-topic-state');
            if (emptyTopicState) {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                    <div class="tv-vocab-list" id="tvVocabList"></div>
                    <div class="tv-empty-filter-state" id="emptyFilterState" style="display: none;">
                        <div class="tv-empty-filter-icon">
                            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <h4 class="tv-empty-filter-title">Chưa có từ vựng nào trong mục này</h4>
                        <p class="tv-empty-filter-desc">Hãy luyện tập thêm để bổ sung từ vào danh sách này.</p>
                        <button type="button" class="sp-btn sp-btn--neutral sp-btn--small" onclick="resetToAllFilter()">
                            Xem tất cả từ vựng
                        </button>
                    </div>
                `;
                emptyTopicState.parentNode.replaceChild(wrapper, emptyTopicState);
                listContainer = document.getElementById('tvVocabList');
            }
        }

        if (listContainer) {
            const cardEl = document.createElement('div');
            cardEl.innerHTML = createVocabCardHtml(vocab);
            if (cardEl.firstElementChild) {
                listContainer.appendChild(cardEl.firstElementChild);
            }
        }

        // Bổ sung vào modal chỉnh sửa chủ đề (danh sách chọn để xóa) nếu đang có trên trang
        let editList = document.getElementById('editVocabList');
        const editEmpty = document.getElementById('editVocabEmptyState');
        if (editEmpty && !editList) {
            const editParent = editEmpty.parentNode;
            if (editParent) {
                const newEditWrapper = document.createElement('div');
                newEditWrapper.innerHTML = `
                    <div class="tv-edit-vocab-list" id="editVocabList"></div>
                    <div class="tv-edit-vocab-footer-summary" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <span id="selectedVocabCountText">Đã chọn: <strong>0</strong> từ để xóa</span>
                        <button type="button"
                                id="btnTriggerDeleteVocabs"
                                class="tv-btn-action-delete"
                                style="display: none; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; transition: all 0.15s ease;"
                                onclick="openConfirmDeleteVocabsModal()">
                            Xóa từ đã chọn
                        </button>
                    </div>
                `;
                editParent.replaceChild(newEditWrapper, editEmpty);
                editList = document.getElementById('editVocabList');
            }
        }

        if (editList) {
            const editItem = document.createElement('div');
            editItem.innerHTML = `
                <label class="tv-edit-vocab-item" for="select-vocab-${vocab.vocabulary_id}" id="edit-vocab-item-${vocab.vocabulary_id}">
                    <input type="checkbox"
                           class="tv-edit-vocab-checkbox"
                           id="select-vocab-${vocab.vocabulary_id}"
                           value="${vocab.vocabulary_id}"
                           onchange="handleVocabCheckChange(this, '${vocab.vocabulary_id}')">
                    <div class="tv-edit-vocab-info">
                        <div class="tv-edit-vocab-word-row">
                            <span class="tv-edit-vocab-word">${escapeHtml(vocab.word)}</span>
                            ${vocab.part_of_speech ? `<span class="tv-edit-vocab-pos">${escapeHtml(vocab.part_of_speech)}</span>` : ''}
                        </div>
                        <div class="tv-edit-vocab-meaning">${escapeHtml(vocab.meaning_vi)}</div>
                    </div>
                    <span class="tv-edit-vocab-tag-delete">Sẽ xóa</span>
                </label>
            `;
            if (editItem.firstElementChild) {
                editList.appendChild(editItem.firstElementChild);
            }
            updateSelectedVocabCount();
        }

        // Cập nhật số lượng hiển thị trên trang
        const total = typeof totalVocabularies !== 'undefined'
            ? totalVocabularies
            : document.querySelectorAll('.tv-vocab-card').length;

        const countEl = document.querySelector('.tv-topic-vocab-count');
        if (countEl) {
            countEl.textContent = total + ' từ vựng';
        }

        recalculateTabCounts();

        // Đảm bảo hiển thị nếu đang ở tab 'all'
        if (currentFilter !== 'all') {
            const activeTab = document.querySelector('.tv-filter-tab-btn.is-active');
            setFilterTab(currentFilter, activeTab);
        } else {
            const emptyState = document.getElementById('emptyFilterState');
            if (emptyState) emptyState.style.display = 'none';
            if (listContainer) listContainer.style.display = 'flex';
        }
    }

    // Đăng ký sự kiện mở modal từ nút "+ THÊM TỪ VỰNG"
    const btnAddVocab = document.getElementById('btnAddVocabulary');
    if (btnAddVocab) {
        btnAddVocab.addEventListener('click', function (e) {
            e.preventDefault();
            openCreateVocabModal();
        });
    }

    // Đăng ký sự kiện đóng modal từ nút Hủy và biểu tượng X
    const btnCancelCreateVocab = document.getElementById('btnCancelCreateVocab');
    if (btnCancelCreateVocab) {
        btnCancelCreateVocab.addEventListener('click', function (e) {
            e.preventDefault();
            closeCreateVocabModal();
        });
    }

    const btnCloseVocabModal = document.getElementById('btnCloseVocabModal');
    if (btnCloseVocabModal) {
        btnCloseVocabModal.addEventListener('click', function (e) {
            e.preventDefault();
            closeCreateVocabModal();
        });
    }

    // Đăng ký submit form khi nhấn nút "+ Thêm từ vựng" / "Lưu thay đổi"
    const btnSubmitCreateVocab = document.getElementById('btnSubmitCreateVocab');
    if (btnSubmitCreateVocab) {
        btnSubmitCreateVocab.addEventListener('click', function (e) {
            e.preventDefault();
            handleCreateVocabSubmit();
        });
    }

    // Đóng modal khi bấm phím Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            document.querySelectorAll('.tv-vocab-dropdown').forEach(m => m.classList.remove('show'));

            const modal = document.getElementById('modalCreateVocabulary');
            if (modal && !modal.hidden) {
                closeCreateVocabModal();
            }

            const deleteSingleModal = document.getElementById('modalConfirmDeleteSingleVocab');
            if (deleteSingleModal && !deleteSingleModal.hidden) {
                closeDeleteSingleVocabModal();
            }
        }
    });

    // Đóng modal khi click ra lớp nền mờ overlay
    const vocabModalEl = document.getElementById('modalCreateVocabulary');
    if (vocabModalEl) {
        const backdrop = vocabModalEl.querySelector('.sp-modal__backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                closeCreateVocabModal();
            });
        }
    }

    const deleteSingleModalEl = document.getElementById('modalConfirmDeleteSingleVocab');
    if (deleteSingleModalEl) {
        const backdrop = deleteSingleModalEl.querySelector('.sp-modal__backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                closeDeleteSingleVocabModal();
            });
        }
    }

    // Tự động xóa lỗi tức thì khi người dùng bắt đầu nhập giá trị hợp lệ
    const liveCheckFields = [
        { id: 'vocabWordInput', check: function (v) { return v.trim().length > 0 && v.trim().length <= 150; } },
        { id: 'vocabMeaningViInput', check: function (v) { return v.trim().length > 0; } },
        { id: 'vocabPronunciationInput', check: function (v) { return v.trim().length <= 150; } },
        { id: 'vocabPosInput', check: function (v) { return v.trim().length <= 30; } },
        { id: 'vocabImageUrlInput', check: function (v) { return v.trim().length === 0 || (v.trim().length <= 500 && isValidHttpUrl(v.trim())); } }
    ];

    liveCheckFields.forEach(function (item) {
        const fieldEl = document.getElementById(item.id);
        if (fieldEl) {
            const evtName = fieldEl.tagName === 'SELECT' ? 'change' : 'input';
            fieldEl.addEventListener(evtName, function () {
                if (item.check(this.value)) {
                    clearVocabFieldError(this);
                }
            });
        }
    });
</script>
@endpush

</x-layouts.app>

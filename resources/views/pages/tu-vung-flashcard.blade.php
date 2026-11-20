<x-layouts.app active="tu-vung" :title="'Flashcard - ' . ($topic['name'] ?? 'Học từ vựng')">

@push('styles')
    {{-- CSS riêng cho trang Flashcard và các màn hình Luyện tập --}}
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung-flashcard.css') }}">
@endpush

<div class="tv-fc-container">

    {{-- ===== 1. HEADER: QUAY LẠI, TIÊU ĐỀ & TIẾN ĐỘ ===== --}}
    <div class="tv-fc-header">
        <div class="tv-fc-header-left">
            <a href="{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}"
               class="tv-fc-back-btn"
               id="fcHeaderBackBtn"
               title="Quay lại chọn từ"
               aria-label="Quay lại chọn từ"
               onclick="handleHeaderBack(event)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>

            <div>
                <h1 class="tv-fc-title" id="fcHeaderTitle">Flashcard: {{ $topic['name'] ?? 'Luyện từ mới' }}</h1>
                <div class="tv-fc-topic-sub" id="fcHeaderSub">Học từ vựng và câu ví dụ</div>
            </div>
        </div>

        @if(count($vocabularies) > 0)
            <div class="tv-fc-progress-badge" aria-label="Tiến độ học">
                <span id="fcCurrentIdxText">1</span> / <span id="fcTotalCountText">{{ count($vocabularies) }}</span> từ
            </div>
        @endif
    </div>

    @if(count($vocabularies) > 0)
        {{-- Thanh tiến độ thị giác --}}
        <div class="tv-fc-progress-track" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="{{ count($vocabularies) }}">
            <div class="tv-fc-progress-fill" id="fcProgressBar" style="width: {{ count($vocabularies) > 0 ? (1 / count($vocabularies) * 100) : 0 }}%;"></div>
        </div>

        {{-- ========================================================= --}}
        {{-- ===== MÀN HÌNH 1: HỌC FLASHCARD (Tham chiếu Ảnh 1) ===== --}}
        {{-- ========================================================= --}}
        <div class="tv-screen-view" id="screenFlashcard">
            <div class="tv-fc-card-wrapper">
                {{-- Sân khấu thẻ 3D lật mặt trước / sau --}}
                <div class="tv-fc-stage-modern">
                    <div class="tv-fc-card-modern" id="fcCardElement" onclick="toggleFlipCard()" tabindex="0" role="button" aria-label="Thẻ từ vựng. Nhấn để lật thẻ.">

                        {{-- MẶT TRƯỚC (FRONT) --}}
                        <div class="tv-fc-face-modern tv-fc-face-modern--front">
                            {{-- Cặp nút phát âm: Loa chuẩn (Vàng) & Ốc sên nghe chậm --}}
                            <div class="tv-card-audio-group">
                                <button type="button"
                                        class="tv-audio-round-btn"
                                        id="fcAudioNormalFront"
                                        title="Nghe phát âm chuẩn"
                                        aria-label="Nghe phát âm chuẩn"
                                        onclick="speakCurrentWord(event, 0.9)">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="#FBBF24"></polygon>
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                    </svg>
                                </button>

                                <button type="button"
                                        class="tv-audio-round-btn tv-audio-round-btn--sm"
                                        id="fcAudioSlowFront"
                                        title="Nghe phát âm chậm"
                                        aria-label="Nghe phát âm chậm"
                                        onclick="speakCurrentWord(event, 0.55)">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 17C4 14 7 14 9 14C11 14 13 14 15 14C17.5 14 20 15 20 17C20 18.5 18 19 14 19H7C5 19 4 18.5 4 17Z" fill="#FBBF24" stroke="#D97706" stroke-width="1.5"/>
                                        <circle cx="11" cy="12" r="5" fill="#F59E0B" stroke="#D97706" stroke-width="1.5"/>
                                        <path d="M11 9.5C12.5 9.5 13.5 10.5 13.5 12C13.5 13 12.5 13.5 11.5 13.5" stroke="#FFFBEB" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M18 15L20 11M20 15L21.5 12" stroke="#D97706" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Nội dung chính: Từ tiếng Anh, phiên âm, nghĩa tiếng Việt & từ loại --}}
                            <div class="tv-fc-modern-body">
                                <h2 class="tv-fc-modern-word" id="fcFrontWord">examination</h2>
                                <p class="tv-fc-modern-phonetic" id="fcFrontPhonetic">/ɪɡˌzæmɪˈneɪʃn/</p>
                                <p class="tv-fc-modern-meaning" id="fcFrontMeaningPos">Kiểm tra, kỳ thi (n)</p>

                                {{-- Ảnh minh họa nếu có trong CSDL --}}
                                <div class="tv-fc-modern-image-wrap" id="fcFrontImageWrap" style="display: none;">
                                    <img id="fcFrontImage" src="" alt="Minh họa từ vựng" />
                                </div>
                            </div>

                            {{-- Biểu tượng chạm tay lật thẻ ở góc phải --}}
                            <div class="tv-fc-tap-hint">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2L12.5 3.8L14.3 4.3L12.5 4.8L12 6.6L11.5 4.8L9.7 4.3L11.5 3.8L12 2Z" fill="#FBBF24"/>
                                    <path d="M14 12V8.5C14 7.67 13.33 7 12.5 7C11.67 7 11 7.67 11 8.5V14.5L9.6 13.2C8.98 12.65 8.02 12.7 7.47 13.32C6.98 13.88 7.04 14.72 7.6 15.22L11.4 18.67C12.18 19.38 13.2 19.78 14.25 19.78H17C18.66 19.78 20 18.44 20 16.78V14C20 12.9 19.1 12 18 12H14Z" fill="#FED7AA" stroke="#EA580C" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Chạm để lật thẻ</span>
                            </div>
                        </div>

                        {{-- MẶT SAU (BACK) - Chi tiết & câu ví dụ --}}
                        <div class="tv-fc-face-modern tv-fc-face-modern--back">
                            <div class="tv-fc-back-header">
                                <div class="tv-fc-back-word-row">
                                    <div>
                                        <h3 class="tv-fc-back-word" id="fcBackWord">examination</h3>
                                        <p class="tv-fc-back-phonetic" id="fcBackPhonetic">/ɪɡˌzæmɪˈneɪʃn/</p>
                                    </div>
                                    <button type="button"
                                            class="tv-audio-round-btn tv-audio-round-btn--sm"
                                            title="Nghe phát âm"
                                            onclick="speakCurrentWord(event, 0.9)">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.3">
                                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="#FBBF24"></polygon>
                                            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="tv-fc-back-body">
                                <div>
                                    <div class="tv-fc-back-meaning-label">Nghĩa tiếng Việt</div>
                                    <div class="tv-fc-back-meaning-val" id="fcBackMeaning">Kiểm tra, kỳ thi</div>
                                </div>

                                {{-- Câu ví dụ song ngữ --}}
                                <div class="tv-fc-example-card" id="fcExampleContainer">
                                    <div class="tv-fc-example-en-line">
                                        <span id="fcExampleEn">The examination will start tomorrow.</span>
                                        <button type="button"
                                                class="tv-audio-round-btn tv-audio-round-btn--sm"
                                                style="width: 32px; height: 32px;"
                                                title="Nghe câu ví dụ"
                                                onclick="speakCurrentExample(event)">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.3">
                                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="#FBBF24"></polygon>
                                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="tv-fc-example-vi-line" id="fcExampleVi">Kỳ thi sẽ bắt đầu vào ngày mai.</div>
                                </div>
                            </div>

                            <div class="tv-fc-back-return-hint">
                                <span>Chạm vào thẻ để quay lại mặt trước</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Hai nút lựa chọn chính dưới thẻ Flashcard (Dùng x-button) --}}
                <div class="tv-fc-actions-column">
                    <x-button variant="success"
                              size="large"
                              id="btnFlashcardContinue"
                              onclick="handleFlashcardContinue()"
                              class="tv-fc-continue-btn">
                        Tiếp tục
                    </x-button>

                    <button type="button"
                            class="tv-fc-skip-text-btn"
                            id="btnFlashcardSkip"
                            onclick="handleFlashcardSkip()">
                        Mình đã thuộc từ này
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- ===== MÀN HÌNH 2: LUYỆN TẬP NGHE VÀ VIẾT LẠI (Tham chiếu Ảnh 2) ===== --}}
        {{-- ========================================================================= --}}
        <div class="tv-screen-view" id="screenListenWrite" style="display: none;">
            <div class="tv-practice-card">
                <div class="tv-practice-type-title">Nghe và viết lại</div>

                {{-- Khung phát âm trung tâm: Nút loa vàng lớn & Ốc sên chậm --}}
                <div class="tv-practice-audio-area">
                    <button type="button"
                            class="tv-practice-audio-big"
                            title="Nghe phát âm chuẩn"
                            aria-label="Nghe phát âm chuẩn"
                            onclick="speakCurrentWord(event, 0.9)">
                        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="#FBBF24"></polygon>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </button>

                    <button type="button"
                            class="tv-practice-audio-snail"
                            title="Nghe phát âm chậm"
                            aria-label="Nghe phát âm chậm"
                            onclick="speakCurrentWord(event, 0.55)">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 17C4 14 7 14 9 14C11 14 13 14 15 14C17.5 14 20 15 20 17C20 18.5 18 19 14 19H7C5 19 4 18.5 4 17Z" fill="#FBBF24" stroke="#D97706" stroke-width="1.5"/>
                            <circle cx="11" cy="12" r="5" fill="#F59E0B" stroke="#D97706" stroke-width="1.5"/>
                            <path d="M11 9.5C12.5 9.5 13.5 10.5 13.5 12C13.5 13 12.5 13.5 11.5 13.5" stroke="#FFFBEB" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M18 15L20 11M20 15L21.5 12" stroke="#D97706" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Ô input nhập từ nghe được --}}
                <div class="tv-practice-input-wrap">
                    <input type="text"
                           id="listenInput"
                           class="tv-practice-text-input"
                           placeholder="Nhập từ nghe được..."
                           autocomplete="off"
                           autocapitalize="off"
                           spellcheck="false"
                           oninput="handleListenInputChange()"
                           onkeydown="handlePracticeInputKeyDown(event, 'listen')" />
                </div>

                {{-- Nút Kiểm tra (Dùng x-button) --}}
                <div id="listenActionWrap" style="width: 100%; display: flex; justify-content: center;">
                    <x-button variant="success"
                              size="medium"
                              id="btnCheckListen"
                              disabled
                              onclick="checkListenAnswer()"
                              class="tv-practice-check-btn">
                        Kiểm tra
                    </x-button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- ===== MÀN HÌNH 3: LUYỆN TẬP ĐIỀN TỪ (Tham chiếu Ảnh 3) ===== --}}
        {{-- ================================================================= --}}
        <div class="tv-screen-view" id="screenFillBlank" style="display: none;">
            <div class="tv-practice-card">
                <div class="tv-practice-type-title">Điền từ</div>

                {{-- Gợi ý nghĩa tiếng Việt và từ loại --}}
                <h2 class="tv-fill-prompt-meaning" id="fillBlankPromptTitle">Hiệu trưởng (n)</h2>

                {{-- Hộp chứa các ô chữ cái và chữ còn thiếu (1 đường gạch dưới duy nhất cho mỗi ký tự) --}}
                <div class="tv-fill-slots-box" id="fillSlotsBox" onclick="focusFillInput()">
                    <div class="tv-fill-slots-content" id="fillSlotsContainer">
                        {{-- Render động bằng JavaScript --}}
                    </div>

                    {{-- Ô input thật để nhận phím gõ từ người dùng (nhập toàn bộ từ) --}}
                    <input type="text"
                           id="fillHiddenInput"
                           class="tv-fill-hidden-input"
                           autocomplete="off"
                           autocapitalize="off"
                           spellcheck="false"
                           oninput="handleFillInputChange(this.value)"
                           onkeydown="handlePracticeInputKeyDown(event, 'fill')" />
                </div>

                {{-- Nút Kiểm tra (Dùng x-button) --}}
                <div id="fillActionWrap" style="width: 100%; display: flex; justify-content: center;">
                    <x-button variant="success"
                              size="medium"
                              id="btnCheckFill"
                              disabled
                              onclick="checkFillAnswer()"
                              class="tv-practice-check-btn">
                        Kiểm tra
                    </x-button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- ===== FEEDBACK BOTTOM DRAWER (SLIDE-UP TỪ DƯỚI - ẢNH THAM CHIẾU MỚI) ===== --}}
        {{-- ========================================================================= --}}
        <div class="tv-feedback-drawer" id="feedbackDrawer">
            <div class="tv-feedback-card is-correct" id="feedbackCard">
                <div class="tv-feedback-inner">
                    <div class="tv-feedback-top-row">
                        <div class="tv-feedback-status-title" id="feedbackStatusTitle">
                            <span>Chính xác!</span>
                        </div>
                        <div class="tv-feedback-tools">
                            <button type="button" class="tv-feedback-icon-btn" title="Lưu từ vựng" aria-label="Lưu từ vựng">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </button>
                            <button type="button" class="tv-feedback-icon-btn" title="Thu gọn" aria-label="Thu gọn" onclick="closeFeedbackDrawer()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="tv-feedback-main">
                        <button type="button"
                                class="tv-feedback-speaker-btn"
                                title="Nghe lại"
                                onclick="speakCurrentWord(event, 0.9)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.3">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="#FBBF24"></polygon>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>

                        <div class="tv-feedback-details">
                            <h3 class="tv-feedback-word-title" id="feedbackWordTitle">principal (n)</h3>
                            <p class="tv-feedback-phonetic" id="feedbackPhonetic">/ˈprɪnsəpl/</p>
                            <p class="tv-feedback-meaning" id="feedbackMeaning">Hiệu trưởng</p>

                            <div class="tv-feedback-example" id="feedbackExampleWrap">
                                <span id="feedbackExampleEn">When two boys fought in class, the teacher sent them to the <strong>principal</strong>'s office.</span>
                                <div class="tv-feedback-example-vi" id="feedbackExampleVi">Khi hai bạn nam đánh nhau trong lớp, giáo viên đã gửi họ tới văn phòng hiệu trưởng.</div>
                            </div>

                            <button type="button" class="tv-feedback-trans-btn" onclick="toggleFeedbackTranslation()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 8l6 6"></path><path d="M4 14l6-6 2-3"></path><path d="M2 5h12"></path><path d="M7 2h1"></path>
                                    <path d="M22 22l-5-10-5 10"></path><path d="M14 18h6"></path>
                                </svg>
                                <span>Xem dịch nghĩa câu ví dụ</span>
                            </button>
                        </div>
                    </div>

                    <div class="tv-feedback-continue-wrap">
                        <x-button variant="neutral"
                                  size="medium"
                                  id="btnFeedbackContinue"
                                  onclick="handleFeedbackContinue()"
                                  class="tv-feedback-continue-btn">
                            Tiếp tục
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- ===== MÀN HÌNH 4: HOÀN THÀNH PHIÊN HỌC (COMPLETE SCREEN) ===== --}}
        {{-- ========================================================================= --}}
        <div class="tv-screen-view" id="screenCompletion" style="display: none;">
            <div class="tv-fc-complete-card">
                <div class="tv-fc-complete-icon-wrap">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                </div>

                <h2 class="tv-fc-complete-title">Xuất sắc! Bạn đã hoàn thành phiên học</h2>
                <p class="tv-fc-complete-desc">
                    Bạn đã ôn luyện toàn bộ các từ vựng đã chọn trong chủ đề <strong>{{ $topic['name'] ?? 'từ vựng' }}</strong>.
                </p>

                <div class="tv-fc-complete-stats">
                    <div class="tv-fc-stat-item">
                        <span class="tv-fc-stat-val" id="fcStatCount">{{ count($vocabularies) }}</span>
                        <span class="tv-fc-stat-label">Từ đã ôn luyện</span>
                    </div>
                    <div class="tv-fc-stat-item">
                        <span class="tv-fc-stat-val">100%</span>
                        <span class="tv-fc-stat-label">Tiến độ phiên học</span>
                    </div>
                </div>

                <div class="tv-fc-complete-actions">
                    <x-button variant="primary" size="medium" onclick="restartFlashcards()">
                        ↻ Học lại phiên này
                    </x-button>

                    <x-button variant="neutral" size="medium" href="{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}">
                        Chọn từ khác
                    </x-button>

                    <x-button variant="neutral" size="medium" href="{{ route('tu-vung.show', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}">
                        Về danh sách từ vựng
                    </x-button>
                </div>
            </div>
        </div>

    @else
        {{-- Trạng thái không có từ nào để học --}}
        <div class="tv-fc-complete-card" style="display: flex;">
            <div class="tv-fc-complete-icon-wrap" style="background: #FEE2E2; color: #DC2626;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h2 class="tv-fc-complete-title">Chưa có từ vựng nào được chọn</h2>
            <p class="tv-fc-complete-desc">Hãy quay lại trang chọn từ để chọn các từ vựng bạn muốn học qua Flashcard.</p>
            <x-button variant="primary" size="medium" href="{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? 'test-1']) }}">
                Quay lại chọn từ
            </x-button>
        </div>
    @endif

</div>

@push('scripts')
<script>
    // 1. Dữ liệu từ vựng của phiên học Flashcard từ Backend BE-004
    const flashcardList = @json($vocabularies);
    const totalCount = flashcardList.length;

    // Trạng thái phiên học trên Frontend
    let currentIndex = 0;
    let isFlipped = false;
    let currentScreen = 'flashcard'; // 'flashcard' | 'listenWrite' | 'fillBlank' | 'completion'
    let isSubmitting = false; // Tránh gửi request trùng lặp khi người dùng click/Enter liên tục

    // Trạng thái cho màn hình điền từ (Screen 3):
    // Người dùng nhập TOÀN BỘ từ tiếng Anh vào input thật, các ô hiển thị theo ký tự người dùng gõ
    let fillBlankState = {
        word: '',         // Từ gốc chuẩn
        hintIndices: [],  // Các vị trí hiển thị chữ cái gợi ý ban đầu
        userTyped: '',    // Toàn bộ chuỗi người học đã nhập
    };

    // Khởi tạo phiên học khi tải trang
    document.addEventListener('DOMContentLoaded', function () {
        if (totalCount > 0) {
            initFlashcardView();
        }

        // Đảm bảo focus/blur mượt mà trên ô điền từ
        const fillInput = document.getElementById('fillHiddenInput');
        if (fillInput) {
            fillInput.addEventListener('focus', () => {
                const box = document.getElementById('fillSlotsBox');
                if (box) box.classList.add('is-focused');
            });
            fillInput.addEventListener('blur', () => {
                const box = document.getElementById('fillSlotsBox');
                if (box) box.classList.remove('is-focused');
            });
        }
    });

    // 2. Chuyển đổi hiển thị màn hình
    function showScreen(screenId) {
        currentScreen = screenId;
        const screens = {
            'flashcard': document.getElementById('screenFlashcard'),
            'listenWrite': document.getElementById('screenListenWrite'),
            'fillBlank': document.getElementById('screenFillBlank'),
            'completion': document.getElementById('screenCompletion')
        };

        Object.keys(screens).forEach(key => {
            if (screens[key]) {
                screens[key].style.display = (key === screenId) ? 'block' : 'none';
            }
        });

        // Ẩn Feedback Drawer khi chuyển màn hình
        closeFeedbackDrawer();

        // Cập nhật tiêu đề phụ header tùy theo màn hình
        const subTitle = document.getElementById('fcHeaderSub');
        if (subTitle) {
            if (screenId === 'flashcard') subTitle.textContent = 'Lật thẻ để ghi nhớ từ vựng và câu ví dụ';
            else if (screenId === 'listenWrite') subTitle.textContent = 'Nghe phát âm và nhập lại từ vựng';
            else if (screenId === 'fillBlank') subTitle.textContent = 'Quan sát gợi ý và điền các chữ cái còn thiếu';
            else if (screenId === 'completion') subTitle.textContent = 'Hoàn thành phiên học xuất sắc!';
        }
    }

    // Xử lý nút Back trên Header: Nếu đang ở màn hình luyện tập thì quay về Flashcard của từ hiện tại
    function handleHeaderBack(e) {
        if (currentScreen === 'listenWrite' || currentScreen === 'fillBlank') {
            e.preventDefault();
            initFlashcardView();
        }
        // Nếu đang ở 'flashcard' hoặc 'completion' thì để thẻ <a> tự điều hướng về tu-vung.chon-tu
    }

    // 3. Khởi tạo & hiển thị thẻ Flashcard (Bước 1 của từ hiện tại)
    function initFlashcardView() {
        if (currentIndex < 0 || currentIndex >= totalCount) return;

        showScreen('flashcard');
        const vocab = flashcardList[currentIndex];
        const cardElem = document.getElementById('fcCardElement');

        // Reset trạng thái lật thẻ về mặt trước
        if (cardElem && isFlipped) {
            cardElem.classList.remove('is-flipped');
            isFlipped = false;
        }

        // Cập nhật số đếm tiến độ
        updateProgressIndicators();

        // Gán dữ liệu Mặt trước
        const fWord = document.getElementById('fcFrontWord');
        const fPhonetic = document.getElementById('fcFrontPhonetic');
        const fMeaningPos = document.getElementById('fcFrontMeaningPos');
        const fImgWrap = document.getElementById('fcFrontImageWrap');
        const fImg = document.getElementById('fcFrontImage');

        const posTag = vocab.part_of_speech ? ` (${formatPos(vocab.part_of_speech)})` : '';
        const meaningText = (vocab.meaning_vi || '') + posTag;

        if (fWord) fWord.textContent = vocab.word || '';
        if (fPhonetic) fPhonetic.textContent = vocab.pronunciation || '';
        if (fMeaningPos) fMeaningPos.textContent = meaningText;

        if (fImgWrap && fImg) {
            if (vocab.image_url) {
                fImg.src = vocab.image_url;
                fImgWrap.style.display = 'block';
            } else {
                fImgWrap.style.display = 'none';
            }
        }

        // Gán dữ liệu Mặt sau
        const bWord = document.getElementById('fcBackWord');
        const bPhonetic = document.getElementById('fcBackPhonetic');
        const bMeaning = document.getElementById('fcBackMeaning');
        const bExampleEn = document.getElementById('fcExampleEn');
        const bExampleVi = document.getElementById('fcExampleVi');

        if (bWord) bWord.textContent = vocab.word || '';
        if (bPhonetic) bPhonetic.textContent = vocab.pronunciation || '';
        if (bMeaning) bMeaning.textContent = vocab.meaning_vi || '';

        // Highlight từ vựng trong câu ví dụ
        if (bExampleEn) {
            const enText = vocab.example_en || '';
            const targetWord = vocab.word || '';
            if (targetWord && enText) {
                const regex = new RegExp('\\b(' + escapeRegExp(targetWord) + '[a-z]*)\\b', 'gi');
                bExampleEn.innerHTML = escapeHtml(enText).replace(regex, '<span class="tv-fc-highlight-term">$1</span>');
            } else {
                bExampleEn.textContent = enText;
            }
        }
        if (bExampleVi) bExampleVi.textContent = vocab.example_vi || '';
    }

    // 4. Lật thẻ (Toggle Flip Card)
    function toggleFlipCard() {
        const cardElem = document.getElementById('fcCardElement');
        if (!cardElem) return;

        isFlipped = !isFlipped;
        if (isFlipped) {
            cardElem.classList.add('is-flipped');
            cardElem.setAttribute('aria-label', 'Mặt sau của thẻ từ vựng. Nhấn để quay lại mặt trước.');
        } else {
            cardElem.classList.remove('is-flipped');
            cardElem.setAttribute('aria-label', 'Mặt trước của thẻ từ vựng. Nhấn để lật mặt sau.');
        }
    }

    // 5. Nút "Mình đã thuộc từ này" (Bỏ qua từ hiện tại, sang Flashcard tiếp theo)
    function handleFlashcardSkip() {
        if (currentIndex < totalCount - 1) {
            currentIndex++;
            initFlashcardView();
        } else {
            showCompletionScreen();
        }
    }

    // 6. Nút "Tiếp tục" từ Flashcard: LUÔN vào Bài 1: Nghe và điền lại từ
    function handleFlashcardContinue() {
        setupListenWriteScreen();
    }

    // =========================================================================
    // ===== BÀI LUYỆN TẬP 1: NGHE VÀ ĐIỀN LẠI TỪ (Tham chiếu Ảnh 2) =====
    // =========================================================================
    function setupListenWriteScreen() {
        showScreen('listenWrite');

        const input = document.getElementById('listenInput');
        const btnCheck = document.getElementById('btnCheckListen');

        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 120);
        }
        if (btnCheck) {
            btnCheck.disabled = true;
            btnCheck.classList.remove('is-active');
        }

        // Tự động phát âm để người học nghe
        setTimeout(() => {
            speakCurrentWord(null, 0.9);
        }, 200);
    }

    function handleListenInputChange() {
        const input = document.getElementById('listenInput');
        const btnCheck = document.getElementById('btnCheckListen');
        if (!input || !btnCheck) return;

        const val = input.value.trim();
        if (val.length > 0) {
            btnCheck.disabled = false;
            btnCheck.classList.add('is-active');
        } else {
            btnCheck.disabled = true;
            btnCheck.classList.remove('is-active');
        }
    }

    // Hàm gửi đáp án lên Backend kiểm tra và cập nhật CSDL (BE-008.1)
    async function submitPracticeAnswer(practiceType, rawAnswer) {
        if (isSubmitting) return;

        const drawer = document.getElementById('feedbackDrawer');
        if (drawer && drawer.classList.contains('is-open')) return;

        const vocab = flashcardList[currentIndex];
        if (!vocab) return;

        const answer = (rawAnswer || '').trim();
        if (!answer) return;

        isSubmitting = true;

        // Vô hiệu hóa nút Kiểm tra trong khi đang xử lý để ngăn gửi trùng
        const btnCheckListen = document.getElementById('btnCheckListen');
        const btnCheckFill = document.getElementById('btnCheckFill');
        if (btnCheckListen) btnCheckListen.disabled = true;
        if (btnCheckFill) btnCheckFill.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '{{ csrf_token() }}';

        try {
            const response = await fetch('{{ route("tu-vung.check-answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    vocabulary_id: vocab.vocabulary_id,
                    practice_type: practiceType,
                    answer: answer
                })
            });

            if (response.status === 401) {
                if (typeof window.spOpenAuthModal === 'function') {
                    window.spOpenAuthModal();
                } else {
                    alert('Vui lòng đăng nhập để lưu kết quả luyện tập.');
                }
                return;
            }

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                console.error('Error submitting practice answer:', errData);
                alert(errData.message || 'Đã có lỗi xảy ra khi kiểm tra đáp án.');
                return;
            }

            const data = await response.json();
            showFeedbackResult(Boolean(data.is_correct), vocab);
        } catch (error) {
            console.error('Network error checking answer:', error);
            alert('Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng.');
        } finally {
            isSubmitting = false;

            // Nếu Feedback Drawer không mở (ví dụ lỗi mạng), kích hoạt lại nút
            const isDrawerOpen = drawer && drawer.classList.contains('is-open');
            if (!isDrawerOpen) {
                if (practiceType === 'listen') {
                    handleListenInputChange();
                } else if (practiceType === 'fill') {
                    const fillInput = document.getElementById('fillHiddenInput');
                    if (fillInput) handleFillInputChange(fillInput.value);
                }
            }
        }
    }

    function checkListenAnswer() {
        if (isSubmitting) return;

        const drawer = document.getElementById('feedbackDrawer');
        if (drawer && drawer.classList.contains('is-open')) return;

        const input = document.getElementById('listenInput');
        if (!input || !input.value.trim()) return;

        submitPracticeAnswer('listen', input.value);
    }

    // =========================================================================
    // ===== BÀI LUYỆN TẬP 2: ĐIỀN TỪ CÒN TRỐNG (Tham chiếu Ảnh 3 & Yêu cầu mới) =====
    // =========================================================================
    function setupFillBlankScreen() {
        showScreen('fillBlank');

        const vocab = flashcardList[currentIndex];
        const promptTitle = document.getElementById('fillBlankPromptTitle');
        const btnCheck = document.getElementById('btnCheckFill');

        const posTag = vocab.part_of_speech ? ` (${formatPos(vocab.part_of_speech)})` : '';
        if (promptTitle) {
            promptTitle.textContent = (vocab.meaning_vi || '') + posTag;
        }

        if (btnCheck) {
            btnCheck.disabled = true;
            btnCheck.classList.remove('is-active');
        }

        setupFillSlots(vocab.word);

        setTimeout(() => {
            focusFillInput();
        }, 120);
    }

    function setupFillSlots(word) {
        const cleanWord = (word || '').trim();
        const len = cleanWord.length;

        // Xác định vị trí các ký tự gợi ý ban đầu
        const hintIndices = [];
        if (len > 0) hintIndices.push(0);           // Chữ cái đầu tiên
        if (len >= 6) hintIndices.push(len - 2);     // Chữ cái gần cuối (ví dụ deadline -> d và n)

        fillBlankState = {
            word: cleanWord,
            hintIndices: hintIndices,
            userTyped: ''
        };

        const hiddenInput = document.getElementById('fillHiddenInput');
        if (hiddenInput) {
            hiddenInput.value = '';
            hiddenInput.maxLength = len;
        }

        renderFillSlots();
    }

    function renderFillSlots() {
        const container = document.getElementById('fillSlotsContainer');
        if (!container) return;

        const { word, hintIndices, userTyped } = fillBlankState;
        container.innerHTML = '';

        const activeIdx = userTyped.length; // Vị trí ký tự tiếp theo người dùng sẽ gõ

        for (let i = 0; i < word.length; i++) {
            const char = word[i];
            const slotItem = document.createElement('div');
            slotItem.className = 'tv-slot-item';

            if (char === ' ') {
                slotItem.innerHTML = '<span class="tv-slot-char">&nbsp;</span>';
            } else if (char === '-') {
                slotItem.innerHTML = `
                    <span class="tv-slot-char">-</span>
                    <div class="tv-slot-underline"></div>
                `;
            } else if (i < userTyped.length) {
                // Người dùng ĐÃ gõ ký tự vào vị trí này -> hiển thị chữ người dùng gõ
                const typedChar = userTyped[i];
                slotItem.classList.add('is-filled');
                slotItem.innerHTML = `
                    <span class="tv-slot-char is-filled">${escapeHtml(typedChar.toLowerCase())}</span>
                    <div class="tv-slot-underline"></div>
                `;
            } else if (hintIndices.includes(i)) {
                // Vị trí có chữ gợi ý ban đầu (chưa bị người dùng gõ tới)
                slotItem.classList.add('is-hint');
                slotItem.innerHTML = `
                    <span class="tv-slot-char is-hint">${escapeHtml(char.toLowerCase())}</span>
                    <div class="tv-slot-underline"></div>
                `;
            } else {
                // Vị trí ô trống chưa gõ -> CHỈ có 1 đường gạch dưới, KHÔNG có ký tự '_' lặp
                slotItem.classList.add('is-blank');
                slotItem.innerHTML = `
                    <span class="tv-slot-char is-blank">&nbsp;</span>
                    <div class="tv-slot-underline"></div>
                `;
            }

            // Hiển thị con trỏ cam indicator tại vị trí đang nhập
            if (i === activeIdx && activeIdx < word.length) {
                slotItem.classList.add('is-active');
                const caret = document.createElement('div');
                caret.className = 'tv-slot-caret-indicator';
                slotItem.appendChild(caret);
            }

            container.appendChild(slotItem);
        }
    }

    function handleFillInputChange(val) {
        // Chỉ nhận các ký tự chữ cái tiếng Anh
        let cleanVal = (val || '').toLowerCase().replace(/[^a-zA-Z]/g, '');
        const maxLen = fillBlankState.word.length;
        if (cleanVal.length > maxLen) {
            cleanVal = cleanVal.slice(0, maxLen);
        }

        fillBlankState.userTyped = cleanVal;

        const hiddenInput = document.getElementById('fillHiddenInput');
        if (hiddenInput && hiddenInput.value !== cleanVal) {
            hiddenInput.value = cleanVal;
        }

        renderFillSlots();

        // Kích hoạt nút Kiểm tra khi người dùng đã gõ ít nhất 1 chữ cái
        const btnCheck = document.getElementById('btnCheckFill');
        if (btnCheck) {
            const hasLetters = cleanVal.length > 0;
            btnCheck.disabled = !hasLetters;
            if (hasLetters) {
                btnCheck.classList.add('is-active');
            } else {
                btnCheck.classList.remove('is-active');
            }
        }
    }

    function focusFillInput() {
        const input = document.getElementById('fillHiddenInput');
        const box = document.getElementById('fillSlotsBox');
        if (input) {
            input.focus();
            const len = input.value.length;
            try {
                input.setSelectionRange(len, len);
            } catch (err) {}
        }
        if (box) box.classList.add('is-focused');
    }

    function checkFillAnswer() {
        if (isSubmitting) return;

        const drawer = document.getElementById('feedbackDrawer');
        if (drawer && drawer.classList.contains('is-open')) return;

        const input = document.getElementById('fillHiddenInput');
        if (!input || !input.value.trim()) return;

        submitPracticeAnswer('fill', input.value);
    }

    // =========================================================================
    // ===== FEEDBACK DRAWER TỪ DƯỚI LÊN & ĐIỀU HƯỚNG THEO LUỒNG 2 BÀI TẬP =====
    // =========================================================================
    function showFeedbackResult(isCorrect, vocab) {
        const drawer = document.getElementById('feedbackDrawer');
        const card = document.getElementById('feedbackCard');
        const statusTitle = document.getElementById('feedbackStatusTitle');

        const wordTitle = document.getElementById('feedbackWordTitle');
        const phonetic = document.getElementById('feedbackPhonetic');
        const meaning = document.getElementById('feedbackMeaning');
        const exampleEn = document.getElementById('feedbackExampleEn');
        const exampleVi = document.getElementById('feedbackExampleVi');

        if (card) {
            card.className = `tv-feedback-card ${isCorrect ? 'is-correct' : 'is-incorrect'}`;
        }

        if (statusTitle) {
            statusTitle.innerHTML = isCorrect
                ? '<span>Chính xác!</span>'
                : '<span>Chưa chính xác!</span>';
        }

        const posTag = vocab.part_of_speech ? ` (${formatPos(vocab.part_of_speech)})` : '';
        if (wordTitle) wordTitle.textContent = (vocab.word || '') + posTag;
        if (phonetic) phonetic.textContent = vocab.pronunciation || '';
        if (meaning) meaning.textContent = vocab.meaning_vi || '';

        if (exampleEn) {
            const enText = vocab.example_en || '';
            const targetWord = vocab.word || '';
            if (targetWord && enText) {
                const regex = new RegExp('\\b(' + escapeRegExp(targetWord) + '[a-z]*)\\b', 'gi');
                exampleEn.innerHTML = escapeHtml(enText).replace(regex, '<strong>$1</strong>');
            } else {
                exampleEn.textContent = enText;
            }
        }

        if (exampleVi) {
            exampleVi.textContent = vocab.example_vi || '';
            exampleVi.style.display = 'none';
        }

        if (drawer) {
            drawer.classList.add('is-open');
        }

        // Tự động phát âm củng cố
        speakCurrentWord(null, 0.9);
    }

    function closeFeedbackDrawer() {
        const drawer = document.getElementById('feedbackDrawer');
        if (drawer) {
            drawer.classList.remove('is-open');
        }
    }

    function toggleFeedbackTranslation() {
        const viElem = document.getElementById('feedbackExampleVi');
        if (!viElem) return;
        viElem.style.display = viElem.style.display === 'none' ? 'block' : 'none';
    }

    // Bấm "Tiếp tục" trong Feedback Drawer: Điều hướng luồng chuẩn
    function handleFeedbackContinue() {
        closeFeedbackDrawer();

        if (currentScreen === 'listenWrite') {
            // Bước 1 xong -> Luôn chuyển sang Bước 2: Điền từ còn trống của CHÍNH TỪ ĐÓ
            setupFillBlankScreen();
        } else if (currentScreen === 'fillBlank') {
            // Bước 2 xong -> Đã hoàn thành 2 bài luyện tập của từ hiện tại -> Chuyển sang TỪ TIẾP THEO
            if (currentIndex < totalCount - 1) {
                currentIndex++;
                initFlashcardView();
            } else {
                showCompletionScreen();
            }
        }
    }

    // 7. Cập nhật thanh tiến độ thị giác
    function updateProgressIndicators() {
        const idxText = document.getElementById('fcCurrentIdxText');
        const progressBar = document.getElementById('fcProgressBar');

        if (idxText) idxText.textContent = (currentIndex + 1);
        if (progressBar && totalCount > 0) {
            const pct = ((currentIndex + 1) / totalCount) * 100;
            progressBar.style.width = pct + '%';
        }
    }

    // 8. Màn hình chúc mừng hoàn thành
    function showCompletionScreen() {
        showScreen('completion');
        const statCount = document.getElementById('fcStatCount');
        if (statCount) statCount.textContent = totalCount;
    }

    function restartFlashcards() {
        currentIndex = 0;
        initFlashcardView();
    }

    // 9. Phát âm chuẩn bằng Web Speech API
    function speakCurrentWord(event, rate = 0.9) {
        if (event) event.stopPropagation();

        if (currentIndex < 0 || currentIndex >= totalCount) return;
        const vocab = flashcardList[currentIndex];
        const textToSpeak = vocab.word || '';

        if (!textToSpeak || !('speechSynthesis' in window)) return;

        window.speechSynthesis.cancel();
        const utter = new SpeechSynthesisUtterance(textToSpeak);
        utter.lang = 'en-US';
        utter.rate = rate;

        const btn = event ? event.currentTarget : null;
        if (btn) {
            btn.classList.add('is-playing');
            utter.onend = () => btn.classList.remove('is-playing');
            utter.onerror = () => btn.classList.remove('is-playing');
        }

        window.speechSynthesis.speak(utter);
    }

    function speakCurrentExample(event) {
        if (event) event.stopPropagation();

        if (currentIndex < 0 || currentIndex >= totalCount) return;
        const vocab = flashcardList[currentIndex];
        const textToSpeak = vocab.example_en || vocab.word || '';

        if (!textToSpeak || !('speechSynthesis' in window)) return;

        window.speechSynthesis.cancel();
        const utter = new SpeechSynthesisUtterance(textToSpeak);
        utter.lang = 'en-US';
        utter.rate = 0.9;
        window.speechSynthesis.speak(utter);
    }

    // 10. Xử lý bàn phím và phím tắt (Giải quyết triệt để lỗi Enter)
    function handlePracticeInputKeyDown(e, type) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation(); // Ngăn sự kiện nổi bọt lên document gây đóng banner ngay lập tức

            const drawer = document.getElementById('feedbackDrawer');
            const isDrawerOpen = drawer && drawer.classList.contains('is-open');

            if (isDrawerOpen) {
                // Nếu banner đang mở -> nhấn Enter sẽ chuyển tiếp
                handleFeedbackContinue();
            } else {
                if (isSubmitting) return; // Tránh gửi request trùng khi nhấn Enter liên tục
                // Nếu banner chưa mở -> nhấn Enter gọi chung hàm kiểm tra đáp án
                if (type === 'listen') {
                    checkListenAnswer();
                } else if (type === 'fill') {
                    checkFillAnswer();
                }
            }
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            // Nếu con trỏ đang trong ô input, sự kiện đã được handlePracticeInputKeyDown xử lý riêng
            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                return;
            }

            const drawer = document.getElementById('feedbackDrawer');
            const isDrawerOpen = drawer && drawer.classList.contains('is-open');

            if (isDrawerOpen) {
                e.preventDefault();
                handleFeedbackContinue();
                return;
            }

            if (currentScreen === 'flashcard') {
                e.preventDefault();
                handleFlashcardContinue();
            }
        } else if (e.code === 'Space') {
            if (currentScreen === 'flashcard' && e.target && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                toggleFlipCard();
            }
        }
    });

    // Helper utilities
    function cleanInputString(str) {
        return (str || '').toString().toLowerCase().trim().replace(/[^a-z0-9]/g, '');
    }

    function formatPos(pos) {
        if (!pos) return '';
        const map = {
            'noun': 'n',
            'verb': 'v',
            'adjective': 'adj',
            'adverb': 'adv',
            'preposition': 'prep',
            'conjunction': 'conj'
        };
        const lower = pos.toLowerCase();
        return map[lower] || lower;
    }

    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function escapeHtml(string) {
        const div = document.createElement('div');
        div.textContent = string;
        return div.innerHTML;
    }
</script>
@endpush

</x-layouts.app>

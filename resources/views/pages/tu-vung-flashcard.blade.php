<x-layouts.app active="tu-vung" :title="'Flashcard - ' . ($topic['name'] ?? 'Học từ vựng')">

@push('styles')
    {{-- CSS kế thừa và CSS riêng cho Flashcard --}}
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung-flashcard.css') }}">
@endpush

<div class="tv-fc-container">

    {{-- ===== 1. HEADER: QUAY LẠI, TIÊU ĐỀ & TIẾN ĐỘ ===== --}}
    <div class="tv-fc-header">
        <div class="tv-fc-header-left">
            <a href="{{ route('tu-vung.chon-tu', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}"
               class="tv-fc-back-btn"
               title="Quay lại chọn từ"
               aria-label="Quay lại chọn từ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>

            <div>
                <h1 class="tv-fc-title">Flashcard: {{ $topic['name'] ?? 'Luyện từ mới' }}</h1>
                <div class="tv-fc-topic-sub">Lật thẻ để ghi nhớ từ vựng và câu ví dụ</div>
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

        {{-- ===== 2. SÂN KHẤU THẺ FLASHCARD 3D ===== --}}
        <div class="tv-fc-stage" id="fcStageArea">
            <div class="tv-fc-card" id="fcCardElement" onclick="toggleFlipCard()" tabindex="0" role="button" aria-label="Thẻ từ vựng. Nhấn để lật mặt sau.">

                {{-- MẶT TRƯỚC (FRONT) --}}
                <div class="tv-fc-face tv-fc-face--front" id="fcFaceFront">
                    {{-- Hàng đầu: CEFR badge + Loại từ + Nút loa phát âm --}}
                    <div class="tv-fc-top-row">
                        <div class="tv-fc-meta-tags">
                            <span class="tv-fc-cefr-badge" id="fcFrontCefr">B1</span>
                            <span class="tv-fc-pos-badge" id="fcFrontPos">noun</span>
                        </div>

                        <button type="button"
                                class="tv-fc-speaker-btn"
                                id="fcSpeakerFront"
                                title="Nghe phát âm từ"
                                aria-label="Nghe phát âm"
                                onclick="speakWordAction(event, 'word')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Nội dung chính mặt trước: Từ tiếng Anh & Phiên âm --}}
                    <div class="tv-fc-center-content">
                        <h2 class="tv-fc-word-main" id="fcFrontWord">combine</h2>
                        <p class="tv-fc-phonetic-text" id="fcFrontPhonetic">/kəmˈbaɪn/</p>

                        <div class="tv-fc-card-illustration" aria-hidden="true">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Dưới đáy: Gợi ý lật thẻ --}}
                    <div class="tv-fc-bottom-hint">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        <span>Nhấn vào thẻ hoặc bấm phím Cách để lật mặt sau</span>
                    </div>
                </div>

                {{-- MẶT SAU (BACK) --}}
                <div class="tv-fc-face tv-fc-face--back" id="fcFaceBack">
                    {{-- Hàng đầu: CEFR badge + Loại từ + Từ gốc nhỏ + Nút loa --}}
                    <div class="tv-fc-top-row">
                        <div class="tv-fc-meta-tags">
                            <span class="tv-fc-cefr-badge" id="fcBackCefr">B1</span>
                            <span class="tv-fc-pos-badge" id="fcBackPos">noun</span>
                            <span style="font-weight: 800; color: #111C2D; font-size: 15px; margin-left: 4px;" id="fcBackWordSmall">combine</span>
                        </div>

                        <button type="button"
                                class="tv-fc-speaker-btn"
                                id="fcSpeakerBack"
                                title="Nghe phát âm từ"
                                aria-label="Nghe phát âm"
                                onclick="speakWordAction(event, 'word')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Nội dung chính mặt sau: Nghĩa tiếng Việt & Câu ví dụ --}}
                    <div class="tv-fc-back-center">
                        <div>
                            <span class="tv-fc-back-meaning-title">Nghĩa tiếng Việt</span>
                            <h2 class="tv-fc-meaning-text" id="fcBackMeaning">kết hợp</h2>
                        </div>

                        {{-- Hộp câu ví dụ song ngữ --}}
                        <div class="tv-fc-example-box" id="fcExampleContainer">
                            <div class="tv-fc-example-en-row">
                                <p class="tv-fc-example-en" id="fcExampleEn">
                                    <span class="tv-fc-highlight-term">combine</span> two departments effectively
                                </p>
                                <button type="button"
                                        class="tv-fc-audio-sm-btn"
                                        title="Nghe câu ví dụ"
                                        aria-label="Nghe câu ví dụ"
                                        onclick="speakWordAction(event, 'example')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="tv-fc-example-vi" id="fcExampleVi">kết hợp hai phòng ban một cách hiệu quả</p>
                        </div>
                    </div>

                    {{-- Dưới đáy: Gợi ý quay lại --}}
                    <div class="tv-fc-bottom-hint">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        <span>Nhấn vào thẻ để quay lại mặt trước</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===== 3. BỘ NÚT ĐIỀU HƯỚNG BÊN DƯỚI ===== --}}
        <div class="tv-fc-nav-bar" id="fcNavControls">
            <x-button variant="neutral"
                      size="medium"
                      id="btnPrevWord"
                      onclick="goToPrevWord()">
                ← Từ trước
            </x-button>

            <x-button variant="neutral"
                      size="medium"
                      id="btnFlipTrigger"
                      onclick="toggleFlipCard()">
                Lật thẻ ↻
            </x-button>

            <x-button variant="primary"
                      size="medium"
                      id="btnNextWord"
                      onclick="goToNextWord()">
                Từ tiếp theo →
            </x-button>
        </div>

        <div class="tv-fc-shortcuts-hint" id="fcShortcutsHint">
            Gợi ý phím tắt: <span class="tv-fc-key-tag">Space</span> Lật thẻ • <span class="tv-fc-key-tag">←</span> Từ trước • <span class="tv-fc-key-tag">→</span> Từ tiếp theo
        </div>

        {{-- ===== 4. MÀN HÌNH CHÚC MỪNG HOÀN THÀNH (COMPLETE VIEW) ===== --}}
        <div class="tv-fc-complete-card" id="fcCompleteView">
            <div class="tv-fc-complete-icon-wrap">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="7"></circle>
                    <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                </svg>
            </div>

            <h2 class="tv-fc-complete-title">Xuất sắc! Bạn đã hoàn thành phiên học</h2>
            <p class="tv-fc-complete-desc">
                Bạn đã ôn tập và nắm bắt toàn bộ các từ vựng đã chọn trong chủ đề <strong>{{ $topic['name'] ?? 'từ vựng' }}</strong>.
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
    // 1. Dữ liệu từ vựng của phiên học Flashcard
    const flashcardList = @json($vocabularies);
    const totalCount = flashcardList.length;
    let currentIndex = 0;
    let isFlipped = false;

    // CEFR badge colors
    const cefrColors = {
        'A1': '#64748B',
        'A2': '#0284C7',
        'B1': '#10B981',
        'B2': '#EF4444',
        'C1': '#F59E0B',
        'C2': '#D97706',
    };

    // Khởi tạo thẻ đầu tiên khi tải trang
    document.addEventListener('DOMContentLoaded', function () {
        if (totalCount > 0) {
            renderCurrentCard();
        }
    });

    // 2. Render dữ liệu từ hiện tại vào hai mặt của thẻ
    function renderCurrentCard() {
        if (currentIndex < 0 || currentIndex >= totalCount) return;

        const vocab = flashcardList[currentIndex];
        const cardElem = document.getElementById('fcCardElement');

        // Reset trạng thái lật thẻ về mặt trước khi đổi từ
        if (cardElem && isFlipped) {
            cardElem.classList.remove('is-flipped');
            isFlipped = false;
        }

        // Cập nhật số đếm tiến độ
        const idxText = document.getElementById('fcCurrentIdxText');
        const progressBar = document.getElementById('fcProgressBar');
        if (idxText) idxText.textContent = (currentIndex + 1);
        if (progressBar) {
            const pct = ((currentIndex + 1) / totalCount) * 100;
            progressBar.style.width = pct + '%';
        }

        const cefrColor = cefrColors[vocab.cefr_level] || '#10B981';

        // --- CẬP NHẬT MẶT TRƯỚC (FRONT) ---
        const fCefr = document.getElementById('fcFrontCefr');
        const fPos = document.getElementById('fcFrontPos');
        const fWord = document.getElementById('fcFrontWord');
        const fPhonetic = document.getElementById('fcFrontPhonetic');

        if (fCefr) {
            fCefr.textContent = vocab.cefr_level || 'B1';
            fCefr.style.backgroundColor = cefrColor;
        }
        if (fPos) fPos.textContent = vocab.part_of_speech || 'Từ vựng';
        if (fWord) fWord.textContent = vocab.word || '';
        if (fPhonetic) fPhonetic.textContent = vocab.pronunciation || '';

        // --- CẬP NHẬT MẶT SAU (BACK) ---
        const bCefr = document.getElementById('fcBackCefr');
        const bPos = document.getElementById('fcBackPos');
        const bWordSmall = document.getElementById('fcBackWordSmall');
        const bMeaning = document.getElementById('fcBackMeaning');
        const bExampleEn = document.getElementById('fcExampleEn');
        const bExampleVi = document.getElementById('fcExampleVi');

        if (bCefr) {
            bCefr.textContent = vocab.cefr_level || 'B1';
            bCefr.style.backgroundColor = cefrColor;
        }
        if (bPos) bPos.textContent = vocab.part_of_speech || 'Từ vựng';
        if (bWordSmall) bWordSmall.textContent = vocab.word || '';
        if (bMeaning) bMeaning.textContent = vocab.meaning_vi || '';

        // Highlight từ khóa trong câu ví dụ tiếng Anh
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

        // --- CẬP NHẬT NÚT ĐIỀU HƯỚNG ---
        const btnPrev = document.getElementById('btnPrevWord');
        const btnNext = document.getElementById('btnNextWord');

        if (btnPrev) {
            if (currentIndex === 0) {
                btnPrev.setAttribute('disabled', 'disabled');
                btnPrev.style.opacity = '0.5';
                btnPrev.style.pointerEvents = 'none';
            } else {
                btnPrev.removeAttribute('disabled');
                btnPrev.style.opacity = '1';
                btnPrev.style.pointerEvents = 'auto';
            }
        }

        if (btnNext) {
            if (currentIndex === totalCount - 1) {
                btnNext.innerHTML = 'Hoàn thành ✓';
            } else {
                btnNext.innerHTML = 'Từ tiếp theo →';
            }
        }
    }

    // 3. Lật thẻ (Toggle Flip Card)
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

    // 4. Chuyển sang từ kế tiếp
    function goToNextWord() {
        if (currentIndex < totalCount - 1) {
            currentIndex++;
            renderCurrentCard();
        } else {
            // Đã học xong từ cuối cùng -> hiển thị màn hình hoàn thành
            showCompletionScreen();
        }
    }

    // 5. Quay lại từ trước
    function goToPrevWord() {
        if (currentIndex > 0) {
            currentIndex--;
            renderCurrentCard();
        }
    }

    // 6. Hiển thị màn hình chúc mừng hoàn thành
    function showCompletionScreen() {
        const stageArea = document.getElementById('fcStageArea');
        const navControls = document.getElementById('fcNavControls');
        const shortcutsHint = document.getElementById('fcShortcutsHint');
        const completeView = document.getElementById('fcCompleteView');

        if (stageArea) stageArea.style.display = 'none';
        if (navControls) navControls.style.display = 'none';
        if (shortcutsHint) shortcutsHint.style.display = 'none';

        if (completeView) {
            completeView.style.display = 'flex';
        }
    }

    // 7. Học lại phiên học này từ đầu
    function restartFlashcards() {
        currentIndex = 0;
        isFlipped = false;

        const stageArea = document.getElementById('fcStageArea');
        const navControls = document.getElementById('fcNavControls');
        const shortcutsHint = document.getElementById('fcShortcutsHint');
        const completeView = document.getElementById('fcCompleteView');

        if (completeView) completeView.style.display = 'none';
        if (stageArea) stageArea.style.display = 'block';
        if (navControls) navControls.style.display = 'flex';
        if (shortcutsHint) shortcutsHint.style.display = 'block';

        renderCurrentCard();
    }

    // 8. Phát âm bằng Web Speech API
    function speakWordAction(event, targetType) {
        event.stopPropagation(); // Không lật thẻ khi bấm loa

        if (currentIndex < 0 || currentIndex >= totalCount) return;
        const vocab = flashcardList[currentIndex];

        let textToSpeak = '';
        if (targetType === 'example') {
            textToSpeak = vocab.example_en || vocab.word || '';
        } else {
            textToSpeak = vocab.word || '';
        }

        if (!textToSpeak) return;

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utter = new SpeechSynthesisUtterance(textToSpeak);
            utter.lang = 'en-US';
            utter.rate = targetType === 'example' ? 0.95 : 0.88;

            const btn = event.currentTarget;
            if (btn) {
                btn.classList.add('is-playing');
                utter.onend = () => btn.classList.remove('is-playing');
                utter.onerror = () => btn.classList.remove('is-playing');
            }

            window.speechSynthesis.speak(utter);
        }
    }

    // 9. Hỗ trợ phím tắt bàn phím
    document.addEventListener('keydown', function (e) {
        // Tránh can thiệp khi đang focus vào ô input nếu có
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        if (e.code === 'Space') {
            e.preventDefault();
            toggleFlipCard();
        } else if (e.code === 'ArrowRight') {
            e.preventDefault();
            goToNextWord();
        } else if (e.code === 'ArrowLeft') {
            e.preventDefault();
            goToPrevWord();
        }
    });

    // Helper functions
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

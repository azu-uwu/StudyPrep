<x-layouts.app active="tu-vung" :title="'Chọn từ muốn học - ' . ($topic['name'] ?? 'StudyPrep')">

@push('styles')
    {{-- CSS kế thừa và CSS riêng cho trang Chọn từ --}}
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung-danh-sach.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung-chon-tu.css') }}">
@endpush

<div class="tv-select-container">

    {{-- ===== 1. HEADER: QUAY LẠI & TIÊU ĐỀ ===== --}}
    <div class="tv-select-header">
        <a href="{{ route('tu-vung.show', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}"
           class="tv-select-back-btn"
           title="Quay lại danh sách từ vựng"
           aria-label="Quay lại danh sách từ vựng">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>

        <div class="tv-select-header-text">
            <h1 class="tv-select-title">Chọn từ muốn học</h1>
            <p class="tv-select-subtitle">Chọn những từ bạn muốn thêm vào phiên học này.</p>
        </div>
    </div>

    @if(count($vocabularies) > 0)
        {{-- ===== 2. THANH CÔNG CỤ: BỘ ĐẾM & CHỌN TẤT CẢ ===== --}}
        <div class="tv-select-toolbar">
            <div class="tv-select-counter">
                <span>Số từ đã chọn:</span>
                <span class="tv-select-counter-badge">
                    Đã chọn <strong id="selectedCountDisplay" style="margin: 0 3px;">0</strong>/<span id="totalCountDisplay">{{ count($vocabularies) }}</span> từ
                </span>
            </div>

            <button type="button"
                    class="tv-select-all-btn"
                    id="btnSelectAll"
                    onclick="toggleSelectAll()"
                    aria-label="Chọn hoặc bỏ chọn tất cả từ vựng">
                <span class="tv-select-all-checkbox" id="selectAllCheckboxIcon">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
                <span id="selectAllBtnText">Chọn tất cả</span>
            </button>
        </div>

        {{-- ===== 3. DANH SÁCH CÁC TỪ CÓ THỂ HỌC ===== --}}
        @php
            $cefrColors = [
                'A1' => '#64748B',
                'A2' => '#0284C7',
                'B1' => '#10B981',
                'B2' => '#EF4444',
                'C1' => '#F59E0B',
                'C2' => '#D97706',
            ];
        @endphp

        <div class="tv-select-list" id="tvSelectList" role="group" aria-label="Danh sách từ vựng có thể chọn">
            @foreach($vocabularies as $vocab)
                @php
                    $vId = $vocab['vocabulary_id'] ?? $loop->iteration;
                    $vWord = $vocab['word'] ?? '';
                    $vMeaning = $vocab['meaning_vi'] ?? '';
                    $vPos = $vocab['part_of_speech'] ?? 'Từ vựng';
                    $vPronunciation = $vocab['pronunciation'] ?? '';
                    $vCefr = $vocab['cefr_level'] ?? 'B1';
                    $badgeColor = $cefrColors[$vCefr] ?? '#10B981';
                @endphp

                <div class="tv-select-item"
                     id="select-item-{{ $vId }}"
                     data-id="{{ $vId }}"
                     tabindex="0"
                     role="checkbox"
                     aria-checked="false"
                     onclick="toggleSelectWord('{{ $vId }}')"
                     onkeydown="handleCardKeyDown(event, '{{ $vId }}')">

                    {{-- Vùng Checkbox rõ ràng --}}
                    <div class="tv-select-item-checkbox" id="checkbox-{{ $vId }}" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>

                    {{-- Vùng thông tin gọn gàng của từ --}}
                    <div class="tv-select-item-body">
                        <div class="tv-select-item-top">
                            {{-- Từ tiếng Anh --}}
                            <span class="tv-select-item-word">{{ $vWord }}</span>

                            {{-- CEFR Badge --}}
                            <span class="tv-select-cefr-badge" style="background-color: {{ $badgeColor }};">
                                {{ $vCefr }}
                            </span>

                            {{-- Loại từ --}}
                            <span class="tv-select-pos-tag">{{ $vPos }}</span>

                            {{-- Phiên âm --}}
                            @if(!empty($vPronunciation))
                                <span class="tv-select-phonetic">{{ $vPronunciation }}</span>
                            @endif
                        </div>

                        {{-- Nghĩa tiếng Việt --}}
                        <div class="tv-select-item-meaning">
                            {{ $vMeaning }}
                        </div>
                    </div>

                    {{-- Nút nghe phát âm nhanh tiện lợi (ngăn trigger chọn/bỏ chọn thẻ) --}}
                    <button type="button"
                            class="tv-select-audio-btn"
                            title="Nghe phát âm từ {{ $vWord }}"
                            aria-label="Nghe phát âm từ {{ $vWord }}"
                            onclick="handleAudioClick(event, '{{ addslashes($vWord) }}', this)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>

        {{-- ===== 4. THANH HÀNH ĐỘNG CỐ ĐỊNH Ở ĐÁY ===== --}}
        <div class="tv-select-action-card">
            <div class="tv-select-action-info">
                <p class="tv-select-action-title">Sẵn sàng học từ vựng</p>
                <p class="tv-select-action-hint" id="selectActionHint">Vui lòng chọn ít nhất 1 từ để bắt đầu phiên học Flashcard.</p>
            </div>

            <div class="tv-select-action-btns">
                <x-button variant="neutral"
                          size="medium"
                          href="{{ route('tu-vung.show', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}">
                    Quay lại
                </x-button>

                <x-button variant="primary"
                          size="medium"
                          id="btnStartLearning"
                          class="tv-btn-start-learn"
                          :disabled="true"
                          onclick="handleStartLearningSession()">
                    BẮT ĐẦU HỌC <span id="btnWordsCountText"></span>
                </x-button>
            </div>
        </div>

    @else
        {{-- Trạng thái rỗng nếu chủ đề chưa có từ vựng --}}
        <div class="tv-select-empty-state">
            <div class="tv-select-empty-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h3 class="tv-select-empty-title">Chưa có từ vựng để chọn</h3>
            <p class="tv-select-empty-desc">Chủ đề này hiện chưa có danh sách từ vựng khả dụng để học.</p>
            <x-button variant="primary" size="medium" href="{{ route('tu-vung') }}">
                Quay lại danh mục Từ vựng
            </x-button>
        </div>
    @endif

</div>

{{-- Toast thông báo nhỏ --}}
<div class="tv-select-toast" id="selectToastPopup" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <span id="selectToastText">Thông báo</span>
</div>

@push('scripts')
<script>
    // Danh sách ID các từ được chọn
    const selectedWordIds = new Set();
    const allAvailableIds = [
        @foreach($vocabularies as $vocab)
            '{{ $vocab['vocabulary_id'] ?? $loop->iteration }}',
        @endforeach
    ];
    const totalWords = allAvailableIds.length;
    const flashcardBaseUrl = "{{ route('tu-vung.flashcard', ['topicId' => $topicId ?? ($topic['topic_id'] ?? 'test-1')]) }}";

    // 1. Chọn / Bỏ chọn một từ cụ thể
    function toggleSelectWord(wordId) {
        const itemElem = document.getElementById('select-item-' + wordId);
        if (!itemElem) return;

        if (selectedWordIds.has(wordId)) {
            selectedWordIds.delete(wordId);
            itemElem.classList.remove('is-selected');
            itemElem.setAttribute('aria-checked', 'false');
        } else {
            selectedWordIds.add(wordId);
            itemElem.classList.add('is-selected');
            itemElem.setAttribute('aria-checked', 'true');
        }

        updateSelectionStateUI();
    }

    // 2. Hỗ trợ phím Space / Enter khi focus vào card
    function handleCardKeyDown(event, wordId) {
        if (event.key === ' ' || event.key === 'Enter') {
            event.preventDefault();
            toggleSelectWord(wordId);
        }
    }

    // 3. Chọn tất cả / Bỏ chọn tất cả
    function toggleSelectAll() {
        if (selectedWordIds.size === totalWords) {
            // Đã chọn toàn bộ -> Bỏ chọn tất cả
            selectedWordIds.clear();
            allAvailableIds.forEach(id => {
                const item = document.getElementById('select-item-' + id);
                if (item) {
                    item.classList.remove('is-selected');
                    item.setAttribute('aria-checked', 'false');
                }
            });
            showSelectToast('Đã bỏ chọn toàn bộ từ vựng.');
        } else {
            // Chưa chọn hết -> Chọn tất cả
            allAvailableIds.forEach(id => {
                selectedWordIds.add(id);
                const item = document.getElementById('select-item-' + id);
                if (item) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-checked', 'true');
                }
            });
            showSelectToast('Đã chọn tất cả ' + totalWords + ' từ vựng.');
        }

        updateSelectionStateUI();
    }

    // 4. Cập nhật giao diện số lượng, checkbox tổng và nút Bắt đầu học
    function updateSelectionStateUI() {
        const selectedCount = selectedWordIds.size;

        // Cập nhật số đếm ở Toolbar
        const countDisplay = document.getElementById('selectedCountDisplay');
        if (countDisplay) {
            countDisplay.textContent = selectedCount;
        }

        // Cập nhật nút Chọn tất cả
        const selectAllBtn = document.getElementById('btnSelectAll');
        const selectAllText = document.getElementById('selectAllBtnText');

        if (selectAllBtn && selectAllText) {
            if (selectedCount === totalWords && totalWords > 0) {
                selectAllBtn.className = 'tv-select-all-btn is-checked';
                selectAllText.textContent = 'Bỏ chọn tất cả';
            } else if (selectedCount > 0 && selectedCount < totalWords) {
                selectAllBtn.className = 'tv-select-all-btn is-indeterminate';
                selectAllText.textContent = 'Chọn tất cả';
            } else {
                selectAllBtn.className = 'tv-select-all-btn';
                selectAllText.textContent = 'Chọn tất cả';
            }
        }

        // Cập nhật Nút BẮT ĐẦU HỌC & text gợi ý
        const btnStart = document.getElementById('btnStartLearning');
        const countSuffix = document.getElementById('btnWordsCountText');
        const hintElem = document.getElementById('selectActionHint');

        if (btnStart) {
            if (selectedCount >= 1) {
                btnStart.removeAttribute('disabled');
                btnStart.removeAttribute('aria-disabled');
                btnStart.style.pointerEvents = 'auto';
                btnStart.style.opacity = '1';
                btnStart.style.cursor = 'pointer';

                if (countSuffix) {
                    countSuffix.textContent = '(' + selectedCount + ' TỪ)';
                }
                if (hintElem) {
                    hintElem.textContent = 'Bạn đã sẵn sàng ôn tập ' + selectedCount + ' từ đã chọn.';
                }
            } else {
                btnStart.setAttribute('disabled', 'disabled');
                btnStart.setAttribute('aria-disabled', 'true');
                btnStart.style.pointerEvents = 'none';
                btnStart.style.opacity = '0.55';
                btnStart.style.cursor = 'not-allowed';

                if (countSuffix) {
                    countSuffix.textContent = '';
                }
                if (hintElem) {
                    hintElem.textContent = 'Vui lòng chọn ít nhất 1 từ để bắt đầu phiên học Flashcard.';
                }
            }
        }
    }

    // 5. Chuyển sang Flashcard với các từ đã chọn
    function handleStartLearningSession() {
        if (selectedWordIds.size === 0) {
            showSelectToast('Vui lòng chọn ít nhất 1 từ để bắt đầu học.');
            return;
        }

        const idsArray = Array.from(selectedWordIds);
        const targetUrl = flashcardBaseUrl + '?words=' + encodeURIComponent(idsArray.join(','));
        window.location.href = targetUrl;
    }

    // 6. Phát âm từ vựng (ngăn lan truyền sự kiện click của thẻ)
    function handleAudioClick(event, text, btnElement) {
        event.stopPropagation(); // Ngăn chọn/bỏ chọn card

        if (!text) return;
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utter = new SpeechSynthesisUtterance(text);
            utter.lang = 'en-US';
            utter.rate = 0.9;

            if (btnElement) {
                btnElement.classList.add('is-playing');
                utter.onend = () => btnElement.classList.remove('is-playing');
                utter.onerror = () => btnElement.classList.remove('is-playing');
            }

            window.speechSynthesis.speak(utter);
        } else {
            showSelectToast('Trình duyệt chưa hỗ trợ phát âm trực tiếp.');
        }
    }

    // 7. Toast helper
    let selectToastTimer;
    function showSelectToast(message) {
        const toast = document.getElementById('selectToastPopup');
        const textElem = document.getElementById('selectToastText');
        if (!toast || !textElem) return;

        textElem.textContent = message;
        toast.classList.add('show');

        clearTimeout(selectToastTimer);
        selectToastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2200);
    }
</script>
@endpush

</x-layouts.app>

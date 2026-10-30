@props([
    'vocab' => [],
])

@php
    $word = $vocab['word'] ?? '';
    $meaningVi = $vocab['meaning_vi'] ?? '';
    $pos = $vocab['part_of_speech'] ?? 'Từ vựng';
    $pronunciation = $vocab['pronunciation'] ?? '';
    $exampleEn = $vocab['example_en'] ?? '';
    $exampleVi = $vocab['example_vi'] ?? '';
    $cefr = $vocab['cefr_level'] ?? 'B1';
    $status = $vocab['status'] ?? 'new';
    $imageUrl = $vocab['image_url'] ?? null;
    $vocabId = $vocab['vocabulary_id'] ?? rand(100, 999);

    // CEFR badge color mapping
    $cefrBgColors = [
        'A1' => '#64748B',
        'A2' => '#0284C7',
        'B1' => '#10B981',
        'B2' => '#EF4444',
        'C1' => '#F59E0B',
        'C2' => '#D97706',
    ];
    $badgeBg = $cefrBgColors[$cefr] ?? '#10B981';

    // Highlight target word in English example sentence
    $highlightedExample = $exampleEn;
    if (!empty($word) && !empty($exampleEn)) {
        $pattern = '/\b(' . preg_quote($word, '/') . '[a-z]*)\b/i';
        $highlightedExample = preg_replace(
            $pattern,
            '<span class="tv-vocab-highlight">$1</span>',
            e($exampleEn)
        );
    } else {
        $highlightedExample = e($exampleEn);
    }
@endphp

<article class="tv-vocab-card"
         data-status="{{ $status }}"
         data-word="{{ strtolower($word) }}"
         data-cefr="{{ $cefr }}"
         id="vocab-card-{{ $vocabId }}">

    {{-- 1. Hàng đầu: Từ tiếng Anh & Nút tùy chọn (⋮) --}}
    <div class="tv-vocab-header">
        <h3 class="tv-vocab-word">{{ $word }}</h3>

        <div class="tv-vocab-actions">
            <button type="button"
                    class="tv-vocab-menu-btn"
                    aria-label="Tùy chọn từ vựng {{ $word }}"
                    title="Tùy chọn"
                    onclick="toggleCardMenu('{{ $vocabId }}')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="5" r="2.2" />
                    <circle cx="12" cy="12" r="2.2" />
                    <circle cx="12" cy="19" r="2.2" />
                </svg>
            </button>

            {{-- Menu dropdown nhỏ khi bấm nút 3 chấm --}}
            <div class="tv-vocab-dropdown" id="vocabMenu-{{ $vocabId }}">
                <button type="button" class="tv-vocab-dropdown-item" onclick="markVocabStatus('{{ $vocabId }}', 'mastered')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Đánh dấu Đã nắm vững
                </button>
                <button type="button" class="tv-vocab-dropdown-item" onclick="markVocabStatus('{{ $vocabId }}', 'review')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Đánh dấu Dễ quên
                </button>
                <button type="button" class="tv-vocab-dropdown-item" onclick="markVocabStatus('{{ $vocabId }}', 'skipped')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Bỏ qua từ này
                </button>
            </div>
        </div>
    </div>

    {{-- 2. Hàng metadata: CEFR badge + Part of Speech + Nút loa phát âm + Phiên âm --}}
    <div class="tv-vocab-meta">
        {{-- CEFR Badge --}}
        <span class="tv-cefr-badge" style="background-color: {{ $badgeBg }};">
            {{ $cefr }}
        </span>

        {{-- Part of Speech --}}
        <span class="tv-vocab-pos">{{ $pos }}</span>

        {{-- Speaker Audio Icon Button --}}
        <button type="button"
                class="tv-audio-btn tv-audio-btn--small"
                aria-label="Nghe phát âm từ {{ $word }}"
                title="Nghe phát âm"
                onclick="speakWord('{{ addslashes($word) }}', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
            </svg>
        </button>

        {{-- Phonetic pronunciation --}}
        @if(!empty($pronunciation))
            <span class="tv-vocab-phonetic">{{ $pronunciation }}</span>
        @endif
    </div>

    {{-- 3. Hàng Nghĩa tiếng Việt (Ngôi sao đỏ + Nghĩa tiếng Việt) --}}
    <div class="tv-vocab-meaning">
        <span class="tv-star-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#EF4444">
                <circle cx="12" cy="12" r="11" fill="#EF4444" />
                <path d="M12 6.5l1.6 3.8 4.1.4-3.1 2.7.9 4.1-3.5-2.1-3.5 2.1.9-4.1-3.1-2.7 4.1-.4L12 6.5z" fill="#FFFFFF" />
            </svg>
        </span>
        <span class="tv-meaning-text">{{ $meaningVi }}</span>
    </div>

    {{-- 4. Hộp Ví dụ câu tiếng Anh (Khung xám bo góc) --}}
    @if(!empty($exampleEn))
        <div class="tv-vocab-example-box">
            <div class="tv-example-header">
                {{-- Nút phát âm câu ví dụ --}}
                <button type="button"
                        class="tv-audio-btn tv-audio-btn--example"
                        aria-label="Nghe câu ví dụ"
                        title="Nghe câu ví dụ"
                        onclick="speakWord('{{ addslashes($exampleEn) }}', this)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    </svg>
                </button>

                {{-- Câu tiếng Anh có highlight từ vựng --}}
                <div class="tv-example-content">
                    <p class="tv-example-en">{!! $highlightedExample !!}</p>
                </div>

                {{-- Nút quả cầu dịch nghĩa (toggle ví dụ tiếng Việt) --}}
                @if(!empty($exampleVi))
                    <button type="button"
                            class="tv-translate-btn"
                            aria-label="Xem dịch câu ví dụ"
                            title="Dịch câu ví dụ"
                            onclick="toggleTranslation('{{ $vocabId }}')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </button>
                @endif
            </div>

            {{-- Dịch tiếng Việt (ẩn mặc định, mở khi bấm nút quả cầu) --}}
            @if(!empty($exampleVi))
                <div class="tv-example-vi" id="example-vi-{{ $vocabId }}">
                    <span>{{ $exampleVi }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- 5. Ảnh minh họa (nếu có trong dữ liệu) --}}
    @if(!empty($imageUrl))
        <div class="tv-vocab-illustration">
            <img src="{{ asset($imageUrl) }}" alt="{{ $word }}" loading="lazy">
        </div>
    @endif

</article>

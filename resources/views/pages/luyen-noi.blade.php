<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudyPrep - Luyện nói (Speaking Practice)</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Nunito & Instrument Sans Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800|instrument-sans:400,500,600,700">

    {{-- Core Design System --}}
    <link rel="stylesheet" href="{{ asset('css/studyprep-core.css') }}">

    {{-- CSS riêng của trang Luyện nói (Full screen, no sidebar) --}}
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-noi.css') }}">
</head>
<body>

    @php
        $type = request('type', 'sentence'); // 'sentence' hoặc 'passage'
        $reqId = (int)request('id', 1);
        $backUrl = ($type === 'passage') ? url('/luyen-tap/doc-doan') : url('/luyen-tap/doc-cau');
    @endphp

    <div class="sp-speaking-page">

        {{-- 1. Top Header Bar: Logo + Nút ✕ + Tiêu đề bài + Bookmark/Báo cáo --}}
        <header class="sp-speaking-header">
            <a href="{{ url('/luyen-tap') }}" class="sp-speaking-logo-wrap" title="Về trang chủ StudyPrep">
                <img class="sp-speaking-logo" src="{{ asset('images/logo.svg') }}" alt="StudyPrep">
            </a>

            <a href="{{ $backUrl }}" class="sp-speaking-close-btn" title="Thoát bài luyện nói" aria-label="Đóng bài luyện nói">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            <h2 class="sp-speaking-header-title" id="speakingHeaderTitle">1. If you have a good job, think smart.</h2>

            {{-- Các nút hành động bên phải: Lưu (Bookmark) và Báo cáo (Ảnh 2 & Ảnh 4) --}}
            <div class="sp-speaking-header-actions">
                <button type="button" class="sp-header-action-btn" id="headerBookmarkBtn" onclick="toggleHeaderBookmark()" title="Lưu bài học vào danh sách yêu thích">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span id="headerBookmarkLabel">Lưu</span>
                </button>

                <button type="button" class="sp-header-action-btn" onclick="openReportModal()" title="Báo cáo lỗi nội dung hoặc âm thanh">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                        <line x1="4" y1="22" x2="4" y2="15"></line>
                    </svg>
                    <span>Báo cáo</span>
                </button>
            </div>
        </header>

        {{-- 2. Vùng nội dung chính --}}
        <main class="sp-speaking-main">

            {{-- Tiêu đề trang: Phát âm --}}
            <h1 class="sp-speaking-title">Phát âm</h1>

            {{-- Dòng chọn chế độ: Phát âm / Shadowing --}}
            <div class="sp-speaking-mode-row">
                <span class="sp-speaking-mode-label">Chế độ</span>
                <button type="button" class="sp-mode-btn sp-mode-btn--active" id="modePronounceBtn" onclick="switchMode('pronounce')">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                    </svg>
                    <span>Phát âm</span>
                </button>
                <button type="button" class="sp-mode-btn sp-mode-btn--inactive" id="modeShadowingBtn" onclick="switchMode('shadowing')">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <span>Shadowing</span>
                </button>
            </div>

            {{-- 3. Thanh Audio Player Controller --}}
            <div class="sp-audio-player-bar">
                <button type="button" class="sp-player-play-btn" id="playerPlayBtn" onclick="togglePlayAudio()" aria-label="Phát âm thanh">
                    <svg viewBox="0 0 24 24" id="playIcon">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                </button>

                <button type="button" class="sp-player-speed-btn" id="playerSpeedBtn" onclick="toggleSpeed()" title="Tốc độ phát">
                    1x
                </button>

                <span class="sp-player-time" id="playerCurrentTime">00:00</span>

                <div class="sp-player-track-container" id="playerTrackContainer" onclick="seekAudio(event)">
                    <div class="sp-player-track">
                        <div class="sp-player-fill" id="playerFill"></div>
                    </div>
                    <div class="sp-player-thumb" id="playerThumb"></div>
                </div>

                <span class="sp-player-time" id="playerDuration">00:02</span>

                <button type="button" class="sp-player-voice-btn" id="playerVoiceBtn" onclick="toggleVoiceDropdown()" title="Chọn giọng đọc thử TTS">
                    <span id="voiceName">John</span>
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            {{-- Ghi chú phân biệt rõ TTS phát âm thử vs Audio chuẩn --}}
            <div style="font-size: 12px; color: #64748B; margin: -16px 0 16px 12px; display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #0076FE;"></span>
                <span>Giọng đọc phát âm thử (Web Speech TTS). Audio chính thức đang chờ dữ liệu xuất bản từ Admin.</span>
            </div>

            {{-- 4. Khung nội dung luyện tập chính (Viền nét đứt chuẩn Ảnh 2 & Ảnh 4) --}}
            <div class="sp-sentence-box">
                {{-- Hiển thị câu đơn (Sentence) hoặc đoạn văn (Passage) --}}
                <div class="sp-sentence-text" id="sentenceDisplay"></div>

                {{-- Phiên âm IPA --}}
                <div class="sp-sentence-ipa" id="sentenceIpa"></div>

                {{-- Khung hiển thị bản dịch tiếng Việt khi nhấn Dịch --}}
                <div class="sp-sentence-translation" id="sentenceTranslation"></div>
            </div>

            {{-- 5. Hàng công cụ phụ trợ: Dịch, IPA và Lời nhắc --}}
            <div class="sp-tools-row">
                <div class="sp-tools-left">
                    <button type="button" class="sp-tool-btn sp-tool-btn--outline" id="translateBtn" onclick="toggleTranslation()">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M5 8h14M12 4v4m-4 8c1.5-2 3.5-3.5 6-4M7 19c2-1.5 3.5-3.5 4.5-6M15 11l4 9M19 18h-6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Dịch</span>
                    </button>

                    <button type="button" class="sp-tool-btn sp-tool-btn--primary" id="ipaToggleBtn" onclick="toggleIpa()">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v8M8 12h8" stroke-linecap="round"/>
                        </svg>
                        <span>IPA</span>
                    </button>
                </div>

                <p class="sp-tools-hint">Ấn vào từ để xem cách phát âm.</p>
            </div>

            {{-- 6. Điều hướng câu trước / câu tiếp --}}
            <div class="sp-nav-buttons-row">
                <button type="button" class="sp-nav-btn" id="prevQuestionBtn" onclick="navigateItem(-1)">
                    ← Câu trước
                </button>
                <button type="button" class="sp-nav-btn" id="nextQuestionBtn" onclick="navigateItem(1)">
                    Câu tiếp →
                </button>
            </div>

            {{-- Đường phân cách --}}
            <hr class="sp-section-divider">

            {{-- 7. Khu vực "Ghi âm của tôi" (Microphone thật qua MediaRecorder API) --}}
            <section class="sp-recordings-section" aria-label="Ghi âm của tôi">
                <div class="sp-recordings-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                        <line x1="12" y1="19" x2="12" y2="23"/>
                        <line x1="8" y1="23" x2="16" y2="23"/>
                    </svg>
                    <span>Ghi âm của tôi</span>
                </div>

                {{-- Khung rỗng ban đầu chuẩn Ảnh 2 & 4 --}}
                <div class="sp-recordings-empty" id="recordingsEmpty">
                    <svg class="sp-empty-folder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <p class="sp-recordings-empty-text">
                        Bạn chưa có ghi âm nào. Hãy bắt đầu ghi âm để kiểm tra kỹ năng phát âm của bạn.
                    </p>
                </div>

                {{-- Danh sách bài ghi âm thực tế của người dùng --}}
                <div id="recordingsList" style="display: none;"></div>
            </section>

        </main>

        {{-- 8. Fixed Bottom Bar: Nút (+) bên trái & Nút Micro lớn ở giữa --}}
        <footer class="sp-speaking-bottom-bar">
            <div class="sp-bottom-left-action">
                <button type="button" class="sp-circle-plus-btn" title="Thêm ghi chú bài học" onclick="showNoteToast()" aria-label="Thêm ghi chú">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="sp-bottom-center-action">
                <div class="sp-recording-status-toast" id="recordingToast">
                    🎙 Đang ghi âm... Nhấn lại để hoàn tất
                </div>

                <button type="button" class="sp-main-mic-btn" id="mainMicBtn" onclick="toggleRealRecording()" aria-label="Ghi âm giọng nói qua Microphone">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                </button>
            </div>
        </footer>

    </div>

    {{-- JavaScript điều khiển toàn bộ tương tác --}}
    <script>
        const currentPracticeType = "{{ $type }}"; // 'sentence' hoặc 'passage'
        const initialId = {{ $reqId }};

        // Dữ liệu Đọc Câu (Sentences)
        const sentencesData = [
            {
                id: 1,
                title: "1. If you have a good job, think smart.",
                fullText: "If you have a good job, think smart.",
                chunks: [
                    { text: "If you have a good job,", color: "red" },
                    { text: "think smart.", color: "green" }
                ],
                ipa: "ɪf  ju:  hæv  ə  gʊd  dʒɒb,  θɪŋk  smɑ:t.",
                translation: "Nếu bạn có một công việc tốt, hãy suy nghĩ thông minh."
            },
            {
                id: 2,
                title: "2. Our affordable loans can start repairing your credit right away.",
                fullText: "Our affordable loans can start repairing your credit right away.",
                chunks: [
                    { text: "Our affordable loans", color: "blue" },
                    { text: "can start repairing your credit right away.", color: "green" }
                ],
                ipa: "ˈaʊər  əˈfɔːdəbl  ləʊnz  kæn  stɑːt  rɪˈpeərɪŋ  jɔː  ˈkrɛdɪt  raɪt  əˈweɪ.",
                translation: "Các gói vay phải chăng của chúng tôi có thể bắt đầu cải thiện điểm tín dụng của bạn ngay lập tức."
            },
            {
                id: 3,
                title: "3. Say goodbye to cash advances on your credit card.",
                fullText: "Say goodbye to cash advances on your credit card and high interest rates.",
                chunks: [
                    { text: "Say goodbye to cash advances", color: "red" },
                    { text: "on your credit card and high interest rates.", color: "orange" }
                ],
                ipa: "seɪ  ɡʊdˈbaɪ  tuː  kæʃ  ədˈvɑːnsɪz  ɒn  jɔː  ˈkrɛdɪt  kɑːd  ænd  haɪ  ˈɪntrəst  reɪts.",
                translation: "Hãy nói lời tạm biệt với việc ứng tiền mặt từ thẻ tín dụng và mức lãi suất đắt đỏ."
            },
            {
                id: 4,
                title: "4. If you own a car that is less than three years old, you qualify instantly.",
                fullText: "If you own a car that is less than three years old, you qualify instantly.",
                chunks: [
                    { text: "If you own a car that is less than three years old,", color: "blue" },
                    { text: "you qualify instantly.", color: "red" }
                ],
                ipa: "ɪf  ju:  əʊn  ə  kɑː  ðæt  ɪz  lɛs  ðæn  θriː  jɪəz  əʊld,  ju:  ˈkwɒlɪfaɪ  ˈɪnstəntli.",
                translation: "Nếu bạn sở hữu ô tô dưới ba năm tuổi, bạn đủ điều kiện tham gia ngay."
            },
            {
                id: 5,
                title: "5. Visit our website at www.wise-financial.com.",
                fullText: "Visit our website at www.wise-financial.com.",
                chunks: [
                    { text: "Visit our website at", color: "green" },
                    { text: "www.wise-financial.com.", color: "blue" }
                ],
                ipa: "ˈvɪzɪt  ˈaʊər  ˈwɛbˌsaɪt  æt  dʌbljuː-dʌbljuː-dʌbljuː  waɪz  faɪˈnænʃəl  dɒt  kɒm.",
                translation: "Hãy truy cập trang web của chúng tôi tại www.wise-financial.com."
            }
        ];

        // Dữ liệu Đọc Đoạn (Passages - Bám sát 100% Ảnh 4)
        const passagesData = [
            {
                id: 1,
                title: "1. Did you all pay off your debts?",
                fullText: "Did you all pay off your debts? If you have a good job, think smart. Think Wise Financial! Our affordable loans can start repairing your credit right away. Say goodbye to cash advances on your redit card and taking out a bank loan with their extremely high interest rates. Here is something else to consider: If you own a car that is less than three years old, you qualify instantly. Visit our website at www.wise-financial.com.",
                chunks: [
                    { text: "Did you all pay off your debts?", color: "red" },
                    { text: "If you have a good job,", color: "green" },
                    { text: "think smart.", color: "blue" },
                    { text: "Think Wise Financial!", color: "brown" },
                    { text: "Our affordable loans can start repairing your credit right away.", color: "emerald" },
                    { text: "Say goodbye to cash advances on your redit card", color: "blue" },
                    { text: "and taking out a bank loan with their extremely high interest rates.", color: "red" },
                    { text: "Here is something else to consider:", color: "brown" },
                    { text: "If you own a car that is less than three years old,", color: "blue" },
                    { text: "you qualify instantly.", color: "red" },
                    { text: "Visit our website at www.wise-financial.com.", color: "green" }
                ],
                ipa: "dɪd ju: ɔːl peɪ ɒf jɔː dɛts? ɪf ju: hæv ə gʊd dʒɒb, θɪŋk smɑ:t. θɪŋk waɪz faɪˈnænʃəl! ˈaʊər əˈfɔːdəbl ləʊnz kæn stɑːt rɪˈpeərɪŋ jɔː ˈkrɛdɪt raɪt əˈweɪ. seɪ gʊdˈbaɪ tu: kæʃ ədˈvɑːnsɪz ɒn jɔː ˈkrɛdɪt kɑːd ænd ˈteɪkɪŋ aʊt ə bæŋk ləʊn wɪð ðeər ɪksˈtriːmli haɪ ˈɪntrəst reɪts. hɪər ɪz ˈsʌmθɪŋ ɛls tu: kənˈsɪdə: ɪf ju: əʊn ə kɑː ðæt ɪz lɛs ðæn θriː jɪəz əʊld, ju: ˈkwɒlɪfaɪ ˈɪnstəntli. ˈvɪzɪt ˈaʊər ˈwɛbˌsaɪt æt waɪz-faɪˈnænʃəl.kɒm.",
                translation: "Tất cả các bạn đã trả hết các khoản nợ của mình chưa? Nếu bạn có một công việc tốt, hãy suy nghĩ thông minh. Hãy nghĩ đến Wise Financial! Các khoản vay giá cả phải chăng của chúng tôi có thể bắt đầu phục hồi tín dụng của bạn ngay lập tức. Hãy nói lời tạm biệt với việc ứng tiền mặt từ thẻ tín dụng và vay ngân hàng với mức lãi suất cực cao. Đây là một điều nữa cần cân nhắc: Nếu bạn sở hữu một chiếc xe ô tô chưa đầy ba năm tuổi, bạn đủ điều kiện ngay lập tức. Hãy truy cập trang web của chúng tôi tại www.wise-financial.com."
            },
            {
                id: 2,
                title: "2. You don't need to spend all of your hard earned money on bakery bread.",
                fullText: "You don't need to spend all of your hard earned money on bakery bread. Making your own bread at home is easy with the new Double Duty Dough Mixer by Berring. Unlike other bread machines that can be difficult to clean and store, the Double Duty Dough Mixer is compact and dishwasher safe.",
                chunks: [
                    { text: "You don't need to spend all of your hard earned money on bakery bread.", color: "blue" },
                    { text: "Making your own bread at home is easy with the new Double Duty Dough Mixer by Berring.", color: "green" },
                    { text: "Unlike other bread machines that can be difficult to clean and store,", color: "orange" },
                    { text: "the Double Duty Dough Mixer is compact and dishwasher safe.", color: "emerald" }
                ],
                ipa: "ju: dəʊnt niːd tu: spɛnd ɔːl ɒv jɔː hɑːd ɜːnd ˈmʌni ɒn ˈbeɪkəri brɛd. ˈmeɪkɪŋ jɔːr əʊn brɛd æt həʊm ɪz ˈiːzi wɪð ðə njuː ˈdʌbl ˈdjuːti dəʊ ˈmɪksə baɪ ˈbɛrɪŋ.",
                translation: "Bạn không cần phải tiêu hết số tiền vất vả kiếm được vào bánh mì ở tiệm. Tự làm bánh mì tại nhà thật dễ dàng với Máy trộn bột Double Duty mới của Berring. Khác với những máy làm bánh mì khác khó vệ sinh và cất giữ, máy trộn Double Duty nhỏ gọn và an toàn khi dùng trong máy rửa bát."
            }
        ];

        // Lựa chọn bộ dữ liệu dựa theo type
        const activeDataset = (currentPracticeType === 'passage') ? passagesData : sentencesData;
        const storageBookmarkKey = (currentPracticeType === 'passage') ? 'studyprep_passage_bookmarks' : 'studyprep_sentence_bookmarks';
        const storageCountKey = (currentPracticeType === 'passage') ? 'studyprep_passage_practice_counts' : 'studyprep_sentence_practice_counts';

        // Tìm chỉ số phần tử theo id
        let currentIndex = activeDataset.findIndex(item => item.id === initialId);
        if (currentIndex === -1) currentIndex = 0;

        // Trạng thái Audio Player
        let isPlaying = false;
        let playInterval = null;
        let playProgress = 0;
        let currentSpeed = 1;
        const speeds = [0.75, 1, 1.25];
        let voicesList = ['John (US)', 'Emma (UK)', 'Alex (AU)'];
        let currentVoiceIndex = 0;

        // Trạng thái MediaRecorder Microphone
        let mediaRecorder = null;
        let audioChunks = [];
        let isRecording = false;
        let recordStartTime = 0;
        let recordTimerInterval = null;

        // Render bài học hiện tại
        function renderItem(index) {
            const data = activeDataset[index];
            if (!data) return;

            document.getElementById('speakingHeaderTitle').textContent = data.title;

            // Render Chunks màu sắc và từ có thể click nghe
            const displayEl = document.getElementById('sentenceDisplay');
            displayEl.className = (currentPracticeType === 'passage') ? 'sp-passage-display' : 'sp-sentence-text';
            displayEl.innerHTML = '';

            data.chunks.forEach(chunk => {
                const chunkSpan = document.createElement('span');
                chunkSpan.className = 'sp-chunk-' + chunk.color;
                chunkSpan.title = 'Bấm để nghe cụm này';

                // Tách từng từ để người dùng có thể nhấp vào từ bất kỳ để nghe phát âm
                const words = chunk.text.split(' ');
                words.forEach((w, wIdx) => {
                    const wordSpan = document.createElement('span');
                    wordSpan.className = 'sp-clickable-word';
                    wordSpan.textContent = w + (wIdx < words.length - 1 ? ' ' : '');
                    wordSpan.onclick = function(e) {
                        e.stopPropagation();
                        speakWord(w.replace(/[.,/#!$%^&*;:{}=\-_`~()?'"]/g, ""));
                    };
                    chunkSpan.appendChild(wordSpan);
                });

                // Click đúp hoặc click cả cụm
                chunkSpan.onclick = function() {
                    speakText(chunk.text);
                };

                displayEl.appendChild(chunkSpan);
                displayEl.appendChild(document.createTextNode(' '));
            });

            // Phiên âm IPA
            const ipaEl = document.getElementById('sentenceIpa');
            ipaEl.className = (currentPracticeType === 'passage') ? 'sp-passage-ipa' : 'sp-sentence-ipa';
            ipaEl.textContent = data.ipa;

            // Bản dịch
            document.getElementById('sentenceTranslation').innerHTML = `💡 <strong>Bản dịch:</strong> ${data.translation}`;

            // Nút chuyển bài
            const prevBtn = document.getElementById('prevQuestionBtn');
            const nextBtn = document.getElementById('nextQuestionBtn');
            prevBtn.disabled = (index === 0);
            prevBtn.className = (index === 0) ? 'sp-nav-btn sp-nav-btn--disabled' : 'sp-nav-btn';
            nextBtn.disabled = (index === activeDataset.length - 1);
            nextBtn.className = (index === activeDataset.length - 1) ? 'sp-nav-btn sp-nav-btn--disabled' : 'sp-nav-btn';

            // Cập nhật trạng thái Bookmark
            updateBookmarkButtonState(data.id);

            // Reset Audio player
            resetPlayer();
        }

        // Bookmark Header
        function getStoredBookmarks() {
            try {
                return JSON.parse(localStorage.getItem(storageBookmarkKey) || '[]');
            } catch (e) {
                return [];
            }
        }

        function updateBookmarkButtonState(id) {
            const bookmarks = getStoredBookmarks();
            const btn = document.getElementById('headerBookmarkBtn');
            const label = document.getElementById('headerBookmarkLabel');
            if (bookmarks.includes(id)) {
                btn.classList.add('is-active');
                label.textContent = 'Đã lưu';
            } else {
                btn.classList.remove('is-active');
                label.textContent = 'Lưu';
            }
        }

        function toggleHeaderBookmark() {
            const curItem = activeDataset[currentIndex];
            if (!curItem) return;
            let bookmarks = getStoredBookmarks();

            if (bookmarks.includes(curItem.id)) {
                bookmarks = bookmarks.filter(bId => bId !== curItem.id);
                showToastNotification('Đã gỡ bài học khỏi danh mục Bookmark');
            } else {
                bookmarks.push(curItem.id);
                showToastNotification('⭐ Đã thêm bài học vào danh mục Bookmark');
            }

            localStorage.setItem(storageBookmarkKey, JSON.stringify(bookmarks));
            updateBookmarkButtonState(curItem.id);
        }

        // Điều hướng câu trước / câu sau
        function navigateItem(direction) {
            const nextIdx = currentIndex + direction;
            if (nextIdx >= 0 && nextIdx < activeDataset.length) {
                currentIndex = nextIdx;
                renderItem(currentIndex);
                // Cập nhật URL mà không reload trang
                const newUrl = `${window.location.pathname}?type=${currentPracticeType}&id=${activeDataset[currentIndex].id}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
            }
        }

        // Chuyển đổi chế độ (Phát âm / Shadowing)
        function switchMode(mode) {
            const btnPronounce = document.getElementById('modePronounceBtn');
            const btnShadowing = document.getElementById('modeShadowingBtn');
            if (mode === 'pronounce') {
                btnPronounce.className = 'sp-mode-btn sp-mode-btn--active';
                btnShadowing.className = 'sp-mode-btn sp-mode-btn--inactive';
            } else {
                btnPronounce.className = 'sp-mode-btn sp-mode-btn--inactive';
                btnShadowing.className = 'sp-mode-btn sp-mode-btn--active';
            }
        }

        // Audio Player Controls (Web SpeechSynthesis)
        function togglePlayAudio() {
            if (isPlaying) {
                pauseAudio();
            } else {
                playAudio();
            }
        }

        function playAudio() {
            isPlaying = true;
            document.getElementById('playIcon').innerHTML = '<rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>';

            const text = activeDataset[currentIndex].fullText;
            speakText(text, function() {
                pauseAudio();
            });

            // Ước lượng độ dài dựa theo số lượng từ
            const wordCount = text.split(' ').length;
            const estimatedDurationSec = Math.max(2, Math.round(wordCount / (2.5 * currentSpeed)));
            document.getElementById('playerDuration').textContent = `00:${estimatedDurationSec < 10 ? '0' + estimatedDurationSec : estimatedDurationSec}`;

            clearInterval(playInterval);
            const totalDurationMs = estimatedDurationSec * 1000;
            const startTime = Date.now() - (playProgress * totalDurationMs);

            playInterval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                playProgress = Math.min(1, elapsed / totalDurationMs);

                const fillPercent = (playProgress * 100).toFixed(1) + '%';
                document.getElementById('playerFill').style.width = fillPercent;
                document.getElementById('playerThumb').style.left = fillPercent;

                const curSec = Math.floor(playProgress * estimatedDurationSec);
                document.getElementById('playerCurrentTime').textContent = `00:${curSec < 10 ? '0' + curSec : curSec}`;

                if (playProgress >= 1) {
                    pauseAudio();
                    resetPlayer();
                }
            }, 60);
        }

        function pauseAudio() {
            isPlaying = false;
            clearInterval(playInterval);
            document.getElementById('playIcon').innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        }

        function resetPlayer() {
            pauseAudio();
            playProgress = 0;
            document.getElementById('playerFill').style.width = '0%';
            document.getElementById('playerThumb').style.left = '0%';
            document.getElementById('playerCurrentTime').textContent = '00:00';
        }

        function toggleSpeed() {
            const curIdx = speeds.indexOf(currentSpeed);
            currentSpeed = speeds[(curIdx + 1) % speeds.length];
            document.getElementById('playerSpeedBtn').textContent = currentSpeed + 'x';
            if (isPlaying) {
                playAudio();
            }
        }

        function toggleVoiceDropdown() {
            currentVoiceIndex = (currentVoiceIndex + 1) % voicesList.length;
            const voice = voicesList[currentVoiceIndex];
            document.getElementById('voiceName').textContent = voice.split(' ')[0];
            showToastNotification(`Đã chuyển giọng đọc thử sang: ${voice}`);
        }

        function seekAudio(e) {
            const rect = document.getElementById('playerTrackContainer').getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            playProgress = Math.max(0, Math.min(1, clickX / rect.width));
            const fillPercent = (playProgress * 100).toFixed(1) + '%';
            document.getElementById('playerFill').style.width = fillPercent;
            document.getElementById('playerThumb').style.left = fillPercent;
        }

        // Phát âm từ hoặc đoạn
        function speakWord(word) {
            if (!word) return;
            speakText(word);
        }

        function speakText(text, onEndCallback) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = (currentVoiceIndex === 1) ? 'en-GB' : (currentVoiceIndex === 2 ? 'en-AU' : 'en-US');
                utterance.rate = currentSpeed;
                if (onEndCallback) {
                    utterance.onend = onEndCallback;
                    utterance.onerror = onEndCallback;
                }
                window.speechSynthesis.speak(utterance);
            } else {
                alert('Trình duyệt của bạn không hỗ trợ tính năng Web Speech.');
            }
        }

        // Bật / tắt Dịch
        function toggleTranslation() {
            const transBox = document.getElementById('sentenceTranslation');
            const btn = document.getElementById('translateBtn');
            if (transBox.style.display === 'block') {
                transBox.style.display = 'none';
                btn.classList.remove('is-active');
            } else {
                transBox.style.display = 'block';
                btn.classList.add('is-active');
            }
        }

        // Bật / tắt phiên âm IPA
        function toggleIpa() {
            const ipaEl = document.getElementById('sentenceIpa');
            const btn = document.getElementById('ipaToggleBtn');
            if (ipaEl.classList.contains('is-hidden')) {
                ipaEl.classList.remove('is-hidden');
                btn.className = 'sp-tool-btn sp-tool-btn--primary';
            } else {
                ipaEl.classList.add('is-hidden');
                btn.className = 'sp-tool-btn sp-tool-btn--outline';
            }
        }

        // Ghi âm Micro thực tế với MediaRecorder API
        async function toggleRealRecording() {
            const micBtn = document.getElementById('mainMicBtn');
            const toast = document.getElementById('recordingToast');

            if (!isRecording) {
                // Yêu cầu quyền Micro thật từ trình duyệt
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    audioChunks = [];
                    mediaRecorder = new MediaRecorder(stream);

                    mediaRecorder.ondataavailable = function(e) {
                        if (e.data && e.data.size > 0) {
                            audioChunks.push(e.data);
                        }
                    };

                    mediaRecorder.onstop = function() {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm;codecs=opus' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        const durationSec = Math.round((Date.now() - recordStartTime) / 1000);
                        addRealRecordingItem(audioUrl, durationSec);

                        // Tắt các track micro
                        stream.getTracks().forEach(track => track.stop());
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    recordStartTime = Date.now();
                    micBtn.classList.add('is-recording');
                    toast.classList.add('is-visible');
                    toast.textContent = '🎙 Đang ghi âm (00:00)... Nhấn lại để hoàn tất';

                    clearInterval(recordTimerInterval);
                    recordTimerInterval = setInterval(() => {
                        const elapsedSec = Math.round((Date.now() - recordStartTime) / 1000);
                        const s = elapsedSec < 10 ? '0' + elapsedSec : elapsedSec;
                        toast.textContent = `🎙 Đang ghi âm (00:${s})... Nhấn lại để hoàn tất`;

                        // Tự động dừng sau 60 giây nếu người dùng quên nhấn
                        if (elapsedSec >= 60) {
                            toggleRealRecording();
                        }
                    }, 1000);

                } catch (err) {
                    alert('Không thể truy cập microphone. Vui lòng cấp quyền micro trên trình duyệt để ghi âm.');
                    console.error('Microphone access error:', err);
                }
            } else {
                // Dừng ghi âm
                clearInterval(recordTimerInterval);
                isRecording = false;
                micBtn.classList.remove('is-recording');
                toast.textContent = '✅ Đang lưu bản ghi âm...';

                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                }

                setTimeout(() => {
                    toast.classList.remove('is-visible');
                }, 600);
            }
        }

        // Thêm bản ghi âm thật vào danh sách "Ghi âm của tôi"
        function addRealRecordingItem(audioUrl, durationSec) {
            document.getElementById('recordingsEmpty').style.display = 'none';
            const list = document.getElementById('recordingsList');
            list.style.display = 'block';

            const item = document.createElement('div');
            item.className = 'sp-recording-item';
            const timeStr = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

            item.innerHTML = `
                <div class="sp-recording-info">
                    <button type="button" class="sp-circle-plus-btn" style="width: 32px; height: 32px; border-color: #0076FE; color: #0076FE;" onclick="playRecordedAudio('${audioUrl}', this)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    </button>
                    <div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #1E293B;">Bản ghi âm ${timeStr} (${durationSec}s)</div>
                        <div style="font-size: 12px; color: #64748B;">Đã thu trực tiếp qua Microphone • Nhấn nút Play để nghe lại</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <audio src="${audioUrl}" controls class="sp-recording-audio" style="height: 32px; max-width: 220px;"></audio>
                    <button type="button" class="sp-recording-delete-btn" title="Xóa bản ghi âm này" onclick="this.closest('.sp-recording-item').remove(); checkRecordingsEmpty();">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            `;
            list.prepend(item);

            // Cập nhật số lần luyện tập vào LocalStorage
            incrementPracticeCount(activeDataset[currentIndex].id);
        }

        function playRecordedAudio(url, btn) {
            const audio = new Audio(url);
            audio.play();
        }

        function checkRecordingsEmpty() {
            const list = document.getElementById('recordingsList');
            if (!list.children.length) {
                list.style.display = 'none';
                document.getElementById('recordingsEmpty').style.display = 'flex';
            }
        }

        function incrementPracticeCount(id) {
            try {
                const counts = JSON.parse(localStorage.getItem(storageCountKey) || '{}');
                counts[id] = (counts[id] || 0) + 1;
                localStorage.setItem(storageCountKey, JSON.stringify(counts));
            } catch (e) {}
        }

        // Báo cáo & Ghi chú Toast
        function openReportModal() {
            const reason = prompt('Nhập nội dung báo cáo góp ý cho bài học này:', 'Lỗi phát âm / lỗi hiển thị từ vựng');
            if (reason) {
                showToastNotification('Cảm ơn bạn! Báo cáo đã được ghi nhận.');
            }
        }

        function showNoteToast() {
            prompt('Ghi chú của bạn cho bài học này:', '');
        }

        function showToastNotification(msg) {
            const toast = document.getElementById('recordingToast');
            toast.textContent = msg;
            toast.classList.add('is-visible');
            setTimeout(() => {
                toast.classList.remove('is-visible');
            }, 2500);
        }

        // Khởi tạo
        document.addEventListener('DOMContentLoaded', function() {
            renderItem(currentIndex);
        });
    </script>
</body>
</html>

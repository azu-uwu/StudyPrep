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

    <div class="sp-speaking-page">

        {{-- 1. Top Header Bar: Logo + Nút ✕ + Tiêu đề bài câu --}}
        <header class="sp-speaking-header">
            <a href="{{ url('/luyen-tap') }}" class="sp-speaking-logo-wrap" title="Về trang chủ StudyPrep">
                <img class="sp-speaking-logo" src="{{ asset('images/logo.svg') }}" alt="StudyPrep">
            </a>

            <a href="{{ url('/luyen-tap/noi') }}" class="sp-speaking-close-btn" title="Thoát bài luyện nói" aria-label="Đóng bài luyện nói">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            <h2 class="sp-speaking-header-title" id="speakingHeaderTitle">1. If you have a good job, think smart.</h2>
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

                <button type="button" class="sp-player-voice-btn" id="playerVoiceBtn" onclick="toggleVoiceDropdown()">
                    <span id="voiceName">John</span>
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            {{-- 4. Khung câu luyện tập chính (Viền nét đứt chuẩn ảnh mẫu) --}}
            <div class="sp-sentence-box">
                <div class="sp-sentence-text" id="sentenceDisplay">
                    <span class="sp-chunk-red" id="chunkRed" onclick="speakChunk('red')" title="Bấm để nghe cụm này">If you have a good job,</span>
                    <span class="sp-chunk-green" id="chunkGreen" onclick="speakChunk('green')" title="Bấm để nghe cụm này">think smart.</span>
                </div>

                <p class="sp-sentence-ipa" id="sentenceIpa">
                    ɪf &nbsp; ju: &nbsp; hæv &nbsp; ə &nbsp; gʊd &nbsp; dʒɒb, &nbsp; θɪŋk &nbsp; smɑ:t.
                </p>

                {{-- Khung hiển thị bản dịch tiếng Việt khi nhấn Dịch --}}
                <div class="sp-sentence-translation" id="sentenceTranslation">
                    💡 <strong>Bản dịch:</strong> Nếu bạn có một công việc tốt, hãy suy nghĩ thông minh.
                </div>
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
                <button type="button" class="sp-nav-btn sp-nav-btn--disabled" id="prevQuestionBtn" onclick="navigateSentence(-1)" disabled>
                    ← Câu trước
                </button>
                <button type="button" class="sp-nav-btn" id="nextQuestionBtn" onclick="navigateSentence(1)">
                    Câu tiếp →
                </button>
            </div>

            {{-- Đường phân cách --}}
            <hr class="sp-section-divider">

            {{-- 7. Khu vực "Ghi âm của tôi" --}}
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

                {{-- Khung rỗng ban đầu theo đúng ảnh tham chiếu --}}
                <div class="sp-recordings-empty" id="recordingsEmpty">
                    <svg class="sp-empty-folder-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <p class="sp-recordings-empty-text">
                        Bạn chưa có ghi âm nào. Hãy bắt đầu ghi âm để kiểm tra kỹ năng phát âm của bạn.
                    </p>
                </div>

                {{-- Danh sách bài ghi âm sau khi người dùng thu âm --}}
                <div id="recordingsList" style="display: none;"></div>
            </section>

        </main>

        {{-- 8. Fixed Bottom Bar: Nút (+) bên trái & Nút Micro lớn ở giữa --}}
        <footer class="sp-speaking-bottom-bar">
            <div class="sp-bottom-left-action">
                <button type="button" class="sp-circle-plus-btn" title="Thêm ghi chú hoặc từ vựng" aria-label="Thêm ghi chú">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="sp-bottom-center-action">
                <div class="sp-recording-status-toast" id="recordingToast">
                    🎙 Đang ghi âm... Nhấn lại để dừng
                </div>

                <button type="button" class="sp-main-mic-btn" id="mainMicBtn" onclick="toggleMainRecording()" aria-label="Ghi âm giọng nói">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                </button>
            </div>
        </footer>

    </div>

    {{-- JavaScript điều khiển toàn bộ logic tương tác Luyện nói --}}
    <script>
        // Dữ liệu danh sách câu luyện nói
        const sentencesData = [
            {
                id: 1,
                title: "1. If you have a good job, think smart.",
                redChunk: "If you have a good job,",
                greenChunk: "think smart.",
                ipa: "ɪf   ju:   hæv   ə   gʊd   dʒɒb,   θɪŋk   smɑ:t.",
                translation: "Nếu bạn có một công việc tốt, hãy suy nghĩ thông minh.",
                fullText: "If you have a good job, think smart."
            },
            {
                id: 2,
                title: "2. Our affordable loans can start repairing your credit score.",
                redChunk: "Our affordable loans",
                greenChunk: "can start repairing your credit score.",
                ipa: "ˈaʊər   əˈfɔːdəbl   ləʊnz   kæn   stɑːt   rɪˈpeərɪŋ   jɔː   ˈkrɛdɪt   skɔː.",
                translation: "Các gói vay phải chăng của chúng tôi có thể bắt đầu cải thiện điểm tín dụng của bạn.",
                fullText: "Our affordable loans can start repairing your credit score."
            },
            {
                id: 3,
                title: "3. Say goodbye to cash advances on your credit card.",
                redChunk: "Say goodbye to cash advances",
                greenChunk: "on your credit card.",
                ipa: "seɪ   ɡʊdˈbaɪ   tuː   kæʃ   ədˈvɑːnsɪz   ɒn   jɔː   ˈkrɛdɪt   kɑːd.",
                translation: "Hãy nói lời tạm biệt với việc ứng tiền mặt từ thẻ tín dụng của bạn.",
                fullText: "Say goodbye to cash advances on your credit card."
            }
        ];

        let currentIndex = 0;
        let isPlaying = false;
        let playInterval = null;
        let playProgress = 0;
        let currentSpeed = 1;
        const speeds = [0.75, 1, 1.25];
        let isRecording = false;
        let recordingSeconds = 0;
        let recordingTimer = null;
        let voicesList = ['John (US)', 'Emma (UK)', 'Alex (AU)'];
        let currentVoiceIndex = 0;

        // Cập nhật câu hiện tại
        function renderSentence(index) {
            const data = sentencesData[index];
            document.getElementById('speakingHeaderTitle').textContent = data.title;
            document.getElementById('chunkRed').textContent = data.redChunk;
            document.getElementById('chunkGreen').textContent = data.greenChunk;
            document.getElementById('sentenceIpa').textContent = data.ipa;
            document.getElementById('sentenceTranslation').innerHTML = `💡 <strong>Bản dịch:</strong> ${data.translation}`;

            // Cập nhật trạng thái nút chuyển câu
            document.getElementById('prevQuestionBtn').disabled = (index === 0);
            if (index === 0) {
                document.getElementById('prevQuestionBtn').classList.add('sp-nav-btn--disabled');
            } else {
                document.getElementById('prevQuestionBtn').classList.remove('sp-nav-btn--disabled');
            }

            document.getElementById('nextQuestionBtn').disabled = (index === sentencesData.length - 1);
            if (index === sentencesData.length - 1) {
                document.getElementById('nextQuestionBtn').classList.add('sp-nav-btn--disabled');
            } else {
                document.getElementById('nextQuestionBtn').classList.remove('sp-nav-btn--disabled');
            }

            resetPlayer();
        }

        function navigateSentence(direction) {
            const newIndex = currentIndex + direction;
            if (newIndex >= 0 && newIndex < sentencesData.length) {
                currentIndex = newIndex;
                renderSentence(currentIndex);
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

        // Audio Player Controls
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

            // Dùng SpeechSynthesis phát âm chuẩn
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const text = sentencesData[currentIndex].fullText;
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'en-US';
                utterance.rate = currentSpeed;
                window.speechSynthesis.speak(utterance);
            }

            // Thanh tiến trình giả lập 2 giây
            clearInterval(playInterval);
            const totalDuration = 2000 / currentSpeed;
            const startTime = Date.now() - (playProgress * totalDuration);

            playInterval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                playProgress = Math.min(1, elapsed / totalDuration);

                const fillPercent = (playProgress * 100).toFixed(1) + '%';
                document.getElementById('playerFill').style.width = fillPercent;
                document.getElementById('playerThumb').style.left = fillPercent;

                const curSec = Math.floor(playProgress * 2);
                document.getElementById('playerCurrentTime').textContent = `00:0${curSec}`;

                if (playProgress >= 1) {
                    pauseAudio();
                    playProgress = 0;
                    document.getElementById('playerFill').style.width = '0%';
                    document.getElementById('playerThumb').style.left = '0%';
                    document.getElementById('playerCurrentTime').textContent = '00:00';
                }
            }, 50);
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
        }

        function seekAudio(e) {
            const rect = document.getElementById('playerTrackContainer').getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            playProgress = Math.max(0, Math.min(1, clickX / rect.width));
            const fillPercent = (playProgress * 100).toFixed(1) + '%';
            document.getElementById('playerFill').style.width = fillPercent;
            document.getElementById('playerThumb').style.left = fillPercent;
        }

        // Bấm từng từ/cụm để phát âm
        function speakChunk(type) {
            const data = sentencesData[currentIndex];
            const text = (type === 'red') ? data.redChunk : data.greenChunk;
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'en-US';
                utterance.rate = 0.85;
                window.speechSynthesis.speak(utterance);
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

        // Ghi âm tương tác tại nút Mic dưới cùng
        function toggleMainRecording() {
            const micBtn = document.getElementById('mainMicBtn');
            const toast = document.getElementById('recordingToast');

            if (!isRecording) {
                // Bắt đầu ghi âm
                isRecording = true;
                recordingSeconds = 0;
                micBtn.classList.add('is-recording');
                toast.classList.add('is-visible');
                toast.textContent = '🎙 Đang ghi âm (00:00)... Nhấn lại để hoàn tất';

                recordingTimer = setInterval(() => {
                    recordingSeconds++;
                    const s = recordingSeconds < 10 ? '0' + recordingSeconds : recordingSeconds;
                    toast.textContent = `🎙 Đang ghi âm (00:${s})... Nhấn lại để hoàn tất`;

                    // Tự động dừng sau 6 giây
                    if (recordingSeconds >= 6) {
                        finishMainRecording();
                    }
                }, 1000);
            } else {
                finishMainRecording();
            }
        }

        function finishMainRecording() {
            clearInterval(recordingTimer);
            isRecording = false;
            const micBtn = document.getElementById('mainMicBtn');
            const toast = document.getElementById('recordingToast');

            micBtn.classList.remove('is-recording');
            toast.textContent = '✅ Đang chấm điểm phát âm...';

            setTimeout(() => {
                toast.classList.remove('is-visible');
                addRecordingItem();
            }, 800);
        }

        function addRecordingItem() {
            document.getElementById('recordingsEmpty').style.display = 'none';
            const list = document.getElementById('recordingsList');
            list.style.display = 'block';

            const item = document.createElement('div');
            item.className = 'sp-recording-item';
            const timeStr = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

            item.innerHTML = `
                <div class="sp-recording-info">
                    <button type="button" class="sp-circle-plus-btn" style="width: 32px; height: 32px; border-color: #10B981; color: #10B981;" onclick="playSampleVoice()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    </button>
                    <div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #1E293B;">Bản ghi âm lúc ${timeStr}</div>
                        <div style="font-size: 12.5px; color: #64748B;">Độ chuẩn xác: 95% • Ngữ điệu: Tự nhiên</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="sp-recording-score">95/100</span>
                    <button type="button" style="border: none; background: transparent; cursor: pointer; color: #94A3B8; padding: 4px;" onclick="this.closest('.sp-recording-item').remove()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            `;
            list.prepend(item);
        }

        function playSampleVoice() {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(sentencesData[currentIndex].fullText);
                utterance.lang = 'en-US';
                window.speechSynthesis.speak(utterance);
            }
        }

        // Khởi tạo hiển thị câu đầu tiên
        renderSentence(0);
    </script>
</body>
</html>

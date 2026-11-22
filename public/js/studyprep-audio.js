/**
 * StudyPrep Global Audio & Speech Helper
 * Cung cấp giải pháp phát âm thanh toàn diện cho:
 * 1. Từ vựng & Flashcards (Web Speech API với fallback Google / Free Dictionary Audio)
 * 2. Ngân hàng câu hỏi & Đề thi mô phỏng TOEIC (HTML5 Audio với fallback SpeechSynthesis)
 */
(function (global) {
    'use strict';

    // =========================================================================
    // PHẦN 1: PHÁT ÂM TỪ VỰNG & CÂU VÍ DỤ (speakWord / playAudio)
    // =========================================================================

    var cachedEnglishVoice = null;
    var currentAudioObj = null;

    function getBestEnglishVoice() {
        if (!('speechSynthesis' in global)) return null;
        var voices = global.speechSynthesis.getVoices() || [];
        if (!voices.length) return null;

        var preferred = ['en-US', 'en-GB', 'en_US', 'en_GB', 'en'];
        for (var i = 0; i < preferred.length; i++) {
            var target = preferred[i].toLowerCase();
            for (var j = 0; j < voices.length; j++) {
                var lang = String(voices[j].lang || '').toLowerCase();
                if (lang === target || lang.indexOf(target) === 0) {
                    return voices[j];
                }
            }
        }
        return voices[0] || null;
    }

    if ('speechSynthesis' in global && typeof global.speechSynthesis.onvoiceschanged !== 'undefined') {
        global.speechSynthesis.onvoiceschanged = function () {
            cachedEnglishVoice = getBestEnglishVoice();
        };
    }

    /**
     * Phát âm một từ vựng tiếng Anh hoặc một câu ví dụ
     * @param {string} text - Từ hoặc câu cần phát âm
     * @param {HTMLElement|null} btnElement - Nút bấm (để thêm hiệu ứng xoay / sáng khi đang phát)
     * @param {number} rate - Tốc độ đọc (mặc định 0.9)
     */
    function speakWord(text, btnElement, rate) {
        if (!text || typeof text !== 'string') return;
        var cleanText = text.trim();
        if (!cleanText) return;

        // Dừng âm thanh đang phát trước đó
        stopAllAudio();

        if (btnElement && btnElement.classList) {
            btnElement.classList.add('is-playing');
            btnElement.classList.add('audio-active');
        }

        function clearBtnState() {
            if (btnElement && btnElement.classList) {
                btnElement.classList.remove('is-playing');
                btnElement.classList.remove('audio-active');
            }
        }

        // Ưu tiên 1: Web Speech API chuẩn trình duyệt
        if ('speechSynthesis' in global) {
            try {
                global.speechSynthesis.cancel();
                var utterance = new SpeechSynthesisUtterance(cleanText);
                utterance.lang = 'en-US';
                utterance.rate = typeof rate === 'number' ? rate : 0.9;
                utterance.pitch = 1.0;

                var voice = cachedEnglishVoice || getBestEnglishVoice();
                if (voice) {
                    utterance.voice = voice;
                }

                utterance.onend = clearBtnState;
                utterance.onerror = function (e) {
                    clearBtnState();
                    // Fallback sang Google TTS Audio nếu SpeechSynthesis gặp sự cố
                    playTtsAudioFallback(cleanText, clearBtnState);
                };

                global.speechSynthesis.speak(utterance);
                return;
            } catch (err) {
                console.warn('SpeechSynthesis error, falling back to TTS URL:', err);
            }
        }

        // Ưu tiên 2 (Fallback): Google TTS audio URL trực tiếp
        playTtsAudioFallback(cleanText, clearBtnState);
    }

    function playTtsAudioFallback(text, onComplete) {
        try {
            var encoded = encodeURIComponent(text.slice(0, 200));
            var ttsUrl = 'https://translate.google.com/translate_tts?ie=UTF-8&tl=en&client=tw-ob&q=' + encoded;
            var audio = new Audio(ttsUrl);
            currentAudioObj = audio;

            audio.onended = function () {
                currentAudioObj = null;
                if (typeof onComplete === 'function') onComplete();
            };

            audio.onerror = function () {
                currentAudioObj = null;
                if (typeof onComplete === 'function') onComplete();
            };

            audio.play().catch(function () {
                currentAudioObj = null;
                if (typeof onComplete === 'function') onComplete();
            });
        } catch (e) {
            if (typeof onComplete === 'function') onComplete();
        }
    }

    function stopAllAudio() {
        if ('speechSynthesis' in global) {
            try { global.speechSynthesis.cancel(); } catch (e) {}
        }
        if (currentAudioObj) {
            try {
                currentAudioObj.pause();
                currentAudioObj.currentTime = 0;
            } catch (e) {}
            currentAudioObj = null;
        }
        if (global.ToeicAudioPlayer && typeof global.ToeicAudioPlayer.stop === 'function') {
            try { global.ToeicAudioPlayer.stop(); } catch (e) {}
        }
    }

    // Đăng ký toàn cục
    global.speakWord = speakWord;
    global.playAudio = speakWord;
    global.stopAllAudio = stopAllAudio;

})(window);

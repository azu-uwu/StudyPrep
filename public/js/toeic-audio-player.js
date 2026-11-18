/**
 * ToeicAudioPlayer — Bộ phát âm thanh 2 lớp dùng chung cho Admin Preview & Student Exam Player.
 * Lớp 1: Nếu có resolved_audio_url -> phát trực tiếp qua HTML5 <audio>.
 * Lớp 2: Nếu chưa có file MP3 (null) -> dùng window.speechSynthesis:
 *        - Đợi onvoiceschanged để chọn giọng en-US / en-GB chuẩn.
 *        - Tách đoạn văn dài thành từng câu ngắn đọc nối tiếp qua onend (tránh lỗi Chrome ngắt giữa chừng).
 *        - Đồng bộ thanh tiến trình & nút Play/Pause.
 */
(function (global) {
    'use strict';

    var activeHtmlAudio = null;
    var activeChunks = [];
    var activeChunkIndex = 0;
    var activeSpeaking = false;
    var activePaused = false;
    var activeTimer = null;
    var activeElapsed = 0;
    var activeEstimatedDuration = 10;
    var activeCallbacks = {};
    var cachedEnglishVoice = null;

    function selectBestEnglishVoice() {
        if (!('speechSynthesis' in global)) {
            return null;
        }
        var voices = global.speechSynthesis.getVoices() || [];
        if (!voices.length) {
            return null;
        }

        var preferredLangs = ['en-US', 'en-GB', 'en_US', 'en_GB', 'en'];
        for (var i = 0; i < preferredLangs.length; i++) {
            var target = preferredLangs[i].toLowerCase();
            for (var j = 0; j < voices.length; j++) {
                var vLang = String(voices[j].lang || '').toLowerCase();
                if (vLang === target || vLang.indexOf(target) === 0) {
                    return voices[j];
                }
            }
        }
        return null;
    }

    function ensureVoicesReady(callback) {
        if (!('speechSynthesis' in global)) {
            callback(null);
            return;
        }
        var voice = cachedEnglishVoice || selectBestEnglishVoice();
        if (voice) {
            cachedEnglishVoice = voice;
            callback(voice);
            return;
        }

        var finished = false;
        var finishOnce = function () {
            if (finished) return;
            finished = true;
            cachedEnglishVoice = selectBestEnglishVoice();
            callback(cachedEnglishVoice);
        };

        if (typeof global.speechSynthesis.onvoiceschanged !== 'undefined') {
            var prevHandler = global.speechSynthesis.onvoiceschanged;
            global.speechSynthesis.onvoiceschanged = function () {
                if (typeof prevHandler === 'function') {
                    try { prevHandler(); } catch (e) {}
                }
                finishOnce();
            };
        }

        // Timeout dự phòng nếu trình duyệt không kích hoạt onvoiceschanged
        setTimeout(finishOnce, 250);
    }

    function splitIntoSpeechChunks(rawText) {
        var text = String(rawText || '')
            .replace(/---[\s\S]*$/g, '')
            .replace(/\[Dịch[^\]]*\][\s\S]*$/gi, '')
            .replace(/\r\n|\r/g, '\n')
            .replace(/\s+/g, ' ')
            .trim();

        if (!text) return [];
        if (text.length <= 160) return [text];

        var sentences = text.split(/(?<=[.?!;])\s+/);
        var chunks = [];
        var current = '';

        for (var i = 0; i < sentences.length; i++) {
            var s = sentences[i].trim();
            if (!s) continue;

            if ((current + ' ' + s).trim().length <= 160) {
                current = (current + ' ' + s).trim();
            } else {
                if (current) chunks.push(current);
                if (s.length > 160) {
                    var words = s.split(' ');
                    var buf = '';
                    for (var w = 0; w < words.length; w++) {
                        if ((buf + ' ' + words[w]).trim().length <= 160) {
                            buf = (buf + ' ' + words[w]).trim();
                        } else {
                            if (buf) chunks.push(buf);
                            buf = words[w];
                        }
                    }
                    current = buf;
                } else {
                    current = s;
                }
            }
        }
        if (current) chunks.push(current);
        return chunks;
    }

    function stopTimers() {
        if (activeTimer) {
            clearInterval(activeTimer);
            activeTimer = null;
        }
    }

    function stopAll() {
        stopTimers();
        if (activeHtmlAudio) {
            try {
                activeHtmlAudio.pause();
                activeHtmlAudio.currentTime = 0;
            } catch (e) {}
            activeHtmlAudio = null;
        }
        activeSpeaking = false;
        activePaused = false;
        activeChunks = [];
        activeChunkIndex = 0;
        activeElapsed = 0;

        if ('speechSynthesis' in global) {
            try { global.speechSynthesis.cancel(); } catch (e) {}
        }
    }

    function speakNextChunk(voice) {
        if (!activeSpeaking || activePaused) return;
        if (activeChunkIndex >= activeChunks.length) {
            stopTimers();
            activeSpeaking = false;
            if (typeof activeCallbacks.onEnded === 'function') {
                activeCallbacks.onEnded();
            }
            return;
        }

        var chunkText = activeChunks[activeChunkIndex];
        var utter = new SpeechSynthesisUtterance(chunkText);
        utter.lang = (voice && voice.lang) ? voice.lang : 'en-US';
        if (voice) utter.voice = voice;
        utter.rate = activeCallbacks.rate || 0.95;

        utter.onend = function () {
            if (!activeSpeaking || activePaused) return;
            activeChunkIndex += 1;
            speakNextChunk(voice);
        };

        utter.onerror = function () {
            if (!activeSpeaking) return;
            activeChunkIndex += 1;
            speakNextChunk(voice);
        };

        global.speechSynthesis.speak(utter);
    }

    function playSpeech(text, callbacks) {
        stopAll();
        activeCallbacks = callbacks || {};
        activeChunks = splitIntoSpeechChunks(text);
        if (!activeChunks.length) {
            if (typeof activeCallbacks.onError === 'function') {
                activeCallbacks.onError('Không có nội dung transcript để đọc.');
            }
            return;
        }

        var wordCount = text.trim().split(/\s+/).length;
        activeEstimatedDuration = activeCallbacks.duration || Math.max(4, Math.round((wordCount / 130) * 60));
        activeElapsed = 0;
        activeSpeaking = true;
        activePaused = false;
        activeChunkIndex = 0;

        if (typeof activeCallbacks.onPlay === 'function') {
            activeCallbacks.onPlay(activeEstimatedDuration);
        }

        activeTimer = setInterval(function () {
            if (!activeSpeaking || activePaused) return;
            if (activeElapsed < activeEstimatedDuration) {
                activeElapsed += 1;
                if (typeof activeCallbacks.onProgress === 'function') {
                    activeCallbacks.onProgress(activeElapsed, activeEstimatedDuration);
                }
            }
        }, 1000);

        ensureVoicesReady(function (voice) {
            if (!activeSpeaking) return;
            speakNextChunk(voice);
        });
    }

    function playMp3(audioEl, url, callbacks) {
        stopAll();
        activeCallbacks = callbacks || {};
        if (!audioEl) {
            audioEl = new Audio();
        }
        activeHtmlAudio = audioEl;
        audioEl.src = url;

        audioEl.onloadedmetadata = function () {
            var dur = isFinite(audioEl.duration) ? Math.round(audioEl.duration) : 0;
            if (typeof activeCallbacks.onMetadata === 'function') {
                activeCallbacks.onMetadata(dur);
            }
        };

        audioEl.ontimeupdate = function () {
            var cur = Math.floor(audioEl.currentTime || 0);
            var dur = isFinite(audioEl.duration) ? Math.round(audioEl.duration) : 0;
            if (typeof activeCallbacks.onProgress === 'function') {
                activeCallbacks.onProgress(cur, dur);
            }
        };

        audioEl.onended = function () {
            if (typeof activeCallbacks.onEnded === 'function') {
                activeCallbacks.onEnded();
            }
        };

        audioEl.onerror = function () {
            if (typeof activeCallbacks.onError === 'function') {
                activeCallbacks.onError('Không thể tải file âm thanh MP3.');
            }
        };

        var playPromise = audioEl.play();
        if (typeof activeCallbacks.onPlay === 'function') {
            activeCallbacks.onPlay(0);
        }
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function () {
                if (typeof activeCallbacks.onPause === 'function') {
                    activeCallbacks.onPause();
                }
            });
        }
    }

    function pauseCurrent() {
        if (activeHtmlAudio) {
            activeHtmlAudio.pause();
            if (typeof activeCallbacks.onPause === 'function') {
                activeCallbacks.onPause();
            }
            return;
        }
        if (activeSpeaking) {
            activePaused = true;
            if ('speechSynthesis' in global) {
                global.speechSynthesis.cancel();
            }
            if (typeof activeCallbacks.onPause === 'function') {
                activeCallbacks.onPause();
            }
        }
    }

    function resumeCurrent() {
        if (activeHtmlAudio) {
            activeHtmlAudio.play();
            if (typeof activeCallbacks.onPlay === 'function') {
                var dur = isFinite(activeHtmlAudio.duration) ? Math.round(activeHtmlAudio.duration) : 0;
                activeCallbacks.onPlay(dur);
            }
            return;
        }
        if (activeSpeaking && activePaused) {
            activePaused = false;
            if (typeof activeCallbacks.onPlay === 'function') {
                activeCallbacks.onPlay(activeEstimatedDuration);
            }
            ensureVoicesReady(function (voice) {
                speakNextChunk(voice);
            });
        }
    }

    function seekSeconds(delta) {
        if (activeHtmlAudio && isFinite(activeHtmlAudio.duration)) {
            var nextTime = Math.max(0, Math.min(activeHtmlAudio.duration, (activeHtmlAudio.currentTime || 0) + delta));
            activeHtmlAudio.currentTime = nextTime;
            return Math.floor(nextTime);
        }
        return null;
    }

    function seekTo(second) {
        if (activeHtmlAudio && isFinite(activeHtmlAudio.duration)) {
            activeHtmlAudio.currentTime = Math.max(0, Math.min(activeHtmlAudio.duration, second));
        }
    }

    global.ToeicAudioPlayer = {
        playMp3: playMp3,
        playSpeech: playSpeech,
        pause: pauseCurrent,
        resume: resumeCurrent,
        stop: stopAll,
        seekBy: seekSeconds,
        seekTo: seekTo,
        splitIntoChunks: splitIntoSpeechChunks,
        isSpeaking: function () { return activeSpeaking && !activePaused; },
        hasActiveHtmlAudio: function () { return Boolean(activeHtmlAudio); }
    };
})(window);


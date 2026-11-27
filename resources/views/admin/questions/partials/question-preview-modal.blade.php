{{-- MODAL XEM TRƯỚC MÔ PHỎNG ĐỀ THI TOEIC THẬT (Dùng chung cho Ngân hàng câu hỏi & Bulk Import) --}}
<div id="modal-preview-question" class="admin-modal-backdrop" onclick="if (event.target === this) { closePreviewExamModal(); }">
    <div class="admin-modal-box" style="max-width: 780px; width: 95vw; border-radius: 16px; overflow: hidden;">
        {{-- Header mô phỏng phòng thi TOEIC --}}
        <div class="admin-modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                <span id="preview-skill-badge" style="display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 800; letter-spacing: 0.02em; text-transform: uppercase;">Reading</span>
                <h3 class="admin-modal-title" id="preview-modal-title" style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0;">
                    Xem trước câu hỏi
                </h3>
                <span id="preview-code-pill" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; font-weight: 700; color: #475569; background: #e2e8f0; padding: 2px 8px; border-radius: 6px;">#Q-0</span>
                <span class="q-source-pill" id="preview-source" style="display: none;"></span>
                <span class="status-pill status-badge-active" id="preview-status" style="margin-left: auto;">• Hoạt động</span>
            </div>
            <button type="button" class="admin-modal-close" onclick="closePreviewExamModal()" style="margin-left: 10px;">&times;</button>
        </div>

        {{-- Body mô phỏng đề thi thật --}}
        <div class="admin-modal-body" style="max-height: 76vh; overflow-y: auto; padding: 20px; background: #ffffff;">
            {{-- Vùng Stimulus: Hình ảnh / Audio / Đoạn văn / Email / Transcript --}}
            <div id="preview-stimulus-section" style="display: none; margin-bottom: 18px;">
                <div id="preview-media-wrap" style="display: none; margin-bottom: 14px; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;"></div>

                <div id="preview-tts-bar" style="display: none; margin-bottom: 12px; padding: 10px 14px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #0369a1; font-weight: 600;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                        </svg>
                        <span id="preview-tts-status">Chưa có file MP3 — Nghe thử bằng trình duyệt (TTS) hoặc sinh file MP3 ngay</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <button type="button" id="preview-tts-btn" onclick="togglePreviewTts()" style="padding: 5px 12px; border-radius: 8px; border: 1px solid #0284c7; background: #0284c7; color: #fff; font-size: 12px; font-weight: 700; cursor: pointer;">
                            Nghe thử
                        </button>
                        <button type="button" id="preview-gen-mp3-btn" onclick="generateMp3FromPreview()" style="padding: 5px 12px; border-radius: 8px; border: 1px solid #4338ca; background: #eef2ff; color: #4338ca; font-size: 12px; font-weight: 700; cursor: pointer;">
                            Sinh Audio MP3
                        </button>
                    </div>
                </div>

                <div id="preview-passage-box" style="display: none; background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #4f46e5; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px;">
                    <div id="preview-passage-label" style="font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #4338ca; margin-bottom: 8px;">
                        Đoạn văn / Tài liệu đề bài
                    </div>
                    <div id="preview-passage-text" style="font-size: 13.5px; color: #1e293b; line-height: 1.65; white-space: pre-line;"></div>
                </div>

                <div id="preview-transcript-wrap" style="display: none; margin-bottom: 8px;">
                    <button type="button" id="preview-transcript-toggle" onclick="togglePreviewTranscript()" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-size: 12.5px; font-weight: 700; cursor: pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <span id="preview-transcript-toggle-text">Xem Transcript / Lời thoại</span>
                    </button>
                    <div id="preview-transcript-body" style="display: none; margin-top: 8px; padding: 12px 14px; background: #f1f5f9; border: 1px dashed #94a3b8; border-radius: 10px; font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-line;"></div>
                </div>
            </div>

            {{-- Vùng nội dung chính theo từng kỹ năng (L&R Single / L&R Grouped / Speaking / Writing) --}}
            <div id="preview-exam-body"></div>

            {{-- Vùng giải thích / bản dịch song ngữ độc lập (cho câu đơn & Speaking/Writing) --}}
            <div id="preview-explanation-card" style="display: none; margin-top: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 16px;">
                <div id="preview-explanation-heading" style="font-size: 12.5px; font-weight: 800; color: #92400e; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;">
                    Giải thích &amp; Bản dịch song ngữ
                </div>
                <div id="preview-explanation-text" style="font-size: 13.5px; color: #451a03; line-height: 1.65; white-space: pre-line;"></div>
            </div>
        </div>

        <div class="admin-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
            <span id="preview-footer-meta" style="font-size: 12px; color: #64748b; font-weight: 600;"></span>
            <button type="button" class="btn-admin-action" onclick="closePreviewExamModal()">Đóng</button>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="{{ asset('js/toeic-audio-player.js') }}"></script>
<script>
    (function () {
        var previewMicTimer = null;
        var previewMicSeconds = 0;
        var previewTtsText = '';
        var currentPreviewPayload = null;

        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function stopPreviewSimulators() {
            if (previewMicTimer) {
                clearInterval(previewMicTimer);
                previewMicTimer = null;
            }
            previewMicSeconds = 0;
            if (window.ToeicAudioPlayer) {
                window.ToeicAudioPlayer.stop();
            } else if (window.speechSynthesis) {
                try { window.speechSynthesis.cancel(); } catch (e) {}
            }
        }

        window.closePreviewExamModal = function () {
            stopPreviewSimulators();
            if (typeof closeAdminModal === 'function') {
                closeAdminModal('modal-preview-question');
            } else {
                var modal = document.getElementById('modal-preview-question');
                if (modal) modal.classList.remove('open');
            }
        };

        window.togglePreviewTranscript = function () {
            var body = document.getElementById('preview-transcript-body');
            var label = document.getElementById('preview-transcript-toggle-text');
            if (!body) return;
            var isHidden = body.style.display === 'none';
            body.style.display = isHidden ? 'block' : 'none';
            if (label) {
                label.textContent = isHidden ? 'Ẩn Transcript / Lời thoại' : 'Xem Transcript / Lời thoại';
            }
        };

        window.togglePreviewTts = function () {
            var btn = document.getElementById('preview-tts-btn');
            if (!previewTtsText || !window.ToeicAudioPlayer) return;

            if (window.ToeicAudioPlayer.isSpeaking()) {
                window.ToeicAudioPlayer.stop();
                if (btn) btn.textContent = 'Nghe thử';
                return;
            }

            window.ToeicAudioPlayer.playSpeech(previewTtsText, {
                onPlay: function () {
                    if (btn) btn.textContent = 'Dừng đọc';
                },
                onEnded: function () {
                    if (btn) btn.textContent = 'Nghe thử';
                },
                onPause: function () {
                    if (btn) btn.textContent = 'Nghe thử';
                }
            });
        };

        window.generateMp3FromPreview = function () {
            if (!currentPreviewPayload) return;
            var btn = document.getElementById('preview-gen-mp3-btn');
            var statusSpan = document.getElementById('preview-tts-status');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Đang sinh MP3...';
            }
            if (statusSpan) {
                statusSpan.textContent = 'Đang gọi TextToSpeechService tạo file MP3...';
            }

            var qSet = currentPreviewPayload.question_set || null;
            fetch('{{ url('/api/admin/questions/generate-tts') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: currentPreviewPayload.question_id,
                    question_set_id: qSet ? qSet.question_set_id : null
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = 'Sinh Audio MP3';
                }
                if (res && res.success && res.audio_url) {
                    var mediaWrap = document.getElementById('preview-media-wrap');
                    var ttsBar = document.getElementById('preview-tts-bar');
                    if (mediaWrap) {
                        var oldAudio = mediaWrap.querySelector('audio');
                        if (oldAudio) oldAudio.remove();
                        var audio = document.createElement('audio');
                        audio.controls = true;
                        audio.src = res.audio_url;
                        audio.style.cssText = 'width:100%;height:40px;margin-top:8px;';
                        mediaWrap.appendChild(audio);
                        mediaWrap.style.display = 'block';
                    }
                    if (ttsBar) ttsBar.style.display = 'none';
                } else {
                    if (statusSpan) statusSpan.textContent = (res && res.message) ? res.message : 'Không thể sinh MP3.';
                }
            })
            .catch(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = 'Sinh Audio MP3';
                }
                if (statusSpan) statusSpan.textContent = 'Lỗi kết nối khi sinh file MP3.';
            });
        };

        window.togglePreviewMicSim = function () {
            var btn = document.getElementById('preview-mic-btn');
            var statusEl = document.getElementById('preview-mic-status');
            if (previewMicTimer) {
                clearInterval(previewMicTimer);
                previewMicTimer = null;
                if (btn) {
                    btn.style.background = '#d97706';
                    btn.innerHTML = '<span>🎙 Bắt đầu ghi âm thử</span>';
                }
                if (statusEl) {
                    statusEl.textContent = 'Đã dừng ghi âm mô phỏng (' + previewMicSeconds + ' giây).';
                }
                return;
            }
            previewMicSeconds = 0;
            if (btn) {
                btn.style.background = '#dc2626';
                btn.innerHTML = '<span>■ Dừng ghi âm</span>';
            }
            if (statusEl) {
                statusEl.textContent = 'Đang ghi âm mô phỏng... 00:00';
            }
            previewMicTimer = setInterval(function () {
                previewMicSeconds += 1;
                var mm = String(Math.floor(previewMicSeconds / 60)).padStart(2, '0');
                var ss = String(previewMicSeconds % 60).padStart(2, '0');
                if (statusEl) {
                    statusEl.textContent = 'Đang ghi âm mô phỏng... ' + mm + ':' + ss;
                }
            }, 1000);
        };

        window.updatePreviewWritingCount = function (textarea, minWords) {
            var counter = document.getElementById('preview-writing-wordcount');
            if (!counter) return;
            var text = (textarea.value || '').trim();
            var words = text ? text.split(/\s+/).length : 0;
            counter.textContent = words + ' từ' + (minWords ? ' / mục tiêu ' + minWords + '+ từ' : '');
            counter.style.color = (minWords && words >= minWords) ? '#047857' : '#6d28d9';
            counter.style.background = (minWords && words >= minWords) ? '#d1fae5' : '#ede9fe';
        };

        function getSkillStyle(skillKey) {
            var map = {
                listening: { bg: '#e0f2fe', color: '#0369a1', border: '#bae6fd', label: 'Listening' },
                reading:   { bg: '#d1fae5', color: '#047857', border: '#a7f3d0', label: 'Reading' },
                speaking:  { bg: '#fef3c7', color: '#b45309', border: '#fde68a', label: 'Speaking' },
                writing:   { bg: '#ede9fe', color: '#6d28d9', border: '#ddd6fe', label: 'Writing' }
            };
            return map[skillKey] || map.reading;
        }

        function renderOptionsHtml(options) {
            if (!Array.isArray(options) || options.length === 0) {
                return '';
            }
            var html = '<div style="display: flex; flex-direction: column; gap: 7px; margin-top: 10px;">';
            options.forEach(function (o) {
                var isCorrect = Boolean(o.is_correct);
                var rowStyle = isCorrect
                    ? 'display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:9px 12px;border-radius:9px;background:#ecfdf5;border:1.5px solid #6ee7b7;color:#065f46;font-weight:700;font-size:13.5px;'
                    : 'display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:9px 12px;border-radius:9px;background:#ffffff;border:1px solid #e2e8f0;color:#334155;font-size:13.5px;';
                var circleStyle = isCorrect
                    ? 'display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:999px;background:#059669;color:#ffffff;font-size:12px;font-weight:800;flex-shrink:0;'
                    : 'display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:12px;font-weight:700;flex-shrink:0;';

                html += '<div style="' + rowStyle + '">';
                html += '<div style="display:flex;align-items:flex-start;gap:10px;">';
                html += '<span style="' + circleStyle + '">' + escapeHtml(o.label) + '</span>';
                html += '<span style="line-height:1.45;padding-top:2px;">' + escapeHtml(o.content) + '</span>';
                html += '</div>';
                if (isCorrect) {
                    html += '<span style="white-space:nowrap;font-size:11.5px;font-weight:800;padding:3px 8px;border-radius:999px;background:#059669;color:#ffffff;flex-shrink:0;">✓ Đáp án đúng</span>';
                }
                html += '</div>';
            });
            html += '</div>';
            return html;
        }

        window.openPreviewModal = function (payload) {
            stopPreviewSimulators();
            if (!payload) return;
            currentPreviewPayload = payload;

            var skillKey = payload.skill_key || 'reading';
            var skillStyle = getSkillStyle(skillKey);
            var qSet = payload.question_set || null;
            var groupCount = Number(payload.group_count || (qSet && qSet.group_count) || 1);
            var partCode = String(payload.part_code || '').toUpperCase();

            // 1. Header
            var skillBadge = document.getElementById('preview-skill-badge');
            if (skillBadge) {
                skillBadge.textContent = payload.skill_label || skillStyle.label;
                skillBadge.style.background = skillStyle.bg;
                skillBadge.style.color = skillStyle.color;
                skillBadge.style.border = '1px solid ' + skillStyle.border;
            }

            var titleText = payload.part_full_label || payload.part_badge || 'Xem trước câu hỏi';
            if (groupCount > 1 && (skillKey === 'listening' || skillKey === 'reading')) {
                titleText += ' (Nhóm ' + groupCount + ' câu)';
            }
            var titleEl = document.getElementById('preview-modal-title');
            if (titleEl) titleEl.textContent = titleText;

            var codeEl = document.getElementById('preview-code-pill');
            if (codeEl) codeEl.textContent = payload.question_code || ('#Q-' + payload.question_id);

            var srcEl = document.getElementById('preview-source');
            if (srcEl) {
                if (payload.source_tag && payload.source_tag !== 'Chưa gắn bộ đề') {
                    srcEl.textContent = payload.source_tag;
                    srcEl.style.display = 'inline-flex';
                } else {
                    srcEl.style.display = 'none';
                }
            }

            var statusEl = document.getElementById('preview-status');
            if (statusEl) {
                var stUpper = String(payload.status || 'ACTIVE').toUpperCase();
                var stMap = {
                    ACTIVE:   { bg: '#ecfdf5', color: '#047857', border: '#a7f3d0', dot: '#10b981' },
                    DRAFT:    { bg: '#f1f5f9', color: '#475569', border: '#cbd5e1', dot: '#64748b' },
                    PENDING:  { bg: '#fffbeb', color: '#b45309', border: '#fde68a', dot: '#f59e0b' },
                    REPORTED: { bg: '#fef2f2', color: '#b91c1c', border: '#fecaca', dot: '#ef4444' },
                    ARCHIVED: { bg: '#f5f3ff', color: '#5b21b6', border: '#ddd6fe', dot: '#7c3aed' }
                };
                var stCfg = stMap[stUpper] || stMap.DRAFT;
                statusEl.style.cssText = 'margin-left:auto;display:inline-flex;align-items:center;gap:6px;padding:4px 11px;border-radius:9999px;font-size:12px;font-weight:700;white-space:nowrap;background:' + stCfg.bg + ';color:' + stCfg.color + ';border:1px solid ' + stCfg.border + ';';
                statusEl.innerHTML = '<span style="width:6px;height:6px;border-radius:9999px;background:' + stCfg.dot + ';flex-shrink:0;"></span><span>' + escapeHtml(payload.status_label || 'Hoạt động') + '</span>';
            }

            // 2. Stimulus / Media / Transcript
            var stimSection = document.getElementById('preview-stimulus-section');
            var mediaWrap = document.getElementById('preview-media-wrap');
            var ttsBar = document.getElementById('preview-tts-bar');
            var passageBox = document.getElementById('preview-passage-box');
            var passageLabel = document.getElementById('preview-passage-label');
            var passageText = document.getElementById('preview-passage-text');
            var transcriptWrap = document.getElementById('preview-transcript-wrap');
            var transcriptBody = document.getElementById('preview-transcript-body');
            var transcriptToggleText = document.getElementById('preview-transcript-toggle-text');

            var hasStimulusAnything = false;
            var mediaUrl = qSet ? (qSet.resolved_media_url || qSet.media_url || '') : '';
            var audioUrl = qSet ? (qSet.resolved_audio_url || '') : '';
            var isImage = qSet ? Boolean(qSet.is_image) : false;
            var isAudio = Boolean(audioUrl) || (qSet ? Boolean(qSet.is_audio) : false);
            if (mediaUrl && !isImage && !audioUrl) {
                isImage = /\.(png|jpe?g|gif|webp|bmp)$/i.test(mediaUrl) || /^blob:/i.test(mediaUrl) || /^data:image\//i.test(mediaUrl);
                if (!isImage) {
                    audioUrl = mediaUrl;
                    isAudio = true;
                }
            }

            if (mediaWrap) {
                mediaWrap.innerHTML = '';
                mediaWrap.style.padding = (isImage && mediaUrl) ? '6px' : '14px';
                if (isImage && mediaUrl) {
                    var img = document.createElement('img');
                    img.src = mediaUrl;
                    img.alt = 'Hình minh họa câu hỏi';
                    img.style.cssText = 'width:100%;max-width:672px;max-height:420px;border-radius:8px;border:1px solid #cbd5e1;object-fit:contain;box-shadow:0 2px 8px rgba(15,23,42,0.08);display:block;margin:0 auto;';
                    mediaWrap.appendChild(img);
                    hasStimulusAnything = true;
                }
                if (audioUrl) {
                    var audio = document.createElement('audio');
                    audio.controls = true;
                    audio.src = audioUrl;
                    audio.style.cssText = 'width:100%;height:40px;' + (isImage ? 'margin-top:12px;' : '');
                    mediaWrap.appendChild(audio);
                    hasStimulusAnything = true;
                }
                mediaWrap.style.display = (isImage && mediaUrl) || audioUrl ? 'block' : 'none';
            }

            // TTS fallback bar for Listening when no MP3 attached
            previewTtsText = '';
            if (ttsBar) {
                if (skillKey === 'listening' && !audioUrl) {
                    var candidateTts = (qSet && qSet.transcript) ? qSet.transcript : '';
                    if (!candidateTts && Array.isArray(payload.options) && payload.options.length > 0) {
                        var optText = payload.options.map(function (o) {
                            return '(' + o.label + ') ' + o.content;
                        }).join('. ');
                        candidateTts = (partCode === 'PART2' && payload.content ? payload.content + ' ' : '') + optText;
                    }
                    if (!candidateTts) {
                        candidateTts = payload.content || '';
                    }
                    if (candidateTts) {
                        previewTtsText = candidateTts;
                        ttsBar.style.display = 'flex';
                        var ttsBtn = document.getElementById('preview-tts-btn');
                        if (ttsBtn) ttsBtn.textContent = 'Nghe thử';
                        var statusSpan = document.getElementById('preview-tts-status');
                        if (statusSpan) statusSpan.textContent = 'Chưa có file MP3 — Nghe thử bằng trình duyệt (TTS) hoặc sinh file MP3 ngay';
                        hasStimulusAnything = true;
                    } else {
                        ttsBar.style.display = 'none';
                    }
                } else {
                    ttsBar.style.display = 'none';
                }
            }

            // Passage / Email / Schedule stimulus_text
            var stimText = qSet && qSet.stimulus_text ? String(qSet.stimulus_text).trim() : '';
            if (passageBox && passageText) {
                if (stimText) {
                    var pLabel = 'Đoạn văn / Tài liệu đính kèm';
                    if (partCode === 'WRITING_T2') pLabel = 'Email / Yêu cầu cần phản hồi';
                    else if (partCode === 'SPK_T1') pLabel = 'Đoạn văn bản cần đọc to (Read Aloud)';
                    else if (partCode === 'SPK_T4') pLabel = 'Bảng thông tin / Lịch trình tham chiếu';
                    else if (partCode === 'PART6' || partCode === 'PART7') pLabel = 'Đoạn văn đọc hiểu (Reading Passage)';
                    if (passageLabel) passageLabel.textContent = pLabel;
                    passageText.textContent = stimText;
                    passageBox.style.display = 'block';
                    hasStimulusAnything = true;
                } else {
                    passageBox.style.display = 'none';
                }
            }

            // Transcript toggle
            var transcriptText = qSet && qSet.transcript ? String(qSet.transcript).trim() : '';
            if (transcriptWrap && transcriptBody) {
                if (transcriptText) {
                    transcriptBody.textContent = transcriptText;
                    transcriptBody.style.display = 'none';
                    if (transcriptToggleText) transcriptToggleText.textContent = 'Xem Transcript / Lời thoại';
                    transcriptWrap.style.display = 'block';
                    hasStimulusAnything = true;
                } else {
                    transcriptWrap.style.display = 'none';
                }
            }

            if (stimSection) {
                stimSection.style.display = hasStimulusAnything ? 'block' : 'none';
            }

            // 3. Skill-specific body
            var bodyEl = document.getElementById('preview-exam-body');
            var expCard = document.getElementById('preview-explanation-card');
            var expHeading = document.getElementById('preview-explanation-heading');
            var expText = document.getElementById('preview-explanation-text');
            var footerMeta = document.getElementById('preview-footer-meta');

            if (footerMeta) {
                footerMeta.textContent = 'Độ khó: ' + (payload.difficulty || 'MEDIUM') + (groupCount > 1 ? ' • Cụm ' + groupCount + ' câu hỏi' : ' • Câu hỏi đơn');
            }

            if (skillKey === 'speaking') {
                var spkTiming = payload.ets_timing || { prep: '45 giây', response: '30 giây', hint: 'Trả lời rõ ràng, đúng trọng tâm yêu cầu.' };
                var spkHtml = '';
                spkHtml += '<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">';
                spkHtml += '<span style="padding:5px 11px;border-radius:8px;background:#fef3c7;color:#92400e;font-size:12px;font-weight:800;border:1px solid #fde68a;">⏱ Chuẩn bị: ' + escapeHtml(spkTiming.prep) + '</span>';
                spkHtml += '<span style="padding:5px 11px;border-radius:8px;background:#ffedd5;color:#9a3412;font-size:12px;font-weight:800;border:1px solid #fed7aa;">🎤 Trả lời: ' + escapeHtml(spkTiming.response) + '</span>';
                spkHtml += '</div>';

                spkHtml += '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:14px;">';
                spkHtml += '<div style="font-size:11.5px;font-weight:800;color:#b45309;text-transform:uppercase;margin-bottom:6px;">Yêu cầu đề bài (Speaking Prompt)</div>';
                spkHtml += '<div style="font-size:14.5px;font-weight:700;color:#0f172a;line-height:1.6;white-space:pre-line;">' + escapeHtml(payload.content || '') + '</div>';
                if (spkTiming.hint) {
                    spkHtml += '<div style="margin-top:8px;font-size:12.5px;color:#64748b;">💡 ' + escapeHtml(spkTiming.hint) + '</div>';
                }
                spkHtml += '</div>';

                spkHtml += '<div style="background:#fff7ed;border:1px dashed #fdba74;border-radius:12px;padding:14px 16px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">';
                spkHtml += '<div>';
                spkHtml += '<div style="font-size:13px;font-weight:800;color:#9a3412;">Mô phỏng Micro phòng thi TOEIC Speaking</div>';
                spkHtml += '<div id="preview-mic-status" style="font-size:12.5px;color:#7c2d12;margin-top:2px;">Nhấn nút bên cạnh để kiểm tra trải nghiệm bấm giờ nói.</div>';
                spkHtml += '</div>';
                spkHtml += '<button type="button" id="preview-mic-btn" onclick="togglePreviewMicSim()" style="padding:8px 14px;border-radius:10px;border:none;background:#d97706;color:#ffffff;font-size:13px;font-weight:700;cursor:pointer;"><span>🎙 Bắt đầu ghi âm thử</span></button>';
                spkHtml += '</div>';

                if (bodyEl) bodyEl.innerHTML = spkHtml;

                if (expCard && expHeading && expText) {
                    expHeading.textContent = 'Bài nói mẫu / Gợi ý trả lời & Bản dịch';
                    expText.textContent = payload.explanation || 'Chưa có bài nói mẫu cho câu hỏi này.';
                    expCard.style.display = 'block';
                }
            } else if (skillKey === 'writing') {
                var wrtTiming = payload.ets_timing || { time: '10 phút', min_words: 80, hint: 'Viết rõ ràng, đúng ngữ pháp và đủ ý theo yêu cầu.' };
                var wrtHtml = '';
                wrtHtml += '<div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:14px;">';
                wrtHtml += '<span style="padding:5px 11px;border-radius:8px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:800;border:1px solid #ddd6fe;">⏱ Thời gian ETS: ' + escapeHtml(wrtTiming.time) + '</span>';
                wrtHtml += '<span id="preview-writing-wordcount" style="margin-left:auto;padding:5px 11px;border-radius:8px;background:#ede9fe;color:#6d28d9;font-size:12px;font-weight:800;">0 từ' + (wrtTiming.min_words ? ' / mục tiêu ' + wrtTiming.min_words + '+ từ' : '') + '</span>';
                wrtHtml += '</div>';

                wrtHtml += '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:14px;">';
                wrtHtml += '<div style="font-size:11.5px;font-weight:800;color:#6d28d9;text-transform:uppercase;margin-bottom:6px;">Đề bài / Từ khóa bắt buộc (Writing Prompt)</div>';
                wrtHtml += '<div style="font-size:14.5px;font-weight:700;color:#0f172a;line-height:1.6;white-space:pre-line;">' + escapeHtml(payload.content || '') + '</div>';
                if (wrtTiming.hint) {
                    wrtHtml += '<div style="margin-top:8px;font-size:12.5px;color:#64748b;">💡 ' + escapeHtml(wrtTiming.hint) + '</div>';
                }
                wrtHtml += '</div>';

                wrtHtml += '<div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:12px;padding:12px 14px;">';
                wrtHtml += '<label style="display:block;font-size:12px;font-weight:800;color:#6b21a8;margin-bottom:6px;">Khung soạn thảo mô phỏng thí sinh (Thử gõ để đếm từ)</label>';
                wrtHtml += '<textarea rows="4" oninput="updatePreviewWritingCount(this, ' + Number(wrtTiming.min_words || 0) + ')" placeholder="Nhập bài viết mô phỏng tại đây..." style="width:100%;box-sizing:border-box;padding:10px 12px;border-radius:8px;border:1px solid #d8b4fe;font-size:13.5px;line-height:1.55;resize:vertical;font-family:inherit;"></textarea>';
                wrtHtml += '</div>';

                if (bodyEl) bodyEl.innerHTML = wrtHtml;

                if (expCard && expHeading && expText) {
                    expHeading.textContent = 'Bài viết mẫu / Dàn ý gợi ý & Bản dịch';
                    expText.textContent = payload.explanation || 'Chưa có bài viết mẫu / dàn ý cho câu hỏi này.';
                    expCard.style.display = 'block';
                }
            } else if (groupCount > 1 && qSet && Array.isArray(qSet.questions) && qSet.questions.length > 1) {
                // Listening & Reading Grouped (Part 3, 4, 6, 7)
                var grpHtml = '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">';
                grpHtml += '<span style="font-size:12.5px;font-weight:800;color:#4338ca;">Cụm ' + qSet.questions.length + ' câu hỏi dùng chung ngữ liệu bên trên</span>';
                grpHtml += '<span style="font-size:12px;color:#64748b;">Câu đang chọn được đánh dấu nổi bật</span>';
                grpHtml += '</div>';

                qSet.questions.forEach(function (sq, idx) {
                    var isCurrent = Number(sq.question_id) === Number(payload.question_id);
                    var cardStyle = isCurrent
                        ? 'background:#eef2ff;border:1.5px solid #6366f1;border-radius:12px;padding:14px 16px;margin-bottom:12px;'
                        : 'background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px;margin-bottom:12px;';

                    grpHtml += '<div style="' + cardStyle + '">';
                    grpHtml += '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;">';
                    grpHtml += '<span style="font-size:12px;font-weight:800;color:' + (isCurrent ? '#4338ca' : '#475569') + ';">Câu ' + (idx + 1) + ' (' + escapeHtml(sq.question_code || ('#Q-' + sq.question_id)) + ')</span>';
                    if (isCurrent) {
                        grpHtml += '<span style="font-size:11px;font-weight:800;padding:2px 8px;border-radius:999px;background:#4f46e5;color:#ffffff;">Đang xem</span>';
                    }
                    grpHtml += '</div>';
                    grpHtml += '<div style="font-size:14px;font-weight:700;color:#0f172a;line-height:1.55;white-space:pre-line;">' + escapeHtml(sq.content || '') + '</div>';
                    grpHtml += renderOptionsHtml(sq.options || []);

                    if (sq.explanation) {
                        grpHtml += '<div style="margin-top:10px;padding:10px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12.5px;color:#78350f;line-height:1.55;white-space:pre-line;">';
                        grpHtml += '<strong style="color:#92400e;">Giải thích / Bản dịch:</strong>\n' + escapeHtml(sq.explanation);
                        grpHtml += '</div>';
                    }
                    grpHtml += '</div>';
                });

                if (bodyEl) bodyEl.innerHTML = grpHtml;
                if (expCard) expCard.style.display = 'none';
            } else {
                // Listening & Reading Single (Part 1, Part 2, Part 5, or single Part 3/4/6/7)
                var singleHtml = '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">';
                singleHtml += '<div style="font-size:14.5px;font-weight:700;color:#0f172a;line-height:1.6;white-space:pre-line;">' + escapeHtml(payload.content || '') + '</div>';
                singleHtml += renderOptionsHtml(payload.options || []);
                singleHtml += '</div>';

                if (bodyEl) bodyEl.innerHTML = singleHtml;

                if (expCard && expHeading && expText) {
                    expHeading.textContent = 'Giải thích & Bản dịch song ngữ';
                    expText.textContent = payload.explanation || 'Không có giải thích chi tiết.';
                    expCard.style.display = 'block';
                }
            }

            if (typeof openAdminModal === 'function') {
                openAdminModal('modal-preview-question');
            } else {
                var modal = document.getElementById('modal-preview-question');
                if (modal) modal.classList.add('open');
            }
        };
    })();
</script>
@endpush
@endonce


@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Đối chiếu & Chuẩn hóa XML Import | StudyPrep')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.questions.index') }}" style="color: inherit; text-decoration: none;">Ngân hàng câu hỏi</a>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.questions.bulk-import') }}" style="color: inherit; text-decoration: none;">Nhập hàng loạt</a>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">Đối chiếu &amp; Duyệt lưu</span>
@endsection

@section('content')
@php
    $sets = $staging['sets'] ?? [];
    $summary = $staging['summary'] ?? ['total_questions' => 0, 'valid_questions' => 0, 'error_questions' => 0, 'image_count' => 0, 'transcript_count' => 0];
    $partCodesInBatch = collect($sets)->pluck('part')->unique()->values()->all();
    $hasTtsTranscripts = (int) ($summary['transcript_count'] ?? 0) > 0;
@endphp

<style>
    .srev-sticky-bar { position: sticky; top: 10px; z-index: 40; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 12px 18px; margin-bottom: 18px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08); }
    .srev-stat-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; border: 1px solid #e2e8f0; background: #f8fafc; color: #334155; white-space: nowrap; }
    .srev-stat-valid { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .srev-stat-error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .srev-filter-btn { padding: 5px 11px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .srev-filter-btn.active { background: #4338ca; color: #ffffff; border-color: #4338ca; }
    .srev-set-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin-bottom: 16px; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03); }
    .srev-set-card.has-error { border-color: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.10); }
    .srev-set-head { padding: 10px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
    .srev-split-grid { display: grid; grid-template-columns: 40% 60%; }
    @media (max-width: 1024px) { .srev-split-grid { grid-template-columns: 1fr; } }
    .srev-col-left { padding: 14px 16px; background: #f8fafc; border-right: 1px solid #e2e8f0; }
    .srev-col-right { padding: 14px 16px; background: #ffffff; }
    .srev-raw-box { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; line-height: 1.55; color: #1e293b; white-space: pre-wrap; max-height: 360px; overflow-y: auto; }
    .srev-field-label { display: block; font-size: 11.5px; font-weight: 700; color: #475569; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; }
    .srev-input { width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #0f172a; background: #fff; }
    .srev-input.is-invalid { border-color: #ef4444; background: #fef2f2; }
    .srev-q-box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; margin-top: 10px; background: #fcfcfd; }
    .srev-q-box.has-error { border-color: #ef4444; background: #fffafa; }
    .srev-err-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .srev-xml-pre { background: #0f172a; color: #e2e8f0; padding: 12px; border-radius: 10px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; line-height: 1.5; white-space: pre-wrap; max-height: 420px; overflow-y: auto; }
</style>

<div class="qbank-page">
    <form method="POST" action="{{ route('admin.questions.import.commit', ['batchId' => $batchId]) }}" id="stagingCommitForm">
        @csrf

        {{-- 1. STICKY HEADER GỌN 1 HÀNG ĐIỀU KHIỂN CHÍNH --}}
        <div class="srev-sticky-bar">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                    <span style="font-size: 14.5px; font-weight: 800; color: #0f172a;" title="Batch #{{ $batchId }}">
                        {{ $staging['file_name'] }}
                    </span>
                    <span class="srev-stat-pill">Tổng: {{ $summary['total_questions'] }}</span>
                    <span class="srev-stat-pill srev-stat-valid">Hợp lệ: {{ $summary['valid_questions'] }}</span>
                    <span class="srev-stat-pill srev-stat-error">Lỗi: {{ $summary['error_questions'] }}</span>
                </div>

                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
                    <button type="button" class="srev-filter-btn active" onclick="filterStagingSets('all', this)">Tất cả</button>
                    <button type="button" class="srev-filter-btn" onclick="filterStagingSets('error', this)">Chỉ câu lỗi</button>
                    <select id="partFilterSelect" class="srev-input" style="width: auto; padding: 5px 9px; font-size: 12px; font-weight: 700;" onchange="filterStagingByPart(this.value)">
                        <option value="ALL">Tất cả Part</option>
                        @foreach ($partCodesInBatch as $pCode)
                            <option value="{{ $pCode }}">{{ $pCode }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.questions.import.xml', ['batchId' => $batchId]) }}" class="btn-admin-action" style="text-decoration: none; padding: 6px 12px; font-size: 12.5px; color: #0369a1; border-color: #bae6fd; background: #f0f9ff; font-weight: 700;">
                        Tải XML
                    </a>
                    <button type="submit" class="btn-admin-primary" style="background: #059669; border-color: #059669; font-size: 13px; padding: 7px 14px;">
                        Duyệt &amp; Lưu vào Ngân hàng đề
                    </button>
                </div>
            </div>

            {{-- Tùy chọn phụ gọn nhẹ (ẩn checkbox TTS nếu file không có Transcript Listening/Speaking) --}}
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; font-size: 12.5px;">
                <div>
                    @if ($hasTtsTranscripts)
                        <label style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; color: #0f172a; cursor: pointer;">
                            <input type="checkbox" name="auto_generate_tts" value="1" checked style="width: 15px; height: 15px; accent-color: #0284c7;">
                            <span>Tự động sinh Audio MP3 (TTS) cho {{ $summary['transcript_count'] }} cụm có Transcript</span>
                        </label>
                    @else
                        <span style="color: #64748b; font-weight: 600;">
                            <a href="{{ route('admin.questions.bulk-import') }}" style="color: #4338ca; text-decoration: none; font-weight: 700;">&larr; Chọn tệp khác</a>
                            &bull; Ảnh trích xuất: {{ $summary['image_count'] }}
                        </span>
                    @endif
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <select name="publish_status" class="srev-input" style="width: auto; padding: 4px 8px; font-size: 12px; font-weight: 700;">
                        <option value="ACTIVE" {{ ($staging['publish_status'] ?? 'ACTIVE') === 'ACTIVE' ? 'selected' : '' }}>Xuất bản (ACTIVE)</option>
                        <option value="DRAFT" {{ ($staging['publish_status'] ?? 'ACTIVE') === 'DRAFT' ? 'selected' : '' }}>Bản nháp (DRAFT)</option>
                    </select>

                    <select name="target_test_id" class="srev-input" style="width: auto; max-width: 240px; padding: 4px 8px; font-size: 12px; font-weight: 700;">
                        <option value="">-- Chỉ lưu Ngân hàng --</option>
                        @foreach ($availableTests as $testOption)
                            <option value="{{ $testOption->test_form_id }}" {{ (int) ($staging['target_test_id'] ?? 0) === (int) $testOption->test_form_id ? 'selected' : '' }}>
                                [{{ str_replace(['—', '–'], '-', $testOption->test_code) }}] {{ $testOption->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 2. KHUNG ĐỐI CHIẾU 2 CỘT (40% TRÁI / 60% PHẢI) --}}
        @foreach ($sets as $sIdx => $set)
            @php
                $setHasError = ! empty($set['errors']);
                $partCode = $set['part'] ?? 'PART5';
                $showStimulus = in_array($partCode, ['PART3', 'PART4', 'PART6', 'PART7'], true) || ! empty($set['stimulus']);
                $showTranscript = in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4', 'SPK_T3', 'SPK_T4'], true);
            @endphp
            <div class="srev-set-card {{ $setHasError ? 'has-error' : '' }}" data-part="{{ $partCode }}" data-has-error="{{ $setHasError ? '1' : '0' }}" id="srev-set-{{ $sIdx }}">
                <div class="srev-set-head">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="q-part-pill">{{ $partCode }}</span>
                        <strong style="font-size: 13.5px; color: #0f172a;">Cụm #{{ $sIdx + 1 }}: {{ $set['title'] ?? $partCode }}</strong>
                        <span style="font-size: 12px; color: #64748b;">({{ count($set['questions'] ?? []) }} câu)</span>
                        @foreach ($set['errors'] ?? [] as $errText)
                            <span class="srev-err-badge">⚠ {{ $errText }}</span>
                        @endforeach
                    </div>

                    <div style="display: inline-flex; gap: 4px; align-items: center;">
                        <button type="button" class="srev-filter-btn" style="padding: 3px 9px; font-size: 11.5px; color: #0f766e; border-color: #99f6e4; background: #f0fdfa; display: inline-flex; align-items: center; gap: 4px;" onclick="previewStagingQuestion({{ $sIdx }}, 0)" title="Xem trước cụm câu hỏi theo giao diện phòng thi">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <span>Xem trước</span>
                        </button>
                        <button type="button" class="srev-filter-btn active" style="padding: 3px 9px; font-size: 11.5px;" onclick="toggleSetViewMode({{ $sIdx }}, 'form', this)">Form</button>
                        <button type="button" class="srev-filter-btn" style="padding: 3px 9px; font-size: 11.5px;" onclick="toggleSetViewMode({{ $sIdx }}, 'xml', this)">Xem mã XML</button>
                    </div>
                </div>

                <div class="srev-split-grid">
                    {{-- CỘT TRÁI (40%): BẢN GỐC TỪ FILE WORD --}}
                    <div class="srev-col-left">
                        <div class="srev-field-label" style="margin-bottom: 6px;">Nội dung gốc trong File Word</div>
                        @if (! empty($set['raw_images']))
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                                @foreach ($set['raw_images'] as $rawImg)
                                    <img src="{{ asset(ltrim($rawImg, '/')) }}" alt="Ảnh gốc Word" style="max-height: 130px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; object-fit: contain;">
                                @endforeach
                            </div>
                        @endif
                        <div class="srev-raw-box">{{ $set['raw_source'] ?? '(Không có nội dung gốc)' }}</div>
                    </div>

                    {{-- CỘT PHẢI (60%): FORM TRỰC QUAN MẶC ĐỊNH --}}
                    <div class="srev-col-right">
                        <div id="srev-form-pane-{{ $sIdx }}">
                            <input type="hidden" name="sets[{{ $sIdx }}][title]" value="{{ $set['title'] ?? '' }}">
                            <input type="hidden" name="sets[{{ $sIdx }}][audio_url]" value="{{ $set['audio_url'] ?? '' }}">

                            {{-- 1. Chọn Part (& Ảnh nếu có) --}}
                            <div style="display: grid; grid-template-columns: {{ ! empty($set['media_url']) || $partCode === 'PART1' ? '1fr 1fr' : '1fr' }}; gap: 10px; margin-bottom: 10px;">
                                <div>
                                    <label class="srev-field-label">Chọn Part</label>
                                    <select name="sets[{{ $sIdx }}][part]" class="srev-input">
                                        @foreach ($sections as $sec)
                                            <option value="{{ $sec->code }}" {{ strtoupper($sec->code) === strtoupper($partCode) ? 'selected' : '' }}>
                                                {{ $sec->code }} — {{ $sec->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if (! empty($set['media_url']) || $partCode === 'PART1')
                                    <div>
                                        <label class="srev-field-label">Hình ảnh cụm</label>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <input type="text"
                                                   name="sets[{{ $sIdx }}][media_url]"
                                                   id="set-media-{{ $sIdx }}"
                                                   value="{{ $set['media_url'] ?? '' }}"
                                                   class="srev-input {{ ($partCode === 'PART1' && empty($set['media_url'])) ? 'is-invalid' : '' }}"
                                                   placeholder="Chưa có ảnh">
                                            @if (! empty($set['media_url']))
                                                <button type="button" class="btn-admin-action" style="padding: 5px 8px; font-size: 11.5px; color: #dc2626;" onclick="document.getElementById('set-media-{{ $sIdx }}').value = '';">Xóa</button>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="sets[{{ $sIdx }}][media_url]" value="{{ $set['media_url'] ?? '' }}">
                                @endif
                            </div>

                            {{-- 2. Đoạn văn / Ngữ cảnh (Stimulus) - chỉ hiện với Part 3, 4, 6, 7 --}}
                            @if ($showStimulus)
                                <div style="margin-bottom: 10px;">
                                    <label class="srev-field-label">Đoạn văn / Ngữ cảnh (Stimulus)</label>
                                    <textarea name="sets[{{ $sIdx }}][stimulus]" rows="3" class="srev-input">{{ $set['stimulus'] ?? '' }}</textarea>
                                </div>
                            @endif

                            {{-- Transcript cho Listening / Speaking --}}
                            @if ($showTranscript)
                                <div style="margin-bottom: 10px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <label class="srev-field-label" style="margin: 0;">Transcript (TTS)</label>
                                        <button type="button" class="btn-admin-action" style="padding: 2px 9px; font-size: 11.5px; color: #0369a1; border-color: #bae6fd; background: #f0f9ff; font-weight: 700;" onclick="previewStagingTts({{ $sIdx }}, '{{ $partCode }}')">
                                            🔊 Nghe thử TTS
                                        </button>
                                    </div>
                                    <textarea name="sets[{{ $sIdx }}][transcript]" id="set-transcript-{{ $sIdx }}" rows="2" class="srev-input {{ (in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4'], true) && empty($set['transcript'])) ? 'is-invalid' : '' }}">{{ $set['transcript'] ?? '' }}</textarea>
                                    <div id="tts-preview-wrap-{{ $sIdx }}" style="margin-top: 6px; display: none;">
                                        <audio id="tts-preview-audio-{{ $sIdx }}" controls style="height: 30px; width: 100%;"></audio>
                                    </div>
                                </div>
                            @endif

                            {{-- 3. Danh sách từng câu hỏi con --}}
                            @foreach ($set['questions'] ?? [] as $qIdx => $q)
                                @php
                                    $qErrors = $q['errors'] ?? [];
                                    $isProductive = in_array($partCode, ['SPK_T1', 'SPK_T2', 'SPK_T3', 'SPK_T4', 'SPK_T5', 'WRITING_T1', 'WRITING_T2', 'WRITING_T3'], true);
                                @endphp
                                <div class="srev-q-box {{ ! empty($qErrors) ? 'has-error' : '' }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: 6px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <strong style="font-size: 13px; color: #4338ca;">Câu #{{ $q['seq'] ?? ($qIdx + 1) }}</strong>
                                            @foreach ($qErrors as $qErr)
                                                <span class="srev-err-badge">⚠ {{ $qErr }}</span>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn-admin-action" style="padding: 3px 8px; font-size: 11.5px; color: #0f766e; border-color: #99f6e4; background: #f0fdfa; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" onclick="previewStagingQuestion({{ $sIdx }}, {{ $qIdx }})" title="Xem trước câu hỏi này theo giao diện phòng thi">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            <span>Xem trước</span>
                                        </button>
                                    </div>

                                    <input type="hidden" name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][seq]" value="{{ $q['seq'] ?? ($qIdx + 1) }}">
                                    <input type="hidden" name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][difficulty]" value="{{ $q['difficulty'] ?? 'UNSET' }}">
                                    <input type="hidden" name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][media_url]" value="{{ $q['media_url'] ?? '' }}">

                                    <div style="margin-bottom: 8px;">
                                        <label class="srev-field-label">Nội dung câu hỏi</label>
                                        <textarea name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][content]" rows="2" class="srev-input {{ (empty(trim((string) ($q['content'] ?? ''))) && ! ($partCode === 'PART6' && ! empty(trim((string) ($set['stimulus'] ?? ''))))) ? 'is-invalid' : '' }}">{{ $q['content'] ?? '' }}</textarea>
                                    </div>

                                    @if (! $isProductive)
                                        @php
                                            $letters = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
                                            $opts = $q['options'] ?? [];
                                        @endphp
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 8px;">
                                            @foreach ($letters as $letter)
                                                @php $isCorrect = strtoupper((string) ($q['correct'] ?? '')) === $letter; @endphp
                                                <div style="display: flex; align-items: center; gap: 6px; padding: 4px 7px; border-radius: 7px; border: 1px solid {{ $isCorrect ? '#10b981' : '#e2e8f0' }}; background: {{ $isCorrect ? '#ecfdf5' : '#fff' }};">
                                                    <input type="radio"
                                                           name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][correct]"
                                                           value="{{ $letter }}"
                                                           {{ $isCorrect ? 'checked' : '' }}
                                                           title="Đáp án đúng">
                                                    <strong style="font-size: 12px; color: #334155;">({{ $letter }})</strong>
                                                    <input type="text"
                                                           name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][options][{{ $letter }}]"
                                                           value="{{ $opts[$letter] ?? '' }}"
                                                           class="srev-input"
                                                           style="padding: 4px 6px; font-size: 12.5px;">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div>
                                        <label class="srev-field-label">Giải thích</label>
                                        <textarea name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][explanation]" rows="2" class="srev-input">{{ $q['explanation'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tab xem mã XML gọn nhẹ --}}
                        <div id="srev-xml-pane-{{ $sIdx }}" style="display: none;">
                            <div class="srev-field-label" style="margin-bottom: 6px;">Mã XML chuẩn StudyPrep của cụm #{{ $sIdx + 1 }}</div>
                            <pre class="srev-xml-pre">{{ app(\App\Services\DocxToStandardXmlService::class)->toStandardXml([$set]) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </form>
</div>

@include('admin.questions.partials.question-preview-modal')

@push('scripts')
<script>
    function filterStagingSets(mode, btn) {
        document.querySelectorAll('.srev-sticky-bar .srev-filter-btn').forEach(function (b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        document.getElementById('partFilterSelect').value = 'ALL';

        document.querySelectorAll('.srev-set-card').forEach(function (card) {
            if (mode === 'error') {
                card.style.display = card.getAttribute('data-has-error') === '1' ? 'block' : 'none';
            } else {
                card.style.display = 'block';
            }
        });
    }

    function filterStagingByPart(partCode) {
        document.querySelectorAll('.srev-set-card').forEach(function (card) {
            if (partCode === 'ALL' || card.getAttribute('data-part') === partCode) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function toggleSetViewMode(sIdx, mode, btn) {
        var formPane = document.getElementById('srev-form-pane-' + sIdx);
        var xmlPane = document.getElementById('srev-xml-pane-' + sIdx);
        if (!formPane || !xmlPane) return;

        var parent = btn.parentElement;
        if (parent) {
            parent.querySelectorAll('.srev-filter-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
        }

        formPane.style.display = mode === 'form' ? 'block' : 'none';
        xmlPane.style.display = mode === 'xml' ? 'block' : 'none';
    }

    function normalizeStagingAssetUrl(rawUrl) {
        var val = String(rawUrl || '').trim();
        if (!val) return '';
        if (/^https?:\/\//i.test(val) || val.startsWith('/')) return val;
        var base = '{{ rtrim(asset(''), '/') }}';
        return base + '/' + val.replace(/^\/+/, '');
    }

    function getStagingSkillMeta(partCode) {
        var code = String(partCode || 'PART5').toUpperCase();
        var spkTimings = {
            SPK_T1: { prep: '45 giây', response: '45 giây', hint: 'Đọc to, rõ ràng, chú ý phát âm và ngữ điệu.' },
            SPK_T2: { prep: '45 giây', response: '30 giây', hint: 'Mô tả chi tiết bức tranh theo bố cục tổng quát đến chi tiết.' },
            SPK_T3: { prep: '3 giây', response: '15–30 giây', hint: 'Trả lời trực tiếp câu hỏi theo ngữ cảnh giao tiếp.' },
            SPK_T4: { prep: '45 giây', response: '15–30 giây', hint: 'Đọc kỹ bảng thông tin/lịch trình và trả lời chính xác.' },
            SPK_T5: { prep: '30 giây', response: '60 giây', hint: 'Nêu rõ quan điểm kèm 2 lý do và ví dụ minh họa.' }
        };
        var wrtTimings = {
            WRITING_T1: { time: '8 phút (5 câu)', min_words: 12, hint: 'Viết 1 câu hoàn chỉnh sử dụng đúng 2 từ khóa cho sẵn.' },
            WRITING_T2: { time: '10 phút / email', min_words: 80, hint: 'Phản hồi email lịch sự, đáp ứng đủ các yêu cầu đề bài.' },
            WRITING_T3: { time: '30 phút', min_words: 300, hint: 'Viết bài luận nêu quan điểm rõ ràng kèm lập luận và dẫn chứng.' }
        };
        if (code.startsWith('SPK_')) {
            return { skill_key: 'speaking', skill_label: 'Speaking', ets_timing: spkTimings[code] || spkTimings.SPK_T2 };
        }
        if (code.startsWith('WRITING_')) {
            return { skill_key: 'writing', skill_label: 'Writing', ets_timing: wrtTimings[code] || wrtTimings.WRITING_T2 };
        }
        if (['PART1', 'PART2', 'PART3', 'PART4'].indexOf(code) !== -1) {
            return { skill_key: 'listening', skill_label: 'Listening', ets_timing: null };
        }
        return { skill_key: 'reading', skill_label: 'Reading', ets_timing: null };
    }

    function previewStagingQuestion(sIdx, qIdx) {
        if (typeof window.openPreviewModal !== 'function') return;

        var formPane = document.getElementById('srev-form-pane-' + sIdx);
        if (!formPane) return;

        var partSelect = formPane.querySelector('select[name="sets[' + sIdx + '][part]"]');
        var partCode = partSelect ? String(partSelect.value || 'PART5').toUpperCase() : 'PART5';
        var setMediaInput = formPane.querySelector('input[name="sets[' + sIdx + '][media_url]"]');
        var setAudioInput = formPane.querySelector('input[name="sets[' + sIdx + '][audio_url]"]');
        var stimInput = formPane.querySelector('textarea[name="sets[' + sIdx + '][stimulus]"]');
        var transInput = formPane.querySelector('textarea[name="sets[' + sIdx + '][transcript]"]');

        var setMedia = setMediaInput ? setMediaInput.value : '';
        var setAudio = setAudioInput ? setAudioInput.value : '';
        var stimulusText = stimInput ? stimInput.value : '';
        var transcriptText = transInput ? transInput.value : '';

        var qBoxes = formPane.querySelectorAll('.srev-q-box');
        var groupQuestions = [];
        var selectedQ = null;

        qBoxes.forEach(function (box, idx) {
            var seqEl = box.querySelector('input[name="sets[' + sIdx + '][questions][' + idx + '][seq]"]');
            var diffEl = box.querySelector('input[name="sets[' + sIdx + '][questions][' + idx + '][difficulty]"]');
            var qMediaEl = box.querySelector('input[name="sets[' + sIdx + '][questions][' + idx + '][media_url]"]');
            var contentEl = box.querySelector('textarea[name="sets[' + sIdx + '][questions][' + idx + '][content]"]');
            var expEl = box.querySelector('textarea[name="sets[' + sIdx + '][questions][' + idx + '][explanation]"]');
            var checkedOpt = box.querySelector('input[name="sets[' + sIdx + '][questions][' + idx + '][correct]"]:checked');
            var correctLetter = checkedOpt ? String(checkedOpt.value).toUpperCase() : '';

            var opts = [];
            ['A', 'B', 'C', 'D'].forEach(function (letter) {
                var optInput = box.querySelector('input[name="sets[' + sIdx + '][questions][' + idx + '][options][' + letter + ']"]');
                if (optInput && (optInput.value.trim() !== '' || ['PART1', 'PART2', 'PART3', 'PART4', 'PART5', 'PART6', 'PART7'].indexOf(partCode) !== -1)) {
                    if (partCode === 'PART2' && letter === 'D' && optInput.value.trim() === '') return;
                    opts.push({
                        label: letter,
                        content: optInput.value,
                        is_correct: correctLetter === letter
                    });
                }
            });

            var qObj = {
                question_id: (sIdx + 1) * 1000 + idx + 1,
                question_code: 'Câu #' + (seqEl ? seqEl.value : (idx + 1)),
                seq: seqEl ? seqEl.value : (idx + 1),
                difficulty: diffEl ? diffEl.value : 'UNSET',
                media_url: qMediaEl ? qMediaEl.value : '',
                content: contentEl ? contentEl.value : '',
                explanation: expEl ? expEl.value : '',
                options: opts
            };
            groupQuestions.push(qObj);
            if (idx === Number(qIdx)) {
                selectedQ = qObj;
            }
        });

        if (!selectedQ) {
            selectedQ = groupQuestions[0] || {
                question_id: (sIdx + 1) * 1000 + 1,
                question_code: 'Câu #1',
                difficulty: 'UNSET',
                media_url: '',
                content: '',
                explanation: '',
                options: []
            };
        }

        var meta = getStagingSkillMeta(partCode);
        var effectiveMedia = normalizeStagingAssetUrl(selectedQ.media_url || setMedia);
        var effectiveAudio = normalizeStagingAssetUrl(setAudio);

        window.openPreviewModal({
            question_id: selectedQ.question_id,
            question_code: selectedQ.question_code,
            part_code: partCode,
            part_full_label: partCode + ' — Xem trước từ Form đối chiếu',
            skill_key: meta.skill_key,
            skill_label: meta.skill_label,
            ets_timing: meta.ets_timing,
            source_tag: 'Bản nháp đối chiếu (Chưa lưu DB)',
            status: 'DRAFT',
            status_label: 'Đang đối chiếu',
            difficulty: selectedQ.difficulty || 'UNSET',
            content: selectedQ.content || '',
            explanation: selectedQ.explanation || '',
            options: selectedQ.options || [],
            group_count: groupQuestions.length,
            question_set: {
                question_set_id: null,
                media_url: effectiveMedia,
                resolved_media_url: effectiveMedia,
                resolved_audio_url: effectiveAudio,
                stimulus_text: stimulusText,
                transcript: transcriptText,
                group_count: groupQuestions.length,
                questions: groupQuestions
            }
        });
    }

    async function previewStagingTts(sIdx, partCode) {
        var textEl = document.getElementById('set-transcript-' + sIdx);
        var wrapEl = document.getElementById('tts-preview-wrap-' + sIdx);
        var audioEl = document.getElementById('tts-preview-audio-' + sIdx);
        if (!textEl || !textEl.value.trim()) {
            alert('Vui lòng nhập Transcript trước khi nghe thử TTS.');
            return;
        }

        var partNum = parseInt(String(partCode).replace(/[^0-9]/g, ''), 10) || 1;
        if (partNum < 1 || partNum > 4) partNum = 1;

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        try {
            var resp = await fetch('{{ url('/api/admin/questions/generate-tts') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    text: textEl.value.trim(),
                    part_number: partNum
                })
            });
            var data = await resp.json();
            if (resp.ok && data && data.audio_url) {
                wrapEl.style.display = 'block';
                audioEl.src = data.audio_url;
                audioEl.play();
            } else {
                alert((data && data.message) ? data.message : 'Không thể sinh thử audio TTS.');
            }
        } catch (err) {
            alert('Lỗi kết nối khi gọi dịch vụ TTS.');
        }
    }
</script>
@endpush
@endsection

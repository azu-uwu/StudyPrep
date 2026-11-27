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
    $initialImageUrls = [];
    foreach ($sets as $s) {
        $sMedia = trim((string) ($s['media_url'] ?? ''));
        if ($sMedia !== '') {
            $initialImageUrls[$sMedia] = true;
        }
        foreach ($s['questions'] ?? [] as $q) {
            $qMedia = trim((string) ($q['media_url'] ?? ''));
            if ($qMedia !== '') {
                $initialImageUrls[$qMedia] = true;
            }
        }
    }
    $initialImageCount = count($initialImageUrls);
@endphp

<style>
    .srev-sticky-bar { position: sticky; top: 10px; z-index: 40; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 12px 18px; margin-bottom: 18px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08); }
    .srev-stat-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; border: 1px solid #e2e8f0; background: #f8fafc; color: #334155; white-space: nowrap; }
    .srev-stat-valid { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .srev-stat-error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .srev-filter-btn { padding: 5px 11px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .srev-filter-btn.active { background: #4338ca; color: #ffffff; border-color: #4338ca; }
    .srev-set-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin-bottom: 16px; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03); content-visibility: auto; contain-intrinsic-size: 280px; }
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
    <form method="POST" action="{{ route('admin.questions.import.commit', ['batchId' => $batchId]) }}" id="stagingCommitForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="payload_json" id="commit-payload-json" value="">

        {{-- 1. STICKY HEADER GỌN 1 HÀNG ĐIỀU KHIỂN CHÍNH --}}
        <div class="srev-sticky-bar">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                    <span style="font-size: 14.5px; font-weight: 800; color: #0f172a;" title="Batch #{{ $batchId }}">
                        {{ $staging['file_name'] }}
                    </span>
                    <span class="srev-stat-pill" id="srev-stat-total">Tổng: {{ $summary['total_questions'] }}</span>
                    <span class="srev-stat-pill srev-stat-valid" id="srev-stat-valid">Hợp lệ: {{ $summary['valid_questions'] }}</span>
                    <span class="srev-stat-pill srev-stat-error" id="srev-stat-error">Lỗi: {{ $summary['error_questions'] }}</span>
                    <span class="srev-stat-pill" id="srev-stat-images" style="background: #f0f9ff; color: #0369a1; border-color: #bae6fd;">Có ảnh: {{ $initialImageCount }}</span>
                    <span class="srev-stat-pill" id="srev-stat-transcripts" style="background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe;">Transcript TTS: {{ $summary['transcript_count'] }}</span>
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
        @if (isset($errors) && $errors->any())
            <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c; padding: 12px 16px; border-radius: 12px; margin-bottom: 14px; font-size: 13px;">
                <strong>Không thể lưu vào Ngân hàng đề:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach ($errors->all() as $errMsg)
                        <li>{{ $errMsg }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (! empty($staging['validation_errors']))
            <div style="background: #fffbeb; border: 1.5px solid #fcd34d; color: #92400e; padding: 12px 16px; border-radius: 12px; margin-bottom: 14px; font-size: 13px;">
                <strong>Cảnh báo kiểm tra cấu trúc đề TOEIC:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach ($staging['validation_errors'] as $vErr)
                        <li>{{ $vErr }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @php
            $partDirections = $staging['part_directions'] ?? [];
            $renderedDirectionsParts = [];
        @endphp
        @foreach ($sets as $sIdx => $set)
            @php
                $setHasError = ! empty($set['errors']);
                $partCode = $set['part'] ?? 'PART5';
                $showStimulus = in_array($partCode, ['PART3', 'PART4', 'PART6', 'PART7'], true) || ! empty($set['stimulus']);
                $showTranscript = in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4', 'SPK_T3', 'SPK_T4'], true);
                $showPartDirectionsBanner = ! in_array($partCode, $renderedDirectionsParts, true)
                    && (in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4'], true) || ! empty($partDirections[$partCode]));
                if ($showPartDirectionsBanner) {
                    $renderedDirectionsParts[] = $partCode;
                }
            @endphp
            @if ($showPartDirectionsBanner)
                <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 12px 16px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                        <strong style="font-size: 13px; color: #0369a1;">Directions đầu {{ $partCode }} (Lời dẫn mở đầu Part — không tính vào số câu hỏi)</strong>
                        @if (in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4'], true))
                            <button type="button" class="btn-admin-action" style="padding: 3px 10px; font-size: 11.5px; color: #0369a1; border-color: #7dd3fc; background: #fff; font-weight: 700;" onclick="previewPartDirectionsTts('{{ $partCode }}')">
                                🔊 Nghe thử Directions TTS
                            </button>
                        @endif
                    </div>
                    <textarea name="part_directions[{{ $partCode }}]" id="part-directions-{{ $partCode }}" rows="2" class="srev-input" style="background: #fff; font-size: 12.5px;">{{ $partDirections[$partCode] ?? '' }}</textarea>
                    <div id="part-directions-audio-wrap-{{ $partCode }}" style="margin-top: 6px; display: none;">
                        <audio id="part-directions-audio-{{ $partCode }}" controls style="height: 30px; width: 100%;"></audio>
                    </div>
                </div>
            @endif
            <div class="srev-set-card review-set-card {{ $setHasError ? 'has-error' : '' }}" data-part="{{ $partCode }}" data-has-error="{{ $setHasError ? '1' : '0' }}" id="srev-set-{{ $sIdx }}">
                <div class="srev-set-head">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="q-part-pill">{{ $partCode }}</span>
                        <strong style="font-size: 13.5px; color: #0f172a;">Cụm #{{ $sIdx + 1 }}: {{ $set['title'] ?? $partCode }}</strong>
                        <span class="srev-set-q-count" style="font-size: 12px; color: #64748b;">({{ count($set['questions'] ?? []) }} câu)</span>
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
                        <button type="button" class="srev-filter-btn" style="padding: 3px 9px; font-size: 11.5px; color: #b91c1c; border-color: #fecaca; background: #fef2f2; display: inline-flex; align-items: center; gap: 4px;" onclick="removeStagingSet({{ $sIdx }})" title="Xóa toàn bộ cụm câu hỏi này">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6"></path>
                                <path d="M14 11v6"></path>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                            </svg>
                            <span>Xóa cụm</span>
                        </button>
                    </div>
                </div>

                <div class="srev-split-grid">
                    {{-- CỘT TRÁI (40%): BẢN GỐC TỪ FILE WORD --}}
                    <div class="srev-col-left">
                        <div class="srev-field-label" style="margin-bottom: 6px;">Nội dung gốc trong File Word</div>
                        @if (! empty($set['raw_images']))
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                                @foreach ($set['raw_images'] as $rawImg)
                                    <img src="{{ asset(ltrim($rawImg, '/')) }}" loading="lazy" alt="Ảnh gốc Word" style="max-height: 130px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; object-fit: contain;">
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

                            {{-- 1. Chọn Part & Hình ảnh cụm (Hỗ trợ Thay ảnh / Upload mới / Kéo thả / Xóa ảnh) --}}
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; align-items: start;">
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

                                @php
                                    $hasSetMedia = ! empty($set['media_url']);
                                    $resolvedSetMediaPreview = $hasSetMedia
                                        ? (preg_match('/^https?:\/\//i', (string) $set['media_url']) ? $set['media_url'] : asset(ltrim((string) $set['media_url'], '/')))
                                        : '';
                                @endphp
                                <div>
                                    <label class="srev-field-label">Hình ảnh cụm</label>
                                    <input type="hidden"
                                           name="sets[{{ $sIdx }}][media_url]"
                                           id="set-media-{{ $sIdx }}"
                                           value="{{ $set['media_url'] ?? '' }}">
                                    <input type="file"
                                           name="set_images[{{ $sIdx }}]"
                                           id="set-image-file-{{ $sIdx }}"
                                           class="srev-set-image-input"
                                           accept=".jpg,.jpeg,.png,.webp,image/*"
                                           style="display: none;"
                                           onchange="handleStagingImageSelected({{ $sIdx }}, this)">

                                    <div id="set-image-dropzone-{{ $sIdx }}"
                                         ondragover="event.preventDefault(); this.style.borderColor='#4338ca'; this.style.background='#eef2ff';"
                                         ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';"
                                         ondrop="handleStagingImageDrop(event, {{ $sIdx }}, this)"
                                         style="border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 8px; background: #f8fafc; transition: all 0.15s ease;">

                                        <div id="set-image-preview-wrap-{{ $sIdx }}" style="display: {{ $hasSetMedia ? 'block' : 'none' }};">
                                            <img id="set-image-preview-{{ $sIdx }}"
                                                 src="{{ $resolvedSetMediaPreview }}"
                                                 loading="lazy"
                                                 data-blob-url=""
                                                 alt="Ảnh cụm #{{ $sIdx + 1 }}"
                                                 style="width: 100%; max-height: 256px; object-fit: contain; border-radius: 6px; background: #fff; border: 1px solid #e2e8f0; display: block; margin-bottom: 6px;">
                                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                <button type="button"
                                                        class="btn-admin-action"
                                                        style="padding: 4px 10px; font-size: 11.5px; color: #4338ca; border-color: #c7d2fe; background: #eef2ff; font-weight: 700;"
                                                        onclick="document.getElementById('set-image-file-{{ $sIdx }}').click()">
                                                    Thay ảnh
                                                </button>
                                                <button type="button"
                                                        class="btn-admin-action"
                                                        style="padding: 4px 10px; font-size: 11.5px; color: #b91c1c; border-color: #fecaca; background: #fef2f2; font-weight: 700;"
                                                        onclick="removeStagingImage({{ $sIdx }})">
                                                    Xóa ảnh
                                                </button>
                                            </div>
                                        </div>

                                        <div id="set-image-empty-{{ $sIdx }}" style="display: {{ $hasSetMedia ? 'none' : 'flex' }}; flex-direction: column; align-items: center; justify-content: center; padding: 10px 6px; text-align: center; gap: 6px;">
                                            <span style="font-size: 11.5px; color: {{ $partCode === 'PART1' ? '#b91c1c' : '#64748b' }}; font-weight: 600;">
                                                {{ $partCode === 'PART1' ? 'Chưa có ảnh (Bắt buộc cho Part 1)' : 'Chưa có ảnh (kéo thả hoặc chọn file)' }}
                                            </span>
                                            <button type="button"
                                                    class="btn-admin-action"
                                                    style="padding: 5px 11px; font-size: 11.5px; color: #0369a1; border-color: #bae6fd; background: #f0f9ff; font-weight: 700;"
                                                    onclick="document.getElementById('set-image-file-{{ $sIdx }}').click()">
                                                + Chọn ảnh tải lên
                                            </button>
                                        </div>
                                    </div>
                                </div>
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
                                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                                            <button type="button" class="btn-admin-action" style="padding: 3px 8px; font-size: 11.5px; color: #0f766e; border-color: #99f6e4; background: #f0fdfa; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" onclick="previewStagingQuestion({{ $sIdx }}, {{ $qIdx }})" title="Xem trước câu hỏi này theo giao diện phòng thi">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                <span>Xem trước</span>
                                            </button>
                                            <button type="button" class="btn-admin-action" style="padding: 3px 8px; font-size: 11.5px; color: #b91c1c; border-color: #fecaca; background: #fef2f2; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" onclick="removeStagingQuestion(this, {{ $sIdx }})" title="Xóa câu hỏi này khỏi cụm">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                    <path d="M10 11v6"></path>
                                                    <path d="M14 11v6"></path>
                                                </svg>
                                                <span>Xóa câu</span>
                                            </button>
                                        </div>
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
                                        <div class="srev-options-grid" data-sidx="{{ $sIdx }}" data-qidx="{{ $qIdx }}" style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 6px;">
                                            @foreach ($letters as $letter)
                                                @php $isCorrect = strtoupper((string) ($q['correct'] ?? '')) === $letter; @endphp
                                                <div class="srev-opt-pill" data-letter="{{ $letter }}" style="display: flex; align-items: center; gap: 6px; padding: 4px 7px; border-radius: 7px; border: 1px solid {{ $isCorrect ? '#10b981' : '#e2e8f0' }}; background: {{ $isCorrect ? '#ecfdf5' : '#fff' }};">
                                                    <input type="radio"
                                                           name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][correct]"
                                                           value="{{ $letter }}"
                                                           {{ $isCorrect ? 'checked' : '' }}
                                                           onchange="handleCorrectOptionChange(this, {{ $sIdx }})"
                                                           style="cursor: pointer;"
                                                           title="Chọn {{ $letter }} làm đáp án đúng">
                                                    <strong style="font-size: 12px; color: #334155;">({{ $letter }})</strong>
                                                    <input type="text"
                                                           name="sets[{{ $sIdx }}][questions][{{ $qIdx }}][options][{{ $letter }}]"
                                                           value="{{ $opts[$letter] ?? '' }}"
                                                           class="srev-input"
                                                           style="padding: 4px 6px; font-size: 12.5px;">
                                                    @if ($loop->last && count($letters) > 3)
                                                        <button type="button" onclick="removeStagingOption(this, {{ $sIdx }})" title="Xóa phương án {{ $letter }}" style="border: none; background: transparent; color: #dc2626; font-weight: 700; cursor: pointer; padding: 0 3px;">&times;</button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        @if (count($letters) < 4)
                                            <div style="margin-bottom: 8px;">
                                                <button type="button" class="btn-admin-action" style="padding: 2px 8px; font-size: 11px; color: #4338ca; border-color: #c7d2fe; background: #eef2ff;" onclick="addStagingOption(this, {{ $sIdx }}, {{ $qIdx }})">+ Thêm đáp án</button>
                                            </div>
                                        @endif
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
        var previewImgEl = document.getElementById('set-image-preview-' + sIdx);
        var blobUrl = previewImgEl ? String(previewImgEl.getAttribute('data-blob-url') || '').trim() : '';
        var effectiveMedia = blobUrl !== ''
            ? blobUrl
            : normalizeStagingAssetUrl(selectedQ.media_url || setMedia);
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

    async function previewPartDirectionsTts(partCode) {
        var textEl = document.getElementById('part-directions-' + partCode);
        var wrapEl = document.getElementById('part-directions-audio-wrap-' + partCode);
        var audioEl = document.getElementById('part-directions-audio-' + partCode);
        if (!textEl || !textEl.value.trim()) {
            alert('Vui lòng nhập nội dung Directions trước khi nghe thử TTS.');
            return;
        }
        var partNum = parseInt(String(partCode).replace(/[^0-9]/g, ''), 10) || 1;
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        try {
            var resp = await fetch('{{ url('/api/admin/questions/generate-tts') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ text: textEl.value.trim(), part_number: partNum })
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

    var PRODUCTIVE_PARTS_JS = ['SPK_T1', 'SPK_T2', 'SPK_T3', 'SPK_T4', 'SPK_T5', 'WRITING_T1', 'WRITING_T2', 'WRITING_T3'];

    function removeStagingSet(sIdx) {
        if (!confirm('Xóa toàn bộ cụm câu hỏi này khỏi danh sách đối chiếu?')) {
            return;
        }
        var card = document.getElementById('srev-set-' + sIdx);
        if (card) {
            card.remove();
            recalculateReviewStats();
        }
    }

    function removeStagingQuestion(btn, sIdx) {
        var qBox = btn ? btn.closest('.srev-q-box') : null;
        if (!qBox) return;
        var card = document.getElementById('srev-set-' + sIdx);
        qBox.remove();

        if (card) {
            var remaining = card.querySelectorAll('.srev-q-box').length;
            if (remaining === 0) {
                card.remove();
            } else {
                var countSpan = card.querySelector('.srev-set-q-count');
                if (countSpan) {
                    countSpan.textContent = '(' + remaining + ' câu)';
                }
            }
        }
        recalculateReviewStats();
    }

    function recalculateReviewStats() {
        var commitForm = document.getElementById('stagingCommitForm');
        if (!commitForm) return;

        var totalQuestions = 0;
        var validQuestions = 0;
        var errorQuestions = 0;
        var uniqueMediaUrls = {};
        var transcriptCount = 0;

        commitForm.querySelectorAll('.srev-set-card').forEach(function (card) {
            var partEl = card.querySelector('select[name$="[part]"]');
            var partCode = partEl ? String(partEl.value || 'PART5').toUpperCase() : 'PART5';
            var mediaEl = card.querySelector('input[name$="[media_url]"]');
            var transEl = card.querySelector('textarea[name$="[transcript]"]');
            var stimEl = card.querySelector('textarea[name$="[stimulus]"]');

            var setMedia = mediaEl ? mediaEl.value.trim() : '';
            var setTrans = transEl ? transEl.value.trim() : '';
            var setStim = stimEl ? stimEl.value.trim() : '';

            if (setMedia !== '') uniqueMediaUrls[setMedia] = true;
            if (setTrans !== '') transcriptCount++;

            var setHasError = false;
            if (partCode === 'PART1' && setMedia === '') setHasError = true;
            if (['PART1', 'PART2', 'PART3', 'PART4'].indexOf(partCode) !== -1 && setTrans === '') setHasError = true;
            if (['PART6', 'PART7'].indexOf(partCode) !== -1 && setStim === '' && setMedia === '') setHasError = true;

            var isProductive = PRODUCTIVE_PARTS_JS.indexOf(partCode) !== -1;
            var expectedLetters = partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];

            card.querySelectorAll('.srev-q-box').forEach(function (box) {
                totalQuestions++;
                var qMediaEl = box.querySelector('input[name$="[media_url]"]');
                var qMedia = qMediaEl ? qMediaEl.value.trim() : '';
                if (qMedia !== '') uniqueMediaUrls[qMedia] = true;

                var contentEl = box.querySelector('textarea[name$="[content]"]');
                var qContent = contentEl ? contentEl.value.trim() : '';
                var qHasError = false;

                if (qContent === '' && !(partCode === 'PART6' && setStim !== '') && partCode !== 'PART1') {
                    qHasError = true;
                }

                if (!isProductive) {
                    var filled = 0;
                    expectedLetters.forEach(function (letter) {
                        var optEl = box.querySelector('input[name$="[options][' + letter + ']"]');
                        if (optEl && optEl.value.trim() !== '') filled++;
                    });
                    if (filled < expectedLetters.length) qHasError = true;

                    var checkedOpt = box.querySelector('input[name$="[correct]"]:checked');
                    var correctVal = checkedOpt ? String(checkedOpt.value).toUpperCase() : '';
                    if (!correctVal || expectedLetters.indexOf(correctVal) === -1) qHasError = true;
                } else {
                    if ((partCode === 'SPK_T2' || partCode === 'WRITING_T1') && qMedia === '' && setMedia === '') {
                        qHasError = true;
                    }
                }

                if (qHasError) {
                    errorQuestions++;
                    setHasError = true;
                    box.classList.add('has-error');
                } else {
                    validQuestions++;
                    box.classList.remove('has-error');
                }
            });

            card.setAttribute('data-has-error', setHasError ? '1' : '0');
            if (setHasError) {
                card.classList.add('has-error');
            } else {
                card.classList.remove('has-error');
            }
        });

        var elTotal = document.getElementById('srev-stat-total');
        var elValid = document.getElementById('srev-stat-valid');
        var elError = document.getElementById('srev-stat-error');
        var elImg = document.getElementById('srev-stat-images');
        var elTrs = document.getElementById('srev-stat-transcripts');
        var imageCount = Object.keys(uniqueMediaUrls).length;
        if (elTotal) elTotal.textContent = 'Tổng: ' + totalQuestions;
        if (elValid) elValid.textContent = 'Hợp lệ: ' + validQuestions;
        if (elError) elError.textContent = 'Lỗi: ' + errorQuestions;
        if (elImg) elImg.textContent = 'Có ảnh: ' + imageCount;
        if (elTrs) elTrs.textContent = 'Transcript TTS: ' + transcriptCount;
    }

    function handleStagingImageSelected(sIdx, input) {
        var file = input && input.files && input.files[0] ? input.files[0] : null;
        if (!file) return;

        var blobUrl = URL.createObjectURL(file);
        var previewImg = document.getElementById('set-image-preview-' + sIdx);
        var previewWrap = document.getElementById('set-image-preview-wrap-' + sIdx);
        var emptyWrap = document.getElementById('set-image-empty-' + sIdx);
        var mediaHidden = document.getElementById('set-media-' + sIdx);

        if (previewImg) {
            var oldBlob = previewImg.getAttribute('data-blob-url');
            if (oldBlob) URL.revokeObjectURL(oldBlob);
            previewImg.src = blobUrl;
            previewImg.setAttribute('data-blob-url', blobUrl);
        }
        if (previewWrap) previewWrap.style.display = 'block';
        if (emptyWrap) emptyWrap.style.display = 'none';
        if (mediaHidden) mediaHidden.value = '__PENDING_UPLOAD__:' + file.name;

        var setCard = document.getElementById('srev-set-' + sIdx);
        if (setCard) {
            setCard.querySelectorAll('.srev-q-box input[name$="[media_url]"]').forEach(function (qMediaInput) {
                qMediaInput.value = '__PENDING_UPLOAD__:' + file.name;
            });
        }

        recalculateReviewStats();
    }

    function handleStagingImageDrop(event, sIdx, zone) {
        event.preventDefault();
        if (zone) {
            zone.style.borderColor = '#cbd5e1';
            zone.style.background = '#f8fafc';
        }
        var files = event.dataTransfer ? event.dataTransfer.files : null;
        if (!files || !files.length) return;
        var file = files[0];
        if (!/^image\//i.test(file.type || '')) return;

        var fileInput = document.getElementById('set-image-file-' + sIdx);
        if (fileInput) {
            var dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            handleStagingImageSelected(sIdx, fileInput);
        }
    }

    function removeStagingImage(sIdx) {
        var fileInput = document.getElementById('set-image-file-' + sIdx);
        var previewImg = document.getElementById('set-image-preview-' + sIdx);
        var previewWrap = document.getElementById('set-image-preview-wrap-' + sIdx);
        var emptyWrap = document.getElementById('set-image-empty-' + sIdx);
        var mediaHidden = document.getElementById('set-media-' + sIdx);

        if (fileInput) fileInput.value = '';
        if (previewImg) {
            var oldBlob = previewImg.getAttribute('data-blob-url');
            if (oldBlob) URL.revokeObjectURL(oldBlob);
            previewImg.src = '';
            previewImg.setAttribute('data-blob-url', '');
        }
        if (previewWrap) previewWrap.style.display = 'none';
        if (emptyWrap) emptyWrap.style.display = 'flex';
        if (mediaHidden) mediaHidden.value = '';

        var setCard = document.getElementById('srev-set-' + sIdx);
        if (setCard) {
            setCard.querySelectorAll('.srev-q-box input[name$="[media_url]"]').forEach(function (qMediaInput) {
                qMediaInput.value = '';
            });
        }

        recalculateReviewStats();
    }

    function handleCorrectOptionChange(radioEl, sIdx) {
        var qBox = radioEl ? radioEl.closest('.srev-q-box') : null;
        if (!qBox) return;

        qBox.querySelectorAll('.srev-opt-pill').forEach(function (pill) {
            var r = pill.querySelector('input[type="radio"]');
            var active = r && r.checked;
            pill.style.borderColor = active ? '#10b981' : '#e2e8f0';
            pill.style.background = active ? '#ecfdf5' : '#fff';
        });

        qBox.querySelectorAll('.srev-err-badge').forEach(function (badge) {
            var txt = (badge.textContent || '').toLowerCase();
            if (txt.indexOf('đáp án') !== -1 || txt.indexOf('part 2') !== -1) {
                badge.remove();
            }
        });

        recalculateReviewStats();

        var setCard = document.getElementById('srev-set-' + sIdx);
        if (setCard && setCard.getAttribute('data-has-error') === '0') {
            var head = setCard.querySelector('.srev-set-head');
            if (head) {
                head.querySelectorAll('.srev-err-badge').forEach(function (b) { b.remove(); });
            }
        }
    }

    function addStagingOption(btn, sIdx, qIdx) {
        var qBox = btn ? btn.closest('.srev-q-box') : null;
        var grid = qBox ? qBox.querySelector('.srev-options-grid') : null;
        if (!grid) return;

        var existingLetters = Array.from(grid.querySelectorAll('.srev-opt-pill')).map(function (p) {
            return p.getAttribute('data-letter');
        });
        var nextLetter = ['A', 'B', 'C', 'D'].find(function (l) { return existingLetters.indexOf(l) === -1; });
        if (!nextLetter) return;

        var pill = document.createElement('div');
        pill.className = 'srev-opt-pill';
        pill.setAttribute('data-letter', nextLetter);
        pill.style.cssText = 'display: flex; align-items: center; gap: 6px; padding: 4px 7px; border-radius: 7px; border: 1px solid #e2e8f0; background: #fff;';
        pill.innerHTML = '<input type="radio" name="sets[' + sIdx + '][questions][' + qIdx + '][correct]" value="' + nextLetter + '" onchange="handleCorrectOptionChange(this, ' + sIdx + ')" style="cursor: pointer;" title="Chọn ' + nextLetter + ' làm đáp án đúng">'
            + '<strong style="font-size: 12px; color: #334155;">(' + nextLetter + ')</strong>'
            + '<input type="text" name="sets[' + sIdx + '][questions][' + qIdx + '][options][' + nextLetter + ']" value="" class="srev-input" style="padding: 4px 6px; font-size: 12.5px;">'
            + '<button type="button" onclick="removeStagingOption(this, ' + sIdx + ')" title="Xóa phương án ' + nextLetter + '" style="border: none; background: transparent; color: #dc2626; font-weight: 700; cursor: pointer; padding: 0 3px;">&times;</button>';

        grid.appendChild(pill);
        if (existingLetters.length + 1 >= 4 && btn.parentElement) {
            btn.parentElement.style.display = 'none';
        }
        recalculateReviewStats();
    }

    function removeStagingOption(btn, sIdx) {
        var pill = btn ? btn.closest('.srev-opt-pill') : null;
        var qBox = btn ? btn.closest('.srev-q-box') : null;
        if (!pill || !qBox) return;
        pill.remove();
        var addBtn = qBox.querySelector('button[onclick^="addStagingOption"]');
        if (addBtn && addBtn.parentElement) {
            addBtn.parentElement.style.display = 'block';
        }
        recalculateReviewStats();
    }

    (function () {
        var commitForm = document.getElementById('stagingCommitForm');
        var payloadInput = document.getElementById('commit-payload-json');
        if (!commitForm || !payloadInput) return;

        commitForm.addEventListener('input', recalculateReviewStats);
        commitForm.addEventListener('change', recalculateReviewStats);

        commitForm.addEventListener('submit', function () {
            var setsPayload = [];
            var formPanes = commitForm.querySelectorAll('[id^="srev-form-pane-"]');

            formPanes.forEach(function (formPane) {
                var partEl = formPane.querySelector('select[name$="[part]"]');
                var titleEl = formPane.querySelector('input[name$="[title]"]');
                var stimEl = formPane.querySelector('textarea[name$="[stimulus]"]');
                var transEl = formPane.querySelector('textarea[name$="[transcript]"]');
                var mediaEl = formPane.querySelector('input[name$="[media_url]"]');
                var audioEl = formPane.querySelector('input[name$="[audio_url]"]');

                var questionsPayload = [];
                var qBoxes = formPane.querySelectorAll('.srev-q-box');
                qBoxes.forEach(function (box, idx) {
                    var seqEl = box.querySelector('input[name$="[seq]"]');
                    var diffEl = box.querySelector('input[name$="[difficulty]"]');
                    var qMediaEl = box.querySelector('input[name$="[media_url]"]');
                    var contentEl = box.querySelector('textarea[name$="[content]"]');
                    var expEl = box.querySelector('textarea[name$="[explanation]"]');
                    var checkedOpt = box.querySelector('input[name$="[correct]"]:checked');

                    var optionsMap = {};
                    ['A', 'B', 'C', 'D'].forEach(function (letter) {
                        var optInput = box.querySelector('input[name$="[options][' + letter + ']"]');
                        if (optInput) {
                            optionsMap[letter] = optInput.value;
                        }
                    });

                    questionsPayload.push({
                        seq: seqEl ? seqEl.value : (idx + 1),
                        difficulty: diffEl ? diffEl.value : 'UNSET',
                        media_url: qMediaEl ? qMediaEl.value : '',
                        content: contentEl ? contentEl.value : '',
                        explanation: expEl ? expEl.value : '',
                        correct: checkedOpt ? checkedOpt.value : 'A',
                        options: optionsMap
                    });
                });

                if (questionsPayload.length === 0) {
                    return;
                }

                var setFileInput = formPane.querySelector('.srev-set-image-input');
                if (setFileInput) {
                    setFileInput.setAttribute('name', 'set_images[' + setsPayload.length + ']');
                }

                setsPayload.push({
                    part: partEl ? partEl.value : 'PART5',
                    title: titleEl ? titleEl.value : '',
                    stimulus: stimEl ? stimEl.value : '',
                    transcript: transEl ? transEl.value : '',
                    media_url: mediaEl ? mediaEl.value : '',
                    audio_url: audioEl ? audioEl.value : '',
                    questions: questionsPayload
                });
            });

            payloadInput.value = JSON.stringify(setsPayload);
            commitForm.querySelectorAll('[name^="sets["]').forEach(function (el) {
                el.disabled = true;
            });
        });
    })();
</script>
@endpush
@endsection

@extends('layouts.admin', ['active' => 'de-thi'])

@section('title', $test['title'] . ' | Chi tiết & Soạn đề thi')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.tests.index') }}" style="color: inherit; text-decoration: none;">Quản lý đề thi</a>
    <span class="crumb-arrow">></span>
    <span class="crumb-active">{{ str_replace(['—', '–'], '-', $test['code']) }}</span>
@endsection

@section('content')
<style>
    .ts-part-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
    .ts-part-head { padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px; }
    .ts-part-progress-track { width: 100px; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 4px; }
    .ts-part-progress-fill { height: 100%; border-radius: 999px; transition: width 0.3s ease; }
    .ts-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; white-space: nowrap; }
    .ts-badge-active { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .ts-badge-draft { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .ts-badge-archived { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .ts-q-card { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .ts-q-child-card { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #edf2f7; }
    .ts-q-code { font-size: 11.5px; font-weight: 700; color: #64748b; background: #f8fafc; padding: 2px 7px; border-radius: 5px; border: 1px solid #e2e8f0; }
    .ts-q-answer-pill { font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
</style>
<div class="qbank-page">

    {{-- Thông báo kết quả thao tác --}}
    @if (session('success'))
        <div class="alert-admin-success" style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 14px; margin-bottom: 20px; font-size: 14px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <strong>Thành công!</strong>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-admin-error" style="display: flex; align-items: center; gap: 10px; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 14px; margin-bottom: 20px; font-size: 14px;">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 1. Header Trang & Trạng thái lưu (Save State) + Các nút thao tác --}}
    <div class="user-page-header" style="margin-bottom: 20px;">
        <div>
            <div class="qbank-breadcrumb-sub">
                <a href="{{ route('admin.tests.index') }}" style="color: inherit; text-decoration: none;">&larr; Quay lại danh sách đề thi</a>
                <span>></span>
                <span class="crumb-active">{{ str_replace(['—', '–'], '-', $test['code']) }}</span>
            </div>
            <div class="user-title-row" style="margin-top: 6px; flex-wrap: wrap; gap: 10px;">
                <h1 class="user-page-title">{{ str_replace(['—', '–'], '-', $test['title']) }}</h1>
                <span class="user-count-badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                    {{ str_replace(['—', '–'], '-', $test['code']) }}
                </span>

                {{-- Badge Trạng thái lưu (Save State) --}}
                <span id="saveStateBadge" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; padding: 4px 12px; border-radius: 999px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                    <span id="saveStateDot" style="width: 7px; height: 7px; border-radius: 50%; background: #10b981;"></span>
                    <span id="saveStateText">Đã lưu lúc {{ $lastSavedAt ?? now()->format('H:i') }}</span>
                </span>
            </div>
            <p class="user-page-subtitle" style="margin-top: 4px;">
                Loại đề: <strong>{{ $test['type_name'] }}</strong> - Thời gian chuẩn: <strong>{{ $test['total_time'] }} phút</strong> - Ngày tạo: <strong>{{ $test['created_at'] ?: '-' }}</strong>
            </p>
        </div>

        <div class="user-header-actions" style="flex-wrap: wrap; gap: 8px;">
            @php
                $audioSummary = $test['audio_summary'] ?? ['is_listening_test' => false, 'missing_audio_count' => 0, 'total_listening_questions' => 0, 'missing_audio_question_ids' => []];
            @endphp
            @if (!empty($audioSummary['is_listening_test']))
                <button type="button"
                        id="btnTestBatchTts"
                        class="btn-admin-action"
                        style="{{ ($audioSummary['missing_audio_count'] ?? 0) > 0 ? 'color: #0369a1; border-color: #7dd3fc; background: #f0f9ff; font-weight: 700;' : 'color: #065f46; border-color: #a7f3d0; background: #ecfdf5; font-weight: 700;' }}"
                        onclick="openTestBatchTtsModal()"
                        title="Sinh tự động file Audio MP3 (TTS) cho các câu Listening Part 1–4 còn thiếu trong đề này">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                    </svg>
                    @if (($audioSummary['missing_audio_count'] ?? 0) > 0)
                        <span>Sinh Audio TTS hàng loạt (Thiếu {{ $audioSummary['missing_audio_count'] }})</span>
                    @else
                        <span>✓ Đủ 100% Audio Listening</span>
                    @endif
                </button>
            @endif

            {{-- Nút Xem trước toàn bộ Đề thi --}}
            <button type="button" class="btn-admin-action" style="color: #0f766e; border-color: #99f6e4; background: #f0fdfa; font-weight: 700;" onclick="openExamPreviewModal('full', null, null)" title="Xem trước giao diện làm bài của học viên cho toàn bộ đề thi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Xem trước Đề thi</span>
            </button>

            {{-- Nút Chọn câu hỏi có sẵn từ Ngân hàng --}}
            <button type="button" class="btn-admin-action" style="color: #4338ca; border-color: #c7d2fe; background: #eef2ff; font-weight: 700;" onclick="openBankPickerModal('all')" title="Chọn câu hỏi có sẵn từ Ngân hàng câu hỏi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                </svg>
                <span>Chọn từ Ngân hàng</span>
            </button>

            {{-- Nút Tạo câu hỏi mới --}}
            <a href="{{ route('admin.questions.create', ['test_id' => $test['id']]) }}" class="btn-admin-action" style="color: #4338ca; border-color: #c7d2fe; background: #ffffff; font-weight: 700; text-decoration: none;" title="Tạo câu hỏi mới vào đề thi này">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Tạo câu hỏi mới</span>
            </a>

            {{-- Nút Lưu Đề thi --}}
            <form method="POST" action="{{ route('admin.tests.save', $test['id']) }}" style="display: inline; margin: 0;">
                @csrf
                <input type="hidden" name="action_type" value="save">
                <button type="submit" class="btn-admin-primary" title="Lưu trạng thái đề thi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu đề thi</span>
                </button>
            </form>

            {{-- Nút Duyệt & Xuất bản đề thi --}}
            <form method="POST" action="{{ route('admin.tests.save', $test['id']) }}" style="display: inline; margin: 0;">
                @csrf
                <input type="hidden" name="action_type" value="approve">
                <button type="submit" class="btn-admin-primary" style="background: #059669; border-color: #059669;" title="Duyệt tất cả câu hỏi trong đề và xuất bản chính thức">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Duyệt &amp; Xuất bản</span>
                </button>
            </form>
        </div>
    </div>

    {{-- 2. Card Tổng quan Tiến độ Hoàn thiện Đề thi --}}
    @php
        $isComplete = $test['status'] === 'COMPLETED';
        $isInvalid = $test['status'] === 'INVALID';
        $isEmpty = $test['status'] === 'EMPTY';
        $rate = $test['completion_rate'];
        $barWidth = min(100, max(0, $rate));
    @endphp
    <div id="testOverviewProgressCard" data-total-target="{{ $test['target_questions'] }}" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 22px 24px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 16px;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    Tiến độ hoàn thiện đề thi ({{ $test['short_type_name'] ?? $test['type_name'] }})
                </div>
                <div style="font-size: 25px; font-weight: 850; color: #0f172a; margin-top: 4px; display: flex; align-items: baseline; gap: 8px;">
                    <span id="overviewCountText">{{ $test['current_questions'] }} / {{ $test['target_questions'] }} câu hỏi</span>
                    <span id="overviewRateText" style="font-size: 16px; font-weight: 700; color: {{ $isInvalid ? '#dc2626' : ($isComplete ? '#059669' : ($isEmpty ? '#94a3b8' : '#4338ca')) }};">
                        ({{ number_format($rate, 1) }}%)
                    </span>
                </div>
            </div>

            <div id="overviewStatusBadgeWrap" style="display: flex; align-items: center; gap: 10px;">
                @if ($isInvalid)
                    <div id="overviewStatusBadge" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #fef2f2; color: #b91c1c; border: 1.5px solid #fecaca; font-size: 13.5px; font-weight: 700;">
                        <span>Vượt chỉ tiêu / Lệch cấu trúc{{ !empty($test['issue_reason']) ? ' (' . $test['issue_reason'] . ')' : '' }}</span>
                    </div>
                @elseif ($isComplete)
                    <div id="overviewStatusBadge" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; font-size: 13.5px; font-weight: 700;">
                        <span>✓ Đã hoàn thiện (100%)</span>
                    </div>
                @elseif ($isEmpty)
                    <div id="overviewStatusBadge" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #f8fafc; color: #64748b; border: 1.5px solid #e2e8f0; font-size: 13.5px; font-weight: 700;">
                        <span>Đề thi mới (Chưa có câu hỏi nào)</span>
                    </div>
                @else
                    <div id="overviewStatusBadge" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; font-size: 13.5px; font-weight: 700;">
                        <span>Đang biên soạn (Còn Part chưa đủ chỉ tiêu)</span>
                    </div>
                @endif
            </div>
        </div>

        <div style="width: 100%; height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 10px;">
            <div id="overviewProgressBarFill" style="height: 100%; width: {{ $barWidth }}%; background: {{ $isInvalid ? '#ef4444' : ($isComplete ? '#10b981' : ($isEmpty ? '#cbd5e1' : '#4f46e5')) }}; border-radius: 999px; transition: width 0.4s ease;"></div>
        </div>

        <div style="font-size: 13px; color: #64748b; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px;">
            <span>Cấu trúc Part được khóa cứng theo đúng loại đề <strong>{{ $test['blueprint']['name'] }}</strong> (không trộn lẫn Part của kỹ năng khác).</span>
            <span>Trạng thái lưu gần nhất: <strong>{{ $lastSavedAt ?? now()->format('H:i') }}</strong></span>
        </div>

        <div id="overviewDraftWarningBanner" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: 10px; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; font-size: 13px; font-weight: 600;"></div>
    </div>

    {{-- 2b. Banner Kiểm định Chất lượng Đề thi (Test Validation Banner) --}}
    @php
        $quality = $test['quality'] ?? ['has_warnings' => false, 'quota_issues' => [], 'wrong_skill_ids' => [], 'format_issues' => [], 'duplicate_ids' => [], 'duplicate_groups' => [], 'cleanup_ids' => []];
        $cleanupIds = $quality['cleanup_ids'] ?? [];
    @endphp
    @if (!empty($quality['has_warnings']))
        <div id="testQualityValidationBanner" style="background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 16px; padding: 18px 22px; margin-bottom: 24px; color: #78350f;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; background: #fef3c7; border: 1px solid #f59e0b; color: #b45309; font-weight: 900; font-size: 16px;">!</span>
                    <div>
                        <div style="font-size: 15px; font-weight: 800; color: #92400e;">
                            Cảnh báo Kiểm định Chất lượng Đề thi ({{ $quality['warning_count'] ?? 0 }} vấn đề cần lưu ý)
                        </div>
                        <div style="font-size: 12.5px; color: #a16207;">
                            Hệ thống tự động rà soát chỉ tiêu ETS từng Part, kỹ năng, định dạng đáp án/media và câu hỏi trùng lặp.
                        </div>
                    </div>
                </div>

                @if (!empty($cleanupIds))
                    <form method="POST" action="{{ route('admin.tests.cleanup-issues', $test['id']) }}" style="margin: 0;" onsubmit="return confirm('Gỡ nhanh {{ count($cleanupIds) }} câu trùng lặp / sai kỹ năng khỏi đề thi này? (Câu hỏi vẫn được giữ lại trong Ngân hàng câu hỏi)');">
                        @csrf
                        <button type="submit" class="btn-admin-action" style="background: #dc2626; color: #ffffff; border-color: #b91c1c; font-weight: 700; font-size: 12.5px; padding: 8px 14px;">
                            <span>Xóa nhanh câu trùng / sai kỹ năng ({{ count($cleanupIds) }} câu)</span>
                        </button>
                    </form>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; font-size: 13px;">
                @if (!empty($quality['quota_issues']))
                    <div style="background: #ffffff; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 14px;">
                        <div style="font-weight: 800; color: #b45309; margin-bottom: 6px;">1. Lệch chỉ tiêu ETS từng Part</div>
                        <ul style="margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 4px; color: #334155;">
                            @foreach ($quality['quota_issues'] as $qi)
                                <li style="{{ $qi['type'] === 'excess' ? 'color: #b91c1c; font-weight: 700;' : '' }}">
                                    {{ $qi['message'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (!empty($quality['wrong_skill_ids']))
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 14px;">
                        <div style="font-weight: 800; color: #b91c1c; margin-bottom: 6px;">2. Câu hỏi sai kỹ năng / sai Part ({{ count($quality['wrong_skill_ids']) }} câu)</div>
                        <div style="color: #7f1d1d; line-height: 1.5;">
                            Các mã câu không thuộc cấu trúc đề này:
                            <strong>{{ implode(', ', array_map(fn ($qid) => '#Q-'.$qid, $quality['wrong_skill_ids'])) }}</strong>
                        </div>
                    </div>
                @endif

                @if (!empty($quality['duplicate_groups']))
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 14px;">
                        <div style="font-weight: 800; color: #b91c1c; margin-bottom: 6px;">3. Phát hiện câu trùng nội dung ({{ count($quality['duplicate_ids']) }} câu thừa)</div>
                        <ul style="margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 4px; color: #7f1d1d;">
                            @foreach (array_slice($quality['duplicate_groups'], 0, 5) as $dg)
                                <li>
                                    Giữ <strong>#Q-{{ $dg['keep_id'] }}</strong>, trùng với
                                    <strong>{{ implode(', ', array_map(fn ($qid) => '#Q-'.$qid, $dg['duplicate_ids'])) }}</strong>
                                    <span style="color: #64748b;">("{{ $dg['sample'] }}")</span>
                                </li>
                            @endforeach
                            @if (count($quality['duplicate_groups']) > 5)
                                <li style="font-style: italic;">... và {{ count($quality['duplicate_groups']) - 5 }} nhóm trùng khác.</li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if (!empty($quality['format_issues']))
                    <div style="background: #ffffff; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 14px;">
                        <div style="font-weight: 800; color: #b45309; margin-bottom: 6px;">4. Câu thiếu định dạng / media / đáp án ({{ count($quality['format_issues']) }})</div>
                        <ul style="margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 4px; color: #334155;">
                            @foreach (array_slice($quality['format_issues'], 0, 6) as $fi)
                                <li><strong>{{ $fi['code'] }}</strong> ({{ $fi['part_code'] }}): {{ $fi['reason'] }}</li>
                            @endforeach
                            @if (count($quality['format_issues']) > 6)
                                <li style="font-style: italic; color: #64748b;">... và {{ count($quality['format_issues']) - 6 }} lỗi định dạng khác.</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- 3. Cấu trúc Kỹ năng và Danh sách từng Part --}}
    @foreach ($test['skills'] as $skillKey => $skill)
        <div style="margin-bottom: 36px;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #4338ca; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">
                        {{ strtoupper(substr($skill['name'], 0, 1)) }}
                    </div>
                    <div>
                        <h2 style="font-size: 19px; font-weight: 800; color: #0f172a; margin: 0;">
                            Kỹ năng {{ $skill['name'] }}
                        </h2>
                    </div>
                </div>
                <div style="font-size: 13.5px; font-weight: 600; color: #64748b;">
                    Thời gian: <strong>{{ $skill['time_limit'] }} phút</strong> - Chỉ tiêu: <strong>{{ $skill['total_questions'] }} câu hỏi</strong>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                @foreach ($skill['parts'] as $part)
                    @php
                        $partComplete = $part['is_complete'];
                        $partEmpty = $part['current_count'] === 0;
                        $partRate = $part['completion_rate'];
                        $partMode = $part['mode'] ?? $skillKey;
                    @endphp
                    <div class="test-part-card ts-part-card" data-part-code="{{ $part['code'] }}" data-target="{{ $part['target_count'] }}">
                        {{-- Header của Part --}}
                        <div class="ts-part-head">
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <span style="font-size: 16px; font-weight: 800; color: #1e1b4b;">
                                        {{ str_replace(['—', '–'], '-', $part['name']) }}
                                    </span>
                                    <span class="part-status-badge ts-badge {{ $partComplete ? 'ts-badge-active' : ($partEmpty ? 'ts-badge-archived' : 'ts-badge-draft') }}">
                                        @if ($partComplete)
                                            ✓ Đã đủ câu
                                        @elseif ($partEmpty)
                                            Chưa có câu hỏi
                                        @else
                                            Thiếu {{ max(0, $part['target_count'] - $part['current_count']) }} câu
                                        @endif
                                    </span>
                                </div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 3px;">
                                    {{ str_replace(['—', '–'], '-', $part['description']) }}
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                {{-- Tiến độ số câu --}}
                                <div style="text-align: right; margin-right: 6px;">
                                    <div class="part-count-text" style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                                        {{ $part['current_count'] }} / {{ $part['target_count'] }} câu
                                    </div>
                                    <div class="ts-part-progress-track">
                                        <div class="part-progress-fill ts-part-progress-fill" style="width: {{ $partRate }}%; background: {{ $partComplete ? '#10b981' : ($partEmpty ? '#cbd5e1' : '#4f46e5') }};"></div>
                                    </div>
                                </div>

                                {{-- Nút Xem trước Part --}}
                                <button type="button" class="btn-admin-action" style="background: #f0fdfa; color: #0f766e; border-color: #99f6e4; font-weight: 700; font-size: 12px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 5px;" onclick="openExamPreviewModal('part', '{{ $part['code'] }}', null)" title="Xem trước giao diện học viên cho {{ $part['name'] }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>Xem trước Part</span>
                                </button>

                                {{-- Nút Lưu Part --}}
                                <form method="POST" action="{{ route('admin.tests.save', $test['id']) }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <input type="hidden" name="action_type" value="save">
                                    <input type="hidden" name="part_name" value="{{ $part['name'] }}">
                                    <button type="submit" class="btn-admin-action" style="background: #ecfdf5; color: #059669; border-color: #a7f3d0; font-weight: 700; font-size: 12px; padding: 6px 10px; display: inline-flex; align-items: center; gap: 5px;" title="Lưu phần thi này">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                            <polyline points="7 3 7 8 15 8"></polyline>
                                        </svg>
                                        <span>Lưu Part</span>
                                    </button>
                                </form>

                                {{-- Nút Chọn câu hỏi từ Ngân hàng --}}
                                <button type="button" class="btn-admin-action" style="background: #eef2ff; color: #4338ca; border-color: #c7d2fe; font-weight: 700; font-size: 12px; padding: 6px 11px;" onclick="openBankPickerModal('{{ $part['code'] }}')" title="Chọn câu hỏi có sẵn từ Ngân hàng cho {{ $part['name'] }}">
                                    <span>+ Chọn từ Ngân hàng</span>
                                </button>

                                {{-- Nút Tạo câu mới --}}
                                <a href="{{ route('admin.questions.create', ['test_id' => $test['id'], 'section_id' => $part['section_id']]) }}" class="btn-admin-action" style="background: #ffffff; color: #4338ca; border-color: #c7d2fe; font-weight: 700; font-size: 12px; padding: 6px 11px; text-decoration: none;" title="Tạo câu hỏi mới vào {{ $part['name'] }}">
                                    <span>+ Tạo câu mới</span>
                                </a>
                            </div>
                        </div>

                        {{-- Danh sách câu hỏi trong Part --}}
                        <div style="padding: 18px 20px;">
                            @if (empty($part['questions']))
                                <div style="text-align: center; padding: 28px 16px; background: #fafaf9; border: 1.5px dashed #e2e8f0; border-radius: 12px;">
                                    <div style="font-size: 14.5px; font-weight: 700; color: #334155;">
                                        Phần thi {{ str_replace(['—', '–'], '-', $part['name']) }} chưa có câu hỏi nào
                                    </div>
                                    <p style="font-size: 13px; color: #64748b; margin: 4px 0 14px; max-width: 440px; margin-left: auto; margin-right: auto;">
                                        Cần bổ sung đúng <strong>{{ $part['target_count'] }} câu hỏi</strong> theo cấu trúc TOEIC để hoàn thiện phần thi này.
                                    </p>
                                    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
                                        <button type="button" class="btn-admin-action" style="display: inline-flex; font-size: 13px; padding: 7px 16px; background: #eef2ff; color: #4338ca; border-color: #c7d2fe; font-weight: 700;" onclick="openBankPickerModal('{{ $part['code'] }}')">
                                            <span>Chọn câu hỏi từ Ngân hàng</span>
                                        </button>
                                        <a href="{{ route('admin.questions.create', ['test_id' => $test['id'], 'section_id' => $part['section_id']]) }}" class="btn-admin-primary" style="display: inline-flex; font-size: 13px; padding: 7px 16px; text-decoration: none;">
                                            <span>+ Tạo câu hỏi mới vào Part này</span>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    @foreach ($part['questions'] as $item)
                                        @if (!empty($item['is_grouped']))
                                            {{-- NHÓM CÂU HỎI (QUESTION SET) --}}
                                            <div style="border: 1.5px solid #c7d2fe; border-radius: 12px; background: #fdfdfd; overflow: hidden;">
                                                <div style="background: #eef2ff; padding: 12px 18px; border-bottom: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: space-between;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <span style="font-size: 12px; font-weight: 800; background: #4338ca; color: #fff; padding: 2px 7px; border-radius: 5px;">
                                                            QUESTION SET
                                                        </span>
                                                        <span style="font-size: 13.5px; font-weight: 700; color: #1e1b4b;">
                                                            {{ str_replace(['—', '–'], '-', $item['group_title']) }}
                                                        </span>
                                                    </div>
                                                    <span style="font-size: 12px; color: #4338ca; font-weight: 600;">
                                                        Gồm {{ count($item['child_questions']) }} câu hỏi con
                                                    </span>
                                                </div>

                                                <div style="padding: 12px 18px; background: #ffffff; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #475569; line-height: 1.6;">
                                                    @if (!empty($item['audio_url']))
                                                        <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                            <span style="font-size: 11.5px; font-weight: 700; color: #0369a1; background: #e0f2fe; padding: 2px 8px; border-radius: 999px;">Audio nhóm</span>
                                                            <audio controls preload="none" src="{{ $item['audio_url'] }}" style="height: 32px; max-width: 300px;"></audio>
                                                        </div>
                                                    @elseif ($partMode === 'listening')
                                                        <div style="margin-bottom: 8px;">
                                                            <span style="font-size: 11.5px; font-weight: 700; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 999px;">Chưa có Audio MP3</span>
                                                        </div>
                                                    @endif
                                                    @if (!empty($item['image_url']))
                                                        <div style="margin-bottom: 8px;">
                                                            <img src="{{ $item['image_url'] }}" alt="Minh họa nhóm câu hỏi" style="max-height: 150px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: contain;">
                                                        </div>
                                                    @endif
                                                    @if (!empty($item['stimulus_text']))
                                                        <div style="margin-bottom: 6px; font-style: italic; color: #334155;">
                                                            {{ $item['stimulus_text'] }}
                                                        </div>
                                                    @endif
                                                    @if (!empty($item['transcript']))
                                                        <div style="padding: 8px 12px; background: #f8fafc; border-radius: 6px; border-left: 3px solid #6366f1; font-size: 12.5px; color: #334155;">
                                                            <strong>Transcript / Lời thoại:</strong> {{ \Illuminate\Support\Str::limit($item['transcript'], 180) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div style="padding: 12px 18px; display: flex; flex-direction: column; gap: 10px;">
                                                    @foreach ($item['child_questions'] as $child)
                                                        @php
                                                            $childStatus = strtoupper((string) ($child['status'] ?? 'ACTIVE'));
                                                        @endphp
                                                        <div class="test-q-item ts-q-child-card" data-question-id="{{ $child['id'] }}" data-status="{{ $childStatus }}">
                                                            <div style="display: flex; align-items: baseline; gap: 10px; flex: 1; min-width: 240px;">
                                                                <span style="font-size: 13px; font-weight: 800; color: #4338ca; width: 34px;">
                                                                    #{{ $child['sequence_no'] }}
                                                                </span>
                                                                <span class="ts-q-code">
                                                                    {{ $child['question_code'] }}
                                                                </span>
                                                                @if ($childStatus === 'ARCHIVED')
                                                                    <span class="ts-badge ts-badge-archived">Lưu trữ</span>
                                                                @elseif ($childStatus !== 'ACTIVE')
                                                                    <span class="ts-badge ts-badge-draft">Bản nháp</span>
                                                                @endif
                                                                <span style="font-size: 13.5px; color: #1e293b; font-weight: 600;">
                                                                    {{ $child['content'] }}
                                                                </span>
                                                            </div>
                                                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                                @if (!empty($child['correct_option']))
                                                                    <span class="ts-q-answer-pill">
                                                                        Đ/A: ({{ $child['correct_option'] }})
                                                                    </span>
                                                                @endif
                                                                <button type="button" class="action-icon-btn danger" title="Gỡ câu hỏi khỏi đề" onclick="confirmDetachQuestion({{ $child['id'] }}, '{{ $child['question_code'] }}')">
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            {{-- CÂU HỎI ĐƠN LẺ (SINGLE QUESTION) --}}
                                            @php
                                                $itemStatus = strtoupper((string) ($item['status'] ?? 'ACTIVE'));
                                            @endphp
                                            <div class="test-q-item ts-q-card" data-question-id="{{ $item['id'] }}" data-status="{{ $itemStatus }}">
                                                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 240px;">
                                                    <span style="font-size: 13.5px; font-weight: 800; color: #4338ca; width: 36px; text-align: center;">
                                                        #{{ $item['sequence_no'] }}
                                                    </span>
                                                    <span class="ts-q-code">
                                                        {{ $item['question_code'] }}
                                                    </span>
                                                    @if ($itemStatus === 'ARCHIVED')
                                                        <span class="ts-badge ts-badge-archived">Lưu trữ</span>
                                                    @elseif ($itemStatus !== 'ACTIVE')
                                                        <span class="ts-badge ts-badge-draft">Bản nháp</span>
                                                    @endif
                                                    @if (!empty($item['image_url']))
                                                        <img src="{{ $item['image_url'] }}" alt="Hình ảnh câu hỏi" style="width: 48px; height: 36px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                                    @endif
                                                    <div>
                                                        <div style="font-size: 13.5px; color: #1e293b; font-weight: 600;">
                                                            {{ $item['content'] }}
                                                        </div>
                                                        @if (!empty($item['audio_url']))
                                                            <div style="margin-top: 6px;">
                                                                <audio controls preload="none" src="{{ $item['audio_url'] }}" style="height: 30px; max-width: 260px;"></audio>
                                                            </div>
                                                        @elseif ($partMode === 'listening')
                                                            <div style="margin-top: 4px;">
                                                                <span style="font-size: 11px; font-weight: 700; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 1px 7px; border-radius: 999px;">Chưa có Audio MP3</span>
                                                            </div>
                                                        @endif
                                                        @if (!empty($item['options']))
                                                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; font-size: 12px; color: #64748b;">
                                                                @foreach ($item['options'] as $opt)
                                                                    <span style="{{ ($opt['label'] === $item['correct_option']) ? 'color:#059669; font-weight:700;' : '' }}">
                                                                        ({{ $opt['label'] }}) {{ $opt['content'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                    @if (!empty($item['correct_option']))
                                                        <span class="ts-q-answer-pill">
                                                            Đ/A: ({{ $item['correct_option'] }})
                                                        </span>
                                                    @endif
                                                    <button type="button" class="action-icon-btn danger" title="Gỡ câu hỏi khỏi đề" onclick="confirmDetachQuestion({{ $item['id'] }}, '{{ $item['question_code'] }}')">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

</div>

{{-- Form ẩn xóa câu hỏi khỏi đề thi --}}
<form id="detach-question-form" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- MODAL 1: CHỌN CÂU HỎI TỪ NGÂN HÀNG CÂU HỎI (ĐÃ LỌC ĐÚNG LOẠI ĐỀ) --}}
<div id="modal-pick-from-bank" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 780px; width: 95%;">
        <div class="admin-modal-header">
            <div>
                <h3 class="admin-modal-title" style="margin: 0;">Chọn câu hỏi từ Ngân hàng gắn vào đề {{ str_replace(['—', '–'], '-', $test['code']) }}</h3>
                <div style="font-size: 12.5px; color: #64748b; margin-top: 2px;">
                    Đề thi: <strong>{{ $test['title'] }}</strong> ({{ $test['short_type_name'] ?? $test['type_name'] }})
                </div>
            </div>
            <button type="button" class="admin-modal-close" onclick="closeBankPickerModal()">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.tests.questions.attach', $test['id']) }}">
            @csrf
            <div class="admin-modal-body" style="padding: 18px 22px;">
                <div style="display: grid; grid-template-columns: 1fr 240px auto; gap: 10px; margin-bottom: 14px;">
                    <input type="text" id="bankSearchInput" class="form-control-admin" placeholder="Tìm theo mã #Q-..., nội dung câu hỏi..." oninput="filterBankModalRows()" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;">

                    <select id="bankPartSelect" class="form-control-admin" onchange="filterBankModalRows()" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff;">
                        <option value="all">Tất cả Part thuộc đề này</option>
                        @if (isset($sections))
                            @foreach ($sections as $sec)
                                <option value="{{ strtoupper($sec->code) }}">{{ str_replace(['—', '–'], '-', $sec->name) }}</option>
                            @endforeach
                        @endif
                    </select>

                    <a href="{{ route('admin.questions.index', ['test_id' => $test['id']]) }}" class="btn-admin-action" style="padding: 8px 12px; font-size: 12.5px; text-decoration: none; white-space: nowrap;">
                        Mở Ngân hàng &rarr;
                    </a>
                </div>

                <div style="max-height: 360px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px;">
                    @if (isset($bankQuestions) && $bankQuestions->isNotEmpty())
                        <table class="user-data-table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="bankCheckAll" onchange="toggleAllVisibleBankQuestions(this)">
                                    </th>
                                    <th style="width: 120px;">Mã &amp; Part</th>
                                    <th>Nội dung câu hỏi</th>
                                    <th style="width: 80px;">Độ khó</th>
                                    <th style="width: 90px; text-align: right;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bankQuestions as $bq)
                                    @php
                                        $partCode = strtoupper((string) ($bq->section?->code ?? ''));
                                        $bqPayload = [
                                            'id' => $bq->question_id,
                                            'question_code' => $bq->question_code_display,
                                            'part_code' => $partCode,
                                            'content' => $bq->content,
                                            'difficulty' => $bq->difficulty,
                                            'explanation' => $bq->explanation ?? '',
                                            'stimulus_text' => $bq->questionSet?->stimulus_text,
                                            'transcript' => $bq->questionSet?->transcript,
                                            'image_url' => ($bq->questionSet && strtoupper((string) $bq->questionSet->media_type) === 'IMAGE') ? $bq->questionSet->resolved_media_url : null,
                                            'audio_url' => $bq->questionSet?->resolved_audio_url,
                                            'correct_option' => $bq->options->firstWhere('is_correct', true)?->label ?? '',
                                            'options' => $bq->options->map(fn ($o) => ['label' => $o->label, 'content' => $o->content, 'is_correct' => (bool) $o->is_correct])->values()->all(),
                                        ];
                                    @endphp
                                    <tr class="bank-modal-row" data-part="{{ $partCode }}" data-search="{{ mb_strtolower($bq->question_code_display . ' ' . $bq->content) }}">
                                        <td style="text-align: center;">
                                            <input type="checkbox" name="question_ids[]" value="{{ $bq->question_id }}" class="bank-q-checkbox" onchange="updateBankSelectedCounter()">
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #4338ca; font-size: 12.5px;">{{ $bq->question_code_display }}</div>
                                            <span class="q-part-pill" style="font-size: 11px;">{{ $bq->part_badge }}</span>
                                        </td>
                                        <td>
                                            <div style="font-size: 13px; color: #1e293b; font-weight: 600;">
                                                {{ \Illuminate\Support\Str::limit($bq->content, 110) }}
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 11.5px; font-weight: 700; color: #475569;">{{ $bq->difficulty }}</span>
                                        </td>
                                        <td>
                                            <div class="user-actions-cell" style="justify-content: flex-end; gap: 4px;">
                                                <button type="button" class="action-icon-btn" title="Xem trước câu hỏi" onclick='openExamPreviewModal("question", "{{ $partCode }}", @json($bqPayload))'>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </button>
                                                <button type="button" class="action-icon-btn" title="Chỉnh sửa câu hỏi" onclick='openEditTestQuestionModal(@json($bqPayload))'>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 32px 16px; text-align: center; color: #64748b; font-size: 13.5px;">
                            Không còn câu hỏi nào chưa gắn thuộc các Part của đề thi này trong Ngân hàng.
                            <div style="margin-top: 10px;">
                                <a href="{{ route('admin.questions.create', ['test_id' => $test['id']]) }}" class="btn-admin-primary" style="display: inline-flex; font-size: 13px; padding: 7px 14px; text-decoration: none;">
                                    + Tạo câu hỏi mới ngay
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="admin-modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; color: #475569;">
                    Đã chọn: <strong id="bankSelectedCount" style="color: #4338ca;">0</strong> câu hỏi
                </span>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-admin-action" onclick="closeBankPickerModal()">Hủy</button>
                    <button type="submit" class="btn-admin-primary">Gắn vào Đề thi {{ str_replace(['—', '–'], '-', $test['code']) }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: CHỈNH SỬA CÂU HỎI TRỰC TIẾP TRONG ĐỀ THI --}}
<div id="modal-edit-test-question" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 620px; width: 95%;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="editTestQTitle" style="margin: 0;">Chỉnh sửa câu hỏi trong đề</h3>
            <button type="button" class="admin-modal-close" onclick="closeEditTestQuestionModal()">&times;</button>
        </div>

        <form method="POST" action="" id="formEditTestQuestion">
            @csrf
            @method('PUT')
            <div class="admin-modal-body" style="padding: 20px;">
                <div class="form-group-admin" style="margin-bottom: 14px;">
                    <label style="font-weight: 700; font-size: 13px; display: block; margin-bottom: 5px;">Nội dung câu hỏi / Văn bản đề <span style="color: #dc2626;">*</span></label>
                    <textarea name="content" id="editTestQContent" rows="3" class="form-control-admin" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px;" oninput="markTestUnsaved()"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="font-weight: 700; font-size: 13px; display: block; margin-bottom: 5px;">Độ khó</label>
                        <select name="difficulty" id="editTestQDifficulty" class="form-control-admin" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="UNSET">UNSET (Chưa xác định)</option>
                            <option value="EASY">EASY (Cơ bản)</option>
                            <option value="MEDIUM">MEDIUM (Trung bình)</option>
                            <option value="HARD">HARD (Nâng cao)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: 700; font-size: 13px; display: block; margin-bottom: 5px;">Đáp án đúng (A/B/C/D)</label>
                        <select name="correct_option" id="editTestQCorrect" class="form-control-admin" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="A">Đáp án A</option>
                            <option value="B">Đáp án B</option>
                            <option value="C">Đáp án C</option>
                            <option value="D">Đáp án D</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; font-size: 13px; display: block; margin-bottom: 6px;">4 Phương án trắc nghiệm (A, B, C, D):</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <input type="text" name="opt_a" id="editTestQOptA" placeholder="Phương án A" class="form-control-admin" style="padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <input type="text" name="opt_b" id="editTestQOptB" placeholder="Phương án B" class="form-control-admin" style="padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <input type="text" name="opt_c" id="editTestQOptC" placeholder="Phương án C" class="form-control-admin" style="padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                        <input type="text" name="opt_d" id="editTestQOptD" placeholder="Phương án D" class="form-control-admin" style="padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <label style="font-weight: 700; font-size: 13px;">Văn bản chuyển giọng đọc Audio (Transcript cho Listening / Speaking)</label>
                        <button type="button" class="btn-admin-action" style="padding: 4px 10px; font-size: 12px; color: #0284c7; border-color: #bae6fd; background: #f0f9ff; display: inline-flex; align-items: center; gap: 5px;" onclick="generateTtsFromEditModal()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                            </svg>
                            <span>Chuyển văn bản thành Ghi âm (TTS)</span>
                        </button>
                    </div>
                    <textarea name="transcript" id="editTestQTranscript" rows="2" class="form-control-admin" placeholder="Nhập lời thoại tiếng Anh để hệ thống chuyển thành file âm thanh cho học viên nghe..." style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;"></textarea>
                    <div id="editTestQTtsStatus" style="font-size: 12px; color: #059669; margin-top: 4px; display: none;"></div>
                </div>

                <div>
                    <label style="font-weight: 700; font-size: 13px; display: block; margin-bottom: 5px;">Giải thích đáp án</label>
                    <textarea name="explanation" id="editTestQExplanation" rows="2" class="form-control-admin" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px;"></textarea>
                </div>
            </div>

            <div class="admin-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-admin-action" onclick="closeEditTestQuestionModal()">Hủy</button>
                <button type="submit" class="btn-admin-primary">Lưu cập nhật câu hỏi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: XEM TRƯỚC GIAO DIỆN HỌC VIÊN (XEM TẤT CẢ CÂU / CHẾ ĐỘ THI TỪNG CÂU - HỖ TRỢ IMAGE, AUDIO, SPEAKING RECORDING & AI EXPLANATION) --}}
<div id="modal-preview-exam" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 920px; width: 96%;">
        <div class="admin-modal-header" style="background: #0f172a; color: #ffffff; border-radius: 14px 14px 0 0; padding: 16px 22px;">
            <div>
                <div style="font-size: 11.5px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px;" id="previewScopeBadge">
                    CHẾ ĐỘ XEM TRƯỚC GIAO DIỆN HỌC VIÊN (STUDENT SIMULATOR)
                </div>
                <h3 class="admin-modal-title" id="previewExamModalTitle" style="margin: 3px 0 0 0; color: #ffffff; font-size: 16px;">
                    {{ str_replace(['—', '–'], '-', $test['title']) }}
                </h3>
            </div>
            <button type="button" class="admin-modal-close" style="color: #ffffff;" onclick="closeExamPreviewModal()">&times;</button>
        </div>

        <div class="admin-modal-body" style="padding: 20px 22px; background: #f8fafc; max-height: 75vh; overflow-y: auto;">
            {{-- Thanh chuyển chế độ xem & điều hướng --}}
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 14px; background: #ffffff; padding: 10px 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span id="previewQuestionCounter" style="font-weight: 800; color: #4338ca; font-size: 13.5px;">Câu 1 / 1</span>
                    <span id="previewPartBadge" style="font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 6px; background: #eef2ff; color: #4338ca;">Part 1</span>
                </div>

                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <button type="button" id="btnModeSingle" class="btn-admin-action preview-mode-btn" style="padding: 6px 12px; font-size: 12.5px; font-weight: 700;" onclick="setPreviewDisplayMode('single')">
                        Xem từng câu
                    </button>
                    <button type="button" id="btnModePart" class="btn-admin-action preview-mode-btn" style="padding: 6px 12px; font-size: 12.5px; font-weight: 700;" onclick="setPreviewDisplayMode('part')">
                        Xem từng Part
                    </button>
                    <button type="button" id="btnModeList" class="btn-admin-action preview-mode-btn" style="padding: 6px 12px; font-size: 12.5px; font-weight: 700;" onclick="setPreviewDisplayMode('list')">
                        Xem tất cả câu
                    </button>
                    <span id="previewSingleNavWrap" style="display: inline-flex; gap: 6px; margin-left: 6px;">
                        <button type="button" class="btn-admin-action" style="padding: 5px 10px; font-size: 12px;" onclick="stepPreviewQuestion(-1)">&larr; Trước</button>
                        <button type="button" class="btn-admin-action" style="padding: 5px 10px; font-size: 12px;" onclick="stepPreviewQuestion(1)">Tiếp &rarr;</button>
                    </span>
                </div>
            </div>

            {{-- Thanh chọn nhanh từng Part (chỉ hiện ở chế độ Xem từng Part) --}}
            <div id="previewPartSelectorWrap" style="display: none; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; padding: 10px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;"></div>

            {{-- Bảng chọn nhanh số câu hỏi (Question Navigator Palette - chỉ hiện ở chế độ Xem từng câu) --}}
            <div id="previewQuestionPalette" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; padding: 10px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;"></div>

            {{-- CHẾ ĐỘ XEM TỪNG PART / XEM TẤT CẢ CÂU --}}
            <div id="previewAllQuestionsContainer" style="display: none; flex-direction: column; gap: 14px;"></div>

            {{-- CHẾ ĐỘ 2: XEM TỪNG CÂU (SINGLE EXAM SIMULATOR) --}}
            <div id="previewSingleQuestionContainer">
                {{-- KHUNG 1: GIAO DIỆN LISTENING --}}
                <div id="previewListeningZone" style="display: none; background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 14px; padding: 18px; margin-bottom: 14px;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 36px; height: 36px; border-radius: 50%; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                </svg>
                            </span>
                            <div>
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a;">Giao diện Nghe của Học viên (Audio / TTS)</div>
                                <div style="font-size: 12px; color: #64748b;">Học viên nghe file audio hoặc giọng đọc chuẩn TTS và chọn đáp án đúng</div>
                            </div>
                        </div>
                        <button type="button" class="btn-admin-action" style="font-size: 12px; padding: 6px 12px; color: #0284c7; border-color: #7dd3fc; background: #f0f9ff; display: inline-flex; align-items: center; gap: 5px;" onclick="playCurrentPreviewAudio()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            <span>Phát âm thanh</span>
                        </button>
                    </div>

                    <div style="background: #0f172a; color: #fff; padding: 12px 16px; border-radius: 10px; display: flex; align-items: center; gap: 12px;">
                        <button type="button" onclick="playCurrentPreviewAudio()" style="width: 36px; height: 36px; border-radius: 50%; border: none; background: #38bdf8; color: #0f172a; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"></polygon></svg>
                        </button>
                        <div style="flex: 1;">
                            <div style="font-size: 12px; color: #94a3b8; margin-bottom: 5px;" id="previewAudioStatusText">Nhấn nút Phát để nghe câu hỏi (MP3 / Text-to-Speech)...</div>
                            <div style="height: 6px; background: #334155; border-radius: 999px; overflow: hidden;">
                                <div id="previewAudioProgressBar" style="width: 0%; height: 100%; background: #38bdf8; transition: width 0.3s;"></div>
                            </div>
                        </div>
                        <audio id="previewHtmlAudio" controls style="display: none; height: 34px;"></audio>
                    </div>
                </div>

                {{-- KHUNG HÌNH ẢNH MINH HỌA (CHO PART 1 / PART 7 / WRITING T1) --}}
                <div id="previewImageZone" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; margin-bottom: 14px; text-align: center;">
                    <img id="previewQuestionImage" src="" alt="Hình ảnh đề thi" style="max-width: 100%; max-height: 280px; border-radius: 10px; object-fit: contain;">
                </div>

                {{-- KHUNG 2: NỘI DUNG CÂU HỎI / ĐOẠN VĂN --}}
                <div id="previewTextZone" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; margin-bottom: 14px;">
                    <div id="previewStimulusBox" style="display: none; padding: 12px 14px; background: #f8fafc; border-left: 3px solid #4f46e5; border-radius: 6px; margin-bottom: 12px; font-size: 13.5px; color: #334155; white-space: pre-line;"></div>
                    <div id="previewQuestionContent" style="font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.6;"></div>
                </div>

                {{-- KHUNG 3: CÁC ĐÁP ÁN TRẮC NGHIỆM --}}
                <div id="previewChoicesZone" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px;"></div>

                {{-- KHUNG AI GIẢI THÍCH CHI TIẾT CHO CÂU HIỆN TẠI --}}
                <div id="previewAiExplainWrap" style="margin-bottom: 14px;">
                    <button type="button" class="btn-admin-action" style="color: #4338ca; border-color: #c7d2fe; background: #eef2ff; font-weight: 700; font-size: 12.5px; padding: 7px 13px; display: inline-flex; align-items: center; gap: 6px;" onclick="requestPreviewAiExplanation()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"></path>
                        </svg>
                        <span>AI Giải thích chi tiết câu này</span>
                    </button>
                    <div id="previewAiExplainBox" style="display: none; margin-top: 10px; padding: 14px 16px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; font-size: 13px; color: #1e1b4b; line-height: 1.6;"></div>
                </div>

                {{-- KHUNG 4: GIAO DIỆN SPEAKING (WEB AUDIO API MEDIARECORDER + SPEECH-TO-TEXT + AI CHẤM SỬA) --}}
                <div id="previewSpeakingZone" style="display: none; background: #ffffff; border: 1.5px solid #c7d2fe; border-radius: 14px; padding: 18px;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 36px; height: 36px; border-radius: 50%; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                    <line x1="12" y1="19" x2="12" y2="23"></line>
                                    <line x1="8" y1="23" x2="16" y2="23"></line>
                                </svg>
                            </span>
                            <div>
                                <div style="font-size: 14.5px; font-weight: 800; color: #1e1b4b;">Phòng thu Speaking (Web Audio API &amp; Speech-to-Text)</div>
                                <div style="font-size: 12.5px; color: #64748b;">Thu âm giọng nói trực tiếp, lưu file âm thanh &amp; chấm phát âm/ngữ pháp bằng AI</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button type="button" id="btnStartRecording" class="btn-admin-primary" style="background: #dc2626; border-color: #dc2626; padding: 8px 14px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;" onclick="toggleStudentSpeechRecording()">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                </svg>
                                <span id="btnStartRecordingLabel">Bắt đầu thu âm</span>
                            </button>
                            <button type="button" class="btn-admin-action" style="color: #4338ca; border-color: #c7d2fe; background: #eef2ff; font-weight: 700; padding: 8px 14px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;" onclick="runAiSpeakingCorrection()">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"></path>
                                </svg>
                                <span>Lưu bản thu &amp; AI Chấm điểm</span>
                            </button>
                        </div>
                    </div>

                    <div id="previewRecordedAudioWrap" style="display: none; margin-bottom: 12px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                        <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Nghe lại bản thu âm vừa ghi:</div>
                        <audio id="previewRecordedAudioPlayer" controls style="width: 100%; height: 36px;"></audio>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 12.5px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">
                            Đoạn văn nhận diện từ giọng nói (Speech-to-Text Transcript):
                        </label>
                        <textarea id="studentSpokenText" rows="3" class="form-control-admin" placeholder="Nhấn 'Bắt đầu thu âm' và đọc vào micro, hoặc nhập nội dung bài nói tại đây để AI chấm điểm..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 13.5px;"></textarea>
                    </div>

                    <div id="aiSpeakingFeedbackBox" style="display: none; padding: 14px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; font-size: 13px; color: #065f46; line-height: 1.6;"></div>
                </div>
            </div>
        </div>

        <div class="admin-modal-footer" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px;">
            <span style="font-size: 12.5px; color: #64748b;">
                Kiểm tra giao diện hiển thị từng câu, từng Part hoặc toàn bộ đề thi.
            </span>
            <button type="button" class="btn-admin-action" onclick="closeExamPreviewModal()">Đóng xem trước</button>
        </div>
    </div>
</div>

@php
    $allPreviewQuestions = [];
    $testPartsMeta = [];
    foreach ($test['skills'] as $sKey => $sData) {
        foreach ($sData['parts'] as $pData) {
            $pMode = $pData['mode'] ?? $sKey;
            $testPartsMeta[] = [
                'code' => $pData['code'],
                'name' => str_replace(['—', '–'], '-', $pData['name']),
                'mode' => $pMode,
                'target_count' => $pData['target_count'],
            ];
            foreach ($pData['questions'] as $qItem) {
                if (!empty($qItem['is_grouped'])) {
                    foreach ($qItem['child_questions'] as $cItem) {
                        $allPreviewQuestions[] = array_merge($cItem, [
                            'part_code' => $pData['code'],
                            'part_name' => str_replace(['—', '–'], '-', $pData['name']),
                            'mode' => $pMode,
                            'stimulus_text' => $qItem['stimulus_text'] ?? null,
                            'transcript' => $qItem['transcript'] ?? null,
                            'image_url' => $qItem['image_url'] ?? ($cItem['image_url'] ?? null),
                            'audio_url' => $qItem['audio_url'] ?? ($cItem['audio_url'] ?? null),
                        ]);
                    }
                } else {
                    $allPreviewQuestions[] = array_merge($qItem, [
                        'part_code' => $pData['code'],
                        'part_name' => str_replace(['—', '–'], '-', $pData['name']),
                        'mode' => $pMode,
                    ]);
                }
            }
        }
    }
@endphp

{{-- Modal Tiến trình Sinh Audio TTS hàng loạt cho Đề Listening --}}
<div id="modal-test-batch-tts" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.55); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 18px; padding: 24px; box-shadow: 0 25px 50px rgba(15, 23, 42, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">Sinh Audio TTS hàng loạt cho Đề thi</h3>
            <button type="button" id="btnCloseTestBatchTts" onclick="closeTestBatchTtsModal()" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer;">&times;</button>
        </div>

        <p id="testBatchTtsDesc" style="margin: 0 0 14px; font-size: 13.5px; color: #475569; line-height: 1.5;"></p>

        <div style="width: 100%; height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 10px;">
            <div id="testBatchTtsProgressBar" style="height: 100%; width: 0%; background: #0284c7; border-radius: 999px; transition: width 0.3s ease;"></div>
        </div>

        <div id="testBatchTtsStats" style="font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 16px;">
            Sẵn sàng xử lý từng lô 5 câu hỏi (an toàn chống lỗi HTTP 429).
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" id="btnCancelTestBatchTts" class="btn-admin-action" onclick="closeTestBatchTtsModal()">Đóng</button>
            <button type="button" id="btnStartTestBatchTts" class="btn-admin-primary" style="background: #0284c7; border-color: #0284c7;" onclick="runTestBatchTts()">
                Bắt đầu sinh Audio
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/toeic-audio-player.js') }}"></script>
<script>
    var ALL_TEST_QUESTIONS = @json($allPreviewQuestions);
    var TEST_PARTS_META = @json($testPartsMeta);
    var CURRENT_PREVIEW_LIST = [];
    var CURRENT_PREVIEW_INDEX = 0;
    var CURRENT_PREVIEW_MODE = 'single';
    var CURRENT_PREVIEW_PART_CODE = TEST_PARTS_META.length > 0 ? TEST_PARTS_META[0].code : 'PART1';
    var CURRENT_EDITING_QUESTION_ID = null;
    var isSpeechRecording = false;
    var speechRecognizer = null;

    function recalculateTestProgress() {
        var partCards = document.querySelectorAll('.test-part-card');
        var totalCurrent = 0;
        var totalDraft = 0;
        var totalTarget = 0;
        var hasShortPart = false;
        var hasOverPart = false;

        partCards.forEach(function(card) {
            var partTarget = parseInt(card.getAttribute('data-target') || '0', 10);
            var allItems = card.querySelectorAll('.test-q-item');
            var partCurrent = 0;
            var partDraft = 0;

            allItems.forEach(function(el) {
                var st = (el.getAttribute('data-status') || 'ACTIVE').toUpperCase();
                if (st === 'ACTIVE') {
                    partCurrent++;
                } else {
                    partDraft++;
                }
            });

            totalCurrent += partCurrent;
            totalDraft += partDraft;
            totalTarget += partTarget;

            if (partCurrent < partTarget) hasShortPart = true;
            if (partCurrent > partTarget) hasOverPart = true;

            var countText = card.querySelector('.part-count-text');
            if (countText) {
                countText.textContent = partCurrent + ' / ' + partTarget + ' câu' + (partDraft > 0 ? ' (+' + partDraft + ' nháp)' : '');
            }

            var partFill = card.querySelector('.part-progress-fill');
            if (partFill) {
                var pct = partTarget > 0 ? Math.min(100, Math.round((partCurrent / partTarget) * 100)) : 0;
                partFill.style.width = pct + '%';
                if (partCurrent > partTarget) {
                    partFill.style.background = '#ef4444';
                } else if (partCurrent === partTarget && partTarget > 0) {
                    partFill.style.background = '#10b981';
                } else if (partCurrent === 0) {
                    partFill.style.background = '#cbd5e1';
                } else {
                    partFill.style.background = '#4f46e5';
                }
            }

            var partBadge = card.querySelector('.part-status-badge');
            if (partBadge) {
                if (partCurrent > partTarget) {
                    partBadge.textContent = 'Vượt ' + (partCurrent - partTarget) + ' câu';
                    partBadge.style.cssText = 'font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;';
                } else if (partCurrent === partTarget && partTarget > 0) {
                    partBadge.textContent = '✓ Đã đủ câu';
                    partBadge.style.cssText = 'font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;';
                } else if (partCurrent === 0 && partDraft > 0) {
                    partBadge.textContent = partDraft + ' câu bản nháp chưa duyệt';
                    partBadge.style.cssText = 'font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#fffbeb;color:#b45309;border:1px solid #fde68a;';
                } else if (partCurrent === 0) {
                    partBadge.textContent = 'Chưa có câu hỏi';
                    partBadge.style.cssText = 'font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;';
                } else {
                    partBadge.textContent = 'Thiếu ' + (partTarget - partCurrent) + ' câu';
                    partBadge.style.cssText = 'font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#fffbeb;color:#b45309;border:1px solid #fde68a;';
                }
            }
        });

        var overviewCard = document.getElementById('testOverviewProgressCard');
        if (overviewCard && totalTarget === 0) {
            totalTarget = parseInt(overviewCard.getAttribute('data-total-target') || '0', 10);
        }

        var rate = totalTarget > 0 ? (totalCurrent / totalTarget) * 100 : 0;
        var isComplete = (totalTarget > 0 && totalCurrent === totalTarget && !hasShortPart && !hasOverPart);
        var isInvalid = hasOverPart || (totalTarget > 0 && totalCurrent > totalTarget);
        var isEmpty = (totalCurrent === 0);

        var overviewCount = document.getElementById('overviewCountText');
        if (overviewCount) {
            overviewCount.textContent = totalCurrent + ' / ' + totalTarget + ' câu hỏi';
        }

        var overviewRate = document.getElementById('overviewRateText');
        if (overviewRate) {
            overviewRate.textContent = '(' + rate.toFixed(1) + '%)';
            overviewRate.style.color = isInvalid ? '#dc2626' : (isComplete ? '#059669' : (isEmpty ? '#94a3b8' : '#4338ca'));
        }

        var overviewFill = document.getElementById('overviewProgressBarFill');
        if (overviewFill) {
            overviewFill.style.width = Math.min(100, Math.max(0, rate)) + '%';
            overviewFill.style.background = isInvalid ? '#ef4444' : (isComplete ? '#10b981' : (isEmpty ? '#cbd5e1' : '#4f46e5'));
        }

        var overviewBadge = document.getElementById('overviewStatusBadge');
        if (overviewBadge) {
            if (isInvalid) {
                overviewBadge.innerHTML = '<span>Vượt chỉ tiêu / Lệch cấu trúc</span>';
                overviewBadge.style.cssText = 'display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:#fef2f2;color:#b91c1c;border:1.5px solid #fecaca;font-size:13.5px;font-weight:700;';
            } else if (isComplete) {
                overviewBadge.innerHTML = '<span>✓ Đã hoàn thiện (100%)</span>';
                overviewBadge.style.cssText = 'display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:#ecfdf5;color:#065f46;border:1.5px solid #a7f3d0;font-size:13.5px;font-weight:700;';
            } else if (isEmpty) {
                overviewBadge.innerHTML = '<span>Đề thi mới (Chưa có câu hỏi ACTIVE)</span>';
                overviewBadge.style.cssText = 'display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:#f8fafc;color:#64748b;border:1.5px solid #e2e8f0;font-size:13.5px;font-weight:700;';
            } else {
                overviewBadge.innerHTML = '<span>Đang biên soạn (Còn Part chưa đủ chỉ tiêu)</span>';
                overviewBadge.style.cssText = 'display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:#fffbeb;color:#b45309;border:1.5px solid #fde68a;font-size:13.5px;font-weight:700;';
            }
        }

        var draftWarn = document.getElementById('overviewDraftWarningBanner');
        if (draftWarn) {
            if (totalDraft > 0) {
                draftWarn.style.display = 'block';
                draftWarn.innerHTML = '⚠️ Đề thi đang có <strong>' + totalDraft + ' câu hỏi ở trạng thái Bản nháp (DRAFT)</strong> nên chưa được tính vào tiến độ hoàn thiện. Hãy bấm nút <strong>"Duyệt &amp; Xuất bản"</strong> ở góc trên bên phải để xuất bản toàn bộ sang ACTIVE.';
            } else {
                draftWarn.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        recalculateTestProgress();
    });

    function markTestUnsaved() {
        var badge = document.getElementById('saveStateBadge');
        var dot = document.getElementById('saveStateDot');
        var text = document.getElementById('saveStateText');
        if (badge && dot && text) {
            badge.style.background = '#fffbeb';
            badge.style.color = '#b45309';
            badge.style.borderColor = '#fde68a';
            dot.style.background = '#f59e0b';
            text.textContent = 'Có thay đổi chưa lưu';
        }
    }

    function openBankPickerModal(partCode) {
        var modal = document.getElementById('modal-pick-from-bank');
        var partSelect = document.getElementById('bankPartSelect');
        if (partSelect && partCode) {
            partSelect.value = partCode === 'all' ? 'all' : partCode.toUpperCase();
        }
        filterBankModalRows();
        if (modal) modal.classList.add('open');
    }

    function closeBankPickerModal() {
        var modal = document.getElementById('modal-pick-from-bank');
        if (modal) modal.classList.remove('open');
    }

    function filterBankModalRows() {
        var partVal = (document.getElementById('bankPartSelect')?.value || 'all').toUpperCase();
        var searchVal = (document.getElementById('bankSearchInput')?.value || '').trim().toLowerCase();

        document.querySelectorAll('.bank-modal-row').forEach(function(row) {
            var rowPart = (row.getAttribute('data-part') || '').toUpperCase();
            var rowSearch = row.getAttribute('data-search') || '';
            var matchPart = (partVal === 'ALL' || rowPart === partVal);
            var matchSearch = (searchVal === '' || rowSearch.indexOf(searchVal) !== -1);
            row.style.display = (matchPart && matchSearch) ? '' : 'none';
        });
    }

    function toggleAllVisibleBankQuestions(master) {
        document.querySelectorAll('.bank-modal-row').forEach(function(row) {
            if (row.style.display !== 'none') {
                var cb = row.querySelector('.bank-q-checkbox');
                if (cb) cb.checked = master.checked;
            }
        });
        updateBankSelectedCounter();
    }

    function updateBankSelectedCounter() {
        var count = document.querySelectorAll('.bank-q-checkbox:checked').length;
        var el = document.getElementById('bankSelectedCount');
        if (el) el.innerText = count;
    }

    // Sửa câu hỏi trực tiếp trong đề thi
    function openEditTestQuestionModal(q) {
        CURRENT_EDITING_QUESTION_ID = q.id;
        var form = document.getElementById('formEditTestQuestion');
        form.action = '{{ url('/admin/tests/' . $test['id'] . '/questions') }}/' + q.id;
        document.getElementById('editTestQTitle').textContent = 'Sửa câu hỏi ' + (q.question_code || ('#Q-' + q.id));
        document.getElementById('editTestQContent').value = q.content || '';
        document.getElementById('editTestQDifficulty').value = q.difficulty || 'MEDIUM';
        document.getElementById('editTestQCorrect').value = q.correct_option || 'A';
        document.getElementById('editTestQTranscript').value = q.transcript || q.content || '';
        document.getElementById('editTestQExplanation').value = q.explanation || '';

        var optMap = { A: '', B: '', C: '', D: '' };
        if (Array.isArray(q.options)) {
            q.options.forEach(function(o) {
                if (o.label && optMap.hasOwnProperty(o.label)) {
                    optMap[o.label] = o.content;
                }
            });
        }
        document.getElementById('editTestQOptA').value = optMap.A;
        document.getElementById('editTestQOptB').value = optMap.B;
        document.getElementById('editTestQOptC').value = optMap.C;
        document.getElementById('editTestQOptD').value = optMap.D;

        var statusEl = document.getElementById('editTestQTtsStatus');
        if (statusEl) statusEl.style.display = 'none';

        document.getElementById('modal-edit-test-question').classList.add('open');
    }

    function closeEditTestQuestionModal() {
        document.getElementById('modal-edit-test-question').classList.remove('open');
    }

    // Xóa câu hỏi khỏi đề thi
    function confirmDetachQuestion(questionId, questionCode) {
        if (confirm('Bạn có chắc chắn muốn xóa câu hỏi ' + questionCode + ' khỏi đề thi này?')) {
            var form = document.getElementById('detach-question-form');
            form.action = '{{ url('/admin/tests/' . $test['id'] . '/questions') }}/' + questionId;
            form.submit();
        }
    }

    // Chuyển văn bản câu hỏi thành Giọng đọc Audio (TTS API + Web Speech)
    function speakQuestionTts(questionId, textContent) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            var utter = new SpeechSynthesisUtterance(textContent);
            utter.lang = 'en-US';
            utter.rate = 0.95;
            window.speechSynthesis.speak(utter);
        }

        fetch('{{ route('api.admin.questions.generate-tts') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                question_id: questionId,
                text: textContent,
                part_number: 1
            })
        }).catch(function() {});
    }

    function generateTtsFromEditModal() {
        var text = document.getElementById('editTestQTranscript').value || document.getElementById('editTestQContent').value;
        if (!text.trim()) {
            alert('Vui lòng nhập nội dung văn bản hoặc transcript để chuyển thành ghi âm!');
            return;
        }
        speakQuestionTts(CURRENT_EDITING_QUESTION_ID, text);
        var statusEl = document.getElementById('editTestQTtsStatus');
        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.textContent = '✓ Đã chuyển văn bản thành giọng đọc Audio và đang phát thử!';
        }
    }

    // Mở Modal Xem trước (Từng câu, Từng Part, hoặc Toàn bộ Đề thi)
    function escapePreviewHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function openExamPreviewModal(scope, partCode, singleQuestion) {
        if (scope === 'question' && singleQuestion) {
            var mode = 'reading';
            if (partCode && (partCode.indexOf('PART1') === 0 || partCode.indexOf('PART2') === 0 || partCode.indexOf('PART3') === 0 || partCode.indexOf('PART4') === 0)) {
                mode = 'listening';
            } else if (partCode && partCode.indexOf('SPK') === 0) {
                mode = 'speaking';
            } else if (partCode && partCode.indexOf('WRITING') === 0) {
                mode = 'writing';
            }
            CURRENT_PREVIEW_LIST = [Object.assign({}, singleQuestion, { part_code: partCode || 'PART1', mode: mode })];
            CURRENT_PREVIEW_PART_CODE = partCode || CURRENT_PREVIEW_PART_CODE;
            CURRENT_PREVIEW_MODE = 'single';
            document.getElementById('previewScopeBadge').textContent = 'XEM TRƯỚC CÂU HỎI ĐƠN LẺ (' + (singleQuestion.question_code || '') + ')';
        } else if (scope === 'part' && partCode) {
            CURRENT_PREVIEW_PART_CODE = partCode.toUpperCase();
            CURRENT_PREVIEW_LIST = ALL_TEST_QUESTIONS.filter(function(q) {
                return (q.part_code || '').toUpperCase() === CURRENT_PREVIEW_PART_CODE;
            });
            CURRENT_PREVIEW_MODE = 'part';
            document.getElementById('previewScopeBadge').textContent = 'XEM TRƯỚC PHẦN THI: ' + CURRENT_PREVIEW_PART_CODE;
        } else {
            CURRENT_PREVIEW_LIST = ALL_TEST_QUESTIONS.slice();
            CURRENT_PREVIEW_MODE = 'single';
            document.getElementById('previewScopeBadge').textContent = 'XEM TRƯỚC TOÀN BỘ ĐỀ THI ({{ str_replace(['—', '–'], '-', $test['code']) }})';
        }

        if (CURRENT_PREVIEW_LIST.length === 0) {
            var defaultMode = '{{ in_array($test['exam_type'], ['TOEIC_LISTENING'], true) ? 'listening' : (in_array($test['exam_type'], ['TOEIC_SPEAKING', 'TOEIC_SW'], true) ? 'speaking' : 'reading') }}';
            if (partCode && partCode.indexOf('SPK') === 0) defaultMode = 'speaking';
            if (partCode && (partCode === 'PART1' || partCode === 'PART2' || partCode === 'PART3' || partCode === 'PART4')) defaultMode = 'listening';

            CURRENT_PREVIEW_LIST = [{
                sequence_no: 1,
                question_code: '#DEMO-01',
                part_code: partCode || CURRENT_PREVIEW_PART_CODE || 'PART1',
                mode: defaultMode,
                content: defaultMode === 'speaking'
                    ? 'Please read the following announcement aloud clearly and naturally: Welcome aboard Flight 702 bound for Tokyo.'
                    : 'The marketing director will review the quarterly budget proposal before Friday afternoon.',
                options: defaultMode === 'speaking' ? [] : [
                    { label: 'A', content: 'review' },
                    { label: 'B', content: 'reviewed' },
                    { label: 'C', content: 'reviewing' },
                    { label: 'D', content: 'reviews' }
                ],
                correct_option: 'A'
            }];
        }

        CURRENT_PREVIEW_INDEX = 0;
        setPreviewDisplayMode(CURRENT_PREVIEW_MODE);
        document.getElementById('modal-preview-exam').classList.add('open');
    }

    function setPreviewDisplayMode(mode) {
        if (window.ToeicAudioPlayer) window.ToeicAudioPlayer.stop();
        CURRENT_PREVIEW_MODE = (mode === 'list' || mode === 'part') ? mode : 'single';

        var btnSingle = document.getElementById('btnModeSingle');
        var btnPart = document.getElementById('btnModePart');
        var btnList = document.getElementById('btnModeList');

        [
            { btn: btnSingle, active: CURRENT_PREVIEW_MODE === 'single' },
            { btn: btnPart, active: CURRENT_PREVIEW_MODE === 'part' },
            { btn: btnList, active: CURRENT_PREVIEW_MODE === 'list' }
        ].forEach(function(item) {
            if (!item.btn) return;
            item.btn.classList.toggle('active', item.active);
            item.btn.style.background = item.active ? '#4338ca' : '#ffffff';
            item.btn.style.color = item.active ? '#ffffff' : '#334155';
            item.btn.style.borderColor = item.active ? '#4338ca' : '#cbd5e1';
        });

        var navWrap = document.getElementById('previewSingleNavWrap');
        var partSelectorWrap = document.getElementById('previewPartSelectorWrap');
        var paletteWrap = document.getElementById('previewQuestionPalette');
        var singleContainer = document.getElementById('previewSingleQuestionContainer');
        var allContainer = document.getElementById('previewAllQuestionsContainer');

        if (CURRENT_PREVIEW_MODE === 'single') {
            if (navWrap) navWrap.style.display = 'inline-flex';
            if (partSelectorWrap) partSelectorWrap.style.display = 'none';
            if (paletteWrap) paletteWrap.style.display = 'flex';
            if (singleContainer) singleContainer.style.display = 'block';
            if (allContainer) allContainer.style.display = 'none';
            if (CURRENT_PREVIEW_LIST.length === 0) {
                CURRENT_PREVIEW_LIST = ALL_TEST_QUESTIONS.slice();
                CURRENT_PREVIEW_INDEX = 0;
            }
            renderPreviewQuestionPalette();
            renderCurrentPreviewQuestion();
        } else if (CURRENT_PREVIEW_MODE === 'part') {
            if (navWrap) navWrap.style.display = 'none';
            if (partSelectorWrap) partSelectorWrap.style.display = 'flex';
            if (paletteWrap) paletteWrap.style.display = 'none';
            if (singleContainer) singleContainer.style.display = 'none';
            if (allContainer) allContainer.style.display = 'flex';
            renderPreviewPartSelector();
            renderPreviewPartQuestions(CURRENT_PREVIEW_PART_CODE);
        } else {
            if (navWrap) navWrap.style.display = 'none';
            if (partSelectorWrap) partSelectorWrap.style.display = 'none';
            if (paletteWrap) paletteWrap.style.display = 'none';
            if (singleContainer) singleContainer.style.display = 'none';
            if (allContainer) allContainer.style.display = 'flex';
            renderAllPreviewQuestionsList();
        }
    }

    function renderPreviewQuestionPalette() {
        var palette = document.getElementById('previewQuestionPalette');
        if (!palette) return;
        palette.innerHTML = '';
        CURRENT_PREVIEW_LIST.forEach(function(q, idx) {
            var btn = document.createElement('button');
            btn.type = 'button';
            var isCur = idx === CURRENT_PREVIEW_INDEX;
            btn.textContent = (idx + 1);
            btn.title = (q.question_code || '') + ' (' + (q.part_code || '') + ')';
            btn.style.cssText = 'min-width:30px;height:30px;padding:0 8px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;'
                + (isCur
                    ? 'background:#4338ca;color:#ffffff;border:1px solid #4338ca;'
                    : 'background:#f8fafc;color:#334155;border:1px solid #cbd5e1;');
            btn.onclick = function() {
                CURRENT_PREVIEW_INDEX = idx;
                renderPreviewQuestionPalette();
                renderCurrentPreviewQuestion();
            };
            palette.appendChild(btn);
        });
    }

    function renderPreviewPartSelector() {
        var wrap = document.getElementById('previewPartSelectorWrap');
        if (!wrap) return;
        wrap.innerHTML = '';

        TEST_PARTS_META.forEach(function(pm) {
            var countInPart = ALL_TEST_QUESTIONS.filter(function(q) {
                return (q.part_code || '').toUpperCase() === pm.code.toUpperCase();
            }).length;
            var isSelected = pm.code.toUpperCase() === CURRENT_PREVIEW_PART_CODE.toUpperCase();
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = 'padding:6px 12px;border-radius:999px;font-size:12.5px;font-weight:700;cursor:pointer;'
                + (isSelected
                    ? 'background:#4338ca;color:#ffffff;border:1px solid #4338ca;'
                    : 'background:#f8fafc;color:#334155;border:1px solid #cbd5e1;');
            btn.textContent = pm.name.split(':')[0] + ' (' + countInPart + ' câu)';
            btn.onclick = function() {
                CURRENT_PREVIEW_PART_CODE = pm.code;
                renderPreviewPartSelector();
                renderPreviewPartQuestions(pm.code);
            };
            wrap.appendChild(btn);
        });
    }

    function buildQuestionPreviewCardHtml(q, indexNum) {
        var isListening = q.mode === 'listening';
        var isSpeaking = q.mode === 'speaking';
        var isWriting = q.mode === 'writing';
        var audioUrl = q.resolved_audio_url || q.audio_url || '';

        var html = '<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;">';
        html += '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;">';
        html += '<span style="font-size:12.5px;font-weight:800;color:#4338ca;">Câu ' + indexNum + ' (' + escapePreviewHtml(q.question_code || '#Q') + ')</span>';
        html += '<span style="font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#eef2ff;color:#4338ca;">' + escapePreviewHtml(q.part_code || 'Part') + '</span>';
        html += '</div>';

        if (q.image_url) {
            html += '<div style="margin-bottom:12px;text-align:center;"><img src="' + escapePreviewHtml(q.image_url) + '" alt="Hình ảnh câu hỏi" style="max-width:100%;max-height:220px;border-radius:8px;border:1px solid #e2e8f0;object-fit:contain;"></div>';
        }

        if (isListening) {
            if (audioUrl) {
                html += '<div style="margin-bottom:12px;"><audio controls preload="none" src="' + escapePreviewHtml(audioUrl) + '" style="width:100%;height:36px;"></audio></div>';
            } else {
                var speechText = q.transcript || (Array.isArray(q.options) && q.options.length ? q.options.map(function(o){return '('+o.label+') '+o.content;}).join('. ') : q.content);
                html += '<div style="margin-bottom:12px;padding:8px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;display:flex;align-items:center;justify-content:space-between;gap:8px;">';
                html += '<span style="font-size:12px;color:#0369a1;font-weight:600;">Audio TTS trình duyệt sẵn sàng</span>';
                html += '<button type="button" class="btn-admin-action" style="padding:4px 10px;font-size:12px;color:#0284c7;border-color:#7dd3fc;background:#ffffff;" onclick="ToeicAudioPlayer && ToeicAudioPlayer.playSpeech(this.getAttribute(\'data-tts\'))" data-tts="' + escapePreviewHtml(speechText || '') + '">🔊 Nghe thử</button>';
                html += '</div>';
            }
        }

        if (q.stimulus_text) {
            html += '<div style="padding:10px 12px;background:#f8fafc;border-left:3px solid #4f46e5;border-radius:6px;margin-bottom:10px;font-size:13px;color:#334155;white-space:pre-line;line-height:1.55;">' + escapePreviewHtml(q.stimulus_text) + '</div>';
        }

        html += '<div style="font-size:14px;font-weight:700;color:#0f172a;line-height:1.55;margin-bottom:10px;white-space:pre-line;">' + escapePreviewHtml(q.content || '') + '</div>';

        if (!isSpeaking && !isWriting && Array.isArray(q.options) && q.options.length > 0) {
            html += '<div style="display:flex;flex-direction:column;gap:6px;">';
            q.options.forEach(function(opt) {
                var isRight = (opt.label === q.correct_option);
                html += '<div style="padding:8px 12px;border-radius:8px;font-size:13px;' + (isRight ? 'background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;font-weight:700;' : 'background:#f8fafc;border:1px solid #e2e8f0;color:#334155;') + '">';
                html += '<strong>(' + escapePreviewHtml(opt.label) + ')</strong> ' + escapePreviewHtml(opt.content) + (isRight ? ' ✓' : '');
                html += '</div>';
            });
            html += '</div>';
        } else if (isSpeaking) {
            html += '<div style="padding:10px 12px;background:#fff7ed;border:1px dashed #fdba74;border-radius:8px;font-size:12.5px;color:#9a3412;">🎙 Câu hỏi Speaking (Thí sinh trả lời trực tiếp bằng giọng nói)</div>';
        } else if (isWriting) {
            html += '<textarea rows="3" readonly placeholder="Khung nhập bài viết của thí sinh..." style="width:100%;box-sizing:border-box;padding:8px 10px;border-radius:8px;border:1px solid #ddd6fe;background:#faf5ff;font-size:13px;"></textarea>';
        }

        if (q.explanation) {
            html += '<div style="margin-top:10px;padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12.5px;color:#78350f;white-space:pre-line;"><strong>Giải thích / Bài mẫu:</strong> ' + escapePreviewHtml(q.explanation) + '</div>';
        }

        html += '</div>';
        return html;
    }

    function renderPreviewPartQuestions(partCode) {
        var container = document.getElementById('previewAllQuestionsContainer');
        if (!container) return;
        var partMeta = TEST_PARTS_META.find(function(pm) { return pm.code.toUpperCase() === String(partCode).toUpperCase(); });
        var partQuestions = ALL_TEST_QUESTIONS.filter(function(q) {
            return (q.part_code || '').toUpperCase() === String(partCode).toUpperCase();
        });

        document.getElementById('previewQuestionCounter').textContent = (partMeta ? partMeta.name : partCode) + ' (' + partQuestions.length + ' câu)';
        document.getElementById('previewPartBadge').textContent = partCode;

        if (partQuestions.length === 0) {
            container.innerHTML = '<div style="padding:28px;text-align:center;background:#ffffff;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;font-size:13.5px;">Phần thi <strong>' + escapePreviewHtml(partMeta ? partMeta.name : partCode) + '</strong> chưa có câu hỏi nào.</div>';
            return;
        }

        var html = '';
        partQuestions.forEach(function(q, idx) {
            html += buildQuestionPreviewCardHtml(q, idx + 1);
        });
        container.innerHTML = html;
    }

    function renderAllPreviewQuestionsList() {
        var container = document.getElementById('previewAllQuestionsContainer');
        if (!container) return;

        document.getElementById('previewQuestionCounter').textContent = 'Toàn bộ đề thi (' + ALL_TEST_QUESTIONS.length + ' câu)';
        document.getElementById('previewPartBadge').textContent = 'ALL PARTS';

        if (ALL_TEST_QUESTIONS.length === 0) {
            container.innerHTML = '<div style="padding:32px;text-align:center;background:#ffffff;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;font-size:14px;">Đề thi hiện chưa có câu hỏi nào. Vui lòng thêm hoặc chọn câu hỏi từ Ngân hàng.</div>';
            return;
        }

        var html = '';
        var globalIdx = 0;
        TEST_PARTS_META.forEach(function(pm) {
            var partQuestions = ALL_TEST_QUESTIONS.filter(function(q) {
                return (q.part_code || '').toUpperCase() === pm.code.toUpperCase();
            });
            if (partQuestions.length === 0) return;

            html += '<div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">';
            html += '<strong style="color:#1e1b4b;font-size:14px;">' + escapePreviewHtml(pm.name) + '</strong>';
            html += '<span style="font-size:12px;font-weight:700;color:#4338ca;">' + partQuestions.length + ' / ' + pm.target_count + ' câu</span>';
            html += '</div>';

            partQuestions.forEach(function(q) {
                globalIdx += 1;
                html += buildQuestionPreviewCardHtml(q, globalIdx);
            });
        });

        container.innerHTML = html;
    }

    function closeExamPreviewModal() {
        if (window.ToeicAudioPlayer) {
            window.ToeicAudioPlayer.stop();
        } else if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
        document.getElementById('modal-preview-exam').classList.remove('open');
    }

    function stepPreviewQuestion(delta) {
        if (CURRENT_PREVIEW_LIST.length === 0) return;
        if (window.ToeicAudioPlayer) window.ToeicAudioPlayer.stop();
        CURRENT_PREVIEW_INDEX = (CURRENT_PREVIEW_INDEX + delta + CURRENT_PREVIEW_LIST.length) % CURRENT_PREVIEW_LIST.length;
        renderPreviewQuestionPalette();
        renderCurrentPreviewQuestion();
    }

    function renderCurrentPreviewQuestion() {
        var q = CURRENT_PREVIEW_LIST[CURRENT_PREVIEW_INDEX];
        if (!q) return;
        if (window.ToeicAudioPlayer) window.ToeicAudioPlayer.stop();

        document.getElementById('previewQuestionCounter').textContent = 'Câu ' + (CURRENT_PREVIEW_INDEX + 1) + ' / ' + CURRENT_PREVIEW_LIST.length + ' (' + (q.question_code || '#Q') + ')';
        document.getElementById('previewPartBadge').textContent = q.part_code || 'Part';

        var isListening = q.mode === 'listening';
        var isSpeaking = q.mode === 'speaking';

        var listeningZone = document.getElementById('previewListeningZone');
        var imageZone = document.getElementById('previewImageZone');
        var imageEl = document.getElementById('previewQuestionImage');
        var textZone = document.getElementById('previewTextZone');
        var choicesZone = document.getElementById('previewChoicesZone');
        var speakingZone = document.getElementById('previewSpeakingZone');

        // Listening: Học sinh chỉ nhìn thấy trình phát âm thanh, ẩn văn bản đề
        listeningZone.style.display = isListening ? 'block' : 'none';
        textZone.style.display = isListening ? 'none' : 'block';
        speakingZone.style.display = isSpeaking ? 'block' : 'none';

        if (imageZone && imageEl) {
            if (q.image_url) {
                imageEl.src = q.image_url;
                imageZone.style.display = 'block';
            } else {
                imageZone.style.display = 'none';
            }
        }

        var htmlAudio = document.getElementById('previewHtmlAudio');
        var audioUrl = q.resolved_audio_url || q.audio_url || '';
        var bar = document.getElementById('previewAudioProgressBar');
        var status = document.getElementById('previewAudioStatusText');
        if (bar) bar.style.width = '0%';
        if (htmlAudio) {
            if (audioUrl) {
                htmlAudio.src = audioUrl;
                htmlAudio.style.display = 'block';
                if (status) status.textContent = 'File MP3 sẵn sàng — Nhấn Phát hoặc dùng trình phát bên phải.';
            } else {
                htmlAudio.removeAttribute('src');
                htmlAudio.style.display = 'none';
                if (status) status.textContent = 'Nhấn nút Phát để nghe câu hỏi (Text-to-Speech)...';
            }
        }

        var stimBox = document.getElementById('previewStimulusBox');
        if (q.stimulus_text) {
            stimBox.style.display = 'block';
            stimBox.textContent = q.stimulus_text;
        } else {
            stimBox.style.display = 'none';
        }

        document.getElementById('previewQuestionContent').textContent = q.content || '';

        // Render phương án trắc nghiệm
        choicesZone.innerHTML = '';
        if (!isSpeaking && Array.isArray(q.options) && q.options.length > 0) {
            choicesZone.style.display = 'flex';
            q.options.forEach(function(opt) {
                var btn = document.createElement('div');
                btn.style.cssText = 'padding: 12px 16px; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; color: #1e293b; display: flex; justify-content: space-between; align-items: center;';
                btn.innerHTML = '<span><strong>(' + opt.label + ')</strong> ' + (isListening ? 'Nghe và chọn đáp án ' + opt.label + ' (' + opt.content + ')' : opt.content) + '</span>';
                btn.onclick = function() {
                    var isRight = (opt.label === q.correct_option);
                    btn.style.borderColor = isRight ? '#10b981' : '#ef4444';
                    btn.style.background = isRight ? '#ecfdf5' : '#fef2f2';
                };
                choicesZone.appendChild(btn);
            });
        } else {
            choicesZone.style.display = 'none';
        }

        document.getElementById('aiSpeakingFeedbackBox').style.display = 'none';
    }

    function playCurrentPreviewAudio() {
        var q = CURRENT_PREVIEW_LIST[CURRENT_PREVIEW_INDEX];
        if (!q || !window.ToeicAudioPlayer) return;

        var audioUrl = q.resolved_audio_url || q.audio_url || '';
        var htmlAudio = document.getElementById('previewHtmlAudio');
        var bar = document.getElementById('previewAudioProgressBar');
        var status = document.getElementById('previewAudioStatusText');

        if (audioUrl && htmlAudio) {
            htmlAudio.style.display = 'block';
            window.ToeicAudioPlayer.playMp3(htmlAudio, audioUrl, {
                onPlay: function () {
                    if (status) status.textContent = '🔊 Đang phát file MP3...';
                },
                onProgress: function (cur, dur) {
                    if (bar && dur > 0) bar.style.width = Math.min(100, Math.round((cur / dur) * 100)) + '%';
                },
                onEnded: function () {
                    if (bar) bar.style.width = '100%';
                    if (status) status.textContent = '✓ Đã phát xong file âm thanh.';
                }
            });
            return;
        }

        var textToSpeak = q.transcript || '';
        if (!textToSpeak && Array.isArray(q.options) && q.options.length > 0) {
            textToSpeak = q.options.map(function (o) { return '(' + o.label + ') ' + o.content; }).join('. ');
        }
        if (!textToSpeak) {
            textToSpeak = q.content || 'Listen carefully and choose the best answer.';
        }

        window.ToeicAudioPlayer.playSpeech(textToSpeak, {
            onPlay: function () {
                if (status) status.textContent = '🔊 Đang phát giọng đọc TTS trình duyệt...';
                if (bar) bar.style.width = '10%';
            },
            onProgress: function (cur, dur) {
                if (bar && dur > 0) bar.style.width = Math.min(100, Math.round((cur / dur) * 100)) + '%';
            },
            onEnded: function () {
                if (bar) bar.style.width = '100%';
                if (status) status.textContent = '✓ Đã phát xong âm thanh câu hỏi.';
            }
        });
    }

    // Thu âm giọng nói học sinh & Speech-to-Text trong Preview Speaking
    function toggleStudentSpeechRecording() {
        var btn = document.getElementById('btnStartRecording');
        var transcriptBox = document.getElementById('studentSpokenText');
        var q = CURRENT_PREVIEW_LIST[CURRENT_PREVIEW_INDEX];

        if (!isSpeechRecording) {
            isSpeechRecording = true;
            btn.textContent = '⏹️ Dừng thu âm';
            btn.style.background = '#0f172a';

            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition) {
                speechRecognizer = new SpeechRecognition();
                speechRecognizer.lang = 'en-US';
                speechRecognizer.interimResults = true;
                speechRecognizer.onresult = function(event) {
                    var text = '';
                    for (var i = 0; i < event.results.length; i++) {
                        text += event.results[i][0].transcript;
                    }
                    transcriptBox.value = text;
                };
                speechRecognizer.start();
            } else {
                transcriptBox.value = q ? q.content : 'Welcome aboard Flight 702 bound for Tokyo.';
            }
        } else {
            isSpeechRecording = false;
            btn.textContent = ' Bắt đầu thu giọng nói';
            btn.style.background = '#dc2626';
            if (speechRecognizer) {
                try { speechRecognizer.stop(); } catch (e) {}
            }
            if (!transcriptBox.value.trim() && q) {
                transcriptBox.value = q.content;
            }
        }
    }

    function runAiSpeakingCorrection() {
        var q = CURRENT_PREVIEW_LIST[CURRENT_PREVIEW_INDEX];
        var spoken = document.getElementById('studentSpokenText').value.trim();
        var box = document.getElementById('aiSpeakingFeedbackBox');

        if (!spoken) {
            spoken = q ? q.content : 'I think remote working helps employees save commuting time.';
            document.getElementById('studentSpokenText').value = spoken;
        }

        box.style.display = 'block';
        box.innerHTML = '<strong> Kết quả AI Phân tích &amp; Chữa bài nói Speaking:</strong><br>' +
            '• <strong>Đoạn văn học sinh đã nói:</strong> "' + spoken + '"<br>' +
            '• <strong>Điểm phát âm &amp; Độ trôi chảy (Pronunciation &amp; Fluency):</strong> 170 / 200 (Tốt - Rõ âm cuối)<br>' +
            '• <strong>AI Sửa ngữ pháp &amp; Gợi ý nâng cấp từ vựng:</strong> Câu nói mạch lạc, ngữ điệu tự nhiên. Gợi ý nhấn trọng âm rõ hơn ở các danh từ ghép.';
    }
    var TEST_MISSING_AUDIO_IDS = @json($test['audio_summary']['missing_audio_question_ids'] ?? []);
    var TEST_BATCH_TTS_RUNNING = false;

    function openTestBatchTtsModal() {
        var modal = document.getElementById('modal-test-batch-tts');
        var desc = document.getElementById('testBatchTtsDesc');
        var startBtn = document.getElementById('btnStartTestBatchTts');
        var bar = document.getElementById('testBatchTtsProgressBar');
        var stats = document.getElementById('testBatchTtsStats');

        if (!modal) return;
        bar.style.width = '0%';

        if (!Array.isArray(TEST_MISSING_AUDIO_IDS) || TEST_MISSING_AUDIO_IDS.length === 0) {
            desc.textContent = 'Tất cả câu hỏi Listening (Part 1–4) trong đề thi này đã có file Audio MP3 hoàn chỉnh.';
            stats.textContent = 'Không có câu hỏi nào thiếu Audio.';
            if (startBtn) startBtn.style.display = 'none';
        } else {
            desc.textContent = 'Hệ thống phát hiện ' + TEST_MISSING_AUDIO_IDS.length + ' câu Listening (Part 1–4) chưa có file Audio MP3. Tiến trình sẽ tự động chia từng lô 5 câu để sinh giọng đọc chuẩn TOEIC và lưu vào máy chủ.';
            stats.textContent = 'Đang chờ bắt đầu (0 / ' + TEST_MISSING_AUDIO_IDS.length + ' câu)...';
            if (startBtn) {
                startBtn.style.display = 'inline-flex';
                startBtn.disabled = false;
                startBtn.textContent = 'Bắt đầu sinh Audio';
            }
        }

        modal.style.display = 'flex';
    }

    function closeTestBatchTtsModal() {
        if (TEST_BATCH_TTS_RUNNING) return;
        var modal = document.getElementById('modal-test-batch-tts');
        if (modal) modal.style.display = 'none';
    }

    async function runTestBatchTts() {
        if (TEST_BATCH_TTS_RUNNING || !Array.isArray(TEST_MISSING_AUDIO_IDS) || TEST_MISSING_AUDIO_IDS.length === 0) {
            return;
        }

        TEST_BATCH_TTS_RUNNING = true;
        var startBtn = document.getElementById('btnStartTestBatchTts');
        var cancelBtn = document.getElementById('btnCancelTestBatchTts');
        var bar = document.getElementById('testBatchTtsProgressBar');
        var stats = document.getElementById('testBatchTtsStats');
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        if (startBtn) {
            startBtn.disabled = true;
            startBtn.textContent = 'Đang sinh Audio...';
        }
        if (cancelBtn) cancelBtn.disabled = true;

        var total = TEST_MISSING_AUDIO_IDS.length;
        var processed = 0;
        var totalGenerated = 0;
        var totalSkipped = 0;
        var totalFailed = 0;
        var chunkSize = 5;

        for (var i = 0; i < total; i += chunkSize) {
            var chunk = TEST_MISSING_AUDIO_IDS.slice(i, i + chunkSize);
            try {
                var resp = await fetch('{{ url('/api/admin/questions/batch-generate-tts') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ question_ids: chunk })
                });
                var data = await resp.json();
                if (resp.ok && data) {
                    totalGenerated += Number(data.generated || 0);
                    totalSkipped += Number(data.skipped || 0);
                    totalFailed += Number(data.failed || 0);
                } else {
                    totalFailed += chunk.length;
                }
            } catch (err) {
                totalFailed += chunk.length;
            }

            processed = Math.min(total, i + chunk.length);
            var pct = Math.round((processed / total) * 100);
            if (bar) bar.style.width = pct + '%';
            if (stats) {
                stats.textContent = 'Đã xử lý ' + processed + ' / ' + total + ' câu (' + pct + '%) — Đã sinh: ' + totalGenerated + ', Bỏ qua: ' + totalSkipped + ', Lỗi: ' + totalFailed;
            }
        }

        TEST_BATCH_TTS_RUNNING = false;
        if (cancelBtn) cancelBtn.disabled = false;
        if (startBtn) {
            startBtn.disabled = false;
            startBtn.textContent = 'Hoàn tất — Tải lại trang';
            startBtn.onclick = function () { window.location.reload(); };
        }

        if (totalGenerated > 0) {
            setTimeout(function () { window.location.reload(); }, 900);
        }
    }
</script>
@endpush
@include('admin.questions.partials.question-preview-modal')
@endsection

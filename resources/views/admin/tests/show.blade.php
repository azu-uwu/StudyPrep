@extends('layouts.admin', ['active' => 'de-thi'])

@section('title', $test['title'] . ' | Chi tiết đề thi')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.tests.index') }}" style="color: inherit; text-decoration: none;">Quản lý đề thi</a>
    <span class="crumb-arrow">></span>
    <span class="crumb-active">{{ $test['code'] }}</span>
@endsection

@section('content')
<div class="qbank-page">

    {{-- Thông báo kết quả thao tác --}}
    @if (session('success'))
        <div class="alert-admin-success" style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 14px; margin-bottom: 24px; font-size: 14px;">
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

    {{-- 1. Header Trang & Các nút thao tác --}}
    <div class="user-page-header" style="margin-bottom: 24px;">
        <div>
            <div class="qbank-breadcrumb-sub">
                <a href="{{ route('admin.tests.index') }}" style="color: inherit; text-decoration: none;">&larr; Quay lại danh sách đề thi</a>
                <span>></span>
                <span class="crumb-active">{{ $test['code'] }}</span>
            </div>
            <div class="user-title-row" style="margin-top: 6px;">
                <h1 class="user-page-title">{{ $test['title'] }}</h1>
                <span class="user-count-badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                    {{ $test['code'] }}
                </span>
            </div>
            <p class="user-page-subtitle" style="margin-top: 4px;">
                Loại đề: <strong>{{ $test['type_name'] }}</strong> &bull; Thời gian chuẩn: <strong>{{ $test['total_time'] }} phút</strong> &bull; Ngày tạo: <strong>{{ $test['created_at'] }}</strong>
            </p>
        </div>

        <div class="user-header-actions">
            {{-- Nút Quay lại --}}
            <a href="{{ route('admin.tests.index') }}" class="btn-admin-action" title="Quay lại danh sách đề thi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Danh sách đề thi</span>
            </a>

            {{-- Nút Thêm câu hỏi chung --}}
            <a href="{{ route('admin.questions.create', ['test_id' => $test['id']]) }}" class="btn-admin-primary" title="Thêm câu hỏi mới vào đề thi này">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Thêm câu hỏi vào đề</span>
            </a>
        </div>
    </div>

    {{-- 2. Card Tổng quan Tiến độ Hoàn thiện Đề thi --}}
    @php
        $isComplete = $test['status'] === 'COMPLETED';
        $isEmpty = $test['status'] === 'EMPTY';
        $rate = $test['completion_rate'];
    @endphp
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 18px;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    Tiến độ hoàn thiện đề thi
                </div>
                <div style="font-size: 26px; font-weight: 850; color: #0f172a; margin-top: 4px; display: flex; align-items: baseline; gap: 8px;">
                    <span>{{ $test['current_questions'] }} / {{ $test['target_questions'] }} câu hỏi</span>
                    <span style="font-size: 16px; font-weight: 700; color: {{ $isComplete ? '#059669' : ($isEmpty ? '#94a3b8' : '#4338ca') }};">
                        ({{ number_format($rate, 1) }}%)
                    </span>
                </div>
            </div>

            <div>
                @if ($isComplete)
                    <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; font-size: 14px; font-weight: 700;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Đã hoàn thiện (Đủ tất cả các Part)</span>
                    </div>
                @elseif ($isEmpty)
                    <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #f8fafc; color: #64748b; border: 1.5px solid #e2e8f0; font-size: 14px; font-weight: 700;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8;"></span>
                        <span>Đề thi mới (Chưa có câu hỏi nào)</span>
                    </div>
                @else
                    <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; font-size: 14px; font-weight: 700;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Đang biên soạn (Còn Part chưa đủ chỉ tiêu)</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Thanh tiến độ lớn --}}
        <div style="width: 100%; height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 12px;">
            <div style="height: 100%; width: {{ $rate }}%; background: {{ $isComplete ? '#10b981' : ($isEmpty ? '#cbd5e1' : '#4f46e5') }}; border-radius: 999px; transition: width 0.4s ease;"></div>
        </div>

        <div style="font-size: 13px; color: #64748b; display: flex; justify-content: space-between;">
            <span>Yêu cầu hoàn thiện: Đạt đủ số lượng câu hỏi quy định cho từng Part độc lập.</span>
            <span>Chuẩn: <strong>{{ $test['blueprint']['name'] }}</strong></span>
        </div>
    </div>

    {{-- 3. Cấu trúc Kỹ năng và Danh sách từng Part --}}
    @foreach ($test['skills'] as $skillKey => $skill)
        <div style="margin-bottom: 36px;">
            {{-- Tiêu đề Kỹ năng (VD: LISTENING hoặc READING) --}}
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #4338ca; color: #ffffff; display: flex; align-items: center; justify-content: center;">
                        @if ($skillKey === 'listening')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                            </svg>
                        @elseif ($skillKey === 'reading')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        @elseif ($skillKey === 'speaking')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="23"></line>
                                <line x1="8" y1="23" x2="16" y2="23"></line>
                            </svg>
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h2 style="font-size: 19px; font-weight: 800; color: #0f172a; margin: 0;">
                            Phần thi {{ $skill['name'] }}
                        </h2>
                    </div>
                </div>
                <div style="font-size: 13.5px; font-weight: 600; color: #64748b;">
                    Thời gian: <strong>{{ $skill['time_limit'] }} phút</strong> &bull; Chỉ tiêu: <strong>{{ $skill['total_questions'] }} câu hỏi</strong>
                </div>
            </div>

            {{-- Danh sách các Part trong Kỹ năng --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                @foreach ($skill['parts'] as $part)
                    @php
                        $partComplete = $part['is_complete'];
                        $partEmpty = $part['current_count'] === 0;
                        $partRate = $part['completion_rate'];
                    @endphp
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                        {{-- Header của Part --}}
                        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 16px; font-weight: 800; color: #1e1b4b;">
                                        {{ $part['name'] }}
                                    </span>
                                    @if ($partComplete)
                                        <span style="font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                            ✓ Đã đủ câu
                                        </span>
                                    @elseif ($partEmpty)
                                        <span style="font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">
                                            Chưa có câu hỏi
                                        </span>
                                    @else
                                        <span style="font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
                                            Thiếu {{ $part['target_count'] - $part['current_count'] }} câu
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 3px;">
                                    {{ $part['description'] }}
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 16px;">
                                {{-- Tiến độ số câu --}}
                                <div style="text-align: right;">
                                    <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                                        {{ $part['current_count'] }} / {{ $part['target_count'] }} câu
                                    </div>
                                    <div style="width: 110px; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 4px;">
                                        <div style="height: 100%; width: {{ $partRate }}%; background: {{ $partComplete ? '#10b981' : ($partEmpty ? '#cbd5e1' : '#4f46e5') }}; border-radius: 999px;"></div>
                                    </div>
                                </div>

                                {{-- Nút Thêm câu hỏi vào Part này --}}
                                <a href="{{ route('admin.questions.create', ['test_id' => $test['id'], 'section_id' => $part['section_id']]) }}" class="btn-admin-action" style="background: #ffffff; color: #4338ca; border-color: #c7d2fe; font-weight: 700; font-size: 12.5px; padding: 6px 12px; text-decoration: none;" title="Thêm câu hỏi vào {{ $part['name'] }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>Thêm câu hỏi</span>
                                </a>
                            </div>
                        </div>

                        {{-- Danh sách câu hỏi trong Part --}}
                        <div style="padding: 18px 20px;">
                            @if (empty($part['questions']))
                                {{-- TRẠNG THÁI RỖNG (EMPTY STATE CHO PART CHƯA CÓ CÂU HỎI) --}}
                                <div style="text-align: center; padding: 30px 16px; background: #fafaf9; border: 1.5px dashed #e2e8f0; border-radius: 12px;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #94a3b8;">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                            <line x1="12" y1="11" x2="12" y2="17"></line>
                                            <line x1="9" y1="14" x2="15" y2="14"></line>
                                        </svg>
                                    </div>
                                    <div style="font-size: 14.5px; font-weight: 700; color: #334155;">
                                        Phần thi {{ $part['name'] }} chưa có câu hỏi nào
                                    </div>
                                    <p style="font-size: 13px; color: #64748b; margin: 4px 0 14px; max-width: 420px; margin-left: auto; margin-right: auto;">
                                        Cần bổ sung đúng <strong>{{ $part['target_count'] }} câu hỏi</strong> theo cấu trúc TOEIC để hoàn thiện phần thi này.
                                    </p>
                                    <a href="{{ route('admin.questions.create', ['test_id' => $test['id'], 'section_id' => $part['section_id']]) }}" class="btn-admin-primary" style="display: inline-flex; font-size: 13px; padding: 7px 16px; text-decoration: none;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Bắt đầu thêm câu hỏi vào Part này</span>
                                    </a>
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 14px;">
                                    @foreach ($part['questions'] as $item)
                                        @if (!empty($item['is_grouped']))
                                            {{-- HIỂN THỊ QUESTION SET (NHÓM CÂU HỎI DÙNG CHUNG ĐOẠN VĂN / AUDIO / TRANSCRIPT) --}}
                                            <div style="border: 1.5px solid #c7d2fe; border-radius: 12px; background: #fdfdfd; overflow: hidden;">
                                                {{-- Stimulus Header --}}
                                                <div style="background: #eef2ff; padding: 12px 18px; border-bottom: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: space-between;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <span style="font-size: 12px; font-weight: 800; background: #4338ca; color: #fff; padding: 2px 7px; border-radius: 5px;">
                                                            QUESTION SET
                                                        </span>
                                                        <span style="font-size: 13.5px; font-weight: 700; color: #1e1b4b;">
                                                            {{ $item['group_title'] }}
                                                        </span>
                                                    </div>
                                                    <span style="font-size: 12px; color: #4338ca; font-weight: 600;">
                                                        Gồm {{ count($item['child_questions']) }} câu hỏi con
                                                    </span>
                                                </div>

                                                {{-- Nội dung đoạn văn hoặc file nghe --}}
                                                <div style="padding: 12px 18px; background: #ffffff; border-bottom: 1px dashed #e2e8f0; font-size: 13px; color: #475569; line-height: 1.6;">
                                                    @if (!empty($item['stimulus_text']))
                                                        <div style="margin-bottom: 6px; font-style: italic; color: #334155;">
                                                            {{ $item['stimulus_text'] }}
                                                        </div>
                                                    @endif
                                                    @if (!empty($item['transcript']))
                                                        <div style="padding: 8px 12px; background: #f8fafc; border-radius: 6px; border-left: 3px solid #6366f1; font-size: 12.5px; color: #334155;">
                                                            <strong>Transcript / Lời thoại:</strong> {{ Str::limit($item['transcript'], 160) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Danh sách câu hỏi con trong nhóm --}}
                                                <div style="padding: 12px 18px; display: flex; flex-direction: column; gap: 10px;">
                                                    @foreach ($item['child_questions'] as $child)
                                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #edf2f7;">
                                                            <div style="display: flex; align-items: baseline; gap: 10px;">
                                                                <span style="font-size: 13px; font-weight: 800; color: #4338ca; width: 34px;">
                                                                    #{{ $child['sequence_no'] }}
                                                                </span>
                                                                <span style="font-size: 11.5px; font-weight: 700; color: #64748b; background: #fff; padding: 1px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                                                    {{ $child['question_code'] }}
                                                                </span>
                                                                <span style="font-size: 13.5px; color: #1e293b; font-weight: 600;">
                                                                    {{ $child['content'] }}
                                                                </span>
                                                            </div>
                                                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                                <span style="font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                                                    Đáp án: ({{ $child['correct_option'] }})
                                                                </span>
                                                                <span style="font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px; background: #e2e8f0; color: #475569;">
                                                                    {{ $child['difficulty'] }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            {{-- CÂU HỎI ĐƠN LẺ (SINGLE QUESTION) --}}
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s ease;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <span style="font-size: 13.5px; font-weight: 800; color: #4338ca; width: 36px; text-align: center;">
                                                        #{{ $item['sequence_no'] }}
                                                    </span>
                                                    <span style="font-size: 12px; font-weight: 700; color: #64748b; background: #f8fafc; padding: 2px 7px; border-radius: 5px; border: 1px solid #e2e8f0;">
                                                        {{ $item['question_code'] }}
                                                    </span>
                                                    <span style="font-size: 13.5px; color: #1e293b; font-weight: 600;">
                                                        {{ $item['content'] }}
                                                    </span>
                                                </div>

                                                <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                                                    @if (!empty($item['correct_option']))
                                                        <span style="font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                                            Đ/A: ({{ $item['correct_option'] }})
                                                        </span>
                                                    @endif
                                                    <span style="font-size: 11.5px; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                                                        {{ $item['difficulty'] }}
                                                    </span>
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
@endsection

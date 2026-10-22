@extends('layouts.admin', ['active' => 'tu-vung-flashcard'])

@section('title', 'Nhập từ vựng hàng loạt từ file')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.vocabularies.index') }}" style="color: inherit; text-decoration: none;">Từ vựng & Flashcards</a>
    <span class="crumb-arrow">></span>
    <span style="color: #4f46e5; font-weight: 700;">Nhập từ vựng hàng loạt</span>
@endsection

@section('content')
<div class="imp-page">
    {{-- Header Action Bar --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('admin.vocabularies.index') }}" class="vcb-btn-secondary" style="padding: 7px 12px; font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Quay lại</span>
            </a>
            <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0;">Nhập từ vựng hàng loạt</h1>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            {{-- Tải file mẫu --}}
            <a href="{{ route('admin.vocabularies.sample-csv') }}" class="vcb-btn-secondary" style="padding: 8px 14px; font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Tải file Excel/CSV mẫu</span>
            </a>

            {{-- Xem lịch sử import --}}
            <button type="button" class="vcb-btn-secondary" onclick="alert('Đợt import gần nhất: #VOC-IMPORT-2025-05 (210 từ vựng vào ngày hôm qua)')" style="padding: 8px 14px; font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Xem lịch sử import</span>
            </button>

            {{-- Hướng dẫn cấu trúc cột --}}
            <button type="button" class="vcb-btn-secondary" onclick="openModal('modalGuide')" style="padding: 8px 14px; font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>Hướng dẫn cấu trúc cột</span>
            </button>
        </div>
    </div>

    {{-- Title Banner Card --}}
    <div class="imp-card" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #ede9fe; color: #4f46e5; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                        <h2 style="font-size: 19px; font-weight: 800; color: #0f172a; margin: 0;">Nhập từ vựng TOEIC hàng loạt từ file</h2>
                        <span style="background: #ede9fe; color: #4338ca; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px;">#VOC-IMPORT-2025-06</span>
                    </div>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">
                        Hỗ trợ tự động chuẩn hóa định dạng Microsoft Excel (.xlsx), CSV, Anki Deck (.apkg), và tệp văn bản UTF-8 (.txt)
                    </p>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 14px; display: flex; align-items: center; gap: 10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <div>
                    <div style="font-size: 12px; font-weight: 800; color: #0f172a;">Engine AI V4.2</div>
                    <div style="font-size: 11px; color: #64748b;">Oxford Dictionary &amp; ETS Voice</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stepper Bar --}}
    <div class="imp-stepper-card">
        <div class="imp-stepper-grid">
            {{-- Step 1 --}}
            <div class="imp-step-item done">
                <div class="imp-step-circle done">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="imp-step-text">
                    <span class="imp-step-tag">BƯỚC 1 • HOÀN THÀNH</span>
                    <span class="imp-step-label">Tải file &amp; Audio pack</span>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="imp-step-item done">
                <div class="imp-step-circle done">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="imp-step-text">
                    <span class="imp-step-tag">BƯỚC 2 • HOÀN THÀNH</span>
                    <span class="imp-step-label">Khớp nối trường (Mapping)</span>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="imp-step-item active">
                <div class="imp-step-circle active">3</div>
                <div class="imp-step-text">
                    <span class="imp-step-tag">BƯỚC 3 • ĐANG XỬ LÝ</span>
                    <span class="imp-step-label">Hợp lệ &amp; Chuẩn hóa AI</span>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="imp-step-item">
                <div class="imp-step-circle waiting">4</div>
                <div class="imp-step-text">
                    <span class="imp-step-tag">BƯỚC 4 • CHỜ GHI DỮ LIỆU</span>
                    <span class="imp-step-label">Lưu vào bộ Flashcards</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Middle 2-Column Split --}}
    <div class="imp-middle-grid">
        {{-- Left: Data Sources & Field Mapping --}}
        <div class="imp-middle-left">
            {{-- Card 1: Tài nguyên dữ liệu đầu vào --}}
            <div class="imp-card">
                <div class="imp-card-header">
                    <div class="imp-card-title-row">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span class="imp-card-title">Tài nguyên dữ liệu đầu vào</span>
                    </div>
                    <span class="imp-badge-subtle">Khởi tạo 3 phút trước</span>
                </div>

                {{-- 2 File Cards --}}
                <div class="imp-files-grid">
                    <div class="imp-file-box">
                        <div class="imp-file-icon green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                        </div>
                        <div class="imp-file-details">
                            <div class="imp-file-name" title="ETS_TOEIC_Top1000_Words_Batch1.xlsx">ETS_TOEIC_Top1000_Wor...</div>
                            <div class="imp-file-meta">
                                <span>380 KB</span>
                                <span>•</span>
                                <span class="blue">250 từ vựng</span>
                                <span>•</span>
                                <span>Sheet: "MasterVocab"</span>
                            </div>
                        </div>
                        <button type="button" onclick="openModal('modalUploadFile')" title="Đổi tệp khác" style="background: transparent; border: none; cursor: pointer; color: #64748b;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="imp-file-box">
                        <div class="imp-file-icon purple">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                            </svg>
                        </div>
                        <div class="imp-file-details">
                            <div class="imp-file-name" title="Native_Audio_US_UK_Pack.zip">Native_Audio_US_UK_Paci...</div>
                            <div class="imp-file-meta">
                                <span>18.4 MB</span>
                                <span>•</span>
                                <span class="blue">210 tệp MP3</span>
                                <span>•</span>
                                <span>Khớp 84% từ khóa</span>
                            </div>
                        </div>
                        <button type="button" onclick="alert('Đã đồng bộ gói âm thanh native US/UK')" title="Chi tiết audio" style="background: transparent; border: none; cursor: pointer; color: #64748b;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14"></line>
                                <line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line>
                                <line x1="20" y1="12" x2="20" y2="3"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- 4 Features --}}
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                    TIỆN ÍCH TỰ ĐỘNG XỬ LÝ THÔNG MINH
                </div>
                <div class="imp-features-grid">
                    <div class="imp-feat-item">
                        <span class="imp-feat-dot"></span>
                        <span>Tự động tra cứu <strong>Oxford / Cambridge</strong> để bổ sung phiên âm IPA và loại từ thiếu</span>
                    </div>
                    <div class="imp-feat-item">
                        <span class="imp-feat-dot"></span>
                        <span>Tự động tổng hợp <strong>Giọng đọc AI (US/UK)</strong> chuẩn đề thi ETS cho 40 từ thiếu MP3</span>
                    </div>
                    <div class="imp-feat-item">
                        <span class="imp-feat-dot"></span>
                        <span>Tự động gắn thang điểm mục tiêu <strong>450+, 650+, 800+</strong> theo độ khó</span>
                    </div>
                    <div class="imp-feat-item">
                        <span class="imp-feat-dot"></span>
                        <span>Đối soát và phát hiện gặp trùng lặp với kho <strong>18,450 từ vựng</strong> có sẵn</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Trạng thái ánh xạ trường dữ liệu --}}
            <div class="imp-card">
                <div class="imp-card-header">
                    <div class="imp-card-title-row">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="21" x2="4" y2="14"></line>
                            <line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line>
                            <line x1="20" y1="12" x2="20" y2="3"></line>
                        </svg>
                        <span class="imp-card-title">Trạng thái ánh xạ trường dữ liệu (Field Mapping)</span>
                    </div>
                    <a href="javascript:void(0)" onclick="alert('Ánh xạ trường đã được thiết lập tự động tối ưu nhất!')" class="imp-link-small">Chỉnh sửa ánh xạ &#x270E;</a>
                </div>

                <div class="imp-mapping-alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                    <span>Hệ thống đã nhận diện tự động thành công 11/11 trường dữ liệu theo chuẩn từ điển Flashcard StudyPREP.</span>
                </div>

                <div class="imp-mapping-pills">
                    <div class="imp-map-pill">
                        <span>Cột A: Từ vựng</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">word_text</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột B: Loại từ</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">part_of_speech</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột C: Phiên âm</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">pronunciation_ipa</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột D: Định nghĩa Anh</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">definition_en</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột E: Nghĩa Việt</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">vietnamese_meaning</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột F: Câu ví dụ</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">example_sentence</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột G: Dịch câu ví dụ</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">example_translation</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột H: Collocations</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">collocations</span>
                    </div>
                    <div class="imp-map-pill">
                        <span>Cột I: File Audio</span>
                        <span>&rarr;</span>
                        <span class="imp-map-target">audio_filename</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Deck Config & Import Mode --}}
        <div class="imp-card">
            <div class="imp-card-header">
                <div class="imp-card-title-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span class="imp-card-title">Cấu hình bộ thẻ đích đến</span>
                </div>
            </div>

            <form id="confirmImportForm" method="POST" action="{{ route('admin.vocabularies.import-confirm') }}">
                @csrf
                <div class="imp-form-group">
                    <label class="imp-label">Chọn bộ thẻ Flashcard chính</label>
                    <select name="topic_id" id="targetDeckSelect" class="imp-select">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}" {{ str_contains($t->name, 'ETS') || $loop->first ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    <a href="javascript:void(0)" onclick="openModal('modalCreateDeckFast')" class="imp-link-small">+ Tạo bộ thẻ mới từ đợt import này</a>
                </div>

                <div class="imp-form-group">
                    <label class="imp-label">Chủ đề bài thi (Tags)</label>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <span class="vc-tag-pill-teal" style="background: #e0e7ff; color: #4338ca;">
                            <span>Kinh doanh &amp; Tài chính</span>
                            <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                        </span>
                        <span class="vc-tag-pill-teal" style="background: #e0e7ff; color: #4338ca;">
                            <span>Hợp đồng &amp; Đàm phán</span>
                            <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                        </span>
                        <button type="button" class="vc-tag-add-btn" onclick="addTagPromptFast()">+ Thêm tag</button>
                    </div>
                </div>

                <div class="imp-form-group">
                    <label class="imp-label">Phân nhóm cấp độ Target</label>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="vcb-page-btn" style="flex: 1; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 0;" onclick="toggleLevelBtn(this)">Target 450+</button>
                        <button type="button" class="vcb-page-btn active" style="flex: 1; border-radius: 8px; padding: 6px 0;" onclick="toggleLevelBtn(this)">Target 650+</button>
                        <button type="button" class="vcb-page-btn active" style="flex: 1; border-radius: 8px; padding: 6px 0;" onclick="toggleLevelBtn(this)">Target 800+</button>
                    </div>
                </div>

                <div class="imp-form-group" style="margin-bottom: 0;">
                    <label class="imp-label">Chế độ xử lý trùng lặp (Import Mode)</label>

                    <label class="imp-radio-item selected">
                        <input type="radio" name="import_mode" value="skip" checked style="margin-top: 3px; accent-color: #4f46e5;">
                        <div>
                            <div class="imp-radio-title">Bỏ qua từ trùng lặp (Khuyến dùng)</div>
                            <div class="imp-radio-sub">Giữ nguyên thẻ từ vựng cũ đã có trong kho dữ liệu</div>
                        </div>
                    </label>

                    <label class="imp-radio-item">
                        <input type="radio" name="import_mode" value="overwrite" style="margin-top: 3px; accent-color: #4f46e5;">
                        <div>
                            <div class="imp-radio-title">Ghi đè &amp; Cập nhật thông tin</div>
                            <div class="imp-radio-sub">Cập nhật lại nghĩa và âm thanh mới từ file</div>
                        </div>
                    </label>

                    <label class="imp-radio-item">
                        <input type="radio" name="import_mode" value="duplicate" style="margin-top: 3px; accent-color: #4f46e5;">
                        <div>
                            <div class="imp-radio-title">Tạo thẻ trùng lặp mới</div>
                            <div class="imp-radio-sub">Cho phép nhiều thẻ cùng tên từ trong hệ thống</div>
                        </div>
                    </label>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Inspection KPI Metrics --}}
    <div class="imp-kpi-grid">
        <div class="imp-kpi-box">
            <div class="imp-kpi-head">
                <span>TỔNG SỐ TỪ</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
            </div>
            <div class="imp-kpi-val">250</div>
            <div class="imp-kpi-sub">trong file nhập tải lên</div>
        </div>

        <div class="imp-kpi-box">
            <div class="imp-kpi-head">
                <span>HỢP LỆ &amp; SẴN SÀNG</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
            </div>
            <div class="imp-kpi-val" style="color: #0f766e;">
                <span>242</span>
                <span style="font-size: 13px; font-weight: 700; color: #0d9488;">96.8%</span>
            </div>
            <div class="imp-kpi-sub">Đủ cấu trúc flashcard</div>
        </div>

        <div class="imp-kpi-box">
            <div class="imp-kpi-head">
                <span>CẢNH BÁO XEM XÉT</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="imp-kpi-val" style="color: #b45309;">
                <span>6</span>
                <span style="font-size: 13px; font-weight: 700; color: #d97706;">2.4%</span>
            </div>
            <div class="imp-kpi-sub">Thiếu dịch câu/word family</div>
        </div>

        <div class="imp-kpi-box">
            <div class="imp-kpi-head">
                <span>BỊ CHẶN / LỖI</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
            </div>
            <div class="imp-kpi-val" style="color: #b91c1c;">
                <span>2</span>
                <span style="font-size: 13px; font-weight: 700; color: #dc2626;">0.8%</span>
            </div>
            <div class="imp-kpi-sub">Trùng từ kho hiện tại</div>
        </div>
    </div>

    {{-- Filter Tabs & Table Card --}}
    <div class="imp-table-topbar">
        <div class="imp-tabs-group">
            <button type="button" class="imp-tab-btn active" onclick="filterReviewTable('all', this)">Tất cả (250)</button>
            <button type="button" class="imp-tab-btn" onclick="filterReviewTable('valid', this)">Hợp lệ (242)</button>
            <button type="button" class="imp-tab-btn warn" onclick="filterReviewTable('warning', this)">Cảnh báo (6)</button>
            <button type="button" class="imp-tab-btn err" onclick="filterReviewTable('blocked', this)">Lỗi cần sửa (2)</button>
        </div>

        <button type="button" class="vcb-btn-secondary" onclick="alert('Đã tạo file báo cáo kiểm tra: report_check_batch_06.xlsx')" style="padding: 7px 12px; font-size: 12.5px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <span>Xuất file báo cáo kiểm tra (.xlsx)</span>
        </button>
    </div>

    {{-- Review Data Table --}}
    <div class="imp-table-card">
        <table class="imp-table" id="reviewTable">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="masterCheckAll" class="vcb-checkbox-custom" checked onclick="toggleAllReviewRows(this)">
                    </th>
                    <th>MÃ / DÒNG</th>
                    <th>TỪ VỰNG &amp; TỪ LOẠI</th>
                    <th>PHIÊN ÂM</th>
                    <th>NGHĨA TIẾNG VIỆT</th>
                    <th>CÂU VÍ DỤ NGỮ CẢNH ETS</th>
                    <th>TRẠNG THÁI</th>
                    <th style="text-align: center;">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($previewItems as $row)
                    <tr class="review-row review-row-{{ $row['status'] }}">
                        <td style="text-align: center;">
                            <input type="checkbox" class="vcb-checkbox-custom review-chk" {{ $row['status'] !== 'blocked' ? 'checked' : '' }}>
                        </td>
                        <td style="color: #64748b; font-weight: 600;">{{ $row['code'] }}</td>
                        <td>
                            <span style="font-size: 14px; font-weight: 800; color: #0f172a;">{{ $row['word'] }}</span>
                            <span class="vcb-pos-badge" style="margin-left: 6px; font-size: 10.5px;">{{ $row['pos'] }}</span>
                        </td>
                        <td style="color: #64748b; font-family: monospace;">{{ $row['ipa'] }}</td>
                        <td style="font-weight: 600; color: #1e293b; max-width: 180px;">{{ $row['meaning'] }}</td>
                        <td style="max-width: 280px; font-size: 12px;">
                            @if ($row['example'])
                                <span style="font-style: italic; color: #334155;">"{{ $row['example'] }}"</span>
                            @endif

                            @if ($row['warning'])
                                <div style="display: flex; align-items: flex-start; gap: 5px; color: {{ $row['status'] === 'blocked' ? '#b91c1c' : '#b45309' }}; margin-top: 4px; font-size: 11.5px; font-weight: 500;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <span>{{ $row['warning'] }}</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if ($row['status'] === 'valid')
                                <span class="imp-badge-valid">● Hợp lệ</span>
                            @elseif ($row['status'] === 'warning')
                                <span class="imp-badge-warn">● Cảnh báo</span>
                            @else
                                <span class="imp-badge-block">● Bị chặn</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($row['status'] === 'valid')
                                <button type="button" class="vc-btn-trash" onclick="alert('Chỉnh sửa từ: {{ $row['word'] }}')" title="Chỉnh sửa">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            @elseif ($row['code'] === '#003')
                                <button type="button" class="imp-action-btn-pill" style="color: #991b1b; background: #fee2e2; border-color: #fca5a5;" onclick="promptEditMeaning(this)">
                                    <span>Sửa nhanh</span>
                                </button>
                            @elseif ($row['code'] === '#004')
                                <button type="button" class="imp-action-btn-pill" style="color: #4338ca; background: #e0e7ff; border-color: #c7d2fe;" onclick="alert('AI đã tự động bổ sung họ từ: require, requirement!')">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                                    </svg>
                                    <span>AI điền</span>
                                </button>
                            @else
                                <button type="button" class="imp-action-btn-pill" onclick="this.closest('tr').remove();" title="Bỏ qua dòng này">
                                    <span>Bỏ qua dòng</span>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Table Footer --}}
        <div class="vcb-table-footer">
            <div class="vcb-footer-left">
                Hiển thị 1 - 5 trong tổng số 250 từ vựng. Đã chọn <strong style="color: #4f46e5;">242 từ hợp lệ</strong>.
            </div>

            <div class="vcb-pagination-wrap">
                <span class="vcb-page-btn disabled">&lt;</span>
                <span class="vcb-page-btn active">1</span>
                <span class="vcb-page-btn">2</span>
                <span class="vcb-page-btn">3</span>
                <span class="vcb-page-btn disabled">...</span>
                <span class="vcb-page-btn">50</span>
                <span class="vcb-page-btn">&gt;</span>
            </div>
        </div>
    </div>

    {{-- Sticky Bottom Floating Action Bar --}}
    <div class="imp-bottom-bar">
        <div class="imp-bottom-left">
            <div class="imp-bottom-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div>
                <div class="imp-bottom-title">Sẵn sàng nạp 242 / 250 từ vựng vào bộ thẻ "ETS 2024 - 1000 Từ vựng trọng tâm"</div>
                <div class="imp-bottom-desc">Đã loại trừ 2 từ trùng lặp + 6 từ có cảnh báo sẽ được AI tự hoàn thiện lúc nạp</div>
            </div>
        </div>

        <div class="imp-bottom-actions">
            <a href="{{ route('admin.vocabularies.index') }}" class="vcb-btn-secondary" style="padding: 10px 18px;">
                <span>Hủy bỏ</span>
            </a>

            <button type="button" class="vcb-btn-secondary" onclick="alert('Đã lưu cấu hình mapping và chế độ import thành công!')" style="padding: 10px 18px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>Lưu mẫu cấu hình</span>
            </button>

            <button type="button" class="imp-btn-confirm" onclick="document.getElementById('confirmImportForm').submit()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                <span>Bắt đầu Nhập 242 từ vựng vào hệ thống</span>
            </button>
        </div>
    </div>
</div>

{{-- MODAL 1: TẢI FILE MỚI ĐỂ IMPORT --}}
<div id="modalUploadFile" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Tải lên tệp từ vựng mới</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modalUploadFile')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.vocabularies.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="vcb-modal-body">
                <p style="font-size: 13px; color: #475569; margin-top: 0;">
                    Chọn tệp <strong>.CSV</strong>, <strong>.XLSX</strong> hoặc <strong>.TXT</strong> chứa danh sách từ vựng theo mẫu.
                </p>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chọn bộ thẻ đích</label>
                    <select name="topic_id" class="vcb-form-control">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chọn file dữ liệu <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="file" accept=".csv, .xlsx, .txt" class="vcb-form-control" required>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modalUploadFile')">Hủy</button>
                <button type="submit" class="vcb-btn-primary">Tiến hành đọc file</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: HƯỚNG DẪN CẤU TRÚC CỘT --}}
<div id="modalGuide" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog" style="max-width: 650px;">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Hướng dẫn chuẩn cấu trúc cột file Import</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modalGuide')">&times;</button>
        </div>
        <div class="vcb-modal-body">
            <table class="imp-table" style="font-size: 12px; margin-bottom: 12px;">
                <thead>
                    <tr>
                        <th>CỘT</th>
                        <th>TÊN TRƯỜNG</th>
                        <th>BẮT BUỘC</th>
                        <th>VÍ DỤ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>A</td><td><strong>Từ vựng</strong> (word)</td><td><span style="color: #dc2626;">Có</span></td><td>substantial</td></tr>
                    <tr><td>B</td><td><strong>Loại từ</strong> (pos)</td><td>Không</td><td>Adjective (adj)</td></tr>
                    <tr><td>C</td><td><strong>Phiên âm</strong> (ipa)</td><td>Không</td><td>/səbˈstæn.ʃəl/</td></tr>
                    <tr><td>D</td><td><strong>Định nghĩa Anh</strong></td><td>Không</td><td>Large in amount or importance</td></tr>
                    <tr><td>E</td><td><strong>Nghĩa Việt</strong></td><td><span style="color: #dc2626;">Có</span></td><td>Đáng kể, trọng yếu</td></tr>
                    <tr><td>F</td><td><strong>Câu ví dụ</strong></td><td>Không</td><td>The company reported a substantial...</td></tr>
                    <tr><td>G</td><td><strong>Dịch câu ví dụ</strong></td><td>Không</td><td>Công ty báo cáo lợi nhuận...</td></tr>
                    <tr><td>H</td><td><strong>Mức điểm</strong></td><td>Không</td><td>800+</td></tr>
                </tbody>
            </table>
            <div style="background: #eef2ff; color: #4338ca; padding: 10px 14px; border-radius: 8px; font-size: 12px;">
                <strong>Gợi ý:</strong> Tải file mẫu trực tiếp qua nút <em>"Tải file Excel/CSV mẫu"</em> ở trên để có định dạng chuẩn nhất.
            </div>
        </div>
        <div class="vcb-modal-footer">
            <button type="button" class="vcb-btn-secondary" onclick="closeModal('modalGuide')">Đóng</button>
            <a href="{{ route('admin.vocabularies.sample-csv') }}" class="vcb-btn-primary">Tải file mẫu ngay</a>
        </div>
    </div>
</div>

{{-- MODAL 3: TẠO BỘ THẺ NHANH --}}
<div id="modalCreateDeckFast" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Tạo bộ thẻ Flashcard mới</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modalCreateDeckFast')">&times;</button>
        </div>
        <div class="vcb-modal-body">
            <div class="vcb-form-group">
                <label class="vcb-form-label">Tên bộ thẻ mới <span style="color: #ef4444;">*</span></label>
                <input type="text" id="newDeckFastName" class="vcb-form-control" placeholder="Ví dụ: ETS 2025 Test 1 Vocab">
            </div>
        </div>
        <div class="vcb-modal-footer">
            <button type="button" class="vcb-btn-secondary" onclick="closeModal('modalCreateDeckFast')">Hủy</button>
            <button type="button" class="vcb-btn-primary" onclick="createNewDeckFast()">Tạo và áp dụng</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function filterReviewTable(type, btn) {
        document.querySelectorAll('.imp-tab-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');

        var rows = document.querySelectorAll('.review-row');
        rows.forEach(function(r) {
            if (type === 'all') {
                r.style.display = '';
            } else if (type === 'valid') {
                r.style.display = r.classList.contains('review-row-valid') ? '' : 'none';
            } else if (type === 'warning') {
                r.style.display = r.classList.contains('review-row-warning') ? '' : 'none';
            } else if (type === 'blocked') {
                r.style.display = r.classList.contains('review-row-blocked') ? '' : 'none';
            }
        });
    }

    function toggleAllReviewRows(master) {
        document.querySelectorAll('.review-chk').forEach(function(c) {
            c.checked = master.checked;
        });
    }

    function toggleLevelBtn(btn) {
        btn.classList.toggle('active');
    }

    function promptEditMeaning(btn) {
        var meaning = prompt('Nhập bổ sung bản dịch câu ví dụ tiếng Việt:');
        if (meaning && meaning.trim()) {
            var tr = btn.closest('tr');
            var tdExample = tr.querySelector('td:nth-child(6)');
            tdExample.innerHTML = '<span style="font-style: italic; color: #334155;">"The travel agent prepared a comprehensive itinerary."</span><div style="color: #64748b; font-size: 11.5px; margin-top: 2px;">' + meaning.trim() + '</div>';
            var tdStatus = tr.querySelector('td:nth-child(7)');
            tdStatus.innerHTML = '<span class="imp-badge-valid">● Hợp lệ</span>';
            btn.parentElement.innerHTML = '<span style="color: #10b981; font-weight: 700; font-size: 12px;">Đã sửa</span>';
            tr.className = 'review-row review-row-valid';
        }
    }

    function addTagPromptFast() {
        var t = prompt('Nhập tag mới:');
        if (t && t.trim()) {
            alert('Đã thêm tag "' + t.trim() + '" vào đợt import này.');
        }
    }

    function createNewDeckFast() {
        var name = document.getElementById('newDeckFastName').value.trim();
        if (!name) return;
        var sel = document.getElementById('targetDeckSelect');
        var opt = document.createElement('option');
        opt.value = name;
        opt.text = name + ' (Mới tạo)';
        opt.selected = true;
        sel.appendChild(opt);
        closeModal('modalCreateDeckFast');
    }

    function openModal(id) {
        var m = document.getElementById(id);
        if (m) m.classList.add('open');
    }

    function closeModal(id) {
        var m = document.getElementById(id);
        if (m) m.classList.remove('open');
    }
</script>
@endpush
@endsection

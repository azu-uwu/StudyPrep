@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Nhập câu hỏi hàng loạt | StudyPrep')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.questions.index') }}" style="color: inherit; text-decoration: none;">Ngân hàng câu hỏi</a>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">Nhập câu hỏi hàng loạt</span>
@endsection

@section('content')
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\TestForm> $testOptions */
    $testOptions = $availableTests ?? collect();
    $isPdfTabActive = $errors->has('question_pdf')
        || $errors->has('answer_pdf')
        || old('import_tab') === 'pdf';
@endphp

<style>
    .bi-tabs-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 14px 24px 0 24px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 16px 16px 0 0;
    }
    .bi-tab-btn {
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 700;
        color: #64748b;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 10px 10px 0 0;
        cursor: pointer;
        margin-bottom: -1px;
        transition: all 0.15s ease;
    }
    .bi-tab-btn:hover {
        color: #0f172a;
    }
    .bi-tab-btn.active {
        color: var(--admin-primary, #4338ca);
        background: #ffffff;
        border-color: #e2e8f0;
        border-bottom-color: #ffffff;
    }
    .bi-tab-panel {
        padding: 24px;
    }
    .bi-split-grid {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .bi-split-grid {
            grid-template-columns: 1fr;
        }
    }
    .bi-dropzone {
        position: relative;
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        padding: 28px 20px;
        text-align: center;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .bi-dropzone.has-file {
        border-color: #4f46e5;
        background: #eef2ff;
    }
    .bi-dropzone-sm {
        padding: 18px 16px;
        text-align: left;
    }
    .bi-file-chip {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        background: #ffffff;
        border: 1px solid #c7d2fe;
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }
    .bi-file-chip.visible {
        display: flex;
    }
    .bi-clear-btn {
        border: none;
        background: #fee2e2;
        color: #b91c1c;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
    }
    .bi-field-group {
        margin-bottom: 14px;
    }
    .bi-label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .bi-hint {
        font-size: 12px;
        color: #64748b;
        margin: 4px 0 0 0;
    }
    <style>
    .qbank-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px; /* Tạo khoảng thở với khung trắng bên dưới */
        flex-wrap: wrap;     /* Tự xuống dòng đẹp mắt khi màn hình nhỏ */
    }

    .qbank-title {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px 0;
        line-height: 1.3;
    }

    .qbank-subtitle {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
    }

    .qbank-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    /* Nút bấm chuẩn đồng bộ */
    .btn-header-outline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #f2f2f3ff;
        background: #d15c5cff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
        margin-bottom: 12px;
        margin-top:10px; 
    }

    .btn-header-outline:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    /* Hiệu ứng hover cho các mục trong dropdown */
    .sample-menu-item {
        display: block;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        text-decoration: none;
        transition: background 0.15s;
    }

    .sample-menu-item:hover {
        background: #f1f5f9;
        color: #2563eb;
    }

    .download{
        background: #2c649cff;
        border-color: #94a3b8;
        color: #efeff1ff;
    }
</style>
</style>

<div class="qbank-page">
    {{-- Header 1 hàng ngang chuẩn Admin --}}
    <div class="qbank-header">
        <div>
            <h1 class="qbank-title">Nhập câu hỏi hàng loạt</h1>
            <p class="qbank-subtitle">Tải lên đề thi từ file Word mẫu hoặc bộ file PDF để kiểm tra trước khi lưu vào ngân hàng đề.</p>
        </div>

        <div class="qbank-header-actions">
            {{-- Dropdown Tải file mẫu --}}
            <div style="position: relative; display: inline-block;">
                <button type="button"
                        class="btn-header-outline download"
                        onclick="event.stopPropagation(); var m = document.getElementById('biSampleMenu'); m.style.display = (m.style.display === 'block') ? 'none' : 'block';">
                    <span> Tải file mẫu ▾</span>
                </button>
                <div id="biSampleMenu" style="display: none; position: absolute; right: 0; top: calc(100% + 6px); min-width: 190px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12); padding: 6px; z-index: 60;">
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'listening']) }}" class="sample-menu-item">Mẫu Listening</a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'reading']) }}" class="sample-menu-item">Mẫu Reading</a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'speaking']) }}" class="sample-menu-item">Mẫu Speaking</a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'writing']) }}" class="sample-menu-item">Mẫu Writing</a>
                    <div style="height: 1px; background: #e2e8f0; margin: 4px 0;"></div>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'all']) }}" class="sample-menu-item" style="color: #047857;">Mẫu Excel (.xlsx)</a>
                </div>
            </div>

            {{-- Nút Quay lại --}}
            <a href="{{ route('admin.questions.index') }}" class="btn-header-outline">
                <span> Quay lại danh sách</span>
            </a>
        </div>
    </div>

<script>
    // Tự động đóng dropdown khi click ra ngoài vùng menu
    document.addEventListener('click', function () {
        var m = document.getElementById('biSampleMenu');
        if (m) m.style.display = 'none';
    });
</script>

    @if (session('success'))
        <div class="alert-admin-success" style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 12px; margin-bottom: 18px; font-size: 13.5px;">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 12px; margin-bottom: 18px; font-size: 13.5px;">
            <strong>Đã có lỗi xảy ra:</strong>
            <ul style="margin: 6px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1 Card trắng trung tâm với 2 Tab --}}
    <div class="qbank-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
        <div class="bi-tabs-bar" role="tablist">
            <button type="button"
                    class="bi-tab-btn {{ ! $isPdfTabActive ? 'active' : '' }}"
                    id="tabBtnWord"
                    onclick="switchBulkImportTab('word')">
                Nhập từ Word / Excel
            </button>
            <button type="button"
                    class="bi-tab-btn {{ $isPdfTabActive ? 'active' : '' }}"
                    id="tabBtnPdf"
                    onclick="switchBulkImportTab('pdf')">
                Nhập từ PDF
            </button>
        </div>

        {{-- TAB 1: NHẬP TỪ WORD / EXCEL --}}
        <div id="tabPanelWord" class="bi-tab-panel" style="display: {{ ! $isPdfTabActive ? 'block' : 'none' }};">
            <form action="{{ route('admin.questions.import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bi-split-grid">
                    {{-- Cột trái (60%): Vùng chọn / kéo thả file --}}
                    <div>
                        <label class="bi-label">Tệp câu hỏi (.docx, .xlsx, .csv) <span style="color: #dc2626;">*</span></label>
                        <div class="bi-dropzone" id="wordDropzone">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 10px; display: block;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                                Kéo thả hoặc chọn tệp từ máy tính
                            </div>
                            <p class="bi-hint" style="margin-bottom: 14px;">
                                Hỗ trợ file Word (.docx), Excel (.xlsx) hoặc CSV (.csv).
                            </p>
                            <input type="file"
                                   name="import_file"
                                   id="wordFileInput"
                                   accept=".docx,.xlsx,.csv"
                                   required
                                   class="form-control-admin"
                                   style="max-width: 360px; margin: 0 auto; background: #ffffff;"
                                   onchange="handleBiFileChange(this, 'wordFileChip', 'wordFileName', 'wordDropzone')">

                            <div class="bi-file-chip" id="wordFileChip">
                                <span id="wordFileName"></span>
                                <button type="button" class="bi-clear-btn" onclick="clearBiFile('wordFileInput', 'wordFileChip', 'wordDropzone')" title="Bỏ chọn file">&times;</button>
                            </div>
                        </div>
                    </div>

                    {{-- Cột phải (40%): Cấu hình & Nút hành động --}}
                    <div>
                        <div class="bi-field-group">
                            <label class="bi-label">Chế độ nhận diện</label>
                            <select name="import_scope" class="form-control-admin" style="width: 100%;">
                                @include('admin.questions.partials.import-scope-options')
                            </select>
                        </div>

                        <div class="bi-field-group">
                            <label class="bi-label">Gắn vào đề thi (Tùy chọn)</label>
                            <select name="target_test_id" class="form-control-admin" style="width: 100%;">
                                <option value="">-- Chỉ lưu vào ngân hàng câu hỏi --</option>
                                @foreach ($testOptions as $testOption)
                                    <option value="{{ $testOption->test_form_id }}">
                                        [{{ str_replace(['—', '–'], '-', $testOption->test_code) }}] {{ $testOption->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bi-field-group">
                            <label class="bi-label">Trạng thái sau khi duyệt</label>
                            <select name="publish_status" class="form-control-admin" style="width: 100%;">
                                <option value="live">Xuất bản ngay</option>
                                <option value="draft">Lưu bản nháp</option>
                            </select>
                        </div>

                        <div style="padding-top: 8px;">
                            <button type="submit" class="btn-admin-primary" style="width: 100%; justify-content: center; padding: 11px 18px; font-size: 14px;">
                                <span>Phân tích &amp; Kiểm tra đề</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- TAB 2: NHẬP TỪ PDF --}}
        <div id="tabPanelPdf" class="bi-tab-panel" style="display: {{ $isPdfTabActive ? 'block' : 'none' }};">
            <form action="{{ route('admin.questions.import.pdf-preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="import_tab" value="pdf">

                <div class="bi-split-grid">
                    {{-- Cột trái (60%): 2 vùng chọn file PDF --}}
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="bi-dropzone bi-dropzone-sm" id="pdfQuestionDropzone">
                            <label class="bi-label" for="pdfQuestionInput" style="margin-bottom: 4px;">
                                File Đề thi (.pdf) <span style="color: #dc2626;">*</span>
                            </label>
    
                            <input type="file"
                                   name="question_pdf"
                                   id="pdfQuestionInput"
                                   accept=".pdf"
                                   required
                                   class="form-control-admin"
                                   style="width: 100%; background: #ffffff;"
                                   onchange="handleBiFileChange(this, 'pdfQuestionChip', 'pdfQuestionName', 'pdfQuestionDropzone')">
                            <div class="bi-file-chip" id="pdfQuestionChip">
                                <span id="pdfQuestionName"></span>
                                <button type="button" class="bi-clear-btn" onclick="clearBiFile('pdfQuestionInput', 'pdfQuestionChip', 'pdfQuestionDropzone')" title="Bỏ chọn file">&times;</button>
                            </div>
                        </div>

                        <div class="bi-dropzone bi-dropzone-sm" id="pdfAnswerDropzone">
                            <label class="bi-label" for="pdfAnswerInput" style="margin-bottom: 4px;">
                                File Đáp án &amp; Lời giải (.pdf) <span style="color: #64748b; font-weight: 500;">(Tùy chọn)</span>
                            </label>
                            <p class="bi-hint" style="margin-bottom: 10px;">Có thể để trống và bổ sung đáp án ở bước kiểm tra.</p>
                            <input type="file"
                                   name="answer_pdf"
                                   id="pdfAnswerInput"
                                   accept=".pdf"
                                   class="form-control-admin"
                                   style="width: 100%; background: #ffffff;"
                                   onchange="handleBiFileChange(this, 'pdfAnswerChip', 'pdfAnswerName', 'pdfAnswerDropzone')">
                            <div class="bi-file-chip" id="pdfAnswerChip">
                                <span id="pdfAnswerName"></span>
                                <button type="button" class="bi-clear-btn" onclick="clearBiFile('pdfAnswerInput', 'pdfAnswerChip', 'pdfAnswerDropzone')" title="Bỏ chọn file">&times;</button>
                            </div>
                        </div>
                    </div>

                    {{-- Cột phải (40%): Cấu hình & Nút hành động --}}
                    <div>
                        <div class="bi-field-group">
                            <label class="bi-label">Phạm vi đề thi</label>
                            <select name="import_scope" class="form-control-admin" style="width: 100%;">
                                <option value="FULL_LR" {{ old('import_scope', 'FULL_LR') === 'FULL_LR' ? 'selected' : '' }}>Listening &amp; Reading (Câu 1 – 200)</option>
                                <option value="LISTENING" {{ old('import_scope') === 'LISTENING' ? 'selected' : '' }}> Listening (Câu 1 – 100)</option>
                                <option value="READING" {{ old('import_scope') === 'READING' ? 'selected' : '' }}> Reading (Câu 101 – 200)</option>
                            </select>
                        </div>

                        <div class="bi-field-group">
                            <label class="bi-label">Gắn vào đề thi</label>
                            <select name="target_test_id" class="form-control-admin" style="width: 100%;">
                                <option value="">-- Chỉ lưu vào ngân hàng câu hỏi --</option>
                                @foreach ($testOptions as $testOption)
                                    <option value="{{ $testOption->test_form_id }}" {{ (string) old('target_test_id') === (string) $testOption->test_form_id ? 'selected' : '' }}>
                                        [{{ str_replace(['—', '–'], '-', $testOption->test_code) }}] {{ $testOption->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bi-field-group">
                            <label class="bi-label">Trạng thái sau khi duyệt</label>
                            <select name="publish_status" class="form-control-admin" style="width: 100%;">
                                <option value="live" {{ old('publish_status', 'live') === 'live' ? 'selected' : '' }}>Xuất bản ngay</option>
                                <option value="draft" {{ old('publish_status') === 'draft' ? 'selected' : '' }}>Lưu bản nháp</option>
                            </select>
                        </div>

                        <div style="padding-top: 8px;">
                            <button type="submit" class="btn-admin-primary" style="width: 100%; justify-content: center; padding: 11px 18px; font-size: 14px;">
                                <span>Phân tích &amp; Kiểm tra đề</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchBulkImportTab(tab) {
        var isPdf = tab === 'pdf';
        var btnWord = document.getElementById('tabBtnWord');
        var btnPdf = document.getElementById('tabBtnPdf');
        var panelWord = document.getElementById('tabPanelWord');
        var panelPdf = document.getElementById('tabPanelPdf');

        if (btnWord) btnWord.classList.toggle('active', !isPdf);
        if (btnPdf) btnPdf.classList.toggle('active', isPdf);
        if (panelWord) panelWord.style.display = isPdf ? 'none' : 'block';
        if (panelPdf) panelPdf.style.display = isPdf ? 'block' : 'none';
    }

    function handleBiFileChange(input, chipId, nameId, zoneId) {
        var chip = document.getElementById(chipId);
        var nameEl = document.getElementById(nameId);
        var zone = document.getElementById(zoneId);
        var file = input && input.files && input.files[0] ? input.files[0] : null;

        if (file) {
            if (nameEl) nameEl.textContent = file.name;
            if (chip) chip.classList.add('visible');
            if (zone) zone.classList.add('has-file');
        } else {
            if (nameEl) nameEl.textContent = '';
            if (chip) chip.classList.remove('visible');
            if (zone) zone.classList.remove('has-file');
        }
    }

    function clearBiFile(inputId, chipId, zoneId) {
        var input = document.getElementById(inputId);
        var chip = document.getElementById(chipId);
        var zone = document.getElementById(zoneId);
        if (input) input.value = '';
        if (chip) chip.classList.remove('visible');
        if (zone) zone.classList.remove('has-file');
    }

    document.addEventListener('click', function () {
        var menu = document.getElementById('biSampleMenu');
        if (menu) menu.style.display = 'none';
    });
</script>
@endpush
@endsection

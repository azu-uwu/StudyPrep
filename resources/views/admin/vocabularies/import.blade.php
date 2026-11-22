@extends('layouts.admin', ['active' => 'tu-vung-flashcard'])

@section('title', 'Nhập từ vựng TOEIC hàng loạt')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.vocabularies.index') }}" style="color: inherit; text-decoration: none;">Từ vựng &amp; Flashcards</a>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary, #4f46e5); font-weight: 700;">Nhập từ vựng hàng loạt</span>
@endsection

@section('content')
<div class="qimport-container">

    {{-- Thông báo kết quả sau khi Import / Chỉnh sửa / Xóa --}}
    @if (session('success') || session('info'))
        <div class="alert-admin-success" style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') ?? session('info') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <strong>Đã có lỗi xảy ra:</strong>
            <ul style="margin: 6px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. HEADER --}}
    <div class="qimport-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 22px;">
        <div>
            <div class="qimport-title-row">
                <h1 class="qimport-page-title">Nhập từ vựng hàng loạt</h1>
                <span class="qimport-badge-code">#VOC-IMPORT-2025-06</span>
            </div>
            <div class="qimport-subtext">
                Tải lên tệp Excel (.xlsx), CSV hoặc văn bản (.txt) &rarr; Hệ thống tự động bóc tách từ vựng, phiên âm IPA, nghĩa và ví dụ để bạn Xem trước, Chỉnh sửa hoặc Xóa ngay bên dưới.
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.vocabularies.sample-csv') }}" class="btn-admin-action" style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 10px; background: #eef2ff; color: #4338ca; border: 1.5px solid #c7d2fe; font-weight: 700; font-size: 13.5px; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Tải file Excel/CSV mẫu</span>
            </a>

            <a href="{{ route('admin.vocabularies.index') }}" class="btn-admin-action" style="text-decoration: none;">
                <span>&larr; Quay lại Từ vựng &amp; Flashcards</span>
            </a>
        </div>
    </div>

    {{-- 2. FORM TẢI LÊN FILE & CẤU HÌNH ĐÍCH ĐẾN (Kế thừa trực tiếp như bên Nhập câu hỏi hàng loạt, không còn bước rườm rà) --}}
    <form action="{{ route('admin.vocabularies.import') }}" method="POST" enctype="multipart/form-data" id="bulkImportVocabForm" onsubmit="return validateVocabImportForm()">
        @csrf
        <input type="hidden" name="return_to_import_view" value="1">

        <div class="qimport-top-grid" style="margin-bottom: 24px;">
            {{-- Cột trái: Chọn tệp Excel / CSV / TXT & Cấu trúc chuẩn --}}
            <div class="qimport-card">
                <h2 class="qimport-card-title">
                    <span class="qimport-bar-accent"></span>
                    <span>Chọn tệp từ vựng cần nhập (.csv, .xlsx, .txt)</span>
                </h2>

                <div style="background: #f8fafc; border: 2px dashed #818cf8; border-radius: 14px; padding: 24px 20px; text-align: center; margin-bottom: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div style="font-size: 14.5px; font-weight: 700; color: #1e1b4b; margin-bottom: 6px;">
                        Tải lên tệp Excel (.xlsx), CSV hoặc TXT của bạn
                    </div>
                    <div style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;">
                        Hỗ trợ tự động bóc tách: Từ vựng, Loại từ, Phiên âm IPA, Định nghĩa Anh, Nghĩa tiếng Việt, Câu ví dụ &amp; Dịch
                    </div>
                    <input type="file" name="file" id="vocabFileInput" accept=".csv,.txt,.xlsx" class="form-control-admin" style="max-width: 420px; margin: 0 auto; background: #fff; padding: 9px 12px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 13.5px;" required onchange="onVocabFileSelected(this)">
                    <span id="error-upload-file" class="field-error-text" style="color: #dc2626; font-size: 12px; margin-top: 6px; display: none;"></span>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; font-size: 12.5px; color: #475569; line-height: 1.6;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <strong style="color: #0f172a;">Quy chuẩn 8 cột dữ liệu từ vựng TOEIC ETS:</strong>
                        <a href="{{ route('admin.vocabularies.sample-csv') }}" style="font-size: 11.5px; font-weight: 700; color: #4338ca; background: #eef2ff; border: 1px solid #c7d2fe; padding: 3px 9px; border-radius: 999px; text-decoration: none;">
                            Tải file CSV mẫu chuẩn
                        </a>
                    </div>

                    <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; line-height: 1.55;">
                        <div><strong>Cột 1:</strong> Từ vựng (Word) <span style="color: #dc2626;">*Bắt buộc</span> — ví dụ: <code>substantial</code>, <code>negotiate</code></div>
                        <div><strong>Cột 2:</strong> Loại từ (POS) — ví dụ: <code>Adjective (adj)</code>, <code>Verb (v)</code>, <code>Noun (n)</code></div>
                        <div><strong>Cột 3:</strong> Phiên âm (IPA) — ví dụ: <code>/səbˈstæn.ʃəl/</code>, <code>/nəˈɡoʊ.ʃi.eɪt/</code></div>
                        <div><strong>Cột 4:</strong> Định nghĩa tiếng Anh (Definition) — ví dụ: <code>Large in amount or importance</code></div>
                        <div><strong>Cột 5:</strong> Nghĩa tiếng Việt (Meaning) <span style="color: #dc2626;">*Bắt buộc</span> — ví dụ: <code>Đáng kể, trọng yếu</code></div>
                        <div><strong>Cột 6:</strong> Câu ví dụ tiếng Anh (Example) — ví dụ: <code>The company reported a substantial profit.</code></div>
                        <div><strong>Cột 7:</strong> Dịch câu ví dụ tiếng Việt (Translation) — ví dụ: <code>Công ty báo cáo lợi nhuận đáng kể.</code></div>
                        <div><strong>Cột 8:</strong> Mức điểm mục tiêu (Level) — ví dụ: <code>450+</code>, <code>650+</code>, <code>800+</code></div>
                    </div>

                    <div style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                        Hệ thống tự động bỏ qua dòng tiêu đề, hỗ trợ phân cách bằng dấu phẩy <code>,</code> hoặc chấm phẩy <code>;</code>, tương thích 100% tiếng Việt UTF-8 có dấu.
                    </div>
                </div>
            </div>

            {{-- Cột phải: Cấu hình Bộ thẻ & Xử lý trùng lặp --}}
            <div class="qimport-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h2 class="qimport-card-title">
                        <span class="qimport-bar-accent green"></span>
                        <span>Cấu hình Bộ thẻ &amp; Xử lý trùng lặp</span>
                    </h2>

                    {{-- Chọn bộ thẻ Flashcard đích --}}
                    <div class="qimport-dest-field">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="qimport-dest-label" style="margin: 0;">Gán vào Bộ thẻ Flashcard đích</label>
                            <a href="javascript:void(0)" onclick="openAdminModal('modalCreateDeckFast')" style="font-size: 12px; font-weight: 700; color: #4f46e5; text-decoration: none;">
                                + Tạo bộ thẻ mới
                            </a>
                        </div>
                        <select name="topic_id" id="targetDeckSelect" class="qimport-dest-select">
                            @if (isset($topics) && $topics->isNotEmpty())
                                @foreach ($topics as $t)
                                    <option value="{{ $t->topic_id }}">
                                        {{ $t->name }} ({{ $t->vocabularies_count ?? $t->vocabularies()->count() }} từ)
                                    </option>
                                @endforeach
                            @else
                                <option value="">Bộ thẻ chung hệ thống</option>
                            @endif
                        </select>
                    </div>

                    {{-- Phân nhóm cấp độ mặc định --}}
                    <div class="qimport-dest-field">
                        <label class="qimport-dest-label">Mục tiêu điểm mặc định (Nếu file bỏ trống)</label>
                        <select name="default_level" class="qimport-dest-select">
                            <option value="450+">Target 450+ (Cơ bản / Nền tảng)</option>
                            <option value="650+" selected>Target 650+ (Trung cấp / Tiêu chuẩn TOEIC)</option>
                            <option value="800+">Target 800+ (Nâng cao / Chuyên sâu)</option>
                        </select>
                    </div>

                    {{-- Chế độ xử lý trùng lặp --}}
                    <div class="qimport-dest-field">
                        <label class="qimport-dest-label">Chế độ xử lý khi gặp từ trùng lặp</label>
                        <div class="qimport-radio-wrap">
                            <label class="qimport-radio-card active" id="radioCardSkip">
                                <input type="radio" name="import_mode" value="skip" checked onchange="switchImportMode('skip')">
                                <div class="qimport-radio-text">
                                    <span class="qimport-radio-title">Bỏ qua từ trùng lặp (Khuyến dùng)</span>
                                    <span class="qimport-radio-desc">Giữ nguyên thẻ từ vựng cũ đã có trong hệ thống, chỉ nạp thêm những từ mới</span>
                                </div>
                            </label>

                            <label class="qimport-radio-card" id="radioCardOverwrite">
                                <input type="radio" name="import_mode" value="overwrite" onchange="switchImportMode('overwrite')">
                                <div class="qimport-radio-text">
                                    <span class="qimport-radio-title">Ghi đè &amp; Cập nhật thông tin</span>
                                    <span class="qimport-radio-desc">Cập nhật lại nghĩa, phiên âm và ví dụ mới từ file cho từ vựng đã tồn tại</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="qimport-btn-submit" id="btnSubmitBulk" style="width: 100%; justify-content: center; padding: 12px 20px; font-size: 14.5px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span id="btnSubmitText">Tải lên &amp; Nhập từ vựng</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- 3. DANH SÁCH TỪ VỰNG VỪA IMPORT (CHỈ HIỂN THỊ SAU KHI IMPORT XONG ĐỂ XEM TRƯỚC, CHỈNH SỬA, XÓA) --}}
    @if (isset($importedVocabs) && $importedVocabs->isNotEmpty())
        <div class="user-table-card" style="margin-top: 8px;">
            <div style="padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                <div>
                    <h2 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">
                        Danh sách từ vựng vừa Import ({{ $importedVocabs->count() }} từ)
                    </h2>
                    <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">
                        Bạn có thể <strong>Xem trước</strong>, <strong>Chỉnh sửa nhanh</strong> lại thông tin hoặc <strong>Xóa</strong> bất kỳ từ vựng nào bên dưới.
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.vocabularies.index') }}" class="btn-admin-primary" style="text-decoration: none;">
                        <span>Hoàn tất &amp; Quay lại Từ vựng &amp; Flashcards &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="user-table-scroll">
                <table class="user-data-table">
                    <thead>
                        <tr>
                            <th style="width: 180px;">TỪ VỰNG &amp; PHIÊN ÂM</th>
                            <th style="width: 140px;">TỪ LOẠI &amp; LEVEL</th>
                            <th>NGHĨA TIẾNG VIỆT &amp; ĐỊNH NGHĨA</th>
                            <th>CÂU VÍ DỤ &amp; DỊCH NGHĨA</th>
                            <th style="width: 180px;">BỘ THẺ FLASHCARD</th>
                            <th class="admin-actions-heading" style="width: 110px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($importedVocabs as $vocab)
                            <tr>
                                <td>
                                    <div style="font-size: 14.5px; font-weight: 800; color: #0f172a;">{{ $vocab->word }}</div>
                                    @if ($vocab->pronunciation)
                                        <div style="font-size: 12px; color: #64748b; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; margin-top: 2px;">
                                            {{ $vocab->pronunciation }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                        <span class="vcb-pos-badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                            {{ $vocab->clean_pos ?: 'Từ vựng' }}
                                        </span>
                                        @if ($vocab->target_level)
                                            <span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                                Target {{ $vocab->target_level }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 13.5px;">
                                        {{ $vocab->clean_meaning_vi }}
                                    </div>
                                    @if ($vocab->clean_definition_en)
                                        <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                                            {{ $vocab->clean_definition_en }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($vocab->example_en)
                                        <div style="font-style: italic; color: #334155; font-size: 12.5px;">
                                            "{{ $vocab->example_en }}"
                                        </div>
                                    @endif
                                    @if ($vocab->example_vi)
                                        <div style="color: #64748b; font-size: 12px; margin-top: 2px;">
                                            &rarr; {{ $vocab->example_vi }}
                                        </div>
                                    @endif
                                    @if (!$vocab->example_en && !$vocab->example_vi)
                                        <span style="color: #94a3b8; font-style: italic; font-size: 12px;">Chưa có ví dụ</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                        </svg>
                                        {{ $vocab->topic?->name ?? 'Mặc định' }}
                                    </span>
                                </td>
                                <td class="admin-actions-cell">
                                    <div class="user-actions-cell">
                                        {{-- Nút Chỉnh sửa nhanh --}}
                                        <button type="button"
                                                class="action-icon-btn"
                                                title="Chỉnh sửa từ vựng này"
                                                onclick='openEditImportedVocabModal(@json($vocab), "{{ $vocab->clean_pos }}", "{{ $vocab->target_level }}", "{{ $vocab->clean_meaning_vi }}", "{{ $vocab->clean_definition_en }}")'>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                        </button>

                                        {{-- Nút Xóa từ vựng --}}
                                        <button type="button"
                                                class="action-icon-btn danger"
                                                title="Xóa từ vựng này"
                                                onclick="if (confirm('Bạn có chắc chắn muốn xóa từ vựng \"{{ $vocab->word }}\"?')) { deleteImportedVocab('{{ route('admin.vocabularies.destroy', $vocab->vocabulary_id) }}'); }">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

{{-- Form ẩn xóa từ vựng vừa import --}}
<form id="delete-imported-vocab-form" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="redirect_back" value="1">
</form>

{{-- MODAL CHỈNH SỬA TỪ VỰNG VỪA IMPORT --}}
<div id="modal-edit-imported-vocab" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="edit-vocab-modal-title">Cập nhật từ vựng</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-edit-imported-vocab')">&times;</button>
        </div>
        <form id="form-edit-imported-vocab" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_back" value="1">
            <div class="admin-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group-admin">
                        <label>Từ vựng (Word) <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="word" id="edit_vocab_word" class="form-control-admin" required>
                    </div>
                    <div class="form-group-admin">
                        <label>Phiên âm (IPA)</label>
                        <input type="text" name="pronunciation" id="edit_vocab_pronunciation" class="form-control-admin" placeholder="/.../">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group-admin">
                        <label>Loại từ (POS)</label>
                        <select name="part_of_speech" id="edit_vocab_pos" class="form-control-admin">
                            <option value="Noun (n)">Noun (Danh từ)</option>
                            <option value="Verb (v)">Verb (Động từ)</option>
                            <option value="Adjective (adj)">Adjective (Tính từ)</option>
                            <option value="Adverb (adv)">Adverb (Trạng từ)</option>
                            <option value="Preposition (prep)">Preposition (Giới từ)</option>
                            <option value="Conjunction (conj)">Conjunction (Liên từ)</option>
                            <option value="Phrase">Phrase (Cụm từ)</option>
                        </select>
                    </div>
                    <div class="form-group-admin">
                        <label>Mục tiêu điểm (Level)</label>
                        <select name="level" id="edit_vocab_level" class="form-control-admin">
                            <option value="450+">Target 450+</option>
                            <option value="650+">Target 650+</option>
                            <option value="800+">Target 800+</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-admin">
                    <label>Bộ thẻ Flashcard <span style="color: #dc2626;">*</span></label>
                    <select name="topic_id" id="edit_vocab_topic_id" class="form-control-admin" required>
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group-admin">
                    <label>Nghĩa tiếng Việt <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="meaning_vi" id="edit_vocab_meaning_vi" class="form-control-admin" required>
                </div>

                <div class="form-group-admin">
                    <label>Định nghĩa tiếng Anh</label>
                    <input type="text" name="definition_en" id="edit_vocab_definition_en" class="form-control-admin">
                </div>

                <div class="form-group-admin">
                    <label>Câu ví dụ tiếng Anh</label>
                    <textarea name="example_en" id="edit_vocab_example_en" class="form-control-admin" rows="2"></textarea>
                </div>

                <div class="form-group-admin">
                    <label>Dịch câu ví dụ tiếng Việt</label>
                    <textarea name="example_vi" id="edit_vocab_example_vi" class="form-control-admin" rows="2"></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-edit-imported-vocab')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TẠO BỘ THẺ NHANH --}}
<div id="modalCreateDeckFast" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 480px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Tạo bộ thẻ Flashcard mới</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modalCreateDeckFast')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.vocabularies.topics.store') }}">
            @csrf
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Tên bộ thẻ mới <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="name" class="form-control-admin" placeholder="Ví dụ: ETS TOEIC 2025 Test 1 Vocab" required>
                </div>
                <div class="form-group-admin">
                    <label>Mô tả ngắn</label>
                    <textarea name="description" class="form-control-admin" rows="2" placeholder="Bộ thẻ lưu trữ từ vựng trọng tâm..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modalCreateDeckFast')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Tạo bộ thẻ</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL UPLOAD PHỤ (Tương thích kiểm tra giao diện và fallback) --}}
<div id="modalUploadFile" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Tải lên tệp từ vựng</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modalUploadFile')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.vocabularies.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="return_to_import_view" value="1">
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Chọn bộ thẻ đích</label>
                    <select name="topic_id" class="form-control-admin">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-admin">
                    <label>Chọn file dữ liệu <span style="color: #dc2626;">*</span></label>
                    <input type="file" name="file" accept=".csv, .xlsx, .txt" class="form-control-admin" required>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modalUploadFile')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Tiến hành nạp</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    }

    function closeAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    }

    function switchImportMode(mode) {
        var cardSkip = document.getElementById('radioCardSkip');
        var cardOverwrite = document.getElementById('radioCardOverwrite');
        if (cardSkip && cardOverwrite) {
            cardSkip.classList.toggle('active', mode === 'skip');
            cardOverwrite.classList.toggle('active', mode === 'overwrite');
        }
    }

    function onVocabFileSelected(input) {
        var errSpan = document.getElementById('error-upload-file');
        if (errSpan) {
            errSpan.style.display = 'none';
            errSpan.textContent = '';
        }
        if (input.files && input.files[0]) {
            var fileName = input.files[0].name;
            var btnText = document.getElementById('btnSubmitText');
            if (btnText) {
                btnText.textContent = 'Nhập từ vựng từ tệp ' + fileName;
            }
        }
    }

    function validateVocabImportForm() {
        var fileInput = document.getElementById('vocabFileInput');
        var errSpan = document.getElementById('error-upload-file');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            if (errSpan) {
                errSpan.textContent = 'Vui lòng chọn một tệp Excel (.xlsx), CSV hoặc TXT trước khi bấm Tải lên.';
                errSpan.style.display = 'block';
            }
            return false;
        }
        return true;
    }

    function openEditImportedVocabModal(v, cleanPos, targetLevel, cleanMeaningVi, cleanDefEn) {
        var form = document.getElementById('form-edit-imported-vocab');
        form.action = '{{ url('/admin/vocabularies') }}/' + v.vocabulary_id;
        document.getElementById('edit-vocab-modal-title').innerText = 'Cập nhật từ vựng: ' + (v.word || '');
        document.getElementById('edit_vocab_word').value = v.word || '';
        document.getElementById('edit_vocab_pronunciation').value = v.pronunciation || '';
        document.getElementById('edit_vocab_topic_id').value = v.topic_id || '';
        document.getElementById('edit_vocab_meaning_vi').value = cleanMeaningVi || '';
        document.getElementById('edit_vocab_definition_en').value = cleanDefEn || '';
        document.getElementById('edit_vocab_example_en').value = v.example_en || '';
        document.getElementById('edit_vocab_example_vi').value = v.example_vi || '';

        var posSelect = document.getElementById('edit_vocab_pos');
        if (posSelect) {
            for (var i = 0; i < posSelect.options.length; i++) {
                if (posSelect.options[i].value.toLowerCase().includes(cleanPos.toLowerCase())) {
                    posSelect.selectedIndex = i;
                    break;
                }
            }
        }

        var lvlSelect = document.getElementById('edit_vocab_level');
        if (lvlSelect && targetLevel) {
            lvlSelect.value = targetLevel;
        }

        openAdminModal('modal-edit-imported-vocab');
    }

    function deleteImportedVocab(actionUrl) {
        var form = document.getElementById('delete-imported-vocab-form');
        form.action = actionUrl;
        form.submit();
    }
</script>
@endpush

@extends('layouts.admin', ['active' => 'tu-vung-flashcard'])

@section('title', 'Quản lý Từ vựng & Flashcards')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <span style="color: #4f46e5; font-weight: 700;">Từ vựng & Flashcards</span>
@endsection

@section('content')
<div class="vcb-page">
    {{-- Thông báo kết quả thao tác --}}
    @if (session('success'))
        <div class="alert-admin-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="vcb-header">
        <div class="vcb-title-area">
            <h1 class="vcb-title">Quản lý Từ vựng & Flashcards</h1>
            <div class="vcb-count-badge">
                <span>•</span>
                <span>{{ number_format($totalVocabCount) }} TỪ HỆ THỐNG</span>
                <span>•</span>
                <span>{{ $totalTopicCount }} BỘ THẺ FLASHCARDS</span>
            </div>
        </div>

        <div class="vcb-header-actions">
            {{-- Xuất dữ liệu --}}
            <a href="{{ route('admin.vocabularies.export') }}" class="vcb-btn-secondary" title="Xuất toàn bộ từ vựng ra file CSV">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất dữ liệu</span>
            </a>

            {{-- Nhập file --}}
            <a href="{{ route('admin.vocabularies.import-view') }}" class="vcb-btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Nhập file (Excel/Anki)</span>
            </a>

            {{-- Quản lý bộ thẻ Flashcard (Sửa / Xóa) --}}
            <button type="button" class="vcb-btn-secondary" onclick="openModal('modal-manage-topics')" title="Quản lý, chỉnh sửa, xóa các bộ thẻ Flashcards">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Quản lý bộ thẻ</span>
            </button>

            {{-- Bật / Tắt Flashcard Side Panel --}}
            <button type="button" class="vcb-btn-secondary" onclick="toggleSidePreviewDrawer()" title="Xem nhanh Flashcard ở bảng trượt cạnh phải">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="4" width="14" height="17" rx="2"></rect>
                    <line x1="9" y1="9" x2="15" y2="9"></line>
                    <line x1="9" y1="13" x2="15" y2="13"></line>
                </svg>
                <span>Flashcard Panel</span>
            </button>

            {{-- Tạo bộ thẻ mới --}}
            <button type="button" class="vcb-btn-deck" onclick="openModal('modal-new-topic')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span>+ Tạo bộ thẻ mới</span>
            </button>

            {{-- Thêm từ vựng mới --}}
            <a href="{{ route('admin.vocabularies.create') }}" class="vcb-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>+ Thêm từ vựng mới</span>
            </a>
        </div>
    </div>

    {{-- 2. Bốn thẻ thống kê chỉ số (4 KPI Cards - Đồng bộ chuẩn Admin) --}}
    <div class="vcb-kpi-grid">
        {{-- Card 1: Tổng số từ vựng --}}
        <div class="vcb-kpi-card">
            <div class="vcb-kpi-top">
                <p class="vcb-kpi-title">Tổng số từ vựng</p>
                <div class="vcb-kpi-icon icon-purple" title="Từ vựng TOEIC">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="4 7 4 4 20 4 20 7"></polyline>
                        <line x1="9" y1="20" x2="15" y2="20"></line>
                        <line x1="12" y1="4" x2="12" y2="20"></line>
                    </svg>
                </div>
            </div>
            <div class="vcb-kpi-val-row">
                <span class="vcb-kpi-num">{{ number_format($totalVocabCount) }}</span>
                <span class="vcb-kpi-sub">từ</span>
            </div>
            <div class="vcb-kpi-foot">
                <span>Toàn bộ kho dữ liệu từ vựng hệ thống</span>
            </div>
        </div>

        {{-- Card 2: Bộ thẻ Flashcards mở --}}
        <div class="vcb-kpi-card">
            <div class="vcb-kpi-top">
                <p class="vcb-kpi-title">Bộ thẻ Flashcards mở</p>
                <div class="vcb-kpi-icon icon-blue" title="Bộ thẻ Flashcards">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="4" width="14" height="17" rx="2" transform="rotate(-6 12 12)"></rect>
                        <rect x="6" y="3" width="14" height="17" rx="2"></rect>
                    </svg>
                </div>
            </div>
            <div class="vcb-kpi-val-row">
                <span class="vcb-kpi-num">{{ $totalTopicCount }}</span>
                <span class="vcb-kpi-sub">bộ thẻ</span>
            </div>
            <div class="vcb-kpi-foot">
                <span>Phân loại theo chủ đề và kỹ năng TOEIC</span>
            </div>
        </div>

        {{-- Card 3: Thống kê đã thuộc & thành thạo --}}
        <div class="vcb-kpi-card">
            <div class="vcb-kpi-top">
                <p class="vcb-kpi-title">Từ vựng đã thuộc</p>
                <div class="vcb-kpi-icon icon-green" title="Học viên đã thuộc">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>
            <div class="vcb-kpi-val-row">
                <span class="vcb-kpi-num" style="color: #16a34a;">{{ number_format($totalMasteredCount) }}</span>
                <span class="vcb-kpi-sub">từ</span>
            </div>
            <div class="vcb-kpi-foot">
                <span style="color: #16a34a; font-weight: 600;">({{ number_format($totalMasteredCount) }} đã thuộc)</span>
            </div>
        </div>

        {{-- Card 4: Thống kê học viên tương tác --}}
        <div class="vcb-kpi-card">
            <div class="vcb-kpi-top">
                <p class="vcb-kpi-title">Lượt tương tác học tập</p>
                <div class="vcb-kpi-icon icon-amber" title="Lượt ôn tập">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
            <div class="vcb-kpi-val-row">
                <span class="vcb-kpi-num">{{ number_format($totalLearnersInteractions) }}</span>
                <span class="vcb-kpi-sub">lượt học</span>
            </div>
            <div class="vcb-kpi-foot">
                <span>({{ number_format($totalMasteredCount) }} đã thuộc) • Lượt ôn tập</span>
            </div>
        </div>
    </div>

    {{-- 3. Khung tìm kiếm và bộ lọc đa năng (Modern Filter Card) --}}
    <form method="GET" action="{{ route('admin.vocabularies.index') }}" id="vcbFilterForm" class="vcb-filter-card">
        {{-- Hàng 1: Ô tìm kiếm + Nút Đặt lại --}}
        <div class="vcb-search-row">
            <div class="vcb-search-input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="vcb-search-input"
                    placeholder="Tìm theo Từ vựng (Word), Phiên âm IPA, Nghĩa tiếng Việt, Định nghĩa..."
                    value="{{ $search }}"
                    autocomplete="off"
                    onchange="document.getElementById('vcbFilterForm').submit()"
                >
            </div>

            <a href="{{ route('admin.vocabularies.index') }}" class="qbank-reset-btn" title="Đặt lại toàn bộ bộ lọc">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                <span>Đặt lại</span>
            </a>
        </div>

        {{-- Hàng 2: 3 Dropdowns lọc dữ liệu --}}
        <div class="vcb-filters-grid">
            <div class="vcb-filter-col">
                <label class="vcb-filter-label">TỪ LOẠI (POS)</label>
                <select name="pos" class="vcb-filter-select" onchange="document.getElementById('vcbFilterForm').submit()">
                    <option value="all" {{ $posFilter === 'all' ? 'selected' : '' }}>Tất cả từ loại (POS)</option>
                    <option value="Noun" {{ $posFilter === 'Noun' ? 'selected' : '' }}>Danh từ (Noun)</option>
                    <option value="Verb" {{ $posFilter === 'Verb' ? 'selected' : '' }}>Động từ (Verb)</option>
                    <option value="Adj" {{ $posFilter === 'Adj' ? 'selected' : '' }}>Tính từ (Adj)</option>
                    <option value="Adv" {{ $posFilter === 'Adv' ? 'selected' : '' }}>Phó từ (Adv)</option>
                    <option value="Collocation" {{ $posFilter === 'Collocation' ? 'selected' : '' }}>Collocation</option>
                    <option value="Noun Phrase" {{ $posFilter === 'Noun Phrase' ? 'selected' : '' }}>Noun Phrase</option>
                </select>
            </div>

            <div class="vcb-filter-col">
                <label class="vcb-filter-label">MỤC TIÊU / LEVEL</label>
                <select name="level" class="vcb-filter-select" onchange="document.getElementById('vcbFilterForm').submit()">
                    <option value="all" {{ $levelFilter === 'all' ? 'selected' : '' }}>Tất cả mục tiêu điểm</option>
                    <option value="450+" {{ $levelFilter === '450+' ? 'selected' : '' }}>Target 450+ (Căn bản)</option>
                    <option value="550+" {{ $levelFilter === '550+' ? 'selected' : '' }}>Target 550+ (Trung cấp)</option>
                    <option value="650+" {{ $levelFilter === '650+' ? 'selected' : '' }}>Target 650+ (Khá)</option>
                    <option value="750+" {{ $levelFilter === '750+' ? 'selected' : '' }}>Target 750+ (Nâng cao)</option>
                    <option value="800+" {{ $levelFilter === '800+' ? 'selected' : '' }}>Target 800+ (Chuyên sâu)</option>
                    <option value="900+" {{ $levelFilter === '900+' ? 'selected' : '' }}>Target 900+ (Mastery)</option>
                </select>
            </div>

            <div class="vcb-filter-col">
                <label class="vcb-filter-label">BỘ THẺ FLASHCARD</label>
                <select name="topic_id" class="vcb-filter-select" onchange="document.getElementById('vcbFilterForm').submit()">
                    <option value="all" {{ $topicFilter === 'all' ? 'selected' : '' }}>Tất cả bộ thẻ Flashcards</option>
                    @foreach ($topics as $t)
                        <option value="{{ $t->topic_id }}" {{ (string)$topicFilter === (string)$t->topic_id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Main 2-Column Layout --}}
    <div class="vcb-main-grid">
        {{-- Left Column: Vocabulary Table --}}
        <div class="vcb-left-col">
            {{-- Bulk action bar --}}
            <form id="vcbBulkForm" method="POST" action="{{ route('admin.vocabularies.bulk-action') }}">
                @csrf
                <input type="hidden" name="action" id="bulkActionInput" value="">
                <input type="hidden" name="target_topic_id" id="bulkTargetTopicInput" value="">

                <div class="vcb-bulk-bar">
                    <div class="vcb-bulk-left">
                        <input type="checkbox" id="vcbSelectAll" class="vcb-checkbox-custom" onclick="toggleSelectAll(this)">
                        <label for="vcbSelectAll" style="cursor: pointer;">
                            Chọn tất cả ({{ $vocabularies->count() }} trên trang này)
                        </label>
                        <span>•</span>
                        <span id="vcbSelectedCountBadge" style="color: #64748b;">Đã chọn 1 mục</span>
                    </div>

                    <div class="vcb-bulk-actions">
                        <button type="button" class="btn-admin-action" onclick="toggleSidePreviewDrawer()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9px; font-size: 13px; font-weight: 700; background: #ffffff; color: #4338ca; border: 1.5px solid #c7d2fe; cursor: pointer;" title="Bật/Tắt bảng xem trước Flashcard ở cạnh phải">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="4" width="14" height="17" rx="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line></svg>
                            <span>Xem Flashcard</span>
                        </button>
                        <button type="button" class="vcb-btn-bulk-assign" onclick="openBulkAssignModal()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span>Gán vào bộ thẻ</span>
                        </button>
                        <button type="button" class="vcb-btn-bulk-delete" onclick="submitBulkDelete()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            <span>Xóa</span>
                        </button>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="vcb-table-card">
                    <div class="vcb-table-wrap">
                        <table class="vcb-table" id="vcbMainTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="thSelectAll" class="vcb-checkbox-custom" onclick="document.getElementById('vcbSelectAll').click()">
                                    </th>
                                    <th>TỪ VỰNG & IPA</th>
                                    <th>NGHĨA & ĐỊNH NGHĨA</th>
                                    <th style="text-align: center;">ẢNH</th>
                                    <th>NGỮ CẢNH TOEIC & VÍ DỤ</th>
                                    <th>BỘ THẺ FLASHCARD</th>
                                    <th>HỌC VIÊN</th>
                                    <th class="vcb-actions-col">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vocabularies as $index => $item)
                                    @php
                                        $isSelected = ($previewVocab && $previewVocab->vocabulary_id === $item->vocabulary_id) || ($index === 0 && !$previewVocab);

                                        $itemData = [
                                            'id' => $item->vocabulary_id,
                                            'word' => $item->word,
                                            'pronunciation' => $item->pronunciation ?? '',
                                            'pos' => $item->clean_pos,
                                            'level' => $item->target_level,
                                            'topic_id' => $item->topic_id ?? ($topics->first()->topic_id ?? 1),
                                            'topic_name' => $item->topic?->name ?? 'Mặc định',
                                            'meaning_vi' => $item->clean_meaning_vi,
                                            'definition_en' => $item->clean_definition_en,
                                            'example_en' => $item->example_en ?? '',
                                            'example_vi' => $item->example_vi ?? '',
                                            'image_url' => $item->image_url,
                                            'learners_count' => $item->learners_count ?? 0,
                                            'mastered_count' => $item->mastered_count ?? 0,
                                        ];
                                    @endphp
                                    <tr class="vcb-row-item {{ $isSelected ? 'vcb-row-selected' : '' }}"
                                        data-id="{{ $item->vocabulary_id }}"
                                        onclick="selectVocabItem({{ \Illuminate\Support\Js::from($itemData) }}, this)">
                                        {{-- Checkbox --}}
                                        <td style="text-align: center;" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $item->vocabulary_id }}" class="vcb-checkbox-custom vcb-item-check" {{ $isSelected ? 'checked' : '' }} onchange="updateSelectedCount()">
                                        </td>

                                        {{-- TỪ VỰNG & IPA --}}
                                        <td class="vcb-word-col">
                                            <div class="vcb-word-header">
                                                <span class="vcb-word-title">{{ $item->word }}</span>
                                                <button type="button" class="vcb-audio-btn" title="Phát âm" onclick="event.stopPropagation(); playAudio({{ \Illuminate\Support\Js::from($item->word) }})">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="vcb-ipa-text">{{ $item->pronunciation ?? '/.../' }}</div>
                                            <div class="vcb-tags-row">
                                                <span class="vcb-pos-badge">{{ $item->clean_pos }}</span>
                                                @if (!empty($item->target_level))
                                                    <span class="vcb-lvl-badge">{{ $item->target_level }}</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- NGHĨA & ĐỊNH NGHĨA --}}
                                        <td class="vcb-meaning-col">
                                            <div class="vcb-meaning-vi">{{ $item->clean_meaning_vi }}</div>
                                            @if ($item->clean_definition_en)
                                                <div class="vcb-definition-en">{{ $item->clean_definition_en }}</div>
                                            @endif
                                        </td>

                                        {{-- ẢNH --}}
                                        <td class="vcb-img-col">
                                            @if ($item->image_url)
                                                <img src="{{ $item->image_url }}" alt="{{ $item->word }}" class="vcb-img-thumb">
                                            @else
                                                <span>—</span>
                                            @endif
                                        </td>

                                        {{-- NGỮ CẢNH TOEIC & VÍ DỤ --}}
                                        <td class="vcb-example-col">
                                            @if ($item->example_en)
                                                <div class="vcb-example-en">"{{ $item->example_en }}"</div>
                                            @endif
                                            @if ($item->example_vi)
                                                <div class="vcb-example-vi">{{ $item->example_vi }}</div>
                                            @endif
                                        </td>

                                        {{-- BỘ THẺ FLASHCARD --}}
                                        <td class="vcb-deck-col">
                                            <span class="vcb-deck-badge">{{ $item->topic?->name ?? 'Bộ thẻ chung' }}</span>
                                        </td>

                                        {{-- HỌC VIÊN TIẾN ĐỘ --}}
                                        <td class="vcb-learner-col" style="white-space: nowrap;">
                                            <div style="font-size: 13px; font-weight: 700; color: #1e1b4b; display: flex; align-items: center; gap: 4px;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                                <span>{{ number_format($item->learners_count ?? 0) }} học viên</span>
                                            </div>
                                            <div style="font-size: 11.5px; color: #16a34a; font-weight: 600; margin-top: 2px;">
                                                ✓ {{ number_format($item->mastered_count ?? 0) }} đã ghi nhớ
                                            </div>
                                        </td>

                                        {{-- THAO TÁC --}}
                                        <td class="vcb-actions-col" onclick="event.stopPropagation()">
                                            <div class="vcb-actions-wrap">
                                                {{-- Nút xem trước (Eye icon) --}}
                                                <button type="button" class="vcb-action-btn" title="Xem trước & Chi tiết Flashcard" onclick="openVocabPreviewModal({{ \Illuminate\Support\Js::from($itemData) }}, this.closest('tr'))">
                                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </button>

                                                {{-- Nút Chỉnh sửa (Pen icon) --}}
                                                <button type="button" class="vcb-action-btn" title="Chỉnh sửa từ vựng" onclick='openEditVocabModal(@json($itemData))'>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                    </svg>
                                                </button>

                                                {{-- Option menu (3-dots dropdown) --}}
                                                <div class="vcb-dropdown">
                                                    <button type="button" class="vcb-action-btn vcb-dropdown-trigger" title="Thao tác khác" onclick="toggleVcbDropdown(this, event)">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="1.5"></circle>
                                                            <circle cx="12" cy="5" r="1.5"></circle>
                                                            <circle cx="12" cy="19" r="1.5"></circle>
                                                        </svg>
                                                    </button>
                                                    <div class="vcb-dropdown-menu">
                                                        <button type="button" class="vcb-dropdown-item" onclick="openModal('modal-new-vocab')">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                                            </svg>
                                                            <span>Thêm từ vựng mới</span>
                                                        </button>
                                                        <button type="button" class="vcb-dropdown-item" onclick='openEditVocabModal(@json($itemData))'>
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                            </svg>
                                                            <span>Chỉnh sửa thông tin</span>
                                                        </button>
                                                        <button type="button" class="vcb-dropdown-item" onclick="quickAssignSingle({{ $item->vocabulary_id }})">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                                            </svg>
                                                            <span>Đổi bộ thẻ (Topic)</span>
                                                        </button>
                                                        <div class="vcb-dropdown-divider"></div>
                                                        <form method="POST" action="{{ route('admin.vocabularies.destroy', $item->vocabulary_id) }}" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa từ vựng &quot;{{ addslashes($item->word) }}&quot; khỏi hệ thống?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="vcb-dropdown-item text-danger">
                                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                                </svg>
                                                                <span>Xóa từ vựng</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                                            Không tìm thấy từ vựng phù hợp với bộ lọc hiện tại.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Footer / Pagination --}}
                    <div class="vcb-table-footer">
                        <div class="vcb-footer-left">
                            Hiển thị {{ $vocabularies->firstItem() ?? 0 }}–{{ $vocabularies->lastItem() ?? 0 }} trong tổng số {{ number_format($vocabularies->total()) }} từ
                        </div>

                        <div class="vcb-footer-mid">
                            <span>Dòng/trang:</span>
                            <select name="per_page" class="vcb-perpage-select" onchange="document.getElementById('vcbFilterForm').per_page = this.value; document.getElementById('vcbFilterForm').submit();">
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <div class="vcb-pagination-wrap">
                            @if ($vocabularies->onFirstPage())
                                <span class="vcb-page-btn disabled">|&lt;</span>
                                <span class="vcb-page-btn disabled">&lt;</span>
                            @else
                                <a href="{{ $vocabularies->url(1) }}" class="vcb-page-btn">|&lt;</a>
                                <a href="{{ $vocabularies->previousPageUrl() }}" class="vcb-page-btn">&lt;</a>
                            @endif

                            <span class="vcb-page-btn active">1</span>
                            <a href="{{ $vocabularies->url(2) }}" class="vcb-page-btn">2</a>
                            <a href="{{ $vocabularies->url(3) }}" class="vcb-page-btn">3</a>
                            <span class="vcb-page-btn disabled">...</span>
                            <a href="{{ $vocabularies->url(1845) }}" class="vcb-page-btn">1,845</a>

                            @if ($vocabularies->hasMorePages())
                                <a href="{{ $vocabularies->nextPageUrl() }}" class="vcb-page-btn">&gt;</a>
                                <a href="{{ $vocabularies->url($vocabularies->lastPage()) }}" class="vcb-page-btn">&gt;|</a>
                            @else
                                <span class="vcb-page-btn disabled">&gt;</span>
                                <span class="vcb-page-btn disabled">&gt;|</span>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

{{-- BACKDROP & SLIDE DRAWER CHO FLASHCARD LIVE PREVIEW --}}
<div class="vcb-drawer-backdrop" id="vcbDrawerBackdrop" onclick="closeSidePreviewDrawer()"></div>

<div class="vcb-side-drawer" id="vcbSideDrawer" role="dialog" aria-labelledby="sideDrawerTitle">
    <div class="vcb-drawer-head">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="4" width="14" height="17" rx="2"></rect>
                <line x1="9" y1="9" x2="15" y2="9"></line>
                <line x1="9" y1="13" x2="15" y2="13"></line>
            </svg>
            <span id="sideDrawerTitle" style="font-size: 16px; font-weight: 800; color: #0f172a;">Xem trước Flashcard</span>
            <span class="vcb-badge-live">LIVE</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="vcb-scroll-btn" onclick="openVocabPreviewModal()" title="Phóng to Modal toàn màn hình">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <polyline points="9 21 3 21 3 15"></polyline>
                    <line x1="21" y1="3" x2="14" y2="10"></line>
                    <line x1="3" y1="21" x2="10" y2="14"></line>
                </svg>
            </button>
            <button type="button" class="vcb-drawer-close" onclick="closeSidePreviewDrawer()" title="Đóng ngăn kéo">
                &times;
            </button>
        </div>
    </div>

    <div style="padding: 18px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 12.5px; color: #64748b;">Mô phỏng trải nghiệm học viên:</span>
            <div class="vcb-preview-scroll-actions" title="Duyệt qua lại giữa các từ vựng">
                <button type="button" class="vcb-scroll-btn" onclick="navigateVocab(-1)" title="Từ trước đó (←)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
                <button type="button" class="vcb-scroll-btn" onclick="navigateVocab(1)" title="Từ tiếp theo (→)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        </div>

        {{-- Flashcard Simulator inside Drawer --}}
        <div class="vcb-flashcard-sim" style="box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08); border-radius: 16px;">
            <div class="vcb-sim-toolbar">
                <div class="vcb-sim-tabs">
                    <button type="button" class="vcb-sim-tab active" id="tabFront" onclick="showCardFace(false)">Mặt trước</button>
                    <button type="button" class="vcb-sim-tab" id="tabBack" onclick="showCardFace(true)">Mặt sau</button>
                </div>
                <button type="button" class="vcb-sim-flip-btn" onclick="toggleCardFlip()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Lật thẻ</span>
                </button>
            </div>

            <div class="vcb-card-viewport" onclick="toggleCardFlip()" style="cursor: pointer;">
                <div class="vcb-card-flipper" id="cardFlipper">
                    {{-- Mặt trước --}}
                    <div class="vcb-card-face vcb-card-front">
                        <img id="simCardImg" @if ($previewVocab?->image_url) src="{{ $previewVocab->image_url }}" @else hidden @endif alt="{{ $previewVocab?->word ?? '' }}" class="vcb-card-banner-img">
                        <div class="vcb-sim-word-row">
                            <span class="vcb-sim-word" id="simWord">{{ $previewVocab?->word ?? 'Chưa có từ' }}</span>
                            <button type="button" class="vcb-audio-btn" onclick="event.stopPropagation(); playAudio(document.getElementById('simWord').innerText)" title="Nghe phát âm chuẩn">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                            </button>
                        </div>
                        <div class="vcb-sim-ipa" id="simIpa">{{ $previewVocab?->pronunciation ?? '' }}</div>
                        <div class="vcb-sim-badges">
                            <span class="vcb-pos-badge" id="simPos">{{ $previewVocab?->clean_pos ?: 'Từ vựng' }}</span>
                            <span class="vcb-lvl-badge" id="simLvl">Target {{ $previewVocab?->target_level ?: '650+' }}</span>
                        </div>
                        <div class="vcb-sim-hint">Bấm vào thẻ hoặc "Lật thẻ" để xem nghĩa</div>
                    </div>

                    {{-- Mặt sau --}}
                    <div class="vcb-card-face vcb-card-back">
                        <div class="vcb-back-meaning" id="simMeaning">{{ $previewVocab?->clean_meaning_vi ?? '' }}</div>
                        <div class="vcb-back-def" id="simDef">{{ $previewVocab?->clean_definition_en ?? '' }}</div>
                        <div class="vcb-back-example-box">
                            <div class="vcb-back-example-en" id="simExEn">"{{ $previewVocab?->example_en ?? '' }}"</div>
                            <div class="vcb-back-example-vi" id="simExVi">→ {{ $previewVocab?->example_vi ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="vcb-sm2-bar" style="margin-top: 14px;">
                <button type="button" class="vcb-sm2-btn" onclick="rateSM2(1, 'Chưa thuộc')">Chưa thuộc<br><span style="font-size: 10px; font-weight: normal;">(1 ngày)</span></button>
                <button type="button" class="vcb-sm2-btn" onclick="rateSM2(3, 'Khó')">Khó<br><span style="font-size: 10px; font-weight: normal;">(3 ngày)</span></button>
                <button type="button" class="vcb-sm2-btn vcb-sm2-green" onclick="rateSM2(7, 'Đã thuộc')">Đã thuộc<br><span style="font-size: 10px; font-weight: normal;">(7 ngày)</span></button>
            </div>
        </div>
    </div>
</div>

    {{-- Floating Mobile Preview Bar (Dành cho màn hình nhỏ <= 1200px) --}}
    <div class="vcb-mobile-preview-bar" id="vcbMobilePreviewBar" onclick="openVocabPreviewModal()">
        <div class="vcb-mobile-preview-content">
            <div class="vcb-mobile-preview-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="4" width="14" height="17" rx="2" transform="rotate(-6 12 12)"></rect>
                    <rect x="6" y="3" width="14" height="17" rx="2"></rect>
                </svg>
            </div>
            <div class="vcb-mobile-preview-text">
                Xem trước thẻ: <strong id="mobilePreviewWord">{{ $previewVocab?->word ?? ($vocabularies->first()?->word ?? 'Flashcard') }}</strong>
            </div>
        </div>
        <button type="button" class="vcb-btn-mobile-open">
            Mở xem trước &rarr;
        </button>
    </div>
</div>

{{-- MODAL XEM CHI TIẾT & XEM TRƯỚC FLASHCARD (Dành cho mọi kích thước thiết bị & cuộn/duyệt qua lại) --}}
<div id="modal-preview-vocab" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog" style="max-width: 720px; width: 95%;">
        <div class="vcb-modal-head" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="4" width="14" height="17" rx="2" transform="rotate(-6 12 12)"></rect>
                        <rect x="6" y="3" width="14" height="17" rx="2"></rect>
                    </svg>
                </div>
                <div>
                    <h3 class="vcb-modal-title" style="font-size: 16.5px; font-weight: 800; color: #0f172a;">Chi tiết &amp; Xem trước Flashcard</h3>
                    <div style="font-size: 12px; color: #64748b;">Mô phỏng trải nghiệm lật thẻ học 2 mặt của học viên</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="vcb-badge-live">LIVE PREVIEW</span>
                <button type="button" class="vcb-modal-close" onclick="closeModal('modal-preview-vocab')">&times;</button>
            </div>
        </div>

        <div class="vcb-modal-body" style="padding: 20px 22px; max-height: 80vh; overflow-y: auto;">
            {{-- Flashcard Simulation inside Modal --}}
            <div style="max-width: 440px; margin: 0 auto 20px auto;">
                <div class="vcb-sim-toolbar" style="margin-bottom: 10px;">
                    <div class="vcb-sim-tabs">
                        <button type="button" class="vcb-sim-tab active" id="modalTabFront" onclick="showModalCardFace(false)">Mặt trước</button>
                        <button type="button" class="vcb-sim-tab" id="modalTabBack" onclick="showModalCardFace(true)">Mặt sau</button>
                    </div>

                    <button type="button" class="vcb-sim-flip-btn" onclick="toggleModalCardFlip()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <polyline points="1 20 1 14 7 14"></polyline>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                        <span>Lật thẻ</span>
                    </button>
                </div>

                {{-- 3D Flip Viewport inside Modal --}}
                <div class="vcb-card-viewport" onclick="toggleModalCardFlip()" style="cursor: pointer; min-height: 220px;">
                    <div class="vcb-card-flipper" id="modalCardFlipper">
                        {{-- Mặt trước --}}
                        <div class="vcb-card-face vcb-card-front" style="padding: 24px 20px;">
                            <img id="modalSimCardImg" hidden alt="" class="vcb-card-banner-img" style="max-height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">

                            <div class="vcb-sim-word-row">
                                <span class="vcb-sim-word" id="modalSimWord" style="font-size: 26px;">Word</span>
                                <button type="button" class="vcb-audio-btn" onclick="event.stopPropagation(); playAudio(document.getElementById('modalSimWord').innerText)" title="Nghe phát âm chuẩn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="vcb-sim-ipa" id="modalSimIpa" style="font-size: 15px; margin-bottom: 12px;">/ipa/</div>

                            <div class="vcb-sim-badges">
                                <span class="vcb-pos-badge" id="modalSimPos">POS</span>
                                <span class="vcb-lvl-badge" id="modalSimLvl">Target Level</span>
                            </div>

                            <div class="vcb-sim-hint" style="margin-top: 14px;">
                                Bấm vào thẻ để lật sang mặt sau (Nghĩa &amp; Ví dụ)
                            </div>
                        </div>

                        {{-- Mặt sau --}}
                        <div class="vcb-card-face vcb-card-back" style="padding: 22px 20px; text-align: left;">
                            <div class="vcb-back-meaning" id="modalSimMeaning" style="font-size: 17px; margin-bottom: 8px;">Nghĩa</div>
                            <div class="vcb-back-def" id="modalSimDef" style="font-size: 13px; margin-bottom: 14px;">Definition</div>

                            <div class="vcb-back-example-box" style="padding: 12px 14px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                                <div class="vcb-back-example-en" id="modalSimExEn" style="font-size: 13px;">Example</div>
                                <div class="vcb-back-example-vi" id="modalSimExVi" style="font-size: 12.5px; margin-top: 4px;">Dịch nghĩa</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thông tin chi tiết mở rộng --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 18px;">
                <h4 style="font-size: 13.5px; font-weight: 800; color: #1e293b; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.03em;">
                    Dữ liệu chi tiết từ vựng trong hệ thống
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13px;">
                    <div>
                        <span style="color: #64748b; font-size: 12px;">Bộ thẻ hiện tại:</span>
                        <div id="modalDetailTopic" style="font-weight: 700; color: #1e1b4b; margin-top: 2px;">--</div>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 12px;">Mục tiêu điểm thi (Target):</span>
                        <div id="modalDetailLevel" style="font-weight: 700; color: #047857; margin-top: 2px;">--</div>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 12px;">Lượt học viên tương tác:</span>
                        <div id="modalDetailLearners" style="font-weight: 700; color: #4338ca; margin-top: 2px;">--</div>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 12px;">Số học viên đã thuộc từ này:</span>
                        <div id="modalDetailMastered" style="font-weight: 700; color: #16a34a; margin-top: 2px;">--</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="vcb-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 8px;">
                <button type="button" class="vcb-btn-secondary" onclick="navigateModalVocab(-1)" title="Chuyển sang từ trước">
                    &larr; Từ trước
                </button>
                <button type="button" class="vcb-btn-secondary" onclick="navigateModalVocab(1)" title="Chuyển sang từ tiếp theo">
                    Từ tiếp theo &rarr;
                </button>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="vcb-btn-secondary" onclick="editCurrentModalVocab()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                    <span>Chỉnh sửa</span>
                </button>
                <button type="button" class="vcb-btn-primary" onclick="closeModal('modal-preview-vocab')">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: THÊM TỪ VỰNG MỚI --}}
<div id="modal-new-vocab" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Thêm từ vựng mới vào hệ thống</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-new-vocab')">&times;</button>
        </div>
        <form id="form-new-vocab" method="POST" action="{{ route('admin.vocabularies.store') }}" onsubmit="return validateNewVocabForm(event)">
            @csrf
            <div class="vcb-modal-body">
                <div class="vcb-form-row-2">
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Từ vựng (Word) <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="new_vocab_word" name="word" class="vcb-form-control @error('word') is-invalid @enderror" required placeholder="Ví dụ: extraordinary" value="{{ old('word') }}" oninput="clearFieldError(this)">
                        <span class="field-error-text text-danger" id="error-new-vocab-word" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: {{ $errors->has('word') ? 'block' : 'none' }};">{{ $errors->first('word') }}</span>
                    </div>
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Phiên âm IPA</label>
                        <input type="text" id="new_vocab_pronunciation" name="pronunciation" class="vcb-form-control" placeholder="Ví dụ: /ɪkˈstrɔːr.dən.er.i/" value="{{ old('pronunciation') }}" oninput="clearFieldError(this)">
                    </div>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Từ loại (POS)</label>
                    <select name="part_of_speech" class="vcb-form-control">
                        <option value="Noun" {{ old('part_of_speech') === 'Noun' ? 'selected' : '' }}>Danh từ (Noun)</option>
                        <option value="Verb" {{ old('part_of_speech') === 'Verb' ? 'selected' : '' }}>Động từ (Verb)</option>
                        <option value="Adj" {{ old('part_of_speech') === 'Adj' ? 'selected' : '' }}>Tính từ (Adj)</option>
                        <option value="Adv" {{ old('part_of_speech', 'Adv') === 'Adv' ? 'selected' : '' }}>Phó từ (Adv)</option>
                        <option value="Collocation" {{ old('part_of_speech') === 'Collocation' ? 'selected' : '' }}>Collocation</option>
                        <option value="Noun Phrase" {{ old('part_of_speech') === 'Noun Phrase' ? 'selected' : '' }}>Noun Phrase</option>
                    </select>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Bộ thẻ Flashcard (Topic) <span style="color: #ef4444;">*</span></label>
                    <select name="topic_id" class="vcb-form-control">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}" {{ old('topic_id') == $t->topic_id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Nghĩa tiếng Việt <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="new_vocab_meaning_vi" name="meaning_vi" class="vcb-form-control @error('meaning_vi') is-invalid @enderror" required placeholder="Ví dụ: Đặc biệt, phi thường" value="{{ old('meaning_vi') }}" oninput="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-new-vocab-meaning_vi" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: {{ $errors->has('meaning_vi') ? 'block' : 'none' }};">{{ $errors->first('meaning_vi') }}</span>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Định nghĩa tiếng Anh (English Definition)</label>
                    <input type="text" name="definition_en" class="vcb-form-control" placeholder="Ví dụ: To a very unusual or remarkable degree" value="{{ old('definition_en') }}">
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">URL hình ảnh</label>
                    <input type="url" name="image_url" id="new_vocab_image_url" class="vcb-form-control @error('image_url') is-invalid @enderror" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}" oninput="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-new-vocab-image_url" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: {{ $errors->has('image_url') ? 'block' : 'none' }};">{{ $errors->first('image_url') }}</span>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Ví dụ ngữ cảnh TOEIC (Tiếng Anh)</label>
                    <textarea name="example_en" rows="2" class="vcb-form-control" placeholder="Ví dụ: The quarter report demonstrated an extraordinarily high return on investment.">{{ old('example_en') }}</textarea>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Dịch ví dụ (Tiếng Việt)</label>
                    <textarea name="example_vi" rows="2" class="vcb-form-control" placeholder="Ví dụ: Báo cáo quý cho thấy tỷ suất sinh lời đặc biệt cao.">{{ old('example_vi') }}</textarea>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-new-vocab')">Hủy bỏ</button>
                <button type="submit" class="vcb-btn-primary">Lưu từ vựng</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: TẠO BỘ THẺ MỚI --}}
<div id="modal-new-topic" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Tạo bộ thẻ Flashcard mới</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-new-topic')">&times;</button>
        </div>
        <form id="form-new-topic" method="POST" action="{{ route('admin.vocabularies.topics.store') }}" onsubmit="return validateNewTopicForm(event)">
            @csrf
            <div class="vcb-modal-body">
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Tên bộ thẻ Flashcard <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" id="new_topic_name" class="vcb-form-control @error('name') is-invalid @enderror" required placeholder="Ví dụ: Marketing & Promotion 2026" value="{{ old('name') }}" oninput="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-new-topic-name" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: {{ $errors->has('name') ? 'block' : 'none' }};">{{ $errors->first('name') }}</span>
                </div>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Mô tả bộ thẻ</label>
                    <textarea name="description" rows="3" class="vcb-form-control" placeholder="Mô tả nội dung, chủ đề hoặc mục tiêu điểm số của bộ thẻ">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-new-topic')">Hủy bỏ</button>
                <button type="submit" class="vcb-btn-primary">Tạo bộ thẻ</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: NHẬP FILE EXCEL / ANKI --}}
<div id="modal-import" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Nhập từ vựng hàng loạt (CSV / TXT)</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-import')">&times;</button>
        </div>
        <form id="form-import-vocab" method="POST" action="{{ route('admin.vocabularies.import') }}" enctype="multipart/form-data" onsubmit="return validateImportForm(event)">
            @csrf
            <div class="vcb-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                    Tải lên file danh sách từ vựng định dạng <strong>.CSV</strong> hoặc <strong>.TXT</strong> theo thứ tự các cột: <code>Word, POS, IPA, Definition, Meaning, Example, Translation, Level</code>.
                </p>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chọn bộ thẻ đích <span style="color: #ef4444;">*</span></label>
                    <select name="topic_id" id="import_topic_id" class="vcb-form-control">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chế độ xử lý trùng lặp</label>
                    <select name="import_mode" class="vcb-form-control">
                        <option value="skip">Bỏ qua từ đã tồn tại (Giữ nguyên)</option>
                        <option value="overwrite">Ghi đè cập nhật nội dung mới</option>
                    </select>
                </div>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chọn tệp dữ liệu (.CSV, .TXT) <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="file" id="import_file_input" accept=".csv, .txt, .xlsx" class="vcb-form-control @error('file') is-invalid @enderror" required onchange="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-import-file" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: {{ $errors->has('file') ? 'block' : 'none' }};">{{ $errors->first('file') }}</span>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-import')">Đóng</button>
                <button type="submit" class="vcb-btn-primary">Bắt đầu nhập</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: GÁN BỘ THẺ HÀNG LOẠT --}}
<div id="modal-bulk-assign" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Gán từ vựng đã chọn vào bộ thẻ</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-bulk-assign')">&times;</button>
        </div>
        <div class="vcb-modal-body">
            <div class="vcb-form-group">
                <label class="vcb-form-label">Chọn bộ thẻ mục tiêu</label>
                <select id="modalAssignTopicSelect" class="vcb-form-control">
                    @foreach ($topics as $t)
                        <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="vcb-modal-footer">
            <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-bulk-assign')">Hủy</button>
            <button type="button" class="vcb-btn-primary" onclick="confirmBulkAssign()">Gán ngay</button>
        </div>
    </div>
</div>

{{-- MODAL 5: CHỈNH SỬA TỪ VỰNG --}}
<div id="modal-edit-vocab" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Chỉnh sửa từ vựng</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-edit-vocab')">&times;</button>
        </div>
        <form id="form-edit-vocab" method="POST" action="" onsubmit="return validateEditVocabForm(event)">
            @csrf
            @method('PUT')
            <div class="vcb-modal-body">
                <div class="vcb-form-row-2">
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Từ vựng (Word) <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="edit_vocab_word" name="word" class="vcb-form-control" required placeholder="Ví dụ: extraordinary" oninput="clearFieldError(this)">
                        <span class="field-error-text text-danger" id="error-edit-vocab-word" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: none;"></span>
                    </div>
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Phiên âm IPA</label>
                        <input type="text" id="edit_vocab_ipa" name="pronunciation" class="vcb-form-control" placeholder="Ví dụ: /ɪkˈstrɔːr.dən.er.i/">
                    </div>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Từ loại (POS)</label>
                    <select id="edit_vocab_pos" name="part_of_speech" class="vcb-form-control">
                        <option value="Noun">Danh từ (Noun)</option>
                        <option value="Verb">Động từ (Verb)</option>
                        <option value="Adj">Tính từ (Adj)</option>
                        <option value="Adv">Phó từ (Adv)</option>
                        <option value="Collocation">Collocation</option>
                        <option value="Noun Phrase">Noun Phrase</option>
                    </select>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Bộ thẻ Flashcard (Topic) <span style="color: #ef4444;">*</span></label>
                    <select id="edit_vocab_topic" name="topic_id" class="vcb-form-control" required>
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Nghĩa tiếng Việt <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="edit_vocab_meaning_vi" name="meaning_vi" class="vcb-form-control" required placeholder="Ví dụ: Đặc biệt, phi thường" oninput="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-edit-vocab-meaning_vi" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: none;"></span>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Định nghĩa tiếng Anh (English Definition)</label>
                    <input type="text" id="edit_vocab_def_en" name="definition_en" class="vcb-form-control" placeholder="Ví dụ: To a very unusual or remarkable degree">
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">URL hình ảnh</label>
                    <input type="url" id="edit_vocab_image_url" name="image_url" class="vcb-form-control" placeholder="https://example.com/image.jpg" oninput="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-edit-vocab-image_url" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: none;"></span>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Ví dụ ngữ cảnh TOEIC (Tiếng Anh)</label>
                    <textarea id="edit_vocab_example_en" name="example_en" rows="2" class="vcb-form-control" placeholder="Ví dụ: The quarter report demonstrated an extraordinarily high return on investment."></textarea>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Dịch ví dụ (Tiếng Việt)</label>
                    <textarea id="edit_vocab_example_vi" name="example_vi" rows="2" class="vcb-form-control" placeholder="Ví dụ: Báo cáo quý cho thấy tỷ suất sinh lời đặc biệt cao."></textarea>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-edit-vocab')">Hủy bỏ</button>
                <button type="submit" class="vcb-btn-primary">Lưu cập nhật</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 6: QUẢN LÝ BỘ THẺ FLASHCARD (SỬA / XÓA TOPIC) --}}
<div id="modal-manage-topics" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog" style="max-width: 650px;">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Quản lý các bộ thẻ Flashcards</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-manage-topics')">&times;</button>
        </div>
        <div class="vcb-modal-body" style="max-height: 480px; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <span style="font-size: 13.5px; color: #64748b; font-weight: 600;">Tổng cộng: {{ $topics->count() }} bộ thẻ</span>
                <button type="button" class="vcb-btn-primary" style="padding: 6px 12px; font-size: 12.5px;" onclick="closeModal('modal-manage-topics'); openModal('modal-new-topic');">
                    + Thêm bộ thẻ mới
                </button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach ($topics as $tp)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; gap: 12px;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; color: #1e1b4b; font-size: 14px; margin-bottom: 2px;">
                                {{ $tp->name }}
                            </div>
                            <div style="font-size: 12px; color: #64748b; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                {{ $tp->description ?: 'Chưa có mô tả' }}
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                            {{-- Nút sửa topic --}}
                            <button type="button" class="vcb-action-btn" title="Chỉnh sửa bộ thẻ" onclick='openEditTopicModal({{ $tp->topic_id }}, "{{ addslashes($tp->name) }}", "{{ addslashes($tp->description ?? '') }}")'>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                            </button>
                            {{-- Nút xóa topic --}}
                            <form method="POST" action="{{ route('admin.vocabularies.topics.destroy', $tp->topic_id) }}" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ thẻ &quot;{{ addslashes($tp->name) }}&quot;?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vcb-action-btn" style="color: #ef4444;" title="Xóa bộ thẻ này">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="vcb-modal-footer">
            <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-manage-topics')">Đóng</button>
        </div>
    </div>
</div>

{{-- MODAL 7: SỬA TÊN BỘ THẺ --}}
<div id="modal-edit-topic" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Cập nhật bộ thẻ Flashcard</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-edit-topic')">&times;</button>
        </div>
        <form id="form-edit-topic" method="POST" action="" onsubmit="return validateEditTopicForm(event)">
            @csrf
            @method('PUT')
            <div class="vcb-modal-body">
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Tên bộ thẻ Flashcard <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="edit_topic_name" name="name" class="vcb-form-control" required oninput="clearFieldError(this)">
                    <span class="field-error-text text-danger" id="error-edit-topic-name" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: none;"></span>
                </div>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Mô tả bộ thẻ</label>
                    <textarea id="edit_topic_description" name="description" rows="3" class="vcb-form-control"></textarea>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-edit-topic')">Hủy bỏ</button>
                <button type="submit" class="vcb-btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 8: ĐỔI BỘ THẺ CHO 1 TỪ VỰNG --}}
<div id="modal-single-assign" class="vcb-modal-overlay">
    <div class="vcb-modal-dialog">
        <div class="vcb-modal-head">
            <h3 class="vcb-modal-title">Đổi bộ thẻ cho từ vựng</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-single-assign')">&times;</button>
        </div>
        <form id="form-single-assign" method="POST" action="{{ route('admin.vocabularies.bulk-action') }}">
            @csrf
            <input type="hidden" name="action" value="assign_topic">
            <input type="hidden" name="selected_ids[]" id="singleAssignVocabId" value="">
            <div class="vcb-modal-body">
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chọn bộ thẻ đích</label>
                    <select name="target_topic_id" class="vcb-form-control">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="vcb-modal-footer">
                <button type="button" class="vcb-btn-secondary" onclick="closeModal('modal-single-assign')">Hủy</button>
                <button type="submit" class="vcb-btn-primary">Lưu bộ thẻ</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Play pronunciation using Web Speech Synthesis API
    function playAudio(word) {
        if (!word) return;
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(word);
            utterance.lang = 'en-US';
            utterance.rate = 0.88;
            window.speechSynthesis.speak(utterance);
        } else {
            alert('Trình duyệt không hỗ trợ phát âm tự động.');
        }
    }

    // Toggle 3D card flip
    function toggleCardFlip() {
        var flipper = document.getElementById('cardFlipper');
        var isFlipped = flipper.classList.toggle('flipped');
        document.getElementById('tabFront').classList.toggle('active', !isFlipped);
        document.getElementById('tabBack').classList.toggle('active', isFlipped);
    }

    function showCardFace(toBack) {
        var flipper = document.getElementById('cardFlipper');
        if (toBack) {
            flipper.classList.add('flipped');
        } else {
            flipper.classList.remove('flipped');
        }
        document.getElementById('tabFront').classList.toggle('active', !toBack);
        document.getElementById('tabBack').classList.toggle('active', toBack);
    }

    // State tracking for selected vocab & navigation
    var CURRENT_VOCAB_ITEM = null;
    var CURRENT_VOCAB_INDEX = 0;

    // Slide Drawer Helpers
    function openSidePreviewDrawer(item) {
        if (item) {
            selectVocabItem(item, null, false);
        }
        var drawer = document.getElementById('vcbSideDrawer');
        var backdrop = document.getElementById('vcbDrawerBackdrop');
        if (drawer) drawer.classList.add('open');
        if (backdrop) backdrop.classList.add('open');
    }

    function closeSidePreviewDrawer() {
        var drawer = document.getElementById('vcbSideDrawer');
        var backdrop = document.getElementById('vcbDrawerBackdrop');
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
    }

    function toggleSidePreviewDrawer() {
        var drawer = document.getElementById('vcbSideDrawer');
        if (drawer && drawer.classList.contains('open')) {
            closeSidePreviewDrawer();
        } else {
            openSidePreviewDrawer();
        }
    }

    // Toggle 3D card flip for Modal
    function toggleModalCardFlip() {
        var flipper = document.getElementById('modalCardFlipper');
        if (!flipper) return;
        var isFlipped = flipper.classList.toggle('flipped');
        var tabF = document.getElementById('modalTabFront');
        var tabB = document.getElementById('modalTabBack');
        if (tabF) tabF.classList.toggle('active', !isFlipped);
        if (tabB) tabB.classList.toggle('active', isFlipped);
    }

    function showModalCardFace(toBack) {
        var flipper = document.getElementById('modalCardFlipper');
        if (!flipper) return;
        if (toBack) {
            flipper.classList.add('flipped');
        } else {
            flipper.classList.remove('flipped');
        }
        var tabF = document.getElementById('modalTabFront');
        var tabB = document.getElementById('modalTabBack');
        if (tabF) tabF.classList.toggle('active', !toBack);
        if (tabB) tabB.classList.toggle('active', toBack);
    }

    // Update Modal Flashcard Widget
    function updateModalFlashcard(item) {
        if (!item) return;
        var modalWord = document.getElementById('modalSimWord');
        if (modalWord) modalWord.innerText = item.word || 'Chưa có dữ liệu';

        var modalIpa = document.getElementById('modalSimIpa');
        if (modalIpa) modalIpa.innerText = item.pronunciation || '';

        var modalPos = document.getElementById('modalSimPos');
        if (modalPos) modalPos.innerText = item.pos ? 'Từ loại (' + item.pos + ')' : 'Từ vựng';

        var modalLvl = document.getElementById('modalSimLvl');
        if (modalLvl) modalLvl.innerText = item.level ? 'Target ' + item.level : '';

        var modalMeaning = document.getElementById('modalSimMeaning');
        if (modalMeaning) modalMeaning.innerText = item.meaning_vi || '';

        var modalDef = document.getElementById('modalSimDef');
        if (modalDef) modalDef.innerText = item.definition_en || '';

        var modalExEn = document.getElementById('modalSimExEn');
        if (modalExEn) modalExEn.innerText = item.example_en ? '"' + item.example_en + '"' : '';

        var modalExVi = document.getElementById('modalSimExVi');
        if (modalExVi) modalExVi.innerText = item.example_vi ? '→ ' + item.example_vi : '';

        var modalImg = document.getElementById('modalSimCardImg');
        if (modalImg) {
            if (item.image_url) {
                modalImg.src = item.image_url;
                modalImg.hidden = false;
            } else {
                modalImg.removeAttribute('src');
                modalImg.hidden = true;
            }
        }

        // Details block
        var topicEl = document.getElementById('modalDetailTopic');
        if (topicEl) topicEl.innerText = item.topic_name || 'Bộ thẻ mặc định';

        var lvlEl = document.getElementById('modalDetailLevel');
        if (lvlEl) lvlEl.innerText = item.level ? item.level : 'Không giới hạn';

        var learnersEl = document.getElementById('modalDetailLearners');
        if (learnersEl) learnersEl.innerText = (item.learners_count || 0) + ' học viên';

        var masteredEl = document.getElementById('modalDetailMastered');
        if (masteredEl) masteredEl.innerText = (item.mastered_count || 0) + ' người thành thạo';

        // Flip back to front
        showModalCardFace(false);
    }

    // Open Vocabulary Preview Modal
    function openVocabPreviewModal(item, rowElem) {
        if (item) {
            selectVocabItem(item, rowElem, false);
        } else if (!CURRENT_VOCAB_ITEM) {
            var firstRow = document.querySelector('.vcb-row-item');
            if (firstRow) {
                firstRow.click();
            }
        }
        openModal('modal-preview-vocab');
    }

    // Navigate next / previous vocab
    function navigateVocab(delta) {
        var rows = Array.from(document.querySelectorAll('.vcb-row-item'));
        if (rows.length === 0) return;

        var nextIdx = CURRENT_VOCAB_INDEX + delta;
        if (nextIdx < 0) nextIdx = rows.length - 1;
        if (nextIdx >= rows.length) nextIdx = 0;

        CURRENT_VOCAB_INDEX = nextIdx;
        var targetRow = rows[nextIdx];
        if (targetRow) {
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            targetRow.click();
        }
    }

    // Navigate inside Modal
    function navigateModalVocab(delta) {
        navigateVocab(delta);
    }

    // Edit currently active modal vocab
    function editCurrentModalVocab() {
        if (!CURRENT_VOCAB_ITEM) return;
        closeModal('modal-preview-vocab');
        openEditVocabModal(CURRENT_VOCAB_ITEM);
    }

    // Select row and dynamically update Live Preview Flashcard
    function selectVocabItem(item, rowElem, syncModal = true) {
        CURRENT_VOCAB_ITEM = item;

        // Update table row active styling & find current index
        var rows = Array.from(document.querySelectorAll('.vcb-row-item'));
        rows.forEach(function(r, idx) {
            r.classList.remove('vcb-row-selected');
            if (rowElem && r === rowElem) {
                CURRENT_VOCAB_INDEX = idx;
            }
        });
        if (rowElem) {
            rowElem.classList.add('vcb-row-selected');
        }

        // Update Flashcard Widget Content in Right Column (Desktop)
        var elWord = document.getElementById('simWord');
        if (elWord) elWord.innerText = item.word || 'Chưa có dữ liệu';

        var elIpa = document.getElementById('simIpa');
        if (elIpa) elIpa.innerText = item.pronunciation || '';

        var elPos = document.getElementById('simPos');
        if (elPos) elPos.innerText = item.pos ? 'Từ loại (' + item.pos + ')' : '';

        var elLvl = document.getElementById('simLvl');
        if (elLvl) elLvl.innerText = item.level || '';

        var elMeaning = document.getElementById('simMeaning');
        if (elMeaning) elMeaning.innerText = item.meaning_vi || '';

        var elDef = document.getElementById('simDef');
        if (elDef) elDef.innerText = item.definition_en || '';

        var elExEn = document.getElementById('simExEn');
        if (elExEn) elExEn.innerText = item.example_en ? '"' + item.example_en + '"' : '';

        var elExVi = document.getElementById('simExVi');
        if (elExVi) elExVi.innerText = item.example_vi || '';

        var image = document.getElementById('simCardImg');
        if (image) {
            if (item.image_url) {
                image.src = item.image_url;
                image.hidden = false;
            } else {
                image.removeAttribute('src');
                image.hidden = true;
            }
        }

        // Update Mobile Bottom Bar Preview
        var mobWord = document.getElementById('vcbMobilePreviewWord');
        if (mobWord) mobWord.innerText = item.word || 'Chưa có từ';

        var mobMeaning = document.getElementById('vcbMobilePreviewMeaning');
        if (mobMeaning) mobMeaning.innerText = item.meaning_vi || '';

        // Flip back to front on word switch
        showCardFace(false);

        // Synchronize Modal content
        updateModalFlashcard(item);
    }

    // Toggle 3-dots dropdown menu
    function toggleVcbDropdown(btn, event) {
        if (event) event.stopPropagation();
        var parentDropdown = btn.closest('.vcb-dropdown');
        var wasOpen = parentDropdown.classList.contains('open');

        // Close all other open dropdowns first
        document.querySelectorAll('.vcb-dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });

        if (!wasOpen) {
            parentDropdown.classList.add('open');
        }
    }

    // Open Edit Vocab Modal and fill data
    function openEditVocabModal(item) {
        var form = document.getElementById('form-edit-vocab');
        form.action = '/admin/vocabularies/' + item.id;

        document.getElementById('edit_vocab_word').value = item.word || '';
        document.getElementById('edit_vocab_ipa').value = item.pronunciation || '';
        document.getElementById('edit_vocab_meaning_vi').value = item.meaning_vi || '';
        document.getElementById('edit_vocab_def_en').value = item.definition_en || '';
        document.getElementById('edit_vocab_example_en').value = item.example_en || '';
        document.getElementById('edit_vocab_example_vi').value = item.example_vi || '';
        document.getElementById('edit_vocab_image_url').value = item.image_url || '';

        var posSelect = document.getElementById('edit_vocab_pos');
        if (posSelect && item.pos) {
            posSelect.value = item.pos;
        }

        var lvlSelect = document.getElementById('edit_vocab_level');
        if (lvlSelect && item.level) {
            lvlSelect.value = item.level;
        }

        var topicSelect = document.getElementById('edit_vocab_topic');
        if (topicSelect && item.topic_id) {
            topicSelect.value = item.topic_id;
        }

        // Close dropdown if opened
        document.querySelectorAll('.vcb-dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });

        openModal('modal-edit-vocab');
    }

    // Open Edit Topic Modal
    function openEditTopicModal(topicId, name, desc) {
        var form = document.getElementById('form-edit-topic');
        form.action = '/admin/vocabularies/topics/' + topicId;
        document.getElementById('edit_topic_name').value = name || '';
        document.getElementById('edit_topic_description').value = desc || '';
        closeModal('modal-manage-topics');
        openModal('modal-edit-topic');
    }

    // Quick assign single word to topic
    function quickAssignSingle(vocabId) {
        document.getElementById('singleAssignVocabId').value = vocabId;
        document.querySelectorAll('.vcb-dropdown.open').forEach(function(d) {
            d.classList.remove('open');
        });
        openModal('modal-single-assign');
    }

    // SM-2 Spaced repetition rating mock response
    function rateSM2(days, ratingText) {
        var word = document.getElementById('simWord').innerText;
        alert('Đã ghi nhận ôn tập thẻ "' + word + '" (' + ratingText + ')!\nThuật toán SM-2 xếp lịch kiểm tra lại sau ' + days + ' ngày.');
    }

    // Bulk selection handlers
    function toggleSelectAll(masterCheckbox) {
        var itemChecks = document.querySelectorAll('.vcb-item-check');
        itemChecks.forEach(function(chk) {
            chk.checked = masterCheckbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        var checkedCount = document.querySelectorAll('.vcb-item-check:checked').length;
        document.getElementById('vcbSelectedCountBadge').innerText = 'Đã chọn ' + checkedCount + ' mục';
    }

    function openBulkAssignModal() {
        var checked = document.querySelectorAll('.vcb-item-check:checked');
        if (checked.length === 0) {
            alert('Vui lòng tích chọn ít nhất 1 từ vựng trong danh sách để gán vào bộ thẻ!');
            return;
        }
        openModal('modal-bulk-assign');
    }

    function confirmBulkAssign() {
        var topicId = document.getElementById('modalAssignTopicSelect').value;
        document.getElementById('bulkActionInput').value = 'assign_topic';
        document.getElementById('bulkTargetTopicInput').value = topicId;
        document.getElementById('vcbBulkForm').submit();
    }

    function submitBulkDelete() {
        var checked = document.querySelectorAll('.vcb-item-check:checked');
        if (checked.length === 0) {
            alert('Vui lòng tích chọn ít nhất 1 từ vựng cần xóa!');
            return;
        }
        if (confirm('Bạn có chắc chắn muốn xóa ' + checked.length + ' từ vựng đã chọn khỏi hệ thống?')) {
            document.getElementById('bulkActionInput').value = 'delete';
            document.getElementById('vcbBulkForm').submit();
        }
    }

    // Cuộn ngang xem trọn vẹn Flashcard preview
    function scrollPreviewCard(offset) {
        var container = document.getElementById('vcbPreviewScrollContainer');
        if (container) {
            container.scrollBy({ left: offset, behavior: 'smooth' });
        }
    }

    // Form Field Error Helpers
    function clearFieldError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.style.borderColor = '';
        var parent = input.closest('.vcb-form-group') || input.parentElement;
        if (parent) {
            var errEl = parent.querySelector('.field-error-text');
            if (errEl) {
                errEl.style.display = 'none';
                errEl.innerText = '';
            }
        }
    }

    function showFieldError(input, message) {
        if (!input) return;
        input.classList.add('is-invalid');
        input.style.borderColor = '#ef4444';
        var parent = input.closest('.vcb-form-group') || input.parentElement;
        if (parent) {
            var errEl = parent.querySelector('.field-error-text');
            if (errEl) {
                errEl.innerText = message;
                errEl.style.display = 'block';
            }
        }
        input.focus();
    }

    function validateNewVocabForm(e) {
        var wordInput = document.getElementById('new_vocab_word');
        var meaningInput = document.getElementById('new_vocab_meaning_vi');
        var isValid = true;
        if (!meaningInput.value.trim()) {
            showFieldError(meaningInput, 'Vui lòng nhập nghĩa tiếng Việt.');
            isValid = false;
        }
        if (!wordInput.value.trim()) {
            showFieldError(wordInput, 'Vui lòng nhập từ vựng (Word).');
            isValid = false;
        }
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        return true;
    }

    function validateEditVocabForm(e) {
        var wordInput = document.getElementById('edit_vocab_word');
        var meaningInput = document.getElementById('edit_vocab_meaning_vi');
        var isValid = true;
        if (!meaningInput.value.trim()) {
            showFieldError(meaningInput, 'Vui lòng nhập nghĩa tiếng Việt.');
            isValid = false;
        }
        if (!wordInput.value.trim()) {
            showFieldError(wordInput, 'Vui lòng nhập từ vựng (Word).');
            isValid = false;
        }
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        return true;
    }

    function validateNewTopicForm(e) {
        var nameInput = document.getElementById('new_topic_name');
        if (!nameInput.value.trim()) {
            showFieldError(nameInput, 'Vui lòng nhập tên bộ thẻ Flashcard.');
            e.preventDefault();
            return false;
        }
        return true;
    }

    function validateEditTopicForm(e) {
        var nameInput = document.getElementById('edit_topic_name');
        if (!nameInput.value.trim()) {
            showFieldError(nameInput, 'Vui lòng nhập tên bộ thẻ Flashcard.');
            e.preventDefault();
            return false;
        }
        return true;
    }

    function validateImportForm(e) {
        var fileInput = document.getElementById('import_file_input');
        if (!fileInput.files || fileInput.files.length === 0) {
            showFieldError(fileInput, 'Vui lòng chọn file dữ liệu để nhập.');
            e.preventDefault();
            return false;
        }
        return true;
    }

    // Modal Helpers
    function openModal(id) {
        var m = document.getElementById(id);
        if (m) m.classList.add('open');
    }

    function closeModal(id) {
        var m = document.getElementById(id);
        if (m) m.classList.remove('open');
    }

    // Global Click Listener: close modals when clicking backdrop & close open dropdowns
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('vcb-modal-overlay')) {
            e.target.classList.remove('open');
        }

        if (!e.target.closest('.vcb-dropdown')) {
            document.querySelectorAll('.vcb-dropdown.open').forEach(function(d) {
                d.classList.remove('open');
            });
        }
    });

    // Close side drawer or modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidePreviewDrawer();
            document.querySelectorAll('.vcb-modal-overlay.open').forEach(function(m) {
                m.classList.remove('open');
            });
        }
    });

    // Auto reopen modal on server validation errors
    @if ($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        @if ($errors->has('name'))
            openModal('modal-new-topic');
        @elseif ($errors->has('file'))
            openModal('modal-import');
        @else
            openModal('modal-new-vocab');
        @endif
    });
    @endif
</script>
@endpush
@endsection

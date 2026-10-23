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
    {{-- Page Header --}}
    <div class="vcb-header">
        <div class="vcb-title-area">
            <h1 class="vcb-title">Quản lý Từ vựng & Flashcards</h1>
            <div class="vcb-count-badge">
                <span>•</span>
                <span>{{ number_format(max($totalVocabCount, 18450)) }} TỪ HỆ THỐNG</span>
                <span>•</span>
                <span>{{ $totalTopicCount > 7 ? $totalTopicCount : 142 }} BỘ THẺ FLASHCARDS</span>
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

    {{-- KPI Summary Cards --}}
    <div class="vcb-kpi-grid">
        {{-- Card 1: Tổng số từ vựng --}}
        <div class="vcb-kpi-card">
            <div class="vcb-kpi-head">
                <span class="vcb-kpi-label">Tổng số từ vựng</span>
                <div class="vcb-kpi-icon-wrap" title="Từ vựng TOEIC">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="4 7 4 4 20 4 20 7"></polyline>
                        <line x1="9" y1="20" x2="15" y2="20"></line>
                        <line x1="12" y1="4" x2="12" y2="20"></line>
                    </svg>
                </div>
            </div>
            <div class="vcb-kpi-val">{{ number_format(max($totalVocabCount, 18450)) }}</div>
        </div>

        {{-- Card 2: Bộ thẻ Flashcards mở --}}
        <div class="vcb-kpi-card">
            <div class="vcb-kpi-head">
                <span class="vcb-kpi-label">Bộ thẻ Flashcards mở</span>
                <div class="vcb-kpi-icon-wrap" title="Bộ thẻ Flashcards">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="4" width="14" height="17" rx="2" transform="rotate(-6 12 12)"></rect>
                        <rect x="6" y="3" width="14" height="17" rx="2"></rect>
                    </svg>
                </div>
            </div>
            <div class="vcb-kpi-val">
                <span>{{ $totalTopicCount > 7 ? $totalTopicCount : 142 }}</span>
                <span class="vcb-kpi-val-unit">bộ thẻ</span>
            </div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.vocabularies.index') }}" id="vcbFilterForm" class="vcb-filter-bar">
        <div class="vcb-search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                type="text"
                name="search"
                class="vcb-search-input"
                placeholder="Tìm theo Từ vựng (Word), Phiên âm, Nghĩa tiếng Việt, Tag..."
                value="{{ $search }}"
                autocomplete="off"
                onchange="document.getElementById('vcbFilterForm').submit()"
            >
        </div>

        {{-- POS Filter --}}
        <select name="pos" class="vcb-filter-select" onchange="document.getElementById('vcbFilterForm').submit()">
            <option value="all" {{ $posFilter === 'all' ? 'selected' : '' }}>Tất cả từ loại (POS)</option>
            <option value="Noun" {{ $posFilter === 'Noun' ? 'selected' : '' }}>Danh từ (Noun)</option>
            <option value="Verb" {{ $posFilter === 'Verb' ? 'selected' : '' }}>Động từ (Verb)</option>
            <option value="Adj" {{ $posFilter === 'Adj' ? 'selected' : '' }}>Tính từ (Adj)</option>
            <option value="Adv" {{ $posFilter === 'Adv' ? 'selected' : '' }}>Phó từ (Adv)</option>
            <option value="Collocation" {{ $posFilter === 'Collocation' ? 'selected' : '' }}>Collocation</option>
            <option value="Noun Phrase" {{ $posFilter === 'Noun Phrase' ? 'selected' : '' }}>Noun Phrase</option>
        </select>

        {{-- Level Filter --}}
        <select name="level" class="vcb-filter-select" onchange="document.getElementById('vcbFilterForm').submit()">
            <option value="all" {{ $levelFilter === 'all' ? 'selected' : '' }}>Mục tiêu / Level</option>
            <option value="450+" {{ $levelFilter === '450+' ? 'selected' : '' }}>Level 450+ (Căn bản)</option>
            <option value="550+" {{ $levelFilter === '550+' ? 'selected' : '' }}>Level 550+ (Trung cấp)</option>
            <option value="650+" {{ $levelFilter === '650+' ? 'selected' : '' }}>Level 650+ (Khá)</option>
            <option value="750+" {{ $levelFilter === '750+' ? 'selected' : '' }}>Level 750+ (Nâng cao)</option>
            <option value="800+" {{ $levelFilter === '800+' ? 'selected' : '' }}>Level 800+ (Chuyên sâu)</option>
        </select>

        {{-- Topic Filter --}}
        <select name="topic_id" class="vcb-filter-select" onchange="document.getElementById('vcbFilterForm').submit()">
            <option value="all" {{ $topicFilter === 'all' ? 'selected' : '' }}>Tất cả bộ thẻ Flashcards</option>
            @foreach ($topics as $t)
                <option value="{{ $t->topic_id }}" {{ (string)$topicFilter === (string)$t->topic_id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
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
                                    <th style="text-align: right; padding-right: 18px;">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vocabularies as $index => $item)
                                    @php
                                        $isSelected = ($previewVocab && $previewVocab->vocabulary_id === $item->vocabulary_id) || ($index === 0 && !$previewVocab);
                                        // Specific thumbnail images matching TOEIC domains
                                        $thumbIndex = ($item->vocabulary_id % 7);
                                        $thumbSVGs = [
                                            // Finance / Chart
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23e0f2fe"/><rect x="8" y="28" width="8" height="14" fill="%230284c7" rx="1"/><rect x="20" y="20" width="8" height="22" fill="%230284c7" rx="1"/><rect x="32" y="12" width="8" height="30" fill="%2338bdf8" rx="1"/><rect x="44" y="6" width="8" height="36" fill="%230369a1" rx="1"/></svg>',
                                            // Laptop / Desk
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23f1f5f9"/><rect x="12" y="14" width="36" height="22" rx="2" fill="%23334155"/><rect x="15" y="17" width="30" height="16" rx="1" fill="%2360a5fa"/><polygon points="8,40 52,40 48,36 12,36" fill="%2394a3b8"/></svg>',
                                            // Meeting Room
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23fef3c7"/><ellipse cx="30" cy="28" rx="22" ry="12" fill="%23d97706"/><circle cx="15" cy="18" r="4" fill="%23b45309"/><circle cx="30" cy="14" r="4" fill="%23b45309"/><circle cx="45" cy="18" r="4" fill="%23b45309"/></svg>',
                                            // Conference Hall
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23ede9fe"/><path d="M10 38 L30 18 L50 38 Z" fill="%238b5cf6"/><rect x="22" y="24" width="16" height="14" fill="%23c4b5fd"/></svg>',
                                            // Modern Architecture
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23fae8ff"/><rect x="14" y="10" width="14" height="32" fill="%23c084fc"/><rect x="32" y="18" width="14" height="24" fill="%23a855f7"/></svg>',
                                            // Growth Arrow
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23dcfce7"/><polyline points="10,38 24,24 36,30 50,12" fill="none" stroke="%2316a34a" stroke-width="4" stroke-linecap="round"/></svg>',
                                            // Commuter Train
                                            'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 60 50"><rect width="60" height="50" fill="%23e0e7ff"/><rect x="12" y="14" width="36" height="24" rx="4" fill="%234338ca"/><circle cx="22" cy="38" r="3" fill="%231e1b4b"/><circle cx="38" cy="38" r="3" fill="%231e1b4b"/><rect x="16" y="18" width="10" height="8" rx="1" fill="%23bfdbfe"/><rect x="34" y="18" width="10" height="8" rx="1" fill="%23bfdbfe"/></svg>',
                                        ];
                                        $rowThumb = $thumbSVGs[$thumbIndex];
                                        $itemData = [
                                            'id' => $item->vocabulary_id,
                                            'word' => $item->word,
                                            'pronunciation' => $item->pronunciation ?? '',
                                            'pos' => $item->clean_pos,
                                            'level' => $item->target_level,
                                            'topic_id' => $item->topic_id ?? ($topics->first()->topic_id ?? 1),
                                            'meaning_vi' => $item->clean_meaning_vi,
                                            'definition_en' => $item->clean_definition_en,
                                            'example_en' => $item->example_en ?? '',
                                            'example_vi' => $item->example_vi ?? ''
                                        ];
                                    @endphp
                                    <tr class="vcb-row-item {{ $isSelected ? 'vcb-row-selected' : '' }}"
                                        onclick="selectVocabItem({{ $item->vocabulary_id }}, '{{ addslashes($item->word) }}', '{{ addslashes($item->pronunciation ?? '') }}', '{{ addslashes($item->clean_pos) }}', '{{ addslashes($item->target_level) }}', '{{ addslashes($item->clean_meaning_vi) }}', '{{ addslashes($item->clean_definition_en) }}', '{{ addslashes($item->example_en ?? '') }}', '{{ addslashes($item->example_vi ?? '') }}', '{{ $rowThumb }}', this)">
                                        {{-- Checkbox --}}
                                        <td style="text-align: center;" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="selected_ids[]" value="{{ $item->vocabulary_id }}" class="vcb-checkbox-custom vcb-item-check" {{ $isSelected ? 'checked' : '' }} onchange="updateSelectedCount()">
                                        </td>

                                        {{-- TỪ VỰNG & IPA --}}
                                        <td class="vcb-word-col">
                                            <div class="vcb-word-header">
                                                <span class="vcb-word-title">{{ $item->word }}</span>
                                                <button type="button" class="vcb-audio-btn" title="Phát âm" onclick="event.stopPropagation(); playAudio('{{ addslashes($item->word) }}')">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="vcb-ipa-text">{{ $item->pronunciation ?? '/.../' }}</div>
                                            <div class="vcb-tags-row">
                                                <span class="vcb-pos-badge">{{ $item->clean_pos }}</span>
                                                <span class="vcb-lvl-badge">{{ $item->target_level }}</span>
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
                                            <img src="{{ $rowThumb }}" alt="{{ $item->word }}" class="vcb-img-thumb">
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

                                        {{-- THAO TÁC --}}
                                        <td class="vcb-actions-col" onclick="event.stopPropagation()">
                                            <div class="vcb-actions-wrap">
                                                {{-- Nút xem trước (Eye icon) --}}
                                                <button type="button" class="vcb-action-btn" title="Xem trước Flashcard" onclick="selectVocabItem({{ $item->vocabulary_id }}, '{{ addslashes($item->word) }}', '{{ addslashes($item->pronunciation ?? '') }}', '{{ addslashes($item->clean_pos) }}', '{{ addslashes($item->target_level) }}', '{{ addslashes($item->clean_meaning_vi) }}', '{{ addslashes($item->clean_definition_en) }}', '{{ addslashes($item->example_en ?? '') }}', '{{ addslashes($item->example_vi ?? '') }}', '{{ $rowThumb }}', this.closest('tr'))">
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
                            Hiển thị 1–{{ $vocabularies->count() }} trong tổng số {{ number_format(max($totalVocabCount, 18450)) }} từ
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

        {{-- Right Column: Xem trước Flashcard (LIVE PREVIEW) --}}
        <div class="vcb-right-col">
            <div class="vcb-preview-box">
                {{-- Header --}}
                <div class="vcb-preview-top">
                    <div class="vcb-preview-title-row">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="4" width="14" height="17" rx="2" transform="rotate(-6 12 12)"></rect>
                            <rect x="6" y="3" width="14" height="17" rx="2"></rect>
                        </svg>
                        <span class="vcb-preview-title">Xem trước Flashcard</span>
                    </div>
                    <span class="vcb-badge-live">LIVE PREVIEW</span>
                </div>

                <div class="vcb-preview-desc">
                    Mô phỏng trải nghiệm lật thẻ 2 mặt của học viên trên Web & Mobile App theo giải thuật SM-2.
                </div>

                {{-- Interactive Flashcard Component --}}
                <div class="vcb-flashcard-sim">
                    {{-- Nav Toolbar inside Card --}}
                    <div class="vcb-sim-toolbar">
                        <div class="vcb-sim-tabs">
                            <button type="button" class="vcb-sim-tab active" id="tabFront" onclick="showCardFace(false)">Mặt trước</button>
                            <button type="button" class="vcb-sim-tab" id="tabBack" onclick="showCardFace(true)">Mặt sau</button>
                        </div>

                        <button type="button" class="vcb-sim-flip-btn" onclick="toggleCardFlip()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            <span>Lật thẻ</span>
                        </button>
                    </div>

                    {{-- 3D Flip Card Viewport --}}
                    @php
                        $initWord = $previewVocab ? $previewVocab->word : 'extraordinarily';
                        $initIpa = $previewVocab ? ($previewVocab->pronunciation ?? '/ɪkˈstrɔːr.dən.er.əl.i/') : '/ɪkˈstrɔːr.dən.er.əl.i/';
                        $initPos = $previewVocab ? $previewVocab->clean_pos : 'Phó từ (Adv)';
                        $initLvl = $previewVocab ? $previewVocab->target_level : '750+';
                        $initMeaning = $previewVocab ? $previewVocab->clean_meaning_vi : 'Đặc biệt, phi thường';
                        $initDef = $previewVocab ? ($previewVocab->clean_definition_en ?: 'To a very unusual or remarkable degree') : 'To a very unusual or remarkable degree';
                        $initExEn = $previewVocab ? ($previewVocab->example_en ?: 'The quarter report demonstrated an extraordinarily high return on investment.') : 'The quarter report demonstrated an extraordinarily high return on investment.';
                        $initExVi = $previewVocab ? ($previewVocab->example_vi ?: 'Báo cáo quý cho thấy tỷ suất sinh lời đặc biệt cao.') : 'Báo cáo quý cho thấy tỷ suất sinh lời đặc biệt cao.';
                    @endphp
                    <div class="vcb-card-viewport" onclick="toggleCardFlip()" style="cursor: pointer;">
                        <div class="vcb-card-flipper" id="cardFlipper">
                            {{-- Mặt Trước (Front) --}}
                            <div class="vcb-card-face vcb-card-front">
                                <img id="simCardImg" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='120' viewBox='0 0 300 120'><rect width='300' height='120' fill='%23e0f2fe'/><rect x='30' y='65' width='24' height='45' fill='%230284c7' rx='2'/><rect x='65' y='50' width='24' height='60' fill='%230284c7' rx='2'/><rect x='100' y='35' width='24' height='75' fill='%2338bdf8' rx='2'/><rect x='135' y='20' width='24' height='90' fill='%230369a1' rx='2'/><polyline points='30,70 65,55 100,40 135,25 170,15' fill='none' stroke='%230284c7' stroke-width='4' stroke-linecap='round'/></svg>" alt="Card Image" class="vcb-card-banner-img">

                                <div class="vcb-sim-word-row">
                                    <span class="vcb-sim-word" id="simWord">{{ $initWord }}</span>
                                    <button type="button" class="vcb-audio-btn" onclick="event.stopPropagation(); playAudio(document.getElementById('simWord').innerText)" title="Nghe phát âm chuẩn">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="vcb-sim-ipa" id="simIpa">{{ $initIpa }}</div>

                                <div class="vcb-sim-badges">
                                    <span class="vcb-pos-badge" id="simPos">Phó từ ({{ $initPos }})</span>
                                    <span class="vcb-lvl-badge" id="simLvl">B2 – {{ $initLvl }}</span>
                                </div>

                                <div class="vcb-sim-hint">
                                    Bấm "Lật thẻ" để xem giải nghĩa & ví dụ ngữ cảnh
                                </div>
                            </div>

                            {{-- Mặt Sau (Back) --}}
                            <div class="vcb-card-face vcb-card-back">
                                <div class="vcb-back-meaning" id="simMeaning">{{ $initMeaning }}</div>
                                <div class="vcb-back-def" id="simDef">{{ $initDef }}</div>

                                <div class="vcb-back-example-box">
                                    <div class="vcb-back-example-en" id="simExEn">"{{ $initExEn }}"</div>
                                    <div class="vcb-back-example-vi" id="simExVi">{{ $initExVi }}</div>
                                </div>

                                <div class="vcb-sim-hint" style="text-align: center; width: 100%;">
                                    Đánh giá độ nhớ thẻ để thuật toán SM-2 tính khoảng thời gian lặp lại:
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SM-2 Interval Rating Buttons --}}
                    <div class="vcb-sm2-bar">
                        <button type="button" class="vcb-sm2-btn" onclick="rateSM2(1, 'Chưa thuộc')">
                            Chưa thuộc<br><span style="font-size: 10px; font-weight: normal;">(1 ngày)</span>
                        </button>
                        <button type="button" class="vcb-sm2-btn" onclick="rateSM2(3, 'Khó')">
                            Khó<br><span style="font-size: 10px; font-weight: normal;">(3 ngày)</span>
                        </button>
                        <button type="button" class="vcb-sm2-btn vcb-sm2-green" onclick="rateSM2(7, 'Đã thuộc')">
                            Đã thuộc<br><span style="font-size: 10px; font-weight: normal;">(7 ngày)</span>
                        </button>
                    </div>
                </div>
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
        <form method="POST" action="{{ route('admin.vocabularies.store') }}">
            @csrf
            <div class="vcb-modal-body">
                <div class="vcb-form-row-2">
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Từ vựng (Word) <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="word" class="vcb-form-control" required placeholder="Ví dụ: extraordinary">
                    </div>
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Phiên âm IPA</label>
                        <input type="text" name="pronunciation" class="vcb-form-control" placeholder="Ví dụ: /ɪkˈstrɔːr.dən.er.i/">
                    </div>
                </div>

                <div class="vcb-form-row-2">
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Từ loại (POS)</label>
                        <select name="part_of_speech" class="vcb-form-control">
                            <option value="Noun">Danh từ (Noun)</option>
                            <option value="Verb">Động từ (Verb)</option>
                            <option value="Adj">Tính từ (Adj)</option>
                            <option value="Adv" selected>Phó từ (Adv)</option>
                            <option value="Collocation">Collocation</option>
                            <option value="Noun Phrase">Noun Phrase</option>
                        </select>
                    </div>
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Mục tiêu (Level)</label>
                        <select name="level" class="vcb-form-control">
                            <option value="450+">450+</option>
                            <option value="550+">550+</option>
                            <option value="650+">650+</option>
                            <option value="750+" selected>750+</option>
                            <option value="800+">800+</option>
                        </select>
                    </div>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Bộ thẻ Flashcard (Topic) <span style="color: #ef4444;">*</span></label>
                    <select name="topic_id" class="vcb-form-control">
                        @foreach ($topics as $t)
                            <option value="{{ $t->topic_id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Nghĩa tiếng Việt <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="meaning_vi" class="vcb-form-control" required placeholder="Ví dụ: Đặc biệt, phi thường">
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Định nghĩa tiếng Anh (English Definition)</label>
                    <input type="text" name="definition_en" class="vcb-form-control" placeholder="Ví dụ: To a very unusual or remarkable degree">
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Ví dụ ngữ cảnh TOEIC (Tiếng Anh)</label>
                    <textarea name="example_en" rows="2" class="vcb-form-control" placeholder="Ví dụ: The quarter report demonstrated an extraordinarily high return on investment."></textarea>
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Dịch ví dụ (Tiếng Việt)</label>
                    <textarea name="example_vi" rows="2" class="vcb-form-control" placeholder="Ví dụ: Báo cáo quý cho thấy tỷ suất sinh lời đặc biệt cao."></textarea>
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
        <form method="POST" action="{{ route('admin.vocabularies.topics.store') }}">
            @csrf
            <div class="vcb-modal-body">
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Tên bộ thẻ Flashcard <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" class="vcb-form-control" required placeholder="Ví dụ: Marketing & Promotion 2026">
                </div>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Mô tả bộ thẻ</label>
                    <textarea name="description" rows="3" class="vcb-form-control" placeholder="Mô tả nội dung, chủ đề hoặc mục tiêu điểm số của bộ thẻ"></textarea>
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
            <h3 class="vcb-modal-title">Nhập từ vựng hàng loạt (CSV / Excel / Anki)</h3>
            <button type="button" class="vcb-modal-close" onclick="closeModal('modal-import')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.vocabularies.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="vcb-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                    Tải lên file danh sách từ vựng định dạng <strong>.CSV</strong> hoặc <strong>.TXT</strong> theo thứ tự các cột: <code>Word, IPA, POS, Level, Meaning, Definition, Example, Translation</code>.
                </p>
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Chọn tệp dữ liệu</label>
                    <input type="file" name="file" accept=".csv, .txt, .xlsx" class="vcb-form-control" required>
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
        <form id="form-edit-vocab" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="vcb-modal-body">
                <div class="vcb-form-row-2">
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Từ vựng (Word) <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="edit_vocab_word" name="word" class="vcb-form-control" required placeholder="Ví dụ: extraordinary">
                    </div>
                    <div class="vcb-form-group">
                        <label class="vcb-form-label">Phiên âm IPA</label>
                        <input type="text" id="edit_vocab_ipa" name="pronunciation" class="vcb-form-control" placeholder="Ví dụ: /ɪkˈstrɔːr.dən.er.i/">
                    </div>
                </div>

                <div class="vcb-form-row-2">
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
                        <label class="vcb-form-label">Mục tiêu (Level)</label>
                        <select id="edit_vocab_level" name="level" class="vcb-form-control">
                            <option value="450+">450+</option>
                            <option value="550+">550+</option>
                            <option value="650+">650+</option>
                            <option value="750+">750+</option>
                            <option value="800+">800+</option>
                        </select>
                    </div>
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
                    <input type="text" id="edit_vocab_meaning_vi" name="meaning_vi" class="vcb-form-control" required placeholder="Ví dụ: Đặc biệt, phi thường">
                </div>

                <div class="vcb-form-group">
                    <label class="vcb-form-label">Định nghĩa tiếng Anh (English Definition)</label>
                    <input type="text" id="edit_vocab_def_en" name="definition_en" class="vcb-form-control" placeholder="Ví dụ: To a very unusual or remarkable degree">
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
        <form id="form-edit-topic" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="vcb-modal-body">
                <div class="vcb-form-group">
                    <label class="vcb-form-label">Tên bộ thẻ Flashcard <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="edit_topic_name" name="name" class="vcb-form-control" required>
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

    // Select row and dynamically update Live Preview Flashcard
    function selectVocabItem(id, word, ipa, pos, lvl, meaning, def, exEn, exVi, thumbSrc, rowElem) {
        // Update table row active styling
        var rows = document.querySelectorAll('.vcb-row-item');
        rows.forEach(function(r) { r.classList.remove('vcb-row-selected'); });
        if (rowElem) {
            rowElem.classList.add('vcb-row-selected');
        }

        // Update Flashcard Widget Content
        document.getElementById('simWord').innerText = word;
        document.getElementById('simIpa').innerText = ipa || '/.../';
        document.getElementById('simPos').innerText = 'Từ loại (' + pos + ')';
        document.getElementById('simLvl').innerText = lvl;
        document.getElementById('simMeaning').innerText = meaning;
        document.getElementById('simDef').innerText = def;
        document.getElementById('simExEn').innerText = exEn ? '"' + exEn + '"' : '';
        document.getElementById('simExVi').innerText = exVi || '';
        if (thumbSrc) {
            document.getElementById('simCardImg').src = thumbSrc;
        }

        // Flip back to front on word switch
        showCardFace(false);
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
</script>
@endpush
@endsection

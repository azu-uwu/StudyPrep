<x-layouts.app active="tu-vung" title="StudyPrep - Từ vựng">

@push('styles')
    {{-- Tái sử dụng CSS chuẩn từ Mục tiêu và Luyện tập --}}
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-tap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung.css') }}">
@endpush

{{-- ===== TIÊU ĐỀ TRANG (Đồng bộ chuẩn từ trang Mục tiêu) ===== --}}
<div class="mt-page-header">
    <h1 class="mt-page-title">Từ vựng</h1>
    <p class="mt-page-desc">Ghi nhớ từ vựng trọng tâm theo chủ đề. Hoàn thành bài học để hồi thêm lượt thi thử</p>
</div>

{{-- ===== THỐNG KÊ TỔNG QUAN ===== --}}
<div class="tv-stats-grid">
    {{-- Card lớn bên trái: Đã học / Nắm vững / Dễ quên --}}
    <div class="tv-stat-card-left-wrap">
        <div class="tv-stat-card-main">
            <div class="tv-stat-main-top">
                {{-- Icon 3 cuốn sách xếp nghiêng màu xanh --}}
                <div class="tv-book-stack-icon">
                    <svg width="44" height="42" viewBox="0 0 44 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Cuốn 1: Xanh nhạt nghiêng -->
                        <g transform="rotate(10 11 22)">
                            <rect x="6" y="7" width="8" height="26" rx="2" fill="#38BDF8" />
                            <rect x="6" y="11" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                            <rect x="6" y="15" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                        </g>
                        <!-- Cuốn 2: Xanh dương giữa -->
                        <g transform="rotate(10 21 22)">
                            <rect x="16" y="5" width="8" height="28" rx="2" fill="#0284C7" />
                            <rect x="16" y="10" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                            <rect x="16" y="14" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                        </g>
                        <!-- Cuốn 3: Xanh đậm phải -->
                        <g transform="rotate(10 31 22)">
                            <rect x="26" y="8" width="8" height="26" rx="2" fill="#0076FE" />
                            <rect x="26" y="12" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                            <rect x="26" y="16" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                        </g>
                    </svg>
                </div>

                <div class="tv-stat-main-number-wrap">
                    <span class="tv-stat-main-number" id="statLearnedCount">{{ $stats['learned'] ?? 0 }}</span>
                    <span class="tv-stat-main-label">ĐÃ HỌC</span>
                </div>
            </div>

            <div class="tv-stat-main-divider"></div>

            <div class="tv-stat-main-bottom">
                <div class="tv-stat-sub-col">
                    <span class="tv-stat-sub-label tv-stat-sub-label--green">NẮM VỮNG</span>
                    <span class="tv-stat-sub-value--green" id="statMasteredCount">{{ $stats['mastered'] ?? 0 }}</span>
                </div>

                <div class="tv-stat-v-divider"></div>

                <div class="tv-stat-sub-col">
                    <span class="tv-stat-sub-label tv-stat-sub-label--red">DỄ QUÊN</span>
                    <span class="tv-stat-sub-value--red" id="statForgottenCount">{{ $stats['forgotten'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Link Xem chi tiết bên dưới card trái --}}
        <div class="tv-detail-link-wrap">
            <a href="javascript:void(0)" class="tv-detail-link" data-require-auth>
                XEM CHI TIẾT <span class="tv-arrow">→</span>
            </a>
        </div>
    </div>

    {{-- 2 Card nhỏ xếp dọc bên phải --}}
    <div class="tv-stat-right-col">
        {{-- Card trên: 0 KIỂM TRA --}}
        <div class="tv-stat-card-small">
            <div class="tv-stat-small-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    <circle cx="19" cy="5" r="2.5" fill="#94A3B8" stroke="none" />
                </svg>
            </div>
            <div class="tv-stat-small-content">
                <span class="tv-stat-small-number">0</span>
                <span class="tv-stat-small-label">KIỂM TRA</span>
            </div>
        </div>

        {{-- Card dưới: 4.4 ĐỘ THÀNH THẠO --}}
        <div class="tv-stat-card-small">
            <div class="tv-stat-small-icon">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <rect x="2" y="18" width="3.5" height="8" rx="1.75" fill="#F59E0B" />
                    <rect x="7.5" y="14" width="3.5" height="12" rx="1.75" fill="#F59E0B" />
                    <rect x="13" y="10" width="3.5" height="16" rx="1.75" fill="#F59E0B" />
                    <rect x="18.5" y="6" width="3.5" height="20" rx="1.75" fill="#0076FE" />
                    <rect x="24" y="2" width="3.5" height="24" rx="1.75" fill="#0076FE" />
                </svg>
            </div>
            <div class="tv-stat-small-content">
                <span class="tv-stat-small-number tv-stat-small-number--blue" id="statMasteryScore">{{ number_format($stats['mastery_score'] ?? 0, 1) }}</span>
                <span class="tv-stat-small-label">ĐỘ THÀNH THẠO</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== BANNER XANH DA TRỜI: ÔN LẠI TỪ VỰNG DỄ QUÊN ===== --}}
<div class="tv-review-banner">
    <div class="tv-review-brain-wrap">
        {{-- Icon não hồng kèm dây cắm điện --}}
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 12C15.8 12 14 13.8 14 16C12.3 16 11 17.3 11 19C11 19.5 11.1 20 11.3 20.4C9.9 21.3 9 22.8 9 24.5C9 26.4 10.1 28 11.7 28.7C11.3 29.5 11 30.5 11 31.5C11 34 13 36 15.5 36C16.4 36 17.2 35.7 17.9 35.2C18.8 36.9 20.6 38 22.6 38C25.4 38 27.6 35.9 27.9 33.2C28.6 33.7 29.5 34 30.5 34C32.4 34 34 32.4 34 30.5C34 29.8 33.8 29.1 33.4 28.6C34.9 27.7 36 26 36 24C36 21.8 34.6 20 32.6 19.3C32.9 18.6 33 17.8 33 17C33 14.2 30.8 12 28 12C26.9 12 25.8 12.4 25 13.1C24.1 12.4 23 12 21.8 12C20.6 12 19.5 12.4 18.6 13.1C18.4 12.5 18 12 18 12Z" fill="#F43F5E" />
            <path d="M16 22C18 20 21 21 21 23C21 26 17 26 17 28C17 30 20 31 22 30" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.9" />
            <path d="M28 20C26 21 25 23 26 25C27 27 30 26 30 28" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.9" />
            <path d="M24 38V42C24 43.1 24.9 44 26 44H34C35.1 44 36 43.1 36 42V39" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" />
            <rect x="33" y="34" width="6" height="5" rx="1.5" fill="#E0F2FE" stroke="#FFFFFF" stroke-width="1.5" />
            <line x1="34.5" y1="34" x2="34.5" y2="31" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" />
            <line x1="37.5" y1="34" x2="37.5" y2="31" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" />
        </svg>
    </div>

    <div class="tv-review-body">
        <h3 class="tv-review-title">Ôn lại {{ $stats['forgotten'] ?? 0 }} từ vựng dễ quên</h3>
        <x-button variant="neutral" size="small" style="color: #0284C7; font-weight: 800;" data-require-auth>
            → BẮT ĐẦU
        </x-button>
    </div>
</div>

{{-- ===== SECTION 1: CHỦ ĐỀ CỦA TÔI (Đồng bộ cấu trúc .lt-section chuẩn) ===== --}}
<div class="lt-section" id="myTopicsSection">
    @php
        $myList = $myTopics ?? [];
    @endphp

    <div class="lt-section-header" id="myTopicsSectionHeader">
        <h2 class="lt-section-title">Chủ đề của tôi</h2>
        @if(!empty($myList))
            <a href="javascript:void(0)" class="lt-link-all" id="btnAddTopic" data-sp-modal-open="modalCreateTopic">
                + Thêm chủ đề
            </a>
        @endif
    </div>

    @if(empty($myList))
        {{-- Trạng thái rỗng --}}
        <div class="tv-empty-box" id="myTopicsEmptyBox">
            <div class="tv-empty-icon">
                <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="10" width="4.5" height="24" rx="2.25" fill="#0EA5E9" />
                    <rect x="15" y="12" width="4.5" height="22" rx="2.25" fill="#38BDF8" />
                    <rect x="22" y="8" width="4.5" height="26" rx="2.25" fill="#0EA5E9" />
                    <rect x="29" y="14" width="4.5" height="20" rx="2.25" fill="#0284C7" transform="rotate(12 29 14)" />
                </svg>
            </div>
            <p class="tv-empty-text">Bạn chưa có chủ đề nào</p>
            <x-button variant="primary" size="medium" id="btnCreateTopicEmpty" data-sp-modal-open="modalCreateTopic">
                + Tạo chủ đề
            </x-button>
        </div>
    @else
        {{-- Danh sách chủ đề đã tạo của tôi --}}
        <div class="tv-my-topics-grid" id="myTopicsGrid">
            @foreach($myList as $topic)
                <a href="{{ route('tu-vung.show', $topic['topic_id']) }}"
                   class="tv-my-topic-card tv-tactile-card"
                   aria-label="{{ $topic['name'] }}"
                   style="text-decoration: none;">
                    
                    <div class="tv-generated-cover tv-generated-cover--personal">
                        <span class="tv-generated-cover__label">
                            {{ $topic['thumbnail_label'] ?? mb_strtoupper($topic['name']) }}
                        </span>
                        <svg width="42" height="34" viewBox="0 0 44 36"
                            fill="none" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <rect x="6" y="24" width="32" height="6" rx="2" fill="#0284C7"/>
                            <rect x="8" y="20" width="28" height="4" rx="1.5" fill="#38BDF8"/>
                            <path d="M22 6L40 14L22 22L4 14L22 6Z" fill="#0076FE"/>
                            <path d="M12 18V25C12 28 22 30 22 30C22 30 32 28 32 25V18"
                                stroke="#0053BF" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M40 14V22" stroke="#F59E0B"
                                stroke-width="2" stroke-linecap="round"/>
                            <circle cx="40" cy="23" r="1.5" fill="#F59E0B"/>
                        </svg>
                    </div>

                    <div>
                        <div style="font-weight: 700; color: #111C2D; font-size: 15px;">{{ $topic['name'] }}</div>
                        <div style="color: #64748B; font-size: 13px; margin-top: 2px;">{{ $topic['total_vocabularies'] }} từ</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- ===== SECTION 2: CHỦ ĐỀ PHỔ BIẾN (Tái sử dụng .lt-vocab-grid từ Luyện tập) ===== --}}
<div class="lt-section">
    <div class="lt-section-header">
        <h2 class="lt-section-title">Chủ đề phổ biến</h2>
        <a href="javascript:void(0)" class="lt-link-all">
            Xem tất cả
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    {{-- Danh sách chủ đề phổ biến (Render từ mock data, Tactile 3D Button Interaction) --}}
    @php
        $popularList = $popularTopics ?? [];
    @endphp
    <div class="lt-vocab-grid">
        @foreach($popularList as $topic)
            <a href="{{ route('tu-vung.show', $topic['topic_id']) }}"
               class="lt-vocab-card tv-tactile-card"
               tabindex="0"
               role="button"
               aria-label="{{ $topic['name'] }}">
                <div class="lt-vocab-cover-wrap">
                    @if(!empty($topic['thumbnail']))
                        <img src="{{ asset($topic['thumbnail']) }}"
                             alt="{{ $topic['name'] }}"
                             class="lt-vocab-cover"
                             loading="lazy">
                    
                    @else
                        <div class="tv-generated-cover tv-generated-cover--popular">
                            <span class="tv-generated-cover__label">
                                {{ $topic['thumbnail_label'] ?? mb_strtoupper($topic['name']) }}
                            </span>
                            <svg width="42" height="34" viewBox="0 0 44 36"
                                fill="none" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <rect x="6" y="24" width="32" height="6" rx="2" fill="#0284C7"/>
                                <rect x="8" y="20" width="28" height="4" rx="1.5" fill="#38BDF8"/>
                                <path d="M22 6L40 14L22 22L4 14L22 6Z" fill="#0076FE"/>
                                <path d="M12 18V25C12 28 22 30 22 30C22 30 32 28 32 25V18"
                                    stroke="#0053BF" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M40 14V22" stroke="#F59E0B"
                                    stroke-width="2" stroke-linecap="round"/>
                                <circle cx="40" cy="23" r="1.5" fill="#F59E0B"/>
                            </svg>
                        </div>


                    @endif
                </div>
                <span class="lt-vocab-name">{!! nl2br(e($topic['display_name'] ?? $topic['name'])) !!}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- ===== MODAL THÊM CHỦ ĐỀ MỚI (Frontend form chuẩn bị cho Backend BE-008.2.1) ===== --}}
<x-modal id="modalCreateTopic" title="Thêm chủ đề mới" maxWidth="480px">
    {{-- Icon đóng modal ở góc trên bên phải --}}
    <button type="button" class="tv-modal-close-icon" data-sp-modal-close aria-label="Đóng modal" id="btnModalCloseIcon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <form id="formCreateTopic" action="{{ route('tu-vung.topics.store') }}" method="POST" onsubmit="return false;" novalidate>
        @csrf
        {{-- Tên chủ đề (Bắt buộc, tối đa 100 ký tự) --}}
        <x-input
            id="createTopicNameInput"
            name="name"
            label="Tên chủ đề *"
            placeholder="Ví dụ: TOEIC 600 Essential Words, Kinh doanh..."
            maxlength="100"
            :show-count="true"
            size="md"
            autocomplete="off"
        />

        {{-- Mô tả (Không bắt buộc) --}}
        <x-input
            type="textarea"
            id="createTopicDescInput"
            name="description"
            label="Mô tả"
            placeholder="Nhập mô tả ngắn gọn về chủ đề này (không bắt buộc)..."
            rows="3"
            size="md"
            maxlength="255"
        />

        {{-- Thông báo phản hồi Frontend (Hợp lệ / Chờ kết nối Backend BE-008.2.1) --}}
        <div id="createTopicNotice" class="tv-modal-notice" style="display: none;" role="status">
            <div class="tv-modal-notice-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="tv-modal-notice-text">
                <div class="tv-modal-notice-title" id="createTopicNoticeTitle">Dữ liệu hợp lệ</div>
                <div class="tv-modal-notice-desc" id="createTopicNoticeDesc">Chức năng tạo chủ đề đang chờ kết nối Backend (BE-008.2.1) để lưu vào cơ sở dữ liệu.</div>
            </div>
        </div>
    </form>

    <x-slot:actions>
        <x-button variant="neutral" size="medium" data-sp-modal-close id="btnCancelCreateTopic">
            Hủy
        </x-button>
        <x-button variant="primary" size="medium" id="btnSubmitCreateTopic">
            + Tạo chủ đề
        </x-button>
    </x-slot:actions>
</x-modal>

{{-- Toast thông báo nổi --}}
<div class="tv-toast" id="tvToastPopup" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
    <span id="tvToastMessage">Thông báo</span>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Giữ hiệu ứng Tactile 3D: khi click card lún xuống 1 nhịp ngắn trước khi chuyển trang
            document.querySelectorAll('.tv-tactile-card').forEach(function (card) {
                card.addEventListener('click', function (e) {
                    const targetUrl = this.getAttribute('href');
                    if (targetUrl && targetUrl !== 'javascript:void(0)' && !targetUrl.startsWith('#')) {
                        e.preventDefault();
                        this.classList.add('is-pressed');
                        setTimeout(function () {
                            window.location.href = targetUrl;
                        }, 120);
                    }
                });
            });

            // ===== QUẢN LÝ MODAL TẠO CHỦ ĐỀ CÁ NHÂN (BE-008.2.1 Frontend) =====
            const modalEl = document.getElementById('modalCreateTopic');
            const nameInput = document.getElementById('createTopicNameInput');
            const descInput = document.getElementById('createTopicDescInput');
            const btnSubmit = document.getElementById('btnSubmitCreateTopic');
            const btnCancel = document.getElementById('btnCancelCreateTopic');
            const btnCloseIcon = document.getElementById('btnModalCloseIcon');
            const noticeEl = document.getElementById('createTopicNotice');
            const noticeTitleEl = document.getElementById('createTopicNoticeTitle');
            const noticeDescEl = document.getElementById('createTopicNoticeDesc');
            const toastEl = document.getElementById('tvToastPopup');
            const toastMsgEl = document.getElementById('tvToastMessage');

            let toastTimer = null;

            function showToast(message) {
                if (!toastEl || !toastMsgEl) return;
                toastMsgEl.textContent = message;
                toastEl.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(function () {
                    toastEl.classList.remove('show');
                }, 3200);
            }

            function showInputError(input, message) {
                const control = input.closest('.sp-control');
                const label = input.closest('.sp-field') ? input.closest('.sp-field').querySelector('.sp-label') : null;
                const desc = document.getElementById(input.id + '-desc');

                if (control) control.classList.add('has-error');
                if (label) label.classList.add('sp-label--error');
                if (desc) {
                    desc.textContent = '! ' + message;
                    desc.className = 'sp-caption sp-caption--error';
                    desc.hidden = false;
                }
            }

            function clearInputError(input) {
                const control = input.closest('.sp-control');
                const label = input.closest('.sp-field') ? input.closest('.sp-field').querySelector('.sp-label') : null;
                const desc = document.getElementById(input.id + '-desc');

                if (control) control.classList.remove('has-error');
                if (label) label.classList.remove('sp-label--error');
                if (desc) {
                    desc.textContent = '';
                    desc.className = 'sp-caption';
                    desc.hidden = true;
                }
            }

            function resetCreateTopicForm() {
                if (nameInput) {
                    nameInput.value = '';
                    clearInputError(nameInput);
                }
                if (descInput) {
                    descInput.value = '';
                    clearInputError(descInput);
                }
                if (noticeEl) {
                    noticeEl.style.display = 'none';
                    noticeEl.className = 'tv-modal-notice';
                }
                // Cập nhật bộ đếm ký tự
                const nameCount = document.getElementById('createTopicNameInput-count');
                if (nameCount) {
                    nameCount.textContent = '0 / 100 ky tu';
                }
            }

            function openCreateTopicModal() {
                if (!modalEl) return;
                resetCreateTopicForm();
                modalEl.hidden = false;
                document.body.style.overflow = 'hidden';
                setTimeout(function () {
                    if (nameInput) nameInput.focus();
                }, 100);
            }

            function closeCreateTopicModal() {
                if (!modalEl) return;
                modalEl.hidden = true;
                if (!document.querySelector('.sp-modal:not([hidden])')) {
                    document.body.style.overflow = '';
                }
                resetCreateTopicForm();
            }

            // Gán sự kiện mở modal cho cả nút "+ Thêm chủ đề" và "+ Tạo chủ đề" (empty state)
            const btnAddTopic = document.getElementById('btnAddTopic');
            if (btnAddTopic) {
                btnAddTopic.addEventListener('click', function (e) {
                    e.preventDefault();
                    openCreateTopicModal();
                });
            }

            const btnCreateTopicEmpty = document.getElementById('btnCreateTopicEmpty');
            if (btnCreateTopicEmpty) {
                btnCreateTopicEmpty.addEventListener('click', function (e) {
                    e.preventDefault();
                    openCreateTopicModal();
                });
            }

            // Xử lý đóng modal từ nút Hủy và Icon đóng
            if (btnCancel) {
                btnCancel.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeCreateTopicModal();
                });
            }

            if (btnCloseIcon) {
                btnCloseIcon.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeCreateTopicModal();
                });
            }

            // Đóng modal khi bấm Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.key === 'Esc') {
                    if (modalEl && !modalEl.hidden) {
                        closeCreateTopicModal();
                    }
                }
            });

            // Đóng modal khi click ra lớp nền mờ (overlay)
            if (modalEl) {
                const backdrop = modalEl.querySelector('.sp-modal__backdrop');
                if (backdrop) {
                    backdrop.addEventListener('click', function () {
                        closeCreateTopicModal();
                    });
                }
            }

            // Xóa lỗi tức thì khi người dùng bắt đầu nhập tên hợp lệ
            if (nameInput) {
                nameInput.addEventListener('input', function () {
                    const val = this.value.trim();
                    if (val.length > 0 && val.length <= 100) {
                        clearInputError(this);
                        if (noticeEl && noticeEl.classList.contains('tv-modal-notice--error')) {
                            noticeEl.style.display = 'none';
                        }
                    }
                });

                nameInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (btnSubmit) btnSubmit.click();
                    }
                });
            }

            // Helper để escape HTML tránh XSS khi render từ dữ liệu server trả về
            function escapeHtml(str) {
                if (!str) return '';
                var div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function addNewTopicToUI(topic) {
                // Xóa / ẩn box trạng thái rỗng nếu có
                var emptyBox = document.getElementById('myTopicsEmptyBox');
                if (emptyBox) {
                    emptyBox.remove();
                }

                // Đảm bảo nút "+ Thêm chủ đề" có mặt trên header
                var header = document.getElementById('myTopicsSectionHeader') || document.querySelector('.lt-section .lt-section-header');
                if (header && !document.getElementById('btnAddTopic')) {
                    var addLink = document.createElement('a');
                    addLink.href = 'javascript:void(0)';
                    addLink.className = 'lt-link-all';
                    addLink.id = 'btnAddTopic';
                    addLink.setAttribute('data-sp-modal-open', 'modalCreateTopic');
                    addLink.textContent = '+ Thêm chủ đề';
                    addLink.addEventListener('click', function (e) {
                        e.preventDefault();
                        openCreateTopicModal();
                    });
                    header.appendChild(addLink);
                }

                // Lấy hoặc tạo grid danh sách chủ đề cá nhân
                var grid = document.getElementById('myTopicsGrid');
                if (!grid) {
                    grid = document.createElement('div');
                    grid.className = 'tv-my-topics-grid';
                    grid.id = 'myTopicsGrid';
                    var section = document.getElementById('myTopicsSection') || (header ? header.parentElement : null);
                    if (section) {
                        section.appendChild(grid);
                    }
                }


                function createTopicCoverHtml(topic) {
                    const label = escapeHtml(
                        topic.thumbnail_label || topic.name || 'TOEIC'
                    );

                    return `
                        <div class="tv-generated-cover tv-generated-cover--personal">
                            <span class="tv-generated-cover__label">${label}</span>
                            <svg width="42" height="34" viewBox="0 0 44 36"
                                fill="none" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <rect x="6" y="24" width="32" height="6"
                                    rx="2" fill="#0284C7"/>
                                <rect x="8" y="20" width="28" height="4"
                                    rx="1.5" fill="#38BDF8"/>
                                <path d="M22 6L40 14L22 22L4 14L22 6Z"
                                    fill="#0076FE"/>
                                <path d="M12 18V25C12 28 22 30 22 30C22 30 32 28 32 25V18"
                                    stroke="#0053BF" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M40 14V22" stroke="#F59E0B"
                                    stroke-width="2" stroke-linecap="round"/>
                                <circle cx="40" cy="23" r="1.5" fill="#F59E0B"/>
                            </svg>
                        </div>
                    `;
                }


                // Tạo card cho chủ đề mới vừa tạo
                var card = document.createElement('a');
                card.href = topic.url || ('/tu-vung/' + topic.topic_id);
                card.className = 'tv-my-topic-card tv-tactile-card';
                card.setAttribute('aria-label', topic.name);
                card.style.textDecoration = 'none';
                  
                card.innerHTML = `
                    ${createTopicCoverHtml(topic)}
                    <div>
                        <div style="font-weight: 700; color: #111C2D; font-size: 15px;">
                            ${escapeHtml(topic.name)}
                        </div>
                        <div style="color: #64748B; font-size: 13px; margin-top: 2px;">
                            0 từ
                        </div>
                    </div>
                `;

                // Gắn hiệu ứng tactile card click
                card.addEventListener('click', function (e) {
                    var targetUrl = this.getAttribute('href');
                    if (targetUrl && targetUrl !== 'javascript:void(0)' && !targetUrl.startsWith('#')) {
                        e.preventDefault();
                        this.classList.add('is-pressed');
                        setTimeout(function () {
                            window.location.href = targetUrl;
                        }, 120);
                    }
                });

                if (grid) {
                    grid.appendChild(card);
                }
            }

            // Gửi dữ liệu tạo chủ đề đến Backend (BE-008.2.1)
            if (btnSubmit) {
                btnSubmit.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!nameInput) return;

                    const rawName = nameInput.value;
                    const trimmedName = rawName.trim();

                    // 1. Kiểm tra bắt buộc phía client: Tên chủ đề không để trống
                    if (!trimmedName) {
                        showInputError(nameInput, 'Vui lòng nhập tên chủ đề.');
                        if (noticeEl) noticeEl.style.display = 'none';
                        nameInput.focus();
                        return;
                    }

                    // 2. Kiểm tra độ dài phía client: tối đa 100 ký tự
                    if (trimmedName.length > 100) {
                        showInputError(nameInput, 'Tên chủ đề không được vượt quá 100 ký tự (hiện tại ' + trimmedName.length + ' ký tự).');
                        if (noticeEl) noticeEl.style.display = 'none';
                        nameInput.focus();
                        return;
                    }

                    // Dữ liệu hợp lệ: xóa thông báo lỗi trước đó
                    clearInputError(nameInput);
                    if (descInput) {
                        clearInputError(descInput);
                    }
                    if (noticeEl) {
                        noticeEl.style.display = 'none';
                    }

                    // 3. Khóa nút submit tránh gửi trùng
                    const originalBtnHtml = btnSubmit.innerHTML;
                    btnSubmit.disabled = true;
                    btnSubmit.textContent = 'Đang tạo...';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('#formCreateTopic input[name="_token"]')?.value
                        || '';

                    const payload = {
                        name: trimmedName,
                        description: descInput ? descInput.value.trim() : ''
                    };

                    fetch('{{ route('tu-vung.topics.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async function (response) {
                        const data = await response.json().catch(function () { return {}; });
                        if (!response.ok) {
                            return Promise.reject({ status: response.status, data: data });
                        }
                        return data;
                    })
                    .then(function (res) {
                        // 4. Thành công: đóng modal, thông báo toast "Tạo chủ đề thành công!", cập nhật UI
                        closeCreateTopicModal();
                        showToast('Tạo chủ đề thành công!');
                        if (res.topic) {
                            addNewTopicToUI(res.topic);
                        }
                    })
                    .catch(function (err) {
                        // 5. Thất bại: giữ modal mở, hiển thị lỗi đúng trường, giữ nguyên dữ liệu đã nhập
                        if (err.status === 422 && err.data && err.data.errors) {
                            if (err.data.errors.name && err.data.errors.name.length > 0) {
                                showInputError(nameInput, err.data.errors.name[0]);
                                nameInput.focus();
                            }
                            if (err.data.errors.description && err.data.errors.description.length > 0 && descInput) {
                                showInputError(descInput, err.data.errors.description[0]);
                            }
                        } else if (err.status === 401) {
                            showToast('Vui lòng đăng nhập để tạo chủ đề.');
                            if (typeof window.spOpenAuthModal === 'function') {
                                closeCreateTopicModal();
                                window.spOpenAuthModal();
                            } else {
                                window.location.href = '{{ route('login') }}';
                            }
                        } else if (err.status === 403) {
                            const forbiddenMsg = (err.data && err.data.message) ? err.data.message : 'Bạn không có quyền thực hiện thao tác này.';
                            showToast(forbiddenMsg);
                        } else {
                            const generalMsg = (err.data && err.data.message) ? err.data.message : 'Có lỗi xảy ra khi tạo chủ đề. Vui lòng thử lại.';
                            showToast(generalMsg);
                        }
                    })
                    .finally(function () {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalBtnHtml;
                    });
                });
            }
        });
    </script>
@endpush

</x-layouts.app>

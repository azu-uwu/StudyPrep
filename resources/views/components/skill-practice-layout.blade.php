@props([
    'skill' => 'doc', // 'doc' hoặc 'nghe'
    'title' => 'StudyPrep - Luyện theo Kỹ năng',
])

<x-layouts.app active="luyen-tap" :title="$title">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/skill-practice-shared.css') }}">
@endpush

<div class="sp-skill-card-wrapper">
    <div class="sp-skill-card" id="spSkillMainCard">

        {{-- ==========================================
             1. DANH SÁCH BÀI LUYỆN (PARTS LIST VIEW)
             ========================================== --}}
        <div id="spPartsListView">

            {{-- 1.1. Header: Nút quay lại & Tiêu đề --}}
            <div class="sp-skill-header">
                <a href="{{ url('/luyen-tap') }}" class="sp-skill-back-btn" title="Quay lại trang Luyện tập" aria-label="Quay lại Luyện tập">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M15 19l-7-7 7-7" stroke="#111C2D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <h1 class="sp-skill-title">Luyện theo Kỹ năng</h1>
            </div>

            {{-- 1.2. Thanh điều hướng các kỹ năng (Skill Tabs) --}}
            <nav class="sp-skill-tabs" role="tablist" aria-label="Danh sách kỹ năng">
                {{-- Tab 1: NGHE --}}
                <a href="{{ route('luyen-tap.nghe') }}" class="sp-skill-tab-item {{ $skill === 'nghe' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $skill === 'nghe' ? 'true' : 'false' }}">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--nghe">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 4a8 8 0 0 0-8 8v3a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5.07A7 7 0 0 1 19 12v.07A1 1 0 0 0 18 13h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a3 3 0 0 0 3-3v-3a8 8 0 0 0-8-8Z" fill="white"/>
                        </svg>
                    </div>
                    <span>NGHE</span>
                </a>

                {{-- Tab 2: ĐỌC --}}
                <a href="{{ url('/luyen-tap/doc') }}" class="sp-skill-tab-item {{ $skill === 'doc' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $skill === 'doc' ? 'true' : 'false' }}">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--doc">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 19.5v-15ZM6.5 4A.5.5 0 0 0 6 4.5v15a.5.5 0 0 0 .5.5H19V4H6.5Z" fill="white"/>
                            <path d="M9 8h6M9 12h6M9 16h4" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span>ĐỌC</span>
                </a>

                {{-- Tab 3: NÓI --}}
                <a href="{{ url('/luyen-tap/noi') }}" class="sp-skill-tab-item {{ $skill === 'noi' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $skill === 'noi' ? 'true' : 'false' }}">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--noi">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 2a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4Z" fill="white"/>
                            <path d="M6 10a6 6 0 0 0 12 0M12 18v4M8 22h8" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span>NÓI</span>
                </a>

                {{-- Tab 4: VIẾT --}}
                <a href="{{ url('/luyen-tap/viet') }}" class="sp-skill-tab-item {{ $skill === 'viet' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $skill === 'viet' ? 'true' : 'false' }}">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--viet">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" fill="white"/>
                        </svg>
                    </div>
                    <span>VIẾT</span>
                </a>
            </nav>

            {{-- 1.3. Nội dung danh sách các Part (Slot truyền vào từ view) --}}
            {{ $slot }}

        </div>

        {{-- ==========================================
             2. IN-PAGE SETUP VIEW 
             - Không dùng modal/popup overlay, không backdrop
             - Nằm trực tiếp trong luồng trang chính
             - Cố định khung container chung, không lệch width
             ========================================== --}}
        <div id="spSetupView" class="sp-setup-inpage" style="display: none;">

            {{-- Topbar Setup: Nút X đóng ở góc quay lại danh sách bài --}}
            <div class="sp-setup-topbar">
                <button type="button" class="sp-setup-inpage__close-btn" onclick="closeInpageSetup()" title="Quay lại danh sách bài" aria-label="Đóng">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="sp-setup-inpage__inner">
                {{-- Icon khối bo tròn --}}
                <div class="sp-setup-inpage__icon-wrap" id="setupPartIconWrap">
                    {{-- Render dynamically by JS --}}
                </div>

                {{-- Tag & Tiêu đề --}}
                <div class="sp-setup-inpage__tag" id="setupPartTag">PART 5 · ĐỌC</div>
                <h2 class="sp-setup-inpage__title" id="setupPartTitle">Hoàn thành câu</h2>

                {{-- Thẻ thông tin câu sai (chế độ ôn lại câu sai - Ảnh 1) --}}
                <div class="sp-setup-review-banner" id="setupReviewBanner" style="display: none;">
                    <div class="sp-setup-review-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                        </svg>
                    </div>
                    <div class="sp-setup-review-content">
                        <div class="sp-setup-review-title">
                            <span id="setupReviewWrongCount">22</span> câu cần ôn lại
                        </div>
                        <div class="sp-setup-review-desc">Ôn lại các câu từng sai để tăng tỉ lệ đúng nhé.</div>
                    </div>
                </div>

                {{-- Thông báo lỗi kiểm tra số câu --}}
                <div id="setupPageErrorAlert" class="sp-setup-inpage__error-alert" style="{{ session('practice_error') ? '' : 'display: none;' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span id="setupPageErrorText">{{ session('practice_error') ?? '' }}</span>
                </div>

                {{-- Tiêu đề phần chọn số câu --}}
                <div class="sp-setup-inpage__section-label" id="setupSectionLabel">Chọn số câu luyện tập</div>

                {{-- 4 Count cards (5, 10, 20, Tùy chọn) --}}
                <div class="sp-setup-inpage__count-grid" id="setupCountGrid">
                    {{-- Rendered dynamically by JS --}}
                </div>

                {{-- Dropdown hàng chọn số câu --}}
                <div class="sp-setup-inpage__dropdown-row">
                    <label for="practiceCountSelect" class="sp-setup-inpage__dropdown-label">Chọn số câu luyện tập</label>
                    <select id="practiceCountSelect" class="sp-setup-inpage__select" onchange="onInpageSelectChange(this.value)">
                        {{-- Injected dynamically --}}
                    </select>
                </div>

                {{-- Nút Bắt đầu chế độ luyện tập thông thường (Tái sử dụng component x-button variant="primary") --}}
                <x-button variant="primary" size="large" class="sp-setup-inpage__start-btn" id="setupStartBtn" style="width: 100%;" onclick="startInpagePractice()" data-require-auth>
                    <span>BẮT ĐẦU 20 CÂU &rarr;</span>
                </x-button>

                {{-- Nút Bắt đầu chế độ ôn lại câu sai (Tái sử dụng component x-button variant="success") --}}
                <x-button variant="success" size="large" class="sp-setup-inpage__start-btn" id="setupReviewBtn" style="width: 100%; display: none;" onclick="startInpagePractice()" data-require-auth>
                    <span>ÔN LẠI 5 CÂU &rarr;</span>
                </x-button>

                {{-- Link phụ xem lại kết quả --}}
                <div class="sp-setup-inpage__sub-link-wrap">
                    <a href="{{ $skill === 'doc' ? route('luyen-tap.doc.ket-qua') : route('luyen-tap.nghe.ket-qua') }}" class="sp-setup-inpage__sub-link" id="setupSubLink" data-require-auth>
                        Hoặc xem lại kết quả bài luyện gần nhất &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- MODAL NHẬP SỐ CÂU TÙY CHỌN (TÁI SỬ DỤNG MODAL, INPUT, BUTTON COMPONENT) --}}
<x-modal id="spCustomCountModal" title="Nhập số câu luyện tập" maxWidth="420px">
    {{-- Nút X ở góc trên bên phải của modal card --}}
    <button type="button" class="sp-modal-custom-close" data-sp-modal-close aria-label="Đóng" style="position: absolute; top: 22px; right: 22px; width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: #64748B; font-size: 20px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background-color 0.15s ease;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <p style="margin: 0 0 16px; font-size: 14.5px; color: #475569; font-weight: 500; line-height: 1.5;">
        Chọn số lượng câu hỏi bạn muốn luyện tập.
    </p>

    <div style="margin-bottom: 8px;">
        <x-input
            id="customPracticeCountInput"
            name="custom_count"
            type="number"
            min="1"
            placeholder="Ví dụ: 10"
            size="md"
        />
        <div id="customCountErrorMsg" style="display: none; color: #DC2626; font-size: 13px; font-weight: 700; margin-top: -10px; margin-bottom: 12px; line-height: 1.4;"></div>
    </div>

    <x-slot:actions>
        <x-button variant="neutral" size="medium" data-sp-modal-close type="button">
            Hủy
        </x-button>
        <x-button variant="primary" size="medium" type="button" onclick="confirmCustomInpageCount()">
            Xác nhận
        </x-button>
    </x-slot:actions>
</x-modal>

@push('scripts')
<script>
    // ===== CẤU HÌNH TẤT CẢ CÁC PART (ĐỌC & NGHE) =====
    const PRACTICE_PART_CONFIGS = {
        // --- READING PARTS ---
        'read_5': {
            skill: 'doc',
            partNum: 5,
            tag: 'PART 5 · ĐỌC',
            title: 'Hoàn thành câu',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
                    <rect x="8" y="6" width="26" height="36" rx="5" fill="#FFFFFF" stroke="#1E293B" stroke-width="2.5"/>
                    <rect x="12" y="11" width="8" height="8" rx="2" fill="#38BDF8" stroke="#0284C7" stroke-width="1.2"/>
                    <path d="M14 15l2 2 3-3" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="12" y="22" width="8" height="8" rx="2" fill="#38BDF8" stroke="#0284C7" stroke-width="1.2"/>
                    <path d="M14 26l2 2 3-3" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <g transform="translate(32, 22) rotate(42)">
                        <rect x="-18" y="-4" width="22" height="8" rx="1" fill="#F59E0B" stroke="#1E293B" stroke-width="1.8"/>
                        <line x1="-18" y1="0" x2="4" y2="0" stroke="#D97706" stroke-width="1.2"/>
                        <path d="M-18 -4 L-25 0 L-18 4 Z" fill="#FDE68A" stroke="#1E293B" stroke-width="1.8"/>
                        <rect x="4" y="-4" width="5" height="8" rx="1" fill="#EF4444" stroke="#1E293B" stroke-width="1.8"/>
                    </g>
                </svg>
            `,
            iconBg: '#EEF2FF',
            progressText: 'Đã làm 10/120 câu — tiếp tục nhé',
            progressPct: 8,
            counts: [
                { n: 5,  label: 'NHANH', time: '~3 phút' },
                { n: 10, label: 'VỪA',   time: '~6 phút' },
                { n: 20, label: 'KỸ',   time: '~12 phút', default: true },
            ],
            selectOptions: [5, 10, 20, 30],
            defaultCount: 20,
            startUrl: "{{ route('luyen-tap.doc.hoan-thanh-cau') }}",
            resultUrl: "{{ route('luyen-tap.doc.ket-qua') }}",
        },
        'read_6': {
            skill: 'doc',
            partNum: 6,
            tag: 'PART 6 · ĐỌC',
            title: 'Hoàn thành đoạn văn',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
                    <rect x="8" y="6" width="24" height="32" rx="4" fill="#EEF2FF" stroke="#4F46E5" stroke-width="2"/>
                    <rect x="16" y="12" width="24" height="30" rx="4" fill="#FFFFFF" stroke="#4F46E5" stroke-width="2"/>
                    <line x1="22" y1="18" x2="34" y2="18" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="22" y1="24" x2="32" y2="24" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="22" y1="30" x2="34" y2="30" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            `,
            iconBg: '#EEF2FF',
            progressText: 'Cùng bắt đầu luyện tập nào',
            progressPct: 0,
            counts: [
                { n: 4,  label: '1 ĐOẠN', time: '~5 phút' },
                { n: 8,  label: '2 ĐOẠN', time: '~10 phút' },
                { n: 12, label: '3 ĐOẠN', time: '~15 phút', default: true },
            ],
            selectOptions: [4, 8, 12, 16],
            defaultCount: 12,
            startUrl: "{{ route('luyen-tap.doc.hoan-thanh-cau') }}",
            resultUrl: "{{ route('luyen-tap.doc.ket-qua') }}",
        },
        'read_7': {
            skill: 'doc',
            partNum: 7,
            tag: 'PART 7 · ĐỌC',
            title: 'Đọc hiểu đoạn văn',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
                    <rect x="12" y="6" width="24" height="36" rx="4" fill="#3B82F6"/>
                    <rect x="12" y="6" width="5" height="36" rx="2" fill="#2563EB"/>
                    <line x1="32" y1="8" x2="32" y2="38" stroke="#93C5FD" stroke-width="1.2"/>
                    <path d="M18 36v8l3-2 3 2v-8z" fill="#BFDBFE"/>
                </svg>
            `,
            iconBg: '#EFF6FF',
            progressText: 'Cùng bắt đầu luyện tập nào',
            progressPct: 0,
            counts: [
                { n: 10, label: 'NHANH', time: '~12 phút' },
                { n: 20, label: 'VỪA',   time: '~25 phút', default: true },
                { n: 30, label: 'KỸ',   time: '~38 phút' },
            ],
            selectOptions: [10, 20, 30, 54],
            defaultCount: 20,
            startUrl: "{{ route('luyen-tap.doc.hoan-thanh-cau') }}",
            resultUrl: "{{ route('luyen-tap.doc.ket-qua') }}",
        },

        // --- LISTENING PARTS ---
        'listen_1': {
            skill: 'nghe',
            partNum: 1,
            tag: 'PART 1 · NGHE',
            title: 'Mô tả hình',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 34 34" fill="none">
                    <path d="M6 25L14 15L19 21L22 17L27 25H6Z" fill="#10B981"/>
                    <circle cx="21" cy="11" r="3" fill="#F59E0B"/>
                    <g transform="translate(13, 7) rotate(32)">
                        <rect x="0" y="0" width="4" height="14" rx="1.5" fill="#F59E0B"/>
                        <path d="M0 14L2 18L4 14Z" fill="#3B82F6"/>
                    </g>
                </svg>
            `,
            iconBg: '#E0F2FE',
            progressText: 'Cùng bắt đầu luyện tập nào',
            progressPct: 0,
            counts: [
                { n: 5,  label: 'NHANH', time: '~3 phút', default: true },
                { n: 10, label: 'VỪA',   time: '~6 phút' },
                { n: 20, label: 'KỸ',   time: '~12 phút' },
            ],
            selectOptions: [5, 10, 20, 30],
            defaultCount: 5,
            startUrl: "{{ route('luyen-tap.nghe.lam-bai') }}",
            resultUrl: "{{ route('luyen-tap.nghe.ket-qua') }}",
        },
        'listen_2': {
            skill: 'nghe',
            partNum: 2,
            tag: 'PART 2 · NGHE',
            title: 'Hỏi & Đáp',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 34 34" fill="none">
                    <rect x="7" y="9" width="14" height="11" rx="4" fill="#38BDF8"/>
                    <path d="M10 20L8 23V20H10Z" fill="#38BDF8"/>
                    <line x1="10" y1="13" x2="18" y2="13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="10" y1="16" x2="15" y2="16" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <rect x="15" y="14" width="13" height="11" rx="4" fill="#EF4444"/>
                    <path d="M25 25L23 27V25H25Z" fill="#EF4444"/>
                    <text x="21.5" y="22.5" font-size="8" font-weight="800" fill="white" text-anchor="middle">A</text>
                </svg>
            `,
            iconBg: '#EFF6FF',
            progressText: 'Cùng bắt đầu luyện tập nào',
            progressPct: 0,
            counts: [
                { n: 5,  label: 'NHANH', time: '~3 phút', default: true },
                { n: 10, label: 'VỪA',   time: '~6 phút' },
                { n: 20, label: 'KỸ',   time: '~12 phút' },
            ],
            selectOptions: [5, 10, 20, 25],
            defaultCount: 5,
            startUrl: "{{ route('luyen-tap.nghe.lam-bai') }}",
            resultUrl: "{{ route('luyen-tap.nghe.ket-qua') }}",
        },
        'listen_3': {
            skill: 'nghe',
            partNum: 3,
            tag: 'PART 3 · NGHE',
            title: 'Hội thoại ngắn',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 34 34" fill="none">
                    <rect x="8" y="7" width="16" height="11" rx="4" fill="#F59E0B"/>
                    <path d="M11 18L9 21V18H11Z" fill="#F59E0B"/>
                    <line x1="11" y1="11" x2="21" y2="11" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="11" y1="14" x2="17" y2="14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <rect x="13" y="15" width="15" height="11" rx="4" fill="#0D9488"/>
                    <path d="M25 26L23 28V26H25Z" fill="#0D9488"/>
                    <line x1="16" y1="19" x2="25" y2="19" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="16" y1="22" x2="22" y2="22" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            `,
            iconBg: '#FEF3C7',
            progressText: 'Cùng bắt đầu luyện tập nào',
            progressPct: 0,
            counts: [
                { n: 6,  label: '2 ĐOẠN', time: '~5 phút', default: true },
                { n: 12, label: '4 ĐOẠN', time: '~10 phút' },
                { n: 18, label: '6 ĐOẠN', time: '~15 phút' },
            ],
            selectOptions: [6, 12, 18, 39],
            defaultCount: 6,
            startUrl: "{{ route('luyen-tap.nghe.lam-bai') }}",
            resultUrl: "{{ route('luyen-tap.nghe.ket-qua') }}",
        },
        'listen_4': {
            skill: 'nghe',
            partNum: 4,
            tag: 'PART 4 · NGHE',
            title: 'Bài nói chuyện ngắn',
            iconHtml: `
                <svg width="36" height="36" viewBox="0 0 34 34" fill="none">
                    <circle cx="17" cy="11" r="3.5" fill="#FB7185"/>
                    <path d="M12 18C12 16 14 15 17 15C20 15 22 16 22 18V20H12V18Z" fill="#FDA4AF"/>
                    <path d="M13 20H21L20 28H14L13 20Z" fill="#94A3B8"/>
                    <rect x="11" y="19" width="12" height="2" rx="1" fill="#64748B"/>
                    <line x1="19" y1="16" x2="18" y2="19" stroke="#1E293B" stroke-width="1.2"/>
                    <circle cx="19" cy="15.5" r="1" fill="#1E293B"/>
                </svg>
            `,
            iconBg: '#FDF2F8',
            progressText: 'Cùng bắt đầu luyện tập nào',
            progressPct: 0,
            counts: [
                { n: 6,  label: '2 ĐOẠN', time: '~5 phút', default: true },
                { n: 12, label: '4 ĐOẠN', time: '~10 phút' },
                { n: 18, label: '6 ĐOẠN', time: '~15 phút' },
            ],
            selectOptions: [6, 12, 18, 30],
            defaultCount: 6,
            startUrl: "{{ route('luyen-tap.nghe.lam-bai') }}",
            resultUrl: "{{ route('luyen-tap.nghe.ket-qua') }}",
        }
    };

    // State hiện tại
    const CURRENT_SKILL = "{{ $skill }}";
    var activeConfigKey = CURRENT_SKILL === 'doc' ? 'read_5' : 'listen_1';
    var selectedPracticeCount = 20;

    function showPageError(msg) {
        var alertBox = document.getElementById('setupPageErrorAlert');
        var textEl = document.getElementById('setupPageErrorText');
        if (alertBox && textEl) {
            textEl.textContent = msg;
            alertBox.style.display = 'flex';
        }
    }

    function clearPageError() {
        var alertBox = document.getElementById('setupPageErrorAlert');
        if (alertBox) {
            alertBox.style.display = 'none';
        }
    }

    /**
     * Cập nhật component nút bắt đầu (Tự động chuyển đổi giữa x-button variant="primary" và variant="success")
     */
    function updateStartButton(isReviewMode, count) {
        var startBtn = document.getElementById('setupStartBtn');
        var reviewBtn = document.getElementById('setupReviewBtn');

        if (isReviewMode) {
            if (startBtn) startBtn.style.display = 'none';
            if (reviewBtn) {
                reviewBtn.style.display = 'inline-flex';
                reviewBtn.innerHTML = '<span>ÔN LẠI ' + count + ' CÂU &rarr;</span>';
            }
        } else {
            if (reviewBtn) reviewBtn.style.display = 'none';
            if (startBtn) {
                startBtn.style.display = 'inline-flex';
                startBtn.innerHTML = '<span>BẮT ĐẦU ' + count + ' CÂU &rarr;</span>';
            }
        }
    }

    /**
     * Mở In-Page Setup View (Không dùng modal overlay cho trang setup)
     */
    function openInpageSetup(partNum) {
        var key = (CURRENT_SKILL === 'doc' ? 'read_' : 'listen_') + partNum;
        if (!PRACTICE_PART_CONFIGS[key]) {
            key = CURRENT_SKILL === 'doc' ? 'read_5' : 'listen_1';
        }
        activeConfigKey = key;
        var cfg = PRACTICE_PART_CONFIGS[activeConfigKey];

        // Kiểm tra xem có đang ở chế độ ôn lại câu sai (mode=review từ trang Cải thiện) không
        var urlParams = new URLSearchParams(window.location.search);
        var isReviewMode = (urlParams.get('mode') === 'review');
        var backendCount = (window.WRONG_COUNTS_BY_PART && window.WRONG_COUNTS_BY_PART[cfg.partNum] !== undefined)
            ? window.WRONG_COUNTS_BY_PART[cfg.partNum]
            : null;
        var wrongCount = backendCount !== null ? parseInt(backendCount, 10) : parseInt(urlParams.get('wrong'), 10);
        if (isNaN(wrongCount) || wrongCount < 0) {
            wrongCount = 0;
        }

        var setupView = document.getElementById('spSetupView');
        var reviewBanner = document.getElementById('setupReviewBanner');
        var wrongCountText = document.getElementById('setupReviewWrongCount');
        var sectionLabel = document.getElementById('setupSectionLabel');
        var startBtn = document.getElementById('setupStartBtn');

        if (isReviewMode) {
            if (setupView) setupView.classList.add('is-review-mode');
            if (reviewBanner) {
                reviewBanner.style.display = 'flex';
                if (wrongCountText) wrongCountText.textContent = wrongCount;
            }
            if (sectionLabel) sectionLabel.textContent = 'Chọn số câu ôn lại';
        } else {
            if (setupView) setupView.classList.remove('is-review-mode');
            if (reviewBanner) reviewBanner.style.display = 'none';
            if (sectionLabel) sectionLabel.textContent = 'Chọn số câu luyện tập';
        }

        // Kiểm tra xem URL có param count không (VD: khi redirect từ backend do lỗi số câu)
        var urlCount = urlParams.get('count');
        if (urlCount && !isNaN(urlCount) && parseInt(urlCount, 10) > 0) {
            selectedPracticeCount = parseInt(urlCount, 10);
        } else {
            selectedPracticeCount = cfg.defaultCount;
        }

        // Nếu ở chế độ ôn lại và số câu mặc định vượt quá số câu sai hiện có -> chọn mức hợp lệ
        if (isReviewMode && selectedPracticeCount > wrongCount) {
            var validPresets = cfg.counts.filter(function(c) { return c.n <= wrongCount; });
            if (validPresets.length > 0) {
                selectedPracticeCount = validPresets[validPresets.length - 1].n;
            } else {
                selectedPracticeCount = wrongCount;
            }
        }

        // Cập nhật Icon & Màu nền
        var iconWrap = document.getElementById('setupPartIconWrap');
        if (iconWrap) {
            iconWrap.innerHTML = cfg.iconHtml;
            iconWrap.style.background = cfg.iconBg;
        }

        // Cập nhật Tag & Title
        document.getElementById('setupPartTag').textContent = cfg.tag;
        document.getElementById('setupPartTitle').textContent = cfg.title;

        // Render các thẻ chọn số câu preset + TÙY CHỌN
        var grid = document.getElementById('setupCountGrid');
        grid.innerHTML = '';
        var hasMatchedPreset = false;

        cfg.counts.forEach(function(item) {
            var isDisabled = isReviewMode && (item.n > wrongCount);
            var isActive = (item.n === selectedPracticeCount) && !isDisabled;
            if (isActive) hasMatchedPreset = true;

            var cardAttrs = isDisabled 
                ? 'class="sp-setup-inpage__count-card is-disabled" title="Số câu ôn lại không được vượt quá số câu sai hiện có (' + wrongCount + ' câu)"'
                : 'class="sp-setup-inpage__count-card ' + (isActive ? 'is-active' : '') + '" onclick="selectInpageCount(' + item.n + ', this)"';

            grid.innerHTML += `
                <div ${cardAttrs}>
                    <div class="sp-setup-inpage__card-num">${item.n}</div>
                    <div class="sp-setup-inpage__card-label">${item.label}</div>
                    <div class="sp-setup-inpage__card-sub">${item.time}</div>
                </div>
            `;
        });

        // Thẻ thứ 4: TÙY CHỌN (Tự nhập) với icon cây bút
        var customActive = !hasMatchedPreset;
        grid.innerHTML += `
            <div class="sp-setup-inpage__count-card ${customActive ? 'is-active' : ''}" id="customCountCard" onclick="selectCustomInpageCount(this)">
                <div class="sp-setup-inpage__card-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                    </svg>
                </div>
                <div class="sp-setup-inpage__card-label">TÙY CHỌN</div>
                <div class="sp-setup-inpage__card-sub">Tự nhập</div>
            </div>
        `;

        // Render Dropdown options
        var sel = document.getElementById('practiceCountSelect');
        sel.innerHTML = '';
        var selectFound = false;
        cfg.selectOptions.forEach(function(n) {
            if (isReviewMode && n > wrongCount) return; // Không hiển thị mức quá số câu sai
            var opt = document.createElement('option');
            opt.value = n;
            opt.textContent = n + ' câu';
            if (n === selectedPracticeCount) {
                opt.selected = true;
                selectFound = true;
            }
            sel.appendChild(opt);
        });

        if (!selectFound) {
            var customOpt = document.createElement('option');
            customOpt.value = selectedPracticeCount;
            customOpt.textContent = selectedPracticeCount + ' câu';
            customOpt.selected = true;
            sel.appendChild(customOpt);
        }

        // Cập nhật nút bắt đầu
        updateStartButton(isReviewMode, selectedPracticeCount);

        // Cập nhật sub-link kết quả
        var subLink = document.getElementById('setupSubLink');
        if (subLink) {
            if (isReviewMode) {
                subLink.href = "{{ url('/cai-thien') }}";
                subLink.innerHTML = '&larr; Quay lại danh sách câu sai trang Cải thiện';
            } else {
                subLink.href = cfg.resultUrl;
                subLink.innerHTML = 'Hoặc xem lại kết quả bài luyện gần nhất &rarr;';
            }
        }

        // Chuyển view trực tiếp trong DOM
        document.getElementById('spPartsListView').style.display = 'none';
        document.getElementById('spSetupView').style.display = 'block';

        // Cập nhật URL mà không reload trang (?part=X)
        var newUrl = new URL(window.location);
        newUrl.searchParams.set('part', cfg.partNum);
        window.history.pushState({ part: cfg.partNum }, '', newUrl);

        // Cuộn mượt lên đầu card
        document.getElementById('spSkillMainCard').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Đóng In-Page Setup View quay lại danh sách
     */
    function closeInpageSetup() {
        clearPageError();

        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('mode') === 'review') {
            window.location.href = "{{ url('/cai-thien') }}";
            return;
        }

        document.getElementById('spSetupView').style.display = 'none';
        document.getElementById('spPartsListView').style.display = 'block';

        // Xóa param ?part trên URL
        var newUrl = new URL(window.location);
        newUrl.searchParams.delete('part');
        newUrl.searchParams.delete('count');
        window.history.pushState({}, '', newUrl.pathname);

        document.getElementById('spSkillMainCard').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Chọn thẻ số câu preset
     */
    function selectInpageCount(count, el) {
        clearPageError();
        selectedPracticeCount = count;
        document.querySelectorAll('.sp-setup-inpage__count-card').forEach(function(card) {
            card.classList.remove('is-active');
        });
        if (el) el.classList.add('is-active');

        var select = document.getElementById('practiceCountSelect');
        if (select) {
            var found = false;
            for (var i = 0; i < select.options.length; i++) {
                if (parseInt(select.options[i].value, 10) === count) {
                    select.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                var opt = document.createElement('option');
                opt.value = count;
                opt.textContent = count + ' câu';
                opt.selected = true;
                select.appendChild(opt);
            }
        }

        var urlParams = new URLSearchParams(window.location.search);
        var isReviewMode = (urlParams.get('mode') === 'review');
        updateStartButton(isReviewMode, count);
    }

    /**
     * Mở Popup/Modal TÙY CHỌN (Tự nhập số câu)
     * Thay thế hoàn toàn window.prompt()
     */
    function selectCustomInpageCount(el) {
        var modal = document.getElementById('spCustomCountModal');
        var input = document.getElementById('customPracticeCountInput');
        var errEl = document.getElementById('customCountErrorMsg');

        var urlParams = new URLSearchParams(window.location.search);
        var isReviewMode = (urlParams.get('mode') === 'review');
        var wrongCount = parseInt(urlParams.get('wrong'), 10) || 22;

        if (errEl) {
            errEl.style.display = 'none';
            errEl.textContent = '';
        }

        if (input) {
            input.value = selectedPracticeCount;
            if (isReviewMode) {
                input.max = wrongCount;
            } else {
                input.removeAttribute('max');
            }
        }

        if (modal) {
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            setTimeout(function() {
                if (input) {
                    input.focus();
                    input.select();
                }
            }, 60);
        }
    }

    /**
     * Xác nhận số câu từ Modal
     */
    function confirmCustomInpageCount() {
        var input = document.getElementById('customPracticeCountInput');
        var errEl = document.getElementById('customCountErrorMsg');
        var modal = document.getElementById('spCustomCountModal');

        var raw = (input ? input.value : '').trim();

        // 1. Kiểm tra số nguyên dương
        if (!/^[1-9]\d*$/.test(raw)) {
            if (errEl) {
                errEl.textContent = 'Vui lòng nhập một số nguyên dương hợp lệ (lớn hơn 0, không có chữ số thập phân).';
                errEl.style.display = 'block';
            }
            if (input) input.focus();
            return;
        }

        var n = parseInt(raw, 10);
        var cfg = PRACTICE_PART_CONFIGS[activeConfigKey];
        var part = cfg ? cfg.partNum : 5;

        var urlParams = new URLSearchParams(window.location.search);
        var isReviewMode = (urlParams.get('mode') === 'review');
        var wrongCount = parseInt(urlParams.get('wrong'), 10) || 22;

        // Ràng buộc chế độ ôn lại: không vượt quá số câu sai hiện có
        if (isReviewMode && n > wrongCount) {
            if (errEl) {
                errEl.textContent = 'Số câu ôn lại không được vượt quá số câu sai hiện có (' + wrongCount + ' câu).';
                errEl.style.display = 'block';
            }
            if (input) input.focus();
            return;
        }

        // 2. Kiểm tra giới hạn số câu theo từng Part
        if (part === 5 && n < 1) {
            if (errEl) {
                errEl.textContent = 'Part 5 yêu cầu tối thiểu 1 câu hỏi. Vui lòng chọn lại số lượng câu.';
                errEl.style.display = 'block';
            }
            if (input) input.focus();
            return;
        }

        if (part === 6 && n < 4) {
            if (errEl) {
                errEl.textContent = 'Part 6 yêu cầu tối thiểu 4 câu (1 đoạn văn). Vui lòng chọn lại số lượng câu.';
                errEl.style.display = 'block';
            }
            if (input) input.focus();
            return;
        }

        if (part === 7 && n < 2) {
            if (errEl) {
                errEl.textContent = 'Part 7 yêu cầu tối thiểu 2 câu để tạo một nhóm bài đọc. Vui lòng chọn lại số lượng câu.';
                errEl.style.display = 'block';
            }
            if (input) input.focus();
            return;
        }

        // Hợp lệ: cập nhật số câu đã chọn
        selectedPracticeCount = n;

        // Đánh dấu active cho thẻ Tùy chọn, bỏ active khỏi các thẻ preset
        document.querySelectorAll('.sp-setup-inpage__count-card').forEach(function(card) {
            card.classList.remove('is-active');
        });
        var customCard = document.getElementById('customCountCard');
        if (customCard) customCard.classList.add('is-active');

        // Thêm/chọn option vào dropdown
        var select = document.getElementById('practiceCountSelect');
        if (select) {
            var found = false;
            for (var i = 0; i < select.options.length; i++) {
                if (parseInt(select.options[i].value, 10) === n) {
                    select.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                var customOpt = document.createElement('option');
                customOpt.value = n;
                customOpt.textContent = n + ' câu';
                customOpt.selected = true;
                select.appendChild(customOpt);
            }
        }

        // Cập nhật nút bắt đầu
        updateStartButton(isReviewMode, n);

        // Đóng modal
        if (modal) {
            modal.hidden = true;
            document.body.style.overflow = '';
        }
        clearPageError();
    }

    /**
     * Khi thay đổi Dropdown
     */
    function onInpageSelectChange(val) {
        clearPageError();
        selectedPracticeCount = parseInt(val, 10);
        var matched = false;
        document.querySelectorAll('.sp-setup-inpage__count-card').forEach(function(card) {
            card.classList.remove('is-active');
            var numEl = card.querySelector('.sp-setup-inpage__card-num');
            if (numEl && parseInt(numEl.textContent.trim(), 10) === selectedPracticeCount) {
                card.classList.add('is-active');
                matched = true;
            }
        });

        if (!matched) {
            var customCard = document.getElementById('customCountCard');
            if (customCard) customCard.classList.add('is-active');
        }

        var urlParams = new URLSearchParams(window.location.search);
        var isReviewMode = (urlParams.get('mode') === 'review');
        updateStartButton(isReviewMode, val);
    }

    /**
     * Bắt đầu làm bài: Validate phía Frontend trước khi điều hướng
     */
    function startInpagePractice() {
        var cfg = PRACTICE_PART_CONFIGS[activeConfigKey];
        if (!cfg) return;

        var urlParams = new URLSearchParams(window.location.search);
        var isReviewMode = (urlParams.get('mode') === 'review');

        // CHẾ ĐỘ ÔN LẠI CÂU SAI: Điều hướng làm bài ôn lại các câu đã làm sai
        if (isReviewMode) {
            var backendWrong = (window.WRONG_COUNTS_BY_PART && window.WRONG_COUNTS_BY_PART[cfg.partNum] !== undefined)
                ? window.WRONG_COUNTS_BY_PART[cfg.partNum]
                : parseInt(urlParams.get('wrong'), 10);
            var actualWrong = isNaN(backendWrong) ? 0 : backendWrong;

            if (actualWrong <= 0) {
                showPageError('Phần này hiện không có câu sai nào để ôn lại. Vui lòng quay lại trang Cải thiện.');
                return;
            }

            if (selectedPracticeCount > actualWrong) {
                showPageError('Số câu ôn lại không được vượt quá số câu sai hiện có (' + actualWrong + ' câu).');
                return;
            }

            var url = cfg.startUrl + '?part=' + cfg.partNum + '&count=' + selectedPracticeCount + '&mode=review' + (cfg.skill === 'doc' ? '&new=1' : '');
            window.location.href = url;
            return;
        }

        var part = cfg.partNum;
        var count = selectedPracticeCount;

        if (part === 5 && count < 1) {
            showPageError('Part 5 yêu cầu tối thiểu 1 câu hỏi. Vui lòng chọn lại số lượng câu.');
            return;
        }

        if (part === 6 && count < 4) {
            showPageError('Part 6 yêu cầu tối thiểu 4 câu (1 đoạn văn). Vui lòng chọn lại số lượng câu.');
            return;
        }

        if (part === 7 && count < 2) {
            showPageError('Part 7 yêu cầu tối thiểu 2 câu để tạo một nhóm bài đọc. Vui lòng chọn lại số lượng câu.');
            return;
        }

        var url = cfg.startUrl + '?part=' + cfg.partNum + '&count=' + count + (cfg.skill === 'doc' ? '&new=1' : '');
        window.location.href = url;
    }

    // Nhấn Enter trong ô input popup để xác nhận
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            var inp = document.getElementById('customPracticeCountInput');
            if (inp && document.activeElement === inp) {
                e.preventDefault();
                confirmCustomInpageCount();
            }
        }
    });

    // Tự động mở setup nếu có query param ?part=... trên URL (hoặc khi quay lại bằng browser back)
    window.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var partParam = urlParams.get('part');
        if (partParam) {
            openInpageSetup(partParam);
        }
    });

    window.addEventListener('popstate', function(e) {
        var urlParams = new URLSearchParams(window.location.search);
        var partParam = urlParams.get('part');
        if (partParam) {
            openInpageSetup(partParam);
        } else {
            closeInpageSetup();
        }
    });
</script>
@endpush

</x-layouts.app>

<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện theo Kỹ năng Đọc">

@push('styles')
    {{-- Trang Luyện Theo Kỹ Năng CSS (Đồng bộ thiết kế StudyPrep) --}}
    <link rel="stylesheet" href="{{ asset('css/pages/ky-nang.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/doc-practice.css') }}">
@endpush

<div class="sp-skill-card-wrapper">
    <div class="sp-skill-card">

        {{-- 1. Header: Nút quay lại & Tiêu đề --}}
        <div class="sp-skill-header">
            <a href="{{ url('/luyen-tap') }}" class="sp-skill-back-btn" title="Quay lại trang Luyện tập" aria-label="Quay lại Luyện tập">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M15 19l-7-7 7-7" stroke="#111C2D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <h1 class="sp-skill-title">Luyện theo Kỹ năng</h1>
        </div>

        {{-- 2. Thanh điều hướng các kỹ năng (Skill Tabs) --}}
        <nav class="sp-skill-tabs" role="tablist" aria-label="Danh sách kỹ năng">
            {{-- Tab 1: NGHE --}}
            <a href="{{ route('luyen-tap.nghe') }}" class="sp-skill-tab-item" role="tab" aria-selected="false">
                <div class="sp-skill-tab-icon sp-skill-tab-icon--nghe">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 4a8 8 0 0 0-8 8v3a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5.07A7 7 0 0 1 19 12v.07A1 1 0 0 0 18 13h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a3 3 0 0 0 3-3v-3a8 8 0 0 0-8-8Z" fill="white"/>
                    </svg>
                </div>
                <span>NGHE</span>
            </a>

            {{-- Tab 2: ĐỌC (Active mặc định) --}}
            <a href="{{ url('/luyen-tap/doc') }}" class="sp-skill-tab-item is-active" role="tab" aria-selected="true">
                <div class="sp-skill-tab-icon sp-skill-tab-icon--doc">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 19.5v-15ZM6.5 4A.5.5 0 0 0 6 4.5v15a.5.5 0 0 0 .5.5H19V4H6.5Z" fill="white"/>
                        <path d="M9 8h6M9 12h6M9 16h4" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span>ĐỌC</span>
            </a>

            {{-- Tab 3: NÓI --}}
            <a href="{{ url('/luyen-tap/noi') }}" class="sp-skill-tab-item" role="tab" aria-selected="false">
                <div class="sp-skill-tab-icon sp-skill-tab-icon--noi">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 2a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4Z" fill="white"/>
                        <path d="M6 10a6 6 0 0 0 12 0M12 18v4M8 22h8" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span>NÓI</span>
            </a>

            {{-- Tab 4: VIẾT --}}
            <a href="{{ url('/luyen-tap/viet') }}" class="sp-skill-tab-item" role="tab" aria-selected="false">
                <div class="sp-skill-tab-icon sp-skill-tab-icon--viet">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" fill="white"/>
                    </svg>
                </div>
                <span>VIẾT</span>
            </a>
        </nav>

        {{-- 3. Card Ôn tập (6 câu sai) --}}
        <div class="sp-review-banner">
            <div class="sp-review-left">
                <div class="sp-review-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                    </svg>
                </div>
                <div class="sp-review-content">
                    <span class="sp-review-tag">ÔN TẬP</span>
                    <h3 class="sp-review-title">6 câu sai</h3>
                    <p class="sp-review-desc">Bạn có muốn ôn lại câu sai bây giờ không?</p>
                </div>
            </div>
            <a href="{{ url('/cai-thien') }}" class="sp-review-btn">
                <span>Ôn lại</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        {{-- 4. Tiêu đề danh sách: "Tất cả phần luyện" --}}
        <div class="sp-section-heading-wrap">
            <span class="sp-section-heading-bars" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="sp-section-icon">
                    <rect x="4" y="5" width="2.8" height="14" rx="1.4" fill="#111C2D"/>
                    <rect x="10.5" y="5" width="2.8" height="14" rx="1.4" fill="#111C2D"/>
                    <rect x="17" y="7" width="2.8" height="10" rx="1.4" fill="#111C2D"/>
                </svg>
            </span>
            <h2 class="sp-section-heading-title">Tất cả phần luyện</h2>
        </div>

        {{-- 5. Danh sách các phần luyện TOEIC Reading --}}
        <div class="sp-reading-parts-list">

            {{-- Part 5: Hoàn thành câu --}}
            <div class="sp-reading-part-card">
                <div class="sp-reading-part-left">
                    <div class="sp-reading-part-icon">
                        <svg width="58" height="58" viewBox="0 0 64 64" fill="none">
                            <rect x="10" y="8" width="34" height="46" rx="6" fill="#FFFFFF" stroke="#111C2D" stroke-width="2.5"/>
                            <rect x="15" y="14" width="11" height="11" rx="3" fill="#38BDF8" stroke="#0284C7" stroke-width="1.5"/>
                            <path d="M18 19.5l2 2 4-4" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="15" y="28" width="11" height="11" rx="3" fill="#38BDF8" stroke="#0284C7" stroke-width="1.5"/>
                            <path d="M18 33.5l2 2 4-4" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="15" y="42" width="11" height="11" rx="3" fill="#FFFFFF" stroke="#94A3B8" stroke-width="1.5"/>
                            <g transform="translate(42, 28) rotate(42)">
                                <rect x="-24" y="-5" width="28" height="10" rx="1" fill="#F59E0B" stroke="#111C2D" stroke-width="2"/>
                                <line x1="-24" y1="0" x2="4" y2="0" stroke="#D97706" stroke-width="1.5"/>
                                <path d="M-24 -5 L-32 0 L-24 5 Z" fill="#FDE68A" stroke="#111C2D" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M-32 0 L-28 -2.5 L-28 2.5 Z" fill="#111C2D"/>
                                <rect x="4" y="-5" width="6" height="10" rx="1" fill="#EF4444" stroke="#111C2D" stroke-width="2"/>
                                <rect x="2" y="-5" width="3" height="10" fill="#94A3B8" stroke="#111C2D" stroke-width="1.5"/>
                            </g>
                        </svg>
                    </div>
                    <div class="sp-reading-part-info">
                        <h3 class="sp-reading-part-title" onclick="openPart5SetupModal()" style="cursor: pointer;">Part 5: Hoàn thành câu</h3>
                        <div style="margin-bottom: 6px;">
                            <span class="sp-reading-part-badge">Tỉ lệ đúng 40%</span>
                        </div>
                        <p class="sp-reading-part-sub">Hoàn thành 8%</p>
                        <div class="sp-reading-progressbar">
                            <div class="sp-reading-progressfill" style="width: 8%;"></div>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('luyen-tap.doc.ket-qua') }}" class="sp-reading-part-btn" style="background: #FFFFFF; color: #475569; border: 1.5px solid #CBD5E1; text-decoration: none;" title="Xem kết quả bài làm">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>Kết quả</span>
                    </a>
                    <button type="button" class="sp-reading-part-btn sp-reading-part-btn--continue" onclick="openPart5SetupModal()">
                        <span>Hoàn thành câu</span>
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </div>

            {{-- Part 6: Hoàn thành đoạn văn --}}
            <div class="sp-reading-part-card">
                <div class="sp-reading-part-left">
                    <div class="sp-reading-part-icon">
                        <svg width="58" height="58" viewBox="0 0 64 64" fill="none">
                            <rect x="12" y="10" width="28" height="38" rx="4" fill="#EEF2FF" stroke="#4F46E5" stroke-width="2.2"/>
                            <line x1="18" y1="18" x2="32" y2="18" stroke="#818CF8" stroke-width="2" stroke-linecap="round"/>
                            <line x1="18" y1="24" x2="28" y2="24" stroke="#818CF8" stroke-width="2" stroke-linecap="round"/>
                            <rect x="20" y="16" width="30" height="40" rx="5" fill="#FFFFFF" stroke="#4F46E5" stroke-width="2.2"/>
                            <line x1="26" y1="24" x2="44" y2="24" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                            <line x1="26" y1="30" x2="38" y2="30" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                            <line x1="26" y1="36" x2="42" y2="36" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                            <path d="M26 42h18a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3h-12l-4 3v-3h-2a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3z" fill="#818CF8" stroke="#4F46E5" stroke-width="2"/>
                            <circle cx="35" cy="46" r="3" fill="#FFFFFF"/>
                            <line x1="37" y1="48" x2="41" y2="52" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="sp-reading-part-info">
                        <h3 class="sp-reading-part-title">Part 6: Hoàn thành đoạn văn</h3>
                        <p class="sp-reading-part-sub">Chưa bắt đầu</p>
                    </div>
                </div>
                <button type="button" class="sp-reading-part-btn sp-reading-part-btn--start" onclick="openPracticeModal('part6')">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>

            {{-- Part 7: Đọc hiểu đoạn văn --}}
            <div class="sp-reading-part-card">
                <div class="sp-reading-part-left">
                    <div class="sp-reading-part-icon">
                        <svg width="58" height="58" viewBox="0 0 64 64" fill="none">
                            <rect x="18" y="10" width="28" height="42" rx="4" fill="#3B82F6"/>
                            <rect x="18" y="10" width="6" height="42" rx="3" fill="#2563EB"/>
                            <line x1="43" y1="12" x2="43" y2="50" stroke="#60A5FA" stroke-width="1.5"/>
                            <path d="M25 48v9l3.5-2.5 3.5 2.5v-9z" fill="#93C5FD"/>
                        </svg>
                    </div>
                    <div class="sp-reading-part-info">
                        <h3 class="sp-reading-part-title">Part 7: Đọc hiểu đoạn văn</h3>
                        <p class="sp-reading-part-sub">Chưa bắt đầu</p>
                    </div>
                </div>
                <button type="button" class="sp-reading-part-btn sp-reading-part-btn--start" onclick="openPracticeModal('part7')">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>

        </div>

    </div>
</div>

{{-- Modal Bắt đầu Luyện tập Part 5 (Khớp 100% Ảnh mẫu 3) --}}
<div id="part5SetupModal" class="sp-setup-modal-backdrop" onclick="closePart5SetupModal(event)" role="dialog" aria-modal="true">
    <div class="sp-setup-modal" onclick="event.stopPropagation()">
        {{-- Close button --}}
        <button type="button" class="sp-setup-modal__close" onclick="closePart5SetupModal()" aria-label="Đóng cửa sổ">
            ✕
        </button>

        {{-- Icon --}}
        <div class="sp-setup-modal__icon-wrap">
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
        </div>

        <div class="sp-setup-modal__tag">PART 5 · ĐỌC</div>
        <h2 class="sp-setup-modal__title">Hoàn thành câu</h2>

        {{-- Progress card --}}
        <div class="sp-setup-progress-card">
            <div class="sp-setup-progress-top">
                <span>Đã làm 10/120 câu — tiếp tục nhé</span>
                <span class="sp-setup-progress-pct">8%</span>
            </div>
            <div class="sp-setup-progress-bar">
                <div class="sp-setup-progress-fill" style="width: 8%;"></div>
            </div>
        </div>

        <div class="sp-setup-section-label">Chọn số câu luyện tập</div>

        {{-- 4 Count cards --}}
        <div class="sp-setup-count-grid">
            <div class="sp-setup-count-card" onclick="selectPracticeCount(5, this)">
                <div class="sp-setup-card-num">5</div>
                <div class="sp-setup-card-name">NHANH</div>
                <div class="sp-setup-card-time">~3 phút</div>
            </div>
            <div class="sp-setup-count-card" onclick="selectPracticeCount(10, this)">
                <div class="sp-setup-card-num">10</div>
                <div class="sp-setup-card-name">VỪA</div>
                <div class="sp-setup-card-time">~6 phút</div>
            </div>
            <div class="sp-setup-count-card is-active" onclick="selectPracticeCount(20, this)">
                <div class="sp-setup-card-num">20</div>
                <div class="sp-setup-card-name">KỸ</div>
                <div class="sp-setup-card-time">~12 phút</div>
            </div>
            <div class="sp-setup-count-card" onclick="selectCustomCount(this)">
                <div class="sp-setup-card-num" style="font-size: 18px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5B4BF5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <div class="sp-setup-card-name" style="color: #5B4BF5;">TÙY CHỌN</div>
                <div class="sp-setup-card-time">Tự nhập</div>
            </div>
        </div>

        <div class="sp-setup-dropdown-wrap">
            <label for="practiceCountSelect" class="sp-setup-dropdown-label">Chọn số câu luyện tập</label>
            <select id="practiceCountSelect" class="sp-setup-select" onchange="onSelectChange(this.value)">
                <option value="5">5 câu</option>
                <option value="10">10 câu</option>
                <option value="20" selected>20 câu</option>
                <option value="30">30 câu</option>
            </select>
        </div>

        <button type="button" class="sp-setup-start-btn" id="setupStartBtn" onclick="startPracticeSession()">
            BẮT ĐẦU 20 CÂU &rarr;
        </button>

        <div style="text-align: center; margin-top: 14px;">
            <a href="{{ route('luyen-tap.doc.ket-qua') }}" style="font-size: 13.5px; font-weight: 700; color: #64748B; text-decoration: none;">
                Hoặc xem lại kết quả bài luyện gần nhất &rarr;
            </a>
        </div>
    </div>
</div>

{{-- Modal Luyện tập đọc hiểu trực quan (Interactive Reading Session) --}}
<div id="practiceModal" class="sp-practice-modal-backdrop" onclick="closePracticeModal(event)" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="sp-practice-modal" onclick="event.stopPropagation()">
        <div class="sp-practice-modal__header">
            <div>
                <span class="sp-badge sp-badge--primary" id="modalBadge">Kỹ năng Đọc • TOEIC Reading</span>
                <h2 class="sp-practice-modal__title" id="modalTitle" style="margin-top: 6px;">Đang tải bài tập...</h2>
            </div>
            <button type="button" class="sp-practice-modal__close" onclick="closePracticeModal()" aria-label="Đóng cửa sổ">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div class="sp-practice-modal__body" id="modalContent">
            {{-- Nội dung câu hỏi đọc hiểu được render bằng JS --}}
        </div>

        <div class="sp-practice-modal__footer">
            <button type="button" class="sp-tab-btn" onclick="closePracticeModal()">Thoát bài làm</button>
            <button type="button" class="sp-tab-btn active" id="modalSubmitBtn" onclick="submitAnswer()">Kiểm tra đáp án</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Dữ liệu câu hỏi & đoạn văn đọc hiểu
    var practiceData = {
        'part5': {
            title: 'Part 5: Hoàn thành câu (Incomplete Sentences)',
            badge: 'Part 5 • Ngữ pháp & Từ vựng',
            content: `
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <span style="font-size: 13px; font-weight: 700; color: #64748B;">Câu hỏi 101 / 130:</span>
                    <p style="font-size: 16px; font-weight: 700; color: #111C2D; margin: 8px 0 0; line-height: 1.5;">
                        Mr. Henderson requested that all expense reports be submitted _______ Friday afternoon.
                    </p>
                </div>
                <div>
                    <div class="sp-question-option" onclick="selectOption(this, 'A')"><strong>A.</strong> within</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'B')"><strong>B.</strong> before</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'C')"><strong>C.</strong> until</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'D')"><strong>D.</strong> along</div>
                </div>
                <div id="answerExplanation" style="display: none; margin-top: 18px; padding: 14px; border-radius: 10px; background: #ECFDF5; border: 1px solid #BBF7D0; font-size: 14px; color: #065F46;">
                    <strong>✅ Giải thích chi tiết:</strong> Đáp án <strong>B. before</strong> là chính xác. "before Friday afternoon" nghĩa là "trước chiều thứ Sáu". Cấu trúc request that + clause (nguyên thể).
                </div>
            `,
            correct: 'B'
        },
        'part6': {
            title: 'Part 6: Hoàn thành đoạn văn (Text Completion)',
            badge: 'Part 6 • Điền từ đoạn văn',
            content: `
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 12px; padding: 18px; margin-bottom: 20px; font-size: 14px; line-height: 1.6; color: #334155;">
                    <h4 style="margin: 0 0 8px; color: #111C2D;">To: All Marketing Personnel</h4>
                    Please be reminded that our quarterly review meeting has been rescheduled. <br>
                    The presentation room has been moved to Floor 4 to _______ the large audience expected.
                </div>
                <div>
                    <div class="sp-question-option" onclick="selectOption(this, 'A')"><strong>A.</strong> accommodate</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'B')"><strong>B.</strong> accommodation</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'C')"><strong>C.</strong> accommodating</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'D')"><strong>D.</strong> accommodates</div>
                </div>
                <div id="answerExplanation" style="display: none; margin-top: 18px; padding: 14px; border-radius: 10px; background: #ECFDF5; border: 1px solid #BBF7D0; font-size: 14px; color: #065F46;">
                    <strong>✅ Giải thích chi tiết:</strong> Đáp án <strong>A. accommodate</strong> là chính xác. Sau "to" là động từ nguyên thể chỉ mục đích ("to accommodate" = để đáp ứng/chứa đủ lượng khách tham gia).
                </div>
            `,
            correct: 'A'
        },
        'part7': {
            title: 'Part 7: Đọc hiểu đoạn văn (Reading Comprehension)',
            badge: 'Part 7 • Đọc hiểu đoạn văn',
            content: `
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 12px; padding: 18px; margin-bottom: 20px; font-size: 14px; line-height: 1.6; color: #334155;">
                    <div style="font-weight: 800; color: #111C2D; margin-bottom: 6px;">[Email Notice] Apex Global Logistics</div>
                    We are thrilled to announce the opening of our new distribution center in Da Nang. With over 20,000 square meters of state-of-the-art storage, we will cut delivery times to Central Vietnam by 48 hours.
                </div>
                <p style="font-weight: 700; color: #111C2D; margin-bottom: 12px;">What is the main purpose of the announcement?</p>
                <div>
                    <div class="sp-question-option" onclick="selectOption(this, 'A')"><strong>A.</strong> To announce a relocation of company headquarters</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'B')"><strong>B.</strong> To report on new facility opening and faster delivery</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'C')"><strong>C.</strong> To advertise job vacancies in Da Nang</div>
                    <div class="sp-question-option" onclick="selectOption(this, 'D')"><strong>D.</strong> To apologize for shipping delays</div>
                </div>
                <div id="answerExplanation" style="display: none; margin-top: 18px; padding: 14px; border-radius: 10px; background: #ECFDF5; border: 1px solid #BBF7D0; font-size: 14px; color: #065F46;">
                    <strong>✅ Giải thích chi tiết:</strong> Đáp án <strong>B</strong> là chính xác ("opening of our new distribution center... cut delivery times by 48 hours").
                </div>
            `,
            correct: 'B'
        }
    };

    var currentSelectedOption = null;
    var currentExerciseKey = 'part5';

    function openPracticeModal(type) {
        currentExerciseKey = type;
        var data = practiceData[type] || practiceData['part5'];
        document.getElementById('modalTitle').textContent = data.title;
        document.getElementById('modalBadge').textContent = data.badge;
        document.getElementById('modalContent').innerHTML = data.content;
        document.getElementById('practiceModal').classList.add('is-open');
        document.body.style.overflow = 'hidden';
        currentSelectedOption = null;
    }

    function closePracticeModal(event) {
        if (event && event.target !== document.getElementById('practiceModal') && !event.target.classList.contains('sp-practice-modal__close') && !event.target.classList.contains('sp-tab-btn')) {
            return;
        }
        document.getElementById('practiceModal').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function selectOption(el, opt) {
        document.querySelectorAll('.sp-question-option').forEach(function(item) {
            item.classList.remove('is-selected');
        });
        el.classList.add('is-selected');
        currentSelectedOption = opt;
    }

    function submitAnswer() {
        if (!currentSelectedOption) {
            alert('Vui lòng chọn một đáp án (A, B, C hoặc D) trước khi kiểm tra!');
            return;
        }
        var expl = document.getElementById('answerExplanation');
        if (expl) {
            expl.style.display = 'block';
            expl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Setup Modal Part 5 (Hoàn thành câu)
    var selectedPracticeCount = 20;

    function openPart5SetupModal() {
        document.getElementById('part5SetupModal').classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closePart5SetupModal(event) {
        if (event && event.target !== document.getElementById('part5SetupModal') && !event.target.classList.contains('sp-setup-modal__close')) {
            return;
        }
        document.getElementById('part5SetupModal').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function selectPracticeCount(count, el) {
        selectedPracticeCount = count;
        document.querySelectorAll('.sp-setup-count-card').forEach(function(c) {
            c.classList.remove('is-active');
        });
        if (el) el.classList.add('is-active');
        
        var select = document.getElementById('practiceCountSelect');
        if (select) select.value = count;

        var startBtn = document.getElementById('setupStartBtn');
        if (startBtn) startBtn.innerHTML = 'BẮT ĐẦU ' + count + ' CÂU &rarr;';
    }

    function selectCustomCount(el) {
        document.querySelectorAll('.sp-setup-count-card').forEach(function(c) {
            c.classList.remove('is-active');
        });
        if (el) el.classList.add('is-active');

        var customVal = prompt('Nhập số câu bạn muốn luyện tập (từ 5 đến 30):', selectedPracticeCount);
        if (customVal && !isNaN(customVal)) {
            var n = Math.max(5, Math.min(30, parseInt(customVal, 10)));
            selectedPracticeCount = n;
            var startBtn = document.getElementById('setupStartBtn');
            if (startBtn) startBtn.innerHTML = 'BẮT ĐẦU ' + n + ' CÂU &rarr;';
        }
    }

    function onSelectChange(val) {
        selectedPracticeCount = parseInt(val, 10);
        document.querySelectorAll('.sp-setup-count-card').forEach(function(c) {
            c.classList.remove('is-active');
            var numEl = c.querySelector('.sp-setup-card-num');
            if (numEl && numEl.textContent.trim() == val) {
                c.classList.add('is-active');
            }
        });
        var startBtn = document.getElementById('setupStartBtn');
        if (startBtn) startBtn.innerHTML = 'BẮT ĐẦU ' + val + ' CÂU &rarr;';
    }

    function startPracticeSession() {
        window.location.href = "{{ route('luyen-tap.doc.hoan-thanh-cau') }}?count=" + selectedPracticeCount;
    }

    // Đóng modal bằng phím ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePracticeModal();
            closePart5SetupModal();
        }
    });
</script>
@endpush

</x-layouts.app>

<x-skill-practice-layout skill="nghe" title="StudyPrep - Luyện theo Kỹ năng Nghe">

    {{-- 1. Tiêu đề danh sách: "Tất cả phần luyện" --}}
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

    {{-- 2. Danh sách 4 Part luyện Nghe TOEIC --}}
    <div class="sp-parts-list">

        {{-- Part 1: Mô tả hình --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon" style="background: #E0F2FE;">
                    <svg width="58" height="58" viewBox="0 0 34 34" fill="none">
                        <path d="M6 25L14 15L19 21L22 17L27 25H6Z" fill="#10B981"/>
                        <circle cx="21" cy="11" r="3" fill="#F59E0B"/>
                        <g transform="translate(13, 7) rotate(32)">
                            <rect x="0" y="0" width="4" height="14" rx="1.5" fill="#F59E0B"/>
                            <path d="M0 14L2 18L4 14Z" fill="#3B82F6"/>
                        </g>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" onclick="openInpageSetup(1)">Part 1: Mô tả hình</h3>
                    <p class="sp-part-item-sub">Chưa bắt đầu</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                <button type="button" class="sp-part-btn sp-part-btn--start" onclick="openInpageSetup(1)">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </div>

        {{-- Part 2: Hỏi & Đáp --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon" style="background: #EFF6FF;">
                    <svg width="58" height="58" viewBox="0 0 34 34" fill="none">
                        <rect x="7" y="9" width="14" height="11" rx="4" fill="#38BDF8"/>
                        <path d="M10 20L8 23V20H10Z" fill="#38BDF8"/>
                        <line x1="10" y1="13" x2="18" y2="13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="10" y1="16" x2="15" y2="16" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <rect x="15" y="14" width="13" height="11" rx="4" fill="#EF4444"/>
                        <path d="M25 25L23 27V25H25Z" fill="#EF4444"/>
                        <text x="21.5" y="22.5" font-size="8" font-weight="800" fill="white" text-anchor="middle">A</text>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" onclick="openInpageSetup(2)">Part 2: Hỏi & Đáp</h3>
                    <p class="sp-part-item-sub">Chưa bắt đầu</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                <button type="button" class="sp-part-btn sp-part-btn--start" onclick="openInpageSetup(2)">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </div>

        {{-- Part 3: Hội thoại ngắn --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon" style="background: #FEF3C7;">
                    <svg width="58" height="58" viewBox="0 0 34 34" fill="none">
                        <rect x="8" y="7" width="16" height="11" rx="4" fill="#F59E0B"/>
                        <path d="M11 18L9 21V18H11Z" fill="#F59E0B"/>
                        <line x1="11" y1="11" x2="21" y2="11" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="11" y1="14" x2="17" y2="14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <rect x="13" y="15" width="15" height="11" rx="4" fill="#0D9488"/>
                        <path d="M25 26L23 28V26H25Z" fill="#0D9488"/>
                        <line x1="16" y1="19" x2="25" y2="19" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="16" y1="22" x2="22" y2="22" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" onclick="openInpageSetup(3)">Part 3: Hội thoại ngắn</h3>
                    <p class="sp-part-item-sub">Chưa bắt đầu</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                <button type="button" class="sp-part-btn sp-part-btn--start" onclick="openInpageSetup(3)">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </div>

        {{-- Part 4: Bài nói chuyện ngắn --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon" style="background: #FDF2F8;">
                    <svg width="58" height="58" viewBox="0 0 34 34" fill="none">
                        <circle cx="17" cy="11" r="3.5" fill="#FB7185"/>
                        <path d="M12 18C12 16 14 15 17 15C20 15 22 16 22 18V20H12V18Z" fill="#FDA4AF"/>
                        <path d="M13 20H21L20 28H14L13 20Z" fill="#94A3B8"/>
                        <rect x="11" y="19" width="12" height="2" rx="1" fill="#64748B"/>
                        <line x1="19" y1="16" x2="18" y2="19" stroke="#1E293B" stroke-width="1.2"/>
                        <circle cx="19" cy="15.5" r="1" fill="#1E293B"/>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" onclick="openInpageSetup(4)">Part 4: Bài nói chuyện ngắn</h3>
                    <p class="sp-part-item-sub">Chưa bắt đầu</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                <button type="button" class="sp-part-btn sp-part-btn--start" onclick="openInpageSetup(4)">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </div>

    </div>

</x-skill-practice-layout>

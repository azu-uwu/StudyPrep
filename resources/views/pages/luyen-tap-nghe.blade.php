<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện theo Kỹ năng Nghe">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-tap-nghe.css') }}">
@endpush

<div class="sp-nghe-wrapper">
    <div class="sp-nghe-card">

        {{-- 1. Header: Nút quay lại & Tiêu đề --}}
        <div class="sp-nghe-header">
            <a href="{{ url('/luyen-tap') }}" class="sp-nghe-back-btn" title="Quay lại trang Luyện tập" aria-label="Quay lại Luyện tập">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M15 19l-7-7 7-7" stroke="#111C2D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <h1 class="sp-nghe-title">Luyện theo Kỹ năng</h1>
        </div>

        {{-- 2. Thanh điều hướng các kỹ năng (Skill Tabs) --}}
        <nav class="sp-nghe-tabs" role="tablist" aria-label="Danh sách kỹ năng">
            {{-- Tab 1: NGHE (Active bám sát ảnh mẫu) --}}
            <a href="{{ route('luyen-tap.nghe') }}" class="sp-nghe-tab-item is-active" role="tab" aria-selected="true">
                <div class="sp-nghe-tab-icon sp-nghe-tab-icon--nghe">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 4a8 8 0 0 0-8 8v3a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5.07A7 7 0 0 1 19 12v.07A1 1 0 0 0 18 13h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a3 3 0 0 0 3-3v-3a8 8 0 0 0-8-8Z" fill="white"/>
                    </svg>
                </div>
                <span>NGHE</span>
            </a>

            {{-- Tab 2: ĐỌC --}}
            <a href="{{ url('/luyen-tap/doc') }}" class="sp-nghe-tab-item" role="tab" aria-selected="false">
                <div class="sp-nghe-tab-icon sp-nghe-tab-icon--doc">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 19.5v-15ZM6.5 4A.5.5 0 0 0 6 4.5v15a.5.5 0 0 0 .5.5H19V4H6.5Z" fill="white"/>
                        <path d="M9 8h6M9 12h6M9 16h4" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span>ĐỌC</span>
            </a>

            {{-- Tab 3: NÓI --}}
            <a href="{{ url('/luyen-tap/noi') }}" class="sp-nghe-tab-item" role="tab" aria-selected="false">
                <div class="sp-nghe-tab-icon sp-nghe-tab-icon--noi">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 2a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4Z" fill="white"/>
                        <path d="M6 10a6 6 0 0 0 12 0M12 18v4M8 22h8" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span>NÓI</span>
            </a>

            {{-- Tab 4: VIẾT --}}
            <a href="{{ url('/luyen-tap/viet') }}" class="sp-nghe-tab-item" role="tab" aria-selected="false">
                <div class="sp-nghe-tab-icon sp-nghe-tab-icon--viet">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" fill="white"/>
                    </svg>
                </div>
                <span>VIẾT</span>
            </a>
        </nav>

        {{-- 3. Tiêu đề danh sách: "Tất cả phần luyện" --}}
        <h2 class="sp-nghe-section-heading">Tất cả phần luyện</h2>

        {{-- 4. Danh sách 4 Part luyện Nghe (Khớp 100% ảnh mẫu 2) --}}
        <div class="sp-nghe-parts-list">

            {{-- Part 1: Mô tả hình --}}
            <div class="sp-nghe-part-card">
                <div class="sp-nghe-part-left">
                    <div class="sp-nghe-part-icon sp-nghe-part-icon--p1">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            {{-- Núi xanh --}}
                            <path d="M6 25L14 15L19 21L22 17L27 25H6Z" fill="#10B981"/>
                            {{-- Mặt trời vàng --}}
                            <circle cx="21" cy="11" r="3" fill="#F59E0B"/>
                            {{-- Bút vẽ --}}
                            <g transform="translate(13, 7) rotate(32)">
                                <rect x="0" y="0" width="4" height="14" rx="1.5" fill="#F59E0B"/>
                                <path d="M0 14L2 18L4 14Z" fill="#3B82F6"/>
                            </g>
                        </svg>
                    </div>
                    <div class="sp-nghe-part-info">
                        <a href="{{ route('luyen-tap.nghe.mo-ta-hinh') }}" class="sp-nghe-part-title">
                            Part 1: Mô tả hình
                        </a>
                        <p class="sp-nghe-part-status">Chưa bắt đầu</p>
                    </div>
                </div>
                <a href="{{ route('luyen-tap.nghe.mo-ta-hinh') }}" class="sp-nghe-start-btn">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            {{-- Part 2: Hỏi & Đáp --}}
            <div class="sp-nghe-part-card">
                <div class="sp-nghe-part-left">
                    <div class="sp-nghe-part-icon sp-nghe-part-icon--p2">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            {{-- Bubble xanh --}}
                            <rect x="7" y="9" width="14" height="11" rx="4" fill="#38BDF8"/>
                            <path d="M10 20L8 23V20H10Z" fill="#38BDF8"/>
                            <line x1="10" y1="13" x2="18" y2="13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="10" y1="16" x2="15" y2="16" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            {{-- Bubble đỏ chữ A --}}
                            <rect x="15" y="14" width="13" height="11" rx="4" fill="#EF4444"/>
                            <path d="M25 25L23 27V25H25Z" fill="#EF4444"/>
                            <text x="21.5" y="22.5" font-size="8" font-weight="800" fill="white" text-anchor="middle">A</text>
                        </svg>
                    </div>
                    <div class="sp-nghe-part-info">
                        <span class="sp-nghe-part-title">
                            Part 2: Hỏi & Đáp
                        </span>
                        <p class="sp-nghe-part-status">Chưa bắt đầu</p>
                    </div>
                </div>
                <a href="{{ route('luyen-tap.nghe.mo-ta-hinh') }}" class="sp-nghe-start-btn">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            {{-- Part 3: Hội thoại ngắn --}}
            <div class="sp-nghe-part-card">
                <div class="sp-nghe-part-left">
                    <div class="sp-nghe-part-icon sp-nghe-part-icon--p3">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            {{-- Bubble vàng cam trên --}}
                            <rect x="8" y="7" width="16" height="11" rx="4" fill="#F59E0B"/>
                            <path d="M11 18L9 21V18H11Z" fill="#F59E0B"/>
                            <line x1="11" y1="11" x2="21" y2="11" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="11" y1="14" x2="17" y2="14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            {{-- Bubble xanh ngọc dưới --}}
                            <rect x="13" y="15" width="15" height="11" rx="4" fill="#0D9488"/>
                            <path d="M25 26L23 28V26H25Z" fill="#0D9488"/>
                            <line x1="16" y1="19" x2="25" y2="19" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="16" y1="22" x2="22" y2="22" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="sp-nghe-part-info">
                        <span class="sp-nghe-part-title">
                            Part 3: Hội thoại ngắn
                        </span>
                        <p class="sp-nghe-part-status">Chưa bắt đầu</p>
                    </div>
                </div>
                <a href="{{ route('luyen-tap.nghe.mo-ta-hinh') }}" class="sp-nghe-start-btn">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            {{-- Part 4: Bài nói chuyện ngắn --}}
            <div class="sp-nghe-part-card">
                <div class="sp-nghe-part-left">
                    <div class="sp-nghe-part-icon sp-nghe-part-icon--p4">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            {{-- Người thuyết trình --}}
                            <circle cx="17" cy="11" r="3.5" fill="#FB7185"/>
                            <path d="M12 18C12 16 14 15 17 15C20 15 22 16 22 18V20H12V18Z" fill="#FDA4AF"/>
                            {{-- Bục phát biểu --}}
                            <path d="M13 20H21L20 28H14L13 20Z" fill="#94A3B8"/>
                            <rect x="11" y="19" width="12" height="2" rx="1" fill="#64748B"/>
                            {{-- Micro --}}
                            <line x1="19" y1="16" x2="18" y2="19" stroke="#1E293B" stroke-width="1.2"/>
                            <circle cx="19" cy="15.5" r="1" fill="#1E293B"/>
                        </svg>
                    </div>
                    <div class="sp-nghe-part-info">
                        <span class="sp-nghe-part-title">
                            Part 4: Bài nói chuyện ngắn
                        </span>
                        <p class="sp-nghe-part-status">Chưa bắt đầu</p>
                    </div>
                </div>
                <a href="{{ route('luyen-tap.nghe.mo-ta-hinh') }}" class="sp-nghe-start-btn">
                    <span>Bắt đầu</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

        </div>

    </div>
</div>

</x-layouts.app>

<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện theo Kỹ năng Viết">

@push('styles')
    {{-- Trang Luyện Theo Kỹ Năng CSS (Bám sát 100% ảnh mẫu tham chiếu) --}}
    <link rel="stylesheet" href="{{ asset('css/pages/ky-nang.css') }}">
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

            {{-- Tab 2: ĐỌC --}}
            <a href="{{ url('/luyen-tap/doc') }}" class="sp-skill-tab-item" role="tab" aria-selected="false">
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

            {{-- Tab 4: VIẾT (Active) --}}
            <a href="{{ url('/luyen-tap/viet') }}" class="sp-skill-tab-item is-active" role="tab" aria-selected="true">
                <div class="sp-skill-tab-icon sp-skill-tab-icon--viet">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" fill="white"/>
                    </svg>
                </div>
                <span>VIẾT</span>
            </a>
        </nav>

        {{-- 3. Danh sách các bài luyện kỹ năng Viết --}}
        <div class="sp-exercise-list">
            {{-- Lựa chọn 1: Viết câu --}}
            <a href="{{ url('/luyen-tap/viet-cau') }}" class="sp-exercise-card" role="button" aria-label="Luyện Viết câu">
                <div class="sp-exercise-card__main">
                    <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--mint">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="sp-exercise-text">
                        <h2 class="sp-exercise-title">Viết câu</h2>
                        <p class="sp-exercise-desc">Luyện cấu trúc ngữ pháp, hoàn thành câu, sửa lỗi sai và sắp xếp trật tự từ.</p>
                    </div>
                </div>
                <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            {{-- Lựa chọn 2: Viết đoạn văn --}}
            <a href="{{ url('/luyen-tap/viet-doan') }}" class="sp-exercise-card" role="button" aria-label="Luyện Viết đoạn văn">
                <div class="sp-exercise-card__main">
                    <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--amber">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="sp-exercise-text">
                        <h2 class="sp-exercise-title">Viết đoạn văn</h2>
                        <p class="sp-exercise-desc">Luyện viết thư, email công việc, đoạn văn miêu tả và bài luận nghị luận ngắn.</p>
                    </div>
                </div>
                <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

    </div>
</div>

</x-layouts.app>

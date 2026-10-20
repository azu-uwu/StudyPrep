<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện tập">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-tap.css') }}">
@endpush


{{-- Tiêu đề trang --}}
<h1 class="lt-page-title">Luyện tập</h1>

{{-- ===== ĐANG HỌC ===== --}}
<div class="lt-section">
    <div class="lt-section-header">
        <h2 class="lt-section-title">Đang học</h2>
        <span class="lt-badge-count">2 bài</span>
    </div>

    {{-- Bài 1: Nghe Part 1 --}}
    <div class="lt-progress-card">
        <div class="lt-progress-icon">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 4a8 8 0 0 0-8 8v3a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5.07A7 7 0 0 1 19 12v.07A1 1 0 0 0 18 13h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a3 3 0 0 0 3-3v-3a8 8 0 0 0-8-8Z" fill="white"/></svg>
        </div>
        <div class="lt-progress-body">
            <div class="lt-progress-top">
                <p class="lt-progress-name">Nghe: Part 1 – Mô tả tranh</p>
                <a href="{{ url('/luyen-tap') }}" class="lt-progress-cta">Tiếp tục</a>
            </div>
            <p class="lt-progress-meta">Bài đang học dang dở • 12/20 câu</p>
            <div class="lt-progress-bar-wrap">
                <div class="lt-progress-bar-fill" style="width: 60%;"></div>
            </div>
            <span class="lt-progress-pct">60% hoàn thành</span>
        </div>
    </div>

    {{-- Bài 2: Đọc Part 5 --}}
    <div class="lt-progress-card">
        <div class="lt-progress-icon">
            <svg viewBox="0 0 24 24" fill="none"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 19.5v-15ZM6.5 4A.5.5 0 0 0 6 4.5v15a.5.5 0 0 0 .5.5H19V4H6.5Z" fill="white"/><path d="M9 8h6M9 12h6M9 16h4" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div class="lt-progress-body">
            <div class="lt-progress-top">
                <p class="lt-progress-name">Đọc: Part 5 – Hoàn thành câu</p>
                <a href="{{ url('/luyen-tap') }}" class="lt-progress-cta">Tiếp tục</a>
            </div>
            <p class="lt-progress-meta">Bài đang học dang dở • 8/12 câu</p>
            <div class="lt-progress-bar-wrap">
                <div class="lt-progress-bar-fill" style="width: 67%;"></div>
            </div>
            <span class="lt-progress-pct">67% hoàn thành</span>
        </div>
    </div>
</div>

{{-- ===== LUYỆN THEO KỸ NĂNG ===== --}}
<div class="lt-section">
    <div class="lt-section-header">
        <div>
            <h2 class="lt-section-title">Luyện theo kỹ năng</h2>
            <p class="lt-section-sub">Chọn kỹ năng để bắt đầu luyện tập</p>
        </div>
    </div>

    <div class="lt-skill-grid">
        {{-- Nghe --}}
        <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" class="lt-skill-card lt-skill-card--nghe">
            <div class="lt-skill-icon lt-skill-icon--nghe">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 4a8 8 0 0 0-8 8v3a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5.07A7 7 0 0 1 19 12v.07A1 1 0 0 0 18 13h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a3 3 0 0 0 3-3v-3a8 8 0 0 0-8-8Z" fill="white"/></svg>
            </div>
            <div>
                <p class="lt-skill-label">Nghe</p>
                <p class="lt-skill-desc">Luyện kỹ năng Nghe</p>
            </div>
        </a>

        {{-- Đọc (Mở màn hình Luyện theo kỹ năng Đọc) --}}
        <a href="{{ url('/luyen-tap/doc') }}" class="lt-skill-card lt-skill-card--doc">
            <div class="lt-skill-icon lt-skill-icon--doc">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 19.5v-15ZM6.5 4A.5.5 0 0 0 6 4.5v15a.5.5 0 0 0 .5.5H19V4H6.5Z" fill="white"/><path d="M9 8h6M9 12h6M9 16h4" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div>
                <p class="lt-skill-label">Đọc</p>
                <p class="lt-skill-desc">Luyện kỹ năng Đọc</p>
            </div>
        </a>

        {{-- Nói --}}
        <a href="{{ url('/luyen-tap/doc?skill=noi') }}" class="lt-skill-card lt-skill-card--noi">
            <div class="lt-skill-icon lt-skill-icon--noi">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4Z" fill="white"/><path d="M6 10a6 6 0 0 0 12 0M12 18v4M8 22h8" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <p class="lt-skill-label">Nói</p>
                <p class="lt-skill-desc">Chấm điểm & chữa lỗi<br>Phát âm chi tiết</p>
            </div>
        </a>

        {{-- Viết --}}
        <a href="{{ url('/luyen-tap/viet') }}" class="lt-skill-card lt-skill-card--viet">
            <div class="lt-skill-icon lt-skill-icon--viet">
                <svg viewBox="0 0 24 24" fill="none"><path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" fill="white"/></svg>
            </div>
            <div>
                <p class="lt-skill-label">Viết</p>
                <p class="lt-skill-desc">Chấm điểm & Chữa bài<br>Writing chi tiết</p>
            </div>
        </a>
    </div>
</div>

{{-- ===== CỦNG CỐ TỪ VỰNG ===== --}}
<div class="lt-section">
    <div class="lt-section-header">
        <h2 class="lt-section-title">Củng cố từ vựng</h2>
        <a href="{{ url('/tu-vung') }}" class="lt-link-all">
            Xem tất cả
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    <div class="lt-vocab-grid">
        {{-- Thẻ 1: Bộ từ vựng TOEIC 2026 - LC (Ảnh 1 - Xanh lá) --}}
        <a href="{{ url('/tu-vung') }}" class="lt-vocab-card">
            <div class="lt-vocab-cover-wrap">
                <img src="{{ asset('images/vocab/vocab-toeic-2026-lc.png') }}"
                     alt="Bộ từ vựng TOEIC 2026 - LC"
                     class="lt-vocab-cover"
                     loading="lazy">
            </div>
            <span class="lt-vocab-name">Bộ từ vựng<br>TOEIC 2026 - LC</span>
        </a>

        {{-- Thẻ 2: Bộ từ vựng TOEIC 2026 - RC (Ảnh 2 - Vàng) --}}
        <a href="{{ url('/tu-vung') }}" class="lt-vocab-card">
            <div class="lt-vocab-cover-wrap">
                <img src="{{ asset('images/vocab/vocab-toeic-2026-rc.png') }}"
                     alt="Bộ từ vựng TOEIC 2026 - RC"
                     class="lt-vocab-cover"
                     loading="lazy">
            </div>
            <span class="lt-vocab-name">Bộ từ vựng<br>TOEIC 2026 - RC</span>
        </a>

        {{-- Thẻ 3: Bộ từ vựng TOEIC 2025 - LC (Ảnh 3 - Xanh dương) --}}
        <a href="{{ url('/tu-vung') }}" class="lt-vocab-card">
            <div class="lt-vocab-cover-wrap">
                <img src="{{ asset('images/vocab/vocab-toeic-2025-lc.png') }}"
                     alt="Bộ từ vựng TOEIC 2025 - LC"
                     class="lt-vocab-cover"
                     loading="lazy">
            </div>
            <span class="lt-vocab-name">Bộ từ vựng<br>TOEIC 2025 - LC</span>
        </a>
    </div>
</div>

</x-layouts.app>

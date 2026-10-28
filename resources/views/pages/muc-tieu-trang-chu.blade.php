<x-layouts.app active="muc-tieu" title="StudyPrep - Mục tiêu học tập">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu.css') }}">
@endpush

{{-- Tiêu đề trang đồng bộ chuẩn với Luyện tập --}}
<div class="mt-page-header">
    <h1 class="mt-page-title">Mục tiêu học tập</h1>
    <p class="mt-page-desc">Đặt mục tiêu hôm nay, chinh phục kỳ thi sắp tới.</p>
</div>

{{-- ===== TỔNG QUAN MỤC TIÊU TOEIC ===== --}}
<section class="mt-section mt-goal-overview">
    <div class="mt-section-header">
        <h2 class="mt-section-title">Mục tiêu TOEIC của bạn</h2>
        <span class="mt-status-badge">Đang thực hiện</span>
    </div>

    <div class="mt-score-row">
        <strong class="mt-score-val" id="targetScore">750</strong>
        <span class="mt-score-unit">/ 990 điểm mục tiêu</span>
    </div>

    <div class="mt-progress-track">
        <div class="mt-progress-fill" style="width: 61.6%;"></div>
    </div>

    <div class="mt-current-score">
        Điểm hiện tại ước tính: <strong>610/990</strong>
    </div>

    <div class="mt-goal-dates">
        <div class="mt-date-item">
            <span class="mt-date-label">Ngày dự kiến thi</span>
            <strong class="mt-date-value" id="examDateLabel">15/12/2026</strong>
        </div>
        <div class="mt-date-item">
            <span class="mt-date-label">Thời gian còn lại</span>
            <strong class="mt-date-value">63 ngày</strong>
        </div>
    </div>

    <x-button variant="primary" size="medium" href="{{ route('ho-so.muc-tieu-thiet-lap') }}" id="editGoalBtn" style="width: 100%;">
        Chỉnh sửa mục tiêu
    </x-button>
</section>

{{-- ===== MỤC TIÊU THEO KỸ NĂNG ===== --}}
<section class="mt-section mt-skill-section">
    <div class="mt-section-header-block">
        <h2 class="mt-section-title">Mục tiêu theo kỹ năng</h2>
        <p class="mt-section-sub">Vị trí hiện tại theo kỹ năng và điểm cần tiến tới</p>
    </div>

    <div class="mt-skill-list">
        <div class="mt-skill-row">
            <div class="mt-skill-label">
                <span class="mt-skill-name">Listening</span>
                <span class="mt-skill-score">320/495</span>
            </div>
            <div class="mt-skill-track">
                <div class="mt-skill-fill mt-skill-fill--listening" style="width: 64.6%;"></div>
            </div>
        </div>

        <div class="mt-skill-row">
            <div class="mt-skill-label">
                <span class="mt-skill-name">Reading</span>
                <span class="mt-skill-score">290/495</span>
            </div>
            <div class="mt-skill-track">
                <div class="mt-skill-fill mt-skill-fill--reading" style="width: 58.6%;"></div>
            </div>
        </div>

        <div class="mt-skill-row">
            <div class="mt-skill-label">
                <span class="mt-skill-name">Speaking</span>
                <span class="mt-skill-score mt-skill-score--empty">Chưa đặt mục tiêu</span>
            </div>
            <div class="mt-skill-track mt-skill-track--empty"></div>
        </div>

        <div class="mt-skill-row">
            <div class="mt-skill-label">
                <span class="mt-skill-name">Writing</span>
                <span class="mt-skill-score mt-skill-score--empty">Chưa đặt mục tiêu</span>
            </div>
            <div class="mt-skill-track mt-skill-track--empty"></div>
        </div>
    </div>

    <p class="mt-note">Listening và Reading là hai kỹ năng tính điểm TOEIC Listening &amp; Reading. Speaking và Writing áp dụng thang điểm riêng khi bạn đăng ký bài thi tương ứng.</p>
</section>

<div class="mt-toast" id="toast" role="status"></div>

@push('scripts')
<script>
    const toast = (message) => {
        const el = document.getElementById('toast');
        if (!el) return;
        el.textContent = message;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 2500);
    };

    const helpBtn = document.getElementById('helpBtn');
    if (helpBtn) {
        helpBtn.addEventListener('click', () => toast('Trung tâm hỗ trợ StudyPrep.'));
    }
</script>
@endpush

</x-layouts.app>


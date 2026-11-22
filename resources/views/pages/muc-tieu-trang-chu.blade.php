<x-layouts.app active="muc-tieu" title="StudyPrep - Mục tiêu học tập">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu.css') }}">
@endpush

{{-- Tiêu đề trang --}}
<div class="mt-page-header">
    <h1 class="mt-page-title">Mục tiêu học tập</h1>
    <p class="mt-page-desc">
        Đặt mục tiêu hôm nay, chinh phục kỳ thi sắp tới.
    </p>
</div>

{{-- ===== TỔNG QUAN MỤC TIÊU TOEIC ===== --}}
<section class="mt-section mt-goal-overview">

    <div class="mt-section-header">
        <h2 class="mt-section-title">Mục tiêu TOEIC của bạn</h2>

        <span class="mt-status-badge">
            {{ count($overview) > 0 ? 'Đang thực hiện' : 'Chưa thiết lập' }}
        </span>
    </div>

    @forelse($overview as $item)

        @php
            // Tỷ lệ mục tiêu so với điểm tối đa
            $percent = $item['total'] !== null
                ? min(100, ($item['total'] / $item['max']) * 100)
                : 0;

            // Ngày dự kiến thi
            $examDate = $item['date']
                ? \Carbon\Carbon::parse($item['date'])->format('d/m/Y')
                : 'Chưa thiết lập';
        @endphp

        {{-- Điểm mục tiêu tổng thể --}}
        <div class="mt-score-row">
            <strong
                class="mt-score-val"
                id="targetScore-{{ $loop->iteration }}">
                {{ $item['total'] ?? '—' }}
            </strong>

            <span class="mt-score-unit">
                / {{ $item['max'] }} điểm mục tiêu
                {{ $item['name'] }}
            </span>
        </div>

        {{-- Thanh biểu diễn mức điểm mục tiêu --}}
        <div class="mt-progress-track">
            <div
                class="mt-progress-fill"
                style="width: {{ round($percent, 1) }}%;">
            </div>
        </div>

        {{-- Điểm hiện tại --}}
        <div class="mt-current-score">
            Điểm hiện tại ước tính:
            <strong>Chưa có dữ liệu</strong>
        </div>

        {{-- Ngày thi và thời gian còn lại --}}
        <div class="mt-goal-dates">

            <div class="mt-date-item">
                <span class="mt-date-label">
                    Ngày dự kiến thi {{ $item['name'] }}
                </span>

                <strong
                    class="mt-date-value"
                    id="examDateLabel-{{ $loop->iteration }}">
                    {{ $examDate }}
                </strong>
            </div>

            <div class="mt-date-item">
                <span class="mt-date-label">
                    Thời gian còn lại
                </span>

                <strong class="mt-date-value">

                    @if($item['days'] === null)
                        Chưa xác định

                    @elseif($item['days'] > 0)
                        {{ $item['days'] }} ngày

                    @elseif($item['days'] === 0)
                        Hôm nay

                    @else
                        Đã qua {{ abs($item['days']) }} ngày
                    @endif

                </strong>
            </div>

        </div>

        {{-- Khoảng cách giữa 2 nhóm mục tiêu --}}
        @if(!$loop->last)
            <div style="margin-bottom: 30px;"></div>
        @endif

    @empty

        <div class="mt-current-score">
            Bạn chưa thiết lập mục tiêu TOEIC.
        </div>

    @endforelse

    {{-- Giữ nguyên component nút của bạn --}}
    <x-button
        variant="primary"
        size="medium"
        href="{{ route('profile.goal.edit') }}"
        id="editGoalBtn"
        style="width: 100%;">
        Chỉnh sửa mục tiêu
    </x-button>

</section>


{{-- ===== MỤC TIÊU THEO KỸ NĂNG ===== --}}
<section class="mt-section mt-skill-section">

    <div class="mt-section-header-block">
        <h2 class="mt-section-title">Mục tiêu theo kỹ năng</h2>

        <p class="mt-section-sub">
            Vị trí hiện tại theo kỹ năng và điểm cần tiến tới
        </p>
    </div>

    <div class="mt-skill-list">

        @forelse($skills as $skill)

            @php
                $score = $skill['score'];

                $percent = $score !== null
                    ? min(100, ($score / $skill['max']) * 100)
                    : 0;
            @endphp

            <div class="mt-skill-row">

                <div class="mt-skill-label">

                    <span class="mt-skill-name">
                        {{ $skill['name'] }}
                    </span>

                    @if($score !== null)

                        <span class="mt-skill-score">
                            {{ $score }}/{{ $skill['max'] }}
                        </span>

                    @else

                        <span class="mt-skill-score mt-skill-score--empty">
                            Chưa đặt mục tiêu
                        </span>

                    @endif

                </div>

                <div class="mt-skill-track">

                    <div
                        class="mt-skill-fill mt-skill-fill--{{ $skill['class'] }}"
                        style="width: {{ round($percent, 1) }}%;">
                    </div>

                </div>

            </div>

        @empty

            <div class="mt-skill-row">
                <span class="mt-skill-score mt-skill-score--empty">
                    Chưa đặt mục tiêu cho kỹ năng nào.
                </span>
            </div>

        @endforelse

    </div>

    <p class="mt-note">
        Listening và Reading là hai kỹ năng tính điểm TOEIC
        Listening &amp; Reading. Speaking và Writing áp dụng
        thang điểm riêng khi bạn đăng ký bài thi tương ứng.
        Các thanh điểm đang thể hiện mục tiêu so với điểm tối đa,
        không phải tiến độ học thực tế.
    </p>

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
        helpBtn.addEventListener('click', () =>
            toast('Trung tâm hỗ trợ StudyPrep.')
        );
    }

    @if(session('success'))
        toast(@json(session('success')));
    @endif
</script>
@endpush

</x-layouts.app>

<x-layouts.app active="ho-so" title="StudyPrep - Hồ sơ học viên">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu-thiet-lap.css') }}">
@endpush

<main class="setup-card">
    <a class="setup-back-btn" href="{{ url()->previous(url('/muc-tieu-trang-chu')) }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }" aria-label="Quay lại" title="Quay lại">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </a>

    <header class="setup-heading">
        <h1>Thiết lập mục tiêu</h1>
        <p>Tuỳ chỉnh lộ trình học tập cùng StudyPrep</p>
    </header>

    <form id="goalForm">
        {{-- 1. Loại bài thi (giữ nguyên radio HTML & logic) --}}
        <fieldset class="setup-fieldset">
            <legend class="setup-legend">Loại bài thi</legend>
            <label class="radio-option">
                <input type="radio" name="examType" value="lr" />
                <span>TOEIC Listening &amp; Reading</span>
            </label>
            <label class="radio-option selected">
                <input type="radio" name="examType" value="sw" checked />
                <span>TOEIC Speaking &amp; Writing</span>
            </label>
            <label class="radio-option">
                <input type="radio" name="examType" value="all" />
                <span>TOEIC 4 kỹ năng</span>
            </label>
        </fieldset>

        {{-- 2. Điểm mục tiêu tổng thể (Sử dụng x-input component có sẵn) --}}
        <x-input
            id="overallTarget"
            name="overallTarget"
            label="Điểm mục tiêu tổng thể"
            type="number"
            min="10"
            max="990"
            value="750"
            suffix="TARGET"
            required
        />

        {{-- 3. Điểm kỹ năng (Giữ nguyên lưới 2 cột, sử dụng x-input component có sẵn) --}}
        <div class="score-grid">
            <x-input
                id="listening"
                name="listening"
                label="Listening (0–495)"
                type="number"
                min="0"
                max="495"
                placeholder="Tùy chọn"
            />
            <x-input
                id="reading"
                name="reading"
                label="Reading (0–495)"
                type="number"
                min="0"
                max="495"
                placeholder="Tùy chọn"
            />
            <x-input
                id="speaking"
                name="speaking"
                label="Speaking (0–200)"
                type="number"
                min="0"
                max="200"
                placeholder="Tùy chọn"
            />
            <x-input
                id="writing"
                name="writing"
                label="Writing (0–200)"
                type="number"
                min="0"
                max="200"
                placeholder="Tùy chọn"
            />
        </div>

        {{-- 4. Ngày dự kiến thi (Giữ nguyên implementation ngày với styling đồng bộ) --}}
        <div class="exam-date-field">
            <label class="exam-date-label" for="examDate">Ngày dự kiến thi</label>
            <div class="date-wrap">
                <svg class="date-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <input id="examDate" name="examDate" type="date" value="2026-12-15" required />
            </div>
        </div>

        {{-- 5. Nút Hoàn tất (Sử dụng x-button component có sẵn) --}}
        <x-button variant="primary" size="medium" type="submit" class="full finish-button">
            Hoàn tất
        </x-button>

        {{-- 6. Hủy bỏ --}}
        <a class="cancel-link" href="{{ url()->previous(url('/muc-tieu-trang-chu')) }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">Hủy bỏ</a>
    </form>
</main>

<div class="toast" id="toast" role="status"></div>

@push('scripts')
<script>
    const form = document.getElementById('goalForm');
    const typeRadios = [...document.querySelectorAll('input[name="examType"]')];
    typeRadios.forEach(radio => radio.addEventListener('change', () => {
        document.querySelectorAll('.radio-option').forEach(label => label.classList.toggle('selected', label.querySelector('input').checked));
        const target = document.getElementById('overallTarget');
        if (!target) return;
        target.max = radio.value === 'sw' ? '400' : '990';
        if (radio.value === 'sw' && Number(target.value) > 400) target.value = 300;
        if (radio.value !== 'sw' && Number(target.value) <= 400) target.value = 750;
    }));

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const chosenType = document.querySelector('input[name="examType"]:checked').value;
        const target = Number(document.getElementById('overallTarget').value);
        if (!target || target < 10 || target > (chosenType === 'sw' ? 400 : 990)) {
            alert(chosenType === 'sw' ? 'Với Speaking & Writing, điểm tổng thể nên nằm trong khoảng 10–400.' : 'Điểm mục tiêu phải nằm trong khoảng 10–990.');
            return;
        }
        const date = document.getElementById('examDate').value;
        if (!date) { alert('Vui lòng chọn ngày dự kiến thi.'); return; }
        localStorage.setItem('studyprepGoalDemo', JSON.stringify({
            examType: chosenType,
            target,
            listening: document.getElementById('listening').value,
            reading: document.getElementById('reading').value,
            speaking: document.getElementById('speaking').value,
            writing: document.getElementById('writing').value,
            examDate: date
        }));
        const toast = document.getElementById('toast');
        toast.textContent = 'Đã lưu mục tiêu trên trình duyệt (bản demo).';
        toast.classList.add('show');
        setTimeout(() => location.href = '{{ route('muc-tieu-trang-chu') }}', 900);
    });
</script>
@endpush

</x-layouts.app>



<x-layouts.app active="ho-so" title="StudyPrep - Hồ sơ học viên">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu-thiet-lap.css') }}">
@endpush

<main class="setup-card">

    {{-- Nút quay lại --}}
    <a class="setup-back-btn"
       href="{{ route('muc-tieu-trang-chu') }}"
       aria-label="Quay lại"
       title="Quay lại">
        <svg viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2.2"
             stroke-linecap="round"
             stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </a>

    <header class="setup-heading">
        <h1>Thiết lập mục tiêu</h1>
        <p>Tuỳ chỉnh lộ trình học tập cùng StudyPrep</p>
    </header>

    {{-- Hiển thị lỗi từ Laravel để biết vì sao chưa lưu được --}}
    @if($errors->any())
        <div class="setup-validation-errors" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="goalForm"
        method="POST"
        action="{{ route('profile.goal.update') }}"
    >
        @csrf

        {{-- 1. LOẠI BÀI THI --}}
        <fieldset class="setup-fieldset">
            <legend class="setup-legend">Loại bài thi</legend>

            <label class="radio-option">
                <input type="radio"
                       name="examType"
                       value="lr"
                       @checked(old('examType', $goal?->exam_type ?? 'sw') === 'lr')>
                <span>TOEIC Listening &amp; Reading</span>
            </label>

            <label class="radio-option">
                <input type="radio"
                       name="examType"
                       value="sw"
                       @checked(old('examType', $goal?->exam_type ?? 'sw') === 'sw')>
                <span>TOEIC Speaking &amp; Writing</span>
            </label>

            <label class="radio-option">
                <input type="radio"
                       name="examType"
                       value="all"
                       @checked(old('examType', $goal?->exam_type ?? 'sw') === 'all')>
                <span>TOEIC 4 kỹ năng</span>
            </label>
        </fieldset>


        {{-- 2. NHÓM LISTENING & READING --}}
        <div id="lrFields" class="exam-group" hidden>

            {{-- Tổng điểm mục tiêu L&R --}}
            <x-input
                id="overallTargetLR"
                name="overallTargetLR"
                label="Điểm mục tiêu tổng thể Listening & Reading"
                type="number"
                min="10"
                max="990"
                step="5"
                placeholder="Tự động tính"
                suffix="TARGET"
                readonly
            />

            {{-- Điểm từng kỹ năng --}}
            <div class="score-grid">

                <x-input
                    id="listening"
                    name="listening"
                    label="Listening (5–495)"
                    type="number"
                    min="5"
                    max="495"
                    step="5"
                    value="{{ old('listening', $goal?->target_listening ?? '') }}"
                    required
                />

                <x-input
                    id="reading"
                    name="reading"
                    label="Reading (5–495)"
                    type="number"
                    min="5"
                    max="495"
                    step="5"
                    value="{{ old('reading', $goal?->target_reading ?? '') }}"
                    required
                />

            </div>

            {{-- Ngày dự kiến thi L&R --}}
            <div class="exam-date-field">
                <label class="exam-date-label"
                       for="examDateLR">
                    Ngày dự kiến thi Listening & Reading
                </label>

                <div class="date-wrap">
                    <svg class="date-icon"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <rect x="3" y="4"
                              width="18" height="18"
                              rx="2" ry="2"></rect>
                        <line x1="16" y1="2"
                              x2="16" y2="6"></line>
                        <line x1="8" y1="2"
                              x2="8" y2="6"></line>
                        <line x1="3" y1="10"
                              x2="21" y2="10"></line>
                    </svg>

                    <input
                        id="examDateLR"
                        name="examDateLR"
                        type="date"
                        value="{{ old('examDateLR', $goal?->target_date_lr?->format('Y-m-d') ?? '') }}"
                        required
                    />
                </div>
            </div>

        </div>


        {{-- 3. NHÓM SPEAKING & WRITING --}}
        <div id="swFields" class="exam-group">

            {{-- Tổng điểm mục tiêu S&W --}}
            <x-input
                id="overallTargetSW"
                name="overallTargetSW"
                label="Điểm mục tiêu tổng thể Speaking & Writing"
                type="number"
                min="0"
                max="400"
                step="10"
                placeholder="Tự động tính"
                suffix="TARGET"
                readonly
            />

            {{-- Điểm từng kỹ năng --}}
            <div class="score-grid">

                <x-input
                    id="speaking"
                    name="speaking"
                    label="Speaking (0–200)"
                    type="number"
                    min="0"
                    max="200"
                    step="10"
                    placeholder="Nhập điểm Speaking"
                    value="{{ old('speaking', $goal?->target_speaking ?? '') }}"
                    required
                />

                <x-input
                    id="writing"
                    name="writing"
                    label="Writing (0–200)"
                    type="number"
                    min="0"
                    max="200"
                    step="10"
                    placeholder="Nhập điểm Writing"
                    value="{{ old('writing', $goal?->target_writing ?? '') }}"
                    required
                />

            </div>

            {{-- Ngày dự kiến thi S&W --}}
            <div class="exam-date-field">
                <label class="exam-date-label"
                       for="examDateSW">
                    Ngày dự kiến thi Speaking & Writing
                </label>

                <div class="date-wrap">
                    <svg class="date-icon"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <rect x="3" y="4"
                              width="18" height="18"
                              rx="2" ry="2"></rect>
                        <line x1="16" y1="2"
                              x2="16" y2="6"></line>
                        <line x1="8" y1="2"
                              x2="8" y2="6"></line>
                        <line x1="3" y1="10"
                              x2="21" y2="10"></line>
                    </svg>

                    <input
                        id="examDateSW"
                        name="examDateSW"
                        type="date"
                        value="{{ old('examDateSW', $goal?->target_date_sw?->format('Y-m-d') ?? '') }}"
                        required
                    />
                </div>
            </div>

        </div>


        {{-- 4. NÚT HOÀN TẤT --}}
        <x-button
            variant="primary"
            size="medium"
            type="submit"
            class="full finish-button">
            Hoàn tất
        </x-button>

        {{-- 5. HỦY BỎ --}}
        <a class="cancel-link"
           href="{{ route('muc-tieu-trang-chu') }}">
            Hủy bỏ
        </a>

    </form>
</main>

<div class="toast" id="toast" role="status"></div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // 1. KHAI BÁO ELEMENT
    // =========================================

    const typeRadios = document.querySelectorAll(
        'input[name="examType"]'
    );

    const lrFields = document.getElementById('lrFields');
    const swFields = document.getElementById('swFields');

    const listening = document.getElementById('listening');
    const reading = document.getElementById('reading');

    const speaking = document.getElementById('speaking');
    const writing = document.getElementById('writing');

    const targetLR = document.getElementById('overallTargetLR');
    const targetSW = document.getElementById('overallTargetSW');


    // =========================================
    // 2. TÍNH TỔNG ĐIỂM MỤC TIÊU
    // =========================================

    function calculateTotal(input1, input2, target) {

        // Chưa nhập đủ hai điểm
        if (input1.value === '' || input2.value === '') {
            target.value = '';
            return;
        }

        // Điểm không hợp lệ
        if (
            !input1.checkValidity() ||
            !input2.checkValidity()
        ) {
            target.value = '';
            return;
        }

        const score1 = Number(input1.value);
        const score2 = Number(input2.value);

        target.value = score1 + score2;
    }

    function updateTotalLR() {
        calculateTotal(
            listening,
            reading,
            targetLR
        );
    }

    function updateTotalSW() {
        calculateTotal(
            speaking,
            writing,
            targetSW
        );
    }

    // Tính điểm khi người dùng nhập
    listening.addEventListener('input', updateTotalLR);
    reading.addEventListener('input', updateTotalLR);

    speaking.addEventListener('input', updateTotalSW);
    writing.addEventListener('input', updateTotalSW);


    // =========================================
    // 3. ẨN / HIỆN NHÓM BÀI THI
    // =========================================

    function toggleGroup(group, visible) {

        group.hidden = !visible;

        // Vô hiệu hóa input khi nhóm bị ẩn
        group.querySelectorAll('input').forEach(input => {
            input.disabled = !visible;
        });
    }


    function updateExamFields() {

        const selectedType = document.querySelector(
            'input[name="examType"]:checked'
        ).value;

        // Cập nhật trạng thái radio
        document.querySelectorAll('.radio-option').forEach(label => {

            const radio = label.querySelector('input');

            label.classList.toggle(
                'selected',
                radio.checked
            );
        });

        // Xác định nhóm cần hiển thị
        const showLR = selectedType === 'lr' || selectedType === 'all';
        const showSW = selectedType === 'sw' || selectedType === 'all';

        toggleGroup(lrFields, showLR);
        toggleGroup(swFields, showSW);

        // Cập nhật tổng điểm
        updateTotalLR();
        updateTotalSW();
    }


    // =========================================
    // 4. SỰ KIỆN CHỌN LOẠI BÀI THI
    // =========================================

    typeRadios.forEach(radio => {

        radio.addEventListener('change', updateExamFields);

    });

    // Không cho sửa tổng điểm bằng tay
    targetLR.readOnly = true;
    targetSW.readOnly = true;

    // Khởi tạo giao diện
    updateExamFields();


    // Form đã có method="POST", action=route(...) và @csrf.
    // KHÔNG preventDefault: trình duyệt tự gửi dữ liệu tới GoalController@store.
    // Browser tự kiểm tra required/min/max/step trước khi gửi.

});
</script>
@endpush

</x-layouts.app>

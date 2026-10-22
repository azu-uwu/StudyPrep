<x-layouts.app active="luyen-tap" title="StudyPrep - Part 1: Mô tả hình (Nghe)">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-tap-nghe.css') }}">
@endpush

<div class="sp-mo-ta-hinh-wrapper">
    <div class="sp-mo-ta-hinh-card">

        {{-- Nút X đóng ở góc trên bên trái --}}
        <a href="{{ route('luyen-tap.nghe') }}" class="sp-mo-ta-hinh-close-btn" title="Quay lại danh sách bài luyện Nghe" aria-label="Đóng">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>

        {{-- Khối icon và tiêu đề trung tâm --}}
        <div class="sp-mo-ta-hinh-header">
            <div class="sp-mo-ta-hinh-icon-wrap">
                <svg width="36" height="36" viewBox="0 0 34 34" fill="none">
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
            <div class="sp-mo-ta-hinh-tag">PART 1 · NGHE</div>
            <h1 class="sp-mo-ta-hinh-heading">Mô tả hình</h1>
        </div>

        {{-- Khối tiến độ: "Cùng bắt đầu luyện tập nào - 0%" --}}
        <div class="sp-mo-ta-hinh-progress-card">
            <div class="sp-mo-ta-hinh-progress-top">
                <span class="sp-mo-ta-hinh-progress-text">Cùng bắt đầu luyện tập nào</span>
                <span class="sp-mo-ta-hinh-progress-pct">0%</span>
            </div>
            <div class="sp-mo-ta-hinh-progress-track">
                <div class="sp-mo-ta-hinh-progress-fill" style="width: 0%;"></div>
            </div>
        </div>

        {{-- Phần chọn số câu luyện tập --}}
        <h2 class="sp-mo-ta-hinh-selection-title">Chọn số câu luyện tập</h2>

        <div class="sp-mo-ta-hinh-count-grid">
            {{-- Thẻ 1: 5 câu - NHANH (Mặc định chọn) --}}
            <div class="sp-count-card is-active" data-count="5" onclick="selectPracticeCount(5, this)">
                <div class="sp-count-card-num">5</div>
                <div class="sp-count-card-label">NHANH</div>
                <div class="sp-count-card-sub">~3 phút</div>
            </div>

            {{-- Thẻ 2: 10 câu - VỪA --}}
            <div class="sp-count-card" data-count="10" onclick="selectPracticeCount(10, this)">
                <div class="sp-count-card-num">10</div>
                <div class="sp-count-card-label">VỪA</div>
                <div class="sp-count-card-sub">~6 phút</div>
            </div>

            {{-- Thẻ 3: 20 câu - KỸ --}}
            <div class="sp-count-card" data-count="20" onclick="selectPracticeCount(20, this)">
                <div class="sp-count-card-num">20</div>
                <div class="sp-count-card-label">KỸ</div>
                <div class="sp-count-card-sub">~12 phút</div>
            </div>

            {{-- Thẻ 4: TÙY CHỌN - Tự nhập --}}
            <div class="sp-count-card" onclick="selectCustomPracticeCount(this)">
                <div class="sp-count-card-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                    </svg>
                </div>
                <div class="sp-count-card-label">TÙY CHỌN</div>
                <div class="sp-count-card-sub">Tự nhập</div>
            </div>
        </div>

        {{-- Nút Bắt đầu 5 câu → --}}
        <button type="button" class="sp-mo-ta-hinh-submit-btn" id="startPracticeBtn" onclick="handleStartListeningPractice()">
            <span>BẮT ĐẦU 5 CÂU</span>
            <span aria-hidden="true">&rarr;</span>
        </button>

    </div>
</div>

@push('scripts')
<script>
    let selectedCount = 5;

    function selectPracticeCount(count, el) {
        selectedCount = count;
        document.querySelectorAll('.sp-count-card').forEach(function(card) {
            card.classList.remove('is-active');
        });
        if (el) {
            el.classList.add('is-active');
        }

        const btn = document.getElementById('startPracticeBtn');
        if (btn) {
            btn.innerHTML = `<span>BẮT ĐẦU ${count} CÂU</span> <span aria-hidden="true">&rarr;</span>`;
        }
    }

    function selectCustomPracticeCount(el) {
        document.querySelectorAll('.sp-count-card').forEach(function(card) {
            card.classList.remove('is-active');
        });
        if (el) {
            el.classList.add('is-active');
        }

        const inputVal = prompt('Nhập số câu bạn muốn luyện tập (từ 5 đến 30 câu):', selectedCount);
        if (inputVal && !isNaN(inputVal)) {
            const num = Math.max(1, Math.min(50, parseInt(inputVal, 10)));
            selectedCount = num;
            const btn = document.getElementById('startPracticeBtn');
            if (btn) {
                btn.innerHTML = `<span>BẮT ĐẦU ${num} CÂU</span> <span aria-hidden="true">&rarr;</span>`;
            }
        }
    }

    function handleStartListeningPractice() {
        window.location.href = "{{ route('luyen-tap.nghe.lam-bai') }}?count=" + selectedCount;
    }
</script>
@endpush

</x-layouts.app>

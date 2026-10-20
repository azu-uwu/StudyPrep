<x-layouts.app active="phan-tich" title="StudyPrep - Tổng quan Phân tích">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/phan-tich-tong-quan.css') }}">
@endpush

{{-- Khung Card trắng bo tròn bám sát 100% hình ảnh tham chiếu --}}
<div class="ptq-card-container">

    {{-- 1. Header: Icon tia sét tím + Tiêu đề "Phân tích" + Nút mở trang phân tích chi tiết --}}
    <div class="ptq-header">
        <div class="ptq-header-left">
            <div class="ptq-header-icon-wrap">
                {{-- Icon tia sét màu trắng trên nền tím tròn --}}
                <svg viewBox="0 0 24 24">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
            </div>
            <h1 class="ptq-header-title">Phân tích</h1>
        </div>

        <a href="{{ url('/phan-tich/chi-tiet') }}" class="ptq-detail-btn-top" title="Xem báo cáo chi tiết đầy đủ">
            <span>Xem phân tích kết quả học tập</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
    </div>

    {{-- 2. Thanh Tabs phụ: Phần thi (Active), Luyện đề, Điểm yếu, Từ vựng, Hoạt động --}}
    <nav class="ptq-tabs-nav" aria-label="Bộ lọc phân tích">
        <button type="button" class="ptq-tab-link is-active" data-tab="part">Phần thi</button>
        <button type="button" class="ptq-tab-link" data-tab="fulltest" onclick="window.location.href='{{ url('/phan-tich/chi-tiet#fulltest') }}'">Luyện đề</button>
        <button type="button" class="ptq-tab-link" data-tab="weakness" onclick="window.location.href='{{ url('/phan-tich/chi-tiet#weakness') }}'">Điểm yếu</button>
        <button type="button" class="ptq-tab-link" data-tab="vocab" onclick="window.location.href='{{ url('/phan-tich/chi-tiet#vocab') }}'">Từ vựng</button>
        <button type="button" class="ptq-tab-link" data-tab="activity" onclick="window.location.href='{{ url('/lich-su') }}'">Hoạt động</button>
    </nav>

    {{-- 3. Hàng 3 thẻ thống kê tổng quan --}}
    <div class="ptq-stats-grid">
        {{-- Thẻ 1: Điểm cao nhất --}}
        <div class="ptq-stat-card">
            <span class="ptq-stat-label">ĐIỂM CAO NHẤT</span>
            <div class="ptq-stat-val-row">
                <span class="ptq-stat-number">---</span>
            </div>
            <p class="ptq-stat-desc">Chưa có bài Full Test</p>
        </div>

        {{-- Thẻ 2: Tỉ lệ đúng trung bình --}}
        <div class="ptq-stat-card">
            <span class="ptq-stat-label">TỈ LỆ ĐÚNG TRUNG BÌNH</span>
            <div class="ptq-stat-val-row">
                <span class="ptq-stat-number">61%</span>
                <span class="ptq-stat-sub-count">(38/62 câu)</span>
            </div>
            <div style="height: 16px;"></div>
        </div>

        {{-- Thẻ 3: Điểm yếu nhất (Viền vàng hổ phách nổi bật) --}}
        <div class="ptq-stat-card ptq-stat-card--amber">
            <span class="ptq-stat-label">ĐIỂM YẾU NHẤT</span>
            <div class="ptq-stat-val-row">
                <span class="ptq-stat-number ptq-stat-number--amber">Nghe</span>
            </div>
            <p class="ptq-stat-desc">Part 4 - Lời nói • 51%</p>
        </div>
    </div>

    {{-- 4. Biểu đồ cột: Tỉ lệ đúng theo Part --}}
    <h2 class="ptq-section-title">Tỉ lệ đúng theo Part</h2>
    <p class="ptq-section-desc">
        Tính trên mọi Part bạn đã làm (tối thiểu 5 câu) trên các mục: Bài học, Luyện đề từng Part và Full Test.
    </p>

    <div class="ptq-chart-box">
        <div class="ptq-chart-inner">
            {{-- Trục tung Y-Axis --}}
            <div class="ptq-y-axis">
                <span>100</span>
                <span>75</span>
                <span>50</span>
                <span>25</span>
                <span>0</span>
            </div>

            {{-- Các đường lưới ngang đứt nét --}}
            <div class="ptq-grid-lines">
                <div class="ptq-grid-line"></div>
                <div class="ptq-grid-line"></div>
                <div class="ptq-grid-line"></div>
                <div class="ptq-grid-line"></div>
                <div class="ptq-grid-line ptq-grid-line--base"></div>
            </div>

            {{-- Các cột biểu đồ 7 Parts --}}
            <div class="ptq-bars-wrap">
                {{-- Part 1: 81% --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label">81%</span>
                        <div class="ptq-bar-pillar ptq-bar-pillar--blue" style="height: 81%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 1</span>
                </div>

                {{-- Part 2: 60% --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label">60%</span>
                        <div class="ptq-bar-pillar ptq-bar-pillar--blue" style="height: 60%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 2</span>
                </div>

                {{-- Part 3: 73% --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label">73%</span>
                        <div class="ptq-bar-pillar ptq-bar-pillar--indigo" style="height: 73%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 3</span>
                </div>

                {{-- Part 4: 47% --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label">47%</span>
                        <div class="ptq-bar-pillar ptq-bar-pillar--indigo" style="height: 47%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 4</span>
                </div>

                {{-- Part 5: 36% --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label">36%</span>
                        <div class="ptq-bar-pillar ptq-bar-pillar--purple" style="height: 36%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 5</span>
                </div>

                {{-- Part 6: --- (Chưa có dữ liệu) --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label ptq-bar-value-label--empty">---</span>
                        <div class="ptq-bar-pillar" style="height: 0%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 6</span>
                </div>

                {{-- Part 7: --- (Chưa có dữ liệu) --}}
                <div class="ptq-bar-col">
                    <div class="ptq-bar-track">
                        <span class="ptq-bar-value-label ptq-bar-value-label--empty">---</span>
                        <div class="ptq-bar-pillar" style="height: 0%;"></div>
                    </div>
                    <span class="ptq-bar-x-label">Part 7</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Thanh thông báo & Nút xem chi tiết dưới cùng --}}
    <div class="ptq-bottom-alert">
        <div class="ptq-alert-left">
            <div class="ptq-info-icon-badge">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </div>
            <span>Bạn có <strong>24 câu sai cần ôn lại</strong></span>
        </div>

        <a href="{{ url('/phan-tich/chi-tiet') }}" class="ptq-alert-btn">
            Xem chi tiết
        </a>
    </div>

</div>

@push('scripts')
<script>
    // Tab switching support
    document.querySelectorAll('.ptq-tab-link[data-tab]').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.ptq-tab-link').forEach(t => t.classList.remove('is-active'));
            this.classList.add('is-active');
        });
    });
</script>
@endpush

</x-layouts.app>

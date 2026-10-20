<x-layouts.app active="cai-thien" title="StudyPrep - Cần cải thiện">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/cai-thien.css') }}">
@endpush

{{-- Khung hiển thị giao diện trung tâm theo đúng ảnh thiết kế --}}
<div class="ct-card-container">

    {{-- Header: Nút back & Tiêu đề "Cần cải thiện" --}}
    <div class="ct-header">
        <a href="{{ url('/phan-tich') }}" class="ct-back-btn" title="Quay lại">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </a>
        <h1 class="ct-header-title">Cần cải thiện</h1>
    </div>

    {{-- Hàng 2 thẻ thống kê: Tổng câu sai & Điểm yếu nhất --}}
    <div class="ct-stats-grid">
        {{-- Thẻ 1: Tổng câu sai --}}
        <div class="ct-stat-card">
            <div class="ct-stat-icon-wrap ct-stat-icon-wrap--amber">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 6V3.5L18.5 8H15zM8 12h8v2H8v-2zm0 4h5v2H8v-2z"/>
                </svg>
            </div>
            <div class="ct-stat-content">
                <span class="ct-stat-label">Tổng câu sai</span>
                <span class="ct-stat-number">24 câu</span>
            </div>
        </div>

        {{-- Thẻ 2: Điểm yếu nhất (Viền hồng đỏ nhạt) --}}
        <div class="ct-stat-card ct-stat-card--highlight">
            <div class="ct-stat-icon-wrap ct-stat-icon-wrap--red">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <div class="ct-stat-content">
                <span class="ct-stat-label">Điểm yếu nhất</span>
                <span class="ct-stat-number">Part 4</span>
                <span class="ct-stat-subtext">Độ thành thạo 51%</span>
            </div>
        </div>
    </div>

    {{-- Bộ chuyển đổi Segmented Switcher: Câu sai vs Điểm yếu --}}
    <div class="ct-segmented-switcher" role="tablist">
        <button type="button" class="ct-switcher-tab" id="tab-btn-wrong" onclick="switchImproveTab('wrong')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span>Câu sai</span>
        </button>

        <button type="button" class="ct-switcher-tab ct-switcher-tab--weakness is-active" id="tab-btn-weakness" onclick="switchImproveTab('weakness')">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            <span>Điểm yếu</span>
        </button>
    </div>

    {{-- ================= TAB CONTENT 1: ĐIỂM YẾU (BÁM SÁT ẢNH THAM CHIẾU) ================= --}}
    <div id="view-weakness" class="ct-tab-view is-active">
        {{-- Hàng bộ lọc: Tất cả 4 & Nghe 4 --}}
        <div class="ct-filter-pills-row">
            <button type="button" class="ct-filter-pill is-active" onclick="filterWeakness('all', this)">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span>Tất cả 4</span>
            </button>

            <button type="button" class="ct-filter-pill" onclick="filterWeakness('listen', this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
                </svg>
                <span>Nghe 4</span>
            </button>
        </div>

        {{-- Tiêu đề phân mục: Điểm yếu cần được cải thiện | TOP 4 & Tìm hiểu thêm --}}
        <div class="ct-section-meta-header">
            <h2 class="ct-section-meta-title">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
                <span>Điểm yếu cần được cải thiện | TOP 4</span>
            </h2>
            <a href="javascript:void(0)" class="ct-learn-more-link" onclick="openLearnMoreModal()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Tìm hiểu thêm</span>
            </a>
        </div>

        {{-- Danh sách 4 thẻ Điểm yếu --}}
        <div class="ct-weakness-list">
            {{-- Mục 1: Lời nói (Viền đỏ nổi bật, nút Đỏ "Luyện ngay →") --}}
            <div class="ct-weakness-card ct-weakness-card--priority">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--red">1</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE • PART 4</span>
                        <h3 class="ct-weakness-title">Lời nói</h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 51%</p>
                    </div>
                </div>
                <a href="{{ url('/luyen-tap/noi') }}" class="ct-btn-train ct-btn-train--red">
                    Luyện ngay →
                </a>
            </div>

            {{-- Mục 2: Sự vật và bối cảnh trong ảnh --}}
            <div class="ct-weakness-card">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--orange">2</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE • PART 1</span>
                        <h3 class="ct-weakness-title">
                            Sự vật và bối cảnh trong ảnh
                            <span class="ct-weakness-practiced">• Đã luyện 8 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 70%</p>
                    </div>
                </div>
                <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" class="ct-btn-train ct-btn-train--orange">
                    Luyện
                </a>
            </div>

            {{-- Mục 3: Hội thoại 2 người --}}
            <div class="ct-weakness-card">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--orange">3</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE • PART 3</span>
                        <h3 class="ct-weakness-title">
                            Hội thoại 2 người
                            <span class="ct-weakness-practiced">• Đã luyện 13 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 76%</p>
                    </div>
                </div>
                <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" class="ct-btn-train ct-btn-train--orange">
                    Luyện
                </a>
            </div>

            {{-- Mục 4: Nhân vật trong ảnh --}}
            <div class="ct-weakness-card">
                <div class="ct-weakness-left">
                    <div class="ct-rank-circle ct-rank-circle--orange">4</div>
                    <div class="ct-weakness-details">
                        <span class="ct-weakness-tag">NGHE • PART 1</span>
                        <h3 class="ct-weakness-title">
                            Nhân vật trong ảnh
                            <span class="ct-weakness-practiced">• Đã luyện 8 câu</span>
                        </h3>
                        <p class="ct-weakness-proficiency">Độ thành thạo 81%</p>
                    </div>
                </div>
                <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" class="ct-btn-train ct-btn-train--orange">
                    Luyện
                </a>
            </div>
        </div>
    </div>

    {{-- ================= TAB CONTENT 2: DANH SÁCH CÂU SAI (24 CÂU) ================= --}}
    <div id="view-wrong" class="ct-tab-view ct-wrong-questions-view">
        <div class="ct-section-meta-header">
            <h2 class="ct-section-meta-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748B;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>Danh sách 24 câu làm sai cần củng cố</span>
            </h2>
            <a href="{{ url('/luyen-tap') }}" class="ct-btn-train ct-btn-train--red" style="padding: 6px 16px; font-size: 13px;">
                Luyện lại tất cả câu sai →
            </a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 14px;">
            {{-- Câu sai mẫu 1 --}}
            <div class="ct-wrong-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 6px;">
                    <span class="ct-weakness-tag" style="color: #EF4444;">Part 4 • Bài nói chuyện ngắn (Question 71-73)</span>
                    <span style="font-size: 12px; font-weight: 700; color: #DC2626; background: #FEE2E2; padding: 2px 8px; border-radius: 6px;">Sai 2 lần</span>
                </div>
                <p style="font-size: 14.5px; font-weight: 700; color: #1E293B; margin: 0 0 6px;">What is the speaker announcing?</p>
                <div style="font-size: 13px; color: #64748B; margin-bottom: 8px;">
                    <span style="color: #DC2626; text-decoration: line-through; margin-right: 12px;">✖ Đáp án bạn chọn: (B) A renovation project</span>
                    <span style="color: #16A34A; font-weight: 700;">✔ Đáp án đúng: (C) A new store opening</span>
                </div>
                <a href="{{ url('/luyen-tap/noi') }}" style="font-size: 13px; font-weight: 700; color: #4F46E5; text-decoration: none;">Xem lời giải chi tiết &amp; Nghe lại audio →</a>
            </div>

            {{-- Câu sai mẫu 2 --}}
            <div class="ct-wrong-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 6px;">
                    <span class="ct-weakness-tag" style="color: #F59E0B;">Part 1 • Tranh mô tả vật thể (Question 03)</span>
                    <span style="font-size: 12px; font-weight: 700; color: #D97706; background: #FEF3C7; padding: 2px 8px; border-radius: 6px;">Sai 1 lần</span>
                </div>
                <p style="font-size: 14.5px; font-weight: 700; color: #1E293B; margin: 0 0 6px;">Look at the photograph and choose the statement that best describes what you see.</p>
                <div style="font-size: 13px; color: #64748B; margin-bottom: 8px;">
                    <span style="color: #DC2626; text-decoration: line-through; margin-right: 12px;">✖ Đáp án bạn chọn: (A) Chairs are stacked in the corner</span>
                    <span style="color: #16A34A; font-weight: 700;">✔ Đáp án đúng: (D) Some boxes are piled on a shelf</span>
                </div>
                <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" style="font-size: 13px; font-weight: 700; color: #4F46E5; text-decoration: none;">Xem lời giải chi tiết &amp; Nghe lại audio →</a>
            </div>

            {{-- Câu sai mẫu 3 --}}
            <div class="ct-wrong-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 6px;">
                    <span class="ct-weakness-tag" style="color: #4F46E5;">Part 3 • Hội thoại công sở (Question 44-46)</span>
                    <span style="font-size: 12px; font-weight: 700; color: #D97706; background: #FEF3C7; padding: 2px 8px; border-radius: 6px;">Sai 1 lần</span>
                </div>
                <p style="font-size: 14.5px; font-weight: 700; color: #1E293B; margin: 0 0 6px;">Why does the man want to reschedule the appointment?</p>
                <div style="font-size: 13px; color: #64748B; margin-bottom: 8px;">
                    <span style="color: #DC2626; text-decoration: line-through; margin-right: 12px;">✖ Đáp án bạn chọn: (C) His flight was delayed</span>
                    <span style="color: #16A34A; font-weight: 700;">✔ Đáp án đúng: (A) A client meeting was extended</span>
                </div>
                <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" style="font-size: 13px; font-weight: 700; color: #4F46E5; text-decoration: none;">Xem lời giải chi tiết &amp; Nghe lại audio →</a>
            </div>
        </div>
    </div>

</div>

{{-- Modal "Tìm hiểu thêm" --}}
<div id="ct-info-modal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(17, 28, 45, 0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 20px; max-width: 500px; width: 100%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); box-sizing: border-box; animation: ctModalFade 0.2s ease;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #111C2D; margin: 0;">Thuật toán xếp hạng Điểm yếu</h3>
            <button type="button" onclick="closeLearnMoreModal()" style="border: none; background: transparent; font-size: 20px; color: #64748B; cursor: pointer; padding: 4px;">✕</button>
        </div>
        <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 16px;">
            StudyPrep tự động tổng hợp tất cả các bài tập bạn đã làm để tính toán <strong>Độ thành thạo</strong> theo từng dạng bài và từng Part.
        </p>
        <ul style="font-size: 13.5px; color: #475569; line-height: 1.6; padding-left: 20px; margin: 0 0 20px;">
            <li><strong>Thành thạo &lt; 60%:</strong> Báo động đỏ, cần ưu tiên luyện tập ngay để không bị trừ điểm đáng tiếc.</li>
            <li><strong>Thành thạo 60% - 85%:</strong> Đang tiến bộ, cần làm thêm 10-15 câu để củng cố phản xạ.</li>
            <li><strong>Thành thạo &gt; 85%:</strong> Đã vững kỹ năng.</li>
        </ul>
        <button type="button" onclick="closeLearnMoreModal()" class="ct-btn-train ct-btn-train--red" style="width: 100%; border-radius: 12px; padding: 12px;">Đã hiểu</button>
    </div>
</div>

@push('scripts')
<script>
    /**
     * Chuyển đổi tab giữa "Câu sai" và "Điểm yếu"
     */
    function switchImproveTab(tab) {
        var btnWrong = document.getElementById('tab-btn-wrong');
        var btnWeakness = document.getElementById('tab-btn-weakness');
        var viewWrong = document.getElementById('view-wrong');
        var viewWeakness = document.getElementById('view-weakness');

        if (tab === 'wrong') {
            btnWrong.classList.add('is-active');
            btnWeakness.classList.remove('is-active');
            viewWrong.style.display = 'block';
            viewWeakness.style.display = 'none';
        } else {
            btnWeakness.classList.add('is-active');
            btnWrong.classList.remove('is-active');
            viewWeakness.style.display = 'block';
            viewWrong.style.display = 'none';
        }
    }

    /**
     * Lọc danh sách điểm yếu (Tất cả 4 / Nghe 4)
     */
    function filterWeakness(type, btn) {
        document.querySelectorAll('.ct-filter-pill').forEach(function(el) {
            el.classList.remove('is-active');
        });
        btn.classList.add('is-active');
    }

    /**
     * Modal tìm hiểu thêm
     */
    function openLearnMoreModal() {
        var modal = document.getElementById('ct-info-modal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeLearnMoreModal() {
        var modal = document.getElementById('ct-info-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
</script>
@endpush

</x-layouts.app>

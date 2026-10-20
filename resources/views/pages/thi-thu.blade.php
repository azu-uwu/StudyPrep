<x-layouts.app active="thi-thu" title="StudyPrep - Thi thử TOEIC & Kiểm tra đầu vào">
    {{-- Header tiêu đề trang --}}
    <div class="sp-page-header">
        <h1 class="sp-page-title">Thi thử TOEIC &amp; Kiểm tra đầu vào</h1>
        <p class="sp-page-desc">Làm quen với áp lực phòng thi thật, chấm điểm tự động và nhận phân tích chi tiết</p>
    </div>

    {{-- Quy chế thi & Hộp thông tin lượt thi --}}
    <div class="sp-grid-2-1">
        <div class="sp-card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;">
                <span class="sp-badge sp-badge--warning">Quy chế thi nghiêm ngặt</span>
                <span style="font-size: 13px; font-weight: 700; color: #D97706;">Mô phỏng 100% môi trường thi chuẩn IIG</span>
            </div>
            <ul style="margin: 0; padding-left: 20px; font-size: 14px; font-weight: 600; color: #475569; line-height: 1.6;">
                <li>Mỗi lần bắt đầu bài thi thử sẽ <strong>trừ 1 lượt thi</strong> (không hoàn lại khi thoát giữa chừng).</li>
                <li>Bắt buộc chuyển sang <strong>chế độ Toàn màn hình (Full Screen)</strong> trong suốt thời gian làm bài.</li>
                <li>Hệ thống giám sát vi phạm: Cảnh báo và ghi nhận sự kiện khi rời khỏi cửa sổ hoặc mở tab mới.</li>
                <li>Hạn chế thao tác sao chép nội dung câu hỏi (chống gian lận).</li>
            </ul>
        </div>

        <div class="sp-banner sp-banner--green" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">Lượt thi khả dụng</span>
                <div style="display: flex; align-items: baseline; gap: 8px; margin: 8px 0 4px;">
                    <span style="font-size: 38px; font-weight: 800; line-height: 1;">5</span>
                    <span style="font-size: 18px; font-weight: 700; opacity: 0.85;">/ 5 lượt</span>
                </div>
                <p style="font-size: 12px; font-weight: 600; opacity: 0.9; margin: 0;">Trừ 1 lượt khi bấm bắt đầu thi.</p>
            </div>
            <div style="margin-top: 14px;">
                <x-button variant="neutral" href="{{ url('/tu-vung') }}" size="small" style="background: #FFFFFF; color: #059669;">Hồi lượt qua Flashcard +1</x-button>
            </div>
        </div>
    </div>

    {{-- Danh sách các bộ đề thi thử --}}
    <h2 style="font-size: 20px; font-weight: 800; margin: 0 0 16px; color: #111C2D;">Danh sách đề thi tuyển chọn</h2>

    <div style="display: flex; flex-direction: column; gap: 16px;">
        {{-- Đề 1: Full Test --}}
        <div class="sp-card sp-list-item-card">
            <div class="sp-list-item-left">
                <div class="sp-item-icon" style="background: #EBF4FF; color: #0076FE;">
                    🏆
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span class="sp-badge sp-badge--primary">Full Test</span>
                        <span style="font-size: 12px; font-weight: 700; color: #64748B;">Đề thi mới nhất 2026</span>
                    </div>
                    <h3 style="font-size: 17px; font-weight: 800; margin: 0 0 4px; color: #111C2D;">TOEIC Listening &amp; Reading Full Test #01</h3>
                    <p style="font-size: 13px; font-weight: 600; color: #64748B; margin: 0;">200 câu hỏi (Part 1 - Part 7) • Thời gian: 120 phút • Hơn 1,420 lượt thi</p>
                </div>
            </div>
            <div>
                <x-button variant="primary" size="medium" data-sp-modal-open="confirm-test-modal">Bắt đầu thi →</x-button>
            </div>
        </div>

        {{-- Đề 2: Full Test 2 --}}
        <div class="sp-card sp-list-item-card">
            <div class="sp-list-item-left">
                <div class="sp-item-icon" style="background: #EBF4FF; color: #0076FE;">
                    🎯
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span class="sp-badge sp-badge--primary">Full Test</span>
                        <span style="font-size: 12px; font-weight: 700; color: #64748B;">Đề ETS chuẩn</span>
                    </div>
                    <h3 style="font-size: 17px; font-weight: 800; margin: 0 0 4px; color: #111C2D;">TOEIC Listening &amp; Reading Full Test #02</h3>
                    <p style="font-size: 13px; font-weight: 600; color: #64748B; margin: 0;">200 câu hỏi (Part 1 - Part 7) • Thời gian: 120 phút • Hơn 980 lượt thi</p>
                </div>
            </div>
            <div>
                <x-button variant="primary" size="medium" data-sp-modal-open="confirm-test-modal">Bắt đầu thi →</x-button>
            </div>
        </div>

        {{-- Đề 3: Placement Test (Kiểm tra đầu vào) --}}
        <div class="sp-card sp-list-item-card" style="border-color: #C8DDFE;">
            <div class="sp-list-item-left">
                <div class="sp-item-icon" style="background: #FEF7EA; color: #D97706;">
                    📊
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span class="sp-badge sp-badge--warning">Kiểm tra đầu vào</span>
                        <span style="font-size: 12px; font-weight: 700; color: #D97706;">Khảo sát trình độ</span>
                    </div>
                    <h3 style="font-size: 17px; font-weight: 800; margin: 0 0 4px; color: #111C2D;">Mini Placement Test - Đánh giá năng lực nhanh</h3>
                    <p style="font-size: 13px; font-weight: 600; color: #64748B; margin: 0;">50 câu rút gọn đại diện • Thời gian: 45 phút • Dự đoán Band điểm chính xác</p>
                </div>
            </div>
            <div>
                <x-button variant="warning" size="medium" data-sp-modal-open="confirm-test-modal">Kiểm tra ngay →</x-button>
            </div>
        </div>

        {{-- Đề 4: Kỹ năng riêng biệt --}}
        <div class="sp-card sp-list-item-card">
            <div class="sp-list-item-left">
                <div class="sp-item-icon" style="background: #FEECEB; color: #DC2626;">
                    🎙️
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span class="sp-badge sp-badge--danger">Speaking &amp; Writing</span>
                        <span style="font-size: 12px; font-weight: 700; color: #64748B;">Kỹ năng nâng cao</span>
                    </div>
                    <h3 style="font-size: 17px; font-weight: 800; margin: 0 0 4px; color: #111C2D;">TOEIC Speaking &amp; Writing Official Test #01</h3>
                    <p style="font-size: 13px; font-weight: 600; color: #64748B; margin: 0;">19 câu (11 Speaking + 8 Writing) • Thời gian: 80 phút • Chấm điểm AI</p>
                </div>
            </div>
            <div>
                <x-button variant="neutral" size="medium" data-sp-modal-open="confirm-test-modal">Bắt đầu thi →</x-button>
            </div>
        </div>
    </div>

    {{-- Modal xác nhận bắt đầu thi thử --}}
    <x-modal id="confirm-test-modal" title="Xác nhận bắt đầu bài thi thử" maxWidth="520px">
        <p style="margin: 0 0 12px; font-size: 15px; color: #1E293B;">
            Bạn chuẩn bị bắt đầu bài thi thử. Khi nhấn <strong>"Vào thi &amp; Toàn màn hình"</strong>:
        </p>
        <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 12px; padding: 14px; margin-bottom: 16px; font-size: 13px; color: #92400E; font-weight: 600; line-height: 1.5;">
            ⚠️ Tài khoản của bạn sẽ bị <strong>trừ 1 lượt thi</strong> (còn lại 4/5 lượt).<br>
            ⚠️ Trình duyệt sẽ tự động chuyển sang chế độ <strong>Toàn màn hình</strong>.<br>
            ⚠️ Hệ thống sẽ ghi nhận cảnh báo nếu bạn thoát ra ngoài hoặc chuyển tab.
        </div>
        <p style="margin: 0; font-size: 13px; color: #64748B;">
            Hãy đảm bảo bạn có đủ thời gian yên tĩnh và đường truyền internet ổn định trước khi bắt đầu.
        </p>
        <x-slot:actions>
            <x-button variant="neutral" data-sp-modal-close>Huỷ bỏ</x-button>
            <x-button variant="success" onclick="startFullscreenExam()">Vào thi &amp; Toàn màn hình (Trừ 1 lượt)</x-button>
        </x-slot:actions>
    </x-modal>

    <script>
        function startFullscreenExam() {
            var elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen().catch(function(err){
                    console.log('Fullscreen denied or not supported');
                });
            }
            alert('Bài thi thử đã được khởi tạo! Hệ thống đã trừ 1 lượt thi và bắt đầu tính giờ.');
            var modal = document.getElementById('confirm-test-modal');
            if (modal) modal.hidden = true;
            document.body.style.overflow = '';
        }
    </script>
</x-layouts.app>

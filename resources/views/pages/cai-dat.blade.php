<x-layouts.app active="" title="StudyPrep - Cài đặt hệ thống">
    {{-- Header tiêu đề trang --}}
    <div class="sp-page-header">
        <h1 class="sp-page-title">Cài đặt hệ thống &amp; Nhắc nhở</h1>
        <p class="sp-page-desc">Tùy biến lịch nhắc nhở học tập, âm thanh luyện thi và tài khoản liên kết</p>
    </div>

    <div style="max-width: 860px; display: flex; flex-direction: column; gap: 24px;">
        {{-- Phần 1: Nhắc nhở học tập (FR-007) --}}
        <div class="sp-card">
            <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 16px; color: #111C2D;">Nhắc nhở học tập hàng ngày</h3>
            <p style="font-size: 14px; font-weight: 500; color: #64748B; margin: 0 0 20px;">Duy trì chuỗi học tập (streak) đều đặn giúp bạn nâng band điểm nhanh hơn 40%.</p>

            <div class="sp-setting-row">
                <div class="sp-setting-info">
                    <strong class="sp-setting-title">Bật thông báo nhắc học trên trình duyệt</strong>
                    <div class="sp-setting-desc">Nhận thông báo khi đến giờ học từ vựng hoặc luyện tập</div>
                </div>
                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: #0076FE; cursor: pointer;">
            </div>

            <div class="sp-setting-row">
                <div class="sp-setting-info">
                    <strong class="sp-setting-title">Thời gian nhắc nhở mỗi ngày</strong>
                    <div class="sp-setting-desc">Khung giờ bạn rảnh nhất để làm bài</div>
                </div>
                <select class="sp-select">
                    <option value="19:00">19:00 Tối</option>
                    <option value="20:00" selected>20:00 Tối (Khuyên dùng)</option>
                    <option value="21:00">21:00 Tối</option>
                    <option value="07:00">07:00 Sáng</option>
                </select>
            </div>

            <div class="sp-setting-row" style="border-bottom: none;">
                <div class="sp-setting-info">
                    <strong class="sp-setting-title">Gửi email tóm tắt tiến độ tuần</strong>
                    <div class="sp-setting-desc">Báo cáo số câu làm đúng, số từ vựng đã nhớ vào sáng thứ Hai</div>
                </div>
                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: #0076FE; cursor: pointer;">
            </div>
        </div>

        {{-- Phần 2: Cài đặt bài thi & Âm thanh --}}
        <div class="sp-card">
            <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 16px; color: #111C2D;">Cài đặt âm thanh &amp; Phòng thi</h3>

            <div class="sp-setting-row">
                <div class="sp-setting-info">
                    <strong class="sp-setting-title">Tốc độ phát âm thanh mặc định (Listening)</strong>
                    <div class="sp-setting-desc">Tốc độ chuẩn 1.0x (tương đương kỳ thi thật)</div>
                </div>
                <select class="sp-select">
                    <option value="0.9">0.9x (Chậm nhẹ)</option>
                    <option value="1.0" selected>1.0x (Chuẩn đề thi)</option>
                    <option value="1.1">1.1x (Thử thách)</option>
                    <option value="1.2">1.2x (Nâng cao)</option>
                </select>
            </div>

            <div class="sp-setting-row" style="border-bottom: none;">
                <div class="sp-setting-info">
                    <strong class="sp-setting-title">Tự động chuyển câu hỏi tiếp theo</strong>
                    <div class="sp-setting-desc">Sau khi bấm chọn đáp án, hệ thống chuyển sang câu kế tiếp</div>
                </div>
                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: #0076FE; cursor: pointer;">
            </div>
        </div>

        {{-- Phần 3: Tài khoản liên kết (FR-003) --}}
        <div class="sp-card">
            <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 16px; color: #111C2D;">Tài khoản liên kết</h3>
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        🌐
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #111C2D;">Đăng nhập bằng tài khoản Google</strong>
                        <div style="font-size: 13px; color: #10B981; font-weight: 700; margin-top: 2px;">Đã liên kết: zuzuwu2005@gmail.com</div>
                    </div>
                </div>
                <span class="sp-badge sp-badge--success">Đã kết nối</span>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <x-button variant="neutral" href="{{ url('/luyen-tap') }}">Quay lại</x-button>
            <x-button variant="primary" onclick="alert('Đã lưu các thiết lập cài đặt của bạn thành công!')">Lưu cài đặt</x-button>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app active="" title="StudyPrep - Hồ sơ học viên">
    {{-- Header tiêu đề trang --}}
    <div class="sp-page-header">
        <h1 class="sp-page-title">Hồ sơ học viên &amp; Tài khoản</h1>
        <p class="sp-page-desc">Quản lý thông tin cá nhân, mục tiêu điểm số và bảo mật tài khoản</p>
    </div>

    <div class="sp-grid-1-2">
        {{-- Cột trái: Thẻ tóm tắt thông tin học viên --}}
        <div>
            <div class="sp-card" style="text-align: center; margin-bottom: 24px;">
                <div class="sp-avatar-circle">
                    PA
                </div>
                <h2 style="font-size: 20px; font-weight: 800; color: #111C2D; margin: 0 0 4px;">Nguyễn Phương Anh</h2>
                <p style="font-size: 13px; font-weight: 600; color: #64748B; margin: 0 0 16px;">zuzuwu2005@gmail.com</p>
                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 999px; font-size: 13px; font-weight: 700; color: #15803D;">
                    Mã HV: SP-2026-8899
                </div>

                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #F1F5F9; text-align: left;">
                    <div class="sp-profile-row">
                        <span style="font-weight: 600; color: #64748B;">Mục tiêu chứng chỉ:</span>
                        <strong style="color: #0076FE;">TOEIC 850+</strong>
                    </div>
                    <div class="sp-profile-row">
                        <span style="font-weight: 600; color: #64748B;">Lượt thi hiện tại:</span>
                        <strong style="color: #059669;">5 / 5 lượt</strong>
                    </div>
                    <div class="sp-profile-row" style="margin-bottom: 0;">
                        <span style="font-weight: 600; color: #64748B;">Ngày thi dự kiến:</span>
                        <strong style="color: #111C2D;">15/12/2026</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột phải: Form cập nhật thông tin & đổi mật khẩu --}}
        <div>
            <div class="sp-card" style="margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 18px; color: #111C2D;">Thông tin cá nhân</h3>
                <div class="sp-form-grid-2">
                    <x-input label="Họ và tên" name="full_name" value="Nguyễn Phương Anh" size="md" />
                    <x-input label="Email tài khoản" name="email" value="zuzuwu2005@gmail.com" size="md" disabled helper="Email đã xác thực bảo mật" />
                </div>
                <div class="sp-form-grid-2" style="margin-bottom: 20px;">
                    <x-input label="Mục tiêu điểm số" name="target_score" value="850" suffix="TOEIC" size="md" />
                    <x-input label="Số điện thoại" name="phone" value="0987 654 321" size="md" />
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <x-button variant="primary" size="medium">Lưu thông tin</x-button>
                </div>
            </div>

            <div class="sp-card">
                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 18px; color: #111C2D;">Bảo mật &amp; Đổi mật khẩu</h3>
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px;">
                    <x-input label="Mật khẩu hiện tại" name="current_password" type="password" placeholder="Nhập mật khẩu đang dùng..." size="md" />
                    <x-input label="Mật khẩu mới" name="new_password" type="password" placeholder="Ít nhất 8 ký tự, bao gồm chữ và số..." size="md" />
                    <x-input label="Xác nhận mật khẩu mới" name="confirm_password" type="password" placeholder="Nhập lại mật khẩu mới..." size="md" />
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <x-button variant="warning" size="medium">Cập nhật mật khẩu</x-button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

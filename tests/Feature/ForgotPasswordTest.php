<?php

namespace Tests\Feature;

use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    /**
     * Kiểm tra màn hình quên mật khẩu hiển thị flow gửi liên kết qua email.
     */
    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Quên mật khẩu?');
        $response->assertSee('Nhập email đã đăng ký và chúng tôi sẽ gửi cho bạn liên kết để đặt lại mật khẩu.');
        $response->assertSee('GỬI LIÊN KẾT ĐẶT LẠI MẬT KHẨU');
        $response->assertSee('Kiểm tra email của bạn');
        $response->assertSee('Chúng tôi đã gửi liên kết đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra hộp thư để tiếp tục.');
        $response->assertSee('QUAY LẠI ĐĂNG NHẬP');
        $response->assertSee('Cần thêm trợ giúp?');
        $response->assertDontSee('GỬI MÃ');
        $response->assertDontSee('Nhập mã');
        $response->assertDontSee('XÁC MINH');
        $response->assertDontSee('verification_code');
        $response->assertDontSee('ĐỔI MẬT KHẨU');
    }

    /**
     * Kiểm tra route tiếng Việt /quen-mat-khau chuyển hướng đúng về /forgot-password.
     */
    public function test_alias_quen_mat_khau_redirects_to_forgot_password(): void
    {
        $response = $this->get('/quen-mat-khau');

        $response->assertRedirect('/forgot-password');
    }

    /**
     * Kiểm tra trang đăng nhập chứa liên kết chuẩn đến route password.request.
     */
    public function test_login_page_links_to_forgot_password(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    /**
     * Kiểm tra màn hình quên mật khẩu hiển thị thành công với đầy đủ 3 bước.
     */
    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Quên mật khẩu?');
        $response->assertSee('GỬI MÃ');
        $response->assertSee('Nhập mã');
        $response->assertSee('XÁC MINH');
        $response->assertSee('Đặt lại mật khẩu');
        $response->assertSee('ĐỔI MẬT KHẨU');
        $response->assertSee('Cần thêm trợ giúp?');
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

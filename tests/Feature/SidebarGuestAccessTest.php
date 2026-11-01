<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SidebarGuestAccessTest extends TestCase
{
    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'studyprep',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => 3306,
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->user) {
            $this->user->delete();
            $this->user = null;
        }
        parent::tearDown();
    }

    private function getOrCreateTestUser(): User
    {
        if (! $this->user) {
            $this->user = User::create([
                'user_name' => 'test_guest_sidebar',
                'email' => 'test_guest_sidebar@example.com',
                'password_hash' => Hash::make('password123'),
                'full_name' => 'Test Guest Sidebar',
                'role' => 'student',
                'status' => 'ACTIVE',
            ]);
        }

        return $this->user;
    }

    /**
     * Guest có thể truy cập 3 trang Luyện tập, Thi thử, Từ vựng mà không bị redirect.
     */
    public function test_guest_can_access_learning_pages(): void
    {
        $resLuyenTap = $this->get('/luyen-tap');
        $resLuyenTap->assertStatus(200);

        $resThiThu = $this->get('/thi-thu');
        $resThiThu->assertStatus(200);

        $resTuVung = $this->get('/tu-vung');
        $resTuVung->assertStatus(200);
    }

    /**
     * Khi chưa đăng nhập: Sidebar hiển thị "Đăng nhập" thay vì "Hồ sơ", và KHÔNG hiển thị "Đăng xuất".
     */
    public function test_guest_sidebar_shows_login_and_hides_logout(): void
    {
        $response = $this->get('/luyen-tap');

        $response->assertStatus(200);
        $response->assertSee('Đăng nhập');
        $response->assertSee(route('login'));
        $response->assertDontSee('Đăng xuất');
    }

    /**
     * Khi chưa đăng nhập: các mục TIẾN ĐỘ trên Sidebar có attribute data-require-auth.
     */
    public function test_guest_sidebar_tien_do_items_have_data_require_auth(): void
    {
        $response = $this->get('/luyen-tap');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Kiểm tra Phân tích, Lịch sử, Cải thiện, Mục tiêu có data-require-auth
        $this->assertStringContainsString('data-require-auth', $content);
        $this->assertMatchesRegularExpression('/href="[^"]*\/phan-tich"[^>]*data-require-auth/', $content);
        $this->assertMatchesRegularExpression('/href="[^"]*\/lich-su"[^>]*data-require-auth/', $content);
        $this->assertMatchesRegularExpression('/href="[^"]*\/cai-thien"[^>]*data-require-auth/', $content);
        $this->assertMatchesRegularExpression('/href="[^"]*\/muc-tieu-trang-chu"[^>]*data-require-auth/', $content);
    }

    /**
     * Trang chứa modal yêu cầu đăng nhập dùng chung với nội dung và 2 nút theo yêu cầu.
     */
    public function test_page_renders_shared_auth_modal(): void
    {
        $response = $this->get('/luyen-tap');

        $response->assertStatus(200);
        $response->assertSee('id="sp-auth-modal"', false);
        $response->assertSee('Bạn cần đăng nhập');
        $response->assertSee('Vui lòng đăng nhập để sử dụng tính năng này.');
        $response->assertSee('Để sau');
        $response->assertSee('data-sp-modal-close', false);
        $response->assertSee('Đăng nhập');
        $response->assertSee(route('login'));
    }

    /**
     * Trong 3 trang HỌC, các nút tính năng yêu cầu tài khoản có data-require-auth.
     */
    public function test_learning_pages_have_data_require_auth_attributes(): void
    {
        $resLuyenTap = $this->get('/luyen-tap');
        $resLuyenTap->assertSee('data-require-auth');

        $resThiThu = $this->get('/thi-thu');
        $resThiThu->assertSee('data-require-auth');

        $resTuVung = $this->get('/tu-vung');
        $resTuVung->assertSee('data-require-auth');
    }

    /**
     * Khi đã đăng nhập: Sidebar hiển thị "Hồ sơ" và "Đăng xuất", không hiển thị "Đăng nhập".
     */
    public function test_authenticated_user_sidebar_shows_profile_and_logout(): void
    {
        $user = $this->getOrCreateTestUser();

        $response = $this->actingAs($user)->get('/luyen-tap');

        $response->assertStatus(200);
        $response->assertSee('Hồ sơ');
        $response->assertSee(url('/ho-so'));
        $response->assertSee('Đăng xuất');
        $response->assertSee(route('logout'));
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    private User $testUser;

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

        // Đảm bảo có user test với mật khẩu xác định
        $user = User::where('email', 'test_remember@example.com')->first();
        if (! $user) {
            $user = User::create([
                'user_name' => 'test_remember',
                'email' => 'test_remember@example.com',
                'password_hash' => Hash::make('password123'),
                'full_name' => 'Test Remember Me',
                'role' => 'student',
                'status' => 'ACTIVE',
            ]);
        } else {
            $user->update([
                'password_hash' => Hash::make('password123'),
                'status' => 'ACTIVE',
                'locked_until' => null,
                'remember_token' => null,
            ]);
        }

        $this->testUser = $user->fresh();
    }

    protected function tearDown(): void
    {
        Auth::logout();
        if (isset($this->testUser)) {
            $this->testUser->delete();
        }
        parent::tearDown();
    }

    /**
     * Helper tìm cookie recaller của guard 'web' trong mảng cookies.
     */
    private function findRecallerCookie(array $cookies): ?Cookie
    {
        $guardName = Auth::guard('web')->getRecallerName();

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $guardName) {
                return $cookie;
            }
        }

        return null;
    }

    /**
     * Trường hợp 0: Form đăng nhập hiển thị checkbox "Ghi nhớ đăng nhập".
     */
    public function test_login_page_renders_remember_me_checkbox(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Ghi nhớ đăng nhập');
        $response->assertSee('name="remember"', false);
        $response->assertSee('id="remember"', false);
        $response->assertSee('type="checkbox"', false);
    }

    /**
     * Trường hợp 1: Đăng nhập KHÔNG chọn "Ghi nhớ đăng nhập" -> hoạt động bình thường, không tạo remember cookie.
     */
    public function test_login_without_remember_me_does_not_set_remember_cookie(): void
    {
        $response = $this->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/luyen-tap');
        $this->assertAuthenticatedAs($this->testUser);

        $recallerCookie = $this->findRecallerCookie($response->headers->getCookies());
        $this->assertNull($recallerCookie, 'Không được tạo recaller cookie khi không tích Ghi nhớ đăng nhập');
    }

    /**
     * Trường hợp 2: Đăng nhập CÓ chọn "Ghi nhớ đăng nhập" -> tạo recaller cookie và lưu remember_token.
     */
    public function test_login_with_remember_me_sets_recaller_cookie_and_token(): void
    {
        $response = $this->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/luyen-tap');
        $this->assertAuthenticatedAs($this->testUser);

        $recallerCookie = $this->findRecallerCookie($response->headers->getCookies());
        $this->assertNotNull($recallerCookie, 'Phải có recaller cookie khi tích Ghi nhớ đăng nhập');
        $this->assertNotEmpty($recallerCookie->getValue());

        $this->testUser->refresh();
        $this->assertNotNull($this->testUser->remember_token, 'Bảng users phải được cập nhật remember_token');
    }

    /**
     * Trường hợp 2 (tiếp): Đóng/mở lại trình duyệt (mất session) nhưng còn cookie Remember Me -> vẫn duy trì đăng nhập.
     */
    public function test_remember_me_cookie_authenticates_user_after_session_is_lost(): void
    {
        $loginResponse = $this->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'password123',
            'remember' => '1',
        ]);

        $recallerCookie = $this->findRecallerCookie($loginResponse->headers->getCookies());
        $this->assertNotNull($recallerCookie);

        // Mô phỏng đóng trình duyệt: Xóa session và reset trạng thái auth trong RAM (không gọi logout)
        session()->flush();
        Auth::forgetGuards();
        $this->assertGuest();

        // Gửi request mới với cookie recaller đã được mã hóa sẵn từ header response
        $guardName = Auth::guard('web')->getRecallerName();
        $response = $this->withUnencryptedCookie($guardName, $recallerCookie->getValue())
            ->get('/admin/dashboard');

        // Request được nhận diện người dùng (nhận diện thành công testUser)
        $this->assertAuthenticatedAs($this->testUser);
    }

    /**
     * Trường hợp 3: Đăng nhập với sai mật khẩu -> Remember Me không được tạo.
     */
    public function test_login_with_invalid_credentials_does_not_create_remember_me(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'wrong_password',
            'remember' => '1',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();

        $recallerCookie = $this->findRecallerCookie($response->headers->getCookies());
        $this->assertNull($recallerCookie, 'Không được tạo recaller cookie khi đăng nhập thất bại');
    }

    /**
     * Trường hợp 4: Chọn Remember Me rồi đăng xuất -> không được tự động đăng nhập lại ngoài ý muốn.
     */
    public function test_logout_invalidates_remember_token_and_cookie(): void
    {
        $loginResponse = $this->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'password123',
            'remember' => '1',
        ]);

        $recallerCookie = $this->findRecallerCookie($loginResponse->headers->getCookies());
        $this->assertNotNull($recallerCookie);

        $oldToken = $this->testUser->fresh()->remember_token;
        $this->assertNotNull($oldToken);

        // Thực hiện đăng xuất
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();

        // Token trong DB đã được refresh hoặc khác token cũ
        $newToken = $this->testUser->fresh()->remember_token;
        $this->assertNotEquals($oldToken, $newToken, 'Token trong DB phải được làm mới khi logout');

        // Gửi request với cookie cũ -> không thể đăng nhập lại tự động
        session()->flush();
        Auth::logout();

        $guardName = Auth::guard('web')->getRecallerName();
        $this->call(
            'GET',
            '/admin/dashboard',
            [],
            [$guardName => $recallerCookie->getValue()]
        );

        $this->assertGuest();
    }

    /**
     * Trường hợp 5: Đăng nhập lại bình thường sau khi logout vẫn hoạt động.
     */
    public function test_login_again_after_logout_works_normally(): void
    {
        // Lần 1: Login có remember
        $this->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'password123',
            'remember' => '1',
        ]);
        $this->assertAuthenticatedAs($this->testUser);

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Lần 2: Login lại bình thường không remember
        $response = $this->post('/login', [
            'email' => $this->testUser->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/luyen-tap');
        $this->assertAuthenticatedAs($this->testUser);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createStudyPrepSchema();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_user_management(): void
    {
        $student = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($student)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_user_management_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users.index');
        $response->assertSee('Quản lý người dùng');
        $response->assertSee('TỔNG SỐ NGƯỜI DÙNG');
        $response->assertSee('GIẢNG VIÊN');
        $response->assertSee('CHẤM BÀI');
        $response->assertSee('KHÓA / CẢNH BÁO');
        $response->assertSee('Xuất dữ liệu Excel');
        $response->assertSee('Thêm người dùng mới');
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'full_name' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@studyprep.edu.vn',
            'user_name' => 'minhanh',
            'password' => 'secret123',
            'role' => 'user',
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'minhanh@studyprep.edu.vn',
            'full_name' => 'Nguyễn Minh Anh',
            'role' => 'user',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_cannot_create_duplicate_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existing@studyprep.com']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'full_name' => 'User Hai',
            'email' => 'existing@studyprep.com',
            'password' => 'secret123',
            'role' => 'user',
            'status' => 'ACTIVE',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['full_name' => 'Tên Ban Đầu']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->user_id), [
            'full_name' => 'Tên Mới Sau Cập Nhật',
            'email' => $targetUser->email,
            'role' => 'teacher',
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'user_id' => $targetUser->user_id,
            'full_name' => 'Tên Mới Sau Cập Nhật',
            'role' => 'teacher',
        ]);
    }

    public function test_admin_can_toggle_user_lock_status(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['status' => 'ACTIVE']);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-status', $targetUser->user_id));

        $response->assertRedirect();
        $this->assertEquals('LOCKED', $targetUser->fresh()->status);

        // Toggle lại thành ACTIVE
        $this->actingAs($admin)->patch(route('admin.users.toggle-status', $targetUser->user_id));
        $this->assertEquals('ACTIVE', $targetUser->fresh()->status);
    }

    public function test_admin_can_export_users_to_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.export'));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('Content-Disposition'), 'danh_sach_nguoi_dung_studyprep_'));
    }
}

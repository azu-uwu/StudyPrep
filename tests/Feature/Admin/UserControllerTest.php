<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_user_management_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users.index');
        $response->assertSee('User Management');
        $response->assertSee('TOTAL USERS');
        $response->assertSee('TEACHERS & STAFF', false);
        $response->assertSee('LOCKED / SUSPENDED');
        $response->assertSee('Export CSV');
        $response->assertSee('Add User');
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'full_name' => 'John Doe',
            'email' => 'johndoe@studyprep.edu.vn',
            'user_name' => 'johndoe',
            'password' => 'secret123',
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 35,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@studyprep.edu.vn',
            'full_name' => 'John Doe',
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 35,
        ]);
    }

    public function test_cannot_create_duplicate_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existing@studyprep.com']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'full_name' => 'User Two',
            'email' => 'existing@studyprep.com',
            'password' => 'secret123',
            'role' => 'student',
            'status' => 'ACTIVE',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_cannot_create_user_with_missing_full_name_or_invalid_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'full_name' => '',
            'email' => 'not-an-email',
            'password' => '123',
            'role' => 'invalid-role',
            'status' => 'INVALID_STATUS',
        ]);

        $response->assertSessionHasErrors(['full_name', 'email', 'password', 'role', 'status']);
    }

    public function test_cannot_update_user_with_missing_full_name(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->user_id), [
            'full_name' => '',
            'email' => $targetUser->email,
            'role' => 'student',
            'status' => 'ACTIVE',
        ]);

        $response->assertSessionHasErrors('full_name');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['full_name' => 'Original Name']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->user_id), [
            'full_name' => 'Updated Name',
            'email' => $targetUser->email,
            'role' => 'teacher',
            'status' => 'ACTIVE',
            'energy' => 50,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'user_id' => $targetUser->user_id,
            'full_name' => 'Updated Name',
            'role' => 'teacher',
            'energy' => 50,
        ]);
    }

    public function test_admin_cannot_demote_self_or_lock_self(): void
    {
        $admin = User::factory()->admin()->create();

        // Attempt to demote own admin role
        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin->user_id), [
            'full_name' => $admin->full_name,
            'email' => $admin->email,
            'role' => 'student',
            'status' => 'ACTIVE',
        ]);
        $response->assertSessionHas('error');

        // Attempt to lock own account
        $responseLock = $this->actingAs($admin)->put(route('admin.users.update', $admin->user_id), [
            'full_name' => $admin->full_name,
            'email' => $admin->email,
            'role' => 'admin',
            'status' => 'LOCKED',
        ]);
        $responseLock->assertSessionHas('error');
    }

    public function test_admin_can_toggle_user_lock_status(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['status' => 'ACTIVE']);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-status', $targetUser->user_id));

        $response->assertRedirect();
        $this->assertEquals('LOCKED', $targetUser->fresh()->status);

        // Toggle back to ACTIVE
        $this->actingAs($admin)->patch(route('admin.users.toggle-status', $targetUser->user_id));
        $this->assertEquals('ACTIVE', $targetUser->fresh()->status);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.users.reset-password', $targetUser->user_id), [
            'new_password' => 'StudyPrep@9999',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $targetUser->user_id));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', [
            'user_id' => $targetUser->user_id,
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->user_id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'user_id' => $admin->user_id,
        ]);
    }

    public function test_admin_can_perform_bulk_actions(): void
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create(['status' => 'ACTIVE']);
        $user2 = User::factory()->create(['status' => 'ACTIVE']);

        // Bulk lock
        $responseLock = $this->actingAs($admin)->post(route('admin.users.bulk-action'), [
            'action' => 'lock',
            'user_ids' => [$user1->user_id, $user2->user_id],
        ]);
        $responseLock->assertRedirect(route('admin.users.index'));
        $this->assertEquals('LOCKED', $user1->fresh()->status);
        $this->assertEquals('LOCKED', $user2->fresh()->status);

        // Bulk unlock
        $responseUnlock = $this->actingAs($admin)->post(route('admin.users.bulk-action'), [
            'action' => 'unlock',
            'user_ids' => [$user1->user_id, $user2->user_id],
        ]);
        $responseUnlock->assertRedirect(route('admin.users.index'));
        $this->assertEquals('ACTIVE', $user1->fresh()->status);
        $this->assertEquals('ACTIVE', $user2->fresh()->status);

        // Bulk delete
        $responseDel = $this->actingAs($admin)->post(route('admin.users.bulk-action'), [
            'action' => 'delete',
            'user_ids' => [$user1->user_id, $user2->user_id],
        ]);
        $responseDel->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['user_id' => $user1->user_id]);
        $this->assertDatabaseMissing('users', ['user_id' => $user2->user_id]);
    }

    public function test_admin_can_export_users_to_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.export'));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('Content-Disposition'), 'studyprep_users_'));
    }

    public function test_admin_can_download_sample_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.sample-csv'));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('Content-Disposition'), 'sample_users_template.csv'));
    }

    public function test_admin_can_import_users_from_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $csvContent = "Full Name,Email,Role,Status,Password\n"
            ."Imported Student,imported.student@example.com,student,ACTIVE,Secret123\n"
            ."Imported Teacher,imported.teacher@example.com,teacher,ACTIVE,Secret123\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.users.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'imported.student@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'imported.teacher@example.com', 'role' => 'teacher']);
    }
}

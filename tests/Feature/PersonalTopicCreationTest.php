<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class PersonalTopicCreationTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // RÀNG BUỘC AN TOÀN CSDL TUYỆT ĐỐI:
        // Đảm bảo test CHỈ chạy trên SQLite in-memory độc lập, không chạm vào MySQL thật.
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('Bảo vệ an toàn: Test chỉ được phép chạy trên SQLite :memory:');
        }

        $this->createStudyPrepSchema();

        if (! Schema::hasTable('user_vocabulary')) {
            Schema::create('user_vocabulary', function (Blueprint $table): void {
                $table->id('user_voca_id');
                $table->unsignedBigInteger('vocabulary_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('learned_at')->nullable();
                $table->boolean('is_done')->default(false);
            });
        }

        $this->student = User::create([
            'user_name' => 'student_'.uniqid(),
            'full_name' => 'Học viên Test',
            'email' => 'student_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 5,
        ]);
    }

    /**
     * Trường hợp 1: Học viên tạo chủ đề thành công qua JSON request.
     */
    public function test_student_can_create_personal_topic_successfully(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Từ vựng TOEIC Part 1',
                'description' => 'Mô tả bộ từ vựng luyện thi Part 1',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Tạo chủ đề thành công!',
                'topic' => [
                    'name' => 'Từ vựng TOEIC Part 1',
                    'description' => 'Mô tả bộ từ vựng luyện thi Part 1',
                    'user_id' => $this->student->user_id,
                    'total_vocabularies' => 0,
                ],
            ]);
    }

    /**
     * Trường hợp 2: Chủ đề được lưu đúng name và description trong CSDL.
     */
    public function test_topic_is_saved_with_correct_name_and_description(): void
    {
        $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Business Communication',
                'description' => 'Từ vựng giao tiếp kinh doanh chuyên sâu',
            ]);

        $this->assertDatabaseHas('topics', [
            'name' => 'Chủ đề Business Communication',
            'description' => 'Từ vựng giao tiếp kinh doanh chuyên sâu',
            'user_id' => $this->student->user_id,
        ]);
    }

    /**
     * Trường hợp 3: user_id được gán đúng người đang đăng nhập.
     */
    public function test_topic_user_id_matches_authenticated_user(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Kiểm tra User ID',
            ]);

        $response->assertStatus(201);

        $topic = Topic::where('name', 'Chủ đề Kiểm tra User ID')->first();
        $this->assertNotNull($topic);
        $this->assertSame((int) $this->student->user_id, (int) $topic->user_id);
    }

    /**
     * Trường hợp 4: parent_topic_id luôn là NULL.
     */
    public function test_parent_topic_id_is_always_null(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Không có Parent',
                'parent_topic_id' => 999, // Cố tình truyền parent_topic_id
            ]);

        $response->assertStatus(201);

        $topic = Topic::where('name', 'Chủ đề Không có Parent')->first();
        $this->assertNotNull($topic);
        $this->assertNull($topic->parent_topic_id);
    }

    /**
     * Trường hợp 5: Người dùng không thể giả mạo user_id bằng cách gửi kèm trong request.
     */
    public function test_user_cannot_spoof_user_id(): void
    {
        $anotherUserId = 88888;

        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Chống Spoofing',
                'user_id' => $anotherUserId, // Cố tình giả mạo tài khoản khác
            ]);

        $response->assertStatus(201);

        $topic = Topic::where('name', 'Chủ đề Chống Spoofing')->first();
        $this->assertNotNull($topic);
        $this->assertSame((int) $this->student->user_id, (int) $topic->user_id);
        $this->assertNotSame($anotherUserId, (int) $topic->user_id);
    }

    /**
     * Trường hợp 6: Không tạo chủ đề nếu tên trống.
     */
    public function test_cannot_create_topic_with_empty_name(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => '',
                'description' => 'Mô tả hợp lệ',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertSame(0, Topic::count());
    }

    /**
     * Trường hợp 7: Không tạo chủ đề nếu tên chỉ gồm khoảng trắng.
     */
    public function test_cannot_create_topic_with_whitespace_only_name(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => "   \t   \n  ",
                'description' => 'Mô tả hợp lệ',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertSame(0, Topic::count());
    }

    /**
     * Trường hợp 8: Không tạo chủ đề nếu tên dài quá 100 ký tự.
     */
    public function test_cannot_create_topic_with_name_exceeding_100_characters(): void
    {
        $longName = str_repeat('a', 101);

        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => $longName,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertSame(0, Topic::count());
    }

    /**
     * Trường hợp 9: Mô tả có thể để trống hoặc null.
     */
    public function test_description_can_be_empty_or_null(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Không Có Mô Tả',
                'description' => '',
            ]);

        $response->assertStatus(201);

        $topic = Topic::where('name', 'Chủ đề Không Có Mô Tả')->first();
        $this->assertNotNull($topic);
        $this->assertNull($topic->description);
    }

    /**
     * Trường hợp 10: Khách chưa đăng nhập không thể tạo chủ đề.
     */
    public function test_guest_cannot_create_personal_topic(): void
    {
        $response = $this->postJson(route('tu-vung.topics.store'), [
            'name' => 'Chủ đề Của Khách',
        ]);

        $response->assertStatus(401);
        $this->assertSame(0, Topic::count());
    }

    /**
     * Trường hợp 11: Chủ đề mới chỉ hiển thị cho chủ sở hữu, không hiển thị cho người khác.
     */
    public function test_personal_topic_is_only_visible_to_owner(): void
    {
        // Học viên 1 tạo chủ đề
        $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Riêng Của Học Viên 1',
            ]);

        $topic = Topic::where('name', 'Chủ đề Riêng Của Học Viên 1')->first();
        $this->assertNotNull($topic);

        // Học viên 2 đăng nhập
        $student2 = User::create([
            'user_name' => 'student2_'.uniqid(),
            'full_name' => 'Học viên 2',
            'email' => 'student2_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 5,
        ]);

        // Học viên 2 xem trang /tu-vung, không thấy chủ đề của Học viên 1
        $viewResponse = $this->actingAs($student2)->get(route('tu-vung'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertDontSee('Chủ đề Riêng Của Học Viên 1');

        // Học viên 2 truy cập trực tiếp URL của chủ đề của Học viên 1 thì bị 403 Forbidden
        $showResponse = $this->actingAs($student2)->get(route('tu-vung.show', $topic->topic_id));
        $showResponse->assertStatus(403);
    }

    /**
     * Trường hợp 12: Chủ đề hệ thống không bị ảnh hưởng khi tạo chủ đề cá nhân.
     */
    public function test_system_topics_are_not_affected_by_personal_topic_creation(): void
    {
        // Tạo một chủ đề hệ thống có user_id IS NULL
        $systemTopic = Topic::create([
            'name' => 'Chủ đề Hệ Thống TOEIC 2026',
            'description' => 'Chủ đề dùng chung toàn hệ thống',
            'user_id' => null,
            'parent_topic_id' => null,
        ]);

        // Học viên tạo chủ đề cá nhân
        $this->actingAs($this->student)
            ->postJson(route('tu-vung.topics.store'), [
                'name' => 'Chủ đề Cá Nhân Học Viên',
            ]);

        // Xác nhận chủ đề hệ thống vẫn giữ nguyên user_id = null
        $refreshedSystemTopic = Topic::find($systemTopic->topic_id);
        $this->assertNotNull($refreshedSystemTopic);
        $this->assertNull($refreshedSystemTopic->user_id);
        $this->assertTrue($refreshedSystemTopic->isSystem());

        // Kiểm tra trang /tu-vung hiển thị chủ đề hệ thống trong danh sách chung
        $response = $this->actingAs($this->student)->get(route('tu-vung'));
        $response->assertStatus(200);
        $response->assertSee('Chủ đề Hệ Thống TOEIC 2026');
        $response->assertSee('Chủ đề Cá Nhân Học Viên');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class VocabularyPracticeTest extends TestCase
{
    /**
     * Kiểm tra trang chính Từ vựng (/tu-vung) hiển thị danh sách chủ đề phổ biến có link điều hướng.
     */
    public function test_vocabulary_index_renders_topics_with_links(): void
    {
        $response = $this->get('/tu-vung');

        $response->assertStatus(200);
        $response->assertSee('Từ vựng');
        $response->assertSee('Chủ đề phổ biến');
        $response->assertSee('/tu-vung/toeic-lc-2026');
        $response->assertSee('/tu-vung/toeic-rc-2026');
        $response->assertSee('tv-tactile-card');
    }

    /**
     * Kiểm tra trang Danh sách từ vựng theo chủ đề (/tu-vung/test-1).
     */
    public function test_vocabulary_list_renders_test_1_topic_details_and_cards(): void
    {
        $response = $this->get('/tu-vung/test-1');

        $response->assertStatus(200);
        $response->assertSee('Danh sách từ vựng');
        $response->assertSee('Test 1');
        $response->assertSee('80 từ vựng');
        $response->assertSee('Tiến độ');
        $response->assertSee('HỌC TỪ MỚI');
        $response->assertSee('Tất cả');
        $response->assertSee('Nắm vững');
        $response->assertSee('Dễ quên');
        $response->assertSee('Bỏ qua');
        $response->assertSee('combine');
        $response->assertSee('kết hợp');
        $response->assertSee('administrator');
        $response->assertSee('người quản trị');
        $response->assertSee('assign');
    }

    /**
     * Kiểm tra routing số nguyên (/tu-vung/1) cũng trỏ đúng Test 1.
     */
    public function test_vocabulary_list_numeric_id_mapping(): void
    {
        $response = $this->get('/tu-vung/1');

        $response->assertStatus(200);
        $response->assertSee('Test 1');
        $response->assertSee('combine');
    }

    /**
     * Kiểm tra chủ đề TOEIC LC 2026 (/tu-vung/toeic-lc-2026).
     */
    public function test_vocabulary_list_toeic_lc_2026(): void
    {
        $response = $this->get('/tu-vung/toeic-lc-2026');

        $response->assertStatus(200);
        $response->assertSee('800 Từ vựng TOEIC LC 2026');
        $response->assertSee('800 từ vựng');
    }

    /**
     * Kiểm tra Empty State khi chủ đề chưa có từ vựng.
     */
    public function test_vocabulary_list_empty_state(): void
    {
        $response = $this->get('/tu-vung/chu-de-rong');

        $response->assertStatus(200);
        $response->assertSee('Chủ đề này chưa có từ vựng.');
    }

    /**
     * Kiểm tra Fallback an toàn khi topicId không tồn tại trong hệ thống.
     */
    public function test_vocabulary_list_safe_fallback_for_unknown_topic(): void
    {
        $response = $this->get('/tu-vung/unknown-topic-999');

        $response->assertStatus(200);
        $response->assertSee('Không tìm thấy chủ đề này');
    }
}

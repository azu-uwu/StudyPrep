<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpeakingReadAloudTest extends TestCase
{
    /**
     * Test màn hình danh sách Đọc Câu (Ảnh 1).
     */
    public function test_sentence_list_page_renders_correctly(): void
    {
        $response = $this->get('/luyen-tap/doc-cau');

        $response->assertStatus(200);
        $response->assertSee('Read Aloud');
        $response->assertSee('Đọc Câu');
        $response->assertSee('Đọc Đoạn');
        $response->assertSee('200 Basic Words');
        $response->assertSee('TOEIC Speaking');
        $response->assertSee('Độ khó');
        $response->assertSee('Tiến độ');
        $response->assertSee('Bookmark');
        $response->assertSee('Học trên Điện');
        $response->assertSee('AppStore');
        $response->assertSee('Google Play');
        $response->assertSee('If you have a good job, think smart.');
        $response->assertSee('ra-bookmark-btn');
    }

    /**
     * Test màn hình danh sách Đọc Đoạn (Ảnh 3).
     */
    public function test_passage_list_page_renders_correctly(): void
    {
        $response = $this->get('/luyen-tap/doc-doan');

        $response->assertStatus(200);
        $response->assertSee('Read Aloud');
        $response->assertSee('Đọc Câu');
        $response->assertSee('Đọc Đoạn');
        $response->assertSee('44 Bài học');
        $response->assertSee('Did you all pay off your debts?');
        $response->assertSee('Học trên Điện');
        $response->assertSee('AppStore');
        $response->assertSee('Google Play');
    }

    /**
     * Test màn hình chi tiết luyện nói Đọc Câu (Ảnh 2).
     */
    public function test_sentence_detail_practice_page_renders_correctly(): void
    {
        $response = $this->get('/luyen-tap/noi/phat-am?type=sentence&id=1');

        $response->assertStatus(200);
        $response->assertSee('Phát âm');
        $response->assertSee('Shadowing');
        $response->assertSee('Lưu');
        $response->assertSee('Báo cáo');
        $response->assertSee('Dịch');
        $response->assertSee('IPA');
        $response->assertSee('Ghi âm của tôi');
        $response->assertSee('Bạn chưa có ghi âm nào');
        $response->assertSee('mainMicBtn');
    }

    /**
     * Test màn hình chi tiết luyện nói Đọc Đoạn (Ảnh 4).
     */
    public function test_passage_detail_practice_page_renders_correctly(): void
    {
        $response = $this->get('/luyen-tap/noi/phat-am?type=passage&id=1');

        $response->assertStatus(200);
        $response->assertSee('Phát âm');
        $response->assertSee('Shadowing');
        $response->assertSee('Lưu');
        $response->assertSee('Báo cáo');
        $response->assertSee('Did you all pay off your debts?');
        $response->assertSee('Think Wise Financial!');
        $response->assertSee('Ghi âm của tôi');
        $response->assertSee('mainMicBtn');
    }
}

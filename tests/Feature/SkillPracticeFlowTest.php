<?php

namespace Tests\Feature;

use Tests\TestCase;

class SkillPracticeFlowTest extends TestCase
{
    public function test_practice_home_links_to_skills(): void
    {
        $response = $this->get('/luyen-tap');

        $response->assertStatus(200);
        $response->assertSee(route('luyen-tap.nghe'));
        $response->assertSee(url('/luyen-tap/doc'));
        $response->assertSee(url('/luyen-tap/noi'));
    }

    public function test_reading_skill_page_renders_reading_parts_and_questions(): void
    {
        $response = $this->get('/luyen-tap/doc');

        $response->assertStatus(200);
        $response->assertSee('Luyện theo Kỹ năng');
        $response->assertSee('NGHE');
        $response->assertSee('ĐỌC');
        $response->assertSee('NÓI');
        $response->assertSee('VIẾT');
        $response->assertSee('Part 5: Hoàn thành câu');
        $response->assertSee('Part 6: Hoàn thành đoạn văn');
        $response->assertSee('Part 7: Đọc hiểu đoạn văn');
        $response->assertSee('Mr. Henderson requested that all expense reports be submitted');
    }

    public function test_speaking_skill_page_renders_sample_image_read_aloud_cards(): void
    {
        $response = $this->get('/luyen-tap/noi');

        $response->assertStatus(200);
        $response->assertSee('Luyện theo Kỹ năng');
        $response->assertSee('NGHE');
        $response->assertSee('ĐỌC');
        $response->assertSee('NÓI');
        $response->assertSee('VIẾT');
        $response->assertSee('Đọc Câu');
        $response->assertSee('Luyện phát âm với câu, chấm điểm và chữa lỗi phát âm.');
        $response->assertSee('Đọc Đoạn văn');
        $response->assertSee('Luyện phát âm với đoạn văn ngắn, chấm điểm và chữa lỗi phát âm.');
        $response->assertSee(url('/luyen-tap/doc-cau'));
        $response->assertSee(url('/luyen-tap/doc-doan'));
    }

    public function test_read_aloud_exercises_return_to_speaking_page(): void
    {
        $responseSentence = $this->get('/luyen-tap/doc-cau');
        $responseSentence->assertStatus(200);
        $responseSentence->assertSee(url('/luyen-tap/noi'));
        $responseSentence->assertSee('If you have a good job, think smart.');

        $responseParagraph = $this->get('/luyen-tap/doc-doan');
        $responseParagraph->assertStatus(200);
        $responseParagraph->assertSee(url('/luyen-tap/noi'));
        $responseParagraph->assertSee('Did you all pay off your debts?');
    }

    public function test_reading_practice_screen_renders(): void
    {
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau'));
        $response->assertStatus(200);
        $response->assertSee('voca');
        $response->assertSee('PREP');
        $response->assertSee('Amon Donates to Music School in Grenel City');
        $response->assertSee('Giải thích');
    }

    public function test_reading_submit_and_redirects_to_result(): void
    {
        $response = $this->post(route('luyen-tap.doc.nop-bai'), [
            'total_count' => 4,
            'time_spent' => 120,
            'answers' => [
                '139' => 'C', // Correct
                '140' => 'B', // Correct
                '141' => 'A', // Wrong (correct is B)
            ],
        ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $resultResponse = $this->get(route('luyen-tap.doc.ket-qua'));
        $resultResponse->assertStatus(200);
        $resultResponse->assertSee('KẾT QUẢ BÀI LUYỆN');
        $resultResponse->assertSee('Câu đúng');
        $resultResponse->assertSee('Câu sai');
        $resultResponse->assertSee('Chưa trả lời');
        $resultResponse->assertSee('Luyện lại câu sai');
        $resultResponse->assertSee('Xem đáp án');
    }

    public function test_reading_result_page_accessible_directly(): void
    {
        $response = $this->get(route('luyen-tap.doc.ket-qua'));
        $response->assertStatus(200);
        $response->assertSee('KẾT QUẢ BÀI LUYỆN');
        $response->assertSee('Part 5: Hoàn thành câu');
    }

    public function test_retry_wrong_questions_flow(): void
    {
        // First submit a practice with 1 correct and 1 wrong answer
        $this->post(route('luyen-tap.doc.nop-bai'), [
            'total_count' => 2,
            'time_spent' => 60,
            'answers' => [
                '139' => 'C', // Correct
                '140' => 'A', // Wrong (correct is B)
            ],
        ]);

        // Access retry_wrong
        $retryResponse = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['retry_wrong' => 1]));
        $retryResponse->assertStatus(200);
        $retryResponse->assertViewHas('questions', function ($questions) {
            return count($questions) === 1 && $questions[0]['id'] === 140;
        });
    }

    public function test_listening_skill_page_renders_four_parts_and_start_buttons(): void
    {
        $response = $this->get(route('luyen-tap.nghe'));

        $response->assertStatus(200);
        $response->assertSee('Luyện theo Kỹ năng');
        $response->assertSee('NGHE');
        $response->assertSee('ĐỌC');
        $response->assertSee('NÓI');
        $response->assertSee('VIẾT');
        $response->assertSee('Tất cả phần luyện');
        $response->assertSee('Part 1: Mô tả hình');
        $response->assertSee('Part 2: Hỏi & Đáp', false);
        $response->assertSee('Part 3: Hội thoại ngắn');
        $response->assertSee('Part 4: Bài nói chuyện ngắn');
        $response->assertSee('Chưa bắt đầu');
        $response->assertSee('Bắt đầu');
        $response->assertSee(route('luyen-tap.nghe.mo-ta-hinh'));
    }

    public function test_listening_part1_setup_page_renders_matching_sample_image(): void
    {
        $response = $this->get(route('luyen-tap.nghe.mo-ta-hinh'));

        $response->assertStatus(200);
        $response->assertSee('PART 1 · NGHE');
        $response->assertSee('Mô tả hình');
        $response->assertSee('Cùng bắt đầu luyện tập nào');
        $response->assertSee('0%');
        $response->assertSee('Chọn số câu luyện tập');
        $response->assertSee('NHANH');
        $response->assertSee('VỪA');
        $response->assertSee('KỸ');
        $response->assertSee('TÙY CHỌN');
        $response->assertSee('BẮT ĐẦU 5 CÂU');
        $response->assertSee(route('luyen-tap.nghe'));
    }

    public function test_listening_practice_exam_renders_mockup_elements(): void
    {
        $response = $this->get(route('luyen-tap.nghe.lam-bai', ['count' => 5]));

        $response->assertStatus(200);
        $response->assertSee('StudyPrep');
        $response->assertSee('Câu trước');
        $response->assertSee('Giải thích');
        $response->assertSee('Câu sau');
        $response->assertSee('Chọn đáp án');
        $response->assertSee('Transcript');
        $response->assertSee('Dịch');
        $response->assertSee('part1_supermarket.jpg');
        $response->assertSee('She is at the supermarket.');
        $response->assertSee('She is selecting a flowerpot.');
    }

    public function test_listening_submit_preserves_answers_and_redirects_to_review(): void
    {
        $postData = [
            'answers' => [
                '6' => 'A', // Incorrect (correct is B)
                '7' => 'C',
            ],
            'time_spent' => 45,
            'count' => 5,
        ];

        $response = $this->post(route('luyen-tap.nghe.nop-bai'), $postData);

        $response->assertRedirect(route('luyen-tap.nghe.xem-dap-an'));

        // Follow redirect to review page
        $reviewResponse = $this->get(route('luyen-tap.nghe.xem-dap-an'));
        $reviewResponse->assertStatus(200);
        $reviewResponse->assertSee('is-review-mode');
        // Marked wrong answer A
        $reviewResponse->assertSee('is-review-wrong');
        // Marked correct answer B
        $reviewResponse->assertSee('is-review-correct');
        $reviewResponse->assertSee('Đáp án chính xác');
        // Explanation box
        $reviewResponse->assertSee('là đáp án đúng vì trong hình người phụ nữ đang cầm và lựa chọn');
    }
}


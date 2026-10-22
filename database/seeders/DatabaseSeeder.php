<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@studyprep.com'],
            [
                'user_name' => 'admin',
                'full_name' => 'Nguyễn Admin',
                'password_hash' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'ACTIVE',
                'energy' => 999,
                'created_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'thaotran@studyprep.edu.vn'],
            [
                'user_name' => 'thaotran',
                'full_name' => 'Thảo Trần',
                'password_hash' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'ACTIVE',
                'energy' => 15,
                'created_at' => now()->subDays(45),
                'email_verified_at' => now()->subDays(45),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'tuan.hoang@studyprep.edu.vn'],
            [
                'user_name' => 'tuan.hoang',
                'full_name' => 'ThS. Hoàng Minh Tuấn',
                'password_hash' => Hash::make('password123'),
                'role' => 'teacher',
                'status' => 'ACTIVE',
                'energy' => 100,
                'created_at' => now()->subMonths(6),
                'email_verified_at' => now()->subMonths(6),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'uyen.dang@outlook.com'],
            [
                'user_name' => 'uyen.dang',
                'full_name' => 'Đặng Phương Uyên',
                'password_hash' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'PENDING',
                'energy' => 5,
                'created_at' => now()->subDays(5),
                'email_verified_at' => null,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'huy.vuduc@gmail.com'],
            [
                'user_name' => 'huy.vuduc',
                'full_name' => 'Vũ Đức Huy',
                'password_hash' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'ACTIVE',
                'energy' => 20,
                'created_at' => now()->subDays(20),
                'email_verified_at' => now()->subDays(20),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'bao.le@gmail.com'],
            [
                'user_name' => 'bao.le',
                'full_name' => 'Lê Quốc Bảo',
                'password_hash' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'LOCKED',
                'energy' => 0,
                'created_at' => now()->subDays(60),
                'locked_until' => now()->addDays(30),
                'email_verified_at' => now()->subDays(60),
            ]
        );

        // 2. Kỹ năng & Phần thi (Skills & Sections)
        $skillLC = \App\Models\Skill::query()->updateOrCreate(['skill_id' => 1], ['code' => 'LC', 'skill_name' => 'Listening']);
        $skillRC = \App\Models\Skill::query()->updateOrCreate(['skill_id' => 2], ['code' => 'RC', 'skill_name' => 'Reading']);
        $skillSW = \App\Models\Skill::query()->updateOrCreate(['skill_id' => 3], ['code' => 'SW', 'skill_name' => 'Speaking & Writing']);

        $secPart1 = \App\Models\ExamSection::query()->updateOrCreate(['section_id' => 1], ['skill_id' => 1, 'code' => 'PART1', 'name' => 'Part 1: Photographs', 'sequence_no' => 1]);
        $secPart3 = \App\Models\ExamSection::query()->updateOrCreate(['section_id' => 3], ['skill_id' => 1, 'code' => 'PART3', 'name' => 'Part 3: Conversations', 'sequence_no' => 3]);
        $secPart5 = \App\Models\ExamSection::query()->updateOrCreate(['section_id' => 5], ['skill_id' => 2, 'code' => 'PART5', 'name' => 'Part 5: Incomplete Sentences', 'sequence_no' => 5]);
        $secPart7 = \App\Models\ExamSection::query()->updateOrCreate(['section_id' => 7], ['skill_id' => 2, 'code' => 'PART7', 'name' => 'Part 7: Reading Comprehension', 'sequence_no' => 7]);
        $secWrite1 = \App\Models\ExamSection::query()->updateOrCreate(['section_id' => 8], ['skill_id' => 3, 'code' => 'WRITING_T1', 'name' => 'Writing Task 1: Write a Sentence', 'sequence_no' => 8]);
        $secWrite2 = \App\Models\ExamSection::query()->updateOrCreate(['section_id' => 9], ['skill_id' => 3, 'code' => 'WRITING_T2', 'name' => 'Writing Task 2: Respond to Email', 'sequence_no' => 9]);

        $qTypeChoice = \App\Models\QuestionType::query()->updateOrCreate(['question_type_id' => 1], ['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);
        $qTypeText = \App\Models\QuestionType::query()->updateOrCreate(['question_type_id' => 2], ['name' => 'Written Response', 'response_mode' => 'TEXT']);
        $qTypeAudio = \App\Models\QuestionType::query()->updateOrCreate(['question_type_id' => 3], ['name' => 'Audio Listening', 'response_mode' => 'AUDIO']);

        // 3. Câu hỏi chuẩn hóa (Khớp ảnh mẫu)
        // Q-10892
        $q1 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10892],
            [
                'section_id' => 5,
                'question_type_id' => 1,
                'content' => 'With bicycling becoming more popular than ever, the city council plans to --- the number of bicycle lanes along riverside avenues.',
                'explanation' => 'Động từ "expand" (mở rộng) phù hợp nhất về ngữ nghĩa khi nói về số lượng làn xe đạp.',
                'difficulty' => 'MEDIUM',
                'points' => 1.00,
                'status' => 'ACTIVE',
                'created_at' => now()->subDays(12),
            ]
        );
        \App\Models\AnswerOption::query()->updateOrCreate(['question_id' => 10892, 'label' => 'A'], ['content' => 'define', 'is_correct' => false]);
        \App\Models\AnswerOption::query()->updateOrCreate(['question_id' => 10892, 'label' => 'B'], ['content' => 'inspect', 'is_correct' => false]);
        \App\Models\AnswerOption::query()->updateOrCreate(['question_id' => 10892, 'label' => 'C'], ['content' => 'expand', 'is_correct' => true]);
        \App\Models\AnswerOption::query()->updateOrCreate(['question_id' => 10892, 'label' => 'D'], ['content' => 'relieve', 'is_correct' => false]);

        // Q-10893
        $q2 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10893],
            [
                'section_id' => 1,
                'question_type_id' => 3,
                'content' => 'Bức tranh số 01: Nhân viên văn phòng làm việc với máy tính xách tay bên cửa sổ kính.',
                'explanation' => 'audio_track_01.mp3 • Đáp án đúng: (B)',
                'difficulty' => 'EASY',
                'points' => 1.00,
                'status' => 'ACTIVE',
                'created_at' => now()->subDays(10),
            ]
        );

        // Q-10894
        $q3 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10894],
            [
                'section_id' => 8,
                'question_type_id' => 2,
                'content' => 'Từ khóa bắt buộc: tourist / landmark — Yêu cầu viết 1 câu mô tả chính xác bức tranh.',
                'explanation' => 'Chấm AI Tự động + Kiểm tra ngữ pháp',
                'difficulty' => 'MEDIUM',
                'points' => 3.00,
                'status' => 'ACTIVE',
                'created_at' => now()->subDays(8),
            ]
        );

        // Q-10895
        $q4 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10895],
            [
                'section_id' => 7,
                'question_type_id' => 1,
                'content' => 'Amon Donates $50,000 to Grenel Music Academy & Confirmation of Program Scholarship Fund. Questions 176–180 refer to the following press release and subsequent confirmation letter.',
                'explanation' => 'Đoạn văn đôi (4 câu hỏi). Khóa câu: #10895-1 (C), #10895-2 (A), #10895-3 (D), #10895-4 (B)',
                'difficulty' => 'HARD',
                'points' => 4.00,
                'status' => 'ACTIVE',
                'created_at' => now()->subDays(5),
            ]
        );

        // Q-10896 (Reported)
        $q5 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10896],
            [
                'section_id' => 5,
                'question_type_id' => 1,
                'content' => 'The marketing director suggested that we ------ the product launch date until next quarter to avoid overlapping campaigns.',
                'explanation' => 'Hiện tại: B. postpone • 2 học viên phản hồi cấu trúc bàng thái cách (subjunctive)',
                'difficulty' => 'HARD',
                'points' => 1.00,
                'status' => 'REPORTED',
                'created_at' => now()->subDays(3),
            ]
        );

        // Q-10897 (Pending)
        $q6 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10897],
            [
                'section_id' => 9,
                'question_type_id' => 2,
                'content' => 'Vehicle Fleet Rental Inquiry from Apex Logistics — Yêu cầu học viên trả lời thư đề nghị cung cấp biểu phí và điều khoản hủy dịch vụ.',
                'explanation' => 'Người tạo: GV. Trần Minh Hoàng • Biên soạn 2 giờ trước',
                'difficulty' => 'MEDIUM',
                'points' => 4.00,
                'status' => 'PENDING',
                'created_at' => now()->subHours(2),
            ]
        );

        // Q-10898
        $q7 = \App\Models\Question::query()->updateOrCreate(
            ['question_id' => 10898],
            [
                'section_id' => 3,
                'question_type_id' => 3,
                'content' => 'Two colleagues negotiating conference hall bookings. Questions 41–43 refer to the conversation between a project manager and an event coordinator.',
                'explanation' => 'Audio 0:42s (Cụm 3 câu) • Đáp án đúng: 41(A) • 42(D) • 43(B)',
                'difficulty' => 'MEDIUM',
                'points' => 3.00,
                'status' => 'ACTIVE',
                'created_at' => now()->subDays(1),
            ]
        );

        // 4. Bộ thẻ & Từ vựng Flashcards (Khớp ảnh mẫu)
        $adminUser = \App\Models\User::query()->where('role', 'admin')->first() ?? \App\Models\User::first();
        $adminId = $adminUser ? $adminUser->user_id : 1;

        $topicFinance = \App\Models\Topic::query()->updateOrCreate(['name' => 'Finance & Budget'], ['user_id' => $adminId, 'description' => 'Thuật ngữ tài chính và ngân sách doanh nghiệp']);
        $topicContracts = \App\Models\Topic::query()->updateOrCreate(['name' => 'Contracts & Finance'], ['user_id' => $adminId, 'description' => 'Hợp đồng, điều khoản và chu kỳ kế toán']);
        $topicWorkplaces = \App\Models\Topic::query()->updateOrCreate(['name' => 'Office & Workplaces'], ['user_id' => $adminId, 'description' => 'Ban quản trị và môi trường công sở']);
        $topicEssential = \App\Models\Topic::query()->updateOrCreate(['name' => '600 Essential Words'], ['user_id' => $adminId, 'description' => 'Từ vựng cốt lõi cho kỳ thi TOEIC']);
        $topicFacilities = \App\Models\Topic::query()->updateOrCreate(['name' => 'Office & Facilities'], ['user_id' => $adminId, 'description' => 'Cơ sở hạ tầng, sửa chữa và trùng tu']);
        $topicEts = \App\Models\Topic::query()->updateOrCreate(['name' => 'ETS 2024 Test 3'], ['user_id' => $adminId, 'description' => 'Bộ đề thi thật ETS 2024 Test 3']);
        $topicTransport = \App\Models\Topic::query()->updateOrCreate(['name' => 'Transport & Work'], ['user_id' => $adminId, 'description' => 'Giao thông và di chuyển công tác']);

        $vocabs = [
            [
                'word' => 'extraordinarily',
                'topic_id' => $topicFinance->topic_id,
                'part_of_speech' => 'Adv|750+',
                'pronunciation' => '/ɪkˈstrɔːr.dən.er.əl.i/',
                'meaning_vi' => "Đặc biệt, phi thường || To a very unusual or remarkable degree",
                'example_en' => 'The quarter report demonstrated an extraordinarily high return on investment.',
                'example_vi' => 'Báo cáo quý tăng trưởng vượt trội mang lại tỷ suất sinh lời đặc biệt cao.',
            ],
            [
                'word' => 'fiscal quarter',
                'topic_id' => $topicContracts->topic_id,
                'part_of_speech' => 'Noun Phrase|800+',
                'pronunciation' => '/ˈfɪs.kəl ˈkwɔːr.tər/',
                'meaning_vi' => 'Quý tài chính || 3-month business accounting period',
                'example_en' => 'The board analyzed all financial statements for the third fiscal quarter.',
                'example_vi' => 'Hội đồng xem xét báo cáo tài chính quý 3.',
            ],
            [
                'word' => 'board of directors',
                'topic_id' => $topicWorkplaces->topic_id,
                'part_of_speech' => 'Collocation|650+',
                'pronunciation' => '/bɔːrd əv dɪˈrek.tərz/',
                'meaning_vi' => 'Hội đồng quản trị || Elected group governing a company',
                'example_en' => 'The board of directors approved the expansion proposal unanimously.',
                'example_vi' => 'Ban giám đốc duyệt đề xuất mở rộng một cách đồng thuận.',
            ],
            [
                'word' => 'accommodate',
                'topic_id' => $topicEssential->topic_id,
                'part_of_speech' => 'Verb|650+',
                'pronunciation' => '/əˈkɑː.mə.deɪt/',
                'meaning_vi' => 'Chứa đựng, đáp ứng || To hold or meet someone’s need',
                'example_en' => 'The renovated hall can accommodate up to five hundred attendees.',
                'example_vi' => 'Hội trường có thể tiếp đón lên tới năm trăm người tham dự.',
            ],
            [
                'word' => 'renovation',
                'topic_id' => $topicFacilities->topic_id,
                'part_of_speech' => 'Noun|550+',
                'pronunciation' => '/ˌren.əˈveɪ.ʃən/',
                'meaning_vi' => 'Sự đổi mới, trùng tu || Upgrading or repairing structural units',
                'example_en' => 'Building B will be closed temporarily for scheduled floor renovation.',
                'example_vi' => 'Tòa B tạm thời đóng để nâng cấp và làm mới cơ sở vật chất.',
            ],
            [
                'word' => 'substantial',
                'topic_id' => $topicEts->topic_id,
                'part_of_speech' => 'Adj|800+',
                'pronunciation' => '/səbˈstæn.ʃəl/',
                'meaning_vi' => 'Đáng kể, trọng yếu || Large in amount, value, or importance',
                'example_en' => 'The corporation gained a substantial market share after releasing the new service.',
                'example_vi' => 'Tập đoàn đạt lợi thế thị phần đáng kể sau khi tung ra dịch vụ mới.',
            ],
            [
                'word' => 'commute',
                'topic_id' => $topicTransport->topic_id,
                'part_of_speech' => 'Verb|450+',
                'pronunciation' => '/kəˈmjuːt/',
                'meaning_vi' => 'Đi lại hàng ngày || Travel back and forth to work',
                'example_en' => 'Many executives commute to the metropolitan office by rapid train.',
                'example_vi' => 'Nhiều quản lý di chuyển đi làm tại trung tâm bằng tàu điện nhanh.',
            ],
        ];

        foreach ($vocabs as $item) {
            \App\Models\Vocabulary::query()->updateOrCreate(
                ['word' => $item['word']],
                $item
            );
        }
    }
}

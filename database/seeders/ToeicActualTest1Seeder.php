<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ToeicActualTest1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $now = Carbon::now();

            // Xác định tên bảng thực tế hoặc view
            $setTable = Schema::hasTable('question_sets') ? 'question_sets' : 'question_set';
            $optTable = Schema::hasTable('answer_options') ? 'answer_options' : 'answer_option';

            // Đảm bảo các ExamSection cho LC (Part 1, 2, 3, 4) tồn tại
            if (Schema::hasTable('exam_section')) {
                DB::table('exam_section')->updateOrInsert(['code' => 'PART1'], ['skill_id' => 1, 'name' => 'Part 1: Photographs', 'sequence_no' => 1]);
                DB::table('exam_section')->updateOrInsert(['code' => 'PART2'], ['skill_id' => 1, 'name' => 'Part 2: Question-Response', 'sequence_no' => 2]);
                DB::table('exam_section')->updateOrInsert(['code' => 'PART3'], ['skill_id' => 1, 'name' => 'Part 3: Conversations', 'sequence_no' => 3]);
                DB::table('exam_section')->updateOrInsert(['code' => 'PART4'], ['skill_id' => 1, 'name' => 'Part 4: Short Talks', 'sequence_no' => 4]);
            }

            $secMap = DB::table('exam_section')->pluck('section_id', 'code')->toArray();
            $secP1 = $secMap['PART1'] ?? 1;
            $secP2 = $secMap['PART2'] ?? $secP1;
            $secP3 = $secMap['PART3'] ?? $secP1;
            $secP4 = $secMap['PART4'] ?? $secP1;

            // ----------------------------------------------------
            // SECTION 1: PART 1 - PHOTOS (Questions 1 - 6)
            // ----------------------------------------------------
            $part1Data = [
                [
                    'q_num' => 1,
                    'stem' => 'Look at the picture marked No. 1 in your test book.',
                    'exp' => 'Đáp án đúng là (A). Lời giải chi tiết: Cô ấy đang ăn trong khu dã ngoại.',
                    'options' => [
                        ['label' => 'A', 'content' => 'She’s eating in a picnic area. (Cô ấy đang ăn trong khu picnic.)', 'is_correct' => true],
                        ['label' => 'B', 'content' => 'She’s waiting in line at a food truck. (Cô ấy đang chờ trong hàng tại một xe bán đồ ăn.)', 'is_correct' => false],
                        ['label' => 'C', 'content' => 'She’s wiping off a bench. (Cô ấy đang lau chùi một chiếc ghế dài.)', 'is_correct' => false],
                        ['label' => 'D', 'content' => 'She’s throwing away a plate. (Cô ấy đang vứt đi một cái đĩa.)', 'is_correct' => false],
                    ],
                ],
                [
                    'q_num' => 2,
                    'stem' => 'Look at the picture marked No. 2 in your test book.',
                    'exp' => 'Đáp án đúng là (B). Lời giải chi tiết: Người đàn ông đứng trong tuyết bên cạnh một chiếc ô tô.',
                    'options' => [
                        ['label' => 'A', 'content' => 'The man is brushing snow off the roof of a car. (Người đàn ông đang phủi tuyết khỏi nóc ô tô.)', 'is_correct' => false],
                        ['label' => 'B', 'content' => 'The man is standing in the snow beside a car. (Người đàn ông đứng trong tuyết bên cạnh một chiếc ô tô.)', 'is_correct' => true],
                        ['label' => 'C', 'content' => 'The man is shoveling snow from a walkway. (Người đàn ông đang xúc tuyết trên lối đi.)', 'is_correct' => false],
                        ['label' => 'D', 'content' => 'The man is running through the snow. (Người đàn ông chạy qua tuyết.)', 'is_correct' => false],
                    ],
                ],
                [
                    'q_num' => 3,
                    'stem' => 'Look at the picture marked No. 3 in your test book.',
                    'exp' => 'Đáp án đúng là (B). Lời giải chi tiết: Hai người trong số họ đang trò chuyện.',
                    'options' => [
                        ['label' => 'A', 'content' => 'Some workers are hanging art in a gallery. (Một số công nhân đang treo tác phẩm nghệ thuật trong phòng trưng bày.)', 'is_correct' => false],
                        ['label' => 'B', 'content' => 'Two of the people are having a conversation. (Hai người trong số họ đang trò chuyện.)', 'is_correct' => true],
                        ['label' => 'C', 'content' => 'One of the men is rearranging cushions on a sofa. (Một người đàn ông đang sắp xếp lại những chiếc đệm trên ghế sofa.)', 'is_correct' => false],
                        ['label' => 'D', 'content' => 'One of the men is painting a picture. (Một người đàn ông đang vẽ một bức tranh.)', 'is_correct' => false],
                    ],
                ],
                [
                    'q_num' => 4,
                    'stem' => 'Look at the picture marked No. 4 in your test book.',
                    'exp' => 'Đáp án đúng là (D). Lời giải chi tiết: Quần áo đang được trưng bày dưới lều.',
                    'options' => [
                        ['label' => 'A', 'content' => 'Vehicles are entering a parking garage. (Các phương tiện đang vào bãi đỗ xe.)', 'is_correct' => false],
                        ['label' => 'B', 'content' => 'Clothes hangers are scattered on the floor. (Những chiếc móc áo rơi vãi trên sàn.)', 'is_correct' => false],
                        ['label' => 'C', 'content' => 'Empty racks are lined up next to a building. (Các giá đỡ trống được xếp cạnh một tòa nhà.)', 'is_correct' => false],
                        ['label' => 'D', 'content' => 'Clothing is being displayed under a tent. (Quần áo đang được trưng bày dưới lều.)', 'is_correct' => true],
                    ],
                ],
                [
                    'q_num' => 5,
                    'stem' => 'Look at the picture marked No. 5 in your test book.',
                    'exp' => 'Đáp án đúng là (C). Lời giải chi tiết: Một máy tính đã được sắp xếp trên bàn.',
                    'options' => [
                        ['label' => 'A', 'content' => 'Potted plants have been suspended from a ceiling. (Những chậu cây được treo trên trần nhà.)', 'is_correct' => false],
                        ['label' => 'B', 'content' => 'Chairs have been stacked in front of an entryway. (Ghế đã được xếp trước lối vào.)', 'is_correct' => false],
                        ['label' => 'C', 'content' => 'A computer station has been set up on a desk. (Một máy tính đã được sắp xếp trên bàn.)', 'is_correct' => true],
                        ['label' => 'D', 'content' => 'A rug has been rolled up against a wall. (Một tấm thảm đã được cuộn lên tường.)', 'is_correct' => false],
                    ],
                ],
                [
                    'q_num' => 6,
                    'stem' => 'Look at the picture marked No. 6 in your test book.',
                    'exp' => 'Đáp án đúng là (C). Lời giải chi tiết: Một cánh cửa đã bị tháo ra khỏi khung.',
                    'options' => [
                        ['label' => 'A', 'content' => 'One of the men is sweeping a patio. (Một người đàn ông đang quét sân.)', 'is_correct' => false],
                        ['label' => 'B', 'content' => 'One of the men is replacing some flooring. (Một người đàn ông đang thay thế lót sàn.)', 'is_correct' => false],
                        ['label' => 'C', 'content' => 'A door has been taken off its frame. (Một cánh cửa đã bị tháo ra khỏi khung.)', 'is_correct' => true],
                        ['label' => 'D', 'content' => 'A light fixture has been left on the ground. (Một chiếc đèn đã được bỏ lại trên mặt đất.)', 'is_correct' => false],
                    ],
                ],
            ];

            foreach ($part1Data as $p1) {
                $audioUrl = '/storage/audio/listening/test1/part1_q'.$p1['q_num'].'.mp3';

                $setId = DB::table($setTable)->insertGetId([
                    'title' => 'ACTUAL TEST 1 - TOEIC Part 1 - Question '.$p1['q_num'],
                    'stimulus_text' => 'Part 1: Question '.$p1['q_num'],
                    'media_url' => $audioUrl,
                    'media_type' => 'AUDIO',
                    'transcript' => $p1['stem'],
                ]);

                $qData = [
                    'question_set_id' => $setId,
                    'content' => $p1['stem'],
                    'explanation' => $p1['exp'],
                    'difficulty' => 'MEDIUM',
                    'points' => 5.0,
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                ];

                if (Schema::hasColumn('questions', 'section_id')) {
                    $qData['section_id'] = $secP1;
                }
                if (Schema::hasColumn('questions', 'question_type_id')) {
                    $qData['question_type_id'] = 3;
                }
                if (Schema::hasColumn('questions', 'part_number')) {
                    $qData['part_number'] = 1;
                }
                if (Schema::hasColumn('questions', 'audio_url')) {
                    $qData['audio_url'] = $audioUrl;
                }

                $qId = DB::table('questions')->insertGetId($qData);

                foreach ($p1['options'] as $opt) {
                    DB::table($optTable)->insert([
                        'question_id' => $qId,
                        'label' => $opt['label'],
                        'content' => $opt['content'],
                        'is_correct' => $opt['is_correct'],
                    ]);
                }
            }

            // ----------------------------------------------------
            // SECTION 2: PART 2 - QUESTION-RESPONSE (Questions 7 - 31)
            // ----------------------------------------------------
            $part2Data = [
                7 => ['q' => 'How old is this building?', 'correct' => 'B', 'opts' => ['A' => 'To ship some materials (Để vận chuyển một số vật liệu)', 'B' => 'About ten years old (Khoảng mười năm tuổi)', 'C' => 'Company offices, I think (Văn phòng công ty, tôi nghĩ vậy)']],
                8 => ['q' => 'Can you come to my jazz performance tonight?', 'correct' => 'C', 'opts' => ['A' => "I'm sorry I was late for the meeting (Xin lỗi, tôi đến muộn cuộc họp)", 'B' => 'Mostly just local musicians (Chủ yếu là các nhạc sĩ địa phương)', 'C' => "Sure, I'll be there (Chắc chắn, tôi sẽ đến đó)"]],
                9 => ['q' => 'Which apartment submitted a work order?', 'correct' => 'C', 'opts' => ['A' => "It's what you did for a living (Đó là công việc bạn đã làm)", 'B' => 'Submit your assignment here (Nộp bài tập của bạn ở đây)', 'C' => 'It came from the tenants in B23 (Nó đến từ các cư dân ở B23)']],
                10 => ['q' => 'Will you contact the vendor about changing our delivery date?', 'correct' => 'A', 'opts' => ['A' => "Of course, I'll take care of it (Tất nhiên, tôi sẽ lo liệu chuyện đó)", 'B' => 'An e-mail receipt (Một biên lai email)', 'C' => 'Could I get change for a dollar? (Tôi có thể nhận tiền thừa cho một đô la không?)']],
                11 => ['q' => 'Why was the maintenance worker here?', 'correct' => 'C', 'opts' => ['A' => "No, he didn't (Không, anh ấy không làm vậy)", 'B' => 'From three o\'clock until four (Từ ba giờ đến bốn giờ)', 'C' => 'Because a light needed to be fixed (Vì một bóng đèn cần được sửa)']],
                12 => ['q' => 'Did they make a hiring decision yet?', 'correct' => 'C', 'opts' => ['A' => 'Pat it on the highest shelf (Đặt nó lên kệ cao nhất)', 'B' => 'The personnel department (Phòng nhân sự)', 'C' => 'Yes, they chose Jacob Borgman (Vâng, họ đã chọn Jacob Borgman)']],
                13 => ['q' => 'Do you want to eat here in our cafeteria or go out?', 'correct' => 'C', 'opts' => ['A' => 'He went there yesterday (Anh ấy đã đến đó ngày hôm qua)', 'B' => 'Well, maybe a sandwich (Chà, có thể là một chiếc sandwich)', 'C' => 'Let’s eat here (Hãy ăn ở đây)']],
                14 => ['q' => 'Didn\'t you e-mail the employment contract to Mr. Patel yesterday?', 'correct' => 'B', 'opts' => ['A' => 'Yes, I would agree (Vâng, tôi đồng ý)', 'B' => 'No, I\'ll send it now (Không, tôi sẽ gửi ngay bây giờ)', 'C' => 'Check the employee manual (Kiểm tra sổ tay nhân viên)']],
                15 => ['q' => 'Our division\'s picnic is this Saturday, right?', 'correct' => 'A', 'opts' => ['A' => 'There’s a lot of rain in the forecast. (Dự báo sẽ có rất nhiều mưa.)', 'B' => 'Sure, I like salad. (Chắc chắn rồi, tôi thích salad.)', 'C' => 'At the end of this corridor. (Ở cuối hành lang này.)']],
                16 => ['q' => 'Would you like coffee or tea?', 'correct' => 'A', 'opts' => ['A' => 'Just water, please. (Chỉ cần nước thôi.)', 'B' => 'For a few dollars more. (Để có thêm vài đô la.)', 'C' => 'A fifteen-minute break. (Nghỉ giải lao mười lăm phút.)']],
                17 => ['q' => 'We achieved our sales targets this month.', 'correct' => 'A', 'opts' => ['A' => 'That\'s excellent news. (Đó là tin tuyệt vời.)', 'B' => 'A few times a day. (Vài lần một ngày.)', 'C' => 'To the end of April. (Đến cuối tháng 4.)']],
                18 => ['q' => 'How often do you travel for your job?', 'correct' => 'C', 'opts' => ['A' => 'It turned out well. (Mọi việc diễn ra tốt đẹp.)', 'B' => 'Yes, I did find one. (Vâng, tôi đã tìm thấy một cái.)', 'C' => 'About once a month. (Khoảng một tháng một lần.)']],
                19 => ['q' => 'We should hike the Wildflower Trail today.', 'correct' => 'B', 'opts' => ['A' => 'This seat is available. (Chỗ ngồi này còn trống.)', 'B' => 'I didn’t bring boots. (Tôi không mang ủng.)', 'C' => 'At the visitors’ center. (Tại trung tâm dành cho du khách.)']],
                20 => ['q' => 'You\'ve booked a hotel in London, haven\'t you?', 'correct' => 'C', 'opts' => ['A' => 'Very enjoyable, thanks. (Rất thú vị, cảm ơn.)', 'B' => 'He usually takes the train. (Anh ấy thường đi tàu.)', 'C' => 'Yes, I made a reservation last week. (Vâng, tôi đã đặt chỗ vào tuần trước.)']],
                21 => ['q' => 'Are there any tickets left for tonight’s concert?', 'correct' => 'A', 'opts' => ['A' => 'It’s sold out. (Nó đã được bán hết.)', 'B' => 'He’s a concert violinist. (Anh ấy là một nghệ sĩ violin hòa nhạc.)', 'C' => 'They already left. (Họ đã rời đi rồi.)']],
                22 => ['q' => 'Haven’t you used this software before?', 'correct' => 'B', 'opts' => ['A' => 'Can I take your order? (Tôi có thể nhận đơn đặt hàng của bạn được không?)', 'B' => 'I haven’t had a chance. (Tôi chưa có cơ hội.)', 'C' => 'About 40 dollars. (Khoảng 40 đô la.)']],
                23 => ['q' => 'When is the new blender going to be released?', 'correct' => 'C', 'opts' => ['A' => 'Only with fruits and vegetables. (Chỉ với trái cây và rau quả.)', 'B' => 'In the kitchen cabinet. (Trong tủ bếp.)', 'C' => 'The prototype is still being tested. (Nguyên mẫu vẫn đang được thử nghiệm.)']],
                24 => ['q' => 'Who\'s picking up our clients at the airport?', 'correct' => 'A', 'opts' => ['A' => 'They decided to drive. (Họ đã quyết định lái xe.)', 'B' => 'At terminal 2. (Tại ga số 2.)', 'C' => 'It’s a marketing position. (Đó là một vị trí marketing.)']],
                25 => ['q' => 'Where are the red roses that came in this morning?', 'correct' => 'C', 'opts' => ['A' => 'About three liters of water. (Khoảng ba lít nước.)', 'B' => 'No, I didn’t check out the sale. (Không, tôi chưa kiểm tra đợt giảm giá.)', 'C' => 'I needed some for a large bouquet. (Tôi cần một ít cho một bó hoa lớn.)']],
                26 => ['q' => 'This film has been nominated for several awards.', 'correct' => 'A', 'opts' => ['A' => 'Why don’t we go see it? (Tại sao chúng ta không đi xem nó?)', 'B' => 'After the announcement. (Sau thông báo.)', 'C' => 'He made a great speech. (Anh ấy đã có một bài phát biểu tuyệt vời.)']],
                27 => ['q' => 'Who\'s interested in starting a car pool program?', 'correct' => 'B', 'opts' => ['A' => 'Thanks, but I can\'t swim. (Cảm ơn, nhưng tôi không thể bơi.)', 'B' => 'Carat’s already organizing one. (Carat đã tổ chức một nhóm.)', 'C' => 'It’s a very interesting article. (Đó là một bài báo thú vị.)']],
                28 => ['q' => 'Where will I teach my workshop this month?', 'correct' => 'A', 'opts' => ['A' => 'We just sent an e-mail to all instructors. (Chúng tôi đã gửi email đến tất cả các giảng viên.)', 'B' => 'Five to seven months. (5 đến 7 tháng.)', 'C' => 'Yes, it’s at the professional building. (Vâng, nó ở tòa nhà chuyên nghiệp.)']],
                29 => ['q' => 'Why are we moving these sweaters to the back of the store?', 'correct' => 'C', 'opts' => ['A' => 'In the new shopping mall. (Trong khu mua sắm mới.)', 'B' => 'Yes, they come in other colors. (Vâng, chúng có màu khác.)', 'C' => 'Our spring merchandise is arriving soon. (Hàng hóa mùa xuân của chúng tôi sắp đến.)']],
                30 => ['q' => 'Would you be interested in working on some of these contracts?', 'correct' => 'C', 'opts' => ['A' => 'Thank you for meeting me. (Cảm ơn vì đã gặp tôi.)', 'B' => 'A contact lens prescription. (Một đơn thuốc kính mắt.)', 'C' => 'I have very little interest. (Tôi không có nhiều sự quan tâm.)']],
                31 => ['q' => 'What type of job are you looking for?', 'correct' => 'B', 'opts' => ['A' => 'No, at ten AM. (Không, lúc 10 giờ sáng.)', 'B' => 'I really like working with computers. (Tôi thực sự thích làm việc với máy tính.)', 'C' => 'Just a résumé. (Chỉ cần một sơ yếu lý lịch.)']],
            ];

            foreach ($part2Data as $qNum => $item) {
                $audioUrl = '/storage/audio/listening/test1/part2_q'.$qNum.'.mp3';

                $setId = DB::table($setTable)->insertGetId([
                    'title' => 'ACTUAL TEST 1 - TOEIC Part 2 - Question '.$qNum,
                    'stimulus_text' => 'Part 2: Question '.$qNum,
                    'media_url' => $audioUrl,
                    'media_type' => 'AUDIO',
                    'transcript' => $item['q'],
                ]);

                $qData = [
                    'question_set_id' => $setId,
                    'content' => $item['q'],
                    'explanation' => 'Đáp án đúng là ('.$item['correct'].').',
                    'difficulty' => 'MEDIUM',
                    'points' => 5.0,
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                ];

                if (Schema::hasColumn('questions', 'section_id')) {
                    $qData['section_id'] = $secP2;
                }
                if (Schema::hasColumn('questions', 'question_type_id')) {
                    $qData['question_type_id'] = 3;
                }
                if (Schema::hasColumn('questions', 'part_number')) {
                    $qData['part_number'] = 2;
                }
                if (Schema::hasColumn('questions', 'audio_url')) {
                    $qData['audio_url'] = $audioUrl;
                }

                $qId = DB::table('questions')->insertGetId($qData);

                foreach ($item['opts'] as $label => $text) {
                    DB::table($optTable)->insert([
                        'question_id' => $qId,
                        'label' => $label,
                        'content' => $text,
                        'is_correct' => ($label === $item['correct']),
                    ]);
                }
            }

            // ----------------------------------------------------
            // SECTION 3: PART 3 - CONVERSATIONS (Questions 32 - 70)
            // ----------------------------------------------------
            $part3Groups = [
                [
                    'range' => '32-34',
                    'part' => 3,
                    'transcript' => "W: Thank you so much for organizing the annual company picnic, Jingdao. Everybody seemed to enjoy it.\nM: Well, we deserved it after working so hard this year.\nW: I agree. The food was great, by the way. Especially the peach pie you made. Would you mind sharing the recipe? It was delicious.\nM: I found the recipe online. I'll send you a link to the webpage. There's a really helpful video that walks you through all the steps. I recommend you watch it first.\nW: All right. Thanks.\n---\n[Dịch Tiếng Việt]\nW: Cảm ơn bạn rất nhiều vì đã tổ chức buổi dã ngoại hàng năm cho công ty, Jingdao. Mọi người dường như thích nó.\nM: Chà, chúng ta xứng đáng được như vậy sau khi đã làm việc rất chăm chỉ trong năm nay.\nW: Tôi đồng ý. Nhân tiện, đồ ăn rất tuyệt. Đặc biệt là món bánh đào bạn làm. Bạn có phiền chia sẻ công thức không? Nó rất là ngon.\nM: Tôi tìm thấy công thức trên mạng. Tôi sẽ gửi cho bạn một liên kết đến trang web. Có một video thực sự hữu ích hướng dẫn bạn qua tất cả các bước. Tôi khuyên bạn nên xem nó đầu tiên.\nW: Được rồi. Cảm ơn.",
                    'questions' => [
                        [
                            'q_num' => 32,
                            'content' => 'What event does the woman mention? (Người phụ nữ nhắc đến sự kiện gì?)',
                            'correct' => 'D',
                            'exp' => 'Đáp án đúng là (D). Dựa vào lời thoại: "Thank you so much for organizing the annual company picnic".',
                            'options' => ['A' => 'A job fair', 'B' => 'A cooking class', 'C' => 'A fund-raiser', 'D' => 'A company picnic'],
                        ],
                        [
                            'q_num' => 33,
                            'content' => 'What does the woman ask for? (Người phụ nữ yêu cầu điều gì?)',
                            'correct' => 'B',
                            'exp' => 'Đáp án đúng là (B). Dựa vào lời thoại: "Would you mind sharing the recipe?".',
                            'options' => ['A' => 'A guest list', 'B' => 'A dessert recipe', 'C' => 'A business card', 'D' => 'A promotional code'],
                        ],
                        [
                            'q_num' => 34,
                            'content' => 'What does the man recommend doing? (Người đàn ông khuyên bạn nên làm gì?)',
                            'correct' => 'B',
                            'exp' => 'Đáp án đúng là (B). Dựa vào lời thoại: "There\'s a really helpful video... I recommend you watch it first".',
                            'options' => ['A' => 'Returning some merchandise', 'B' => 'Watching a video', 'C' => 'Creating an account', 'D' => 'Reading a review'],
                        ],
                    ],
                ],
                [
                    'range' => '35-37',
                    'part' => 3,
                    'transcript' => "M: I'd like to finish calculating the company's expense reports for the month. Have you finished reviewing the travel reimbursement forms from all the departments?\nW: I'm almost done. But I have a question about a hotel receipt from one of our employees.\nM: What's the problem?\nW: Well, our policy is for employees to stay at a hotel that's on our list of approved accommodations. This one isn't on the list.\nM: Who submitted the receipt?\nW: Moritz-Siegler, one of our sales representatives.\nM: Hmm. He's a new employee and may have forgotten the policy. As a supervisor, I can approve the expense this one time.",
                    'questions' => [
                        [
                            'q_num' => 35,
                            'content' => 'What department do the speakers most likely work in?',
                            'correct' => 'A',
                            'exp' => 'Đáp án đúng là (A). Người nói thảo luận về "expense reports" và "reimbursement forms" nên họ thuộc bộ phận Kế toán (Accounting).',
                            'options' => ['A' => 'Accounting', 'B' => 'Research and development', 'C' => 'Maintenance', 'D' => 'Marketing'],
                        ],
                        [
                            'q_num' => 36,
                            'content' => 'What problem does the woman mention?',
                            'correct' => 'C',
                            'exp' => 'Đáp án đúng là (C). Khách sạn không nằm trong danh sách phê duyệt ("policy has not been followed").',
                            'options' => ['A' => 'A report has not been submitted.', 'B' => 'An invoice is not accurate.', 'C' => 'A policy has not been followed.', 'D' => 'An order has not been delivered'],
                        ],
                        [
                            'q_num' => 37,
                            'content' => 'What does the man say he will do?',
                            'correct' => 'B',
                            'exp' => 'Đáp án đúng là (B). Người đàn ông nói "I can approve the expense this one time" (Phê duyệt hoàn tiền).',
                            'options' => ['A' => 'Delete an electronic file', 'B' => 'Authorize a reimbursement', 'C' => 'Set up a sales meeting', 'D' => 'Review a spreadsheet'],
                        ],
                    ],
                ],
                [
                    'range' => '38-40',
                    'part' => 3,
                    'transcript' => "M: Good morning, Demolola. How's everything up here on deck?\nW: Hi, Pedro. It was an uneventful night, and our cargo ship still hasn't moved yet.\nM: Hmm. I hope the fog over the harbor lifts soon.\nW: Yeah, me too. The ship won't be able to leave until the weather improves.\nM: I hope we won't get too far behind schedule. I'll be sure to call the port authority soon for an update on when we'll be cleared to leave.\nW: Sounds good.",
                    'questions' => [
                        [
                            'q_num' => 38,
                            'content' => 'What industry do the speakers most likely work in?',
                            'correct' => 'A',
                            'exp' => 'Đáp án đúng là (A). Các từ khóa "cargo ship", "deck", "harbor" thuộc ngành Vận tải biển (Shipping).',
                            'options' => ['A' => 'Shipping', 'B' => 'Manufacturing', 'C' => 'Hospitality', 'D' => 'Meteorology'],
                        ],
                        [
                            'q_num' => 39,
                            'content' => 'What is the reason for a delay?',
                            'correct' => 'C',
                            'exp' => 'Đáp án đúng là (C). Do sương mù ("fog over the harbor") -> "Weather conditions are poor".',
                            'options' => ['A' => 'A schedule was written incorrectly.', 'B' => 'Some equipment is not properly set up.', 'C' => 'Weather conditions are poor.', 'D' => 'Several staff members are absent.'],
                        ],
                        [
                            'q_num' => 40,
                            'content' => 'What does the man say he will do?',
                            'correct' => 'D',
                            'exp' => 'Đáp án đúng là (D). Người đàn ông nói "I\'ll be sure to call the port authority soon" -> Place a call.',
                            'options' => ['A' => 'Update a shift schedule', 'B' => 'Clear a work space', 'C' => 'Complete a checklist', 'D' => 'Place a call'],
                        ],
                    ],
                ],
            ];

            foreach ($part3Groups as $group) {
                $firstQ = $group['questions'][0]['q_num'];
                $audioUrl = '/storage/audio/listening/test1/part3_q'.$firstQ.'.mp3';

                $setId = DB::table($setTable)->insertGetId([
                    'title' => 'ACTUAL TEST 1 - TOEIC Part 3 - Questions '.$group['range'],
                    'stimulus_text' => 'Questions '.$group['range'].' refer to the following conversation.',
                    'media_url' => $audioUrl,
                    'media_type' => 'AUDIO',
                    'transcript' => $group['transcript'],
                ]);

                foreach ($group['questions'] as $q) {
                    $qData = [
                        'question_set_id' => $setId,
                        'content' => $q['content'],
                        'explanation' => $q['exp'],
                        'difficulty' => 'MEDIUM',
                        'points' => 5.0,
                        'status' => 'ACTIVE',
                        'created_at' => $now,
                    ];

                    if (Schema::hasColumn('questions', 'section_id')) {
                        $qData['section_id'] = $secP3;
                    }
                    if (Schema::hasColumn('questions', 'question_type_id')) {
                        $qData['question_type_id'] = 3;
                    }
                    if (Schema::hasColumn('questions', 'part_number')) {
                        $qData['part_number'] = $group['part'];
                    }
                    if (Schema::hasColumn('questions', 'audio_url')) {
                        $qData['audio_url'] = $audioUrl;
                    }

                    $qId = DB::table('questions')->insertGetId($qData);

                    foreach ($q['options'] as $label => $text) {
                        DB::table($optTable)->insert([
                            'question_id' => $qId,
                            'label' => $label,
                            'content' => $text,
                            'is_correct' => ($label === $q['correct']),
                        ]);
                    }
                }
            }

            // ----------------------------------------------------
            // SECTION 4: PART 4 - TALKS (Questions 71 - 100)
            // ----------------------------------------------------
            $part4Groups = [
                [
                    'range' => '71-73',
                    'part' => 4,
                    'transcript' => "You have reached the information line for the Cranbury Apartments management office. On Monday, April twelfth, maintenance work will begin to repave the entire parking area adjacent to our building's main entrance. All Cranbury residents should move their vehicles from their designated parking spots before eight A.M. on Monday. Any vehicle still in its spot after eight A.M. will be towed at the owner's expense. A map of alternate parking sites was mailed to residents last week and is also posted in the building lobby.\n---\n[Dịch Tiếng Việt]\nQuý khách đã gọi đến đường dây thông tin của văn phòng quản lý chung cư Cranbury. Vào thứ Hai, ngày 12 tháng Tư, công tác bảo trì sẽ bắt đầu để lát lại toàn bộ khu vực đậu xe liền kề lối vào chính của tòa nhà. Tất cả cư dân Cranbury vui lòng di chuyển xe của mình khỏi chỗ đậu xe được chỉ định trước 8 giờ sáng thứ Hai. Bất kỳ xe nào vẫn còn ở chỗ đậu sau 8 giờ sáng sẽ bị kéo đi và chủ xe phải chịu chi phí. Bản đồ các khu vực đậu xe thay thế đã được gửi đến cư dân vào tuần trước và cũng được dán ở sảnh tòa nhà.",
                    'questions' => [
                        [
                            'q_num' => 71,
                            'content' => 'Who has recorded the message?',
                            'correct' => 'D',
                            'exp' => 'Đáp án đúng là (D). Lời nhắn từ "Cranbury Apartments management office" (Văn phòng quản lý tòa nhà).',
                            'options' => ['A' => 'A city mayor’s office', 'B' => 'A maintenance department', 'C' => 'An automobile dealership', 'D' => 'A building management office'],
                        ],
                        [
                            'q_num' => 72,
                            'content' => 'What are the listeners asked to do?',
                            'correct' => 'A',
                            'exp' => 'Đáp án đúng là (A). Lời thoại: "All Cranbury residents should move their vehicles".',
                            'options' => ['A' => 'Move their vehicles', 'B' => 'Pay their parking fines', 'C' => 'Use an alternate entrance', 'D' => 'Participate in a meeting'],
                        ],
                        [
                            'q_num' => 73,
                            'content' => 'What does the speaker say was mailed last week?',
                            'correct' => 'C',
                            'exp' => 'Đáp án đúng là (C). Lời thoại: "A map of alternate parking sites was mailed to residents last week".',
                            'options' => ['A' => 'An election ballot', 'B' => 'A maintenance plan', 'C' => 'A map', 'D' => 'A coupon'],
                        ],
                    ],
                ],
                [
                    'range' => '74-76',
                    'part' => 4,
                    'transcript' => "Welcome to Your House Works. On today's episode, we'll go over how you can maintain and make minor repairs to the roof of your home. The first thing to do is to invest in a few special tools, like a trowel and crowbar. It's important to choose some that are high quality because you'll use them for many years. With your trowel and some roof cement, you can seal any cracks or chips. The crowbar will help you remove loose shingles that you can then replace. Now, I highly recommend you take photos of your roof every year so that you can track its overall condition.",
                    'questions' => [
                        [
                            'q_num' => 74,
                            'content' => 'What is the topic of the episode?',
                            'correct' => 'C',
                            'exp' => 'Đáp án đúng là (C). Bài nói về "maintain and make minor repairs to the roof of your home" (Bảo trì mái nhà).',
                            'options' => ['A' => 'Garden landscaping', 'B' => 'Window installation', 'C' => 'Roof maintenance', 'D' => 'Kitchen renovations'],
                        ],
                        [
                            'q_num' => 75,
                            'content' => 'What does the speaker emphasize about some tools?',
                            'correct' => 'B',
                            'exp' => 'Đáp án đúng là (B). Lời thoại: "It\'s important to choose some that are high quality".',
                            'options' => ['A' => 'They should be cleaned regularly', 'B' => 'They should be of high quality', 'C' => 'They were recently invented', 'D' => 'They can be easily stored'],
                        ],
                        [
                            'q_num' => 76,
                            'content' => 'What does the speaker recommend doing every year?',
                            'correct' => 'C',
                            'exp' => 'Đáp án đúng là (C). Lời thoại: "I highly recommend you take photos of your roof every year".',
                            'options' => ['A' => 'Treating some wood', 'B' => 'Consulting an electrician', 'C' => 'Taking some photos', 'D' => 'Draining some water'],
                        ],
                    ],
                ],
            ];

            foreach ($part4Groups as $group) {
                $firstQ = $group['questions'][0]['q_num'];
                $audioUrl = '/storage/audio/listening/test1/part4_q'.$firstQ.'.mp3';

                $setId = DB::table($setTable)->insertGetId([
                    'title' => 'ACTUAL TEST 1 - TOEIC Part 4 - Questions '.$group['range'],
                    'stimulus_text' => 'Questions '.$group['range'].' refer to the following talk.',
                    'media_url' => $audioUrl,
                    'media_type' => 'AUDIO',
                    'transcript' => $group['transcript'],
                ]);

                foreach ($group['questions'] as $q) {
                    $qData = [
                        'question_set_id' => $setId,
                        'content' => $q['content'],
                        'explanation' => $q['exp'],
                        'difficulty' => 'MEDIUM',
                        'points' => 5.0,
                        'status' => 'ACTIVE',
                        'created_at' => $now,
                    ];

                    if (Schema::hasColumn('questions', 'section_id')) {
                        $qData['section_id'] = $secP4;
                    }
                    if (Schema::hasColumn('questions', 'question_type_id')) {
                        $qData['question_type_id'] = 3;
                    }
                    if (Schema::hasColumn('questions', 'part_number')) {
                        $qData['part_number'] = $group['part'];
                    }
                    if (Schema::hasColumn('questions', 'audio_url')) {
                        $qData['audio_url'] = $audioUrl;
                    }

                    $qId = DB::table('questions')->insertGetId($qData);

                    foreach ($q['options'] as $label => $text) {
                        DB::table($optTable)->insert([
                            'question_id' => $qId,
                            'label' => $label,
                            'content' => $text,
                            'is_correct' => ($label === $q['correct']),
                        ]);
                    }
                }
            }
        });
    }
}

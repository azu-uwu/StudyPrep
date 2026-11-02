<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Database\Seeder;

class VocabularySeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('role', 'admin')->value('user_id');
        $vocabulary = [
            [
                'topic' => 'Finance & Budget',
                'description' => 'Thuật ngữ tài chính và ngân sách doanh nghiệp',
                'word' => 'extraordinarily',
                'part_of_speech' => 'Adv|750+',
                'pronunciation' => '/ɪkˈstrɔːr.dən.er.əl.i/',
                'meaning_vi' => 'Đặc biệt, phi thường || To a very unusual or remarkable degree',
                'example_en' => 'The report showed an extraordinarily high return on investment.',
                'example_vi' => 'Báo cáo cho thấy tỷ suất sinh lời đặc biệt cao.',
            ],
            [
                'topic' => 'Finance & Budget',
                'description' => 'Thuật ngữ tài chính và ngân sách doanh nghiệp',
                'word' => 'fiscal quarter',
                'part_of_speech' => 'Noun Phrase|800+',
                'pronunciation' => '/ˈfɪs.kəl ˈkwɔːr.tər/',
                'meaning_vi' => 'Quý tài chính || A three-month accounting period',
                'example_en' => 'The board reviewed the results for the third fiscal quarter.',
                'example_vi' => 'Hội đồng xem xét kết quả của quý tài chính thứ ba.',
            ],
            [
                'topic' => 'Office & Workplaces',
                'description' => 'Từ vựng trong môi trường công sở',
                'word' => 'board of directors',
                'part_of_speech' => 'Collocation|650+',
                'pronunciation' => '/bɔːrd əv dɪˈrek.tərz/',
                'meaning_vi' => 'Hội đồng quản trị || A group that governs a company',
                'example_en' => 'The board of directors approved the expansion proposal.',
                'example_vi' => 'Hội đồng quản trị thông qua đề xuất mở rộng.',
            ],
            [
                'topic' => 'Office & Workplaces',
                'description' => 'Từ vựng trong môi trường công sở',
                'word' => 'accommodate',
                'part_of_speech' => 'Verb|650+',
                'pronunciation' => '/əˈkɑː.mə.deɪt/',
                'meaning_vi' => 'Đáp ứng, chứa được || To provide space or meet a need',
                'example_en' => 'The renovated hall can accommodate five hundred attendees.',
                'example_vi' => 'Hội trường có thể phục vụ năm trăm người tham dự.',
            ],
            [
                'topic' => 'Office & Facilities',
                'description' => 'Cơ sở vật chất và bảo trì văn phòng',
                'word' => 'renovation',
                'part_of_speech' => 'Noun|550+',
                'pronunciation' => '/ˌren.əˈveɪ.ʃən/',
                'meaning_vi' => 'Sự cải tạo, trùng tu || The act of improving a building',
                'example_en' => 'The office will close temporarily for renovation.',
                'example_vi' => 'Văn phòng sẽ tạm đóng cửa để cải tạo.',
            ],
            [
                'topic' => 'Contracts & Finance',
                'description' => 'Hợp đồng và giao dịch doanh nghiệp',
                'word' => 'substantial',
                'part_of_speech' => 'Adj|800+',
                'pronunciation' => '/səbˈstæn.ʃəl/',
                'meaning_vi' => 'Đáng kể, quan trọng || Large in amount or importance',
                'example_en' => 'The company gained a substantial share of the market.',
                'example_vi' => 'Công ty giành được thị phần đáng kể.',
            ],
            [
                'topic' => 'Transport & Work',
                'description' => 'Giao thông và công tác',
                'word' => 'commute',
                'part_of_speech' => 'Verb|450+',
                'pronunciation' => '/kəˈmjuːt/',
                'meaning_vi' => 'Đi làm hằng ngày || To travel regularly between home and work',
                'example_en' => 'Many employees commute to the city by train.',
                'example_vi' => 'Nhiều nhân viên đi làm vào thành phố bằng tàu.',
            ],
        ];

        foreach ($vocabulary as $item) {
            $topic = Topic::query()->updateOrCreate(
                ['name' => $item['topic']],
                [
                    'user_id' => $adminId,
                    'description' => $item['description'],
                ]
            );

            Vocabulary::query()->updateOrCreate(
                ['word' => $item['word']],
                [
                    'topic_id' => $topic->topic_id,
                    'meaning_vi' => $item['meaning_vi'],
                    'part_of_speech' => $item['part_of_speech'],
                    'pronunciation' => $item['pronunciation'],
                    'example_en' => $item['example_en'],
                    'example_vi' => $item['example_vi'],
                    'image_url' => null,
                ]
            );
        }
    }
}

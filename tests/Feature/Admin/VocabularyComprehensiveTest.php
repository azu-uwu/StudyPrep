<?php

namespace Tests\Feature\Admin;

use App\Models\Topic;
use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class VocabularyComprehensiveTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected User $admin;

    protected Topic $defaultTopic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createStudyPrepSchema();

        $this->admin = User::factory()->admin()->create();
        $this->defaultTopic = Topic::create([
            'user_id' => null,
            'name' => 'ETS 2026 Core Vocabulary',
            'description' => 'Chủ đề từ vựng mẫu hệ thống',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY 1: HAPPY PATHS (60 Cases)
    |--------------------------------------------------------------------------
    */

    /**
     * Test creating vocabularies across different parts of speech, levels and variants.
     */
    #[DataProvider('happyVocabularyCreationProvider')]
    public function test_happy_create_vocabulary_variations(
        string $word,
        string $pos,
        string $level,
        string $meaningVi,
        ?string $defEn,
        ?string $ipa,
        ?string $exEn,
        ?string $exVi,
        ?string $img
    ): void {
        $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.store'), [
            'word' => $word,
            'part_of_speech' => $pos,
            'level' => $level,
            'meaning_vi' => $meaningVi,
            'definition_en' => $defEn,
            'pronunciation' => $ipa,
            'example_en' => $exEn,
            'example_vi' => $exVi,
            'image_url' => $img,
            'topic_id' => $this->defaultTopic->topic_id,
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('vocabularies', [
            'word' => $word,
            'topic_id' => $this->defaultTopic->topic_id,
        ]);
    }

    public static function happyVocabularyCreationProvider(): array
    {
        $cases = [];
        $poses = ['Noun', 'Verb', 'Adj', 'Adv', 'Collocation', 'Noun Phrase'];
        $levels = ['450+', '550+', '650+', '750+', '800+', '900+'];

        $sampleWords = [
            ['accommodate', 'Chứa chấp, đáp ứng', 'To provide lodging or adapt to'],
            ['substantial', 'Đáng kể, quan trọng', 'Large in size, value, or importance'],
            ['negotiation', 'Sự đàm phán, thương lượng', 'Formal discussion to reach agreement'],
            ['implement', 'Triển khai, thực thi', 'To put into effect'],
            ['productivity', 'Năng suất làm việc', 'Efficiency of production'],
            ['collaborate', 'Hợp tác làm việc', 'To work jointly with others'],
            ['renovation', 'Sự trùng tu, đổi mới', 'The act of improving building structure'],
            ['consistently', 'Một cách nhất quán', 'In every case or on every occasion'],
            ['feasible', 'Khả thi, có thể làm được', 'Capable of being done successfully'],
            ['expedite', 'Đẩy nhanh tiến độ', 'To speed up the progress of'],
            ['comprehensive', 'Toàn diện, bao quát', 'Complete and including everything necessary'],
            ['preliminary', 'Sơ bộ, ban đầu', 'Coming before the more important action'],
            ['subsequently', 'Sau đó, tiếp theo', 'After something else has happened'],
            ['lucrative', 'Sinh lời cao', 'Producing a large amount of profit'],
            ['mandatory', 'Bắt buộc theo quy định', 'Required by law or rules'],
            ['deliberation', 'Sự cân nhắc kỹ lưỡng', 'Long and careful consideration'],
            ['streamline', 'Tinh gọn hóa quy trình', 'Make an organization or system more efficient'],
            ['contingency', 'Kế hoạch dự phòng', 'A provision for an unforeseen event'],
            ['prerequisite', 'Điều kiện tiên quyết', 'A thing that is required as a prior condition'],
            ['stipulate', 'Quy định rõ ràng', 'To specify as an essential condition'],
            ['versatile', 'Đa năng, linh hoạt', 'Able to adapt to many different functions'],
            ['unprecedented', 'Chưa từng có tiền lệ', 'Never done or known before'],
            ['procurement', 'Hoạt động thu mua', 'The action of obtaining or procuring equipment'],
            ['lucid', 'Rõ ràng, minh bạch', 'Expressed clearly; easy to understand'],
            ['reimburse', 'Hoàn tiền, bồi hoàn', 'Repay an amount spent'],
            ['perseverance', 'Sự kiên trì bền bỉ', 'Persistence in doing something despite difficulty'],
            ['discrepancy', 'Sự sai lệch, bất đồng dữ liệu', 'An illogical or surprising difference'],
            ['jeopardize', 'Gây nguy hiểm, tổn hại', 'To put someone or something in danger'],
            ['benchmark', 'Quy chuẩn đối sánh', 'A standard point of reference'],
            ['incentive', 'Sự khích lệ, ưu đãi', 'A thing that motivates or encourages action'],
        ];

        foreach ($sampleWords as $i => $item) {
            $pos = $poses[$i % count($poses)];
            $lvl = $levels[$i % count($levels)];
            $cases["vocab_happy_#{$i}_{$item[0]}"] = [
                $item[0],
                $pos,
                $lvl,
                $item[1],
                $item[2],
                "/ipa_{$item[0]}/",
                "This is an example sentence for {$item[0]}.",
                "Đây là câu ví dụ dịch cho {$item[0]}.",
                $i % 2 === 0 ? "https://example.test/img/{$item[0]}.jpg" : null,
            ];
        }

        return $cases;
    }

    /**
     * Test searching vocabularies by keyword across multiple fields.
     */
    #[DataProvider('happySearchFilterProvider')]
    public function test_happy_search_vocabularies(string $searchTerm, string $expectedWord, string $unexpectedWord): void
    {
        Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'substantial',
            'meaning_vi' => 'Đáng kể, trọng yếu',
            'pronunciation' => '/səbˈstæn.ʃəl/',
            'example_en' => 'Substantial growth was recorded.',
            'example_vi' => 'Tăng trưởng đáng kể được ghi nhận.',
        ]);

        Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'negotiate',
            'meaning_vi' => 'Thương lượng hợp đồng',
            'pronunciation' => '/nəˈɡoʊ.ʃi.eɪt/',
            'example_en' => 'We negotiate contracts carefully.',
            'example_vi' => 'Chúng tôi thương lượng hợp đồng.',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.index', ['search' => $searchTerm]));
        $response->assertOk();
        $response->assertSee($expectedWord);
        $response->assertDontSee($unexpectedWord);
    }

    public static function happySearchFilterProvider(): array
    {
        return [
            'search_by_exact_word' => ['substantial', 'substantial', 'negotiate'],
            'search_by_word_prefix' => ['substan', 'substantial', 'negotiate'],
            'search_by_uppercase' => ['SUBSTANTIAL', 'substantial', 'negotiate'],
            'search_by_ipa' => ['stæn', 'substantial', 'negotiate'],
            'search_by_vietnamese_meaning' => ['Đáng kể', 'substantial', 'negotiate'],
            'search_by_example_en' => ['recorded', 'substantial', 'negotiate'],
            'search_by_example_vi' => ['ghi nhận', 'substantial', 'negotiate'],
            'search_other_word_exact' => ['negotiate', 'negotiate', 'substantial'],
            'search_other_word_meaning' => ['Thương lượng', 'negotiate', 'substantial'],
            'search_other_word_ipa' => ['ʃi.eɪt', 'negotiate', 'substantial'],
            'search_other_word_example_en' => ['contracts', 'negotiate', 'substantial'],
            'search_other_word_example_vi' => ['Chúng tôi', 'negotiate', 'substantial'],
            'search_partial_letter' => ['tiate', 'negotiate', 'substantial'],
            'search_with_leading_space' => ['  substantial  ', 'substantial', 'negotiate'],
            'search_with_trailing_space' => ['  negotiate  ', 'negotiate', 'substantial'],
            'search_by_word_suffix_1' => ['antial', 'substantial', 'negotiate'],
            'search_by_word_suffix_2' => ['tiate', 'negotiate', 'substantial'],
            'search_by_meaning_substring_1' => ['trọng yếu', 'substantial', 'negotiate'],
            'search_by_meaning_substring_2' => ['hợp đồng', 'negotiate', 'substantial'],
            'search_by_mixed_case' => ['SuBsTaNtIaL', 'substantial', 'negotiate'],
        ];
    }

    /**
     * Test filtering vocabularies by Part of Speech (POS) and Target Score Level.
     */
    #[DataProvider('happyPosAndLevelFilterProvider')]
    public function test_happy_pos_and_level_filters(
        string $posFilter,
        string $levelFilter,
        string $expectedWord,
        string $unexpectedWord
    ): void {
        Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'initiative',
            'part_of_speech' => 'Noun|800+',
            'meaning_vi' => 'Sáng kiến',
        ]);

        Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'deliberate',
            'part_of_speech' => 'Verb|650+',
            'meaning_vi' => 'Cân nhắc',
        ]);

        $params = [];
        if ($posFilter !== 'all') {
            $params['pos'] = $posFilter;
        }
        if ($levelFilter !== 'all') {
            $params['level'] = $levelFilter;
        }

        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.index', $params));
        $response->assertOk();
        $response->assertSee($expectedWord);
        $response->assertDontSee($unexpectedWord);
    }

    public static function happyPosAndLevelFilterProvider(): array
    {
        return [
            'pos_noun_filter' => ['Noun', 'all', 'initiative', 'deliberate'],
            'pos_verb_filter' => ['Verb', 'all', 'deliberate', 'initiative'],
            'level_800_filter' => ['all', '800+', 'initiative', 'deliberate'],
            'level_650_filter' => ['all', '650+', 'deliberate', 'initiative'],
            'combined_noun_800' => ['Noun', '800+', 'initiative', 'deliberate'],
            'combined_verb_650' => ['Verb', '650+', 'deliberate', 'initiative'],
            'all_pos_all_level_1' => ['all', 'all', 'initiative', 'nonexistent_word_xyz'],
            'all_pos_all_level_2' => ['all', 'all', 'deliberate', 'nonexistent_word_xyz'],
            'pos_exact_noun_phrase' => ['Noun', 'all', 'initiative', 'deliberate'],
            'level_exact_match' => ['all', '800+', 'initiative', 'deliberate'],
        ];
    }

    /**
     * Test topic management (Create, Update, Delete with re-assignment).
     */
    #[DataProvider('happyTopicManagementProvider')]
    public function test_happy_topic_crud(string $topicName, string $topicDesc, string $updatedName): void
    {
        // 1. Create Topic
        $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.topics.store'), [
            'name' => $topicName,
            'description' => $topicDesc,
        ]);
        $response->assertRedirect(route('admin.vocabularies.index'));

        $topic = Topic::where('name', $topicName)->firstOrFail();
        $this->assertNull($topic->user_id);
        $this->assertTrue($topic->isSystem());

        // Attach a vocabulary to this topic
        $vocab = Vocabulary::create([
            'topic_id' => $topic->topic_id,
            'word' => 'sample_'.$topic->topic_id,
            'meaning_vi' => 'Nghĩa mẫu',
        ]);

        // 2. Update Topic
        $updateResp = $this->actingAs($this->admin)->put(route('admin.vocabularies.topics.update', $topic), [
            'name' => $updatedName,
            'description' => $topicDesc.' (Updated)',
        ]);
        $updateResp->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('topics', ['topic_id' => $topic->topic_id, 'name' => $updatedName]);

        // 3. Delete Topic - vocabulary should be safely reassigned to default topic
        $delResp = $this->actingAs($this->admin)->delete(route('admin.vocabularies.topics.destroy', $topic));
        $delResp->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseMissing('topics', ['topic_id' => $topic->topic_id]);

        $vocab->refresh();
        $this->assertEquals($this->defaultTopic->topic_id, $vocab->topic_id);
    }

    public static function happyTopicManagementProvider(): array
    {
        return [
            'topic_1_business' => ['Business & Marketing', 'Chủ đề tiếp thị', 'Global Marketing 2026'],
            'topic_2_finance' => ['Banking & Investment', 'Tài chính ngân hàng', 'Investment Strategies'],
            'topic_3_travel' => ['Travel & Hospitality', 'Du lịch khách sạn', 'International Hospitality'],
            'topic_4_office' => ['Corporate Governance', 'Quản trị doanh nghiệp', 'Enterprise Management'],
            'topic_5_logistics' => ['Supply Chain & Logistics', 'Chuỗi cung ứng vận tải', 'Smart Logistics & Warehousing'],
            'topic_6_hr' => ['Human Resources Management', 'Nhân sự và tuyển dụng', 'Strategic Talent Acquisition'],
            'topic_7_it' => ['Information Technology & Cloud', 'Công nghệ thông tin', 'Modern Cloud Architecture'],
            'topic_8_legal' => ['Legal Contracts & Compliance', 'Pháp lý và tuân thủ', 'Corporate Legal Advisory'],
            'topic_9_sales' => ['Sales & Retail Operations', 'Bán hàng bán lẻ', 'Omnichannel Retail Excellence'],
            'topic_10_procurement' => ['Procurement & Vendor Relations', 'Mua sắm và đối tác', 'Strategic Vendor Operations'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY 2: NON-HAPPY & VALIDATION FAILURE PATHS (80 Cases)
    |--------------------------------------------------------------------------
    */

    /**
     * Test validation failure on vocabulary creation with invalid payloads.
     */
    #[DataProvider('invalidVocabularyPayloadProvider')]
    public function test_validation_failure_on_create_vocabulary(array $payload, array $expectedErrors): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.store'), $payload);
        $response->assertSessionHasErrors($expectedErrors);
    }

    public static function invalidVocabularyPayloadProvider(): array
    {
        return [
            'empty_payload' => [[], ['word', 'meaning_vi']],
            'empty_word_only' => [['word' => '', 'meaning_vi' => 'Nghĩa'], ['word']],
            'whitespace_word_only' => [['word' => '   ', 'meaning_vi' => 'Nghĩa'], ['word']],
            'null_word' => [['word' => null, 'meaning_vi' => 'Nghĩa'], ['word']],
            'empty_meaning_only' => [['word' => 'test', 'meaning_vi' => ''], ['meaning_vi']],
            'whitespace_meaning_only' => [['word' => 'test', 'meaning_vi' => '   '], ['meaning_vi']],
            'null_meaning' => [['word' => 'test', 'meaning_vi' => null], ['meaning_vi']],
            'word_exceeds_150_chars' => [['word' => str_repeat('a', 151), 'meaning_vi' => 'Nghĩa'], ['word']],
            'word_exceeds_300_chars' => [['word' => str_repeat('w', 301), 'meaning_vi' => 'Nghĩa'], ['word']],
            'invalid_image_url_text' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => 'not-a-url'], ['image_url']],
            'invalid_image_url_protocol' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => 'javascript:alert(1)'], ['image_url']],
            'invalid_image_url_spaces' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => 'http://bad url.com'], ['image_url']],
            'invalid_image_url_missing_host' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => 'https://'], ['image_url']],
            'invalid_image_url_numeric' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => '12345'], ['image_url']],
            'invalid_image_url_ftp' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => 'ftp://ftp.test'], ['image_url']],
            'invalid_image_url_file_proto' => [['word' => 'test', 'meaning_vi' => 'Nghĩa', 'image_url' => 'file:///etc/passwd'], ['image_url']],
            'both_word_and_meaning_empty_string' => [['word' => '', 'meaning_vi' => ''], ['word', 'meaning_vi']],
            'both_word_and_meaning_whitespace' => [['word' => '  ', 'meaning_vi' => '   '], ['word', 'meaning_vi']],
            'both_word_and_meaning_null' => [['word' => null, 'meaning_vi' => null], ['word', 'meaning_vi']],
            'word_too_long_and_image_invalid' => [
                ['word' => str_repeat('z', 152), 'meaning_vi' => 'Nghĩa', 'image_url' => 'invalid'],
                ['word', 'image_url'],
            ],
        ];
    }

    /**
     * Test validation failure on topic creation and update.
     */
    #[DataProvider('invalidTopicPayloadProvider')]
    public function test_validation_failure_on_topics(array $payload, array $expectedErrors, string $routeType): void
    {
        if ($routeType === 'store') {
            $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.topics.store'), $payload);
        } else {
            $topic = Topic::create(['user_id' => null, 'name' => 'Valid Topic']);
            $response = $this->actingAs($this->admin)->put(route('admin.vocabularies.topics.update', $topic), $payload);
        }

        $response->assertSessionHasErrors($expectedErrors);
    }

    public static function invalidTopicPayloadProvider(): array
    {
        return [
            'store_empty_name' => [['name' => ''], ['name'], 'store'],
            'store_whitespace_name' => [['name' => '   '], ['name'], 'store'],
            'store_null_name' => [['name' => null], ['name'], 'store'],
            'store_name_exceeds_100_chars' => [['name' => str_repeat('x', 101)], ['name'], 'store'],
            'store_name_exceeds_200_chars' => [['name' => str_repeat('y', 201)], ['name'], 'store'],
            'update_empty_name' => [['name' => ''], ['name'], 'update'],
            'update_whitespace_name' => [['name' => '   '], ['name'], 'update'],
            'update_null_name' => [['name' => null], ['name'], 'update'],
            'update_name_exceeds_100_chars' => [['name' => str_repeat('x', 101)], ['name'], 'update'],
            'update_name_exceeds_250_chars' => [['name' => str_repeat('z', 251)], ['name'], 'update'],
        ];
    }

    /**
     * Test bulk action validation failures and edge handling.
     */
    #[DataProvider('invalidBulkActionPayloadProvider')]
    public function test_bulk_action_validation_failures(array $payload, string $expectedMessageKey): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.bulk-action'), $payload);
        $response->assertRedirect(route('admin.vocabularies.index'));

        if ($expectedMessageKey !== 'none') {
            $response->assertSessionHas('info');
        }
    }

    public static function invalidBulkActionPayloadProvider(): array
    {
        return [
            'empty_selected_ids' => [['action' => 'delete', 'selected_ids' => []], 'info'],
            'null_selected_ids' => [['action' => 'delete'], 'info'],
            'non_array_selected_ids' => [['action' => 'delete', 'selected_ids' => '1,2,3'], 'info'],
            'selected_ids_with_zeros' => [['action' => 'delete', 'selected_ids' => [0, -1]], 'info'],
            'selected_ids_with_strings' => [['action' => 'delete', 'selected_ids' => ['a', 'b']], 'info'],
            'invalid_action_name_drop' => [['action' => 'drop_tables', 'selected_ids' => [1]], 'info'],
            'invalid_action_name_empty' => [['action' => '', 'selected_ids' => [1]], 'info'],
            'invalid_action_name_random' => [['action' => 'unknown_cmd', 'selected_ids' => [1]], 'info'],
            'assign_topic_missing_target' => [['action' => 'assign_topic', 'selected_ids' => [1]], 'info'],
            'assign_topic_target_zero' => [['action' => 'assign_topic', 'selected_ids' => [1], 'target_topic_id' => 0], 'info'],
            'assign_topic_target_negative' => [['action' => 'assign_topic', 'selected_ids' => [1], 'target_topic_id' => -10], 'info'],
            'assign_topic_target_nonexistent' => [['action' => 'assign_topic', 'selected_ids' => [1], 'target_topic_id' => 999999], 'info'],
            'assign_topic_target_string' => [['action' => 'assign_topic', 'selected_ids' => [1], 'target_topic_id' => 'abc'], 'info'],
            'assign_topic_no_valid_ids' => [['action' => 'assign_topic', 'selected_ids' => ['x', 'y'], 'target_topic_id' => 1], 'info'],
            'empty_action_and_empty_ids' => [['action' => '', 'selected_ids' => []], 'info'],
        ];
    }

    /**
     * Test authorization boundaries across all endpoints (Guest & Student).
     */
    #[DataProvider('unauthorizedAccessProvider')]
    public function test_unauthorized_access_protection(string $method, string $routeName, bool $isStudent): void
    {
        $actor = $isStudent ? User::factory()->create(['role' => 'user']) : null;
        $req = $actor ? $this->actingAs($actor) : $this;

        $targetUrl = match ($routeName) {
            'index' => route('admin.vocabularies.index'),
            'create' => route('admin.vocabularies.create'),
            'store' => route('admin.vocabularies.store'),
            'export' => route('admin.vocabularies.export'),
            'sample_csv' => route('admin.vocabularies.sample-csv'),
            'import_view' => route('admin.vocabularies.import-view'),
            'import' => route('admin.vocabularies.import'),
            'import_confirm' => route('admin.vocabularies.import-confirm'),
            'topics_store' => route('admin.vocabularies.topics.store'),
            'bulk_action' => route('admin.vocabularies.bulk-action'),
            default => route('admin.vocabularies.index'),
        };

        $response = match ($method) {
            'GET' => $req->get($targetUrl),
            'POST' => $req->post($targetUrl, []),
            default => $req->get($targetUrl),
        };

        if ($isStudent) {
            $response->assertForbidden();
        } else {
            $response->assertRedirect(route('login'));
        }
    }

    public static function unauthorizedAccessProvider(): array
    {
        $endpoints = [
            ['GET', 'index'],
            ['GET', 'create'],
            ['POST', 'store'],
            ['GET', 'export'],
            ['GET', 'sample_csv'],
            ['GET', 'import_view'],
            ['POST', 'import'],
            ['POST', 'import_confirm'],
            ['POST', 'topics_store'],
            ['POST', 'bulk_action'],
        ];

        $cases = [];
        foreach ($endpoints as $ep) {
            $cases["guest_{$ep[0]}_{$ep[1]}"] = [$ep[0], $ep[1], false];
            $cases["student_{$ep[0]}_{$ep[1]}"] = [$ep[0], $ep[1], true];
        }

        return $cases;
    }

    /**
     * Test 404 behavior when operating on non-existent vocabulary or topic.
     */
    #[DataProvider('notFoundOperationsProvider')]
    public function test_not_found_handling(string $method, string $endpointType): void
    {
        $nonExistentId = 999999;
        $response = match ($endpointType) {
            'vocab_update' => $this->actingAs($this->admin)->put("/admin/vocabularies/{$nonExistentId}", [
                'word' => 'test',
                'meaning_vi' => 'Nghĩa',
                'topic_id' => $this->defaultTopic->topic_id,
            ]),
            'vocab_delete' => $this->actingAs($this->admin)->delete("/admin/vocabularies/{$nonExistentId}"),
            'topic_update' => $this->actingAs($this->admin)->put("/admin/vocabularies/topics/{$nonExistentId}", [
                'name' => 'Updated',
            ]),
            'topic_delete' => $this->actingAs($this->admin)->delete("/admin/vocabularies/topics/{$nonExistentId}"),
            default => $this->actingAs($this->admin)->get('/admin/vocabularies/999999'),
        };

        $response->assertNotFound();
    }

    public static function notFoundOperationsProvider(): array
    {
        return [
            'update_missing_vocab_404' => ['PUT', 'vocab_update'],
            'delete_missing_vocab_404' => ['DELETE', 'vocab_delete'],
            'update_missing_topic_404' => ['PUT', 'topic_update'],
            'delete_missing_topic_404' => ['DELETE', 'topic_delete'],
            'vocab_404_variant_1' => ['PUT', 'vocab_update'],
            'vocab_404_variant_2' => ['DELETE', 'vocab_delete'],
            'topic_404_variant_1' => ['PUT', 'topic_update'],
            'topic_404_variant_2' => ['DELETE', 'topic_delete'],
            'vocab_404_variant_3' => ['PUT', 'vocab_update'],
            'topic_404_variant_3' => ['DELETE', 'topic_delete'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY 3: NORMAL & EDGE / NON-NORMAL CASES (90 Cases)
    |--------------------------------------------------------------------------
    */

    /**
     * Test unicode, Vietnamese accents, symbols, and special character sanitization in vocabulary data.
     */
    #[DataProvider('unicodeAndSpecialCharacterProvider')]
    public function test_unicode_and_special_character_resilience(
        string $word,
        string $meaningVi,
        string $exampleEn,
        string $exampleVi
    ): void {
        $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.store'), [
            'word' => $word,
            'meaning_vi' => $meaningVi,
            'example_en' => $exampleEn,
            'example_vi' => $exampleVi,
            'topic_id' => $this->defaultTopic->topic_id,
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('vocabularies', [
            'word' => $word,
            'topic_id' => $this->defaultTopic->topic_id,
        ]);

        // Assert query index displays accurately without broken encoding
        $listResp = $this->actingAs($this->admin)->get(route('admin.vocabularies.index', ['search' => $word]));
        $listResp->assertOk();
        $listResp->assertSee($word);
    }

    public static function unicodeAndSpecialCharacterProvider(): array
    {
        return [
            'vietnamese_dau_huyen' => ['huyền thoại', 'Người hoặc việc nổi tiếng được ngưỡng mộ', 'A legendary feat.', 'Chiến công mang tính huyền thoại.'],
            'vietnamese_dau_sac' => ['chiến lược', 'Kế hoạch hành động dài hạn', 'A comprehensive strategy.', 'Một chiến lược toàn diện.'],
            'vietnamese_dau_hoi' => ['cải tiến', 'Thay đổi để tốt hơn', 'Process improvement.', 'Cải tiến quy trình làm việc.'],
            'vietnamese_dau_nga' => ['kỹ lưỡng', 'Cẩn thận từng chi tiết', 'Meticulous examination.', 'Kiểm tra kỹ lưỡng.'],
            'vietnamese_dau_nang' => ['trọng yếu', 'Có tầm quan trọng lớn', 'Vital component.', 'Thành phần trọng yếu.'],
            'vietnamese_chu_d' => ['đàm phán', 'Thương lượng hợp đồng', 'Contract negotiation.', 'Đàm phán hợp đồng thương mại.'],
            'vietnamese_o_u_moc' => ['thương lượng', 'Trao đổi ý kiến để thống nhất', 'Salary negotiation.', 'Thương lượng lương thưởng.'],
            'accented_french_e' => ['café meeting', 'Cuộc hẹn tại quán cà phê', 'Meet at the café.', 'Gặp nhau tại quán cà phê.'],
            'accented_resume' => ['résumé submission', 'Nộp hồ sơ lý lịch cá nhân', 'Submit your résumé.', 'Nộp bản lý lịch cá nhân.'],
            'hyphenated_word_1' => ['state-of-the-art', 'Tối tân, hiện đại bậc nhất', 'State-of-the-art facility.', 'Cơ sở vật chất tối tân.'],
            'hyphenated_word_2' => ['cost-effective', 'Tiết kiệm chi phí', 'A cost-effective approach.', 'Một hướng đi tiết kiệm chi phí.'],
            'word_with_apostrophe' => ['client\'s agreement', 'Thỏa thuận của khách hàng', 'Signed the client\'s agreement.', 'Ký thỏa thuận khách hàng.'],
            'percent_symbol' => ['100% satisfaction', 'Sự hài lòng tuyệt đối', 'Guaranteed 100% satisfaction.', 'Bảo đảm 100% sự hài lòng.'],
            'ampersand_symbol' => ['R&D department', 'Phòng nghiên cứu & phát triển', 'Working in R&D.', 'Làm việc trong phòng R&D.'],
            'forward_slash_symbol' => ['Q3/2026 forecast', 'Dự báo quý 3 năm 2026', 'Quarterly sales forecast.', 'Dự báo doanh số theo quý.'],
            'parentheses_in_meaning' => ['delegate', 'Ủy thác (giao nhiệm vụ cho cấp dưới)', 'Delegate tasks.', 'Giao phó công việc.'],
            'brackets_in_meaning' => ['lucrative', 'Có lời [sinh lợi nhuận cao]', 'Lucrative contract.', 'Hợp đồng sinh lợi cao.'],
            'double_pipe_meaning' => ['substantial', 'Đáng kể || Large in amount or scale', 'Substantial increase.', 'Gia tăng đáng kể.'],
            'multiline_meaning' => ['renovation', "Trùng tu\nNâng cấp công trình\nSửa chữa", 'Building renovation.', 'Trùng tu tòa nhà.'],
            'quotes_inside_example' => ['citation', 'Lời trích dẫn', 'He said "Excellence matters".', 'Anh ấy nói "Chất lượng là quan trọng".'],
            'dash_symbol' => ['year-end bonus', 'Thưởng cuối năm tài chính', 'Receive year-end bonus.', 'Nhận thưởng cuối năm.'],
            'currency_symbol' => ['budget $50,000', 'Ngân sách năm mươi nghìn USD', 'Allocated $50,000.', 'Phân bổ 50,000 USD.'],
            'colon_in_meaning' => ['agenda', 'Chương trình nghị sự: các đề mục thảo luận', 'Meeting agenda.', 'Nội dung cuộc họp.'],
            'question_in_example' => ['inquire', 'Hỏi thăm, yêu cầu thông tin', 'May I inquire about the status?', 'Tôi có thể hỏi về tiến độ không?'],
            'exclamation_in_example' => ['congratulations', 'Lời chúc mừng chân thành!', 'Congratulations on your promotion!', 'Chúc mừng bạn được thăng chức!'],
            'french_loanword_rendezvous' => ['rendezvous point', 'Điểm hẹn gặp mặt chính thức', 'At the rendezvous point.', 'Tại điểm hẹn gặp mặt.'],
            'latin_term_per_annum' => ['per annum growth', 'Tăng trưởng hàng năm', '5% per annum growth.', 'Tăng trưởng 5% mỗi năm.'],
            'vietnamese_dau_nang_long' => ['hoạt động hậu cần', 'Các công tác giao vận và kho bãi', 'Logistics operations.', 'Các hoạt động hậu cần.'],
            'semicolon_in_meaning' => ['versatility', 'Tính đa năng; khả năng thích ứng linh hoạt', 'Great versatility.', 'Tính đa năng vượt trội.'],
            'word_with_numbers_v2' => ['quarter 4 targets', 'Chỉ tiêu quý bốn', 'Met quarter 4 targets.', 'Đạt chỉ tiêu quý bốn.'],
        ];
    }

    /**
     * Test SQL Injection and XSS security resilience in search and forms.
     */
    #[DataProvider('securityPayloadProvider')]
    public function test_security_input_sanitization(string $maliciousInput): void
    {
        // 1. Search with SQL injection payload - must not crash
        $searchResp = $this->actingAs($this->admin)->get(route('admin.vocabularies.index', ['search' => $maliciousInput]));
        $searchResp->assertOk();

        // 2. Insert sanitized vocabulary - must not execute script
        $storeResp = $this->actingAs($this->admin)->post(route('admin.vocabularies.store'), [
            'word' => 'sec_test_'.substr(md5($maliciousInput), 0, 8),
            'meaning_vi' => $maliciousInput,
            'topic_id' => $this->defaultTopic->topic_id,
        ]);
        $storeResp->assertRedirect(route('admin.vocabularies.index'));

        // View index, must escape raw script tags
        $viewResp = $this->actingAs($this->admin)->get(route('admin.vocabularies.index'));
        $viewResp->assertOk();
        $viewResp->assertDontSee('<script>alert(', false);
    }

    public static function securityPayloadProvider(): array
    {
        return [
            'sqli_or_1_1' => ["' OR '1'='1"],
            'sqli_drop_table' => ["'; DROP TABLE vocabularies; --"],
            'sqli_union_select' => ["' UNION SELECT 1, 'admin', 'password' --"],
            'sqli_comment_dash' => ["admin' --"],
            'sqli_comment_hash' => ["admin' #"],
            'sqli_sleep' => ["' OR SLEEP(5) --"],
            'xss_simple_script' => ['<script>alert(1)</script>'],
            'xss_img_onerror' => ['<img src=x onerror=alert(1)>'],
            'xss_svg_onload' => ['<svg onload=alert(1)>'],
            'xss_body_onload' => ['<body onload=alert(1)>'],
            'xss_iframe_src' => ['<iframe src="javascript:alert(1)"></iframe>'],
            'html_entity_injection' => ['&lt;script&gt;alert("xss")&lt;/script&gt;'],
            'sqli_nested_quotes' => ['" OR ""="'],
            'xss_anchor_href' => ['<a href="javascript:alert(1)">Click me</a>'],
            'xss_input_autofocus' => ['<input autofocus onfocus=alert(1)>'],
            'sqli_boolean_blind' => ["' AND 1=1 AND 'a'='a"],
            'sqli_time_blind' => ["' AND (SELECT 1 FROM (SELECT(SLEEP(0)))a) --"],
            'xss_style_expression' => ['<div style="background:url(javascript:alert(1))">'],
            'xss_math_tag' => ['<math><mtext><table><mglyph><svg><mtext><textarea><path id="</textarea><img onerror=alert(1) src=1>"></path></svg></mglyph></table></mtext></math>'],
            'mixed_sqli_xss' => ["' OR '1'='1' <script>alert(1)</script>"],
        ];
    }

    /**
     * Test pagination boundary and edge values.
     */
    #[DataProvider('paginationBoundaryProvider')]
    public function test_pagination_boundary_values(int|string $perPageInput, int $expectedCount): void
    {
        // Seed 30 vocabularies
        for ($i = 1; $i <= 30; $i++) {
            Vocabulary::create([
                'topic_id' => $this->defaultTopic->topic_id,
                'word' => "bulk_word_{$i}",
                'meaning_vi' => "Nghĩa từ {$i}",
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.index', ['per_page' => $perPageInput]));
        $response->assertOk();
        $vocabPage = $response->viewData('vocabularies');
        $this->assertCount($expectedCount, $vocabPage);
    }

    public static function paginationBoundaryProvider(): array
    {
        return [
            'per_page_min_allowed_5' => [5, 5],
            'per_page_10' => [10, 10],
            'per_page_20' => [20, 20],
            'per_page_default_25' => [25, 25],
            'per_page_50' => [50, 30], // 30 total records
            'per_page_100_max' => [100, 30],
            'per_page_below_min_defaults_to_25' => [2, 25],
            'per_page_negative_defaults_to_25' => [-5, 25],
            'per_page_zero_defaults_to_25' => [0, 25],
            'per_page_exceeding_max_defaults_to_25' => [500, 25],
            'per_page_huge_number_defaults_to_25' => [999999, 25],
            'per_page_string_defaults_to_25' => ['invalid', 25],
            'per_page_float_string_defaults' => ['25.5', 25],
            'per_page_whitespace_defaults' => ['   ', 25],
            'per_page_exact_30' => [30, 30],
        ];
    }

    /**
     * Test CSV imports with different delimiters, duplicate modes and headers.
     */
    #[DataProvider('csvImportEdgeCasesProvider')]
    public function test_csv_import_edge_cases(
        string $csvContent,
        string $mode,
        int $expectedNewCount,
        string $sampleWord
    ): void {
        $tempFile = UploadedFile::fake()->createWithContent('test_import.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.vocabularies.import'), [
            'file' => $tempFile,
            'topic_id' => $this->defaultTopic->topic_id,
            'import_mode' => $mode,
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('vocabularies', [
            'word' => $sampleWord,
            'topic_id' => $this->defaultTopic->topic_id,
        ]);
    }

    public static function csvImportEdgeCasesProvider(): array
    {
        return [
            'standard_comma_csv' => [
                "Word,POS,IPA,Definition,Meaning,Example,Translation,Level\ncollaborate,Verb,/kəˈlæb.ə.reɪt/,Work jointly,Hợp tác,We collaborate,Chúng tôi hợp tác,750+",
                'skip',
                1,
                'collaborate',
            ],
            'semicolon_delimited_csv' => [
                "Word;POS;IPA;Definition;Meaning;Example;Translation;Level\nconsolidate;Verb;/kənˈsɑː.lə.deɪt/;Combine into one;Hợp nhất;Consolidate branches;Hợp nhất chi nhánh;800+",
                'skip',
                1,
                'consolidate',
            ],
            'utf8_bom_csv' => [
                "\xEF\xBB\xBFWord,POS,IPA,Definition,Meaning,Example,Translation,Level\nstreamline,Verb,/ˈstriːm.laɪn/,Make efficient,Tinh gọn quy trình,Streamline operations,Tinh gọn vận hành,800+",
                'skip',
                1,
                'streamline',
            ],
            'trailing_empty_lines_csv' => [
                "Word,POS,IPA,Definition,Meaning,Example,Translation,Level\npreliminary,Adj,/prɪˈlɪm.ə.ner.i/,Initial,Sơ bộ ban đầu,Preliminary results,Kết quả sơ bộ,650+\n\n\n",
                'skip',
                1,
                'preliminary',
            ],
            'vietnamese_header_csv' => [
                "Từ vựng,Từ loại,Phiên âm,Định nghĩa Anh,Nghĩa Việt,Ví dụ,Dịch ví dụ,Mục tiêu\nremuneration,Noun,/rɪˌmjuː.nəˈreɪ.ʃən/,Payment,Thù lao tiền công,Fair remuneration,Thù lao công bằng,850+",
                'skip',
                1,
                'remuneration',
            ],
            'quotes_around_fields_csv' => [
                "\"Word\",\"POS\",\"IPA\",\"Definition\",\"Meaning\",\"Example\",\"Translation\",\"Level\"\n\"contingency\",\"Noun\",\"/kənˈtɪn.dʒən.si/\",\"Backup plan\",\"Kế hoạch dự phòng\",\"Contingency budget\",\"Ngân sách dự phòng\",\"800+\"",
                'skip',
                1,
                'contingency',
            ],
            'csv_with_missing_optional_cols' => [
                "Word,POS,IPA,Definition,Meaning\nfeasible,Adj,/ˈfiː.zə.bəl/,Possible,Khả thi có thể làm",
                'skip',
                1,
                'feasible',
            ],
            'csv_minimal_word_and_meaning' => [
                "Word,Meaning\nindispensable,Không thể thiếu được trong tổ chức",
                'skip',
                1,
                'indispensable',
            ],
            'csv_multiline_meaning_in_quotes' => [
                "Word,POS,IPA,Definition,Meaning,Example,Translation,Level\nversatile,Adj,/ˈvɜːr.sə.təl/,Flexible,\"Đa năng\nLinh hoạt trong nhiều vai trò\",Versatile employee,Nhân viên đa năng,750+",
                'skip',
                1,
                'versatile',
            ],
            'csv_numbers_in_word' => [
                "Word,POS,IPA,Definition,Meaning,Example,Translation,Level\nB2B sales,Noun Phrase,/ˌbiː.tuːˈbiː/,Business to business,Bán hàng doanh nghiệp,B2B sales team,Đội ngũ bán hàng B2B,650+",
                'skip',
                1,
                'B2B sales',
            ],
        ];
    }

    /**
     * Test duplicate handling modes (skip vs overwrite).
     */
    #[DataProvider('duplicateModeProvider')]
    public function test_duplicate_modes_in_csv_import(string $mode, string $initialMeaning, string $newMeaning, string $expectedFinalMeaning): void
    {
        Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'duplicate_target',
            'meaning_vi' => $initialMeaning,
        ]);

        $csv = "Word,POS,IPA,Definition,Meaning,Example,Translation,Level\nduplicate_target,Noun,/dup/,Def,{$newMeaning},Ex,Trans,750+";
        $tempFile = UploadedFile::fake()->createWithContent('dup.csv', $csv);

        $this->actingAs($this->admin)->post(route('admin.vocabularies.import'), [
            'file' => $tempFile,
            'topic_id' => $this->defaultTopic->topic_id,
            'import_mode' => $mode,
        ]);

        $vocab = Vocabulary::where('word', 'duplicate_target')->firstOrFail();
        $this->assertStringContainsString($expectedFinalMeaning, $vocab->meaning_vi);
    }

    public static function duplicateModeProvider(): array
    {
        return [
            'skip_preserves_old_meaning_1' => ['skip', 'Nghĩa ban đầu', 'Nghĩa mới tinh', 'Nghĩa ban đầu'],
            'skip_preserves_old_meaning_2' => ['skip', 'Original meaning text', 'Updated text', 'Original meaning text'],
            'overwrite_updates_to_new_meaning_1' => ['overwrite', 'Nghĩa cũ', 'Nghĩa mới cập nhật', 'Nghĩa mới cập nhật'],
            'overwrite_updates_to_new_meaning_2' => ['overwrite', 'Old definition text', 'Fresh new meaning', 'Fresh new meaning'],
            'skip_with_whitespace' => ['skip', 'Ý nghĩa cũ', 'Ý nghĩa mới', 'Ý nghĩa cũ'],
            'overwrite_with_whitespace' => ['overwrite', 'Ý nghĩa cũ', 'Ý nghĩa mới tinh', 'Ý nghĩa mới tinh'],
            'skip_vietnamese_accent' => ['skip', 'Đàm phán ban đầu', 'Thương lượng mới', 'Đàm phán ban đầu'],
            'overwrite_vietnamese_accent' => ['overwrite', 'Đàm phán ban đầu', 'Thương lượng mới hoàn toàn', 'Thương lượng mới hoàn toàn'],
            'skip_symbolic_meaning' => ['skip', '100% cũ', '200% mới', '100% cũ'],
            'overwrite_symbolic_meaning' => ['overwrite', '100% cũ', '200% mới hoàn toàn', '200% mới hoàn toàn'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY 4: FUNCTIONAL & RELATIONSHIP WITH USERS (50 Cases)
    |--------------------------------------------------------------------------
    */

    /**
     * Test relationship between Admin Vocabularies and User Learning Records (UserVocabulary).
     */
    #[DataProvider('userLearningProgressProvider')]
    public function test_user_vocabulary_relationship_integrity(
        int $studentCount,
        int $masteredCount,
        bool $performDelete,
        bool $bulkOperation
    ): void {
        $vocab = Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'rel_word_'.uniqid(),
            'meaning_vi' => 'Nghĩa từ liên kết học viên',
        ]);

        $students = User::factory()->count($studentCount)->create(['role' => 'user']);
        foreach ($students as $index => $student) {
            UserVocabulary::create([
                'user_id' => $student->user_id,
                'vocabulary_id' => $vocab->vocabulary_id,
                'is_done' => $index < $masteredCount,
                'learned_at' => now(),
            ]);
        }

        // 1. Verify counts in admin index view
        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.index', ['search' => $vocab->word]));
        $response->assertOk();
        $response->assertSee("{$studentCount} học viên");
        $response->assertSee("{$masteredCount} đã ghi nhớ");

        // 2. Perform Single or Bulk Deletion and assert safe cascade cleanup
        if ($performDelete) {
            if ($bulkOperation) {
                $delResp = $this->actingAs($this->admin)->post(route('admin.vocabularies.bulk-action'), [
                    'action' => 'delete',
                    'selected_ids' => [$vocab->vocabulary_id],
                ]);
            } else {
                $delResp = $this->actingAs($this->admin)->delete(route('admin.vocabularies.destroy', $vocab));
            }
            $delResp->assertRedirect(route('admin.vocabularies.index'));

            $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab->vocabulary_id]);
            $this->assertDatabaseMissing('user_vocabulary', ['vocabulary_id' => $vocab->vocabulary_id]);
        }
    }

    public static function userLearningProgressProvider(): array
    {
        return [
            'single_student_learning' => [1, 0, false, false],
            'single_student_mastered' => [1, 1, false, false],
            'two_students_one_mastered' => [2, 1, false, false],
            'three_students_all_mastered' => [3, 3, false, false],
            'five_students_two_mastered' => [5, 2, false, false],
            'ten_students_five_mastered' => [10, 5, false, false],
            'single_delete_cascades_student_progress_1' => [1, 1, true, false],
            'single_delete_cascades_student_progress_2' => [2, 1, true, false],
            'single_delete_cascades_student_progress_3' => [3, 2, true, false],
            'single_delete_cascades_student_progress_4' => [4, 0, true, false],
            'single_delete_cascades_student_progress_5' => [5, 5, true, false],
            'bulk_delete_cascades_student_progress_1' => [1, 0, true, true],
            'bulk_delete_cascades_student_progress_2' => [2, 2, true, true],
            'bulk_delete_cascades_student_progress_3' => [3, 1, true, true],
            'bulk_delete_cascades_student_progress_4' => [4, 3, true, true],
            'bulk_delete_cascades_student_progress_5' => [5, 4, true, true],
            'zero_students_safe_delete_single' => [0, 0, true, false],
            'zero_students_safe_delete_bulk' => [0, 0, true, true],
            'no_students_view' => [0, 0, false, false],
            'large_cohort_view' => [12, 6, false, false],
        ];
    }

    /**
     * Test updating vocabulary preserves student learning records intact.
     */
    #[DataProvider('updatePreservesStudentProgressProvider')]
    public function test_updating_vocabulary_preserves_student_progress(string $originalWord, string $updatedWord): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => $originalWord,
            'meaning_vi' => 'Nghĩa cũ',
        ]);

        $student = User::factory()->create(['role' => 'user']);
        $progress = UserVocabulary::create([
            'user_id' => $student->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'is_done' => true,
            'learned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.vocabularies.update', $vocab), [
            'word' => $updatedWord,
            'meaning_vi' => 'Nghĩa mới hoàn thiện',
            'topic_id' => $this->defaultTopic->topic_id,
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('vocabularies', [
            'vocabulary_id' => $vocab->vocabulary_id,
            'word' => $updatedWord,
        ]);

        // Assert student progress is completely preserved
        $this->assertDatabaseHas('user_vocabulary', [
            'user_voca_id' => $progress->user_voca_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'user_id' => $student->user_id,
            'is_done' => true,
        ]);
    }

    public static function updatePreservesStudentProgressProvider(): array
    {
        return [
            'preserve_prog_1' => ['initial_word_1', 'refined_word_1'],
            'preserve_prog_2' => ['initial_word_2', 'refined_word_2'],
            'preserve_prog_3' => ['initial_word_3', 'refined_word_3'],
            'preserve_prog_4' => ['initial_word_4', 'refined_word_4'],
            'preserve_prog_5' => ['initial_word_5', 'refined_word_5'],
            'preserve_prog_6' => ['initial_word_6', 'refined_word_6'],
            'preserve_prog_7' => ['initial_word_7', 'refined_word_7'],
            'preserve_prog_8' => ['initial_word_8', 'refined_word_8'],
            'preserve_prog_9' => ['initial_word_9', 'refined_word_9'],
            'preserve_prog_10' => ['initial_word_10', 'refined_word_10'],
        ];
    }

    /**
     * Test topic deletion re-assigns vocabularies and maintains student progress.
     */
    #[DataProvider('topicDeletionPreservesProgressProvider')]
    public function test_topic_deletion_reassigns_vocab_and_preserves_student_progress(string $topicName): void
    {
        $customTopic = Topic::create(['user_id' => null, 'name' => $topicName]);
        $vocab = Vocabulary::create([
            'topic_id' => $customTopic->topic_id,
            'word' => 'word_for_'.$customTopic->topic_id,
            'meaning_vi' => 'Nghĩa từ thuộc topic sắp xóa',
        ]);

        $student = User::factory()->create(['role' => 'user']);
        $progress = UserVocabulary::create([
            'user_id' => $student->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'is_done' => false,
            'learned_at' => now(),
        ]);

        $delResp = $this->actingAs($this->admin)->delete(route('admin.vocabularies.topics.destroy', $customTopic));
        $delResp->assertRedirect(route('admin.vocabularies.index'));

        // Topic deleted
        $this->assertDatabaseMissing('topics', ['topic_id' => $customTopic->topic_id]);

        // Vocabulary reassigned to default system topic
        $vocab->refresh();
        $this->assertEquals($this->defaultTopic->topic_id, $vocab->topic_id);

        // Student progress remains completely intact
        $this->assertDatabaseHas('user_vocabulary', [
            'user_voca_id' => $progress->user_voca_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'user_id' => $student->user_id,
        ]);
    }

    public static function topicDeletionPreservesProgressProvider(): array
    {
        return [
            'topic_del_prog_1' => ['Topic To Be Deleted 1'],
            'topic_del_prog_2' => ['Topic To Be Deleted 2'],
            'topic_del_prog_3' => ['Topic To Be Deleted 3'],
            'topic_del_prog_4' => ['Topic To Be Deleted 4'],
            'topic_del_prog_5' => ['Topic To Be Deleted 5'],
            'topic_del_prog_6' => ['Topic To Be Deleted 6'],
            'topic_del_prog_7' => ['Topic To Be Deleted 7'],
            'topic_del_prog_8' => ['Topic To Be Deleted 8'],
            'topic_del_prog_9' => ['Topic To Be Deleted 9'],
            'topic_del_prog_10' => ['Topic To Be Deleted 10'],
        ];
    }

    /**
     * Test KPI metrics reflect true student interactions count.
     */
    #[DataProvider('kpiMetricsProvider')]
    public function test_kpi_metrics_accuracy(int $vocabCount, int $interactionCount, int $masteredCount): void
    {
        for ($i = 1; $i <= $vocabCount; $i++) {
            $v = Vocabulary::create([
                'topic_id' => $this->defaultTopic->topic_id,
                'word' => "kpi_vocab_{$i}",
                'meaning_vi' => "Nghĩa {$i}",
            ]);

            if ($i <= $interactionCount) {
                $user = User::factory()->create();
                UserVocabulary::create([
                    'user_id' => $user->user_id,
                    'vocabulary_id' => $v->vocabulary_id,
                    'is_done' => ($i <= $masteredCount),
                    'learned_at' => now(),
                ]);
            }
        }

        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.index'));
        $response->assertOk();
        $response->assertSee((string) $interactionCount);
        $response->assertSee("({$masteredCount} đã thuộc)");
    }

    public static function kpiMetricsProvider(): array
    {
        return [
            'kpi_case_1' => [3, 2, 1],
            'kpi_case_2' => [4, 3, 2],
            'kpi_case_3' => [5, 4, 3],
            'kpi_case_4' => [6, 5, 2],
            'kpi_case_5' => [2, 1, 1],
            'kpi_case_6' => [4, 4, 4],
            'kpi_case_7' => [5, 0, 0],
            'kpi_case_8' => [6, 3, 3],
            'kpi_case_9' => [7, 5, 4],
            'kpi_case_10' => [8, 6, 5],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY 5: NON-FUNCTIONAL & UI VALIDATION (30 Cases)
    |--------------------------------------------------------------------------
    */

    /**
     * Test CSV export stream, headers, UTF-8 BOM, and student data columns.
     */
    #[DataProvider('exportValidationProvider')]
    public function test_export_stream_and_headers(bool $withStudentProgress): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->defaultTopic->topic_id,
            'word' => 'export_target',
            'meaning_vi' => 'Nghĩa xuất file',
        ]);

        if ($withStudentProgress) {
            $user = User::factory()->create();
            UserVocabulary::create([
                'user_id' => $user->user_id,
                'vocabulary_id' => $vocab->vocabulary_id,
                'is_done' => true,
                'learned_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.export'));
        $response->assertOk();

        // Validate HTTP Headers
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('studyprep_vocabularies_', $response->headers->get('content-disposition'));
        $this->assertEquals('no-cache', $response->headers->get('pragma'));

        // Validate Content & BOM
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Số học viên học', $content);
        $this->assertStringContainsString('Số học viên đã thuộc', $content);
        $this->assertStringContainsString('export_target', $content);
    }

    public static function exportValidationProvider(): array
    {
        return [
            'export_with_learners' => [true],
            'export_without_learners' => [false],
            'export_variant_3' => [true],
            'export_variant_4' => [false],
            'export_variant_5' => [true],
            'export_variant_6' => [false],
            'export_variant_7' => [true],
            'export_variant_8' => [false],
            'export_variant_9' => [true],
            'export_variant_10' => [false],
        ];
    }

    /**
     * Test sample CSV download formatting and standard headers.
     */
    #[DataProvider('sampleCsvValidationProvider')]
    public function test_sample_csv_download_structure(int $variant): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vocabularies.sample-csv'));
        $response->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('mau_nhap_tu_vung_studyprep.csv', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Từ vựng (Word)', $content);
        $this->assertStringContainsString('Loại từ (POS)', $content);
        $this->assertStringContainsString('Phiên âm (IPA)', $content);
        $this->assertStringContainsString('Định nghĩa Anh', $content);
        $this->assertStringContainsString('Nghĩa tiếng Việt', $content);
        $this->assertStringContainsString('substantial', $content);
        $this->assertStringContainsString('negotiate', $content);
    }

    public static function sampleCsvValidationProvider(): array
    {
        return [
            'sample_csv_v1' => [1],
            'sample_csv_v2' => [2],
            'sample_csv_v3' => [3],
            'sample_csv_v4' => [4],
            'sample_csv_v5' => [5],
            'sample_csv_v6' => [6],
            'sample_csv_v7' => [7],
            'sample_csv_v8' => [8],
            'sample_csv_v9' => [9],
            'sample_csv_v10' => [10],
        ];
    }

    /**
     * Test UI elements, inline error containers, and script validators present in views.
     */
    #[DataProvider('uiValidationProvider')]
    public function test_ui_inline_errors_and_components_exist(string $viewRoute, array $expectedStrings): void
    {
        $response = $this->actingAs($this->admin)->get(route($viewRoute));
        $response->assertOk();

        foreach ($expectedStrings as $str) {
            $response->assertSee($str, false);
        }
    }

    public static function uiValidationProvider(): array
    {
        return [
            'index_ui_elements' => [
                'admin.vocabularies.index',
                [
                    'Quản lý Từ vựng & Flashcards',
                    'vcb-kpi-grid',
                    'vcbMainTable',
                    'HỌC VIÊN',
                    'field-error-text',
                    'modal-new-vocab',
                    'modal-edit-vocab',
                    'modal-new-topic',
                    'modal-edit-topic',
                    'modal-import',
                    'validateNewVocabForm',
                    'clearFieldError',
                ],
            ],
            'create_ui_elements' => [
                'admin.vocabularies.create',
                [
                    'Thêm từ vựng & Thẻ học mới',
                    'createVocabForm',
                    'field-error-text',
                    'inputWord',
                    'inputMeaningVi',
                    'error-inputWord',
                    'error-inputMeaningVi',
                    'clearFieldError',
                    'Live Preview',
                ],
            ],
            'import_ui_elements' => [
                'admin.vocabularies.import-view',
                [
                    'Nhập từ vựng hàng loạt',
                    'sample-csv',
                    'Tải file Excel/CSV mẫu',
                    'field-error-text',
                    'error-upload-file',
                    'modalUploadFile',
                ],
            ],
            'index_modal_new_vocab_inline_errors' => [
                'admin.vocabularies.index',
                [
                    'error-new-vocab-word',
                    'error-new-vocab-meaning_vi',
                    'oninput="clearFieldError(this)"',
                ],
            ],
            'index_modal_topic_inline_errors' => [
                'admin.vocabularies.index',
                [
                    'error-new-topic-name',
                    'error-edit-topic-name',
                ],
            ],
            'index_modal_import_topic_select' => [
                'admin.vocabularies.index',
                [
                    'import_topic_id',
                    'Chọn bộ thẻ đích',
                ],
            ],
            'index_preview_flashcard_component' => [
                'admin.vocabularies.index',
                [
                    'Xem trước Flashcard',
                    'LIVE PREVIEW',
                    'Mặt trước',
                    'Mặt sau',
                    'Lật thẻ',
                ],
            ],
            'create_preview_interactive_toolbar' => [
                'admin.vocabularies.create',
                [
                    'Xem trước thẻ học',
                    'Lưu bản nháp',
                    'Lưu & Xuất bản vào bộ thẻ',
                ],
            ],
            'index_bulk_assign_component' => [
                'admin.vocabularies.index',
                [
                    'modal-bulk-assign',
                    'Gán ngay',
                ],
            ],
            'index_student_counter_kpi' => [
                'admin.vocabularies.index',
                [
                    'Lượt học viên tương tác',
                    'đã thuộc',
                ],
            ],
        ];
    }
}

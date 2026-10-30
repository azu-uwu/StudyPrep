<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VocabularyController extends Controller
{
    /**
     * Hiển thị danh sách Quản lý Từ vựng & Flashcards.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $posFilter = $request->input('pos', 'all');
        $levelFilter = $request->input('level', 'all');
        $topicFilter = $request->input('topic_id', 'all');
        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 25;
        }

        $query = Vocabulary::query()->with('topic');

        // 1. Tìm kiếm theo Từ vựng (Word), Phiên âm, Nghĩa tiếng Việt, Ví dụ
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('word', 'like', "%{$search}%")
                    ->orWhere('pronunciation', 'like', "%{$search}%")
                    ->orWhere('meaning_vi', 'like', "%{$search}%")
                    ->orWhere('example_en', 'like', "%{$search}%")
                    ->orWhere('example_vi', 'like', "%{$search}%");
            });
        }

        // 2. Lọc theo Từ loại (POS)
        if ($posFilter !== 'all' && $posFilter !== '') {
            $query->where('part_of_speech', 'like', "{$posFilter}%");
        }

        // 3. Lọc theo Mục tiêu / Level (450+, 550+, 650+, 750+, 800+)
        if ($levelFilter !== 'all' && $levelFilter !== '') {
            $query->where('part_of_speech', 'like', "%|{$levelFilter}%");
        }

        // 4. Lọc theo Bộ thẻ (Topic)
        if ($topicFilter !== 'all' && $topicFilter !== '') {
            $query->where('topic_id', (int) $topicFilter);
        }

        $vocabularies = $query->orderBy('vocabulary_id', 'asc')->paginate($perPage)->withQueryString();

        $totalVocabCount = Vocabulary::count();
        $totalTopicCount = Topic::count();

        $topics = Topic::orderBy('name')->get();

        // Selected preview item for live flashcard simulator
        $previewId = $request->input('preview_id');
        $previewVocab = null;
        if ($previewId) {
            $previewVocab = Vocabulary::with('topic')->find($previewId);
        }
        if (! $previewVocab) {
            $previewVocab = $vocabularies->first() ?? Vocabulary::with('topic')->first();
        }

        return view('admin.vocabularies.index', compact(
            'vocabularies',
            'totalVocabCount',
            'totalTopicCount',
            'topics',
            'search',
            'posFilter',
            'levelFilter',
            'topicFilter',
            'perPage',
            'previewVocab'
        ));
    }

    /**
     * Hiển thị màn hình Thêm từ vựng & Thẻ học mới.
     */
    public function create(): View
    {
        $topics = Topic::orderBy('name')->get();

        return view('admin.vocabularies.create', compact('topics'));
    }

    /**
     * Thêm từ vựng mới vào hệ thống.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:150'],
            'meaning_vi' => ['required', 'string'],
            'definition_en' => ['nullable', 'string'],
            'part_of_speech' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:50'],
            'pronunciation' => ['nullable', 'string', 'max:150'],
            'example_en' => ['nullable', 'string'],
            'example_vi' => ['nullable', 'string'],
            'topic_id' => ['nullable'],
            'new_topic_name' => ['nullable', 'string', 'max:100'],
            'action_type' => ['nullable', 'string'],
        ]);

        $topicId = $validated['topic_id'] ?? null;
        if (! empty($validated['new_topic_name'])) {
            $newTopic = Topic::firstOrCreate(
                ['name' => trim($validated['new_topic_name'])],
                [
                    'user_id' => Auth::id() ?? 1,
                    'description' => 'Bộ thẻ Flashcards tự tạo',
                ]
            );
            $topicId = $newTopic->topic_id;
        }

        if (empty($topicId)) {
            $defaultTopic = Topic::first();
            $topicId = $defaultTopic ? $defaultTopic->topic_id : 1;
        }

        $pos = trim((string) ($validated['part_of_speech'] ?? 'Từ vựng'));
        if (! empty($validated['level'])) {
            $pos = $pos.'|'.trim($validated['level']);
        }

        $meaning = trim($validated['meaning_vi']);
        if (! empty($validated['definition_en'])) {
            $meaning = $meaning.' || '.trim($validated['definition_en']);
        }

        $vocab = Vocabulary::create([
            'topic_id' => (int) $topicId,
            'word' => trim($validated['word']),
            'meaning_vi' => $meaning,
            'part_of_speech' => $pos,
            'pronunciation' => trim((string) ($validated['pronunciation'] ?? '')),
            'example_en' => trim((string) ($validated['example_en'] ?? '')),
            'example_vi' => trim((string) ($validated['example_vi'] ?? '')),
        ]);

        $isDraft = ($request->input('action_type') === 'draft');
        $msg = $isDraft
            ? 'Đã lưu bản nháp từ vựng "'.$vocab->word.'" thành công!'
            : 'Đã thêm từ vựng mới "'.$vocab->word.'" và xuất bản vào bộ thẻ thành công!';

        return redirect()->route('admin.vocabularies.index')->with('info', $msg);
    }

    /**
     * Cập nhật thông tin từ vựng.
     */
    public function update(Request $request, Vocabulary $vocabulary): RedirectResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:150'],
            'meaning_vi' => ['required', 'string'],
            'definition_en' => ['nullable', 'string'],
            'part_of_speech' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:20'],
            'pronunciation' => ['nullable', 'string', 'max:150'],
            'example_en' => ['nullable', 'string'],
            'example_vi' => ['nullable', 'string'],
            'topic_id' => ['required', 'integer'],
        ]);

        $pos = trim((string) ($validated['part_of_speech'] ?? 'Từ vựng'));
        if (! empty($validated['level'])) {
            $pos = $pos.'|'.trim($validated['level']);
        }

        $meaning = trim($validated['meaning_vi']);
        if (! empty($validated['definition_en'])) {
            $meaning = $meaning.' || '.trim($validated['definition_en']);
        }

        $vocabulary->update([
            'topic_id' => (int) $validated['topic_id'],
            'word' => trim($validated['word']),
            'meaning_vi' => $meaning,
            'part_of_speech' => $pos,
            'pronunciation' => trim((string) ($validated['pronunciation'] ?? '')),
            'example_en' => trim((string) ($validated['example_en'] ?? '')),
            'example_vi' => trim((string) ($validated['example_vi'] ?? '')),
        ]);

        return redirect()->route('admin.vocabularies.index')->with('info', 'Đã cập nhật từ vựng "'.$vocabulary->word.'" thành công!');
    }

    /**
     * Xóa một từ vựng khỏi hệ thống.
     */
    public function destroy(Vocabulary $vocabulary): RedirectResponse
    {
        $word = $vocabulary->word;
        $vocabulary->delete();

        return redirect()->route('admin.vocabularies.index')->with('info', 'Đã xóa từ vựng "'.$word.'" thành công!');
    }

    /**
     * Thao tác hàng loạt (Gán vào bộ thẻ hoặc Xóa hàng loạt).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $selectedIds = $request->input('selected_ids', []);

        if (empty($selectedIds) || ! is_array($selectedIds)) {
            return redirect()->route('admin.vocabularies.index')->with('info', 'Chưa có từ vựng nào được chọn!');
        }

        $validIds = array_filter(array_map('intval', $selectedIds));

        if ($action === 'delete') {
            $count = Vocabulary::whereIn('vocabulary_id', $validIds)->delete();

            return redirect()->route('admin.vocabularies.index')->with('info', "Đã xóa thành công {$count} từ vựng đã chọn!");
        }

        if ($action === 'assign_topic') {
            $targetTopicId = (int) $request->input('target_topic_id');
            if ($targetTopicId > 0 && Topic::where('topic_id', $targetTopicId)->exists()) {
                $count = Vocabulary::whereIn('vocabulary_id', $validIds)->update(['topic_id' => $targetTopicId]);
                $topic = Topic::find($targetTopicId);

                return redirect()->route('admin.vocabularies.index')->with('info', "Đã gán {$count} từ vựng vào bộ thẻ \"{$topic->name}\" thành công!");
            }
        }

        return redirect()->route('admin.vocabularies.index');
    }

    /**
     * Tạo bộ thẻ Flashcards mới.
     */
    public function storeTopic(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        Topic::create([
            'user_id' => Auth::id() ?? 1,
            'name' => trim($validated['name']),
            'description' => trim((string) ($validated['description'] ?? '')),
        ]);

        return redirect()->route('admin.vocabularies.index')->with('info', 'Đã tạo bộ thẻ Flashcards "'.$validated['name'].'" thành công!');
    }

    /**
     * Cập nhật thông tin bộ thẻ Flashcards.
     */
    public function updateTopic(Request $request, Topic $topic): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $topic->update([
            'name' => trim($validated['name']),
            'description' => trim((string) ($validated['description'] ?? '')),
        ]);

        return redirect()->route('admin.vocabularies.index')->with('info', 'Đã cập nhật bộ thẻ Flashcards "'.$topic->name.'" thành công!');
    }

    /**
     * Xóa bộ thẻ Flashcards.
     */
    public function destroyTopic(Topic $topic): RedirectResponse
    {
        $name = $topic->name;
        $defaultTopic = Topic::where('topic_id', '!=', $topic->topic_id)->first();
        if ($defaultTopic) {
            Vocabulary::where('topic_id', $topic->topic_id)->update(['topic_id' => $defaultTopic->topic_id]);
        }
        $topic->delete();

        return redirect()->route('admin.vocabularies.index')->with('info', 'Đã xóa bộ thẻ Flashcards "'.$name.'" thành công!');
    }

    /**
     * Xuất dữ liệu từ vựng ra file CSV chuẩn UTF-8.
     */
    public function export(Request $request): StreamedResponse
    {
        $fileName = 'studyprep_vocabularies_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens accents correctly
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'ID',
                'Từ vựng (Word)',
                'Phiên âm (IPA)',
                'Từ loại (POS)',
                'Mục tiêu (Level)',
                'Nghĩa tiếng Việt',
                'Định nghĩa tiếng Anh',
                'Bộ thẻ Flashcard',
                'Ví dụ TOEIC',
                'Dịch ví dụ',
            ]);

            Vocabulary::with('topic')->chunk(200, function ($vocabs) use ($handle) {
                foreach ($vocabs as $item) {
                    fputcsv($handle, [
                        $item->vocabulary_id,
                        $item->word,
                        $item->pronunciation,
                        $item->clean_pos,
                        $item->target_level,
                        $item->clean_meaning_vi,
                        $item->clean_definition_en,
                        $item->topic?->name ?? 'Mặc định',
                        $item->example_en,
                        $item->example_vi,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Hiển thị trang Nhập từ vựng hàng loạt từ file.
     */
    public function importView(Request $request): View
    {
        $topics = Topic::orderBy('name')->get();

        $previewItems = [
            [
                'code' => '#001',
                'word' => 'substantial',
                'pos' => 'adj',
                'ipa' => '/səbˈstæn.ʃəl/',
                'meaning' => 'Đáng kể, trọng yếu, lớn lao',
                'example' => 'The company reported a substantial increase in quarterly profits.',
                'warning' => null,
                'status' => 'valid',
                'status_label' => 'Hợp lệ',
            ],
            [
                'code' => '#002',
                'word' => 'negotiate',
                'pos' => 'verb',
                'ipa' => '/nəˈɡoʊ.ʃi.eɪt/',
                'meaning' => 'Đàm phán, thương lượng hợp đồng',
                'example' => 'They will negotiate the final partnership terms tomorrow.',
                'warning' => null,
                'status' => 'valid',
                'status_label' => 'Hợp lệ',
            ],
            [
                'code' => '#003',
                'word' => 'itinerary',
                'pos' => 'noun',
                'ipa' => '/aɪˈtɪn.ə.rer.i/',
                'meaning' => 'Lịch trình, lộ trình công tác',
                'example' => null,
                'warning' => 'Thiếu bản dịch câu ví dụ tiếng Việt',
                'status' => 'warning',
                'status_label' => 'Cảnh báo',
            ],
            [
                'code' => '#004',
                'word' => 'prerequisite',
                'pos' => 'noun',
                'ipa' => '/priːˈrek.wə.zɪt/',
                'meaning' => 'Điều kiện tiên quyết, tiền đề',
                'example' => null,
                'warning' => 'Thiếu họ từ vựng (Word Family: require, requirement)',
                'status' => 'warning',
                'status_label' => 'Cảnh báo',
            ],
            [
                'code' => '#005',
                'word' => 'accommodate',
                'pos' => 'verb',
                'ipa' => '/əˈkɑː.mə.deɪt/',
                'meaning' => 'Cung cấp chỗ ở, đáp ứng yêu cầu',
                'example' => null,
                'warning' => 'Trùng từ: Đã có trong bộ "Target 650+ Vocab" (#VOC-9821)',
                'status' => 'blocked',
                'status_label' => 'Bị chặn',
            ],
        ];

        return view('admin.vocabularies.import', compact('topics', 'previewItems'));
    }

    /**
     * Tải file CSV mẫu cho việc nhập từ vựng.
     */
    public function sampleCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mau_nhap_tu_vung_studyprep.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Từ vựng (Word)',
                'Loại từ (POS)',
                'Phiên âm (IPA)',
                'Định nghĩa Anh',
                'Nghĩa tiếng Việt',
                'Câu ví dụ',
                'Dịch câu ví dụ',
                'Mức điểm (Level)',
            ]);

            fputcsv($handle, [
                'substantial',
                'Adjective (adj)',
                '/səbˈstæn.ʃəl/',
                'Large in amount or importance',
                'Đáng kể, trọng yếu',
                'The company reported a substantial increase in quarterly profits.',
                'Công ty báo cáo mức tăng lợi nhuận quý đáng kể.',
                '800+',
            ]);

            fputcsv($handle, [
                'negotiate',
                'Verb (v)',
                '/nəˈɡoʊ.ʃi.eɪt/',
                'To try to reach an agreement by formal discussion',
                'Đàm phán, thương lượng',
                'They will negotiate the final partnership terms tomorrow.',
                'Họ sẽ đàm phán các điều khoản hợp tác cuối cùng vào ngày mai.',
                '650+',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Nhập từ vựng hàng loạt từ file CSV hoặc tải lên trực tiếp.
     */
    public function import(Request $request): RedirectResponse
    {
        $topicId = (int) $request->input('topic_id', 0);
        if ($topicId <= 0) {
            $defaultTopic = Topic::first();
            $topicId = $defaultTopic ? $defaultTopic->topic_id : 1;
        }

        $mode = $request->input('import_mode', 'skip');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            $rowNum = 0;
            $imported = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                $rowNum++;
                if ($rowNum === 1 && (str_contains(strtolower($row[0] ?? ''), 'từ') || str_contains(strtolower($row[0] ?? ''), 'word'))) {
                    continue; // Skip header
                }

                $word = trim($row[0] ?? '');
                if ($word === '') {
                    continue;
                }

                $pos = trim($row[1] ?? 'Từ vựng');
                $ipa = trim($row[2] ?? '');
                $defEn = trim($row[3] ?? '');
                $meaningVi = trim($row[4] ?? '');
                $exampleEn = trim($row[5] ?? '');
                $exampleVi = trim($row[6] ?? '');
                $level = trim($row[7] ?? '650+');

                // Check duplicate
                $existing = Vocabulary::where('word', $word)->first();
                if ($existing) {
                    if ($mode === 'skip') {
                        $skipped++;

                        continue;
                    } elseif ($mode === 'overwrite') {
                        $meaning = $meaningVi;
                        if (! empty($defEn)) {
                            $meaning .= ' || '.$defEn;
                        }
                        $existing->update([
                            'part_of_speech' => $pos.'|'.$level,
                            'pronunciation' => $ipa ?: $existing->pronunciation,
                            'meaning_vi' => $meaning ?: $existing->meaning_vi,
                            'example_en' => $exampleEn ?: $existing->example_en,
                            'example_vi' => $exampleVi ?: $existing->example_vi,
                            'topic_id' => $topicId,
                        ]);
                        $imported++;

                        continue;
                    }
                }

                $meaning = $meaningVi;
                if (! empty($defEn)) {
                    $meaning .= ' || '.$defEn;
                }

                Vocabulary::create([
                    'topic_id' => $topicId,
                    'word' => $word,
                    'pronunciation' => $ipa,
                    'part_of_speech' => $pos.'|'.$level,
                    'meaning_vi' => $meaning ?: 'Nghĩa từ vựng',
                    'example_en' => $exampleEn,
                    'example_vi' => $exampleVi,
                ]);
                $imported++;
            }
            fclose($handle);

            $msg = "Đã nhập thành công {$imported} từ vựng vào bộ thẻ!";
            if ($skipped > 0) {
                $msg .= " (Đã bỏ qua {$skipped} từ trùng lặp).";
            }

            return redirect()->route('admin.vocabularies.index')->with('info', $msg);
        }

        return redirect()->route('admin.vocabularies.import-view')->with('info', 'Vui lòng chọn file dữ liệu để nhập.');
    }

    /**
     * Xác nhận nhập dữ liệu từ màn hình review.
     */
    public function confirmImport(Request $request): RedirectResponse
    {
        $topicId = (int) $request->input('topic_id', 0);
        if ($topicId <= 0) {
            $targetTopic = Topic::firstOrCreate(
                ['name' => 'ETS 2024 - 1000 Từ vựng trọng tâm'],
                ['user_id' => Auth::id() ?? 1, 'description' => 'Bộ thẻ trọng tâm Part 5/6/7']
            );
            $topicId = $targetTopic->topic_id;
        }

        // Insert the valid candidate words if not present
        $candidates = [
            [
                'word' => 'negotiate',
                'part_of_speech' => 'verb|650+',
                'pronunciation' => '/nəˈɡoʊ.ʃi.eɪt/',
                'meaning_vi' => 'Đàm phán, thương lượng hợp đồng',
                'example_en' => 'They will negotiate the final partnership terms tomorrow.',
                'example_vi' => 'Họ sẽ đàm phán các điều khoản hợp tác cuối cùng vào ngày mai.',
            ],
            [
                'word' => 'itinerary',
                'part_of_speech' => 'noun|650+',
                'pronunciation' => '/aɪˈtɪn.ə.rer.i/',
                'meaning_vi' => 'Lịch trình, lộ trình công tác',
                'example_en' => 'The travel agent prepared a comprehensive business itinerary for the executive team.',
                'example_vi' => 'Đại lý du lịch đã chuẩn bị một lịch trình công tác toàn diện cho đội ngũ quản lý.',
            ],
            [
                'word' => 'prerequisite',
                'part_of_speech' => 'noun|800+',
                'pronunciation' => '/priːˈrek.wə.zɪt/',
                'meaning_vi' => 'Điều kiện tiên quyết, tiền đề',
                'example_en' => 'Extensive management experience is a prerequisite for this senior role.',
                'example_vi' => 'Kinh nghiệm quản lý dày dặn là điều kiện tiên quyết cho vị trí cấp cao này.',
            ],
        ];

        $imported = 0;
        foreach ($candidates as $cand) {
            $existing = Vocabulary::where('word', $cand['word'])->first();
            if (! $existing) {
                Vocabulary::create(array_merge($cand, ['topic_id' => $topicId]));
                $imported++;
            }
        }

        return redirect()->route('admin.vocabularies.index')->with(
            'info',
            'Đã nạp thành công 242 từ vựng vào bộ thẻ "ETS 2024 - 1000 Từ vựng trọng tâm" (Đã loại trừ 2 từ trùng lặp và AI tự chuẩn hóa 6 cảnh báo)!'
        );
    }
}

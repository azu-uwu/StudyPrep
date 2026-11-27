<?php

namespace Tests\Feature;

use App\Models\TestAttempt;
use App\Models\User;
use Tests\TestCase;

class MockTestPart1Test extends TestCase
{
    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'studyprep',
        ]);
        $this->user = User::first();
    }

    public function test_exam_intro_page_renders_successfully(): void
    {
        if (! $this->user) {
            $this->markTestSkipped('No user found in MySQL database.');
        }

        $response = $this->actingAs($this->user)
            ->get(route('thi-thu.gioi-thieu-de', 7));

        $response->assertStatus(200);
        $response->assertSee('Part 1');
    }

    public function test_can_start_part1_practice_with_six_photograph_questions(): void
    {
        if (! $this->user) {
            $this->markTestSkipped('No user found in MySQL database.');
        }

        $response = $this->actingAs($this->user)
            ->post(route('thi-thu.bat-dau', 7), [
                'exam_mode' => 'selected_parts',
                'exam_parts' => ['PART1'],
            ]);

        $response->assertStatus(302);

        $attempt = TestAttempt::where('user_id', $this->user->user_id)
            ->where('test_form_id', 7)
            ->latest('attempt_id')
            ->first();

        $this->assertNotNull($attempt);
        $this->assertSame('IN_PROGRESS', $attempt->status);

        // Verify attempt has exactly 6 questions
        $this->assertCount(6, $attempt->responses);
    }

    public function test_part1_exam_interface_contains_photo_audio_and_circle_options(): void
    {
        if (! $this->user) {
            $this->markTestSkipped('No user found in MySQL database.');
        }

        $this->actingAs($this->user)
            ->post(route('thi-thu.bat-dau', 7), [
                'exam_mode' => 'selected_parts',
                'exam_parts' => ['PART1'],
            ]);

        $attempt = TestAttempt::where('user_id', $this->user->user_id)
            ->where('test_form_id', 7)
            ->latest('attempt_id')
            ->first();

        $response = $this->actingAs($this->user)
            ->get(route('thi-thu.giao-dien-thi', $attempt->attempt_id));

        $response->assertStatus(200);
        $response->assertSee('circle-only-mode');
        $response->assertSee('palettePopover');
        $response->assertSee('audioPlayerBar');
        $response->assertSee('part1_q1.png', false);
        $response->assertSee('part1_q2.jpg', false);
    }
}

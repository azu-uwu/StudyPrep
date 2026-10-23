<?php

namespace Tests\Feature;

use App\Services\TextToSpeechService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TextToSpeechTest extends TestCase
{
    public function test_tts_service_generates_mp3_file(): void
    {
        $service = new TextToSpeechService();
        $sampleText = "Look at the picture marked No. 1 in your test book. She is eating in a picnic area.";

        $result = $service->generateAudio($sampleText, 1, 'test_part1_q1');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('audio_url', $result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertStringContainsString('/storage/audio/listening/part_1/', $result['audio_url']);
        $this->assertTrue(File::exists($result['file_path']));
        $this->assertGreaterThan(500, $result['file_size']);
    }

    public function test_api_generate_tts_endpoint(): void
    {
        $response = $this->postJson('/api/admin/questions/generate-tts', [
            'text' => 'Can you come to my jazz performance tonight?',
            'part_number' => 2,
            'custom_filename' => 'api_test_part2',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'part_number' => 2,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'audio_url',
            'file_path',
            'file_size',
            'duration_estimate',
        ]);

        $audioUrl = $response->json('audio_url');
        $this->assertStringContainsString('/storage/audio/listening/part_2/', $audioUrl);
    }

    public function test_api_generate_tts_validation_error_when_no_text(): void
    {
        $response = $this->postJson('/api/admin/questions/generate-tts', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}

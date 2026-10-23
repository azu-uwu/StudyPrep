<?php

namespace Tests\Feature;

use Tests\TestCase;

class SkillPracticeLayoutTest extends TestCase
{
    public function test_reading_skill_route_returns_ok_and_contains_inpage_setup(): void
    {
        $response = $this->get('/luyen-tap/doc');
        $response->assertStatus(200);
        $response->assertSee('Luyện theo Kỹ năng');
        $response->assertSee('Part 5: Hoàn thành câu');
        $response->assertSee('sp-setup-inpage');
        $response->assertDontSee('sp-setup-modal-backdrop');
    }

    public function test_listening_skill_route_returns_ok_and_contains_inpage_setup(): void
    {
        $response = $this->get('/luyen-tap/nghe');
        $response->assertStatus(200);
        $response->assertSee('Luyện theo Kỹ năng');
        $response->assertSee('Part 1: Mô tả hình');
        $response->assertSee('sp-setup-inpage');
        $response->assertDontSee('sp-setup-modal-backdrop');
    }

    public function test_mo_ta_hinh_redirects_to_nghe_part1(): void
    {
        $response = $this->get('/luyen-tap/nghe/mo-ta-hinh');
        $response->assertRedirect('/luyen-tap/nghe?part=1');
    }
}

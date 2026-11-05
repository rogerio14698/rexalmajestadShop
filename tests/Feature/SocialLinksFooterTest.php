<?php

namespace Tests\Feature;

use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_social_links_are_rendered_in_footer(): void
    {
        SocialLink::query()->create([
            'platform' => 'instagram',
            'name' => 'Instagram',
            'url' => 'https://instagram.com/rexalmajestad',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SocialLink::query()->create([
            'platform' => 'tiktok',
            'name' => 'TikTok',
            'url' => 'https://tiktok.com/@rexalmajestad',
            'sort_order' => 2,
            'is_active' => false, // Oculto
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('https://instagram.com/rexalmajestad')
            ->assertSee('Instagram')
            ->assertDontSee('https://tiktok.com/@rexalmajestad');
    }
}

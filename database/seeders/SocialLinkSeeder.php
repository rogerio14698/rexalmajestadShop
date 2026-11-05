<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'platform' => 'instagram',
                'name' => 'Instagram',
                'url' => 'https://www.instagram.com/rexalmajestad',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'platform' => 'tiktok',
                'name' => 'TikTok',
                'url' => 'https://www.tiktok.com/@rexalmajestad',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($links as $link) {
            SocialLink::query()->updateOrCreate(
                ['url' => $link['url']],
                $link,
            );
        }
    }
}

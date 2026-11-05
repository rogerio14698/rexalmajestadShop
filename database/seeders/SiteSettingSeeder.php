<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::query()->firstOrCreate(
            ['key' => 'hero_shop'],
            [
                'label' => 'Fondo / Hero de Tienda',
                'group' => 'banners',
                'value' => null,
            ]
        );

        SiteSetting::query()->firstOrCreate(
            ['key' => 'hero_exhibitions'],
            [
                'label' => 'Fondo / Hero de Exposiciones',
                'group' => 'banners',
                'value' => null,
            ]
        );
    }
}

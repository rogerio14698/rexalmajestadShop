<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_verified_administrators_can_open_the_filament_panel(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_admin_resource_forms_render_with_localized_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/artworks/create')
            ->assertOk()
            ->assertSee('Título')
            ->assertSee('Categoría');

        $this->actingAs($admin)
            ->get('/admin/shipping-zones')
            ->assertOk()
            ->assertSee('Zonas de envío');
    }
}

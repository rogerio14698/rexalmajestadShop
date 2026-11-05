<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_strong_password_and_sends_verification(): void
    {
        Notification::fake();

        $this->get(route('login'))->assertOk();
        $this->post(route('register.store'), [
            'name' => 'Cliente',
            'email' => 'cliente@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->post(route('register.store'), [
            'name' => 'Cliente',
            'email' => 'cliente@example.test',
            'password' => 'FuertePassword123',
            'password_confirmation' => 'FuertePassword123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'cliente@example.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_users_cannot_access_account_and_verified_users_can_export_data(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get(route('account.dashboard'))->assertRedirect(route('verification.notice'));

        $user->markEmailAsVerified();
        $response = $this->actingAs($user)->get(route('account.export'));
        $response->assertOk()->assertDownload('rex-almajestad-datos.json');
    }

    public function test_customer_cannot_modify_another_users_address(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $address = Address::query()->create([
            'user_id' => $owner->getKey(),
            'type' => 'shipping',
            'recipient' => 'Cliente',
            'line_1' => 'Calle 1',
            'city' => 'Madrid',
            'region_code' => 'MD',
            'postal_code' => '28001',
            'country_code' => 'ES',
        ]);
        $otherUser->markEmailAsVerified();

        $this->actingAs($otherUser)
            ->patch(route('account.addresses.update', $address), [
                'type' => 'shipping',
                'recipient' => 'Cambio no autorizado',
                'line_1' => 'Calle 2',
                'city' => 'Madrid',
                'region_code' => 'MD',
                'postal_code' => '28001',
                'country_code' => 'ES',
            ])
            ->assertNotFound();

        $this->assertSame('Cliente', $address->fresh()->recipient);
    }
}

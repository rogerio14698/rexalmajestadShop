<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
        ]);
    }

    public function test_customers_are_redirected_to_google_from_the_main_domain(): void
    {
        $response = $this->get('/auth/google/redirect');

        $response->assertRedirect();
        $this->assertStringStartsWith(
            'https://accounts.google.com/o/oauth2/auth',
            $response->headers->get('Location'),
        );
        $this->assertStringContainsString(
            urlencode('/auth/google/callback'),
            $response->headers->get('Location'),
        );
    }

    public function test_admins_are_redirected_to_google_from_the_admin_subdomain(): void
    {
        $response = $this->get('https://admin.rexalmajestad.com/auth/google/redirect');

        $response->assertRedirect();
        $this->assertStringStartsWith(
            'https://accounts.google.com/o/oauth2/auth',
            $response->headers->get('Location'),
        );
        $this->assertStringContainsString(
            urlencode('https://admin.rexalmajestad.com/auth/google/callback'),
            $response->headers->get('Location'),
        );
    }
}

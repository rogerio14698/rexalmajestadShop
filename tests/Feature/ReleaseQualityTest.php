<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_persists_valid_message_and_rejects_honeypot(): void
    {
        $this->get(route('contact.create'))->assertOk()->assertSee(route('contact.store'));

        $this->post(route('contact.store'), [
            'name' => 'Persona',
            'email' => 'persona@example.test',
            'subject' => 'Consulta',
            'message' => 'Mensaje de prueba.',
            'website' => '',
        ])->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['email' => 'persona@example.test']);

        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.test',
            'message' => 'Spam.',
            'website' => 'https://spam.example',
        ])->assertSessionHasErrors('website');
        $this->assertSame(1, ContactMessage::query()->count());
    }

    public function test_legal_pages_are_not_indexed_until_published_and_escape_content(): void
    {
        $this->get(route('legal.page', ['slug' => 'privacidad']))
            ->assertOk()
            ->assertSee('noindex, nofollow')
            ->assertSee('pendiente de completar');

        LegalDocument::query()->create([
            'slug' => 'privacidad',
            'title' => 'Privacidad revisada',
            'body' => '<script>alert(1)</script>',
            'published_at' => now()->subMinute(),
        ]);

        $this->get(route('legal.page', ['slug' => 'privacidad']))
            ->assertOk()
            ->assertSee('Privacidad revisada')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_security_headers_are_present_on_public_responses(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }
}

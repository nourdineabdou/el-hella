<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_arabic(): void
    {
        $response = $this->get('/login');

        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_switching_locale_updates_session_and_view(): void
    {
        $this->get('/lang/fr');

        $response = $this->get('/login');

        $response->assertSee('lang="fr"', false);
        $response->assertSee('dir="ltr"', false);
    }

    public function test_switching_locale_persists_on_user_account(): void
    {
        $user = User::factory()->create(['language' => 'ar']);

        $this->actingAs($user)->get('/lang/fr');

        $this->assertSame('fr', $user->fresh()->language);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->get('/lang/es')->assertNotFound();
    }
}

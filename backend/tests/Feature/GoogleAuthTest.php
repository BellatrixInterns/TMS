<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_sign_up_creates_an_account_for_a_verified_gmail_address(): void
    {
        $this->fakeGoogleUser('Jane@Gmail.com', true);

        $response = $this->withSession(['google_auth_intent' => 'signup'])
            ->get('/auth/google/callback');

        $response->assertRedirectContains('/auth/callback#token=');
        $token = urldecode((string) parse_url($response->headers->get('Location'), PHP_URL_FRAGMENT));
        $plainTextToken = substr($token, strlen('token='));

        $this->withToken($plainTextToken)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.full_name', 'Jane Bellatrix');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@gmail.com',
            'google_id' => 'google-user-123',
        ]);
    }

    public function test_google_sign_in_links_an_existing_user_with_the_same_email(): void
    {
        User::query()->create([
            'username' => 'jane',
            'password_hash' => 'existing-password-hash',
            'full_name' => 'Jane Bellatrix',
            'email' => 'jane@gmail.com',
        ]);
        $this->fakeGoogleUser('jane@gmail.com', true);

        $this->withSession(['google_auth_intent' => 'signin'])
            ->get('/auth/google/callback')
            ->assertRedirectContains('/auth/callback#token=');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'username' => 'jane',
            'google_id' => 'google-user-123',
        ]);
    }

    public function test_google_sign_up_rejects_an_existing_account(): void
    {
        User::query()->create([
            'username' => 'jane',
            'password_hash' => 'existing-password-hash',
            'full_name' => 'Jane Bellatrix',
            'email' => 'jane@gmail.com',
        ]);
        $this->fakeGoogleUser('jane@gmail.com', true);

        $this->withSession(['google_auth_intent' => 'signup'])
            ->get('/auth/google/callback')
            ->assertRedirectContains('/login?error=account_already_exists');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_sign_in_rejects_a_user_without_an_account(): void
    {
        $this->fakeGoogleUser('jane@gmail.com', true);

        $this->withSession(['google_auth_intent' => 'signin'])
            ->get('/auth/google/callback')
            ->assertRedirectContains('/login?error=account_not_found');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_sign_in_rejects_an_email_outside_the_gmail_domain(): void
    {
        $this->fakeGoogleUser('jane@example.com', true);

        $this->get('/auth/google/callback')
            ->assertRedirectContains('/login?error=company_account_required');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_sign_in_rejects_an_unverified_gmail_address(): void
    {
        $this->fakeGoogleUser('jane@gmail.com', false);

        $this->get('/auth/google/callback')
            ->assertRedirectContains('/login?error=company_account_required');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_sign_in_redirects_back_when_oauth_is_not_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get('/auth/google')
            ->assertRedirect('http://127.0.0.1:3000/login?error=google_not_configured');
    }

    private function fakeGoogleUser(string $email, bool $verified): void
    {
        $googleUser = GoogleUser::fake([
            'id' => 'google-user-123',
            'email' => $email,
            'name' => 'Jane Bellatrix',
        ])->setRaw(['verified_email' => $verified]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);
    }
}

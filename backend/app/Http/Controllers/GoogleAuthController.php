<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    private const ALLOWED_EMAIL_DOMAIN = 'gmail.com';

    public function redirect(): RedirectResponse|SymfonyRedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return $this->frontendRedirect('/login?error=google_not_configured');
        }

        $intent = request()->query('intent', 'signin');
        if (! in_array($intent, ['signin', 'signup'], true)) {
            $intent = 'signin';
        }
        session(['google_auth_intent' => $intent]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $intent = session()->pull('google_auth_intent', 'signin');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException|GuzzleException $exception) {
            Log::warning('Google sign-in could not be completed.', [
                'exception' => $exception::class,
                'reason' => $exception->getMessage(),
            ]);

            return $this->frontendRedirect('/login?error=google_sign_in_failed');
        }

        $email = strtolower((string) $googleUser->getEmail());
        $googleId = (string) $googleUser->getId();
        $verified = ($googleUser->user['verified_email'] ?? false) === true;
        $domain = substr(strrchr($email, '@') ?: '', 1);

        if (! $verified || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $domain !== self::ALLOWED_EMAIL_DOMAIN || $googleId === '') {
            return $this->frontendRedirect('/login?error=company_account_required');
        }

        $user = User::query()->where('google_id', $googleId)->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();
        }

        if ($intent === 'signup' && $user) {
            return $this->frontendRedirect('/login?error=account_already_exists');
        }

        if ($intent === 'signin' && ! $user) {
            return $this->frontendRedirect('/login?error=account_not_found');
        }

        if ($user && $user->google_id && $user->google_id !== $googleId) {
            return $this->frontendRedirect('/login?error=account_not_found');
        }

        if (! $user) {
            $user = new User([
                'username' => 'google_'.Str::lower(Str::random(32)),
                'email' => $email,
                'google_id' => $googleId,
                'password_hash' => Hash::make(Str::random(64)),
                'full_name' => Str::limit($googleUser->getName() ?: $email, 100, ''),
            ]);
        } else {
            $user->email = $email;
            $user->google_id = $googleId;
            if (! $user->full_name) {
                $user->full_name = Str::limit($googleUser->getName() ?: $email, 100, '');
            }
        }

        $user->save();

        $token = $user->createToken('spa')->plainTextToken;

        return $this->frontendRedirect('/auth/callback#token='.rawurlencode($token));
    }

    private function frontendRedirect(string $path): RedirectResponse
    {
        return redirect()->away(rtrim((string) config('app.frontend_url'), '/').$path);
    }
}

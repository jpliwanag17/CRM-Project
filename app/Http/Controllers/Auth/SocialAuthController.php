<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToProvider(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        $socialUser = Socialite::driver($provider)->user();
        $providerIdColumn = $provider.'_id';
        $user = User::where($providerIdColumn, $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                $providerIdColumn => $socialUser->getId(),
                'avatar' => $user->avatar ?: $socialUser->getAvatar(),
            ]);
        } else {
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'CRM User',
                'email' => $socialUser->getEmail(),
                $providerIdColumn => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'role' => 'agent',
                'email_verified_at' => now(),
            ]);
        }

        auth()->login($user, remember: true);

        return to_route('dashboard');
    }

    private function ensureProviderIsSupported(string $provider): void
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);
    }
}

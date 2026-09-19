<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_callback_links_an_existing_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'person@example.com', 'role' => 'agent']);
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-123');
        $socialUser->shouldReceive('getEmail')->andReturn($user->email);
        $socialUser->shouldReceive('getName')->andReturn('Person Example');
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($socialUser);

        $response = $this->get(route('auth.social.callback', 'google'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'google_id' => 'google-123']);
    }

    public function test_social_callback_creates_a_new_agent_user(): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('facebook-456');
        $socialUser->shouldReceive('getEmail')->andReturn('new@example.com');
        $socialUser->shouldReceive('getName')->andReturn('New User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        Socialite::shouldReceive('driver')->with('facebook')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($socialUser);

        $response = $this->get(route('auth.social.callback', 'facebook'));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'facebook_id' => 'facebook-456',
            'role' => 'agent',
        ]);
    }

    public function test_unknown_social_provider_is_rejected(): void
    {
        $this->get(route('auth.social.redirect', 'github'))->assertNotFound();
    }
}

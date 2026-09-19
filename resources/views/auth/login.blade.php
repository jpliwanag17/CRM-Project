<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 font-medium text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Log in') }}
            </button>
            @if (Route::has('password.request'))
                <a class="mt-2 block text-center text-xs text-gray-500 hover:text-gray-700" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>
    </form>

    <div class="mt-6 flex items-center gap-3 text-xs text-gray-400"><span class="h-px flex-1 bg-gray-200"></span><span>Or continue with</span><span class="h-px flex-1 bg-gray-200"></span></div>
    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('auth.social.redirect', 'google') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.23c0-.69-.06-1.36-.18-2H12v3.79h5.38a4.6 4.6 0 0 1-1.99 3.02v2.51h3.22c1.88-1.73 2.99-4.28 2.99-7.32Z"/><path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.45l-3.22-2.51c-.9.6-2.05.96-3.39.96-2.6 0-4.8-1.76-5.59-4.12H3.08v2.59A9.98 9.98 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.41 13.88A6 6 0 0 1 6.1 12c0-.65.11-1.29.31-1.88V7.53H3.08A10 10 0 0 0 2 12c0 1.61.39 3.13 1.08 4.47l3.33-2.59Z"/><path fill="#EA4335" d="M12 6c1.47 0 2.79.51 3.83 1.51l2.87-2.87C16.96 2.92 14.7 2 12 2a9.98 9.98 0 0 0-8.92 5.53l3.33 2.59C7.2 7.76 9.4 6 12 6Z"/></svg>
            Google
        </a>
        <a href="{{ route('auth.social.redirect', 'facebook') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12a12 12 0 1 0-13.88 11.86v-8.4H7.08V12h3.04V9.36c0-3 1.79-4.66 4.53-4.66 1.31 0 2.68.23 2.68.23v2.95h-1.51c-1.49 0-1.95.92-1.95 1.87V12h3.32l-.53 3.46h-2.79v8.4A12 12 0 0 0 24 12Z"/></svg>
            Facebook
        </a>
    </div>
</x-guest-layout>

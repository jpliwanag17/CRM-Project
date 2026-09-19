<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel CRM'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <aside class="group fixed inset-y-0 left-0 z-50 flex w-20 flex-col bg-slate-900 text-white shadow-lg transition-all duration-300 ease-in-out hover:w-64 hover:shadow-2xl">
        <div class="flex h-20 items-center border-b border-slate-800 px-4">
            <a href="{{ route('dashboard') }}" title="CRM System" class="flex min-w-0 w-full items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-500 text-lg font-bold text-white shadow-lg shadow-indigo-950/30">C</span>
                <span class="overflow-hidden whitespace-nowrap text-lg font-bold tracking-tight opacity-0 transition-opacity duration-300 group-hover:opacity-100">CRM System</span>
            </a>
        </div>
        <nav class="flex flex-1 flex-col gap-2 px-3 py-6">
            <a href="{{ route('dashboard') }}" title="Dashboard" class="group/nav flex h-12 items-center justify-center gap-3 overflow-hidden rounded-lg px-3 text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('dashboard') || request()->is('/') ? 'bg-indigo-600 text-white hover:bg-indigo-600' : '' }} group-hover:justify-start">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5zM13.75 3.75h6.5v6.5h-6.5zM3.75 13.75h6.5v6.5h-6.5zM13.75 13.75h6.5v6.5h-6.5z" /></svg>
                <span class="overflow-hidden whitespace-nowrap opacity-0 transition-opacity duration-300 group-hover:opacity-100">Dashboard</span>
            </a>
            <a href="{{ route('pipeline.index') }}" title="Pipeline" class="group/nav flex h-12 items-center justify-center gap-3 overflow-hidden rounded-lg px-3 text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('pipeline.*') ? 'bg-indigo-600 text-white hover:bg-indigo-600' : '' }} group-hover:justify-start">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="4" width="4.25" height="16" rx="1" /><rect x="9.875" y="4" width="4.25" height="10" rx="1" /><rect x="16.25" y="4" width="4.25" height="13" rx="1" /></svg>
                <span class="overflow-hidden whitespace-nowrap opacity-0 transition-opacity duration-300 group-hover:opacity-100">Pipeline</span>
            </a>
            <a href="{{ route('broadcast.index') }}" title="Email Campaigns" class="group/nav flex h-12 items-center justify-center gap-3 overflow-hidden rounded-lg px-3 text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('broadcast.*') ? 'bg-indigo-600 text-white hover:bg-indigo-600' : '' }} group-hover:justify-start">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="5" width="17" height="14" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7 7.5 6 7.5-6" /></svg>
                <span class="overflow-hidden whitespace-nowrap opacity-0 transition-opacity duration-300 group-hover:opacity-100">Email Campaigns</span>
            </a>
            <a href="{{ route('contacts.index') }}" title="Contacts" class="group/nav flex h-12 items-center justify-center gap-3 overflow-hidden rounded-lg px-3 text-slate-300 transition hover:bg-slate-800 hover:text-white {{ request()->routeIs('contacts.*') ? 'bg-indigo-600 text-white hover:bg-indigo-600' : '' }} group-hover:justify-start">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19.25v-1.5a3.75 3.75 0 0 0-3.75-3.75h-4.5A3.75 3.75 0 0 0 4 17.75v1.5M9.75 10.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM16.75 4.15a3.25 3.25 0 0 1 0 6.2M20 19.25v-1.5a3.75 3.75 0 0 0-2.75-3.61" /></svg>
                <span class="overflow-hidden whitespace-nowrap opacity-0 transition-opacity duration-300 group-hover:opacity-100">Contacts</span>
            </a>
        </nav>
        <div class="border-t border-slate-800 px-3 py-4">
            @auth
                <div class="flex items-center gap-3 px-1">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-700 text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div class="min-w-0 overflow-hidden whitespace-nowrap opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <span class="mt-1 inline-flex rounded-full bg-indigo-500/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-200">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" title="Log out" class="flex h-10 w-full items-center justify-center gap-3 rounded-lg px-3 text-sm font-semibold text-slate-300 transition hover:bg-rose-500/15 hover:text-rose-200 group-hover:justify-start">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6.75A1.75 1.75 0 0 0 5 7.75v8.5A1.75 1.75 0 0 0 6.75 18H10M14 8l4 4-4 4M18 12H9" /></svg>
                        <span class="overflow-hidden whitespace-nowrap opacity-0 transition-opacity duration-300 group-hover:opacity-100">Log out</span>
                    </button>
                </form>
            @endauth
        </div>
    </aside>
    <main class="ml-20 min-h-screen">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>

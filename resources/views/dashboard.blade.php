@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name', 'Laravel CRM'))

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Overview</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Dashboard</h1>
            <p class="mt-2 text-slate-600">Your customer relationships at a glance.</p>
        </div>
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['label' => 'Total Contacts', 'value' => $totalContacts, 'color' => 'indigo'], ['label' => 'Leads', 'value' => $leadCount, 'color' => 'sky'], ['label' => 'Customers', 'value' => $customerCount, 'color' => 'emerald'], ['label' => 'Active Contacts', 'value' => $activeCount, 'color' => 'amber']] as $metric)
                <article class="rounded-xl border border-slate-200 border-l-4 border-l-{{ $metric['color'] }}-500 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($metric['value']) }}</p>
                </article>
            @endforeach
        </section>
        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Contacts by status</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($statusCounts as $status => $count)
                        <div>
                            <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700">{{ $status }}</span><span class="text-slate-500">{{ $count }}</span></div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ $totalContacts ? ($count / $totalContacts) * 100 : 0 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-slate-950">Recent activities</h2><a href="{{ route('broadcast.index') }}" class="text-sm font-semibold text-indigo-600">Email campaigns</a></div>
                <div class="mt-5 divide-y divide-slate-100">
                    @forelse ($recentActivities as $activity)
                        <a href="{{ route('contacts.show', $activity->contact) }}" class="block py-3 first:pt-0 last:pb-0 hover:text-indigo-600"><p class="text-sm font-semibold">{{ $activity->contact->first_name }} {{ $activity->contact->last_name }} <span class="font-normal text-slate-500">{{ $activity->created_at->diffForHumans() }}</span></p><p class="mt-1 truncate text-sm text-slate-600">{{ $activity->body }}</p></a>
                    @empty
                        <p class="py-6 text-sm text-slate-500">No recent activities.</p>
                    @endforelse
                </div>
            </article>
        </section>
        <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-950">Recent contacts</h2><a href="{{ route('contacts.index') }}" class="text-sm font-semibold text-indigo-600">View all</a></div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Name</th><th class="px-6 py-4">Company</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">View</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($recentContacts as $contact)<tr><td class="px-6 py-4 font-medium">{{ $contact->first_name }} {{ $contact->last_name }}</td><td class="px-6 py-4 text-slate-600">{{ $contact->company?->name ?? 'Unassigned' }}</td><td class="px-6 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $contact->status }}</span></td><td class="px-6 py-4 text-right"><a href="{{ route('contacts.show', $contact) }}" class="font-semibold text-indigo-600">View</a></td></tr>@empty<tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No contacts yet.</td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
@endsection

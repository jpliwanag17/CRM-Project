@extends('layouts.app')

@section('title', 'Contacts | '.config('app.name', 'Laravel CRM'))

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">CRM directory</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Contacts</h1>
                <p class="mt-2 text-slate-600">Keep every customer relationship close at hand.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('contacts.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Export CSV</a>
                <a href="{{ route('contacts.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Create Contact</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('contacts.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="min-w-0 space-y-2">
                    <label for="search" class="block text-sm font-semibold leading-5 text-slate-700">Search contacts</label>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Name or email" class="block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="min-w-0 space-y-2">
                    <label for="status" class="block text-sm font-semibold leading-5 text-slate-700">Status</label>
                    <select id="status" name="status" class="block h-11 w-full appearance-auto rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All statuses</option>
                        @foreach (['Lead', 'Prospect', 'Customer', 'Inactive'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-0 space-y-2">
                    <label for="company_id" class="block text-sm font-semibold leading-5 text-slate-700">Company</label>
                    <select id="company_id" name="company_id" class="block h-11 w-full appearance-auto rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) request('company_id') === (string) $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-end gap-3">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Filter</button>
                <a href="{{ route('contacts.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg px-4 text-sm font-semibold text-slate-600 hover:bg-slate-100">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Name</th>
                            <th class="px-6 py-4 font-semibold">Company</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($contacts as $contact)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900"><a href="{{ route('contacts.show', $contact) }}" class="hover:text-indigo-600">{{ $contact->first_name }} {{ $contact->last_name }}</a></td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $contact->company?->name ?? 'Unassigned' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $contact->email }}</td>
                                <td class="whitespace-nowrap px-6 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $contact->status }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('contacts.edit', $contact) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Edit</a>
                                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="ml-4 inline" onsubmit="return confirm('Delete this contact?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No contacts yet. Add your first contact to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($contacts->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">{{ $contacts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
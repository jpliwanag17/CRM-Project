@extends('layouts.app')

@section('title', (isset($contact) ? 'Edit contact' : 'New contact').' | '.config('app.name', 'Laravel CRM'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('contacts.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Back to contacts</a>
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950">{{ isset($contact) ? 'Edit contact' : 'Add a contact' }}</h1>
            <p class="mt-2 text-sm text-slate-600">Store the details your team needs for every relationship.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">Please correct the following:</p>
                    <ul class="mt-2 list-inside list-disc"><li>{{ implode('</li><li>', $errors->all()) }}</li></ul>
                </div>
            @endif

            <form action="{{ isset($contact) ? route('contacts.update', $contact) : route('contacts.store') }}" method="POST" class="mt-8 space-y-6">
                @csrf
                @isset($contact) @method('PUT') @endisset
                <div class="grid gap-6 sm:grid-cols-2">
                    <div><label for="first_name" class="block text-sm font-semibold text-slate-700">First name</label><input id="first_name" name="first_name" value="{{ old('first_name', $contact->first_name ?? '') }}" required class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <div><label for="last_name" class="block text-sm font-semibold text-slate-700">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name', $contact->last_name ?? '') }}" required class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div><label for="email" class="block text-sm font-semibold text-slate-700">Email</label><input id="email" name="email" type="email" value="{{ old('email', $contact->email ?? '') }}" required class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <div><label for="phone" class="block text-sm font-semibold text-slate-700">Phone <span class="font-normal text-slate-400">(optional)</span></label><input id="phone" name="phone" type="tel" value="{{ old('phone', $contact->phone ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div><label for="company_id" class="block text-sm font-semibold text-slate-700">Company</label><select id="company_id" name="company_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">No company</option>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(old('company_id', $contact->company_id ?? '') == $company->id)>{{ $company->name }}</option>@endforeach</select></div>
                    <div><label for="status" class="block text-sm font-semibold text-slate-700">Status</label><select id="status" name="status" required class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">@foreach (['Lead', 'Prospect', 'Customer', 'Inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $contact->status ?? 'Lead') === $status)>{{ $status }}</option>@endforeach</select></div>
                </div>
                @if (auth()->user()->isAdmin())
                    <div><label for="assigned_to" class="block text-sm font-semibold text-slate-700">Assigned to</label><select id="assigned_to" name="assigned_to" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Unassigned</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) old('assigned_to', $contact->assigned_to ?? '') === (string) $user->id)>{{ $user->name }} ({{ ucfirst($user->role) }})</option>@endforeach</select></div>
                @endif
                <div class="grid gap-6 sm:grid-cols-2">
                    <div><label for="deal_value" class="block text-sm font-semibold text-slate-700">Deal Value (₱) <span class="font-normal text-slate-400">(optional)</span></label><input id="deal_value" name="deal_value" type="number" min="0" step="0.01" value="{{ old('deal_value', $contact->deal_value ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                    <div><label for="expected_close_date" class="block text-sm font-semibold text-slate-700">Expected Close Date <span class="font-normal text-slate-400">(optional)</span></label><input id="expected_close_date" name="expected_close_date" type="date" value="{{ old('expected_close_date', isset($contact) && $contact->expected_close_date ? $contact->expected_close_date->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
                </div>
                <div><label for="notes" class="block text-sm font-semibold text-slate-700">Notes <span class="font-normal text-slate-400">(optional)</span></label><textarea id="notes" name="notes" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $contact->notes ?? '') }}</textarea></div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6"><a href="{{ route('contacts.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a><button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">{{ isset($contact) ? 'Update contact' : 'Save contact' }}</button></div>
            </form>
        </div>
    </div>
@endsection
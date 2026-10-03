@extends('layouts.app')

@section('title', $contact->first_name.' '.$contact->last_name.' | '.config('app.name', 'Laravel CRM'))

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('contacts.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Back to Contacts</a>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-950">{{ $contact->first_name }} {{ $contact->last_name }}</h1>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $contact->status }}</span>
                    @if ($contact->company)
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">{{ $contact->company->name }}</span>
                    @endif
                </div>
                <p class="mt-2 text-slate-600">Contact details and interaction history</p>
            </div>
            <a href="{{ route('contacts.edit', $contact) }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Edit Contact</a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.5fr)]">
            <section class="h-fit rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Contact info</h2>
                <dl class="mt-6 space-y-5">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ $contact->first_name }} {{ $contact->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                        <dd class="mt-1 break-all text-sm text-slate-700">{{ $contact->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $contact->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Company</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $contact->company?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $contact->created_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
                <a href="{{ route('contacts.edit', $contact) }}" class="mt-8 inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit Contact</a>
            </section>

            <section>
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Schedule a task</h2>
                    <form action="{{ route('tasks.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                        <div>
                            <label for="task-title" class="block text-sm font-semibold text-slate-700">Task</label>
                            <input id="task-title" name="title" type="text" value="{{ old('title') }}" required maxlength="255" placeholder="Prepare follow-up call" class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="task-type" class="block text-sm font-semibold text-slate-700">Type</label>
                                <select id="task-type" name="type" required class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach (['follow-up' => 'Follow-up', 'call' => 'Call', 'meeting' => 'Meeting', 'email' => 'Email'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', 'follow-up') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="task-due-at" class="block text-sm font-semibold text-slate-700">Due</label>
                                <input id="task-due-at" name="due_at" type="datetime-local" value="{{ old('due_at', now()->addDay()->format('Y-m-d\TH:i')) }}" required class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        @foreach (['contact_id', 'title', 'type', 'due_at'] as $field)
                            @error($field) <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        @endforeach
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Schedule task</button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Pending tasks</h2>
                    <div class="mt-5 divide-y divide-slate-100">
                        @forelse ($contact->tasks as $task)
                            @php
                                $taskTypeColor = match ($task->type) {
                                    'call' => 'bg-emerald-100 text-emerald-700',
                                    'meeting' => 'bg-amber-100 text-amber-700',
                                    'email' => 'bg-sky-100 text-sky-700',
                                    default => 'bg-indigo-100 text-indigo-700',
                                };
                            @endphp
                            <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="pt-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="checkbox" aria-label="Complete {{ $task->title }}" onchange="this.form.submit()" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </form>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900">{{ $task->title }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span class="rounded-full px-2 py-1 font-semibold {{ $taskTypeColor }}">{{ ucfirst($task->type) }}</span>
                                        <time datetime="{{ $task->due_at->toIso8601String() }}">{{ $task->due_at->format('M j, Y g:i A') }}</time>
                                    </div>
                                </div>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </div>
                        @empty
                            <p class="py-6 text-sm text-slate-500">No pending tasks for this contact.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Add Activity / Note</h2>
                    <form action="{{ route('contacts.notes.store', $contact) }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="type" class="block text-sm font-semibold text-slate-700">Type</label>
                            <select id="type" name="type" required class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach (['call' => 'Call', 'meeting' => 'Meeting', 'email' => 'Email', 'note' => 'Note'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', 'note') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="body" class="block text-sm font-semibold text-slate-700">Details</label>
                            <textarea id="body" name="body" rows="4" required placeholder="Write an update about this contact..." class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body') }}</textarea>
                        </div>
                        @error('type') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        @error('body') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Add to timeline</button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Interaction timeline</h2>
                    @forelse ($contact->activities as $activity)
                        @php
                            $typeLabel = ucfirst($activity->type);
                            $typeColor = match ($activity->type) {
                                'call' => 'bg-emerald-100 text-emerald-700',
                                'meeting' => 'bg-amber-100 text-amber-700',
                                'email' => 'bg-sky-100 text-sky-700',
                                default => 'bg-indigo-100 text-indigo-700',
                            };
                        @endphp
                        <article class="relative mt-6 border-l-2 border-slate-200 pl-6 first:mt-5">
                            <span class="absolute -left-[0.55rem] top-0 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-indigo-500 ring-1 ring-indigo-200" aria-hidden="true"></span>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeColor }}">{{ $typeLabel }}</span>
                                <time datetime="{{ $activity->created_at->toIso8601String() }}" class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</time>
                            </div>
                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $activity->body }}</p>
                        </article>
                    @empty
                        <div class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                            <p class="text-sm font-semibold text-slate-700">No activity yet</p>
                            <p class="mt-1 text-sm text-slate-500">Add a note, call, meeting, or email to start the timeline.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection

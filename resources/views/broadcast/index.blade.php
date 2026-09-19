@extends('layouts.app')

@section('title', 'Email Broadcast | '.config('app.name', 'Laravel CRM'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Campaign workspace</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Email Broadcast / Campaigns</h1>
            <p class="mt-2 text-slate-600">Compose a focused message and keep reusable templates ready for your next outreach.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Please correct the following:</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)]">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Compose broadcast</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose an audience and write a personal, useful message.</p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">Step 1</span>
                </div>

                <form action="{{ route('broadcast.send') }}" method="POST" class="mt-8 space-y-6">
                    @csrf
                    <fieldset>
                        <legend class="text-sm font-semibold text-slate-700">Target audience</legend>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($statusOptions as $option)
                                @php
                                    $audienceLabel = $option === 'All' ? 'All Contacts' : 'Only '.$option.'s';
                                @endphp
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-3 py-3 transition has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50">
                                    <input type="radio" name="audience" value="{{ $option }}" @checked(old('audience', 'All') === $option) class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-slate-800">{{ $audienceLabel }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ number_format($audienceCounts[$option]) }} recipient{{ $audienceCounts[$option] === 1 ? '' : 's' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div>
                        <label for="template_id" class="block text-sm font-semibold text-slate-700">Load saved template <span class="font-normal text-slate-400">(optional)</span></label>
                        <select id="template_id" name="email_template_id" class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Start from scratch</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-semibold text-slate-700">Subject</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255" class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <label for="body" class="block text-sm font-semibold text-slate-700">Email body</label>
                                <button type="button" id="open-ai-modal" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">✨ Generate with AI</button>
                            </div>
                            <span class="hidden text-xs text-slate-400 sm:inline">Supports {name} and {company}</span>
                        </div>
                        <textarea id="body" name="body" rows="10" required placeholder="Write your message here..." class="mt-2 block min-h-[200px] w-full rounded-lg border-slate-300 px-3 py-2.5 leading-6 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body') }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" id="save-template-button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Save as Template</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Send Broadcast</button>
                    </div>
                </form>
            </section>

            <aside class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Recent Broadcast History</h2>
                            <p class="mt-1 text-sm text-slate-500">The latest queued deliveries.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $campaignLogs->count() }}</span>
                    </div>
                    <div class="mt-5 divide-y divide-slate-100">
                        @forelse ($campaignLogs as $log)
                            @php
                                $statusColor = match ($log->status) {
                                    'sent' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $log->recipient_name ?: $log->recipient_email }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ $log->subject }}</p>
                                    <time class="mt-1 block text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</time>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-1 text-[11px] font-semibold {{ $statusColor }}">{{ ucfirst($log->status) }}</span>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-500">No broadcast deliveries yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Saved templates</h2>
                            <p class="mt-1 text-sm text-slate-500">Click a template to load it.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $templates->count() }}</span>
                    </div>
                    <div class="mt-5 divide-y divide-slate-100">
                        @forelse ($templates as $template)
                            <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <button type="button" data-load-template="{{ $template->id }}" class="min-w-0 flex-1 text-left">
                                    <span class="block truncate text-sm font-semibold text-slate-800 hover:text-indigo-600">{{ $template->name }}</span>
                                    <span class="mt-1 block truncate text-xs text-slate-500">{{ $template->subject }}</span>
                                </button>
                                <form action="{{ route('broadcast.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Delete this template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete template" class="rounded-md p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600">&times;</button>
                                </form>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-500">No saved templates yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-6">
                    <h2 class="text-lg font-bold text-indigo-950">Spam prevention checklist</h2>
                    <ul class="mt-4 space-y-3 text-sm leading-5 text-indigo-900">
                        <li class="flex gap-2"><span class="text-indigo-500">&#10003;</span><span>Use a clear subject without all-caps wording.</span></li>
                        <li class="flex gap-2"><span class="text-indigo-500">&#10003;</span><span>Personalize with the recipient's name or company.</span></li>
                        <li class="flex gap-2"><span class="text-indigo-500">&#10003;</span><span>Keep the message useful and concise.</span></li>
                        <li class="flex gap-2"><span class="text-indigo-500">&#10003;</span><span>Include an unsubscribe link before sending to a real audience.</span></li>
                    </ul>
                </section>
            </aside>
        </div>
    </div>

    <div id="template-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 px-4" role="dialog" aria-modal="true" aria-labelledby="template-modal-title">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="template-modal-title" class="text-lg font-bold text-slate-950">Save as template</h2>
                    <p class="mt-1 text-sm text-slate-500">Give this message a memorable name.</p>
                </div>
                <button type="button" id="close-template-modal" class="text-2xl leading-none text-slate-400 hover:text-slate-700" aria-label="Close">&times;</button>
            </div>
            <label for="template-name" class="mt-6 block text-sm font-semibold text-slate-700">Template name</label>
            <input id="template-name" type="text" maxlength="255" class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p id="template-error" class="mt-2 hidden text-sm text-rose-600"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="cancel-template-modal" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="button" id="confirm-template-save" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Save template</button>
            </div>
        </div>
    </div>

    <div id="ai-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 px-4" role="dialog" aria-modal="true" aria-labelledby="ai-modal-title">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="ai-modal-title" class="text-lg font-bold text-slate-950">Generate an email draft</h2>
                    <p class="mt-1 text-sm text-slate-500">Gemini will create a clean B2B draft using your topic and tone.</p>
                </div>
                <button type="button" id="close-ai-modal" class="text-2xl leading-none text-slate-400 hover:text-slate-700" aria-label="Close">&times;</button>
            </div>
            <div class="mt-6 space-y-5">
                <div>
                    <label for="ai-topic" class="block text-sm font-semibold text-slate-700">What is this email about?</label>
                    <textarea id="ai-topic" rows="3" maxlength="500" placeholder="Follow-up after discovery call, offer free consult" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2.5 leading-6 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label for="ai-tone" class="block text-sm font-semibold text-slate-700">Tone</label>
                    <select id="ai-tone" class="mt-2 block h-11 w-full rounded-lg border-slate-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="Professional">Professional</option>
                        <option value="Friendly / Casual">Friendly / Casual</option>
                        <option value="Urgent / Direct">Urgent / Direct</option>
                        <option value="Promotional">Promotional</option>
                    </select>
                </div>
                <p id="ai-error" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-700"></p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="cancel-ai-modal" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="button" id="generate-ai-draft" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg id="ai-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path></svg>
                    <span id="generate-ai-label">Generate Draft</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const templates = {{ \Illuminate\Support\Js::from($templates->keyBy('id')->map(fn ($template) => ['subject' => $template->subject, 'body' => $template->body])) }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const templateSelect = document.getElementById('template_id');
        const subjectInput = document.getElementById('subject');
        const bodyInput = document.getElementById('body');
        const modal = document.getElementById('template-modal');
        const templateName = document.getElementById('template-name');
        const templateError = document.getElementById('template-error');
        const aiModal = document.getElementById('ai-modal');
        const aiTopic = document.getElementById('ai-topic');
        const aiTone = document.getElementById('ai-tone');
        const aiError = document.getElementById('ai-error');
        const aiButton = document.getElementById('generate-ai-draft');
        const aiSpinner = document.getElementById('ai-spinner');
        const aiLabel = document.getElementById('generate-ai-label');

        const loadTemplate = (templateId) => {
            const template = templates[templateId];

            if (!template) {
                return;
            }

            subjectInput.value = template.subject;
            bodyInput.value = template.body;
        };

        templateSelect.addEventListener('change', () => loadTemplate(templateSelect.value));
        document.querySelectorAll('[data-load-template]').forEach((button) => {
            button.addEventListener('click', () => {
                templateSelect.value = button.dataset.loadTemplate;
                loadTemplate(button.dataset.loadTemplate);
                subjectInput.focus();
            });
        });

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            templateName.value = '';
            templateError.classList.add('hidden');
        };

        document.getElementById('save-template-button').addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            templateName.focus();
        });
        document.getElementById('close-template-modal').addEventListener('click', closeModal);
        document.getElementById('cancel-template-modal').addEventListener('click', closeModal);

        document.getElementById('confirm-template-save').addEventListener('click', async () => {
            if (!templateName.value.trim() || !subjectInput.value.trim() || !bodyInput.value.trim()) {
                templateError.textContent = 'Enter a name and complete the subject and body first.';
                templateError.classList.remove('hidden');
                return;
            }

            const response = await fetch('{{ route('broadcast.templates.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name: templateName.value.trim(),
                    subject: subjectInput.value,
                    body: bodyInput.value,
                }),
            });

            if (!response.ok) {
                templateError.textContent = 'The template could not be saved.';
                templateError.classList.remove('hidden');
                return;
            }

            window.location.reload();
        });

        const closeAiModal = () => {
            aiModal.classList.add('hidden');
            aiModal.classList.remove('flex');
            aiError.classList.add('hidden');
        };

        document.getElementById('open-ai-modal').addEventListener('click', () => {
            aiModal.classList.remove('hidden');
            aiModal.classList.add('flex');
            aiTopic.focus();
        });
        document.getElementById('close-ai-modal').addEventListener('click', closeAiModal);
        document.getElementById('cancel-ai-modal').addEventListener('click', closeAiModal);

        aiButton.addEventListener('click', async () => {
            if (!aiTopic.value.trim()) {
                aiError.textContent = 'Describe the email you want to create first.';
                aiError.classList.remove('hidden');
                return;
            }

            aiButton.disabled = true;
            aiSpinner.classList.remove('hidden');
            aiLabel.textContent = 'Generating...';
            aiError.classList.add('hidden');

            try {
                const response = await fetch('{{ route('broadcast.ai.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        topic: aiTopic.value.trim(),
                        tone: aiTone.value,
                    }),
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.error || 'The AI draft could not be generated.');
                }

                subjectInput.value = result.subject;
                bodyInput.value = result.body;
                closeAiModal();
            } catch (error) {
                aiError.textContent = error.message;
                aiError.classList.remove('hidden');
            } finally {
                aiButton.disabled = false;
                aiSpinner.classList.add('hidden');
                aiLabel.textContent = 'Generate Draft';
            }
        });
    </script>
@endpush

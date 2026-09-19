@extends('layouts.app')

@section('title', 'Sales Pipeline | '.config('app.name', 'Laravel CRM'))

@section('content')
    @php
        $columnStyles = [
            'Lead' => [
                'label' => 'Leads',
                'header' => 'border-indigo-200 text-indigo-800',
                'dot' => 'bg-indigo-500',
            ],
            'Prospect' => [
                'label' => 'Prospects',
                'header' => 'border-sky-200 text-sky-800',
                'dot' => 'bg-sky-500',
            ],
            'Customer' => [
                'label' => 'Customers',
                'header' => 'border-emerald-200 text-emerald-800',
                'dot' => 'bg-emerald-500',
            ],
            'Inactive' => [
                'label' => 'Inactive',
                'header' => 'border-slate-200 text-slate-700',
                'dot' => 'bg-slate-500',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Sales workspace</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Sales Pipeline / Kanban</h1>
                <p class="mt-2 text-slate-600">Move contacts through each stage of the relationship.</p>
            </div>
            <a href="{{ route('contacts.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Create Contact</a>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            @foreach ($columns as $status => $column)
                @php($style = $columnStyles[$status])
                @php($contacts = $column['contacts'])
                <section class="flex min-h-0 max-h-[calc(100vh-220px)] min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-slate-50/70 p-3" aria-labelledby="{{ strtolower($status) }}-heading">
                    <div class="sticky top-0 z-10 flex shrink-0 items-center justify-between rounded-lg border bg-white px-3 py-3 {{ $style['header'] }}">
                        <h2 id="{{ strtolower($status) }}-heading" class="flex items-center gap-2 text-sm font-bold">
                            <span class="h-2.5 w-2.5 rounded-full {{ $style['dot'] }}" aria-hidden="true"></span>
                            {{ $style['label'] }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <span data-total-for="{{ $status }}" class="text-xs font-semibold">₱{{ number_format($column['total_value'], 2) }}</span>
                            <span data-count-for="{{ $status }}" class="flex h-6 min-w-6 items-center justify-center rounded-full bg-white/80 px-2 text-xs font-bold">{{ $contacts->count() }}</span>
                        </div>
                    </div>

                    <div data-status="{{ $status }}" class="pipeline-column mt-3 min-h-40 flex-1 space-y-3 overflow-y-auto overscroll-contain rounded-lg p-2 scroll-smooth transition-colors" aria-label="{{ $style['label'] }} contacts">
                        @foreach ($contacts as $contact)
                            <article draggable="true" data-contact-id="{{ $contact->id }}" class="pipeline-card cursor-grab rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md active:cursor-grabbing">
                                <div class="flex items-start justify-between gap-3">
                                    <a href="{{ route('contacts.show', $contact) }}" class="min-w-0 truncate font-semibold text-slate-900 hover:text-indigo-600">{{ $contact->first_name }} {{ $contact->last_name }}</a>
                                    <span class="shrink-0 rounded-full bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700" title="Activities">{{ $contact->activities_count }} activities</span>
                                </div>
                                <p class="mt-3 truncate text-sm text-slate-600">{{ $contact->company?->name ?? 'Unassigned' }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $contact->email }}</p>
                                @if ($contact->deal_value !== null || $contact->expected_close_date)
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        @if ($contact->deal_value !== null)
                                            <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700">₱{{ number_format((float) $contact->deal_value, 2) }}</span>
                                        @endif
                                        @if ($contact->expected_close_date)
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">Close {{ $contact->expected_close_date->format('M j, Y') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const statusUrlTemplate = @json(route('contacts.updateStatus', ['contact' => '__CONTACT__']));
        let draggedCard = null;
        let originColumn = null;

        const updateColumnCounts = () => {
            document.querySelectorAll('[data-count-for]').forEach((count) => {
                const column = document.querySelector(`[data-status="${count.dataset.countFor}"]`);
                count.textContent = column.querySelectorAll('[data-contact-id]').length;
            });
        };

        document.querySelectorAll('.pipeline-card').forEach((card) => {
            card.addEventListener('dragstart', () => {
                draggedCard = card;
                originColumn = card.parentElement;
                card.classList.add('opacity-50');
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50');
                document.querySelectorAll('.pipeline-column').forEach((column) => column.classList.remove('bg-indigo-50'));
                draggedCard = null;
                originColumn = null;
            });
        });

        document.querySelectorAll('.pipeline-column').forEach((column) => {
            column.addEventListener('dragover', (event) => {
                event.preventDefault();
                column.classList.add('bg-indigo-50');
            });

            column.addEventListener('dragleave', () => {
                column.classList.remove('bg-indigo-50');
            });

            column.addEventListener('drop', async (event) => {
                event.preventDefault();
                column.classList.remove('bg-indigo-50');

                if (!draggedCard || originColumn === column) {
                    return;
                }

                const previousColumn = originColumn;
                const contactId = draggedCard.dataset.contactId;
                const nextStatus = column.dataset.status;
                column.appendChild(draggedCard);
                updateColumnCounts();

                try {
                    const response = await fetch(statusUrlTemplate.replace('__CONTACT__', contactId), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ status: nextStatus }),
                    });

                    if (!response.ok) {
                        throw new Error('Status update failed');
                    }
                    const result = await response.json();
                    Object.entries(result.column_totals || {}).forEach(([status, total]) => {
                        const totalElement = document.querySelector(`[data-total-for="${status}"]`);
                        if (totalElement) {
                            totalElement.textContent = `₱${Number(total).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        }
                    });
                } catch (error) {
                    previousColumn.appendChild(draggedCard);
                    updateColumnCounts();
                    window.alert('The contact status could not be updated. Please try again.');
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .pipeline-column {
            scrollbar-color: #cbd5e1 transparent;
            scrollbar-width: thin;
        }

        .pipeline-column::-webkit-scrollbar {
            width: 6px;
        }

        .pipeline-column::-webkit-scrollbar-track {
            background: transparent;
        }

        .pipeline-column::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        .pipeline-column::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

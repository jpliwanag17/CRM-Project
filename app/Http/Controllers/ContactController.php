<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $contacts = $this->filteredQuery($request)
            ->with(['company', 'assignee'])
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $companies = Company::orderBy('name')->get();

        return view('contacts.index', compact('contacts', 'companies'));
    }

    /**
     * Export the filtered contacts as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () use ($request): void {
            $output = fopen('php://output', 'wb');

            fputcsv($output, ['First name', 'Last name', 'Email', 'Phone', 'Company', 'Status', 'Notes']);

            foreach ($this->filteredQuery($request)->with('company')->lazy(500) as $contact) {
                fputcsv($output, [
                    $contact->first_name,
                    $contact->last_name,
                    $contact->email,
                    $contact->phone,
                    $contact->company?->name,
                    $contact->status,
                    $contact->notes,
                ]);
            }

            fclose($output);
        }, 'contacts.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contacts.csv"',
        ]);
    }

    /**
     * Display the contact details and interaction timeline.
     */
    public function show(Contact $contact): View
    {
        $this->ensureCanAccess($contact);
        $contact->load(['company', 'activities']);

        return view('contacts.show', compact('contact'));
    }

    /**
     * Store a new interaction note for a contact.
     */
    public function storeNote(Request $request, Contact $contact): RedirectResponse
    {
        $this->ensureCanAccess($contact);
        $validated = $request->validate([
            'type' => ['required', Rule::in(['call', 'meeting', 'email', 'note'])],
            'body' => ['required', 'string'],
        ]);

        $contact->activities()->create($validated);

        return to_route('contacts.show', $contact)->with('success', 'Activity was added.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $users = auth()->user()->isAdmin() ? User::orderBy('name')->get() : collect();

        return view('contacts.create', compact('companies', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['assigned_to'] = auth()->user()->isAdmin() ? ($data['assigned_to'] ?? null) : auth()->id();
        $contact = Contact::create($data);

        return to_route('contacts.index')->with('success', "Contact {$contact->first_name} {$contact->last_name} was created.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        $this->ensureCanAccess($contact);
        $companies = Company::orderBy('name')->get();
        $users = auth()->user()->isAdmin() ? User::orderBy('name')->get() : collect();

        return view('contacts.create', compact('contact', 'companies', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $this->ensureCanAccess($contact);
        $data = $this->validatedData($request, $contact);
        if (! auth()->user()->isAdmin()) {
            unset($data['assigned_to']);
        }
        $contact->update($data);

        return to_route('contacts.index')->with('success', 'Contact was updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $this->ensureCanAccess($contact);
        $contact->delete();

        return to_route('contacts.index')->with('success', 'Contact was deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Contact $contact = null): array
    {
        return $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contacts', 'email')->ignore($contact),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Lead', 'Prospect', 'Customer', 'Inactive'])],
            'deal_value' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'expected_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $companyId = $request->integer('company_id');

        $query = Contact::query()
            ->when(auth()->user()->isAgent(), fn (Builder $query): Builder => $query->where('assigned_to', auth()->id()));

        return $query
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['Lead', 'Prospect', 'Customer', 'Inactive'], true),
                fn (Builder $query): Builder => $query->where('status', $status),
            )
            ->when($companyId > 0, fn (Builder $query): Builder => $query->where('company_id', $companyId));
    }

    private function ensureCanAccess(Contact $contact): void
    {
        abort_unless(auth()->user()->isAdmin() || $contact->assigned_to === auth()->id(), 403);
    }
}

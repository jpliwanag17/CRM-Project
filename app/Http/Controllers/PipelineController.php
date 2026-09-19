<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PipelineController extends Controller
{
    public function index(): View
    {
        $contactsByStatus = $this->accessibleContacts()
            ->with('company')
            ->withCount('activities')
            ->latest()
            ->get()
            ->groupBy('status');

        $columns = collect(['Lead', 'Prospect', 'Customer', 'Inactive'])
            ->mapWithKeys(function (string $status) use ($contactsByStatus): array {
                $contacts = $contactsByStatus->get($status, collect());

                return [$status => [
                    'contacts' => $contacts,
                    'total_value' => (float) $contacts->sum('deal_value'),
                ]];
            });

        return view('pipeline.index', compact('columns'));
    }

    public function updateStatus(Request $request, Contact $contact): JsonResponse
    {
        abort_unless(auth()->user()->isAdmin() || $contact->assigned_to === auth()->id(), 403);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Lead', 'Prospect', 'Customer', 'Inactive'])],
        ]);

        $contact->update($validated);

        $columnTotals = $this->accessibleContacts()
            ->selectRaw('status, COALESCE(SUM(deal_value), 0) as total_value')
            ->groupBy('status')
            ->pluck('total_value', 'status')
            ->map(fn ($value): float => (float) $value);

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'column_totals' => collect(['Lead', 'Prospect', 'Customer', 'Inactive'])
                ->mapWithKeys(fn (string $status): array => [$status => $columnTotals->get($status, 0)])
                ->all(),
        ]);
    }

    private function accessibleContacts(): Builder
    {
        return Contact::query()
            ->when(auth()->user()->isAgent(), fn ($query) => $query->where('assigned_to', auth()->id()));
    }
}

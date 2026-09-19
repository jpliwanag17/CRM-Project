<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Note;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $contacts = Contact::query()
            ->when(auth()->user()->isAgent(), fn ($query) => $query->where('assigned_to', auth()->id()));
        $groupedStatusCounts = (clone $contacts)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = collect([
            'Lead' => (int) $groupedStatusCounts->get('Lead', 0),
            'Prospect' => (int) $groupedStatusCounts->get('Prospect', 0),
            'Customer' => (int) $groupedStatusCounts->get('Customer', 0),
            'Inactive' => (int) $groupedStatusCounts->get('Inactive', 0),
        ]);

        $recentContacts = (clone $contacts)->with('company')
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = Note::whereHas('contact', fn ($query) => $query->when(auth()->user()->isAgent(), fn ($query) => $query->where('assigned_to', auth()->id())))
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'totalContacts' => (clone $contacts)->count(),
            'totalCompanies' => Company::count(),
            'leadCount' => $statusCounts->get('Lead'),
            'customerCount' => $statusCounts->get('Customer'),
            'activeCount' => (clone $contacts)->whereIn('status', ['Lead', 'Prospect', 'Customer'])->count(),
            'statusCounts' => $statusCounts,
            'recentContacts' => $recentContacts,
            'recentActivities' => $recentActivities,
        ]);
    }
}

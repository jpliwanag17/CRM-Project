<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['call', 'meeting', 'follow-up', 'email'])],
            'due_at' => ['required', 'date'],
        ]);

        $contact = isset($validated['contact_id']) ? Contact::findOrFail($validated['contact_id']) : null;
        if ($contact) {
            abort_unless(auth()->user()->isAdmin() || $contact->assigned_to === auth()->id(), 403);
        }

        $validated['user_id'] = auth()->id();
        $task = Task::create($validated);

        return $contact
            ? to_route('contacts.show', $contact)->with('success', 'Task was scheduled.')
            : to_route('dashboard')->with('success', 'Task was scheduled.');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $this->ensureCanManage($task);
        $task->update(['is_completed' => ! $task->is_completed]);

        return back()->with('success', $task->is_completed ? 'Task completed.' : 'Task reopened.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->ensureCanManage($task);
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    private function ensureCanManage(Task $task): void
    {
        abort_unless(auth()->id() === $task->user_id, 403);
    }
}
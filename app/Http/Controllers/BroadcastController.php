<?php

namespace App\Http\Controllers;

use App\Jobs\SendBroadcastJob;
use App\Models\CampaignLog;
use App\Models\Contact;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BroadcastController extends Controller
{
    public function index(): View
    {
        $statusOptions = ['Lead', 'Prospect', 'Customer', 'All'];
        $audienceCounts = [
            'Lead' => Contact::where('status', 'Lead')->count(),
            'Prospect' => Contact::where('status', 'Prospect')->count(),
            'Customer' => Contact::where('status', 'Customer')->count(),
            'All' => Contact::count(),
        ];

        return view('broadcast.index', [
            'templates' => EmailTemplate::latest()->get(),
            'statusOptions' => $statusOptions,
            'audienceCounts' => $audienceCounts,
            'campaignLogs' => CampaignLog::latest()->take(15)->get(),
        ]);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        return response()->json([
            'template' => EmailTemplate::create($validated),
            'message' => 'Template saved.',
        ], 201);
    }

    public function generateAiEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:500'],
            'tone' => ['nullable', 'string', 'max:100'],
        ]);

        $apiKey = config('services.gemini.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'error' => 'GEMINI_API_KEY is not set in your .env file. Please add your free key to use AI.',
            ], 422);
        }

        $model = config('services.gemini.model', 'gemini-3.6-flash');
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent?key='.rawurlencode(trim($apiKey));
        $prompt = 'Act as an expert B2B copywriter. Write high-converting, non-spammy emails. Support the placeholders {name} and {company}. Output strictly a clean JSON object with exactly these keys: {"subject": "...", "body": "..."}. Do not include Markdown fences or any text outside the JSON object.';
        $prompt .= "\n\nWrite a B2B email about: {$validated['topic']}\nTone: ".($validated['tone'] ?? 'Professional').'.';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Gemini API Exception', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return response()->json([
                'error' => 'Gemini request failed: '.$exception->getMessage(),
            ], 502);
        }

        if ($response->failed()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Gemini API error (HTTP '.$response->status().'): '.$response->body(),
            ], $response->status() >= 400 && $response->status() < 600 ? $response->status() : 502);
        }

        $generatedText = $response->json('candidates.0.content.parts.0.text');
        $generatedText = is_string($generatedText) ? trim($generatedText) : '';
        $generatedText = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $generatedText) ?? $generatedText;
        $draft = json_decode(trim($generatedText), true);

        if (! is_array($draft) || ! is_string($draft['subject'] ?? null) || ! is_string($draft['body'] ?? null)) {
            Log::error('Gemini API Invalid Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Gemini returned an invalid draft: '.$response->body(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'subject' => $draft['subject'],
            'body' => $draft['body'],
        ]);
    }

    public function destroyTemplate(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->delete();

        return to_route('broadcast.index')->with('success', 'Template deleted.');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'audience' => ['required', Rule::in(['Lead', 'Prospect', 'Customer', 'All'])],
            'email_template_id' => ['nullable', 'integer', 'exists:email_templates,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $recipientQuery = Contact::query();

        if ($validated['audience'] !== 'All') {
            $recipientQuery->where('status', $validated['audience']);
        }

        $recipientCount = 0;

        foreach ($recipientQuery->cursor() as $contact) {
            $campaignLog = CampaignLog::create([
                'email_template_id' => $validated['email_template_id'] ?? null,
                'recipient_email' => $contact->email,
                'recipient_name' => $contact->first_name.' '.$contact->last_name,
                'subject' => $validated['subject'],
                'status' => 'queued',
            ]);

            SendBroadcastJob::dispatch(
                $campaignLog,
                $contact,
                $validated['subject'],
                $validated['body'],
            )->delay(now()->addSeconds($recipientCount));

            $recipientCount++;
        }

        Log::info('Email broadcast queued.', [
            'audience' => $validated['audience'],
            'subject' => $validated['subject'],
            'recipient_count' => $recipientCount,
        ]);

        return to_route('broadcast.index')->with('success', "Broadcast queued successfully for {$recipientCount} recipients. Run 'php artisan queue:work' to process.");
    }
}

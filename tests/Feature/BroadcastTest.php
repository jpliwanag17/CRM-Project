<?php

namespace Tests\Feature;

use App\Jobs\SendBroadcastJob;
use App\Mail\BroadcastMailable;
use App\Models\CampaignLog;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_broadcast_page_displays_audience_counts_and_templates(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Contact::create([
            'first_name' => 'Taylor',
            'last_name' => 'Lead',
            'email' => 'taylor@example.com',
            'status' => 'Lead',
        ]);
        EmailTemplate::create([
            'name' => 'Welcome',
            'subject' => 'Hello',
            'body' => 'Welcome, {name}.',
        ]);

        $response = $this->get(route('broadcast.index'));

        $response->assertOk()->assertSeeText('All Contacts')->assertSeeText('1 recipient')->assertSeeText('Welcome');
    }

    public function test_template_can_be_created_and_broadcast_can_be_queued(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Queue::fake();

        $templateResponse = $this->postJson(route('broadcast.templates.store'), [
            'name' => 'Follow-up',
            'subject' => 'Checking in',
            'body' => 'Hi {name}',
        ]);

        $templateResponse->assertCreated()->assertJsonPath('template.name', 'Follow-up');
        $this->assertDatabaseHas('email_templates', ['name' => 'Follow-up']);

        Contact::create([
            'first_name' => 'Morgan',
            'last_name' => 'Customer',
            'email' => 'morgan@example.com',
            'status' => 'Customer',
        ]);

        $sendResponse = $this->post(route('broadcast.send'), [
            'audience' => 'Customer',
            'subject' => 'A useful update',
            'body' => 'Hello {name}',
        ]);

        $sendResponse->assertRedirect(route('broadcast.index'))
            ->assertSessionHas('success', "Broadcast queued successfully for 1 recipients. Run 'php artisan queue:work' to process.");
        $this->assertDatabaseHas('campaign_logs', [
            'recipient_email' => 'morgan@example.com',
            'status' => 'queued',
        ]);
    }

    public function test_broadcast_dispatches_a_job_for_each_recipient(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Queue::fake();

        Contact::create([
            'first_name' => 'Morgan',
            'last_name' => 'Customer',
            'email' => 'morgan@example.com',
            'status' => 'Customer',
        ]);

        $this->post(route('broadcast.send'), [
            'audience' => 'Customer',
            'subject' => 'A useful update',
            'body' => 'Hello {name}',
        ])->assertRedirect();

        Queue::assertPushed(SendBroadcastJob::class, 1);
    }

    public function test_delivery_job_sends_a_personalized_email_and_marks_log_sent(): void
    {
        Mail::fake();
        $contact = Contact::create([
            'first_name' => 'Morgan',
            'last_name' => 'Customer',
            'email' => 'morgan@example.com',
            'status' => 'Customer',
        ]);
        $campaignLog = CampaignLog::create([
            'recipient_email' => $contact->email,
            'recipient_name' => 'Morgan Customer',
            'subject' => 'A useful update',
            'status' => 'queued',
        ]);

        (new SendBroadcastJob($campaignLog, $contact, 'A useful update', 'Hello {name} at {company}'))->handle();

        Mail::assertSent(BroadcastMailable::class, function (BroadcastMailable $mail) use ($contact): bool {
            return $mail->hasTo($contact->email)
                && $mail->bodyText === 'Hello Morgan Customer at your team';
        });
        $this->assertDatabaseHas('campaign_logs', [
            'id' => $campaignLog->id,
            'status' => 'sent',
        ]);
    }

    public function test_ai_generation_returns_a_clear_error_without_an_api_key(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        config(['services.gemini.api_key' => null]);

        $response = $this->postJson(route('broadcast.ai.generate'), [
            'topic' => 'Follow-up after a discovery call',
            'tone' => 'Professional',
        ]);

        $response->assertStatus(422)->assertJson([
            'error' => 'GEMINI_API_KEY is not set in your .env file. Please add your free key to use AI.',
        ]);
    }

    public function test_ai_generation_returns_the_parsed_gemini_draft(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"subject":"A helpful follow-up","body":"Hi {name},\\n\\nI wanted to follow up with {company}."}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $response = $this->postJson(route('broadcast.ai.generate'), [
            'topic' => 'Follow-up after a discovery call',
            'tone' => 'Professional',
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'subject' => 'A helpful follow-up',
            'body' => "Hi {name},\n\nI wanted to follow up with {company}.",
        ]);
    }

    public function test_ai_generation_exposes_upstream_error_details(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response(['error' => ['message' => 'Invalid API key']], 401),
        ]);

        $response = $this->postJson(route('broadcast.ai.generate'), [
            'topic' => 'A product update',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'Gemini API error (HTTP 401): {"error":{"message":"Invalid API key"}}');
    }
}

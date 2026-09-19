<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_groups_contacts_by_status(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $company = Company::create(['name' => 'Acme Corp']);
        Contact::create([
            'company_id' => $company->id,
            'first_name' => 'Alex',
            'last_name' => 'Lead',
            'email' => 'alex@example.com',
            'status' => 'Lead',
        ]);

        $response = $this->get(route('pipeline.index'));

        $response->assertOk()->assertSee('Alex Lead')->assertSee('Leads');
    }

    public function test_contact_status_can_be_updated_from_pipeline(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $contact = Contact::create([
            'first_name' => 'Jordan',
            'last_name' => 'Prospect',
            'email' => 'jordan@example.com',
            'status' => 'Prospect',
        ]);

        $response = $this->patchJson(route('contacts.updateStatus', $contact), [
            'status' => 'Customer',
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'Status updated',
            'column_totals' => [
                'Lead' => 0,
                'Prospect' => 0,
                'Customer' => 0,
                'Inactive' => 0,
            ],
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'Customer',
        ]);
    }

    public function test_agents_only_see_owned_contacts_and_new_contacts_are_assigned_to_them(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $otherAgent = User::factory()->create(['role' => 'agent']);
        Contact::create([
            'assigned_to' => $otherAgent->id,
            'first_name' => 'Hidden',
            'last_name' => 'Contact',
            'email' => 'hidden@example.com',
            'status' => 'Lead',
        ]);

        $this->actingAs($agent)->get(route('contacts.index'))
            ->assertOk()
            ->assertDontSeeText('Hidden Contact');

        $this->post(route('contacts.store'), [
            'first_name' => 'Owned',
            'last_name' => 'Contact',
            'email' => 'owned@example.com',
            'status' => 'Lead',
        ])->assertRedirect(route('contacts.index'));

        $this->assertDatabaseHas('contacts', [
            'email' => 'owned@example.com',
            'assigned_to' => $agent->id,
        ]);
    }

    public function test_admin_can_see_contacts_owned_by_any_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        Contact::create([
            'assigned_to' => $agent->id,
            'first_name' => 'Visible',
            'last_name' => 'Contact',
            'email' => 'visible@example.com',
            'status' => 'Lead',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('contacts.index'))
            ->assertOk()
            ->assertSeeText('Visible Contact');
    }
}

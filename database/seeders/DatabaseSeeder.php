<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'CRM Admin', 'password' => 'password', 'role' => 'admin', 'email_verified_at' => now()],
        );
        User::updateOrCreate(
            ['email' => 'agent@example.com'],
            ['name' => 'CRM Agent', 'password' => 'password', 'role' => 'agent', 'email_verified_at' => now()],
        );

        foreach ([
            [
                'name' => 'Introductory Pitch',
                'subject' => 'A quick introduction',
                'body' => "Hi {name},\n\nI wanted to introduce myself and learn more about how {company} is approaching its current goals.\n\nWould you be open to a quick conversation?",
            ],
            [
                'name' => 'Quick Check-in / Follow-up',
                'subject' => 'Checking in',
                'body' => "Hi {name},\n\nI am following up on our recent conversation. Is there anything else I can share to help with your next steps?\n\nBest,",
            ],
            [
                'name' => 'Special Promo',
                'subject' => 'A special offer for {company}',
                'body' => "Hi {name},\n\nI wanted to share a special opportunity that may be a good fit for {company}.\n\nLet me know if you would like the details.",
            ],
        ] as $template) {
            EmailTemplate::updateOrCreate(['name' => $template['name']], $template);
        }
    }
}

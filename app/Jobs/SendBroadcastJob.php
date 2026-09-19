<?php

namespace App\Jobs;

use App\Mail\BroadcastMailable;
use App\Models\CampaignLog;
use App\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendBroadcastJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public CampaignLog $campaignLog,
        public Contact $contact,
        public string $subjectLine,
        public string $bodyText,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->contact->email, $this->contact->first_name.' '.$this->contact->last_name)
            ->send(new BroadcastMailable($this->contact, $this->subjectLine, $this->bodyText));

        $this->campaignLog->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->campaignLog->update([
            'status' => 'failed',
            'error_message' => $exception?->getMessage() ?? 'The broadcast job failed.',
        ]);
    }
}

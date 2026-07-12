<?php

namespace App\Jobs;

use App\Models\Subscriber;
use App\Services\MailchimpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailchimpSubscribeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $subscriberId) {}

    public function handle(MailchimpService $mailchimpService): void
    {
        $subscriber = Subscriber::query()->find($this->subscriberId);

        if (! $subscriber || $subscriber->status !== 'active') {
            return;
        }

        $result = $mailchimpService->subscribe($subscriber->email);

        if (($result['success'] ?? false) && ! empty($result['member_id'])) {
            $subscriber->update(['mailchimp_id' => $result['member_id']]);
        }
    }
}

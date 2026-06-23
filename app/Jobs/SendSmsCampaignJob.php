<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
use App\Models\SmsMessage;
use App\Services\ClickSendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmsCampaignJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SmsCampaign $campaign,
        public $recipients,
    ) {}

    public function handle(ClickSendService $clicksend): void
    {
        $this->campaign->update(['status' => 'sending']);

        $messages = [];
        $smsMessages = [];

        foreach ($this->recipients as $user) {
            if (! $user->phone) {
                continue;
            }

            $smsMessage = SmsMessage::create([
                'sms_campaign_id' => $this->campaign->id,
                'user_id' => $user->id,
                'phone' => $user->phone,
                'status' => 'queued',
            ]);

            $messages[] = [
                'phone' => $user->phone,
                'message' => $this->campaign->message,
            ];

            $smsMessages[] = $smsMessage;
        }

        if (empty($messages)) {
            $this->campaign->update(['status' => 'failed']);

            return;
        }

        $result = $clicksend->send($messages);

        if (! $result['success']) {
            foreach ($smsMessages as $smsMessage) {
                $smsMessage->update([
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }

            $this->campaign->update(['status' => 'failed']);

            return;
        }

        foreach ($smsMessages as $index => $smsMessage) {
            $msgResult = $result['messages'][$index] ?? null;

            if ($msgResult) {
                $smsMessage->update([
                    'clicksend_message_id' => $msgResult['message_id'],
                    'status' => $msgResult['status'] === 'sent' ? 'sent' : 'failed',
                    'cost' => $msgResult['cost'],
                    'error' => $msgResult['error'],
                ]);
            }
        }

        $this->campaign->update([
            'status' => 'completed',
            'total_cost' => $result['total_cost'],
            'sent_at' => now(),
        ]);
    }
}

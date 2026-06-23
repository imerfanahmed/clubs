<?php

namespace App\Livewire\Admin;

use App\Jobs\SendSmsCampaignJob;
use App\Models\SmsCampaign;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('SMS Campaigns')]
class SmsCampaigns extends Component
{
    public string $message = '';

    public string $recipientFilter = 'all';

    public ?int $packageFilter = null;

    public int $recipientCount = 0;

    public function updatedRecipientFilter(): void
    {
        $this->previewCount();
    }

    public function updatedPackageFilter(): void
    {
        $this->previewCount();
    }

    public function previewCount(): void
    {
        $this->recipientCount = $this->getRecipientsQuery()->count();
    }

    public function send(): void
    {
        $this->validate([
            'message' => ['required', 'string', 'max:1600'],
        ]);

        $this->previewCount();

        if ($this->recipientCount === 0) {
            Flux::toast(variant: 'error', text: 'No recipients match the selected criteria.');

            return;
        }

        $campaign = SmsCampaign::create([
            'admin_id' => Auth::id(),
            'message' => $this->message,
            'recipient_count' => $this->recipientCount,
            'status' => 'queued',
        ]);

        $recipients = $this->getRecipientsQuery()->get();

        SendSmsCampaignJob::dispatch($campaign, $recipients);

        $this->reset('message');

        Flux::toast(variant: 'success', text: "Campaign queued for {$this->recipientCount} recipients.");
    }

    protected function getRecipientsQuery()
    {
        return match ($this->recipientFilter) {
            'active' => User::active(),
            'pending' => User::pending(),
            'package' => User::where('status', 'active')
                ->where('package_id', $this->packageFilter),
            default => User::whereIn('status', ['active', 'pending']),
        };
    }

    #[Computed]
    public function campaigns()
    {
        return SmsCampaign::with('admin')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.sms-campaigns')
            ->layout('layouts.app');
    }
}

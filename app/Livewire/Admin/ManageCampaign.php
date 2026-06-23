<?php

namespace App\Livewire\Admin;

use App\Mail\CampaignDonationConfirmed;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageCampaign extends Component
{
    public Campaign $campaign;

    public string $title = '';

    public string $summary = '';

    public string $description = '';

    public string $goalAmount = '';

    public string $status = '';

    public string $startsAt = '';

    public string $endsAt = '';

    // New pledge item form
    public string $pledgeName = '';

    public string $pledgeUnit = '';

    public string $pledgeTarget = '';

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->title = $campaign->title;
        $this->summary = $campaign->summary ?? '';
        $this->description = $campaign->description ?? '';
        $this->goalAmount = number_format($campaign->goal_amount / 100, 2, '.', '');
        $this->status = $campaign->status;
        $this->startsAt = $campaign->starts_at?->format('Y-m-d') ?? '';
        $this->endsAt = $campaign->ends_at?->format('Y-m-d') ?? '';
    }

    public function updateCampaign(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'goalAmount' => ['required', 'numeric', 'min:1'],
            'status' => ['required', 'in:draft,active,completed,closed'],
            'startsAt' => ['nullable', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
        ]);

        $this->campaign->update([
            'title' => $this->title,
            'summary' => $this->summary ?: null,
            'description' => $this->description ?: null,
            'goal_amount' => (int) round(((float) $this->goalAmount) * 100),
            'status' => $this->status,
            'starts_at' => $this->startsAt ?: null,
            'ends_at' => $this->endsAt ?: null,
        ]);

        Flux::toast(variant: 'success', text: 'Campaign updated.');
    }

    public function addPledgeItem(): void
    {
        $this->validate([
            'pledgeName' => ['required', 'string', 'max:255'],
            'pledgeUnit' => ['nullable', 'string', 'max:50'],
            'pledgeTarget' => ['required', 'integer', 'min:1'],
        ]);

        $this->campaign->pledgeItems()->create([
            'name' => $this->pledgeName,
            'unit' => $this->pledgeUnit ?: null,
            'target_quantity' => (int) $this->pledgeTarget,
            'sort_order' => (int) $this->campaign->pledgeItems()->max('sort_order') + 1,
        ]);

        $this->reset(['pledgeName', 'pledgeUnit', 'pledgeTarget']);

        Flux::toast(variant: 'success', text: 'Pledge item added.');
    }

    public function deletePledgeItem(int $id): void
    {
        $this->campaign->pledgeItems()->whereKey($id)->delete();

        Flux::toast(variant: 'success', text: 'Pledge item removed.');
    }

    public function approveDonation(int $id): void
    {
        $donation = $this->campaign->donations()->where('status', CampaignDonation::STATUS_PENDING)->find($id);

        if (! $donation) {
            return;
        }

        $donation->update([
            'status' => CampaignDonation::STATUS_COMPLETED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        if ($donation->recipientEmail()) {
            Mail::to($donation->recipientEmail())->queue(new CampaignDonationConfirmed($donation));
        }

        Flux::toast(variant: 'success', text: 'Donation approved.');
    }

    public function rejectDonation(int $id): void
    {
        $donation = $this->campaign->donations()->where('status', CampaignDonation::STATUS_PENDING)->find($id);

        if (! $donation) {
            return;
        }

        $donation->update([
            'status' => CampaignDonation::STATUS_REJECTED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        Flux::toast(variant: 'success', text: 'Donation rejected.');
    }

    #[Computed]
    public function pledgeItems()
    {
        return $this->campaign->pledgeItems()->get();
    }

    #[Computed]
    public function donations()
    {
        return $this->campaign->donations()
            ->with(['user', 'pledgeItem'])
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.manage-campaign')
            ->layout('layouts.app');
    }
}

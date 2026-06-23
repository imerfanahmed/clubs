<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Campaigns')]
class Campaigns extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $summary = '';

    public string $description = '';

    public string $goalAmount = '';

    public string $startsAt = '';

    public string $endsAt = '';

    public $image = null;

    public function createCampaign(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'goalAmount' => ['required', 'numeric', 'min:1'],
            'startsAt' => ['nullable', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        Campaign::create([
            'title' => $this->title,
            'summary' => $this->summary ?: null,
            'description' => $this->description ?: null,
            'image_path' => $this->image?->store('campaign-images', 'public'),
            'goal_amount' => (int) round(((float) $this->goalAmount) * 100),
            'currency' => 'GBP',
            'status' => Campaign::STATUS_DRAFT,
            'starts_at' => $this->startsAt ?: null,
            'ends_at' => $this->endsAt ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['title', 'summary', 'description', 'goalAmount', 'startsAt', 'endsAt', 'image']);

        Flux::toast(variant: 'success', text: 'Campaign created as a draft.');
    }

    public function setStatus(int $id, string $status): void
    {
        if (! in_array($status, [Campaign::STATUS_DRAFT, Campaign::STATUS_ACTIVE, Campaign::STATUS_COMPLETED, Campaign::STATUS_CLOSED], true)) {
            return;
        }

        Campaign::whereKey($id)->update(['status' => $status]);

        Flux::toast(variant: 'success', text: 'Campaign status updated.');
    }

    public function deleteCampaign(int $id): void
    {
        $campaign = Campaign::find($id);

        if (! $campaign) {
            return;
        }

        if ($campaign->image_path) {
            Storage::disk('public')->delete($campaign->image_path);
        }

        $campaign->delete();

        Flux::toast(variant: 'success', text: 'Campaign deleted.');
    }

    #[Computed]
    public function campaigns()
    {
        return Campaign::withCount('donations')->latest()->get();
    }

    public function render()
    {
        return view('livewire.admin.campaigns')
            ->layout('layouts.app');
    }
}

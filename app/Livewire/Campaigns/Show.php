<?php

namespace App\Livewire\Campaigns;

use App\Mail\CampaignDonationReceived;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Services\CampaignCheckoutService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    public string $contributionType = CampaignDonation::TYPE_MONEY;

    public string $amount = '';

    public string $paymentMethod = CampaignDonation::METHOD_CARD;

    public ?int $pledgeItemId = null;

    public string $pledgeQuantity = '';

    public string $donorName = '';

    public string $donorEmail = '';

    public string $donorPhone = '';

    public string $message = '';

    public bool $isAnonymous = false;

    public bool $isMember = false;

    public bool $justDonated = false;

    public function mount(Campaign $campaign, CampaignCheckoutService $checkout): void
    {
        $this->campaign = $campaign;

        if ($user = Auth::user()) {
            $this->isMember = true;
            $this->donorName = $user->name;
            $this->donorEmail = $user->email;
            $this->donorPhone = $user->phone ?? '';
        }

        if (request()->query('donation') === 'success') {
            if ($sessionId = request()->query('session_id')) {
                $checkout->completeFromSession($sessionId);
            }

            $this->justDonated = true;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'contributionType' => ['required', 'in:money,pledge'],
            'message' => ['nullable', 'string', 'max:1000'],
            'isAnonymous' => ['boolean'],
        ];

        if (! $this->isMember) {
            $rules['donorName'] = ['required', 'string', 'max:255'];
            $rules['donorEmail'] = ['required', 'email', 'max:255'];
            $rules['donorPhone'] = ['nullable', 'string', 'max:50'];
        }

        if ($this->contributionType === CampaignDonation::TYPE_MONEY) {
            $rules['amount'] = ['required', 'numeric', 'min:1'];
            $rules['paymentMethod'] = ['required', 'in:card,offline'];
        } else {
            $rules['pledgeItemId'] = ['required', 'integer', 'exists:campaign_pledge_items,id'];
            $rules['pledgeQuantity'] = ['required', 'integer', 'min:1'];
        }

        return $rules;
    }

    public function donate(CampaignCheckoutService $checkout)
    {
        if ($this->campaign->status !== Campaign::STATUS_ACTIVE) {
            Flux::toast(variant: 'danger', text: 'This campaign is not currently accepting donations.');

            return null;
        }

        if ($this->contributionType === CampaignDonation::TYPE_PLEDGE && ! $this->campaign->hasPledgeItems()) {
            Flux::toast(variant: 'danger', text: 'This campaign does not accept item pledges.');

            return null;
        }

        $validated = $this->validate();

        $isCardMoney = $this->contributionType === CampaignDonation::TYPE_MONEY
            && $this->paymentMethod === CampaignDonation::METHOD_CARD;

        $donation = $this->createDonation();

        if ($isCardMoney) {
            return $this->redirect($checkout->createSession($donation), navigate: false);
        }

        if ($donation->recipientEmail()) {
            Mail::to($donation->recipientEmail())->queue(new CampaignDonationReceived($donation));
        }

        $this->resetForm();
        $this->justDonated = true;

        Flux::toast(variant: 'success', text: 'Thank you! Your contribution has been recorded and is pending confirmation.');

        return null;
    }

    protected function createDonation(): CampaignDonation
    {
        $isMoney = $this->contributionType === CampaignDonation::TYPE_MONEY;

        return $this->campaign->donations()->create([
            'user_id' => Auth::id(),
            'type' => $this->contributionType,
            'amount' => $isMoney ? (int) round(((float) $this->amount) * 100) : null,
            'currency' => $this->campaign->currency,
            'pledge_item_id' => $isMoney ? null : $this->pledgeItemId,
            'pledge_quantity' => $isMoney ? null : (int) $this->pledgeQuantity,
            'payment_method' => $isMoney ? $this->paymentMethod : null,
            'status' => CampaignDonation::STATUS_PENDING,
            'donor_name' => $this->isMember ? null : $this->donorName,
            'donor_email' => $this->isMember ? null : $this->donorEmail,
            'donor_phone' => $this->isMember ? null : ($this->donorPhone ?: null),
            'message' => $this->message ?: null,
            'is_anonymous' => $this->isAnonymous,
        ]);
    }

    protected function resetForm(): void
    {
        $this->reset(['amount', 'pledgeItemId', 'pledgeQuantity', 'message', 'isAnonymous']);
        $this->contributionType = CampaignDonation::TYPE_MONEY;
        $this->paymentMethod = CampaignDonation::METHOD_CARD;
    }

    #[Computed]
    public function pledgeItems()
    {
        return $this->campaign->pledgeItems()->get();
    }

    #[Computed]
    public function supporters()
    {
        return $this->campaign->donations()
            ->where('status', CampaignDonation::STATUS_COMPLETED)
            ->where('is_anonymous', false)
            ->with(['user', 'pledgeItem'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.campaigns.show')
            ->layout('layouts.public');
    }
}

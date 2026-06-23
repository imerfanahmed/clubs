<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    {{-- Campaign details --}}
    <div class="lg:col-span-2">
        @if ($campaign->image_path)
            <img src="{{ asset('storage/'.$campaign->image_path) }}" alt="{{ $campaign->title }}"
                 class="mb-6 h-64 w-full rounded-2xl object-cover">
        @endif

        <flux:heading size="xl">{{ $campaign->title }}</flux:heading>

        @if ($campaign->summary)
            <p class="mt-2 text-lg text-zinc-600 dark:text-zinc-300">{{ $campaign->summary }}</p>
        @endif

        @if ($campaign->status !== \App\Models\Campaign::STATUS_ACTIVE)
            <flux:badge color="zinc" class="mt-3">{{ ucfirst($campaign->status) }}</flux:badge>
        @endif

        {{-- Money progress --}}
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <x-progress-bar
                :label="__('Raised')"
                :valueText="$campaign->raisedFormatted() . ' / ' . $campaign->goalFormatted()"
                :percent="$campaign->progressPercent()" />
        </div>

        {{-- Pledge item progress --}}
        @if ($this->pledgeItems->isNotEmpty())
            <div class="mt-4 space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">{{ __('Pledge Goals') }}</flux:heading>
                @foreach ($this->pledgeItems as $item)
                    <x-progress-bar
                        :label="$item->name"
                        :valueText="$item->achievedQuantity() . ' / ' . $item->target_quantity . ' ' . ($item->unit ?? '')"
                        :percent="$item->progressPercent()" />
                @endforeach
            </div>
        @endif

        @if ($campaign->description)
            <div class="mt-6 space-y-3 leading-relaxed">
                @foreach (preg_split('/\n+/', $campaign->description) as $paragraph)
                    <p class="text-zinc-700 dark:text-zinc-300">{{ $paragraph }}</p>
                @endforeach
            </div>
        @endif

        {{-- Supporters --}}
        @if ($this->supporters->isNotEmpty())
            <div class="mt-8">
                <flux:heading size="lg">{{ __('Recent Supporters') }}</flux:heading>
                <ul class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($this->supporters as $supporter)
                        <li class="flex items-center justify-between py-2">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $supporter->displayName() }}</span>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                @if ($supporter->type === \App\Models\CampaignDonation::TYPE_MONEY)
                                    {{ $supporter->amountFormatted() }}
                                @else
                                    {{ $supporter->pledge_quantity }} {{ $supporter->pledgeItem?->name }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Donate form --}}
    <div class="lg:col-span-1">
        <div class="sticky top-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            @if ($justDonated)
                <div class="rounded-xl bg-emerald-50 p-4 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">
                    <flux:heading size="lg">{{ __('JazakAllah Khair!') }}</flux:heading>
                    <p class="mt-1 text-sm">{{ __('Your contribution has been recorded.') }}</p>
                    <flux:button class="mt-3" wire:click="$set('justDonated', false)" size="sm">{{ __('Make another contribution') }}</flux:button>
                </div>
            @elseif ($campaign->status !== \App\Models\Campaign::STATUS_ACTIVE)
                <flux:heading size="lg">{{ __('Donations closed') }}</flux:heading>
                <p class="mt-1 text-sm text-zinc-500">{{ __('This campaign is not currently accepting donations.') }}</p>
            @else
                <flux:heading size="lg">{{ __('Contribute') }}</flux:heading>

                <form wire:submit="donate" class="mt-4 space-y-4">
                    {{-- Contribution type --}}
                    @if ($this->pledgeItems->isNotEmpty())
                        <flux:radio.group wire:model.live="contributionType" :label="__('Contribution type')" variant="segmented">
                            <flux:radio value="money" :label="__('Money')" />
                            <flux:radio value="pledge" :label="__('Pledge items')" />
                        </flux:radio.group>
                    @endif

                    @if ($contributionType === 'money')
                        <flux:input wire:model="amount" :label="__('Amount (£)')" type="number" step="0.01" min="1" required />

                        <flux:radio.group wire:model="paymentMethod" :label="__('Payment method')">
                            <flux:radio value="card" :label="__('Pay by card now')" />
                            <flux:radio value="offline" :label="__('Pay offline (cash / bank transfer)')" />
                        </flux:radio.group>
                    @else
                        <flux:select wire:model="pledgeItemId" :label="__('Item')" required>
                            <flux:select.option value="">{{ __('Select an item') }}</flux:select.option>
                            @foreach ($this->pledgeItems as $item)
                                <flux:select.option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit ?? __('units') }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="pledgeQuantity" :label="__('Quantity')" type="number" min="1" required />
                    @endif

                    @unless ($isMember)
                        <flux:input wire:model="donorName" :label="__('Your name')" type="text" required />
                        <flux:input wire:model="donorEmail" :label="__('Email')" type="email" required />
                        <flux:input wire:model="donorPhone" :label="__('Phone (optional)')" type="text" />
                    @else
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Donating as') }} <span class="font-medium">{{ $donorName }}</span>
                        </p>
                    @endunless

                    <flux:textarea wire:model="message" :label="__('Message (optional)')" rows="2" />

                    <flux:checkbox wire:model="isAnonymous" :label="__('Donate anonymously')" />

                    <flux:button type="submit" variant="primary" class="w-full">
                        @if ($contributionType === 'money' && $paymentMethod === 'card')
                            {{ __('Continue to payment') }}
                        @else
                            {{ __('Submit contribution') }}
                        @endif
                    </flux:button>

                    @if ($contributionType !== 'money' || $paymentMethod === 'offline')
                        <p class="text-xs text-zinc-400">{{ __('Your contribution will be confirmed by our team.') }}</p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</div>

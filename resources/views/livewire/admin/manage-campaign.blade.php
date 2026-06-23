<div>
    <div class="flex items-center justify-between gap-4">
        <flux:heading size="xl">{{ $campaign->title }}</flux:heading>
        <flux:button :href="route('campaigns.show', $campaign)" target="_blank" size="sm" icon="arrow-top-right-on-square">{{ __('View public page') }}</flux:button>
    </div>

    {{-- Totals --}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <x-progress-bar
                :label="__('Money raised')"
                :valueText="$campaign->raisedFormatted() . ' / ' . $campaign->goalFormatted()"
                :percent="$campaign->progressPercent()" />
        </div>
        @if ($this->pledgeItems->isNotEmpty())
            <div class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                @foreach ($this->pledgeItems as $item)
                    <x-progress-bar
                        :label="$item->name"
                        :valueText="$item->achievedQuantity() . ' / ' . $item->target_quantity . ' ' . ($item->unit ?? '')"
                        :percent="$item->progressPercent()" />
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
        {{-- Edit campaign --}}
        <div>
            <flux:heading size="lg">{{ __('Campaign Details') }}</flux:heading>
            <form wire:submit="updateCampaign" class="mt-4 space-y-4">
                <flux:input wire:model="title" :label="__('Title')" type="text" required />
                <flux:input wire:model="summary" :label="__('Summary')" type="text" />
                <flux:textarea wire:model="description" :label="__('Description')" rows="4" />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="goalAmount" :label="__('Goal (£)')" type="number" step="0.01" min="1" required />
                    <flux:select wire:model="status" :label="__('Status')">
                        <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                        <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="startsAt" :label="__('Starts At')" type="date" />
                    <flux:input wire:model="endsAt" :label="__('Ends At')" type="date" />
                </div>
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
            </form>
        </div>

        {{-- Pledge items --}}
        <div>
            <flux:heading size="lg">{{ __('Pledge Items') }}</flux:heading>
            <div class="mt-4 space-y-2">
                @forelse ($this->pledgeItems as $item)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                        <span class="text-sm">{{ $item->name }} — {{ $item->target_quantity }} {{ $item->unit }}</span>
                        <flux:button wire:click="deletePledgeItem({{ $item->id }})" variant="danger" size="xs" onclick="return confirm('Remove this pledge item?')">{{ __('Remove') }}</flux:button>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">{{ __('No pledge items. Add one below if this campaign accepts item pledges.') }}</p>
                @endforelse
            </div>

            <form wire:submit="addPledgeItem" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <flux:input wire:model="pledgeName" :label="__('Name')" type="text" placeholder="Bricks" required />
                <flux:input wire:model="pledgeUnit" :label="__('Unit')" type="text" placeholder="bricks" />
                <flux:input wire:model="pledgeTarget" :label="__('Target Qty')" type="number" min="1" required />
                <div class="sm:col-span-3">
                    <flux:button type="submit" size="sm">{{ __('Add Pledge Item') }}</flux:button>
                </div>
            </form>
        </div>
    </div>

    {{-- Donations --}}
    <div class="mt-10">
        <flux:heading size="lg">{{ __('Donations') }}</flux:heading>
        <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                        <th class="p-3 text-left">{{ __('Date') }}</th>
                        <th class="p-3 text-left">{{ __('Donor') }}</th>
                        <th class="p-3 text-left">{{ __('Contribution') }}</th>
                        <th class="p-3 text-left">{{ __('Method') }}</th>
                        <th class="p-3 text-left">{{ __('Status') }}</th>
                        <th class="p-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->donations as $donation)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3">{{ $donation->created_at->format('d M Y') }}</td>
                            <td class="p-3">
                                <div>{{ $donation->displayName() }}</div>
                                @if ($donation->recipientEmail())
                                    <div class="text-xs text-zinc-400">{{ $donation->recipientEmail() }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                @if ($donation->type === \App\Models\CampaignDonation::TYPE_MONEY)
                                    <span class="font-medium">{{ $donation->amountFormatted() }}</span>
                                @else
                                    {{ $donation->pledge_quantity }} {{ $donation->pledgeItem?->unit }} — {{ $donation->pledgeItem?->name }}
                                @endif
                            </td>
                            <td class="p-3">{{ $donation->payment_method ? ucfirst($donation->payment_method) : '—' }}</td>
                            <td class="p-3">
                                <flux:badge size="sm" :color="match($donation->status) {
                                    'completed' => 'green',
                                    'rejected' => 'red',
                                    default => 'amber',
                                }">{{ ucfirst($donation->status) }}</flux:badge>
                            </td>
                            <td class="p-3 text-right">
                                @if ($donation->status === \App\Models\CampaignDonation::STATUS_PENDING)
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button wire:click="approveDonation({{ $donation->id }})" variant="primary" size="xs">{{ __('Approve') }}</flux:button>
                                        <flux:button wire:click="rejectDonation({{ $donation->id }})" variant="danger" size="xs">{{ __('Reject') }}</flux:button>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3 text-center text-zinc-500" colspan="6">{{ __('No donations yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

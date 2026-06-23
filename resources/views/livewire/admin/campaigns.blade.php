<div>
    <flux:heading size="xl">{{ __('Campaigns') }}</flux:heading>

    <div class="mt-8">
        <flux:heading size="lg">{{ __('Create Campaign') }}</flux:heading>
        <form wire:submit="createCampaign" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:input wire:model="title" :label="__('Title')" type="text" required />
            <flux:input wire:model="goalAmount" :label="__('Goal Amount (£)')" type="number" step="0.01" min="1" required />
            <div class="md:col-span-2">
                <flux:input wire:model="summary" :label="__('Summary')" type="text" placeholder="{{ __('Short tagline shown on cards') }}" />
            </div>
            <div class="md:col-span-2">
                <flux:textarea wire:model="description" :label="__('Description')" rows="4" />
            </div>
            <flux:input wire:model="startsAt" :label="__('Starts At (optional)')" type="date" />
            <flux:input wire:model="endsAt" :label="__('Ends At (optional)')" type="date" />

            <div class="md:col-span-2">
                <flux:field>
                    <flux:label>{{ __('Cover Image (optional)') }}</flux:label>
                    <flux:input wire:model="image" type="file" accept="image/*" />
                    <flux:error name="image" />
                    <div wire:loading wire:target="image" class="mt-1 text-sm text-zinc-500">{{ __('Uploading...') }}</div>
                </flux:field>
            </div>

            <div class="md:col-span-2">
                <flux:button type="submit" variant="primary">{{ __('Create Campaign') }}</flux:button>
            </div>
        </form>
    </div>

    <div class="mt-8">
        <flux:heading size="lg">{{ __('All Campaigns') }}</flux:heading>
        <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                        <th class="p-3 text-left">{{ __('Title') }}</th>
                        <th class="p-3 text-left">{{ __('Status') }}</th>
                        <th class="p-3 text-left">{{ __('Progress') }}</th>
                        <th class="p-3 text-right">{{ __('Donations') }}</th>
                        <th class="p-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->campaigns as $campaign)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3">
                                <div class="font-medium">{{ $campaign->title }}</div>
                                <a href="{{ route('campaigns.show', $campaign) }}" target="_blank" class="text-xs text-blue-600 hover:underline dark:text-blue-400">/c/{{ $campaign->slug }}</a>
                            </td>
                            <td class="p-3">
                                <flux:badge size="sm" :color="match($campaign->status) {
                                    'active' => 'green',
                                    'completed' => 'blue',
                                    'closed' => 'red',
                                    default => 'zinc',
                                }">{{ ucfirst($campaign->status) }}</flux:badge>
                            </td>
                            <td class="p-3">
                                <div class="w-40">
                                    <x-progress-bar
                                        :valueText="$campaign->raisedFormatted() . ' / ' . $campaign->goalFormatted()"
                                        :percent="$campaign->progressPercent()" />
                                </div>
                            </td>
                            <td class="p-3 text-right">{{ $campaign->donations_count }}</td>
                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button :href="route('admin.campaigns.manage', $campaign)" wire:navigate size="xs">{{ __('Manage') }}</flux:button>
                                    @if ($campaign->status !== 'active')
                                        <flux:button wire:click="setStatus({{ $campaign->id }}, 'active')" size="xs" variant="primary">{{ __('Activate') }}</flux:button>
                                    @else
                                        <flux:button wire:click="setStatus({{ $campaign->id }}, 'closed')" size="xs">{{ __('Close') }}</flux:button>
                                    @endif
                                    <flux:button wire:click="deleteCampaign({{ $campaign->id }})" variant="danger" size="xs" onclick="return confirm('Delete this campaign and all its donations?')">{{ __('Delete') }}</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3 text-center text-zinc-500" colspan="5">{{ __('No campaigns yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

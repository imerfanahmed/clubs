<div>
    <flux:heading size="xl">{{ __('SMS Campaigns') }}</flux:heading>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('New Campaign') }}</flux:heading>

            <div class="mt-4 space-y-4">
                <flux:select wire:model.live="recipientFilter" :label="__('Recipients')">
                    <option value="all">{{ __('All Members') }}</option>
                    <option value="active">{{ __('Active Members') }}</option>
                    <option value="pending">{{ __('Pending Members') }}</option>
                    <option value="package">{{ __('By Package') }}</option>
                </flux:select>

                @if ($recipientFilter === 'package')
                    <flux:select wire:model.live="packageFilter" :label="__('Package')">
                        <option value="">{{ __('Select package...') }}</option>
                        @foreach (\App\Models\Package::where('is_active', true)->get() as $pkg)
                            <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                        @endforeach
                    </flux:select>
                @endif

                <flux:textarea wire:model="message" :label="__('Message')" rows="4" />
                <p class="text-xs text-zinc-500">{{ mb_strlen($this->message) }} / 1600 characters</p>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-500">{{ __('Recipients: :count', ['count' => $recipientCount]) }}</span>
                    <flux:button wire:click="send" variant="primary">
                        {{ __('Send Campaign') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Campaign History') }}</flux:heading>

            <div class="mt-4 space-y-3">
                @forelse ($this->campaigns as $campaign)
                    <div class="p-3 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium">{{ Str::limit($campaign->message, 60) }}</div>
                            <flux:badge :color="match($campaign->status) {
                                'completed' => 'emerald',
                                'sending' => 'blue',
                                'failed' => 'red',
                                default => 'gray',
                            }">{{ ucfirst($campaign->status) }}</flux:badge>
                        </div>
                        <div class="text-xs text-zinc-500 mt-1">
                            {{ $campaign->recipient_count }} recipients &middot; {{ $campaign->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">{{ __('No campaigns yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

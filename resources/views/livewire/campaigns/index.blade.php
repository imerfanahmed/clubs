<div>
    <flux:heading size="xl">{{ __('Fundraising Campaigns') }}</flux:heading>
    <p class="mt-2 text-zinc-600 dark:text-zinc-300">{{ __('Support our community by contributing to an active campaign.') }}</p>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
        @forelse ($this->campaigns as $campaign)
            <a href="{{ route('campaigns.show', $campaign) }}" wire:navigate
               class="block overflow-hidden rounded-2xl border border-zinc-200 bg-white transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                @if ($campaign->image_path)
                    <img src="{{ asset('storage/'.$campaign->image_path) }}" alt="{{ $campaign->title }}" class="h-40 w-full object-cover">
                @else
                    <div class="h-40 w-full bg-gradient-to-br from-emerald-900 to-emerald-700"></div>
                @endif
                <div class="p-5">
                    <flux:heading size="lg">{{ $campaign->title }}</flux:heading>
                    @if ($campaign->summary)
                        <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $campaign->summary }}</p>
                    @endif
                    <div class="mt-4">
                        <x-progress-bar
                            :valueText="$campaign->raisedFormatted() . ' / ' . $campaign->goalFormatted()"
                            :percent="$campaign->progressPercent()" />
                    </div>
                </div>
            </a>
        @empty
            <div class="sm:col-span-2 rounded-2xl border border-dashed border-zinc-300 p-10 text-center text-zinc-500 dark:border-zinc-700">
                {{ __('There are no active campaigns at the moment. Please check back soon.') }}
            </div>
        @endforelse
    </div>
</div>

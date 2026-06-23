<div>
    <flux:heading size="xl">{{ __('Pending Members') }}</flux:heading>

    <div class="mt-6 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800">
                    <th class="text-left p-3">{{ __('Name') }}</th>
                    <th class="text-left p-3">{{ __('Email') }}</th>
                    <th class="text-left p-3">{{ __('Package') }}</th>
                    <th class="text-left p-3">{{ __('Registered') }}</th>
                    <th class="text-right p-3">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->pendingUsers as $user)
                    <tr class="border-t border-neutral-200 dark:border-neutral-700">
                        <td class="p-3">
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->phone }}</div>
                        </td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">{{ $user->package?->name ?? '-' }}</td>
                        <td class="p-3">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="p-3 text-right space-x-2">
                            <flux:button wire:click="approve({{ $user->id }})" variant="primary" size="sm">
                                {{ __('Approve') }}
                            </flux:button>
                            <flux:button wire:click="confirmReject({{ $user->id }})" variant="danger" size="sm">
                                {{ __('Reject') }}
                            </flux:button>
                        </td>
                    </tr>
                    @if ($rejectingUserId === $user->id)
                        <tr class="bg-red-50 dark:bg-red-900/20">
                            <td colspan="5" class="p-3">
                                <div class="flex gap-2 items-start">
                                    <flux:textarea wire:model="rejectionReason" :label="__('Reason for rejection')" class="flex-1" rows="2" />
                                    <div class="flex gap-1 mt-6">
                                        <flux:button wire:click="reject" variant="primary" size="sm">
                                            {{ __('Confirm') }}
                                        </flux:button>
                                        <flux:button wire:click="cancelReject" size="sm">
                                            {{ __('Cancel') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr class="border-t border-neutral-200 dark:border-neutral-700">
                        <td class="p-3 text-center text-zinc-500" colspan="5">{{ __('No pending applications.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

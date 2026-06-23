<div>
    <flux:heading size="xl">{{ __('Members') }}</flux:heading>

    <div class="mt-4 flex gap-2">
        <flux:input wire:model.live="search" :placeholder="__('Search by name or email...')" class="max-w-sm" />
        <flux:select wire:model.live="statusFilter" class="max-w-[160px]">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="pending">{{ __('Pending') }}</option>
            <option value="suspended">{{ __('Suspended') }}</option>
            <option value="rejected">{{ __('Rejected') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
        </flux:select>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800">
                    <th class="text-left p-3">{{ __('Name') }}</th>
                    <th class="text-left p-3">{{ __('Email') }}</th>
                    <th class="text-left p-3">{{ __('Package') }}</th>
                    <th class="text-left p-3">{{ __('Status') }}</th>
                    <th class="text-left p-3">{{ __('Member Since') }}</th>
                    <th class="text-right p-3">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->members as $member)
                    <tr class="border-t border-neutral-200 dark:border-neutral-700">
                        <td class="p-3 font-medium">{{ $member->name }}</td>
                        <td class="p-3">{{ $member->email }}</td>
                        <td class="p-3">{{ $member->package?->name ?? '-' }}</td>
                        <td class="p-3">
                            <flux:badge :color="match($member->status) {
                                'active' => 'emerald',
                                'pending' => 'amber',
                                'suspended' => 'red',
                                'rejected' => 'red',
                                'cancelled' => 'gray',
                                default => 'gray',
                            }">{{ ucfirst($member->status) }}</flux:badge>
                        </td>
                        <td class="p-3">{{ $member->created_at->format('d M Y') }}</td>
                        <td class="p-3 text-right space-x-1">
                            @if ($member->status === 'active')
                                <flux:button wire:click="deactivate({{ $member->id }})" variant="danger" size="sm">
                                    {{ __('Deactivate') }}
                                </flux:button>
                            @elseif ($member->status === 'suspended')
                                <flux:button wire:click="reactivate({{ $member->id }})" variant="primary" size="sm">
                                    {{ __('Reactivate') }}
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="border-t border-neutral-200 dark:border-neutral-700">
                        <td class="p-3 text-center text-zinc-500" colspan="6">{{ __('No members found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

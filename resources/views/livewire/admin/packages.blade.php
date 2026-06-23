<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Packages') }}</flux:heading>
        <flux:button wire:click="create" variant="primary">
            {{ __('Add Package') }}
        </flux:button>
    </div>

    @if ($showForm)
        <div class="mt-6 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg">{{ $editingPackageId ? __('Edit Package') : __('New Package') }}</flux:heading>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name" :label="__('Name')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required />
                <div class="md:col-span-2">
                    <flux:textarea wire:model="description" :label="__('Description')" rows="2" />
                </div>
                <flux:input wire:model="price" :label="__('Price (pence)')" type="number" required />
                <flux:select wire:model="interval" :label="__('Interval')">
                    <option value="month">{{ __('Monthly') }}</option>
                    <option value="year">{{ __('Yearly') }}</option>
                </flux:select>
                <flux:input wire:model="sortOrder" :label="__('Sort Order')" type="number" />
                <flux:switch wire:model="isActive" :label="__('Active')" />
            </div>
            <div class="mt-4 flex gap-2">
                <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button wire:click="cancel">{{ __('Cancel') }}</flux:button>
            </div>
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800">
                    <th class="text-left p-3">{{ __('Name') }}</th>
                    <th class="text-left p-3">{{ __('Price') }}</th>
                    <th class="text-left p-3">{{ __('Interval') }}</th>
                    <th class="text-left p-3">{{ __('Stripe Product') }}</th>
                    <th class="text-left p-3">{{ __('Active') }}</th>
                    <th class="text-right p-3">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->packages as $package)
                    <tr class="border-t border-neutral-200 dark:border-neutral-700">
                        <td class="p-3 font-medium">{{ $package->name }}</td>
                        <td class="p-3">{{ $package->priceFormatted() }}</td>
                        <td class="p-3">{{ $package->interval }}</td>
                        <td class="p-3 text-xs">
                            @if ($package->stripe_price_id)
                                <code class="text-xs">{{ $package->stripe_price_id }}</code>
                            @else
                                <span class="text-amber-500">{{ __('Not synced') }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <flux:badge :color="$package->is_active ? 'emerald' : 'gray'">
                                {{ $package->is_active ? __('Yes') : __('No') }}
                            </flux:badge>
                        </td>
                        <td class="p-3 text-right">
                            <flux:button wire:click="edit({{ $package->id }})" size="sm">
                                {{ __('Edit') }}
                            </flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

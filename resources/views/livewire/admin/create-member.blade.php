<div>
    <flux:heading size="xl">{{ __('Create Member') }}</flux:heading>
    <flux:subheading>{{ __('Create a new member account directly from the admin panel.') }}</flux:subheading>

    <form wire:submit="create" class="mt-8 max-w-2xl space-y-6">
        <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">{{ __('Personal Details') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name" :label="__('Full Name')" type="text" required />
                <flux:input wire:model="email" :label="__('Email')" type="email" required />
                <flux:input wire:model="phone" :label="__('Phone')" type="tel" required placeholder="+44..." />
                <flux:input wire:model="password" :label="__('Password')" type="password" required viewable />
                <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required viewable />

                <flux:field>
                    <flux:label>{{ __('Package') }}</flux:label>
                    <flux:select wire:model="package_id" required>
                        <option value="">{{ __('Select a package...') }}</option>
                        @foreach ($this->packages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} ({{ $package->priceFormatted() }}/{{ $package->interval }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="package_id" />
                </flux:field>
            </div>
        </div>

        <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">{{ __('Address') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="line_1" :label="__('Address Line 1')" type="text" required />
                <flux:input wire:model="line_2" :label="__('Address Line 2')" type="text" />
                <flux:input wire:model="city" :label="__('City')" type="text" required />
                <flux:input wire:model="county" :label="__('County')" type="text" />
                <flux:input wire:model="postcode" :label="__('Postcode')" type="text" required />
                <flux:input wire:model="country" :label="__('Country')" type="text" required />
            </div>
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">
                {{ __('Create Member') }}
            </flux:button>
            <flux:button :href="route('admin.members')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>

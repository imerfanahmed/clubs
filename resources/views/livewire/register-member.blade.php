<div>
    <x-auth-header :title="__('Membership Registration')" :description="__('Step '.$step.' of 5')" />

    @if ($step === 1)
        <div class="flex flex-col gap-4">
            <flux:input wire:model="name" :label="__('Full Name')" type="text" required autofocus />
            <flux:input wire:model="email" :label="__('Email')" type="email" required />
            <flux:input wire:model="phone" :label="__('Phone')" type="tel" required placeholder="+44..." />
            <flux:input wire:model="password" :label="__('Password')" type="password" required viewable />
            <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required viewable />

            <flux:button wire:click="submitPersonal" variant="primary" class="w-full">
                {{ __('Next: Address') }}
            </flux:button>
        </div>

    @elseif ($step === 2)
        <div class="flex flex-col gap-4">
            <div class="flex gap-2">
                <flux:input wire:model="postcode" :label="__('Postcode')" class="flex-1" />
                <flux:button wire:click="lookupPostcode" variant="primary" class="mt-6">
                    {{ __('Lookup') }}
                </flux:button>
            </div>

            @if (count($addressOptions) > 0)
                <div class="space-y-2">
                    <flux:label>{{ __('Select an address') }}</flux:label>
                    @foreach ($addressOptions as $index => $address)
                        <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700"
                               wire:click="selectAddress({{ $index }})">
                            <flux:radio :checked="$selectedAddressIndex === $index" />
                            <div class="text-sm">
                                <div>{{ $address['line_1'] }}</div>
                                <div class="text-zinc-500">{{ $address['city'] }}, {{ $address['postcode'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif

            <flux:input wire:model="line_1" :label="__('Address Line 1')" type="text" required />
            <flux:input wire:model="line_2" :label="__('Address Line 2')" type="text" />
            <flux:input wire:model="city" :label="__('City')" type="text" required />
            <flux:input wire:model="county" :label="__('County')" type="text" />
            <flux:input wire:model="postcode" :label="__('Postcode')" type="text" required />

            <flux:button wire:click="submitAddress" variant="primary" class="w-full">
                {{ __('Next: Choose Package') }}
            </flux:button>
        </div>

    @elseif ($step === 3)
        <div class="grid gap-4">
            @foreach ($packages as $package)
                <label class="flex items-center gap-4 p-4 rounded-lg border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700"
                       wire:click="$set('package_id', {{ $package->id }})">
                    <flux:radio :checked="$package_id === $package->id" />
                    <div class="flex-1">
                        <div class="font-semibold">{{ $package->name }}</div>
                        <div class="text-sm text-zinc-500">{{ $package->description }}</div>
                    </div>
                    <div class="text-lg font-bold">{{ $package->priceFormatted() }}/{{ $package->interval }}</div>
                </label>
            @endforeach

            <flux:button wire:click="submitPackage" variant="primary" class="w-full">
                {{ __('Next: Payment') }}
            </flux:button>
        </div>

    @elseif ($step === 4)
        <div class="flex flex-col gap-4" wire:key="step-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Please enter your card details to save a payment method. You will not be charged until your application is approved.') }}</p>

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-sm text-red-600 dark:text-red-400 space-y-2">
                    <div class="flex items-center gap-2 font-semibold">
                        <flux:icon name="exclamation-triangle" class="w-4 h-4 text-red-600 dark:text-red-400" />
                        <span>{{ __('Please correct the following errors:') }}</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-4 border rounded-lg bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700" wire:ignore>
                <div id="card-element"></div>
            </div>

            <div id="card-errors" class="hidden p-4 rounded-lg bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-sm text-red-600 dark:text-red-400" role="alert">
                <div class="flex items-start gap-2">
                    <flux:icon name="credit-card" class="w-4 h-4 mt-0.5 flex-shrink-0" />
                    <span id="card-error-message"></span>
                </div>
            </div>

            <flux:button id="submit-card-button" variant="primary" class="w-full" onclick="handleCardSubmit()">
                {{ __('Submit Application') }}
            </flux:button>
        </div>

    @elseif ($step === 5)
        <div class="text-center space-y-4">
            <flux:heading size="xl">{{ __('Application Submitted!') }}</flux:heading>
            <p class="text-zinc-500">{{ __('Your membership application is pending review. We will notify you once it has been approved.') }}</p>
            <flux:button :href="route('dashboard')" variant="primary" wire:navigate>
                {{ __('Go to Dashboard') }}
            </flux:button>
        </div>
    @endif

    <div class="flex justify-between text-sm text-zinc-400">
        @if ($step > 1 && $step < 4)
            <button wire:click="goToStep({{ $step - 1 }})" class="hover:underline">{{ __('Back') }}</button>
        @else
            <span></span>
        @endif
        <span>{{ __('Step :step of 5', ['step' => $step]) }}</span>
    </div>

    @script
    <script>
        let stripeInstance = null;
        let stripeElements = null;
        let cardElement = null;

        function initStripe() {
            if (stripeInstance) return;
            stripeInstance = Stripe('{{ config('cashier.key') }}');
        }

        function mountCard() {
            initStripe();

            const container = document.getElementById('card-element');
            if (!container) return;

            if (cardElement) {
                cardElement.destroy();
                cardElement = null;
            }

            stripeElements = stripeInstance.elements();
            
            const isDark = document.documentElement.classList.contains('dark');
            cardElement = stripeElements.create('card', {
                hidePostalCode: true,
                style: {
                    base: {
                        color: isDark ? '#ffffff' : '#09090b',
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: isDark ? '#a1a1aa' : '#71717a',
                        },
                    },
                    invalid: {
                        color: '#ef4444',
                        iconColor: '#ef4444',
                    },
                },
            });
            
            cardElement.mount('#card-element');

            // Listen for real-time validation errors on the card element
            cardElement.on('change', function(event) {
                const errorDiv = document.getElementById('card-errors');
                const messageSpan = document.getElementById('card-error-message');
                if (event.error) {
                    messageSpan.textContent = event.error.message;
                    errorDiv.classList.remove('hidden');
                } else {
                    messageSpan.textContent = '';
                    errorDiv.classList.add('hidden');
                }
            });
        }

        window.handleCardSubmit = async function() {
            if (!cardElement) {
                mountCard();
                if (!cardElement) return;
            }

            const button = document.getElementById('submit-card-button');
            button.disabled = true;
            button.textContent = 'Processing...';

            // Clear previous errors before submission starts
            const errorDiv = document.getElementById('card-errors');
            const messageSpan = document.getElementById('card-error-message');
            errorDiv.classList.add('hidden');
            messageSpan.textContent = '';

            const { setupIntent, error } = await stripeInstance.confirmCardSetup(
                '{{ $clientSecret }}',
                {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: $wire.name,
                            phone: $wire.phone,
                            address: {
                                line1: $wire.line_1,
                                line2: $wire.line_2 || null,
                                city: $wire.city,
                                state: $wire.county || null,
                                postal_code: $wire.postcode,
                                country: $wire.country,
                            }
                        }
                    }
                }
            );

            if (error) {
                button.disabled = false;
                button.textContent = 'Submit Application';
                messageSpan.textContent = error.message;
                errorDiv.classList.remove('hidden');
                return;
            }

            try {
                await $wire.set('paymentMethodId', setupIntent.payment_method);
                await $wire.call('confirmPayment');
            } catch (err) {
                button.disabled = false;
                button.textContent = 'Submit Application';
            }
        };

        $wire.$watch('step', (value) => {
            if (value === 4) {
                setTimeout(mountCard, 50);
            }
        });

        if ($wire.step === 4) {
            setTimeout(mountCard, 50);
        }
    </script>
    @endscript
</div>

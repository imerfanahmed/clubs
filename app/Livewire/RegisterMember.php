<?php

namespace App\Livewire;

use App\Events\MemberRegistered;
use App\Models\Package;
use App\Models\User;
use App\Services\PostcodeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Register as Member')]
class RegisterMember extends Component
{
    public int $step = 1;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $postcode = '';

    public array $addressOptions = [];

    public ?int $selectedAddressIndex = null;

    public string $line_1 = '';

    public string $line_2 = '';

    public string $city = '';

    public string $county = '';

    public string $country = 'GB';

    public ?int $package_id = null;

    public string $paymentMethodId = '';

    public string $clientSecret = '';

    public ?int $pendingUserId = null;

    public function render()
    {
        return view('livewire.register-member', [
            'packages' => Package::where('is_active', true)->orderBy('sort_order')->get(),
        ])->layout('layouts.auth');
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= 5) {
            $this->step = $step;
        }
    }

    public function submitPersonal(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->step = 2;
    }

    public function lookupPostcode(PostcodeService $postcodeService): void
    {
        $this->validate([
            'postcode' => ['required', 'string', 'max:10'],
        ]);

        if (! PostcodeService::isValidUkPostcode($this->postcode)) {
            Flux::toast(variant: 'error', text: 'Invalid UK postcode format.');

            return;
        }

        $result = $postcodeService->lookup($this->postcode);

        if (! $result['success']) {
            Flux::toast(variant: 'error', text: $result['message']);

            return;
        }

        $this->addressOptions = $result['addresses'];
        $this->selectedAddressIndex = null;
    }

    public function selectAddress(int $index): void
    {
        if (! isset($this->addressOptions[$index])) {
            return;
        }

        $address = $this->addressOptions[$index];
        $this->line_1 = $address['line_1'];
        $this->line_2 = $address['line_2'] ?? '';
        $this->city = $address['city'];
        $this->county = $address['county'] ?? '';
        $this->postcode = $address['postcode'];
        $this->selectedAddressIndex = $index;
    }

    public function submitAddress(): void
    {
        $this->validate([
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'size:2'],
        ]);

        $this->step = 3;
    }

    public function submitPackage(): void
    {
        $this->validate([
            'package_id' => ['required', 'exists:packages,id'],
        ]);

        $setupIntent = \Laravel\Cashier\Cashier::stripe()->setupIntents->create([
            'payment_method_types' => ['card'],
        ]);

        $this->clientSecret = $setupIntent->client_secret;

        $this->step = 4;
    }

    public function confirmPayment(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'size:2'],
            'package_id' => ['required', 'exists:packages,id'],
            'paymentMethodId' => ['required', 'string'],
        ]);

        $user = \Illuminate\Support\Facades\DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'package_id' => $this->package_id,
                'status' => 'pending',
            ]);

            $user->assignRole('member');

            $user->address()->create([
                'line_1' => $this->line_1,
                'line_2' => $this->line_2 ?: null,
                'city' => $this->city,
                'county' => $this->county ?: null,
                'postcode' => $this->postcode,
                'country' => $this->country,
            ]);

            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($this->paymentMethodId);

            return $user;
        });

        $this->pendingUserId = $user->id;

        Auth::login($user);

        MemberRegistered::dispatch($user);

        $this->step = 5;
    }
}

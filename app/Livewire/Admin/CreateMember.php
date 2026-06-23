<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Member')]
class CreateMember extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $package_id = null;

    public string $line_1 = '';

    public string $line_2 = '';

    public string $city = '';

    public string $county = '';

    public string $postcode = '';

    public string $country = 'GB';

    public function create(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'package_id' => ['required', 'exists:packages,id'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'size:2'],
        ]);

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'package_id' => $this->package_id,
                'status' => 'active',
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
        });

        $this->reset();

        Flux::toast(variant: 'success', text: 'Member created successfully.');
    }

    #[Computed]
    public function packages()
    {
        return Package::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function render()
    {
        return view('livewire.admin.create-member')
            ->layout('layouts.app');
    }
}

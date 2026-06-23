<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Packages')]
class Packages extends Component
{
    public bool $showForm = false;

    public ?int $editingPackageId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public int $price = 0;

    public string $interval = 'month';

    public bool $isActive = true;

    public int $sortOrder = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingPackageId = null;
    }

    public function edit(int $id): void
    {
        $package = Package::findOrFail($id);
        $this->editingPackageId = $package->id;
        $this->name = $package->name;
        $this->slug = $package->slug;
        $this->description = $package->description ?? '';
        $this->price = $package->price;
        $this->interval = $package->interval;
        $this->isActive = $package->is_active;
        $this->sortOrder = $package->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('packages', 'slug')->ignore($this->editingPackageId)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'interval' => ['required', 'in:month,year'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
        ]);

        Package::updateOrCreate(
            ['id' => $this->editingPackageId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description ?: null,
                'price' => $this->price,
                'interval' => $this->interval,
                'is_active' => $this->isActive,
                'sort_order' => $this->sortOrder,
            ]
        );

        $this->resetForm();
        Flux::toast(variant: 'success', text: $this->editingPackageId ? 'Package updated.' : 'Package created.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editingPackageId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->price = 0;
        $this->interval = 'month';
        $this->isActive = true;
        $this->sortOrder = 0;
    }

    #[Computed]
    public function packages()
    {
        return Package::orderBy('sort_order')->get();
    }

    public function render()
    {
        return view('livewire.admin.packages')
            ->layout('layouts.app');
    }
}

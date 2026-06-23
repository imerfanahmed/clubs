<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Campaigns')]
class Index extends Component
{
    #[Computed]
    public function campaigns()
    {
        return Campaign::active()->latest()->get();
    }

    public function render()
    {
        return view('livewire.campaigns.index')
            ->layout('layouts.public');
    }
}

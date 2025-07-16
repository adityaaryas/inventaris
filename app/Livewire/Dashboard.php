<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.back.dashboard')->layout('components.layout.dashlayout');
    }
}

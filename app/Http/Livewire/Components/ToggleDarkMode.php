<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class ToggleDarkMode extends Component
{
    public function render()
    {
        $user = auth()->user();
        return view('livewire.components.toggle-dark-mode', ['status' => $user->dark_mode]);
    }

    public function toggleDarkMode()
    {
        $user = auth()->user();
        $user->update(['dark_mode' => !$user->dark_mode]);

        $this->emit('updateDarkMode', $user->dark_mode);
    }
}

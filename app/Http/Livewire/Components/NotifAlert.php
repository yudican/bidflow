<?php

namespace App\Http\Livewire\Components;

use App\Jobs\GetGpTokenQueue;
use Livewire\Component;

class NotifAlert extends Component
{
    public function render()
    {
        return view('livewire.components.notif-alert');
    }

    
}

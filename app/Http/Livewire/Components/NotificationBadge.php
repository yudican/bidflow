<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class NotificationBadge extends Component
{
    public $notification_count = 0;

    protected $listeners = ['updateNotification'];
    public function mount()
    {
        $this->notification_count = auth()->user()->notification_count;
    }
    public function render()
    {
        return view('livewire.components.notification-badge');
    }

    public function updateNotification()
    {
        $this->notification_count = auth()->user()->notification_count;
    }
}

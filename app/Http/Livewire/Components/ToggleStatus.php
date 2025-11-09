<?php

namespace App\Http\Livewire\Components;

use App\Models\AgentDetail;
use Livewire\Component;

class ToggleStatus extends Component
{
    public $active;
    public $data_id;
    public $field;
    public $emitter;

    public function mount($id, $active, $field, $emitter = null, $parent_id = null, $child_id = null)
    {
        $this->data_id = $id;
        $this->active = $active == 1 ? true : false;
        $this->field = $field;
        $this->emitter = $emitter;
        $this->parent_id = $parent_id;
        $this->child_id = $child_id;
    }

    public function render()
    {
        return view('livewire.components.toggle-status');
    }

    public function toggleStatusAgent($id, $field, $emiter = 'toggleStatusAgent')
    {
        $this->active = !$this->active;
        $this->emit($emiter, [
            'id' => $id,
            'field' => $field,
            'parent_id' => $this->parent_id,
            'child_id' => $this->child_id
        ]);
    }
}

<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\User;

class AgentDomainTable extends LivewireDatatable
{
    protected $listeners = ['setDomainId'];
    public $domainId;
    public function builder()
    {
        return User::query()->whereHas('agentDetail');
    }

    public function columns()
    {
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Nama')->searchable(),
            Column::callback('agentDetail.user_id', function ($user_id) {
                $user = User::whereHas('domains', function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                })->exists();
                return view('livewire.components.toggle-status', [
                    'data_id' => $user_id,
                    'active' => $user ? true : false,
                    'field' => $user ? true : false,
                    'emitter' => null
                ]);
            })->label('Status'),
        ];
    }

    public function toggleStatusAgent($id, $field = null, $emitter = null)
    {
        $user = User::find($id);
        if ($field) {
            return $user->domains()->detach($this->domainId);
        }
        return $user->domains()->attach($this->domainId);
    }

    public function setDomainId($id)
    {
        $this->domainId = $id;
    }
}

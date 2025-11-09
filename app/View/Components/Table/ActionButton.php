<?php

namespace App\View\Components\Table;

use App\Models\Cases;
use App\Models\Team;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\Component;

class ActionButton extends Component
{
    public $id;
    public $segment;
    public $canUpdate = false;
    public $canDelete = false;
    public $badge = false;
    public $extraActions = [];
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $segment = null, $badge = false, $extraActions = [])
    {
        $user = auth()->user();

        $curteam = Team::find($user->current_team_id);
        $this->id = $id;
        $this->segment = $segment;

        $this->canUpdate = $curteam->userHasPermission($user, $segment . ':update');
        $this->canDelete = $curteam->userHasPermission($user, $segment . ':delete');
        $this->badge = $badge;
        $extraActions = [];

        if ($segment == 'contact') {
            $this->canDelete = false;
            $this->canUpdate = false;
            $extraActions = [
                ['label' => 'Detail', 'type' => 'default', 'icon' => 'fa fa-eye', 'route' => "getDetailById('$id')"],
                ['label' => 'Ubah', 'type' => 'default', 'icon' => 'fa fa-eye', 'route' => "getDetailById2('$id')"],
            ];
        }
        if ($segment == 'cases') {
            $cases = Cases::find($id);
            $this->canDelete = false;
            $this->canUpdate = true;
            $telepon = $cases->contactUser?->telepon;
            $extraActions = [
                // ['label' => 'Lihat', 'type' => 'default', 'icon' => 'fa fa-eye', 'route' => "getDetailById('.$cases->uid_case.')"],
                ['label' => 'Chat Whatsapp', 'type' => 'default', 'icon' => 'fa fa-eye', 'route' => "chatWA('.$telepon.')"],
            ];
        }
        if ($segment == 'refund-master') {
            $this->canDelete = true;
            $this->canUpdate = true;
        }
        if ($segment == 'retur-master') {
            $this->canDelete = true;
            $this->canUpdate = true;
        }
        if ($segment == 'sr-master') {
            $this->canDelete = true;
            $this->canUpdate = true;
        }
        $this->extraActions = $extraActions;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.table.action-button');
    }
}

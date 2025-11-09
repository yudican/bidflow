<?php

namespace App\Http\Livewire\Settings;

use App\Exports\MenuExport;
use App\Models\Menu as ModelsMenu;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;

class Menu extends Component
{

    public $menus_id;
    public $menu_label;
    public $menu_route;
    public $menu_icon;
    public $menu_order;
    public $badge;
    public $show_menu = 1;
    public $parent_id;
    public $role_id = ['aaf5ab14-a1cd-46c9-9838-84188cd064b6'];
    public $menus = [];

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataById', 'getId'];

    public function render()
    {
        return view('livewire.settings.menus', [
            'items' => ModelsMenu::whereNull('parent_id')->orderBy('menu_order', 'ASC')->get(),
            'roles' => Role::all()
        ]);
    }

    public function changeMenu($datas)
    {
        foreach ($datas as $data) {
            if ($data['children']) {
                ModelsMenu::find($data['id'])->update([
                    'menu_order' => $data['order'],
                    'parent_id' => null,
                ]);
                foreach ($data['children'] as $children) {
                    ModelsMenu::find($children['id'])->update([
                        'parent_id' => $data['id'],
                        'menu_order' => $children['order'],
                    ]);
                }
            } else {
                ModelsMenu::find($data['id'])->update([
                    'menu_order' => $data['order'],
                    'parent_id' => null,
                ]);
            }
        }
        setSetting('REFRESH_MENU', 'true');
        return $this->emit('showAlert', ['msg' => 'Menu Berhasil Diupdate']);
    }

    public function store()
    {
        $this->_validate();
        $data = [
            'menu_label'  => $this->menu_label,
            'menu_route'  => $this->menu_route,
            'menu_icon'  => $this->menu_icon,
            'menu_order'  => $this->_getOrderNumber(),
            'parent_id'  => $this->parent_id,
            'show_menu'  => $this->show_menu,
            'badge'  => $this->badge,
        ];

        try {
            DB::beginTransaction();

            $menu = ModelsMenu::create($data);
            $menu->roles()->sync($this->role_id);

            if ($this->show_menu > 0) {
                if ($this->menu_route != '#') {
                    $permission = Permission::insert([
                        [
                            'id' => Uuid::uuid4()->toString(),
                            'permission_value' => $this->menu_route . ':create',
                            'permission_name' => 'Create ' . $this->menu_label,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ],
                        [
                            'id' => Uuid::uuid4()->toString(),
                            'permission_value' => $this->menu_route . ':update',
                            'permission_name' => 'Update ' . $this->menu_label,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ],
                        [
                            'id' => Uuid::uuid4()->toString(),
                            'permission_value' => $this->menu_route . ':delete',
                            'permission_name' => 'Delete ' . $this->menu_label,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ],
                    ]);
                }
            }
            setSetting('REFRESH_MENU', 'true');
            $this->_reset();
            DB::commit();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'menu_label'  => $this->menu_label,
            'menu_route'  => $this->menu_route,
            'menu_icon'  => $this->menu_icon,
            'menu_order'  => $this->menu_order,
            'parent_id'  => $this->parent_id,
            'show_menu'  => $this->show_menu,
            'badge'  => $this->badge,
        ];

        try {
            DB::beginTransaction();

            $menu = ModelsMenu::find($this->menus_id);
            $menu->update($data);

            $menu->roles()->sync($this->role_id);
            setSetting('REFRESH_MENU', 'true');
            $this->_reset();
            DB::commit();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();

            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function delete()
    {
        ModelsMenu::find($this->menus_id)->delete();
        setSetting('REFRESH_MENU', 'true');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'menu_label'  => 'required',
            'menu_route'  => 'required',
        ];

        return $this->validate($rule);
    }

    public function getDataById($menus_id)
    {
        $menus = ModelsMenu::find($menus_id);
        $this->menus_id = $menus->id;
        $this->menu_label = $menus->menu_label;
        $this->menu_route = $menus->menu_route;
        $this->menu_icon = $menus->menu_icon;
        $this->menu_order = $menus->menu_order;
        $this->parent_id = $menus->parent_id;
        $this->show_menu = $menus->show_menu;
        $this->badge = $menus->badge;
        $role_id = array_merge($menus->roles()->pluck('roles.id')->toArray(), ['aaf5ab14-a1cd-46c9-9838-84188cd064b6']);
        $this->role_id = $role_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getId($menus_id)
    {
        $menus = ModelsMenu::find($menus_id);
        $this->menus_id = $menus->id;
    }

    public function toggleForm($form)
    {
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('loadForm');
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->menus_id = null;
        $this->menu_label = null;
        $this->menu_route = null;
        $this->menu_icon = null;
        $this->show_menu = 1;
        $this->menu_order = null;
        $this->badge = null;
        $this->role_id = ['aaf5ab14-a1cd-46c9-9838-84188cd064b6'];
        $this->parent_id = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function _getOrderNumber()
    {
        $menu = ModelsMenu::limit(1)->orderBy('menu_order', 'DESC')->first();
        if ($menu) {
            return $menu->menu_order + 1;
        }
        return 1;
    }

    public function export()
    {
        return Excel::download(new MenuExport(), 'data-menu.xlsx');
    }
}

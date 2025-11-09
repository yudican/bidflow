<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MenuController extends Controller
{
    public function index($menu_id = null)
    {
        return view('spa.spa-index');
    }

    public function loadMenu()
    {
        $menus = Menu::whereNull('parent_id')->orderBy('menu_order', 'ASC')->get();
        $data = [];
        $roles = Role::all();
        foreach ($menus as $key => $menu) {
            $route = $menu->menu_route;
            $spa = false;
            if (!in_array($route, ['#', '', null])) {
                try {
                    $route = route($menu->menu_route);
                    if (strpos($route, 'spa')) {
                        $spa = true;
                    }
                } catch (\Throwable $th) {
                    $route = '#';
                }
            }
            $role_list = [];

            foreach ($roles as $index => $role) {
                $role_id = $menu->roles()->pluck('roles.id')->toArray();
                if (in_array($role->id, $role_id)) {
                    $role_list[$index]['id'] = $role->id;
                    $role_list[$index]['name'] = $role->role_name;
                    $role_list[$index]['menu_id'] = $menu->id;
                    $role_list[$index]['status'] = true;
                } else {
                    $role_list[$index]['id'] = $role->id;
                    $role_list[$index]['name'] = $role->role_name;
                    $role_list[$index]['menu_id'] = $menu->id;
                    $role_list[$index]['status'] = false;
                }
            }
            $data[$key] = [
                'id' => $menu->id,
                'menu_label' => $menu->menu_label,
                'menu_url' => $route,
                'menu_route' => $menu->menu_route,
                'menu_icon' => $menu->menu_icon,
                'badge' => $menu->badge,
                'show_menu' => $menu->show_menu,

                'menu_order' => $menu->menu_order,
                'parent_id' => $menu->parent_id,
                'roles' => $role_list,
                'role_id' => $role_id
            ];
            $data[$key]['childrens'] = $menu->children->map(function ($item) use ($roles) {
                $route = $item->menu_route;
                $spa = false;
                if (!in_array($route, ['#', '', null])) {
                    try {
                        $route = route($item->menu_route);
                        if (strpos($route, 'spa')) {
                            $spa = true;
                        }
                    } catch (\Throwable $th) {
                        $route = '#';
                    }
                }

                $role_list = [];

                foreach ($roles as $index => $role) {
                    $role_id = $item->roles()->pluck('roles.id')->toArray();
                    if (in_array($role->id, $role_id)) {
                        $role_list[$index]['id'] = $role->id;
                        $role_list[$index]['name'] = $role->role_name;
                        $role_list[$index]['menu_id'] = $item->id;
                        $role_list[$index]['status'] = true;
                    } else {
                        $role_list[$index]['id'] = $role->id;
                        $role_list[$index]['name'] = $role->role_name;
                        $role_list[$index]['menu_id'] = $item->id;
                        $role_list[$index]['status'] = false;
                    }
                }
                return [
                    'id' => $item->id,
                    'menu_label' => $item->menu_label,
                    'menu_route' => $item->menu_route,
                    'menu_url' => $route,
                    'menu_icon' => $item->menu_icon,
                    'badge' => $item->badge,
                    'show_menu' => $item->show_menu,

                    'menu_order' => $item->menu_order,
                    'parent_id' => $item->parent_id,
                    'roles' => $role_list,
                    'role_id' => $role_id
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function createMenu(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->show_menu > 0) {
                if ($request->menu_route != '#') {
                    $menu_exists = Menu::where('menu_route', $request->menu_route)->exists();
                    if ($menu_exists) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Menu route already exists'
                        ], 400);
                    }
                    Permission::insert([
                        [
                            'id' => Uuid::uuid4()->toString(),
                            'permission_value' => $request->menu_route . ':create',
                            'permission_name' => 'Create ' . $request->menu_label,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ],
                        [
                            'id' => Uuid::uuid4()->toString(),
                            'permission_value' => $request->menu_route . ':update',
                            'permission_name' => 'Update ' . $request->menu_label,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ],
                        [
                            'id' => Uuid::uuid4()->toString(),
                            'permission_value' => $request->menu_route . ':delete',
                            'permission_name' => 'Delete ' . $request->menu_label,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ],
                    ]);
                }
            }

            $data = [
                'menu_label'  => $request->menu_label,
                'menu_route'  => $request->menu_route,
                'menu_icon'  => $request->menu_icon,
                'menu_order'  => $this->_getOrderNumber(),
                'parent_id'  => $request->parent_id,
                'show_menu'  => $request->show_menu,
                'badge'  => $request->badge,
            ];

            $menu = Menu::create($data);
            $menu->roles()->sync($request->role_id);
            setSetting('REFRESH_MENU', 'true');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Menu Berhasil Ditambahkan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Menu Gagal Ditambahkan'
            ]);
        }
    }

    public function updateMenu(Request $request, $menu_id)
    {
        try {
            DB::beginTransaction();
            $menu = Menu::find($menu_id);
            if ($request->show_menu > 0) {
                // if ($request->menu_route != '#') {
                //     $menu_exists = Menu::where('id', '!=', $menu->id)->where('menu_route', $request->menu_route)->exists();
                //     if ($menu_exists) {
                //         return response()->json([
                //             'status' => 'error',
                //             'message' => 'Menu route already exists'
                //         ], 400);
                //     }
                //     $permissions = ['create', 'update', 'delete'];
                //     foreach ($permissions as $key => $permission) {
                //         Permission::updateOrcreate([
                //             'permission_value' => $request->menu_route . ':' . $permission
                //         ], [
                //             'permission_name' => ucfirst($permission) . ' ' . $request->menu_label,
                //             'updated_at' => Carbon::now()
                //         ]);
                //     }
                // }
            }
            $data = [
                'menu_label'  => $request->menu_label,
                'menu_route'  => $request->menu_route,
                'menu_icon'  => $request->menu_icon,
                'menu_order'  => $request->menu_order,
                'parent_id'  => $request->parent_id,
                'show_menu'  => $request->show_menu,
                'badge'  => $request->badge,
            ];
            $menu->update($data);
            $menu->roles()->sync($request->role_id);
            setSetting('REFRESH_MENU', 'true');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Menu Berhasil Diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Menu Gagal Diupdate'
            ]);
        }
    }

    public function copyMenu(Request $request, $menu_id)
    {
        try {
            DB::beginTransaction();

            // Find the original menu and its children
            $originalMenu = Menu::with('children')->findOrFail($menu_id);

            // Create a new menu record with the same attributes as the original
            $newMenu = $originalMenu->replicate();
            $newMenu->menu_label .= ' (Copy)';
            $newMenu->save();

            // Copy permissions if the menu has any
            if ($originalMenu->permissions) {
                foreach ($originalMenu->permissions as $permission) {
                    $newPermission = $permission->replicate();
                    $newPermission->id = Uuid::uuid4()->toString();
                    $newPermission->save();
                    $newMenu->permissions()->attach($newPermission);
                }
            }

            // Copy roles
            $newMenu->roles()->sync($originalMenu->roles->pluck('id')->toArray());

            // Recursive function to copy children
            $this->copyChildren($originalMenu, $newMenu->id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu copied successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Menu copy failed'
            ]);
        }
    }

    private function copyChildren($originalMenu, $newParentId)
    {
        foreach ($originalMenu->children as $child) {
            // Create a new child menu record
            $newChild = $child->replicate();
            $newChild->parent_id = $newParentId;
            $newChild->save();

            // Copy permissions if the child menu has any
            if ($child->permissions) {
                foreach ($child->permissions as $permission) {
                    $newPermission = $permission->replicate();
                    $newPermission->id = Uuid::uuid4()->toString();
                    $newPermission->save();
                    $newChild->permissions()->attach($newPermission);
                }
            }

            // Copy roles
            $newChild->roles()->sync($child->roles->pluck('id')->toArray());

            // Recursively copy children
            if ($child->children) {
                $this->copyChildren($child, $newChild->id);
            }
        }
    }

    public function deleteMenu($menu_id)
    {
        try {
            DB::beginTransaction();
            $menu = Menu::find($menu_id);
            $menu->roles()->detach();
            $menu->children()->delete();
            $menu->delete();
            setSetting('REFRESH_MENU', 'true');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Menu Berhasil Dihapus'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Menu Gagal Dihapus'
            ]);
        }
    }

    public function orderMenu(Request $request)
    {
        foreach ($request->menus as $key => $menu) {
            $menus = Menu::find($menu['id']);
            $menus->update([
                'menu_order' => intval($menu['value'])
            ]);
        }
        setSetting('REFRESH_MENU', 'true');
        return response()->json([
            'status' => 'success',
            'message' => 'Menu Berhasil Diupdate',
            'data' => $request->menus
        ]);
    }

    public function updateMenuRole(Request $request, $menu_id)
    {
        $menu = Menu::find($menu_id);
        if ($request->value) {
            $menu->roles()->attach($request->role_id);
            setSetting('REFRESH_MENU', 'true');
            return response()->json([
                'status' => 'error',
                'message' => 'Role Berhasil Diupdate'
            ]);
        }

        $menu->roles()->detach($request->role_id);

        return response()->json([
            'status' => 'error',
            'message' => 'Role Berhasil Diupdate'
        ]);
    }

    public function _getOrderNumber()
    {
        $menu = Menu::limit(1)->orderBy('menu_order', 'DESC')->first();
        if ($menu) {
            return $menu->menu_order + 1;
        }
        return 1;
    }
}

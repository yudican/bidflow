<?php

namespace App\Http\Controllers\Spa\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Str;

class RoleController extends Controller
{
    public function index($role_id = null)
    {
        return view('spa.spa-index');
    }

    public function listRole(Request $request)
    {
        $search = $request->search;

        $row =  Role::query();
        if ($search) {
            $row->where(function ($query) use ($search) {
                $query->where('role_name', 'like', "%$search%");
            });
        }

        $rows = $row->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Role'
        ]);
    }

    public function getDetailRole($role_id)
    {
        $row = Role::find($role_id);

        return response()->json([
            'status' => 'success',
            'data' => $row,
            'message' => 'Detail Role'
        ]);
    }

    public function saveRole(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'id' => Uuid::uuid4(),
                'role_name'  => $request->role_name,
                'role_type'  => Str::slug($request->role_name),
                'rate_limit_status'  => $request->rate_limit_status ?? 0
            ];

            Role::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Role Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Role Gagal Disimpan'
            ], 400);
        }
    }

    public function updateRole(Request $request, $role_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'role_name'  => $request->role_name,
                'role_type'  => Str::slug($request->role_name),
                'rate_limit_status'  => $request->rate_limit_status ?? 0
            ];
            $row = Role::find($role_id);

            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Role Berhasil di Ubah'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Role Gagal di Ubah'
            ], 400);
        }
    }

    public function deleteRole($role_id)
    {
        // $row = Role::find($role_id);
        // $row->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Role tidak bisa dihapus'
        ]);
    }
}

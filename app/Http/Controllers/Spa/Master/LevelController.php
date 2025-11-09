<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class LevelController extends Controller
{
    public function index($level_id = null)
    {
        return view('spa.spa-index');
    }

    public function listLevel(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $level =  Level::query();
        $level->with('roles:role_name');

        if ($search) {
            $level->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $level->whereIn('status', $status);
        }

        $levels = $level->orderBy('created_at', 'desc')->paginate($request->perpage);
        $levels->each(function ($level) {
            $roleNames = $level->roles->pluck('role_name')->implode(', ');
            $level->role_names = $roleNames;
        });

        return response()->json([
            'status' => 'success',
            'data' => $levels,
            'message' => 'List Level'
        ]);
    }


    public function getDetailLevel($level_id)
    {
        $brand = Level::find($level_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Level'
        ]);
    }

    public function saveLevel(Request $request)
    {
        try {
            DB::beginTransaction();
            $role_id = json_decode($request->role_id, true);
            $data = [
                'name'  => $request->name,
                'description'  => $request->description,
                'status'  => 1
            ];

            $level = Level::create($data);
            $level->roles()->attach($role_id);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Level Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Level Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateLevel(Request $request, $level_id)
    {
        try {
            DB::beginTransaction();
            $role_id = json_decode($request->role_id, true);
            $data = [
                'name'  => $request->name,
                'description'  => $request->description,
                'status'  => 1
            ];
            $row = Level::find($level_id);
            $row->update($data);
            $row->roles()->sync($role_id);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Level Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Level Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteLevel($level_id)
    {
        $banner = Level::find($level_id);
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Level berhasil dihapus'
        ]);
    }
}

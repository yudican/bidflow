<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RateLimitSettingController extends Controller
{
    public function index($role_id = null)
    {
        return view('spa.spa-index');
    }

    public function listRole(Request $request)
    {
        $search = $request->search;

        $role =  Role::query();
        if ($search) {
            $role->where(function ($query) use ($search) {
                $query->where('role_name', 'like', "%$search%");
            });
        }

        $variants = $role->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $variants,
            'message' => 'List Role'
        ]);
    }

    public function updateStatus(Request $request, $role_id)
    {
        $role = Role::find($role_id);
        if ($role) {
            $role->update(['rate_limit_status' => $request->rate_limit_status]);
            return response()->json([
                'status' => 'success',
                'data' => $role,
                'message' => 'Success Update Status'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'error Update Status'
        ], 400);
    }
}

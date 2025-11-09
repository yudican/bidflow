<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Livewire\UserManagement\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function getContactList()
    {
        $user = User::whereHas('roles', function ($query) {
            return $query->where('role_type', ['member', 'agent', 'subagent']);
        })->where('created_by', auth()->user()->id)->get();


        $users = [];

        foreach ($user as $key => $val) {
            $users[] = [
                'id' => $val->id,
                'name' => $val->name,
                'email' => $val->email,
                'telepon' => $val->telepon,
                'bod' => $val->bod,
                'gender' => $val->gender,
                'device_id' => $val->device_id,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'List User',
            'data' => $users
        ]);
    }

    function createContact(Request $request)
    {
        $validate = [
            'name' => 'required',
            'email' => 'required',
            'telepon' => 'required',
            'bod' => 'required',
            'gender' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Tidak Lengkap'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'bod' => $request->bod,
                'gender' => $request->gender,
                'password' => Hash::make('admin123'),
                'created_by' => auth()->user()->id,
            ];

            $role = Role::find('0feb7d3a-90c0-42b9-be3f-63757088cb9a');
            $user = User::create($data);
            $user->brands()->sync(1);
            $user->teams()->sync(1, ['role' => $role->role_type]);
            $user->roles()->sync(1);

            DB::commit();
            return response()->json([
                'message' => 'Successfully add contact',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add contact',
                'data' => []
            ], 400);
        }
    }


    function updateContact(Request $request, $contact_id)
    {
        $validate = [
            'name' => 'required',
            'email' => 'required',
            'telepon' => 'required',
            'bod' => 'required',
            'gender' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Tidak Lengkap'
            ], 400);
        }

        $user = User::find($contact_id);

        if (!$user) {
            return response()->json([
                'message' => 'User Not Found',
                'data' => []
            ], 404);
        }
        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'telepon' => formatPhone($request->telepon),
                'bod' => $request->bod,
                'gender' => $request->gender,
            ];

            $user->update($data);

            DB::commit();
            return response()->json([
                'message' => 'Successfully update contact',
                'data' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error update contact',
                'data' => []
            ], 400);
        }
    }
}

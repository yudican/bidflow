<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AjaxController extends Controller
{
    public function searchUser()
    {
        $query = request()->get('query');
        $users = User::whereHas('roles', function ($q) {
            return $q->whereIn('roles.role_type', ['agent', 'member', 'subagent']);
        })->where('name', 'like', '%' . $query . '%')->paginate(30);
        $data = [];
        foreach ($users as $key => $kelurahan) {
            $data[$key]['id'] = $kelurahan->id;
            $data[$key]['text'] = $kelurahan->name;
        }
        return response()->json($data);
    }

    public function searcContactFromLead()
    {
        $query = request()->get('query');
        $role = request()->get('role');

        $contacts = User::whereHas('roles', function ($q) {
            return $q->whereIn('roles.role_type', ['agent', 'member', 'subagent']);
        });
        if (request()->get('user_id')) {
            $user = User::find(request()->get('user_id'));
            $role_type = $user->role->role_type;
            if (!in_array($role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales'])) {
                $contacts->where('created_by', $user->id);
            }
        }

        $contact_list = $contacts->where('users.name', 'LIKE', '%' . $query . '%')->paginate(30);


        foreach ($contact_list as $key => $contact) {
            $data[$key]['id'] = $contact->id;
            if ($role) {
                $data[$key]['text'] = $contact->name . ' - ' . $contact->role->role_name;
            } else {
                $data[$key]['text'] = $contact->name;
            }
        }
        return response()->json($data);
    }

    public function searcSalesFromLead()
    {
        $query = request()->get('query');
        $sales_list = User::whereHas('roles', function ($q) {
            return $q->whereIn('roles.role_type', ['sales']);
        });

        if (request()->get('user_id')) {
            $user = User::find(request()->get('user_id'));
            $role_type = $user->role->role_type;
            if (!in_array($role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales'])) {
                $sales_list->where('user_id', $user->id);
            }
        }

        $sales = $sales_list->where('users.name', 'LIKE', '%' . $query . '%')->paginate(30);
        foreach ($sales as $key => $sales) {
            $data[$key]['id'] = $sales->id;
            $data[$key]['text'] = $sales->name;
        }
        return response()->json($data);
    }
}

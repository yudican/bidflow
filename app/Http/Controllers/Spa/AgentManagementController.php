<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\AgentAddress;
use App\Models\AgentDetail;
use App\Models\Provinsi;
use Illuminate\Http\Request;

class AgentManagementController extends Controller
{
    public function index($agent_id = null)
    {
        return view('spa.spa-index');
    }

    public function listProvince()
    {
        $provinsi = Provinsi::all();

        return response()->json([
            'status' => 'success',
            'data' => $provinsi
        ]);
    }

    public function listCity($province_id)
    {
        $provinsi = Provinsi::where('pid', $province_id)->first();
        if ($provinsi) {
            $kabupatens = $provinsi->kabupaten;

            return response()->json([
                'status' => 'success',
                'data' => $kabupatens
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function listAgent($city_id)
    {
        $agents = AgentAddress::with(['agent' => function ($query) {
            return $query->orderBy('order', 'ASC');
        }])->where('kabupaten_id', $city_id)->get()->map(function ($item) {
            $item['order'] = $item->agent?->order ?? 0;
            $item['status_agent'] = $item->agent?->user?->status_agent ?? false;
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $agents
        ]);
    }

    public function updateAgent(Request $request)
    {
        $agent = AgentDetail::find($request->agent_id);

        if ($agent) {
            $agent->update([$request->field => $request->value]);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function reOrder(Request $request)
    {
        foreach ($request->data as $key => $value) {
            $agent = AgentDetail::find($value['key']);
            if ($agent) {
                $agent->update(['order' => $value['value']]);
            }
        }


        return response()->json([
            'status' => 'success',
        ]);
    }
}

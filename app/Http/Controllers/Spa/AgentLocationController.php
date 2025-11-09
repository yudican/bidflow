<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\User;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use App\Models\AgentAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgentLocationController extends Controller
{

    public function listUserBySubdistrict($subdistrict_id)
    {
        $agent_address = AgentAddress::where('kecamatan_id', $subdistrict_id)->get();
        return response()->json([
            'data' => $agent_address,
            'message' => 'success'
        ]);
    }

    public function listProvinceByUser()
    {
        $arr_province = [];
        $agent_address = AgentAddress::select('provinsi_id')->distinct()->get();
        
        if (!empty($agent_address)) {
            foreach ($agent_address as $agent) {
                $provinces = Provinsi::find($agent->provinsi_id);
                if ($provinces != null){
                    array_push($arr_province, $provinces);
                }
            }
        }
        return response()->json([
            'data' => $arr_province,
            'message' => 'success'
        ]);
    }

    public function listDistrictByProvince($province_id)
    {
        $districts = Kabupaten::where('prov_id', $province_id)->get();

        return response()->json([
            'data' => $districts,
            'message' => 'success'
        ]);
    }

    public function listSubdistrictByDistrict($district_id)
    {
        $subdistricts = Kecamatan::where('kab_id', $district_id)->get();

        return response()->json([
            'data' => $subdistricts,
            'message' => 'success'
        ]);
    }

    public function listAgentDomain()
    {
        $domains = Domain::all();

        return response()->json([
            'data' => $domains,
            'message' => 'success'
        ]);
    }
    public function listAgentByDomain(Request $request)
    {
        return response()->json([
            'data' => User::whereHas('agentDetail')->paginate($request->perpage),
            'message' => 'success'
        ]);

        return response()->json([
            'data' => [],
            'message' => 'success'
        ]);
    }

}

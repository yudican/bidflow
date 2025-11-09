<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\PriorityCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class PriorityCaseController extends Controller
{
    public function index($priority_case_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPriorityCase(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $priority_case =  PriorityCase::query();
        if ($search) {
            $priority_case->where(function ($query) use ($search) {
                $query->where('priority_name', 'like', "%$search%");
            });
        }

        if ($status) {
            $priority_case->whereIn('status', $status);
        }


        $priority_cases = $priority_case->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $priority_cases,
            'message' => 'List PriorityCase'
        ]);
    }


    public function getDetailPriorityCase($priority_case_id)
    {
        $brand = PriorityCase::find($priority_case_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail PriorityCase'
        ]);
    }

    public function savePriorityCase(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'priority_name'  => $request->priority_name,
                'priority_day'  => $request->priority_day,
                'notes'  => $request->notes
            ];

            PriorityCase::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Priority Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data PriorityCase Gagal Disimpan'
            ], 400);
        }
    }

    public function updatePriorityCase(Request $request, $priority_case_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'priority_name'  => $request->priority_name,
                'priority_day'  => $request->priority_day,
                'notes'  => $request->notes
            ];
            $row = PriorityCase::find($priority_case_id);
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Priority Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data PriorityCase Gagal Disimpan'
            ], 400);
        }
    }

    public function deletePriorityCase($priority_case_id)
    {
        $banner = PriorityCase::find($priority_case_id);
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data PriorityCase berhasil dihapus'
        ]);
    }
}

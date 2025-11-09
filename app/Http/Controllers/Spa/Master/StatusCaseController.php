<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\StatusCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class StatusCaseController extends Controller
{
    public function index($status_case_id = null)
    {
        return view('spa.spa-index');
    }

    public function listStatusCase(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $status_case =  StatusCase::query();
        if ($search) {
            $status_case->where(function ($query) use ($search) {
                $query->where('status_name', 'like', "%$search%");
            });
        }

        if ($status) {
            $status_case->whereIn('status', $status);
        }


        $status_cases = $status_case->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $status_cases,
            'message' => 'List StatusCase'
        ]);
    }


    public function getDetailStatusCase($status_case_id)
    {
        $brand = StatusCase::find($status_case_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail StatusCase'
        ]);
    }

    public function saveStatusCase(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status_name'  => $request->status_name,
                'notes'  => $request->notes,
            ];

            StatusCase::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Status Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data StatusCase Gagal Disimpan'
            ], 400);
        }
    }

    public function updateStatusCase(Request $request, $status_case_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status_name'  => $request->status_name,
                'notes'  => $request->notes,
            ];
            $row = StatusCase::find($status_case_id);
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Status Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data StatusCase Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteStatusCase($status_case_id)
    {
        $banner = StatusCase::find($status_case_id);
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data StatusCase berhasil dihapus'
        ]);
    }
}
